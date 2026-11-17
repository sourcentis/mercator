<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\ApplicationModule;
use App\Services\MailerService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Random\RandomException;
use Throwable;

class CVESearch extends Command
{
    protected $signature = 'mercator:cve-search {--nowait : Whether the job should not wait before start}';

    protected $description = 'Search for CVE';

    protected string $provider;

    protected string $appVersion;

    /** @var int days */
    protected int $checkFrequency;

    /**
     * @throws RandomException
     */
    public function handle(): int
    {
        Log::info('CVESearch - Start', ['day' => Carbon::now()->day]);

        // Charger & normaliser la config une seule fois
        $this->provider = $this->normalizeProvider(config('mercator.cve.provider'));
        $this->checkFrequency = (int) config('mercator.cve.check-frequency', 1);
        $this->appVersion = trim(file_get_contents(base_path('version.txt')));

        // Respecter le provider
        if (! $this->option('nowait')) {
            $seconds = random_int(1, 600); // Be nice with CIRCL
            Log::debug('CVESearch - niceness sleep', ['seconds' => $seconds]);
            sleep($seconds);
        }

        // Ping provider / dbInfo
        $dbInfo = $this->fetchJson('/api/dbInfo');
        if ($dbInfo === null) {
            Log::warning('CVESearch - provider unreachable', ['provider' => $this->provider]);

            return self::FAILURE;
        }

        $nvdUpdate = data_get($dbInfo, 'last_updates.nvd');
        $dbSize = data_get($dbInfo, 'db_sizes.total');
        Log::info('CVESearch - provider dbInfo', ['nvd_last_update' => $nvdUpdate, 'db_total' => $dbSize]);

        // Fenêtre temporelle minimale
        $minTimestamp = Carbon::now()->subDays($this->checkFrequency)->toDateString();
        Log::debug('CVESearch - min timestamp', ['published_after' => $minTimestamp]);

        // Récupérer les Applications & ApplicationModules & noms à matcher (lowercase)
        $namesApplications = Application::query()
            ->orderBy('name')
            ->pluck('name')
            ->filter();

        $namesModules = ApplicationModule::query()
            ->orderBy('name')
            ->pluck('name')
            ->filter();

        $names = $namesApplications
            ->merge($namesModules)
            ->map(fn ($n) => Str::of($n)->lower()->toString())
            ->unique()  // Éviter les doublons
            ->sort()    // Re-trier après le merge
            ->values(); // Réindexer

        if ($names->isEmpty()) {
            Log::info('CVESearch - no application names found, aborting.');

            return self::SUCCESS;
        }

        // Récupération des derniers CVE
        $cves = $this->fetchJson('/api/last');
        if (! is_array($cves)) {
            Log::warning('CVESearch - invalid /api/last payload');

            return self::FAILURE;
        }

        Log::info('CVESearch - CVE fetched', ['count' => count($cves), 'provider' => $this->provider]);

        // Analyze & filtrage
        $lines = [];
        $cveCount = 0;

        foreach ($cves as $cve) {
            // Formats pris en charge ci-dessous, on garde la logique existante mais en "safe access"
            if (data_get($cve, 'dataType') === 'CVE_RECORD') {
                $published = substr((string) data_get($cve, 'cveMetadata.datePublished', ''), 0, 10);
                if ($published >= $minTimestamp) {
                    $desc = Str::of((string) data_get($cve, 'containers.cna.descriptions.0.value', ''))->lower()->toString();
                    $hitName = $this->firstContained($desc, $names);
                    if ($hitName !== null) {
                        $cveId = (string) data_get($cve, 'cveMetadata.cveId', 'CVE-?');
                        $url = rtrim($this->provider, '/').'/vuln/'.$cveId;
                        $lines[] = sprintf(
                            '<a href="%s">%s</a> - <b>%s</b> : <b>%s</b> - %s',
                            e($url),
                            e($cveId),
                            e($hitName),
                            e($cveId),
                            e((string) $desc)
                        );
                        $cveCount++;
                    }
                }

                continue;
            }

            if (isset($cve->details, $cve->published)) {
                $published = substr((string) $cve->published, 0, 10);
                if ($published >= $minTimestamp) {
                    $desc = Str::of((string) $cve->details)->lower()->toString();
                    $hitName = $this->firstContained($desc, $names);
                    if ($hitName !== null) {
                        $alias = Arr::first((array) data_get($cve, 'aliases', []), default: 'CVE-?');
                        $lines[] = sprintf(
                            '<b>%s</b> : <b>%s</b> - %s',
                            e($hitName),
                            e((string) $alias),
                            e((string) $cve->details)
                        );
                        $cveCount++;
                    }
                }

                continue;
            }

            if (data_get($cve, 'document.category') === 'csaf_security_advisory') {
                $published = substr((string) data_get($cve, 'document.tracking.current_release_date', ''), 0, 10);
                if ($published >= $minTimestamp) {
                    $title = (string) data_get($cve, 'document.title', '');
                    $notes0 = (string) data_get($cve, 'document.notes.0.text', '');
                    $hitName = $this->firstContained(Str::lower($title), $names);
                    if ($hitName !== null) {
                        $lines[] = sprintf('<b>%s</b> : <b>%s</b> - %s', e($hitName), e($title), e($notes0));
                        $cveCount++;
                    }
                }

                continue;
            }

            if (isset($cve->descriptions, $cve->id) && str_starts_with((string) $cve->id, 'CVE')) {
                foreach ((array) $cve->descriptions as $d) {
                    $val = (string) data_get($d, 'value', '');
                    $hitName = $this->firstContained(Str::lower($val), $names);
                    if ($hitName !== null) {
                        $lines[] = sprintf('<b>%s</b> : <b>%s</b> - %s', e($hitName), e((string) $cve->id), e($val));
                        $cveCount++;
                        break; // une occurrence suffit
                    }
                }

                continue;
            }

            // Autres formats : log en debug (éviter de saturer les logs)
            Log::debug('CVESearch - Unknown CVE format encountered');
        }

        if ($cveCount <= 0) {
            Log::info('CVESearch - No CVE matched.');
            Log::info('CVESearch - DONE.');

            return self::SUCCESS;
        }

        $message = '<html><body>'.implode('<br>', $lines).'</body></html>';
        Log::info('CVESearch - Matches found', ['count' => $cveCount]);

        $this->sendEmail($message);

        Log::info('CVESearch - DONE.');

        return self::SUCCESS;
    }

    /**
     * Normalise l’URL provider et corrige les anciens endpoints connus.
     */
    protected function normalizeProvider(?string $raw): string
    {
        $url = (string) $raw;

        // Corrige la coquille historique "https//..."
        if (Str::startsWith($url, 'https//')) {
            $url = Str::replaceFirst('https//', 'https://', $url);
        }

        // Migration des anciens domains -> nouveau
        $legacy = [
            'https://cvepremium.circl.lu',
            'https://cve.circl.lu',
            'http://cve.circl.lu',
        ];
        if (in_array(rtrim($url, '/'), $legacy, true)) {
            $url = 'https://vulnerability.circl.lu';
            Log::notice('CVESearch - provider normalized to vulnerability.circl.lu');
        }

        return rtrim($url, '/');
    }

    /**
     * Client HTTP préconfiguré avec headers, timeout, retry & base URL.
     */
    protected function http(): PendingRequest|Factory
    {
        return Http::baseUrl($this->provider)
            ->withHeaders([
                'User-Agent' => "Mercator/{$this->appVersion}",
                'Accept' => 'application/json',
                // Optionnel: préciser ton contact pour faciliter le support côté provider
                // 'From'      => config('mail.from.address'),
            ])
            ->timeout(15)                 // 15s
            ->retry(3, 1000)              // 3 tentatives, backoff 1s
            ->throw();                    // convertit non-2xx en exceptions
    }

    /**
     * Récupère du JSON depuis le provider, ou null en cas d’erreur.
     */
    protected function fetchJson(string $path): ?array
    {
        try {
            $resp = $this->http()->get($path);

            // Vérifie content-type si utile : $resp->header('Content-Type')
            $json = $resp->json();
            if (! is_array($json)) {
                Log::warning('CVESearch - Non-array JSON payload', ['path' => $path]);

                return null;
            }

            return $json;
        } catch (Throwable $e) {
            Log::error('CVESearch - HTTP error', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Retourne le premier nom d’application trouvé en sous-chaîne (case-insensitive),
     * ou null si aucun.
     */
    protected function firstContained(string $haystackLower, $names)
    {
        foreach ($names as $nameLower) {
            if ($nameLower !== '' && str_contains($haystackLower, $nameLower)) {
                return $nameLower;
            }
        }

        return null;
    }

    /**
     * Envoi email (PHPMailer conservé pour compatibilité).
     */
    protected function sendEmail(string $html): void
    {
        $mail = new PHPMailer(true);

        try {
            MailerService::configure($mail);

            $from = config('mercator.cve.mail-from');
            if ($from) {
                $mail->setFrom($from);
            }

            $to = config('mercator.cve.mail-to', '');
            foreach (array_filter(array_map('trim', explode(',', $to))) as $email) {
                $mail->addAddress($email);
            }

            $mail->isHTML(true);
            $mail->Subject = config('mercator.cve.mail-subject', 'Mercator - CVE matches');
            $mail->Body = $html;

            // DKIM (optionnel)
            $mail->DKIM_domain = config('mail.dkim.domain'); // env('MAIL_DKIM_DOMAIN');
            $mail->DKIM_private = config('mail.dkim.private'); //  env('MAIL_DKIM_PRIVATE');
            $mail->DKIM_selector = config('mail.dkim.selector'); // env('MAIL_DKIM_SELECTOR');
            $mail->DKIM_passphrase = config('mail.dkim.passphrase'); // env('MAIL_DKIM_PASSPHRASE');
            $mail->DKIM_identity = $mail->From;

            $mail->send();
            Log::info('CVESearch - Mail sent');
        } catch (Exception $e) {
            Log::error('CVESearch - Mailer error', ['error' => $mail->ErrorInfo ?: $e->getMessage()]);
        }
    }
}
