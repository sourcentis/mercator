<?php

namespace App\Console\Commands;

use App\Models\Parameter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CPESync extends Command
{
    protected $signature = 'mercator:cpe-sync
                            {--per-page=2000 : Number of items per page (max NVD 10000)}
                            {--max=0 : Maximum number of items to process (0 = unlimited)}
                            {--full : Ignore lastMod* window and fetch the full dictionary}
                            {--since= : ISO8601 UTC, ex. 2025-09-20T00:00:00Z (override cache)}
                            {--delay= : Seconds to wait between two NVD requests (default: 6 without API key, 1 with)}
                            {--restart : Ignore any interrupted sync checkpoint and start over}
                            {--now : Run immediately, skip random delay}';

    protected $description = 'Synchronize the database with the CPE dictionary from NVD.';

    /** @internal Cache key used to store the last successful run timestamp in the parameters table. */
    public const string PARAM_LAST_RUN = 'cpe_sync.last_run';

    /** @internal Checkpoint of an interrupted sync (JSON), used to resume where it stopped. */
    public const string PARAM_RESUME = 'cpe_sync.resume';

    /**
     * Backoff delays (ms) applied when NVD answers 403/429/5xx or the connection fails.
     * NVD frequently returns transient 503 and rate-limits with 403 (5 req/30 s without key).
     */
    protected array $retryDelays = [10000, 20000, 40000, 60000, 120000, 180000];

    /**
     * @throws \Throwable
     * @throws NotFoundExceptionInterface
     * @throws ConnectionException
     * @throws ContainerExceptionInterface
     * @throws RequestException
     */
    public function handle(): int
    {
        // Optional random startup delay (0–15 minutes) unless --now is provided
        if (! $this->option('now')) {
            $delay = rand(0, 900); // seconds
            $this->info("Random startup delay: sleeping for {$delay} seconds...");
            sleep($delay);
        }

        $apiKey = config('services.nvd.api_key');
        $apiUrl = config('services.nvd.api_url');

        $perPage = min(max((int) $this->option('per-page'), 1), 10000);
        $maxItems = (int) $this->option('max');
        $isFull = (bool) $this->option('full');
        $sinceOpt = $this->option('since');

        $this->info("Source CPE API: {$apiUrl}");

        // Determine the sync time window (UTC) for incremental mode
        $nowUtc = now('UTC');
        $start = null;
        $end = null;
        $startIndex = 0;

        // Resume an interrupted sync, unless the user explicitly asked for something else
        $resume = json_decode((string) Parameter::getValue(self::PARAM_RESUME, ''), true);
        if ($this->option('restart') || $isFull || $sinceOpt) {
            $resume = null;
        }

        if (is_array($resume) && isset($resume['startIndex'], $resume['runAt'])) {
            $isFull = (bool) ($resume['full'] ?? false);
            $start = ! empty($resume['start']) ? Carbon::parse($resume['start'])->utc() : null;
            $end = ! empty($resume['end']) ? Carbon::parse($resume['end'])->utc() : null;
            $nowUtc = Carbon::parse($resume['runAt'])->utc();
            $startIndex = (int) $resume['startIndex'];
            $this->info("Resuming interrupted sync at index {$startIndex} (use --restart to start over).");
            if (! $isFull && (! $start || ! $end)) {
                $this->error('Invalid resume checkpoint, use --restart.');

                return self::FAILURE;
            }
        } elseif (! $isFull) {
            if ($sinceOpt) {
                try {
                    $start = Carbon::parse($sinceOpt)->utc();
                } catch (\Throwable $e) {
                    $this->error("Invalid --since option: {$sinceOpt}");

                    return self::FAILURE;
                }
            } else {
                // Persistent read from the parameters table (survives container restarts)
                $lastRun = Parameter::getValue(self::PARAM_LAST_RUN);
                if ($lastRun) {
                    $this->info("Last successful run: {$lastRun}");
                    $start = Carbon::parse($lastRun)->utc();
                } else {
                    $this->info('No previous run found, full sync now.');
                    $isFull = true;
                }
            }
            $end = $isFull ? null : $nowUtc;
        }

        // Message
        if (! $isFull) {
            $this->line("Incremental window: {$start->toIso8601String()} -> {$end->toIso8601String()}");
        } else {
            $this->warn('FULL resync mode: ignoring lastMod*, fetching the entire dictionary.');
        }

        // NVD rate limits: 5 requests / 30 s without API key, 50 / 30 s with one.
        $delayOpt = $this->option('delay');
        $delay = $delayOpt !== null && $delayOpt !== ''
            ? max((float) $delayOpt, 0.0)
            : ($apiKey ? 1.0 : 6.0);
        if (! $apiKey) {
            $this->line('No NVD API key configured: throttling to one request every '.$delay.' s.');
        }

        // HTTP client with retry & timeout
        $client = Http::timeout(120)
            ->retry($this->retryDelays, 0, fn (\Throwable $e) => $this->isTransient($e))
            ->withHeaders([
                'User-Agent' => 'mercator',
            ]);

        if ($apiKey) {
            $client = $client->withHeaders(['apiKey' => $apiKey]);
        }

        $processed = 0;
        $createdVendors = 0;
        $createdProducts = 0;
        $createdVersions = 0;
        $requestCount = 0;
        $total = 0;

        // Pagination loop
        while (true) {
            $params = [
                'resultsPerPage' => $perPage,
                'startIndex' => $startIndex,
            ];

            if (! $isFull) {
                $params['lastModStartDate'] = $start->format('Y-m-d\TH:i:s.v\Z');
                $params['lastModEndDate'] = $end->format('Y-m-d\TH:i:s.v\Z');
            }

            if ($requestCount++ > 0 && $delay > 0) {
                Sleep::usleep((int) ($delay * 1_000_000));
            }

            try {
                // ->throw() raises a RequestException on 4xx/5xx (after the retries are exhausted)
                $response = $client->get($apiUrl, $params)->throw();
            } catch (RequestException|ConnectionException $e) {
                $this->saveCheckpoint($isFull, $start, $end, $nowUtc, $startIndex);
                $status = $e instanceof RequestException ? 'HTTP '.$e->response->status() : 'connection error';
                $this->error("NVD API unavailable ({$status}) at index {$startIndex}/{$total}.");
                $this->line('Progress has been saved: run the command again to resume from this point.');
                if (! $apiKey) {
                    $this->line('Tip: set NVD_API_KEY in .env to get higher NVD rate limits.');
                }

                return self::FAILURE;
            }
            $json = $response->json();

            $total = (int) ($json['totalResults'] ?? 0);
            $items = $json['products'] ?? [];

            if ($startIndex === 0) {
                $this->info("totalResults: {$total}");
            }

            if (empty($items)) {
                if ($startIndex === 0) {
                    $this->warn('No items returned for these parameters.');
                }
                break;
            }

            // Wrap in transaction for batch insert performance
            DB::beginTransaction();
            try {
                foreach ($items as $item) {
                    $uri = $item['cpe']['cpeName'] ?? $item['cpeName'] ?? $item['cpe23Uri'] ?? null;
                    if (! $uri) {
                        continue;
                    }

                    $parsed = $this->parseCpe23($uri);
                    if (! $parsed) {
                        continue;
                    }

                    $part = $parsed['part'];
                    $vendorN = $parsed['vendor'];
                    $productN = $parsed['product'];
                    $versionN = $parsed['version'];

                    // 1) Vendor
                    $vendorId = DB::table('cpe_vendors')
                        ->where('part', $part)
                        ->where('name', $vendorN)
                        ->value('id');

                    if (! $vendorId) {
                        $vendorId = DB::table('cpe_vendors')->insertGetId([
                            'part' => $part,
                            'name' => $vendorN,
                        ]);
                        $createdVendors++;
                    }

                    // 2) Product
                    $productId = DB::table('cpe_products')
                        ->where('cpe_vendor_id', $vendorId)
                        ->where('name', $productN)
                        ->value('id');

                    if (! $productId) {
                        $productId = DB::table('cpe_products')->insertGetId([
                            'cpe_vendor_id' => $vendorId,
                            'name' => $productN,
                        ]);
                        $createdProducts++;
                    }

                    // 3) Version
                    if ($versionN) {
                        $versionId = DB::table('cpe_versions')
                            ->where('cpe_product_id', $productId)
                            ->where('name', $versionN)
                            ->value('id');

                        if (! $versionId) {
                            DB::table('cpe_versions')->insert([
                                'cpe_product_id' => $productId,
                                'name' => $versionN,
                            ]);
                            $createdVersions++;
                        }
                    }

                    $processed++;
                    if ($maxItems > 0 && $processed >= $maxItems) {
                        DB::commit();

                        break 2; // exit both loops
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Insert error: '.$e->getMessage());

                return self::FAILURE;
            }

            $startIndex += count($items);
            $this->info("Progress: {$startIndex}/{$total}");
            $this->saveCheckpoint($isFull, $start, $end, $nowUtc, $startIndex);

            if ($startIndex >= $total) {
                break;
            }
        }

        // Persist the last run timestamp to the database (survives container restarts)
        Parameter::setValue(self::PARAM_LAST_RUN, $nowUtc->toIso8601String());
        Parameter::where('name', self::PARAM_RESUME)->delete();

        if ($isFull) {
            $this->info("Full sync completed. Next run will be incremental from {$nowUtc->toIso8601String()}");
        } else {
            $this->info("Incremental sync completed. Next run will start from {$end->toIso8601String()}");
        }

        $this->info("Done. Processed: {$processed}. New => vendors: {$createdVendors}, products: {$createdProducts}, versions: {$createdVersions}");

        return self::SUCCESS;
    }

    /**
     * Whether an HTTP failure is worth retrying (rate limit, server overload, network).
     */
    protected function isTransient(\Throwable $e): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }
        if ($e instanceof RequestException) {
            $status = $e->response->status();

            return $status === 403 || $status === 429 || $status >= 500;
        }

        return false;
    }

    /**
     * Save the current position so that an interrupted sync can be resumed.
     */
    protected function saveCheckpoint(bool $isFull, ?Carbon $start, ?Carbon $end, Carbon $runAt, int $startIndex): void
    {
        Parameter::setValue(self::PARAM_RESUME, json_encode([
            'full' => $isFull,
            'start' => $start?->toIso8601String(),
            'end' => $end?->toIso8601String(),
            'runAt' => $runAt->toIso8601String(),
            'startIndex' => $startIndex,
        ]));
    }

    /**
     * Parse a CPE 2.3 URI, handling escaped characters (\:).
     * Returns [part, vendor, product, version] or null if invalid.
     */
    protected function parseCpe23(string $uri): ?array
    {
        if (! Str::startsWith($uri, 'cpe:2.3:')) {
            return null;
        }

        // Split ":" but respect "\:"
        $segments = [];
        $current = '';
        $len = strlen($uri);
        for ($i = 0; $i < $len; $i++) {
            $ch = $uri[$i];
            if ($ch === '\\' && $i + 1 < $len) {
                $current .= $uri[$i + 1];
                $i++;

                continue;
            }
            if ($ch === ':') {
                $segments[] = $current;
                $current = '';

                continue;
            }
            $current .= $ch;
        }
        $segments[] = $current;

        if (count($segments) < 6) {
            return null;
        }

        [$cpe, $ver, $part, $vendor, $product, $version] = array_pad($segments, 6, null);
        if ($cpe !== 'cpe' || $ver !== '2.3') {
            return null;
        }

        $normalize = function ($s) {
            if ($s === null) {
                return null;
            }
            if ($s === '*' || $s === '-') {
                return null;
            } // ANY/NA -> null

            return $s;
        };

        return [
            'part' => $normalize($part) ?: 'a', // default 'a' (applications)
            'vendor' => $normalize($vendor) ?: 'unknown',
            'product' => $normalize($product) ?: 'unknown',
            'version' => $normalize($version),
        ];
    }
}
