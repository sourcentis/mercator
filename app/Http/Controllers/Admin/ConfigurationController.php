<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\DatabaseCleanup;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\MonarcSyncItem;
use App\Models\Parameter;
use App\Models\Perimeter;
use App\Services\MailerService;
use App\Support\MercatorSettings;
use App\Support\MonarcSettings;
use App\Support\MonarcSyncState;
use App\Support\PerimeterSettings;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ConfigurationController extends Controller
{
    /** Durées de conservation proposées pour le nettoyage de la base (en mois). */
    public const CLEANUP_RETENTION_MONTHS = [1, 3, 6, 12, 24, 36, 60];

    /**
     * Page de configuration unifiée — charge tous les onglets.
     * Lit directement depuis le fichier pour garantir les valeurs à jour
     * après un redirect (le runtime config est chargé une seule fois au boot).
     */
    public function getParameters(Request $request)
    {
        abort_if(Gate::denies('configure'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $cfg = $this->readConfigFile();

        return view('admin.config.parameters', [
            // Général
            'security_need_auth' => $cfg['parameters']['security_need_auth'] ?? false,
            'application_documents' => $cfg['parameters']['application_documents'] ?? false,
            'max_nodes' => (int) ($cfg['parameters']['max_nodes'] ?? 500),
            // Certificats
            'cert_mail_from' => $cfg['cert']['mail-from'] ?? '',
            'cert_mail_to' => $cfg['cert']['mail-to'] ?? '',
            'cert_mail_subject' => $cfg['cert']['mail-subject'] ?? '',
            'cert_check_frequency' => $cfg['cert']['check-frequency'] ?? '0',
            'cert_expire_delay' => $cfg['cert']['expire-delay'] ?? '30',
            'cert_group' => (string) ($cfg['cert']['group'] ?? '0'),
            'cert_repeat_notification' => (string) ($cfg['cert']['repeat-notification'] ?? '0'),
            // CVE
            'cve_mail_from' => $cfg['cve']['mail-from'] ?? '',
            'cve_mail_to' => $cfg['cve']['mail-to'] ?? '',
            'cve_mail_subject' => $cfg['cve']['mail-subject'] ?? '',
            'cve_check_frequency' => $cfg['cve']['check-frequency'] ?? '0',
            'cve_provider' => $cfg['cve']['provider'] ?? '',
            'cpe_guesser' => $cfg['cpe']['guesser'] ?? '',
            // Notifications
            'notif_reminders_enabled' => $cfg['cartography']['reminders_enabled'] ?? false,
            'notif_reminder_from' => $cfg['cartography']['reminder_from'] ?? 'mercator@localhost',
            'notif_reminder_to' => $cfg['cartography']['reminder_to'] ?? 'mercator@localhost.com',
            'notif_reminder_subject' => $cfg['cartography']['reminder_subject'] ?? '[Mercator] Rappel de mise à jour',
            'notif_reminder_body' => $cfg['cartography']['reminder_body'] ?? '',
            'notif_reminder_months' => $cfg['cartography']['reminder_months'] ?? 6,
            'notif_reminder_every_days' => $cfg['cartography']['reminder_every_days'] ?? 30,
            'notif_reminder_last_sent' => $cfg['cartography']['reminder_last_sent'] ?? null,
            'notif_modification_enabled' => $cfg['cartography']['notifier_enabled'] ?? false,
            'notif_modification_from' => $cfg['cartography']['notifier_from'] ?? '',
            'notif_modification_copy_to' => $cfg['cartography']['notifier_to'] ?? '',
            'notif_modification_subject' => $cfg['cartography']['notifier_subject'] ?? '[Mercator] Un objet a été mis à jour',
            'notif_modification_body' => $cfg['cartography']['notifier_body'] ?? '',
            // Monarc
            'monarc_enabled' => MonarcSettings::enabled(),
            'monarc_url' => MonarcSettings::url() ?? '',
            'monarc_uid' => MonarcSettings::uid() ?? '',
            'monarc_has_password' => MonarcSettings::hasPassword(),
            'monarc_sync_state' => $monarcSyncState = MonarcSyncState::load(),
            'monarc_synced_items_count' => isset($monarcSyncState['anr_id'])
                ? MonarcSyncItem::query()->where('anr_id', $monarcSyncState['anr_id'])->count()
                : 0,
            // Nettoyage de la base
            'cleanup_audit_logs_enabled' => (bool) ($cfg['cleanup']['audit_logs_enabled'] ?? false),
            'cleanup_audit_logs_retention' => (int) ($cfg['cleanup']['audit_logs_retention'] ?? 12),
            'cleanup_deleted_objects_enabled' => (bool) ($cfg['cleanup']['deleted_objects_enabled'] ?? false),
            'cleanup_deleted_objects_retention' => (int) ($cfg['cleanup']['deleted_objects_retention'] ?? 3),
            'cleanup_retention_options' => self::CLEANUP_RETENTION_MONTHS,
            'cleanup_last_run' => json_decode((string) Parameter::getValue(DatabaseCleanup::LAST_RUN_PARAMETER), true),
            // Documents
            'count' => Document::query()->count(),
            'sum' => Document::query()->sum('size'),
            // Périmètres
            'perimeters' => Perimeter::query()->withCount('roles')->orderBy('id')->get(),
            'perimeters_enabled' => PerimeterSettings::isEnabled(),
            // Set the active tab
            'active_tab' => $request->query('tab', 'general'),
            // Last CPE Sync
            'last_cpe_sync' => Parameter::getValue('cpe_sync.last_run'),
        ]);
    }

    /**
     * Point d'entrée unique pour toutes les sauvegardes.
     * L'onglet actif est transmis via le champ caché `active_tab`.
     * Le redirect utilise un fragment d'URL (#tab-xxx) pour restaurer l'onglet
     * côté JS — plus fiable que le flash session.
     */
    public function saveConfig(Request $request)
    {
        abort_if(Gate::denies('configure'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tab = $request->input('active_tab', 'general');
        $action = $request->input('action', 'save');

        [$msg, $ok] = match ($tab) {
            'cert' => $this->handleCert($action, $request),
            'cve' => $this->handleCve($action, $request),
            'reminders' => $this->handleReminders($action, $request),
            'notifications' => $this->handleModification($action, $request),
            'monarc' => $this->handleMonarc($request),
            'cleanup' => $this->handleCleanup($request),
            default => $this->handleGeneral($request),
        };

        // Fragment #tab-xxx : repris par location.hash dans le JS de la blade.
        return redirect(route('admin.config.parameters').'?tab='.$tab)
            ->with($ok ? 'success' : 'error', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Handlers par onglet
    // ─────────────────────────────────────────────────────────────────────────

    private function handleGeneral(Request $request): array
    {
        $validated = $request->validate([
            'max_nodes' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $cfg = $this->readConfigFile();
        $cfg['parameters']['security_need_auth'] = $request->boolean('security_need_auth');
        $cfg['parameters']['application_documents'] = $request->boolean('application_documents');
        $cfg['parameters']['max_nodes'] = (int) $validated['max_nodes'];
        $this->writeConfigFile($cfg);

        return [trans('cruds.configuration.saved'), true];
    }

    private function handleCert(string $action, Request $request): array
    {
        if ($action === 'save') {
            $cfg = $this->readConfigFile();
            $cfg['cert']['mail-from'] = $request->input('mail_from');
            $cfg['cert']['mail-to'] = $request->input('mail_to');
            $cfg['cert']['mail-subject'] = $request->input('mail_subject');
            $cfg['cert']['check-frequency'] = $request->input('check_frequency');
            $cfg['cert']['expire-delay'] = $request->input('expire_delay');
            $cfg['cert']['group'] = $request->input('group');
            $cfg['cert']['repeat-notification'] = $request->input('repeat-notification');
            $this->writeConfigFile($cfg);

            return [trans('cruds.configuration.saved'), true];
        }

        return $this->sendTestMail(
            $request->input('mail_from'),
            $request->input('mail_to'),
            $request->input('mail_subject'),
        );
    }

    private function handleCve(string $action, Request $request): array
    {
        $providerPinnedIp = null;

        try {
            // Check provider URL — resolve once, validate all records, store pinned IP
            $provider = $request->input('provider');
            if (! empty($provider)) {
                $provider = $this->validateProviderUrl($provider);
                $providerPinnedIp = $this->resolveAndValidateHost($provider);
            }

            // Check CPE-Guesser URL (validation only; test_guesser re-résout depuis la config sauvée)
            $guesser = $request->input('cpe_guesser');
            if (! empty($guesser)) {
                $guesser = $this->validateProviderUrl($guesser);
                $this->resolveAndValidateHost($guesser);
            }
        } catch (\InvalidArgumentException $e) {
            return [$e->getMessage(), false];
        }

        // Handle action
        if ($action === 'save') {
            $cfg = $this->readConfigFile();
            $cfg['cve']['mail-from'] = $request->input('mail_from');
            $cfg['cve']['mail-to'] = $request->input('mail_to');
            $cfg['cve']['mail-subject'] = $request->input('mail_subject');
            $cfg['cve']['check-frequency'] = $request->input('check_frequency');
            $cfg['cve']['provider'] = $request->input('provider');
            $cfg['cpe']['guesser'] = $request->input('cpe_guesser');
            $this->writeConfigFile($cfg);

            return [trans('cruds.configuration.saved'), true];
        }

        if ($action === 'test_provider') {
            return $this->testProvider($request->input('provider'), $providerPinnedIp ?? '');
        }

        if ($action === 'test_guesser') {
            $cfg = $this->readConfigFile();
            $guesserUrl = $cfg['cpe']['guesser'] ?? '';
            try {
                $this->validateProviderUrl($guesserUrl);
                $guesserPinnedIp = $this->resolveAndValidateHost($guesserUrl);
            } catch (\InvalidArgumentException $e) {
                return [$e->getMessage(), false];
            }

            return $this->testGuesser($guesserUrl, $guesserPinnedIp);
        }

        return $this->sendTestMail(
            $request->input('mail_from'),
            $request->input('mail_to'),
            $request->input('mail_subject'),
        );
    }

    private function handleReminders(string $action, Request $request): array
    {
        if ($action === 'test_reminder') {
            $from = $request->input('reminder_from');
            $to = $request->input('reminder_to') ?: $from;

            return $this->sendTestMail($from, $to, $request->input('reminder_subject'));
        }

        $cfg = $this->readConfigFile();

        $remindersEnabled = $request->boolean('reminders_enabled');
        $cfg['cartography']['reminders_enabled'] = $remindersEnabled;

        if ($remindersEnabled) {
            $cfg['cartography']['reminder_from'] = $request->input('reminder_from');
            $cfg['cartography']['reminder_to'] = $request->input('reminder_to');
            $cfg['cartography']['reminder_subject'] = $request->input('reminder_subject');
            $cfg['cartography']['reminder_body'] = $request->input('reminder_body');
            $cfg['cartography']['reminder_months'] = (int) $request->input('reminder_months', 6);
            $cfg['cartography']['reminder_every_days'] = (int) $request->input('reminder_every_days', 30);
        }

        $this->writeConfigFile($cfg);

        return [trans('cruds.configuration.saved'), true];
    }

    private function handleModification(string $action, Request $request): array
    {
        if ($action === 'test_modification') {
            $from = $request->input('modification_from');
            $to = $request->input('modification_copy_to') ?: $from;

            return $this->sendTestMail($from, $to, $request->input('modification_subject'));
        }

        $cfg = $this->readConfigFile();

        $modificationEnabled = $request->boolean('modification_enabled');
        $cfg['cartography']['notifier_enabled'] = $modificationEnabled;

        if ($modificationEnabled) {
            $cfg['cartography']['notifier_from'] = $request->input('modification_from');
            $cfg['cartography']['notifier_to'] = $request->input('modification_copy_to');
            $cfg['cartography']['notifier_subject'] = $request->input('modification_subject');
            $cfg['cartography']['notifier_body'] = $request->input('modification_body');
        }

        $this->writeConfigFile($cfg);

        return [trans('cruds.configuration.saved'), true];
    }

    private function handleCleanup(Request $request): array
    {
        // Une durée n'est pas transmise quand sa purge est désactivée (fieldset disabled) :
        // la valeur précédente est alors conservée.
        $validated = $request->validate([
            'audit_logs_retention' => ['nullable', 'integer', Rule::in(self::CLEANUP_RETENTION_MONTHS)],
            'deleted_objects_retention' => ['nullable', 'integer', Rule::in(self::CLEANUP_RETENTION_MONTHS)],
        ]);

        $cfg = $this->readConfigFile();
        $current = $cfg['cleanup'] ?? [];
        $cfg['cleanup'] = [
            'audit_logs_enabled' => $request->boolean('audit_logs_enabled'),
            'audit_logs_retention' => (int) ($validated['audit_logs_retention'] ?? $current['audit_logs_retention'] ?? 12),
            'deleted_objects_enabled' => $request->boolean('deleted_objects_enabled'),
            'deleted_objects_retention' => (int) ($validated['deleted_objects_retention'] ?? $current['deleted_objects_retention'] ?? 3),
        ];
        $this->writeConfigFile($cfg);

        return [trans('cruds.configuration.saved'), true];
    }

    private function handleMonarc(Request $request): array
    {
        $url = trim((string) $request->input('url'));

        if ($url !== '') {
            $validator = validator(['url' => $url], ['url' => 'url:http,https']);
            if ($validator->fails()) {
                return [$validator->errors()->first('url'), false];
            }
        }

        $cfg = [
            'enabled' => $request->boolean('enabled'),
            'url' => $url,
            'uid' => (string) $request->input('uid'),
            // Keep the existing (possibly encrypted) password unless a new one is submitted.
            'password' => (string) config('monarc.password'),
        ];

        $password = (string) $request->input('password');
        if ($password !== '') {
            $cfg['password'] = $password;
        }

        MonarcSettings::save($cfg);

        return [trans('cruds.configuration.saved'), true];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Lecture / écriture de la config (stockée dans la table `parameters`)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lit la config courante (défauts de config/mercator.php fusionnés avec
     * les valeurs surchargées en base, déjà appliquées au boot).
     */
    private function readConfigFile(): array
    {
        return config('mercator', []);
    }

    /**
     * Persiste le tableau de config dans la table `parameters`.
     */
    private function writeConfigFile(array $cfg): void
    {
        MercatorSettings::save($cfg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Utilitaires mail / provider
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{0: string, 1: bool} */
    private function sendTestMail(string $from, string $to, string $subject): array
    {
        $mail = new PHPMailer(true);
        try {
            // Mêmes réglages que les envois automatiques : une erreur TLS doit apparaître ici
            // plutôt qu'à l'envoi de minuit.
            MailerService::configure($mail);

            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_BASE64;
            $mail->setFrom($from);
            foreach (explode(',', $to) as $email) {
                $mail->addAddress(trim($email));
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = '<html><body><br>This is a test message !<br><br></body></html>';

            $mail->DKIM_domain = config('mail.dkim.domain');
            $mail->DKIM_private = config('mail.dkim.private');
            $mail->DKIM_selector = config('mail.dkim.selector');
            $mail->DKIM_passphrase = config('mail.dkim.passphrase');
            $mail->DKIM_identity = $mail->From;

            $mail->send();

            return ['Message has been sent', true];
        } catch (Exception $e) {
            // Message de l'exception plutôt que ErrorInfo : après un échec STARTTLS,
            // ErrorInfo se voit ajouter la réponse au QUIT, faite d'octets TLS binaires.
            $error = mb_scrub(trim($e->getMessage()), 'UTF-8');

            return ["Message could not be sent. Mailer Error: {$error}", false];
        }
    }

    /** @return array{0: string, 1: bool} */
    private function testProvider(string $provider, string $pinnedIp): array
    {
        $host = parse_url($provider, PHP_URL_HOST) ?? '';
        $port = parse_url($provider, PHP_URL_PORT)
            ?? (str_starts_with($provider, 'https') ? 443 : 80);

        $client = curl_init($provider.'/api/dbInfo');
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_TIMEOUT, 10);
        curl_setopt($client, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($client, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        // Épingler l'IP validée : curl ne re-résout pas, ce qui ferme le DNS rebinding et le bypass dual-stack.
        if ($pinnedIp !== '') {
            curl_setopt($client, CURLOPT_RESOLVE, ["{$host}:{$port}:{$pinnedIp}"]);
        }

        $response = curl_exec($client);
        curl_close($client);

        if ($response === false) {
            return ['Could not connect to provider', false];
        }
        $json = json_decode($response);
        if ($json === null) {
            return ['Could not connect to provider (invalid JSON)', false];
        }

        return [
            'Last NVD update: '.$json->last_updates->nvd
            .' — Total db size = '.$json->db_sizes->total,
            true,
        ];
    }

    /** @return array{0: string, 1: bool} */
    private function testGuesser(string $guesser, string $pinnedIp): array
    {
        if (empty($guesser)) {
            return ['CPE guesser URL is not configured', false];
        }

        $host = parse_url($guesser, PHP_URL_HOST) ?? '';
        $port = parse_url($guesser, PHP_URL_PORT)
            ?? (str_starts_with($guesser, 'https') ? 443 : 80);
        $url = rtrim($guesser, '/').'/search';
        $payload = json_encode(['query' => ['test']]);

        $client = curl_init($url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_TIMEOUT, 10);
        curl_setopt($client, CURLOPT_POST, true);
        curl_setopt($client, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($client, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.strlen($payload),
        ]);
        curl_setopt($client, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($client, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        // Épingler l'IP validée pour interdire toute re-résolution par curl.
        if ($pinnedIp !== '') {
            curl_setopt($client, CURLOPT_RESOLVE, ["{$host}:{$port}:{$pinnedIp}"]);
        }

        $response = curl_exec($client);
        $httpCode = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        if ($response === false) {
            return ['Could not connect to CPE guesser', false];
        }

        $json = json_decode($response, true);
        if ($json === null) {
            return ['CPE guesser returned invalid JSON', false];
        }

        return [
            'CPE guesser OK (HTTP '.$httpCode.') — '.count($json).' result(s) for "test"',
            true,
        ];
    }

    private function validateProviderUrl(string $url): string
    {
        if (preg_match('/[?#@]/', $url)) {
            throw new \InvalidArgumentException('Invalid provider URL.');
        }
        $parsed = parse_url($url);
        if (! $parsed || ! in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Invalid provider URL scheme.');
        }

        return $url;
    }

    /**
     * Résout tous les enregistrements A et AAAA de l'hôte, valide que chacun est
     * public, et retourne la première IP (IPv4 préférée) pour l'épingler dans curl.
     *
     * Contrairement à gethostbyname(), cette approche :
     *   - couvre IPv6 (bypass dual-stack),
     *   - valide TOUTES les adresses (pas seulement la première),
     *   - retourne l'IP qui sera effectivement utilisée, fermant ainsi le DNS rebinding.
     */
    private function resolveAndValidateHost(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        // Adresse IP littérale : validation directe, pas de résolution DNS
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new \InvalidArgumentException('Provider host resolves to a private or reserved address.');
            }

            return $host;
        }

        // Hostname : résoudre une seule fois tous les enregistrements A + AAAA
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        $ipv4s = array_column($records, 'ip');
        $ipv6s = array_column($records, 'ipv6');
        $allIps = array_merge($ipv4s, $ipv6s);

        if (empty($allIps)) {
            throw new \InvalidArgumentException('Provider host could not be resolved.');
        }

        foreach ($allIps as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new \InvalidArgumentException('Provider host resolves to a private or reserved address.');
            }
        }

        // Préférer IPv4 pour la compatibilité CURLOPT_RESOLVE
        return $ipv4s[0] ?? $ipv6s[0];
    }
}
