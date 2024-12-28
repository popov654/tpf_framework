<?php

namespace Tpf\Service;

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public static function sendMail($recepientEmail, $recepientName = '', $subject = 'Message', $body = '')
    {
        global $TPF_CONFIG;

        if (!isset($TPF_CONFIG['email']) || !isset($TPF_CONFIG['email']['from'])) {
            throw new \Exception("Sender email is not configured");
        }

        $mailer = new PHPMailer(true);
        $mailer->CharSet = "UTF-8";
        $mailer->isHTML(true);

        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->SMTPDebug = 0;

        $useSSL = isset($TPF_CONFIG['mail']['secure']) && $TPF_CONFIG['mail']['secure'];
        $mailer->Host = ($useSSL ? "ssl://" : "") . $TPF_CONFIG['mail']['server'];
        $mailer->Port = $useSSL ? 465 : 25;
        $mailer->Username = $TPF_CONFIG['mail']['user'];
        $mailer->Password = $TPF_CONFIG['mail']['password'];

        $mailer->addAddress($recepientEmail, $recepientName);
        $mailer->setFrom($TPF_CONFIG['email']['from'], $TPF_CONFIG['email']['fromName'] ?? '');
        $mailer->Subject = $subject; //'Welcome to Tiny PHP Framework CMS!';
        $mailer->Body = $body;
    }
}