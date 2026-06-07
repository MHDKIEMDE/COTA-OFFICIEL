<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluencerClick extends Model
{
    public $timestamps = false;

    protected $fillable = ['influencer_id', 'ip', 'country', 'user_agent', 'referrer', 'device', 'clicked_at'];

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }
}
