<?php

declare(strict_types=1);

namespace App\Services\ApiGateway;

class CacheStrategy
{
    const LIVE_TTL       = 60;       // 1 minute  — matchs live
    const TODAY_TTL      = 600;      // 10 minutes — matchs du jour
    const FUTURE_TTL     = 21600;    // 6 heures  — matchs futurs
    const HISTORICAL_TTL = 604800;   // 7 jours   — matchs passés
    const STALE_TTL      = 2592000;  // 30 jours  — fallback total
}
