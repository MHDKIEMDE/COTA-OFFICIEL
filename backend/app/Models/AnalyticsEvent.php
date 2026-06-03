<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_name',
        'properties',
        'session_hash',
        'source',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $eventName,
        array $properties = [],
        ?int $userId = null,
        string $source = 'flutter_app',
        ?string $sessionHash = null,
    ): self {
        return self::create([
            'event_name'   => $eventName,
            'properties'   => $properties,
            'user_id'      => $userId,
            'source'       => $source,
            'session_hash' => $sessionHash,
            'created_at'   => now(),
        ]);
    }
}
