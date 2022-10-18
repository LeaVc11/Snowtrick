<?php

namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private string $api_key = '868db4bf670a70a99116f4aa36f22792';
    private string $api_key_secret = '71d6df0f477c35e1cb02248604a32599';

    public function __construct()
    {

    }
    public function send($to_email, $to_name, $subject,$content): void
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "snowtrick42@gmail.com",
                        'Name' => "snowtrick"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 4275671,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();

    }
}