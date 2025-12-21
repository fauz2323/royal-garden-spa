<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

final class FirebaseServices
{
    protected Messaging $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('firebase.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => $data,
        ];

        return $this->messaging->send($message);
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = [])
    {
        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => $data,
        ];

        return $this->messaging->send($message);
    }
}
