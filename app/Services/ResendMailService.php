<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ResendMailService
{
    public function send(string $to, string $subject, string $html): void
    {
        if (! Config::get('mail.resend_enabled', false)) {
            return;
        }

        $apiKey = Config::get('mail.resend_key');
        $from = Config::get('mail.resend_from');

        if (! $apiKey || ! $from) {
            return;
        }

        Http::withToken($apiKey)
            ->post('https://api.resend.com/emails', [
                'from' => $from,
                'to' => [$to],
                'subject' => $subject,
                'html' => $html,
            ])
            ->throw();
    }
}

