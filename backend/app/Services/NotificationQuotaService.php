<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Contrôle les quotas et préférences de notifications.
 *
 * Quotas journaliers :
 *   - Free    : max 3 notifications/jour
 *   - Premium : max 5 notifications/jour
 *   - VIP     : max 7 notifications/jour (is_admin = true)
 *
 * Silence nocturne : 23h–07h (heure locale de l'utilisateur).
 * Configurable par utilisateur via notification_preferences.
 */
class NotificationQuotaService
{
    private const QUOTA_FREE    = 3;
    private const QUOTA_PREMIUM = 5;
    private const QUOTA_VIP     = 7;

    /**
     * Vérifie si un utilisateur peut recevoir une notification de ce type.
     */
    public function canSend(User $user, string $type): bool
    {
        if (!$this->isWithinQuota($user)) {
            return false;
        }

        if ($this->isInQuietHours($user, $type)) {
            return false;
        }

        if (!$this->isTypeEnabled($user, $type)) {
            return false;
        }

        return true;
    }

    /**
     * Incrémente le compteur après envoi réussi.
     */
    public function increment(int $userId): void
    {
        $key = $this->quotaKey($userId);
        Cache::increment($key);

        // TTL jusqu'à minuit (renouvellement quotidien)
        $secondsUntilMidnight = Carbon::now()->diffInSeconds(Carbon::tomorrow());
        Cache::add($key, 0, $secondsUntilMidnight);
    }

    /**
     * Retourne les utilisateurs éligibles parmi une collection,
     * en filtrant selon quota + quiet hours + préférences.
     */
    public function filterEligible(\Illuminate\Support\Collection $users, string $type): \Illuminate\Support\Collection
    {
        return $users->filter(fn(User $user) => $this->canSend($user, $type));
    }

    private function isWithinQuota(User $user): bool
    {
        $quota = match (true) {
            $user->is_admin  => self::QUOTA_VIP,
            $user->is_premium => self::QUOTA_PREMIUM,
            default          => self::QUOTA_FREE,
        };

        $sent = (int) Cache::get($this->quotaKey($user->id), 0);

        return $sent < $quota;
    }

    private function isInQuietHours(User $user, string $type): bool
    {
        // Les notifications event urgent ignorent le silence
        if ($type === 'event') {
            return false;
        }

        $pref = NotificationPreference::where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        $quietStart = $pref?->quiet_hours_start ?? 23;
        $quietEnd   = $pref?->quiet_hours_end   ?? 7;

        $hour = (int) Carbon::now()->format('H');

        if ($quietStart > $quietEnd) {
            // Ex: 23h–07h (passe minuit)
            return $hour >= $quietStart || $hour < $quietEnd;
        }

        return $hour >= $quietStart && $hour < $quietEnd;
    }

    private function isTypeEnabled(User $user, string $type): bool
    {
        $pref = NotificationPreference::where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        // Si pas de préférence enregistrée → activé par défaut
        return $pref === null || $pref->enabled;
    }

    private function quotaKey(int $userId): string
    {
        return "notif_quota_{$userId}_" . Carbon::today()->toDateString();
    }
}
