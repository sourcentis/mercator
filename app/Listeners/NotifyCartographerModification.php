<?php

namespace App\Listeners;

use App\Events\CartographerModifiedObject;
use App\Models\Cartographer;
use App\Services\MailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

// Note: requires QUEUE_CONNECTION != sync in production for async delivery.
class NotifyCartographerModification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(private readonly MailerService $mailer) {}

    private const DEFAULT_SUBJECT = '[Mercator] :object :name modifié par :user';

    private const DEFAULT_BODY = '<p>L\'objet <strong>:object</strong> <a href=":object_url">:name</a> (id&nbsp;:id) '
        .'a été modifié par <strong>:user</strong> (:email) le :date.</p>'
        .'<p>Champs modifiés&nbsp;: <strong>:fields</strong></p>'
        .'<p><a href=":object_history_url">Voir l\'historique des modifications</a></p>';

    /**
     * Collect the emails of every cartographer assigned to the modified object
     * (direct user_id or via role), excluding the user who made the change.
     *
     * @return string[]
     */
    private function resolveRecipients(string $modelClass, mixed $objectId, string $excludeEmail): array
    {
        $emails = [];

        $cartographers = Cartographer::where('cartographiable_type', $modelClass)
            ->where('cartographiable_id', $objectId)
            ->with(['user', 'role.users'])
            ->get();

        foreach ($cartographers as $cartographer) {
            if ($cartographer->user?->email && $cartographer->user->email !== $excludeEmail) {
                $emails[] = $cartographer->user->email;
            }

            if ($cartographer->role) {
                foreach ($cartographer->role->users as $user) {
                    if ($user->email && $user->email !== $excludeEmail) {
                        $emails[] = $user->email;
                    }
                }
            }
        }

        return array_values(array_unique($emails));
    }

    public function handle(CartographerModifiedObject $event): void
    {
        if (! config('mercator.cartography.notifier_enabled', false)) {
            return;
        }

        $name = $event->object->getAttribute('name');
        $objectKey = $event->object->getKey();
        $class = get_class($event->object);

        $routesMap = Cartographer::cartographiableRoutesMap();
        $showRoute = $routesMap[$class] ?? null;

        $objectUrl = $showRoute
            ? route($showRoute, $objectKey)
            : '#';

        $historyUrl = route('admin.audit-logs.history', [
            'type' => $class,
            'id' => $objectKey,
        ]);

        // Longer keys before shorter prefixes to avoid partial substitution
        $placeholders = [
            ':object_history_url' => $historyUrl,
            ':object_url' => $objectUrl,
            ':user' => (string) $event->user->name,
            ':email' => (string) $event->user->email,
            ':object' => $event->objectType,
            ':id' => (string) $objectKey,
            ':name' => $name !== null ? (string) $name : '#'.$objectKey,
            ':fields' => implode(', ', array_keys($event->dirty)),
            ':date' => now()->format('d/m/Y'),
            ':timestamp' => now()->format('d/m/Y H:i'),
        ];

        $subjectTemplate = (string) config('mercator.cartography.notifier_subject', '') ?: self::DEFAULT_SUBJECT;
        $bodyTemplate = (string) config('mercator.cartography.notifier_body', '') ?: self::DEFAULT_BODY;

        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subjectTemplate);
        $body = str_replace(array_keys($placeholders), array_values($placeholders), $bodyTemplate);

        $from = (string) config('mercator.cartography.notifier_from', '');
        $bcc = (string) config('mercator.cartography.notifier_to', '');

        $recipients = $this->resolveRecipients($class, $objectKey, $event->user->email);

        if ($recipients === [] && $bcc === '') {
            Log::info("[cartographer] no other recipients for {$event->objectType}#{$objectKey}, skipping");

            return;
        }

        $to = $recipients !== [] ? implode(',', $recipients) : $bcc;
        $bccFinal = $recipients !== [] ? $bcc : '';

        $this->mailer->send($from, $to, $subject, $body, $bccFinal);

        Log::info("[cartographer] notification sent for {$event->user->email} modified {$event->objectType}#{$objectKey} to: {$to}");
    }
}
