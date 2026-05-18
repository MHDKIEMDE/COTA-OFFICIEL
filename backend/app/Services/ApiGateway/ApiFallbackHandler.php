<?php

declare(strict_types=1);

namespace App\Services\ApiGateway;

use App\Models\ApiCall;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApiFallbackHandler
{
    private array $consecutiveErrors = [];

    public function log(string $from, string $to, string $context = ''): void
    {
        Log::warning("ApiGateway: fallback {$from} → {$to}", ['context' => $context]);

        try {
            ApiCall::create([
                'provider'        => $from,
                'endpoint'        => $context,
                'method'          => 'GET',
                'status_code'     => 0,
                'was_fallback'    => true,
                'cache_hit'       => false,
                'error_message'   => "Fallback vers {$to}",
            ]);
        } catch (\Throwable $e) {
            Log::error("ApiFallbackHandler: impossible d'écrire log DB", ['error' => $e->getMessage()]);
        }
    }

    public function logApiCall(
        string $provider,
        string $endpoint,
        int    $statusCode,
        int    $responseTimeMs,
        bool   $cacheHit = false,
        ?string $errorMessage = null
    ): void {
        try {
            ApiCall::create([
                'provider'         => $provider,
                'endpoint'         => $endpoint,
                'method'           => 'GET',
                'status_code'      => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'was_fallback'     => false,
                'cache_hit'        => $cacheHit,
                'error_message'    => $errorMessage,
            ]);
        } catch (\Throwable) {
        }

        if ($errorMessage) {
            $this->consecutiveErrors[$provider] = ($this->consecutiveErrors[$provider] ?? 0) + 1;

            if ($this->consecutiveErrors[$provider] >= 3) {
                Log::critical("ApiGateway: {$provider} — 3 erreurs consécutives !", [
                    'provider' => $provider,
                    'errors'   => $this->consecutiveErrors[$provider],
                ]);
            }
        } else {
            $this->consecutiveErrors[$provider] = 0;
        }
    }

    public function getConsecutiveErrors(string $provider): int
    {
        return $this->consecutiveErrors[$provider] ?? 0;
    }
}
