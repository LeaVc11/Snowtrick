<?php
namespace App\Mailer;

interface MailerInterface
{
    public function send(string $toEmail,string $toName,string $subject,string $content);
}