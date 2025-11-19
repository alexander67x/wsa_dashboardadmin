<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    public function sendNotification(string $heading, string $content, ?string $url = null): void
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if (! $appId || ! $apiKey) {
            Log::warning('OneSignal keys missing, skipping notification.');
            return;
        }

        $payload = [
            'app_id' => $appId,
            'headings' => ['en' => $heading],
            'contents' => ['en' => $content],
            'included_segments' => ['All'],
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        try {
            Http::withHeaders([
                'Authorization' => "Basic {$apiKey}",
            ])->post('https://api.onesignal.com/notifications', $payload)->throw();
        } catch (\Throwable $exception) {
            Log::error('OneSignal notification failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
