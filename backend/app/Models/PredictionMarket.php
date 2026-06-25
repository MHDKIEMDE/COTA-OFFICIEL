<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionMarket extends Model
{
    protected $fillable = [
        'prediction_id',
        'category',
        'bet_type',
        'outcome',
        'market_selection',
        'odds',
        'market_score',
        'score_tier',
        'active_side',
        'engine',
        'is_primary',
        'is_premium',
        'is_risky',
        'status',
    ];

    protected $casts = [
        'odds' => 'decimal:2',
        'market_score' => 'decimal:2',
        'is_primary' => 'boolean',
        'is_premium' => 'boolean',
        'is_risky' => 'boolean',
    ];

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
