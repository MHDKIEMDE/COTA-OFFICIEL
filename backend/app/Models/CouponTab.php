<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Onglet de coupon configurable depuis le dashboard admin.
 *
 * La clé `key` (prudent|equilibre|kamikaze|featured) est stable et sert de pont
 * avec la logique de génération ; seuls label / subtitle / is_active / sort_order
 * sont éditables et pilotent l'affichage mobile de façon dynamique.
 */
class CouponTab extends Model
{
    protected $fillable = [
        'key',
        'label',
        'subtitle',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Onglets actifs, dans l'ordre d'affichage. */
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
