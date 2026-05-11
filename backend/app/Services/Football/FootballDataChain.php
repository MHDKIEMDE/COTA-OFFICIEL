<?php

declare(strict_types=1);

namespace App\Services\Football;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Chaîne de fallback football data.
 *
 * Ordre : ApiFootball → Sportradar → LocalCache
 *
 * - Essaie chaque provider dans l'ordre.
 * - Si un provider est indisponible (quota épuisé, erreur réseau…), passe au suivant.
 * - Logue quel provider a répondu et si des données sont stalles.
 * - Le provider actif est mis en cache 1 min pour éviter les checks répétés.
 */
class FootballDataChain
{
    /** @param FootballProviderInterface[] $providers */
    public function __construct(private readonly array $providers) {}

    // ── API publique ──────────────────────────────────────────────────────────

    public function getFixtures(?string $date = null): array
    {
        [$provider, $data] = $this->resolve('getFixtures', [$date]);
        $this->logResult($provider, 'fixtures', count($data));
        return $data;
    }

    public function getLiveMatches(): array
    {
        [$provider, $data] = $this->resolve('getLiveMatches', []);
        $this->logResult($provider, 'live', count($data));
        return $data;
    }

    /** Retourne le nom du provider actuellement actif */
    public function activeProvider(): string
    {
        return Cache::get('football_chain_active_provider', 'unknown');
    }

    /** Status de chaque provider (pour le dashboard admin) */
    public function status(): array
    {
        return array_map(fn (FootballProviderInterface $p) => [
            'name'      => $p->name(),
            'available' => $p->isAvailable(),
        ], $this->providers);
    }

    // ── Résolution interne ────────────────────────────────────────────────────

    /**
     * Parcourt la chaîne et retourne [provider_name, data].
     * Toujours retourne quelque chose (au minimum le CacheProvider).
     */
    private function resolve(string $method, array $args): array
    {
        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                Log::info("FootballChain: {$provider->name()} indisponible, passage au suivant");
                continue;
            }

            try {
                $data = $provider->$method(...$args);

                // Un provider disponible peut retourner [] (pas de matchs du jour) — c'est ok
                // Mais si une exception est levée, on passe au suivant
                Cache::put('football_chain_active_provider', $provider->name(), 60);
                return [$provider->name(), $data];
            } catch (\Throwable $e) {
                Log::error("FootballChain: {$provider->name()} exception sur {$method}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Ne devrait jamais arriver car CacheProvider::isAvailable() === true
        Log::critical('FootballChain: TOUS les providers ont échoué');
        return ['none', []];
    }

    private function logResult(string $providerName, string $type, int $count): void
    {
        $level = $providerName === 'local-cache' ? 'warning' : 'info';
        Log::$level("FootballChain: {$type} servi par [{$providerName}]", ['count' => $count]);
    }
}
