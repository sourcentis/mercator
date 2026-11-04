<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailerService
{
    /**
     * Send an email via SMTP (PHPMailer).
     * $to and $bcc accept comma-separated address lists.
     */
    public function send(string $from, string $to, string $subject, string $body, string $bcc = ''): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = config('mail.mailers.smtp.username') !== null;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption');
            $mail->SMTPAutoTLS = config('mail.mailers.smtp.auto_tls');
            $mail->Port = (int) config('mail.mailers.smtp.port');

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
            Log::error('[mailer] Send error', ['to' => $to, 'subject' => $subject, 'error' => $mail->ErrorInfo ?: $e->getMessage()]);
        }
    }
}
