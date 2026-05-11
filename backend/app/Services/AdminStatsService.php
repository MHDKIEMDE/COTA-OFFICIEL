<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class AdminStatsService
{
    public function overview(): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        return [
            'users'        => $this->userStats($today, $yesterday),
            'premium'      => $this->premiumStats(),
            'predictions'  => $this->predictionStats($today, $yesterday),
            'success_rate' => $this->successRate(),
        ];
    }

    private function userStats(Carbon $today, Carbon $yesterday): array
    {
        return [
            'total'     => User::count(),
            'today'     => User::whereDate('created_at', $today)->count(),
            'yesterday' => User::whereDate('created_at', $yesterday)->count(),
        ];
    }

    private function premiumStats(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastStart = Carbon::now()->subMonth()->startOfMonth();
        $lastEnd   = Carbon::now()->subMonth()->endOfMonth();

        $active = User::where('is_premium', true)
            ->where(fn ($q) => $q->whereNull('premium_expires_at')->orWhere('premium_expires_at', '>', now()))
            ->count();

        $thisMonthNew  = User::where('is_premium', true)->where('created_at', '>=', $thisMonth)->count();
        $lastMonthNew  = User::where('is_premium', true)->whereBetween('created_at', [$lastStart, $lastEnd])->count();

        $trend = $lastMonthNew > 0 ? round((($thisMonthNew - $lastMonthNew) / $lastMonthNew) * 100) : 0;

        return ['active' => $active, 'trend' => $trend];
    }

    private function predictionStats(Carbon $today, Carbon $yesterday): array
    {
        return [
            'today'     => DB::table('predictions')->where('is_published', true)->whereDate('created_at', $today)->count(),
            'yesterday' => DB::table('predictions')->where('is_published', true)->whereDate('created_at', $yesterday)->count(),
        ];
    }

    private function successRate(): array
    {
        $make = fn (Carbon $from, ?Carbon $to = null) => DB::table('predictions')
            ->whereIn('result', ['correct', 'incorrect'])
            ->where('match_date', '>=', $from)
            ->when($to, fn ($q) => $q->where('match_date', '<', $to))
            ->selectRaw('COUNT(*) as total, SUM(result = \'correct\') as correct')
            ->first();

        $current  = $make(Carbon::now()->subDays(30));
        $previous = $make(Carbon::now()->subDays(60), Carbon::now()->subDays(30));

        $rate     = $current && $current->total  > 0 ? round(($current->correct  / $current->total)  * 100, 1) : 0;
        $prevRate = $previous && $previous->total > 0 ? round(($previous->correct / $previous->total) * 100, 1) : 0;

        return ['rate' => $rate, 'prev_rate' => $prevRate];
    }
}
