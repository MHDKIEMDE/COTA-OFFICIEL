<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ReferralService
{
    private const REWARD_TIERS = [
        1  => 3,
        5  => 7,
        10 => 30,
        20 => 45,
    ];

    public function getStats(User $user): array
    {
        $total = $this->countValidated($user->id);
        $daysEarned = (int) DB::table('referrals')
            ->where('referrer_id', $user->id)
            ->where('reward_applied', true)
            ->sum('reward_days');

        return [
            'referral_code'  => $user->referral_code,
            'share_link'     => config('app.url') . '/referral?code=' . $user->referral_code,
            'total_referrals'=> $total,
            'days_earned'    => $daysEarned,
            'next_reward'    => $this->nextReward($total),
        ];
    }

    public function listReferrals(User $user, int $page = 1): array
    {
        $paginator = DB::table('referrals')
            ->join('users', 'users.id', '=', 'referrals.referred_id')
            ->where('referrals.referrer_id', $user->id)
            ->orderBy('referrals.created_at', 'desc')
            ->select('users.name', 'referrals.status', 'referrals.reward_days', 'referrals.reward_applied', 'referrals.created_at', 'referrals.validated_at')
            ->paginate(20, ['*'], 'page', $page);

        return [
            'data' => collect($paginator->items())->map(fn ($r) => [
                'name'          => $r->name ?? 'Utilisateur',
                'status'        => $r->status,
                'reward_days'   => (int) $r->reward_days,
                'reward_applied'=> (bool) $r->reward_applied,
                'joined_at'     => $r->created_at,
                'validated_at'  => $r->validated_at,
            ])->values()->all(),
            'meta' => [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
    }

    public function apply(User $user, string $code): void
    {
        if (DB::table('referrals')->where('referred_id', $user->id)->exists()) {
            throw new \DomainException('Vous avez déjà utilisé un code de parrainage.');
        }

        $referrer = User::where('referral_code', strtoupper($code))->firstOrFail();

        if ($referrer->id === $user->id) {
            throw new \DomainException('Vous ne pouvez pas utiliser votre propre code.');
        }

        DB::table('referrals')->insert([
            'referrer_id'  => $referrer->id,
            'referred_id'  => $user->id,
            'status'       => 'validated',
            'validated_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $total = $this->countValidated($referrer->id);
        $this->grantReward($referrer, $total);

        Log::info('Referral applied', ['referrer' => $referrer->id, 'referred' => $user->id, 'total' => $total]);
    }

    private function countValidated(int $userId): int
    {
        return DB::table('referrals')->where('referrer_id', $userId)->where('status', 'validated')->count();
    }

    private function nextReward(int $total): ?array
    {
        foreach (self::REWARD_TIERS as $target => $days) {
            if ($total < $target) {
                return [
                    'target'    => $target,
                    'reward'    => $days >= 365 ? 'Premium à vie' : "{$days} jours premium",
                    'remaining' => $target - $total,
                    'days'      => $days,
                ];
            }
        }
        return null;
    }

    private function grantReward(User $referrer, int $total): void
    {
        $days = self::REWARD_TIERS[$total] ?? null;
        if (!$days) {
            return;
        }

        $isLifetime = $days >= 365;
        $expires = $isLifetime ? null : (
            ($referrer->is_premium && $referrer->premium_expires_at?->isFuture())
                ? Carbon::parse($referrer->premium_expires_at)->addDays($days)
                : now()->addDays($days)
        );

        $referrer->update([
            'is_premium'         => true,
            'premium_expires_at' => $expires,
            'premium_source'     => $isLifetime ? 'referral_lifetime' : 'referral',
        ]);

        DB::table('referrals')
            ->where('referrer_id', $referrer->id)
            ->where('status', 'validated')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->update(['reward_days' => $days, 'reward_applied' => true, 'reward_applied_at' => now(), 'updated_at' => now()]);
    }
}
