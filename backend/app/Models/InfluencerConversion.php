<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluencerConversion extends Model
{
    public $timestamps = false;

    protected $fillable = ['influencer_id', 'user_id', 'type', 'reward_given', 'converted_at'];

    protected $casts = ['reward_given' => 'boolean'];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
