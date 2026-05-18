<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiQuotaUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider',
        'date',
        'requests_count',
        'quota_limit',
        'last_request_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'requests_count'  => 'integer',
        'quota_limit'     => 'integer',
        'last_request_at' => 'datetime',
    ];

    public function getUsagePercentageAttribute(): float
    {
        if ($this->quota_limit === 0) {
            return 0.0;
        }

        return round(($this->requests_count / $this->quota_limit) * 100, 1);
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->quota_limit - $this->requests_count);
    }

    public function getStatusAttribute(): string
    {
        return match(true) {
            $this->usage_percentage >= 95 => 'critical',
            $this->usage_percentage >= 80 => 'warning',
            default                       => 'ok',
        };
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
