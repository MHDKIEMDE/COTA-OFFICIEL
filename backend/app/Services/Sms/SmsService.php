<?php

namespace App\Services\Sms;

class SmsService
{
    public function __construct(
        private readonly SmsProviderInterface $provider,
    ) {}

    public function sendOtp(string $toPhoneE164, string $code, int $ttlMinutes): void
    {
        $template = (string) config('sms.termii.message_template', 'Votre code COTA: {code}. Valide {ttl} minutes.');

        $message = str_replace(
            ['{code}', '{ttl}'],
            [$code, (string) $ttlMinutes],
            $template
        );

        $this->provider->send($toPhoneE164, $message);
    }

    /**
     * Envoyer un SMS libre (fallback notification).
     * Le numéro peut être au format local (ex: 77XXXXXXX) ou E.164 (ex: +221XXXXXXXX).
     */
    public function sendRaw(string $phone, string $message): void
    {
        // Normaliser en E.164 si nécessaire
        $e164 = str_starts_with($phone, '+') ? $phone : '+' . ltrim($phone, '0');
        $this->provider->send($e164, $message);
    }
}

