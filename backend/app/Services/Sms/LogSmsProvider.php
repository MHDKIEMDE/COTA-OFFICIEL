<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $toPhoneE164, string $message): void
    {
        Log::info('[SMS:LOG] Message simulé', [
            'to' => $toPhoneE164,
            'message' => $message,
        ]);
    }
}

