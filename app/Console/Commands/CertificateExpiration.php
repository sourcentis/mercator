<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Services\MailerService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class CertificateExpiration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mercator:certificate-expiration';

    /**
     * The console command description.
     */
    protected $description = 'Check expired certificates';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::debug('CertificateExpiration - Start.');

        Log::debug('CertificateExpiration - day '.Carbon::now()->day);

        if ($this->needCheck()) {
            // Check for old certificates
            Log::debug('CertificateExpiration - check');

            $certificates = Certificate::where('status', 0)
                ->where('end_validity', '<=', Carbon::now()
                    ->addDays(intval(config('mercator.cert.expire-delay')))->toDateString())
                ->orderBy('end_validity')
                ->get();

            Log::debug(
                $certificates->count().
                ' certificate(s) will expire within '.
                config('mercator.cert.expire-delay').
                ' days.'
            );

            // check
            $repeat_notification = config('mercator.cert.repeat-notification');
            if (intval($repeat_notification) === 0) {
                Log::debug('CertificateExpiration - remove cert aleady notified');
                foreach ($certificates as $key => $cert) {
                    if ($cert->last_notification === null) {
                        // never notified
                        Log::debug('CertificateExpiration - '.$cert->name.' never notified.');
                        $cert->last_notification = now();
                        $cert->save();
                    } elseif (
                        Carbon::createFromFormat(config('panel.date_format').' '.config('panel.time_format'), $cert->last_notification)
                            ->greaterThan(
                                now()->addDays(-intval(config('mercator.cert.expire-delay')))
                            )
                    ) {
                        Log::debug('CertificateExpiration - '.$cert->name.' already notified on '.$cert->last_notification);
                        $certificates->forget($key);
                    } else {
                        // must be notified
                        Log::debug('CertificateExpiration - '.$cert->name.' kept.');
                        $cert->last_notification = now();
                        $cert->save();
                    }
                }
            } else {
                // set last notification for all certificates
                foreach ($certificates as $cert) {
                    $cert->last_notification = now();
                    $cert->save();
                }
            }

            if ($certificates->count() > 0) {
                // send email alert
                $mail_from = config('mercator.cert.mail-from');
                $to_email = config('mercator.cert.mail-to');
                $subject = config('mercator.cert.mail-subject');
                $group = config('mercator.cert.group');

                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    MailerService::configure($mail);

                    // Recipients
                    $mail->setFrom($mail_from);
                    foreach (explode(',', $to_email) as $email) {
                        $mail->addAddress($email);
                    }

                    // Content
                    $mail->isHTML(true);                            // Set email format to HTML

                    // DKIM (optionnel)
                    $mail->DKIM_domain = config('mail.dkim.domain'); // env('MAIL_DKIM_DOMAIN');
                    $mail->DKIM_private = config('mail.dkim.private'); //  env('MAIL_DKIM_PRIVATE');
                    $mail->DKIM_selector = config('mail.dkim.selector'); // env('MAIL_DKIM_SELECTOR');
                    $mail->DKIM_passphrase = config('mail.dkim.passphrase'); // env('MAIL_DKIM_PASSPHRASE');
                    $mail->DKIM_identity = $mail->From;

                    if ($group === null || $group === '1') {
                        $mail->Subject = $subject;
                        $message = '<html><body>These certificates are about to exipre :<br><br>';
                        foreach ($certificates as $cert) {
                            $message .= $cert->end_validity.' - '.$cert->name.' - '.$cert->type.'<br>';
                        }
                        $message .= '</body></html>';

                        $mail->Body = $message;

                        // Send mail
                        $mail->send();

                        // Log
                        Log::debug("Mail sent to {$to_email}");
                    } else {
                        foreach ($certificates as $cert) {
                            $mail->Subject = $subject.' - '.$cert->end_validity.' - '.$cert->name;
                            $message = $cert->description;

                            // Send mail
                            $mail->Body = $message;

                            // Send mail
                            $mail->send();

                            // Log
                            Log::debug("Mail sent to {$to_email}");
                        }
                    }
                } catch (Exception $e) {
                    Log::error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            }
        } else {
            Log::debug('CertificateExpiration - no check');
        }

        Log::debug('CertificateExpiration - DONE.');

    }

    /**
     * return true if check is needed
     */
    private function needCheck(): bool
    {
        $check_frequency = config('mercator.cert.check-frequency');

        return // Daily
            ($check_frequency === '1') ||
            // Weekly
            (($check_frequency === '7') && (Carbon::now()->dayOfWeek === 1)) ||
            // Monthly
            (($check_frequency === '30') && (Carbon::now()->day === 1));
    }
}
