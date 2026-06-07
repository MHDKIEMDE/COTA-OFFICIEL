<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Nettoyage hebdomadaire de la base de données.
 *
 * Rétentions :
 *   predictions publiées   → 90 jours  (archive JSON avant suppression)
 *   matches                → 60 jours
 *   notifications          → 30 jours
 *   combined_bets          → 90 jours
 *   api_source_logs        → 30 jours
 *   user_coupons perdus    → 180 jours
 *   winning_coupons        → 365 jours
 */
class PruneOldDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('PruneOldDataJob: démarrage nettoyage hebdomadaire');

        $stats = [
            'predictions_archived' => $this->archiveAndPrunePredictions(),
            'matches_pruned'       => $this->pruneMatches(),
            'notifications_pruned' => $this->pruneNotifications(),
            'coupons_pruned'       => $this->pruneCombinedBets(),
            'api_logs_pruned'      => $this->pruneApiSourceLogs(),
            'user_coupons_pruned'  => $this->pruneUserCoupons(),
            'winning_coupons_pruned' => $this->pruneWinningCoupons(),
        ];

        Log::info('PruneOldDataJob: terminé', $stats);
    }

    // ── 1. Prédictions > 90 jours — archive JSON + suppression ───────────────

    private function archiveAndPrunePredictions(): int
    {
        $cutoff = Carbon::now()->subDays(90)->format('Y-m-d');

        $predictions = DB::table('predictions')
            ->where('is_published', true)
            ->where('match_date', '<', $cutoff)
            ->get();

        if ($predictions->isEmpty()) return 0;

        // Grouper par mois pour des archives lisibles
        $byMonth = $predictions->groupBy(fn($p) =>
            Carbon::parse($p->match_date)->format('Y-m')
        );

        foreach ($byMonth as $month => $rows) {
            $path = "archives/predictions/{$month}.json";

            // Fusionner avec l'archive existante si elle existe
            $existing = [];
            if (Storage::exists($path)) {
                $existing = json_decode(Storage::get($path), true) ?? [];
            }

            $newData = array_merge($existing, $rows->map(fn($p) => (array) $p)->toArray());
            Storage::put($path, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info("PruneOldDataJob: archivé {$month} → {$path} (" . count($newData) . " prédictions)");
        }

        // Supprimer après archivage
        $deleted = DB::table('predictions')
            ->where('is_published', true)
            ->where('match_date', '<', $cutoff)
            ->delete();

        return $deleted;
    }

    // ── 2. Matchs > 60 jours ─────────────────────────────────────────────────

    private function pruneMatches(): int
    {
        return DB::table('matches')
            ->where('match_date', '<', Carbon::now()->subDays(60))
            ->where('status', 'finished')
            ->delete();
    }

    // ── 3. Notifications > 30 jours ──────────────────────────────────────────

    private function pruneNotifications(): int
    {
        return DB::table('notifications')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->delete();
    }

    // ── 4. Coupons IA > 90 jours ─────────────────────────────────────────────

    private function pruneCombinedBets(): int
    {
        return DB::table('combined_bets')
            ->where('date', '<', Carbon::now()->subDays(90)->format('Y-m-d'))
            ->delete();
    }

    // ── 5. Logs API > 30 jours ───────────────────────────────────────────────

    private function pruneApiSourceLogs(): int
    {
        return DB::table('api_source_logs')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->delete();
    }

    // ── 6. Coupons utilisateur perdus > 180 jours ────────────────────────────

    private function pruneUserCoupons(): int
    {
        return DB::table('user_coupons')
            ->where('result', 'lost')
            ->where('created_at', '<', Carbon::now()->subDays(180))
            ->delete();
    }

    // ── 7. Coupons gagnants > 365 jours ──────────────────────────────────────

    private function pruneWinningCoupons(): int
    {
        return DB::table('winning_coupons')
            ->where('created_at', '<', Carbon::now()->subDays(365))
            ->delete();
    }
}
