<?php

namespace App\Services\Sms;

interface SmsProviderInterface
{
    /**
     * Envoyer un SMS.
     *
     * @throws \RuntimeException si l'envoi échoue
     */
    public function send(string $toPhoneE164, string $message): void;
}

