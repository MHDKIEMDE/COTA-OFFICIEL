<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUse extends Model
{
    protected $fillable = ['promo_code', 'user_id', 'bookmaker', 'phone', 'used_at'];

    protected $casts = ['used_at' => 'datetime'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
