<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Referral extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referral_code',
        'status',
        'reward_granted',
        'reward_days',
        'reward_granted_at',
        'metadata',
    ];

    protected $casts = [
        'reward_granted' => 'boolean',
        'reward_granted_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Statuts possibles
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Paliers de récompenses
     * Nombre de filleuls => Jours premium offerts
     */
    const REWARD_TIERS = [
        1 => 3,      // 1 filleul = 3 jours
        3 => 7,      // 3 filleuls = 7 jours
        10 => 30,    // 10 filleuls = 30 jours
        50 => null,  // 50 filleuls = Premium à vie (null = illimité)
    ];

    /**
     * Relation: Le parrain (celui qui invite)
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    /**
     * Relation: Le filleul (celui qui est invité)
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Scope: Parrainages complétés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Parrainages en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Par parrain
     */
    public function scopeByReferrer($query, int $userId)
    {
        return $query->where('referrer_user_id', $userId);
    }

    /**
     * Scope: Par filleul
     */
    public function scopeByReferred($query, int $userId)
    {
        return $query->where('referred_user_id', $userId);
    }

    /**
     * Marquer comme complété
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Vérifier et accorder les récompenses au parrain
     */
    public function checkAndGrantReward(): void
    {
        $referrer = $this->referrer;
        
        if (!$referrer) {
            return;
        }

        // Compter les filleuls complétés
        $totalReferrals = self::where('referrer_user_id', $referrer->id)
            ->where('status', self::STATUS_COMPLETED)
            ->count();

        // Vérifier si un palier de récompense est atteint
        if (array_key_exists($totalReferrals, self::REWARD_TIERS)) {
            $rewardDays = self::REWARD_TIERS[$totalReferrals];

            if ($rewardDays === null) {
                // Premium à vie
                $referrer->grantLifetimePremium('referral');
            } else {
                // Ajouter des jours premium
                $referrer->addPremiumDays($rewardDays, 'referral');
            }

            // Marquer la récompense comme accordée
            $this->update([
                'reward_granted' => true,
                'reward_days' => $rewardDays,
                'reward_granted_at' => now(),
            ]);

            \Log::info('Referral reward granted', [
                'referrer_id' => $referrer->id,
                'total_referrals' => $totalReferrals,
                'reward_days' => $rewardDays,
            ]);
        }
    }

    /**
     * Obtenir la prochaine récompense pour un utilisateur
     */
    public static function getNextReward(int $userId): ?array
    {
        $totalReferrals = self::where('referrer_user_id', $userId)
            ->where('status', self::STATUS_COMPLETED)
            ->count();

        foreach (self::REWARD_TIERS as $threshold => $days) {
            if ($totalReferrals < $threshold) {
                return [
                    'target' => $threshold,
                    'reward_days' => $days,
                    'remaining' => $threshold - $totalReferrals,
                    'reward_label' => $days === null ? 'Premium à vie' : "{$days} jours premium",
                ];
            }
        }

        return null; // Toutes les récompenses débloquées
    }

    /**
     * Créer un parrainage
     */
    public static function createReferral(User $referrer, User $referred): self
    {
        return self::create([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => $referrer->referral_code,
            'status' => self::STATUS_COMPLETED,
        ]);
    }
}

