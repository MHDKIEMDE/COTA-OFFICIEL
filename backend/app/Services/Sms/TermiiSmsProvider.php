<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

class TermiiSmsProvider implements SmsProviderInterface
{
    public function send(string $toPhoneE164, string $message): void
    {
        $apiKey = config('sms.termii.api_key');
        $baseUrl = rtrim((string) config('sms.termii.base_url'), '/');
        $senderId = (string) config('sms.sender_id', 'COTA');
        $channel = (string) config('sms.termii.channel', 'generic');

        if (!$apiKey) {
            throw new \RuntimeException('TERMII_API_KEY manquant (config sms.termii.api_key).');
        }

        // Termii send message endpoint avec timeout réduit et retry
        try {
            $response = Http::timeout(5)->retry(1, 100)->asJson()->post($baseUrl . '/api/sms/send', [
                'to' => $toPhoneE164,
                'from' => $senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => $channel,
                'api_key' => $apiKey,
            ]);

            if ($response->failed()) {
                \Log::warning('Erreur Termii HTTP', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $toPhoneE164,
                ]);
                // Ne pas throw pour éviter de bloquer l'authentification
                // L'OTP est déjà généré et stocké, l'utilisateur peut le voir dans les logs en dev
                return;
            }

            $data = $response->json();

            // Termii renvoie généralement { code: "ok", message_id: "...", ... } ou un status
            if (is_array($data) && isset($data['code']) && $data['code'] !== 'ok') {
                \Log::warning('Erreur Termii API', [
                    'response' => $data,
                    'phone' => $toPhoneE164,
                ]);
                // Ne pas throw pour éviter de bloquer l'authentification
                return;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Timeout ou erreur de connexion - log mais ne bloque pas
            \Log::warning('Timeout/Connexion Termii', [
                'phone' => $toPhoneE164,
                'error' => $e->getMessage(),
            ]);
            // Ne pas throw - l'OTP est déjà généré
        } catch (\Exception $e) {
            \Log::error('Erreur inattendue Termii', [
                'phone' => $toPhoneE164,
                'error' => $e->getMessage(),
            ]);
            // Ne pas throw pour éviter de bloquer l'authentification
        }
    }
}

