<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailerService
{
    /**
     * Apply the SMTP server settings shared by every mail sent by Mercator.
     *
     * STARTTLS is always used when the server offers it, and the server
     * certificate is always verified: the test mail of the configuration page
     * must fail exactly like the scheduled mails would.
     */
    public static function configure(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host = config('mail.mailers.smtp.host');
        $mail->Port = (int) config('mail.mailers.smtp.port');

        $username = config('mail.mailers.smtp.username');
        $mail->SMTPAuth = filled($username);
        $mail->Username = (string) $username;
        $mail->Password = (string) config('mail.mailers.smtp.password');

        // MAIL_SMTP_SECURE: 'tls' (mandatory STARTTLS), 'ssl' (implicit TLS) or empty (STARTTLS if offered)
        $mail->SMTPSecure = (string) config('mail.mailers.smtp.encryption');
        $mail->SMTPAutoTLS = true;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];
    }

    /**
     * Send an email via SMTP (PHPMailer).
     * $to and $bcc accept comma-separated address lists.
     */
    public function send(string $from, string $to, string $subject, string $body, string $bcc = ''): void
    {
        $mail = new PHPMailer(true);

        try {
            self::configure($mail);

            if ($from) {
                $mail->setFrom($from);
            }

            foreach (array_filter(array_map('trim', explode(',', $to))) as $address) {
                $mail->addAddress($address);
            }

            foreach (array_filter(array_map('trim', explode(',', $bcc))) as $address) {
                $mail->addBCC($address);
            }

            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Encoding = PHPMailer::ENCODING_BASE64;
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            Log::info('[mailer] Mail sent', ['to' => $to, 'subject' => $subject]);
        } catch (Exception $e) {
            Log::error('[mailer] Send error', ['to' => $to, 'subject' => $subject, 'error' => $e->getMessage()]);
        }
    }
}
