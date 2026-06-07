<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'enabled',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'enabled'            => 'boolean',
        'quiet_hours_start'  => 'integer',
        'quiet_hours_end'    => 'integer',
    ];

    // Types de notifications supportés
    public const TYPES = [
        'routine_morning',
        'routine_afternoon',
        'routine_evening',
        'event',
        're_engagement',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
