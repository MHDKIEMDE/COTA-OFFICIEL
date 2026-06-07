<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmakerTip extends Model
{
    protected $fillable = [
        'bookmaker_id',
        'title',
        'icon',
        'tips',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'tips'      => 'array',
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
