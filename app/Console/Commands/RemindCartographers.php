<?php

namespace App\Console\Commands;

use App\Models\Cartographer;
use App\Models\User;
use App\Services\MailerService;
use App\Support\MercatorSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RemindCartographers extends Command
{
    protected $signature = 'mercator:remind-cartographers {--force : Send reminders regardless of the last-sent delay}';

    protected $description = 'Send reminder emails to cartographers with outdated objects';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        Log::info('[mercator:remind-cartographers] Started');

        if (! config('mercator.cartography.reminders_enabled', false)) {
            Log::info('[mercator:remind-cartographers] Reminders disabled — skipping');
            $this->info('Reminders disabled.');

            return 0;
        }

        $everyDays = (int) config('mercator.cartography.reminder_every_days', 30);
        $lastSent = config('mercator.cartography.reminder_last_sent');

        if (! $this->option('force') && $lastSent !== null) {
            $nextAllowed = Carbon::parse($lastSent)->addDays($everyDays);
            if ($nextAllowed->greaterThan(Carbon::today())) {
                Log::info('[mercator:remind-cartographers] Too soon since last send ('.$lastSent.') — skipping');
                $this->info('Too soon since last send.');

                return 0;
            }
        }

        $months = (int) config('mercator.cartography.reminder_months', 6);
        $threshold = Carbon::now()->subMonths($months);
        $from = (string) config('mercator.cartography.reminder_from', 'mercator@localhost');
        $bcc = (string) config('mercator.cartography.reminder_to', '');
        $subject = (string) config('mercator.cartography.reminder_subject', '[Mercator] Rappel');
        $body = (string) config('mercator.cartography.reminder_body', '');

        /** @var array<int, array{user: User, objects: array<string, array{class: class-string<Model>, object: Model}>}> $byUser */
        $byUser = [];

        foreach (array_keys(Cartographer::cartographiableModelsList()) as $modelClass) {
            $outdated = $modelClass::where('updated_at', '<', $threshold)->get();

            if ($outdated->isEmpty()) {
                continue;
            }

            $userIds = Cartographer::query()->where('cartographiable_type', $modelClass)
                ->whereIn('cartographiable_id', $outdated->pluck('id'))
                ->whereNotNull('user_id')
                ->pluck('user_id', 'cartographiable_id');

            foreach ($userIds as $objectId => $userId) {
                $user = User::find($userId);

                if (! $user || ! $user->email) {
                    continue;
                }

                $object = $outdated->find($objectId);

                if (! $object) {
                    continue;
                }

                if (! isset($byUser[$userId])) {
                    $byUser[$userId] = ['user' => $user, 'objects' => []];
                }

                // Deduplicate: same object may appear via multiple Cartographer rows.
                $key = $modelClass.'#'.$object->getKey();

                if (! isset($byUser[$userId]['objects'][$key])) {
                    $byUser[$userId]['objects'][$key] = [
                        'class' => $modelClass,
                        'object' => $object,
                    ];
                }
            }
        }

        $sent = 0;

        foreach ($byUser as ['user' => $user, 'objects' => $objects]) {
            $objects = array_values($objects);
            $count = count($objects);
            $name = $user->name ?? $user->login ?? $user->email;
            $list = $this->buildHtmlTable($objects);
            $appUrl = (string) config('app.url', '');

            $mailBody = str_replace(
                [':name', ':user', ':count', ':list', ':months', ':mercator_url'],
                [(string) $name, (string) $name, (string) $count, $list, (string) $months, $appUrl],
                $body,
            );

            $this->getLaravel()->make(MailerService::class)->send($from, $user->email, $subject, $mailBody, $bcc);

            Log::info("[mercator:remind-cartographers] Reminder sent to {$user->email} ({$count} objects)");
            $sent++;
        }

        $today = Carbon::now()->format('Y-m-d H:i');
        $this->persistLastSent($today);

        Log::info("[mercator:remind-cartographers] Done — {$sent} reminder(s) sent");
        $this->info("{$sent} reminder(s) sent.");

        return 0;
    }

    /**
     * Build an HTML table row for each outdated object.
     *
     * @param  array<int, array{class: class-string<Model>, object: Model}>  $objects
     */
    private function buildHtmlTable(array $objects): string
    {
        $routesMap = Cartographer::cartographiableRoutesMap();
        $rows = '';

        foreach ($objects as ['class' => $class, 'object' => $object]) {
            $type = class_basename($class);
            $rawName = $object->getAttribute('name') ?? $object->getAttribute('label') ?? $object->getAttribute('title');
            $displayName = $rawName !== null ? (string) $rawName : '#'.$object->getKey();
            $rawUpdated = $object->getAttribute('updated_at');
            $updatedAt = $rawUpdated !== null ? Carbon::parse($rawUpdated)->format('d-m-Y') : '—';

            $routeName = $routesMap[$class] ?? null;

            if ($routeName) {
                try {
                    $url = route($routeName, $object->getKey());
                    $nameCell = '<a href="'.htmlspecialchars($url, ENT_QUOTES).'">'
                        .htmlspecialchars($displayName, ENT_QUOTES).'</a>';
                } catch (\Exception) {
                    $nameCell = htmlspecialchars($displayName, ENT_QUOTES);
                }
            } else {
                $nameCell = htmlspecialchars($displayName, ENT_QUOTES);
            }

            $rows .= '<tr>'
                .'<td>'.htmlspecialchars($type, ENT_QUOTES).'</td>'
                .'<td>'.$nameCell.'</td>'
                .'<td>'.$updatedAt.'</td>'
                .'</tr>';
        }

        return '<table border="1" cellpadding="4" cellspacing="0">'
            .'<thead><tr><th>Type</th><th>Nom</th><th>Dernière MàJ</th></tr></thead>'
            .'<tbody>'.$rows.'</tbody>'
            .'</table>';
    }

    /**
     * Write reminder_last_sent into the stored mercator settings, using the
     * same mechanism as the configuration admin page.
     */
    private function persistLastSent(string $date): void
    {
        $cfg = config('mercator', []);
        $cfg['cartography']['reminder_last_sent'] = $date;

        MercatorSettings::save($cfg);
    }
}
