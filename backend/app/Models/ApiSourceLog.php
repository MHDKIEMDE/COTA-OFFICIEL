<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApiSourceLog extends Model
{
    protected $table = 'api_source_logs';

    protected $fillable = [
        'fetch_date',
        'source',
        'matches_saved',
        'matches_updated',
        'api_quota_used',
        'api_quota_remaining',
        'status',
        'notes',
    ];

    protected $casts = [
        'fetch_date' => 'date',
    ];

    public static function record(
        string $date,
        string $source,
        int    $saved,
        int    $updated,
        string $status = 'success',
        ?int   $quotaUsed = null,
        ?int   $quotaRemaining = null,
        ?string $notes = null
    ): self {
        return self::create([
            'fetch_date'          => $date,
            'source'              => $source,
            'matches_saved'       => $saved,
            'matches_updated'     => $updated,
            'api_quota_used'      => $quotaUsed,
            'api_quota_remaining' => $quotaRemaining,
            'status'              => $status,
            'notes'               => $notes,
        ]);
    }

    public function isFallback(): bool
    {
        return $this->status === 'fallback';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }
}
