<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinningCoupon extends Model
{
    protected $fillable = [
        'user_id',
        'picks',
        'total_odds',
        'picks_count',
        'avg_confidence',
        'avg_stars',
        'stake',
        'actual_gain',
        'ai_analysis',
        'played_at',
    ];

    protected $casts = [
        'picks'        => 'array',
        'ai_analysis'  => 'array',
        'total_odds'   => 'float',
        'stake'        => 'float',
        'actual_gain'  => 'float',
        'played_at'    => 'date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Cote personnelle de l'utilisateur = médiane des total_odds de ses coupons gagnants
    public static function personalOddsProfile(int $userId): array
    {
        $coupons = self::where('user_id', $userId)
            ->orderByDesc('played_at')
            ->limit(50)
            ->get();

        if ($coupons->isEmpty()) {
            return ['avg_odds' => null, 'avg_picks' => null, 'count' => 0];
        }

        return [
            'avg_odds'  => round($coupons->avg('total_odds'), 2),
            'avg_picks' => round($coupons->avg('picks_count'), 1),
            'count'     => $coupons->count(),
        ];
    }
}
