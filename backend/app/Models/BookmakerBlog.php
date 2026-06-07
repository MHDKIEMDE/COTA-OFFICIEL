<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmakerBlog extends Model
{
    // Catégories disponibles
    public const CATEGORIES = [
        'guide'      => 'Guide d\'inscription',
        'tutoriel'   => 'Tutoriel',
        'video'      => 'Vidéo',
        'photo'      => 'Photo / Infographie',
        'promotion'  => 'Promotion',
        'actualite'  => 'Actualité',
    ];

    protected $fillable = [
        'bookmaker_id',
        'promo_code',
        'bonus_title',
        'bonus_description',
        'steps',
        'cta_label',
        'category',
        'media_url',
        'thumbnail_url',
        'title',
        'excerpt',
        'is_active',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'steps'        => 'array',
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    public function bookmaker(): BelongsTo
    {
        return $this->belongsTo(Bookmaker::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
