<?php

namespace App\Services\Payment\Drivers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CinetpayDriver implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $siteId;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $isTest       = ($config['env'] ?? 'test') === 'test';
        $this->apiKey = $config['api_key']              ?? '';
        $this->siteId = $config['extra']['site_id']     ?? '';
        $this->baseUrl = $isTest
            ? 'https://api-checkout.cinetpay.com/v2'
            : 'https://api-checkout.cinetpay.com/v2';
    }

    public function createInvoice(array $data): array
    {
        try {
            $transactionId = 'COTA-' . $data['user_id'] . '-' . Str::upper(Str::random(8));

            $payload = [
                'apikey'            => $this->apiKey,
                'site_id'           => $this->siteId,
                'transaction_id'    => $transactionId,
                'amount'            => $data['amount'],
                'currency'          => AppConfig::get('payment.currency', 'XOF'),
                'description'       => $data['description'],
                'return_url'        => config('app.frontend_url', config('app.url')) . '/subscription/success',
                'cancel_url'        => config('app.frontend_url', config('app.url')) . '/subscription/cancel',
                'notify_url'        => config('app.url') . '/api/webhooks/payment',
                'customer_name'     => $data['user_name']  ?? 'Client COTA',
                'customer_surname'  => '',
                'customer_email'    => $data['user_email'] ?? '',
                'customer_phone_number' => $data['user_phone'] ?? '',
                'metadata'          => json_encode([
                    'user_id' => $data['user_id'],
                    'plan'    => $data['plan'],
                ]),
                'channels'          => 'ALL',
                'lang'              => 'fr',
            ];

            $response = Http::post("{$this->baseUrl}/payment", $payload);
            $result   = $response->json();

            if (($result['code'] ?? '') === '201') {
                return [
                    'success'     => true,
                    'token'       => $transactionId,
                    'payment_url' => $result['data']['payment_url'] ?? '',
                ];
            }

            Log::error('CinetPay createInvoice failed', $result);
            return ['success' => false, 'error' => $result['message'] ?? 'Erreur CinetPay'];

        } catch (\Throwable $e) {
            Log::error('CinetPay exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyTransaction(string $token): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/payment/check", [
                'apikey'         => $this->apiKey,
                'site_id'        => $this->siteId,
                'transaction_id' => $token,
            ]);

            $result = $response->json();
            $code   = $result['code'] ?? '';

            $status = match (true) {
                $code === '00'                          => 'completed',
                in_array($code, ['600', 'PENDING'])     => 'pending',
                default                                 => 'failed',
            };

            return ['success' => true, 'status' => $status, 'data' => $result];

        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function parseWebhook(array $payload, string $rawBody, array $headers): ?array
    {
        $token  = $payload['cpm_trans_id'] ?? $payload['transaction_id'] ?? null;
        $code   = $payload['cpm_result']   ?? $payload['code'] ?? null;
        $amount = (int) ($payload['cpm_amount'] ?? $payload['amount'] ?? 0);

        if (!$token) {
            return null;
        }

        return [
            'token'  => $token,
            'status' => $code === '00' ? 'completed' : 'failed',
            'amount' => $amount,
        ];
    }

    public function getPlanPrice(string $plan): int
    {
        $plans = AppConfig::get('app.premium_plans', []);
        return (int) ($plans[$plan]['price'] ?? 0);
    }
}
