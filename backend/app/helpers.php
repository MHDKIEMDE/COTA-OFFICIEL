<?php

if (!function_exists('logo_url')) {
    /**
     * Transforme une URL media.api-sports.io en URL proxy COTA.
     * En prod : Cloudflare cache l'URL proxy → CDN mondial gratuit.
     * En dev  : retourne l'URL proxy locale.
     *
     * Exemples :
     *   logo_url('team', 33)    → /api/img/team/33
     *   logo_url('league', 39)  → /api/img/league/39
     *   logo_url('flag', 'gb')  → /api/img/flag/gb
     *   logo_url(null, null)    → null  (pas de logo dispo)
     */
    function logo_url(?string $type, int|string|null $id): ?string
    {
        if (!$type || !$id) {
            return null;
        }

        $allowedTypes = ['team', 'league', 'flag'];
        if (!in_array($type, $allowedTypes, true)) {
            return null;
        }

        $baseUrl = rtrim(config('app.url'), '/');
        return "{$baseUrl}/api/img/{$type}/{$id}";
    }
}

if (!function_exists('team_logo_url')) {
    function team_logo_url(int|string|null $teamId): ?string
    {
        return logo_url('team', $teamId);
    }
}

if (!function_exists('league_logo_url')) {
    function league_logo_url(int|string|null $leagueId): ?string
    {
        return logo_url('league', $leagueId);
    }
}
