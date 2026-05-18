<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCall extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider',
        'endpoint',
        'method',
        'status_code',
        'response_time_ms',
        'error_message',
        'was_fallback',
        'cache_hit',
        'created_at',
    ];

    protected $casts = [
        'was_fallback'     => 'boolean',
        'cache_hit'        => 'boolean',
        'status_code'      => 'integer',
        'response_time_ms' => 'integer',
        'created_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->created_at ??= now();
        });
    }

    public function scopeFallbacks($query)
    {
        return $query->where('was_fallback', true);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
