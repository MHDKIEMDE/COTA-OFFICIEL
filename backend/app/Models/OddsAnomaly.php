<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OddsAnomaly extends Model
{
    protected $fillable = [
        'match_id', 'home_team', 'away_team', 'competition', 'country',
        'match_date', 'bet_type', 'outcome', 'bookmaker',
        'odd_value', 'market_odd', 'gap_pct', 'is_overpriced',
        'notified', 'expires_at',
    ];

    protected $casts = [
        'match_date'   => 'datetime',
        'expires_at'   => 'datetime',
        'odd_value'    => 'decimal:2',
        'market_odd'   => 'decimal:2',
        'gap_pct'      => 'decimal:1',
        'is_overpriced' => 'boolean',
        'notified'     => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeUnnotified($query)
    {
        return $query->where('notified', false);
    }

    public function getMinutesRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInMinutes($this->expires_at, false));
    }

    public function getLabelAttribute(): string
    {
        $sign = $this->is_overpriced ? '⬆️' : '⬇️';
        return "{$sign} {$this->home_team} vs {$this->away_team} — {$this->outcome} @ {$this->odd_value} sur {$this->bookmaker} (marché: {$this->market_odd}, écart: +{$this->gap_pct}%)";
    }
}
