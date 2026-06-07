<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id',
        'facebook_id',
        'avatar',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'country_code',
        'device_type',
        'fcm_token',
        'referral_code',
        'referred_by',
        'is_premium',
        'is_admin',
        'is_super_admin',
        'premium_expires_at',
        'premium_source',
        'referral_count',
        'referral_days_earned',
        'welcome_combined_used',
        'welcome_combined_expires_at',
        'last_login_at',
        'admin_last_login_at',
        'phone_verified_at',
        'email_verified_at',
        'notification_settings',
        'preferences',
        'locale',
        'telegram_id',
        'telegram_username',
        'preferred_bookmaker_id',
        'bookmaker_slug',
        'parieur_profil',
        'detected_region',
        'pin_attempts',
        'pin_locked_until',
        'last_device_id',
        'tier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'facebook_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'premium_expires_at' => 'datetime',
            'welcome_combined_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_premium' => 'boolean',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
            'welcome_combined_used' => 'boolean',
            'admin_last_login_at' => 'datetime',
            'notification_settings' => 'array',
            'preferences'           => 'array',
            'pin_locked_until'      => 'datetime',
            'pin_attempts'          => 'integer',
        ];
    }

    /**
     * Boot function for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Générer automatiquement un code de parrainage unique à la création
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relations
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function affiliationBonus()
    {
        return $this->hasMany(AffiliationBonus::class);
    }

    public function favorites()
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Vérifier si l'utilisateur est premium
     */
    public function isPremium(): bool
    {
        // Premium à vie si is_premium = true et premium_expires_at = null
        if ($this->is_premium && $this->premium_expires_at === null) {
            return true;
        }

        return $this->is_premium &&
               $this->premium_expires_at &&
               $this->premium_expires_at->isFuture();
    }

    /**
     * Vérifier si le combiné de bienvenue est disponible
     */
    public function canAccessWelcomeCombined(): bool
    {
        return !$this->welcome_combined_used ||
               ($this->welcome_combined_expires_at && $this->welcome_combined_expires_at->isFuture());
    }

    /**
     * Ajouter des jours premium à l'utilisateur
     * 
     * @param int $days Nombre de jours à ajouter
     * @param string|null $source Source du premium (subscription, referral, affiliation_xxx)
     */
    public function addPremiumDays(int $days, ?string $source = null): void
    {
        // Si déjà premium, ajouter à la date existante
        if ($this->isPremium() && $this->premium_expires_at) {
            $newExpiration = $this->premium_expires_at->copy()->addDays($days);
        } else {
            $newExpiration = now()->addDays($days);
        }

        $this->update([
            'is_premium' => true,
            'premium_expires_at' => $newExpiration,
            'premium_source' => $source ?? $this->premium_source,
        ]);

        \Log::info('Premium days added to user', [
            'user_id' => $this->id,
            'days' => $days,
            'source' => $source,
            'expires_at' => $newExpiration,
        ]);
    }

    /**
     * Activer le premium pour un plan d'abonnement
     * 
     * @param string $plan weekly|monthly|quarterly
     */
    public function activatePremium(string $plan): void
    {
        $durations = [
            'weekly' => 7,
            'monthly' => 30,
            'quarterly' => 90,
        ];

        $days = $durations[$plan] ?? 7;
        $this->addPremiumDays($days, 'subscription_' . $plan);
    }

    /**
     * Accorder le premium à vie
     * 
     * @param string|null $source Source du premium (ex: referral)
     */
    public function grantLifetimePremium(?string $source = null): void
    {
        $this->update([
            'is_premium' => true,
            'premium_expires_at' => null, // null = à vie
            'premium_source' => $source ?? 'lifetime',
        ]);

        \Log::info('Lifetime premium granted', [
            'user_id' => $this->id,
            'source' => $source,
        ]);
    }

    /**
     * Révoquer le premium
     */
    public function revokePremium(): void
    {
        $this->update([
            'is_premium' => false,
            'premium_expires_at' => null,
        ]);

        \Log::info('Premium revoked', ['user_id' => $this->id]);
    }

    /**
     * Obtenir le nombre de jours premium restants
     */
    public function premiumDaysRemaining(): ?int
    {
        if (!$this->isPremium()) {
            return 0;
        }

        // Premium à vie
        if ($this->premium_expires_at === null) {
            return null; // null = illimité
        }

        return max(0, now()->diffInDays($this->premium_expires_at, false));
    }

    /**
     * Vérifier si l'utilisateur a le premium à vie
     */
    public function hasLifetimePremium(): bool
    {
        return $this->is_premium && $this->premium_expires_at === null;
    }

    /**
     * Accessor pour subscription_expires_at (alias de premium_expires_at)
     */
    public function getSubscriptionExpiresAtAttribute()
    {
        return $this->premium_expires_at;
    }
}
