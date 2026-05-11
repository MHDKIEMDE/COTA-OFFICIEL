<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'amount',
        'currency',
        'paydunya_token',
        'status',
        'starts_at',
        'expires_at',
        'payment_method',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * Plans disponibles avec leurs détails
     */
    const PLANS = [
        'weekly' => [
            'name' => 'Hebdomadaire',
            'duration_days' => 7,
            'price' => 2500,
            'currency' => 'FCFA',
        ],
        'monthly' => [
            'name' => 'Mensuel',
            'duration_days' => 30,
            'price' => 8000,
            'currency' => 'FCFA',
        ],
        'quarterly' => [
            'name' => 'Trimestriel',
            'duration_days' => 90,
            'price' => 20000,
            'currency' => 'FCFA',
        ],
    ];

    /**
     * Statuts possibles
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Relation: L'abonnement appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Abonnements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope: Abonnements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Abonnements expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope: Par plan
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    /**
     * Vérifier si l'abonnement est actif
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_COMPLETED 
            && $this->expires_at 
            && $this->expires_at->isFuture();
    }

    /**
     * Vérifier si l'abonnement est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Nombre de jours restants
     */
    public function daysRemaining(): int
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->expires_at);
    }

    /**
     * Obtenir les détails d'un plan
     */
    public static function getPlanDetails(string $plan): ?array
    {
        return self::PLANS[$plan] ?? null;
    }

    /**
     * Obtenir le prix d'un plan
     */
    public static function getPlanPrice(string $plan): int
    {
        return self::PLANS[$plan]['price'] ?? 0;
    }

    /**
     * Obtenir la durée d'un plan en jours
     */
    public static function getPlanDuration(string $plan): int
    {
        return self::PLANS[$plan]['duration_days'] ?? 0;
    }

    /**
     * Calculer la date d'expiration pour un plan
     */
    public static function calculateExpirationDate(string $plan, ?Carbon $fromDate = null): Carbon
    {
        $from = $fromDate ?? now();
        $days = self::getPlanDuration($plan);

        return $from->copy()->addDays($days);
    }

    /**
     * Marquer comme complété et activer
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'starts_at' => now(),
            'expires_at' => self::calculateExpirationDate($this->plan),
        ]);

        // Activer le premium pour l'utilisateur
        $this->user->activatePremium($this->plan);
    }

    /**
     * Marquer comme échoué
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Marquer comme annulé
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}

