<?php

namespace App\Services;

use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifications push via FCM HTTP v1 (OAuth2 Bearer token).
 *
 * Prérequis :
 * - FIREBASE_PROJECT_ID=your-project-id   (dans .env)
 * - FIREBASE_CREDENTIALS_PATH=/path/to/firebase-service-account.json
 *
 * Le fichier JSON s'obtient dans Firebase Console →
 * Project settings → Service accounts → Generate new private key.
 */
class NotificationService
{
    private ?string $projectId;
    private ?string $credentialsPath;
    private ?string $cachedToken = null;
    private int $tokenExpiry = 0;

    public function __construct(private readonly SmsService $smsService)
    {
        $this->projectId       = config('services.firebase.project_id');
        $this->credentialsPath = config('services.firebase.credentials_path');
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = User::find($userId);
        if (!$user) {
            Log::warning('Notification: utilisateur introuvable', ['user_id' => $userId]);
            return false;
        }

        $pushSent = false;
        if ($user->fcm_token) {
            $pushSent = $this->sendToToken($user->fcm_token, $title, $body, $data);
        }

        if (!$pushSent && $user->phone) {
            $this->sendSmsFallback($user->phone, $title, $body);
        }

        return $pushSent;
    }

    public function sendToAll(string $title, string $body, array $data = []): int
    {
        $sent = 0;
        User::whereNotNull('fcm_token')->chunkById(200, function ($users) use ($title, $body, $data, &$sent) {
            foreach ($users as $user) {
                if ($this->sendToToken($user->fcm_token, $title, $body, $data)) {
                    $sent++;
                }
            }
        });
        return $sent;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken || !$this->projectId) {
            Log::warning('FCM: project_id ou credentials manquants');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => array_map('strval', $data),
                        'android'      => ['priority' => 'high'],
                        'apns'         => ['payload' => ['aps' => ['sound' => 'default']]],
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            // Token expiré ou invalide → on le supprime
            if (in_array($response->status(), [404, 410])) {
                Log::info('FCM: token invalide supprimé', ['prefix' => substr($token, 0, 20)]);
                User::where('fcm_token', $token)->update(['fcm_token' => null]);
            } else {
                Log::error('FCM: envoi échoué', [
                    'status' => $response->status(),
                    'error'  => $response->json('error.details.0.errorCode') ?? $response->body(),
                ]);
            }

            return false;

        } catch (\Throwable $e) {
            Log::error('FCM: exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Access token OAuth2 depuis le service account JSON.
     * Mis en cache en mémoire pour éviter les appels répétés.
     */
    private function getAccessToken(): ?string
    {
        if ($this->cachedToken && time() < $this->tokenExpiry - 60) {
            return $this->cachedToken;
        }

        if (!$this->credentialsPath || !file_exists($this->credentialsPath)) {
            Log::warning('FCM: fichier credentials introuvable', ['path' => $this->credentialsPath]);
            return null;
        }

        try {
            $creds = json_decode(file_get_contents($this->credentialsPath), true);

            $now  = time();
            $jwt  = $this->buildJwt([
                'iss'   => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $creds['private_key']);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('FCM OAuth2: échec', ['body' => $response->body()]);
                return null;
            }

            $this->cachedToken = $response->json('access_token');
            $this->tokenExpiry = $now + (int) $response->json('expires_in', 3600);

            return $this->cachedToken;

        } catch (\Throwable $e) {
            Log::error('FCM OAuth2: exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function buildJwt(array $claims, string $privateKey): string
    {
        $b64 = fn(string $d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');

        $header  = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $b64(json_encode($claims));
        $signing = $header . '.' . $payload;

        openssl_sign($signing, $signature, $privateKey, 'SHA256');

        return $signing . '.' . $b64($signature);
    }

    private function sendSmsFallback(string $phone, string $title, string $body): void
    {
        $text = mb_substr($title . ' — ' . $body, 0, 155);
        try {
            $this->smsService->sendRaw($phone, $text);
        } catch (\Throwable $e) {
            Log::error('SMS fallback échoué', ['error' => $e->getMessage()]);
        }
    }
}
