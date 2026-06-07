<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmakerCandidate extends Model
{
    protected $fillable = [
        'api_source',
        'api_id',
        'name',
        'slug',
        'logo_url',
        'website_url',
        'description',
        'country',
        'raw_data',
        'bonus_label',
        'bonus_description',
        'primary_color',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'bookmaker_id',
    ];

    protected $casts = [
        'raw_data'    => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function bookmaker(): BelongsTo
    {
        return $this->belongsTo(Bookmaker::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
}
