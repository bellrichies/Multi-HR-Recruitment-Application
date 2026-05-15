<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use PHPMailer\PHPMailer\PHPMailer;

class MailerService
{
    public function send(string $recipient, string $subject, string $body): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) config('services.mail.host');
        $mail->Port = (int) config('services.mail.port', 587);
        $mail->SMTPAuth = config('services.mail.username') !== null && config('services.mail.username') !== '';
        $mail->Username = (string) config('services.mail.username');
        $mail->Password = (string) config('services.mail.password');
        $mail->setFrom((string) config('services.mail.from_address'), (string) config('services.mail.from_name'));
        $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $body;
        $mail->send();
    }
}
