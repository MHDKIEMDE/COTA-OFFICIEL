<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AffiliateControlApiService
{
    private const BASE_URL    = 'https://affiliatecontrol-api.com/affiliates';
    private const CACHE_TTL   = 300; // 5 minutes

    private string $accessKey;
    private string $secretKey;
    private string $customerId;

    public function __construct()
    {
        $this->accessKey  = config('services.affiliatecontrol.access_key');
        $this->secretKey  = config('services.affiliatecontrol.secret_key');
        $this->customerId = config('services.affiliatecontrol.customer_id');
    }

    // ── Requête de base ───────────────────────────────────────────────────────

    private function get(string $endpoint, array $params = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Customer-Id' => $this->customerId,
                'X-Access-Key'  => $this->accessKey,
                'X-Secret-Key'  => $this->secretKey,
                'Accept'        => 'application/json',
            ])->get(self::BASE_URL . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('AffiliateControl API error', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('AffiliateControl API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Profil ────────────────────────────────────────────────────────────────

    public function getProfile(): ?array
    {
        return $this->get('/profile');
    }

    // ── Gains & Rapports ──────────────────────────────────────────────────────

    /** Tableau de bord des gains (mis en cache 5 min) */
    public function getDashboardEarnings(): ?array
    {
        return Cache::remember('affiliatecontrol.earnings', self::CACHE_TTL, function () {
            return $this->get('/reports/dashboard-earnings');
        });
    }

    /** Rapport détaillé par période */
    public function getReport(string $from, string $to): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Customer-Id' => $this->customerId,
                'X-Access-Key'  => $this->accessKey,
                'X-Secret-Key'  => $this->secretKey,
                'Accept'        => 'application/json',
            ])->post(self::BASE_URL . '/reports', [
                'date_from' => $from,
                'date_to'   => $to,
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('AffiliateControl report error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ── Conversions CPA ───────────────────────────────────────────────────────

    /** Liste toutes les conversions CPA */
    public function getCpaConversions(int $page = 1, int $limit = 50): ?array
    {
        return $this->get('/cpa-conversions', [
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    /** Vérifier si un player_id a converti (pour valider inscription manuelle) */
    public function verifyPlayerConversion(string $playerId): bool
    {
        $cacheKey = "affiliatecontrol.player.{$playerId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($playerId) {
            $data = $this->get('/cpa-conversions', ['search' => $playerId]);

            if (!$data) return false;

            $items = $data['data'] ?? $data['items'] ?? [];
            foreach ($items as $conversion) {
                $pid = $conversion['player_id'] ?? $conversion['playerId'] ?? '';
                if ((string) $pid === (string) $playerId) {
                    return true;
                }
            }

            return false;
        });
    }

    // ── Wallet & Paiements ────────────────────────────────────────────────────

    /** Solde des wallets */
    public function getWallets(): ?array
    {
        return Cache::remember('affiliatecontrol.wallets', self::CACHE_TTL, function () {
            return $this->get('/wallets');
        });
    }

    /** Historique des paiements */
    public function getPaymentsHistory(): ?array
    {
        return $this->get('/payments-history');
    }

    // ── Codes promo ───────────────────────────────────────────────────────────

    /** Créer un code promo pour un influenceur */
    public function createPromoCode(string $code, array $options = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Customer-Id' => $this->customerId,
                'X-Access-Key'  => $this->accessKey,
                'X-Secret-Key'  => $this->secretKey,
                'Accept'        => 'application/json',
            ])->post(self::BASE_URL . '/promo-codes', array_merge([
                'code' => strtoupper($code),
            ], $options));

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('AffiliateControl promo code error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** Lister les codes promo existants */
    public function getPromoCodes(): ?array
    {
        return $this->get('/promo-codes');
    }

    // ── Sources de trafic ─────────────────────────────────────────────────────

    public function getTrafficSources(): ?array
    {
        return $this->get('/traffic-sources');
    }

    public function getTrafficSourcePostbacks(): ?array
    {
        return $this->get('/traffic-source-postbacks');
    }
}
