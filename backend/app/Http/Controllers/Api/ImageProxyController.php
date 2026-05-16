<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy d'images pour les logos équipes/ligues depuis media.api-sports.io.
 *
 * Avantages :
 * - Cache local 30 jours → zéro appel répété vers api-sports.io
 * - Cloudflare cache le proxy en prod → CDN mondial gratuit
 * - Headers Cache-Control corrects pour les navigateurs/apps
 *
 * Exemple d'URL :
 *   GET /api/img/team/33       → logo équipe 33 (Manchester United)
 *   GET /api/img/league/39     → logo ligue 39 (Premier League)
 *   GET /api/img/flag/gb       → drapeau GB
 */
class ImageProxyController
{
    private const ALLOWED_TYPES = ['team', 'league', 'flag'];
    private const SOURCE_URLS   = [
        'team'   => 'https://media.api-sports.io/football/teams/{id}.png',
        'league' => 'https://media.api-sports.io/football/leagues/{id}.png',
        'flag'   => 'https://media.api-sports.io/flags/{id}.svg',
    ];

    // Cache 30 jours — les logos changent rarement
    private const CACHE_TTL = 60 * 60 * 24 * 30;

    public function serve(Request $request, string $type, string $id): Response
    {
        // Validation : type autorisé + ID numérique ou code pays 2 lettres
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return response('Not found', 404);
        }

        if ($type === 'flag') {
            if (!preg_match('/^[a-z]{2}$/', $id)) {
                return response('Invalid flag code', 400);
            }
        } else {
            if (!ctype_digit($id) || (int)$id <= 0 || (int)$id > 9999999) {
                return response('Invalid ID', 400);
            }
        }

        $cacheKey = "img_proxy:{$type}:{$id}";

        // Essayer le cache d'abord
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $this->imageResponse($cached['body'], $cached['mime'], true);
        }

        // Récupérer depuis api-sports.io
        $url = str_replace('{id}', $id, self::SOURCE_URLS[$type]);

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'COTA-App/1.0'])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("ImageProxy: impossible de récupérer {$url}", ['status' => $response->status()]);
                return response('Image unavailable', 502);
            }

            $body = $response->body();
            $mime = $response->header('Content-Type') ?? ($type === 'flag' ? 'image/svg+xml' : 'image/png');

            // Stocker dans le cache Redis
            Cache::put($cacheKey, ['body' => $body, 'mime' => $mime], self::CACHE_TTL);

            return $this->imageResponse($body, $mime, false);

        } catch (\Throwable $e) {
            Log::error("ImageProxy: erreur", ['url' => $url, 'error' => $e->getMessage()]);
            return response('Image unavailable', 502);
        }
    }

    private function imageResponse(string $body, string $mime, bool $fromCache): Response
    {
        return response($body, 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=2592000, immutable', // 30 jours navigateur/Cloudflare
            'X-Cache'       => $fromCache ? 'HIT' : 'MISS',
            'Vary'          => 'Accept-Encoding',
        ]);
    }
}
