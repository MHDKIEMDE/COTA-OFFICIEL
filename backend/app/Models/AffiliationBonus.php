<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Model pour gérer les bonus d'affiliation bookmakers
 * 
 * Intégration avec AffiliateControl pour tracker les inscriptions
 * sur les bookmakers partenaires (BetWinner, 1xBet, Melbet, etc.)
 * 
 * Flux:
 * 1. Utilisateur clique sur lien affilié (avec extid = user_id)
 * 2. Utilisateur s'inscrit sur le bookmaker
 * 3. AffiliateControl envoie un postback à notre webhook
 * 4. On active le bonus premium (7 jours par défaut)
 */
class AffiliationBonus extends Model
{
    protected $table = 'affiliations_bonus';

    protected $fillable = [
        'user_id',
        'bookmaker',
        'bookmaker_custom_name',
        'affiliate_link',
        'clicks_count',
        'clicked_at',
        'user_ip',
        'user_agent',
        'registration_confirmed',
        'registration_confirmed_at',
        'bonus_activated',
        'bonus_activated_at',
        'bonus_expires_at',
        'is_verified',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'bonus_applied',
        'bonus_days',
        'player_id',
        'event_type',
        'revenue',
        'request_id',
        'tracking_details',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'registration_confirmed' => 'boolean',
        'registration_confirmed_at' => 'datetime',
        'bonus_activated' => 'boolean',
        'bonus_activated_at' => 'datetime',
        'bonus_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'bonus_applied' => 'boolean',
        'revenue' => 'decimal:2',
        'tracking_details' => 'array',
    ];

    /**
     * Bookmakers partenaires
     */
    const BOOKMAKER_BETWINNER = 'betwinner';
    const BOOKMAKER_1XBET = '1xbet';
    const BOOKMAKER_MELBET = 'melbet';
    const BOOKMAKER_OTHER = 'other';

    const BOOKMAKERS = [
        self::BOOKMAKER_BETWINNER => [
            'name' => 'BetWinner',
            'bonus_days' => 7,
            'tracking_domain' => 'bwredir.com',
        ],
        self::BOOKMAKER_1XBET => [
            'name' => '1xBet',
            'bonus_days' => 7,
            'tracking_domain' => '1xbetaffiliates.com',
        ],
        self::BOOKMAKER_MELBET => [
            'name' => 'Melbet',
            'bonus_days' => 7,
            'tracking_domain' => 'melredir.com',
        ],
    ];

    /**
     * Types d'événements AffiliateControl
     */
    const EVENT_REGISTRATION = 'registration';
    const EVENT_FIRST_DEPOSIT = 'firstDeposit';
    const EVENT_DEPOSIT = 'deposit';

    /**
     * Jours de bonus par défaut
     */
    const DEFAULT_BONUS_DAYS = 7;

    /**
     * Relation: Le bonus appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Bonus activés
     */
    public function scopeActivated($query)
    {
        return $query->where('bonus_activated', true);
    }

    /**
     * Scope: Inscriptions confirmées
     */
    public function scopeRegistrationConfirmed($query)
    {
        return $query->where('registration_confirmed', true);
    }

    /**
     * Scope: Par bookmaker
     */
    public function scopeByBookmaker($query, string $bookmaker)
    {
        return $query->where('bookmaker', $bookmaker);
    }

    /**
     * Scope: Par utilisateur
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Bonus actifs (non expirés)
     */
    public function scopeActiveBonus($query)
    {
        return $query->where('bonus_activated', true)
                     ->where('bonus_expires_at', '>', now());
    }

    /**
     * Enregistrer un clic sur un lien affilié
     */
    public function recordClick(?string $ip = null, ?string $userAgent = null): void
    {
        $this->increment('clicks_count');
        $this->update([
            'clicked_at' => now(),
            'user_ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Confirmer l'inscription sur le bookmaker (via postback AffiliateControl)
     */
    public function confirmRegistration(array $postbackData = []): void
    {
        $this->update([
            'registration_confirmed' => true,
            'registration_confirmed_at' => now(),
            'player_id' => $postbackData['playerId'] ?? null,
            'event_type' => $postbackData['eventType'] ?? self::EVENT_REGISTRATION,
            'revenue' => $postbackData['revenue'] ?? null,
            'request_id' => $postbackData['requestId'] ?? null,
            'tracking_details' => array_merge(
                $this->tracking_details ?? [],
                ['postback_data' => $postbackData, 'confirmed_at' => now()->toIso8601String()]
            ),
        ]);

        \Log::info('Affiliation registration confirmed', [
            'user_id' => $this->user_id,
            'bookmaker' => $this->bookmaker,
            'player_id' => $postbackData['playerId'] ?? null,
        ]);
    }

    /**
     * Activer le bonus premium
     */
    public function activateBonus(?int $bonusDays = null): void
    {
        if ($this->bonus_activated) {
            \Log::warning('Bonus already activated', ['affiliation_id' => $this->id]);
            return;
        }

        $days = $bonusDays ?? $this->getBonusDays();
        $expiresAt = now()->addDays($days);

        $this->update([
            'bonus_activated' => true,
            'bonus_activated_at' => now(),
            'bonus_expires_at' => $expiresAt,
        ]);

        // Activer le premium pour l'utilisateur
        $this->user->addPremiumDays($days, 'affiliation_' . $this->bookmaker);

        \Log::info('Affiliation bonus activated', [
            'user_id' => $this->user_id,
            'bookmaker' => $this->bookmaker,
            'bonus_days' => $days,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Obtenir le nombre de jours de bonus pour ce bookmaker
     */
    public function getBonusDays(): int
    {
        return self::BOOKMAKERS[$this->bookmaker]['bonus_days'] ?? self::DEFAULT_BONUS_DAYS;
    }

    /**
     * Obtenir le nom du bookmaker
     */
    public function getBookmakerName(): string
    {
        if ($this->bookmaker === self::BOOKMAKER_OTHER) {
            return $this->bookmaker_custom_name ?? 'Autre';
        }

        return self::BOOKMAKERS[$this->bookmaker]['name'] ?? $this->bookmaker;
    }

    /**
     * Vérifier si le bonus est actif
     */
    public function isBonusActive(): bool
    {
        return $this->bonus_activated 
            && $this->bonus_expires_at 
            && $this->bonus_expires_at->isFuture();
    }

    /**
     * Vérifier si l'utilisateur a déjà un bonus pour ce bookmaker
     */
    public static function hasExistingBonus(int $userId, string $bookmaker): bool
    {
        return self::where('user_id', $userId)
            ->where('bookmaker', $bookmaker)
            ->where('registration_confirmed', true)
            ->exists();
    }

    /**
     * Créer ou récupérer un enregistrement d'affiliation pour un utilisateur
     */
    public static function getOrCreateForUser(int $userId, string $bookmaker, string $affiliateLink): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'bookmaker' => $bookmaker,
            ],
            [
                'affiliate_link' => $affiliateLink,
                'clicks_count' => 0,
            ]
        );
    }

    /**
     * Traiter un postback AffiliateControl
     * 
     * @param array $data Données du postback
     * @return bool Succès du traitement
     */
    public static function processPostback(array $data): bool
    {
        $extid = $data['extid'] ?? null;
        $eventType = $data['eventType'] ?? $data['event'] ?? null;

        if (!$extid) {
            \Log::warning('Affiliation postback missing extid', ['data' => $data]);
            return false;
        }

        // Trouver l'enregistrement d'affiliation
        $affiliation = self::where('user_id', $extid)->first();

        if (!$affiliation) {
            \Log::warning('Affiliation record not found for postback', [
                'extid' => $extid,
                'data' => $data,
            ]);
            return false;
        }

        // Confirmer l'inscription
        $affiliation->confirmRegistration($data);

        // Activer le bonus si c'est une inscription ou premier dépôt
        if (in_array($eventType, [self::EVENT_REGISTRATION, self::EVENT_FIRST_DEPOSIT])) {
            $affiliation->activateBonus();
        }

        return true;
    }

    /**
     * Générer un lien d'affiliation tracké pour un utilisateur
     * 
     * @param User $user
     * @param string $bookmaker
     * @param string $baseTrackingUrl URL de tracking AffiliateControl
     * @return string
     */
    public static function generateTrackingLink(User $user, string $bookmaker, string $baseTrackingUrl): string
    {
        // Ajouter l'extid (user_id) et les subids au lien
        $separator = str_contains($baseTrackingUrl, '?') ? '&' : '?';
        
        return $baseTrackingUrl . $separator . http_build_query([
            'extid' => $user->id,
            'subid1' => 'cota_app',
            'subid2' => $bookmaker,
            'subid3' => now()->format('Ymd'),
        ]);
    }
}

