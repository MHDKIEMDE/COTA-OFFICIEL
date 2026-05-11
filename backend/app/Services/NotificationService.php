<?php

namespace App\Services;

use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service pour l'envoi de notifications push via FCM
 * 
 * NOTE: Ce service nécessite:
 * 1. Installation de kreait/firebase-php: composer require kreait/firebase-php
 * 2. Configuration Firebase dans .env:
 *    FIREBASE_CREDENTIALS_PATH=/path/to/firebase-credentials.json
 * 3. Fichier de credentials Firebase téléchargé depuis Firebase Console
 */
class NotificationService
{
    private ?string $fcmServerKey;
    private ?string $fcmUrl;

    public function __construct(private readonly SmsService $smsService)
    {
        $this->fcmServerKey = config('services.firebase.server_key');
        $this->fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    }

    /**
     * Envoyer une notification à un utilisateur spécifique
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = User::find($userId);

        if (!$user) {
            Log::warning('Cannot send notification: user not found', ['user_id' => $userId]);
            return false;
        }

        // Tentative FCM push
        $pushSent = false;
        if ($user->fcm_token) {
            $pushSent = $this->sendToToken($user->fcm_token, $title, $body, $data);
        }

        // Fallback SMS si push échoue ou absent et que l'utilisateur a un numéro
        if (!$pushSent && $user->phone) {
            $this->sendSmsFallback($user->phone, $title, $body);
        }

        return $pushSent;
    }

    /**
     * Envoyer un SMS de secours quand le push FCM échoue.
     * Tronque le message à 160 caractères pour rester sur 1 SMS.
     */
    private function sendSmsFallback(string $phone, string $title, string $body): void
    {
        $text = $title . ' — ' . $body;
        if (mb_strlen($text) > 155) {
            $text = mb_substr($text, 0, 152) . '...';
        }

        try {
            $this->smsService->sendRaw($phone, $text);
            Log::info('SMS fallback sent', ['phone' => substr($phone, 0, 6) . '***']);
        } catch (\Throwable $e) {
            Log::error('SMS fallback failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envoyer une notification à un token FCM spécifique
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->fcmServerKey) {
            Log::warning('FCM Server Key not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', [
                    'token' => substr($token, 0, 20) . '...',
                    'title' => $title,
                ]);
                return true;
            }

            Log::error('FCM notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM notification exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Envoyer une notification à tous les utilisateurs
     *
     * @param string $title
     * @param string $body
     * @param array $data
     * @return int Nombre de notifications envoyées
     */
    public function sendToAll(string $title, string $body, array $data = []): int
    {
        $users = User::whereNotNull('fcm_token')->get();
        $sent = 0;

        foreach ($users as $user) {
            if ($this->sendToToken($user->fcm_token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }
}

