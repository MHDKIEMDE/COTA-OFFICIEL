<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Influencer extends Model
{
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'platform', 'user_id',
        'total_clicks', 'total_registrations', 'total_subscriptions',
        'reward_type', 'reward_threshold', 'reward_value',
        'total_rewards_given', 'last_rewarded_at',
        'is_active', 'notes',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'reward_given'     => 'boolean',
        'last_rewarded_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(InfluencerClick::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(InfluencerConversion::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function trackingUrl(): string
    {
        return url("/r/{$this->slug}");
    }

    public function recordClick(string $ip, ?string $userAgent, ?string $referrer, ?string $device): void
    {
        $this->clicks()->create([
            'ip'         => $ip,
            'user_agent' => $userAgent,
            'referrer'   => $referrer,
            'device'     => $device,
        ]);
        $this->increment('total_clicks');
    }

    public function recordConversion(int $userId, string $type): void
    {
        // Éviter les doublons
        $exists = $this->conversions()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->exists();

        if ($exists) return;

        $this->conversions()->create([
            'user_id' => $userId,
            'type'    => $type,
        ]);

        if ($type === 'registration') {
            $this->increment('total_registrations');
        } else {
            $this->increment('total_subscriptions');
        }
    }

    /** Vérifie si le seuil est atteint et accorde la récompense */
    public function checkAndReward(): bool
    {
        $unrewarded = $this->conversions()
            ->where('type', 'registration')
            ->where('reward_given', false)
            ->count();

        if ($unrewarded < $this->reward_threshold) return false;

        // Marquer les conversions comme récompensées
        $this->conversions()
            ->where('type', 'registration')
            ->where('reward_given', false)
            ->update(['reward_given' => true]);

        // Accorder la récompense si lié à un compte COTA
        if ($this->user_id && $this->reward_type !== 'cash') {
            $user = $this->user;
            if ($user) {
                $expires = $user->premium_expires_at && $user->premium_expires_at->isFuture()
                    ? $user->premium_expires_at->addDays($this->reward_value)
                    : now()->addDays($this->reward_value);

                $user->update([
                    'is_premium'          => true,
                    'premium_expires_at'  => $expires,
                    'premium_source'      => 'influencer',
                ]);
            }
        }

        $this->increment('total_rewards_given');
        $this->update(['last_rewarded_at' => now()]);

        return true;
    }
}
