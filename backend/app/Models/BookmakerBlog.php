<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmakerBlog extends Model
{
    protected $fillable = [
        'bookmaker_id',
        'promo_code',
        'bonus_title',
        'bonus_description',
        'steps',
        'cta_label',
        'is_active',
    ];

    protected $casts = [
        'steps'     => 'array',
        'is_active' => 'boolean',
    ];

    public function bookmaker(): BelongsTo
    {
        return $this->belongsTo(Bookmaker::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
