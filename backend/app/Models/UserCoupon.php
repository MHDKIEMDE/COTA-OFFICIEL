<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'picks',
        'total_odds',
        'picks_count',
        'stake',
        'status',
        'actual_gain',
        'played_at',
    ];

    protected $casts = [
        'picks'       => 'array',
        'total_odds'  => 'float',
        'stake'       => 'float',
        'actual_gain' => 'float',
        'played_at'   => 'date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPotentialGainAttribute(): ?float
    {
        return $this->stake ? round($this->total_odds * $this->stake, 2) : null;
    }
}
