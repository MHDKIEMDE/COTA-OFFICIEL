<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSource extends Model
{
    protected $fillable = [
        'name', 'slug', 'rss_url', 'website_url', 'logo_url',
        'language', 'category', 'is_active', 'fetch_interval',
        'last_fetched_at', 'articles_count',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFaviconUrl(): string
    {
        if ($this->logo_url) return $this->logo_url;
        if ($this->website_url) {
            $host = parse_url($this->website_url, PHP_URL_HOST);
            return "https://www.google.com/s2/favicons?domain={$host}&sz=32";
        }
        return '';
    }
}
