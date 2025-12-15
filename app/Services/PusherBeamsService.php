<?php

namespace App\Services;

use Pusher\PushNotifications\PushNotifications;
use Illuminate\Support\Facades\Log;

class PusherBeamsService
{
    protected PushNotifications $beamsClient;

    public function __construct()
    {
        $instanceId = config('broadcasting.instance_id');
        $secretKey  = config('broadcasting.secret_key');

        if (!$instanceId || !$secretKey) {
            Log::error("Pusher Beams instance ID or secret key missing!");
            throw new \Exception("Pusher Beams configuration missing.");
        }

        $this->beamsClient = new PushNotifications([
            'instanceId' => $instanceId,
            'secretKey'  => $secretKey,
        ]);

        Log::info("Pusher Beams initialized");
    }

    /**
     * Envoie une notification à un utilisateur spécifique
     */
    public function publishToUser(string $userId, array $data)
    {

        Log::info("PusherBeams → publishToUsers()", [
            "userId" => "user-$userId",
            "payload" => [
                "title" => $data['title'] ?? null,
                "body"  => $data['body'] ?? null
            ]
        ]);
        return $this->beamsClient->publishToUsers(
            ["user-{$userId}"],
            [
                "web" => [
                    "notification" => [
                        "title" => $data['title'] ?? 'Notification',
                        "body"  => $data['body'] ?? '',
                    ]
                ],
                "apns" => [
                    "aps" => ["alert" => $data['body'] ?? '']
                ],
                "fcm" => [
                    "notification" => [
                        "title" => $data['title'] ?? 'Notification',
                        "body"  => $data['body'] ?? '',
                    ]
                ]
            ]
        );
    }

    /**
     * Envoie une notification à tous les utilisateurs abonnés à l'intérêt "all"
     */
    public function publishToAll(array $data)
    {
        return $this->beamsClient->publishToInterests(
            ["all"],
            [
                "web" => [
                    "notification" => [
                        "title" => $data['title'] ?? 'Notification',
                        "body"  => $data['body'] ?? '',
                    ]
                ],
                "apns" => [
                    "aps" => ["alert" => $data['body'] ?? '']
                ],
                "fcm" => [
                    "notification" => [
                        "title" => $data['title'] ?? 'Notification',
                        "body"  => $data['body'] ?? '',
                    ]
                ]
            ]
        );
    }
}
