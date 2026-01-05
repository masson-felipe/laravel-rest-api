<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class NotificationService
{

    public function send(string $email, string $message): void
    {
        $response = Http::post(
            'https://util.devi.tools/api/v1/notify',
            [
                'email' => $email,
                'message' => $message,
            ]
        );

        if ($response->status() !== 204) {
            throw new \RuntimeException(
                $response->json('message') ?? 'Notification service error'
            );
        }
    }
}
