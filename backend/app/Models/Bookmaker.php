<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bookmaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'primary_color',
        'secondary_color',
        'affiliate_link',
        'download_link',
        'is_active',
        'sort_order',
        'logo_url',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les clics
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(BookmakerClick::class);
    }

    /**
     * Scope: Bookmakers actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Trier par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
