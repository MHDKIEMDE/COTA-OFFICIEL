<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'sportradar_id',
        'name',
        'full_name',
        'country',
        'icon',
        'priority',
        'is_active',
        'is_trending',
        'trending_start',
        'trending_end',
        'description',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_trending' => 'boolean',
        'trending_start' => 'date',
        'trending_end' => 'date',
    ];

    /**
     * Scope: Compétitions actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Compétitions tendance (manuellement ou par date)
     */
    public function scopeTrending($query)
    {
        return $query->where(function ($q) {
            // Manuellement marquée comme tendance
            $q->where('is_trending', true);
            
            // Ou dans la période de tendance
            $q->orWhere(function ($q2) {
                $today = now()->toDateString();
                $q2->whereNotNull('trending_start')
                   ->whereNotNull('trending_end')
                   ->where('trending_start', '<=', $today)
                   ->where('trending_end', '>=', $today);
            });
        });
    }

    /**
     * Scope: Ordonner par priorité
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc')
                     ->orderBy('display_order', 'asc')
                     ->orderBy('name', 'asc');
    }

    /**
     * Scope: Compétitions prioritaires (priorité <= 5)
     */
    public function scopePriority($query)
    {
        return $query->where('priority', '<=', 5);
    }

    /**
     * Vérifier si la compétition est actuellement en tendance
     */
    public function isTrendingNow(): bool
    {
        if ($this->is_trending) {
            return true;
        }

        if ($this->trending_start && $this->trending_end) {
            $today = now()->startOfDay();
            return $today->between($this->trending_start, $this->trending_end);
        }

        return false;
    }

    /**
     * Obtenir les compétitions prioritaires avec cache
     */
    public static function getPriorityCompetitions()
    {
        return cache()->remember('priority_competitions', 3600, function () {
            return self::active()
                ->ordered()
                ->get()
                ->keyBy('sportradar_id');
        });
    }

    /**
     * Obtenir les compétitions tendance avec cache
     */
    public static function getTrendingCompetitions()
    {
        return cache()->remember('trending_competitions', 3600, function () {
            return self::active()
                ->trending()
                ->ordered()
                ->pluck('sportradar_id')
                ->toArray();
        });
    }

    /**
     * Vider le cache des compétitions
     */
    public static function clearCache()
    {
        cache()->forget('priority_competitions');
        cache()->forget('trending_competitions');
    }

    /**
     * Boot: vider le cache lors des modifications
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
