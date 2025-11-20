<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    public static function send(string $token, string $title, string $body, array $data = []): void
    {
        try {
            Http::post('https://exp.host/--/api/v2/push/send', [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'data' => $data,
            ])->throw();
        } catch (\Throwable $exception) {
            Log::error('Expo push notification failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}

