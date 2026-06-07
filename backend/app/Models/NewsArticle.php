<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsArticle extends Model
{
    protected $fillable = [
        'news_source_id', 'title', 'url', 'guid', 'description',
        'image_url', 'published_at', 'author', 'tags', 'is_active',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags'         => 'array',
        'is_active'    => 'boolean',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Vérifie si cet article est lié à ces équipes/compétition
     */
    public function matchesTeams(string $homeTeam, string $awayTeam, string $competition = ''): bool
    {
        $text = strtolower($this->title . ' ' . ($this->description ?? ''));
        $keywords = array_filter([
            strtolower($homeTeam),
            strtolower($awayTeam),
            strtolower($competition),
            // Versions courtes des noms d'équipes
            strtolower(explode(' ', $homeTeam)[0] ?? ''),
            strtolower(explode(' ', $awayTeam)[0] ?? ''),
        ]);

        foreach ($keywords as $kw) {
            if (strlen($kw) > 3 && str_contains($text, $kw)) return true;
        }
        return false;
    }
}
