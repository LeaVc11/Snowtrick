<?php

namespace App\Mailer;

use Mailjet\Client;
use Mailjet\Resources;
use App\Mailer\MailerInterface;


class Mailer implements MailerInterface
{

    public function __construct(
        private readonly string $mailjetApiKey,
        private readonly string $mailjetApiKeySecret,
        private readonly string $mailjetApiVersion,
        private readonly string $emailFrom,
        private readonly string $emailName,
    )
    {

    }
    public function send(string $toEmail, string$toName, string$subject, string$content)
    {
        $mj = new Client($this->mailjetApiKey, $this->mailjetApiKeySecret, true, ['version' => $this->mailjetApiVersion]);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->emailFrom,
                        'Name' => $this->emailName,
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName,
                        ],
                    ],
                    'TemplateID' => 4275671,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ],
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);

        return $response->success();
    }
}