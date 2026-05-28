<?php

namespace App\Jobs;

use App\Models\AffiliationBonus;
use App\Models\User;
use App\Services\AffiliateControlService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job horaire — vérifie les inscriptions bookmakers en attente et active le Premium 7j.
 * Priorité critique §21.3 CDC V2.
 *
 * Deux chemins :
 *   1. Postback reçu (webhook) → registration_confirmed=true, bonus_activated=false → activer
 *   2. Player ID soumis manuellement (pending) → interroger AffiliateControlService → activer si trouvé
 */
class CheckBookmakerRegistrationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(AffiliateControlService $affiliateService): void
    {
        $activated = 0;
        $checked   = 0;

        // ── Chemin 1 : inscriptions confirmées via postback, bonus pas encore activé ──
        $pendingBonus = AffiliationBonus::where('registration_confirmed', true)
            ->where('bonus_activated', false)
            ->with('user')
            ->get();

        foreach ($pendingBonus as $affiliation) {
            if (!$affiliation->user) continue;
            $checked++;

            try {
                $affiliation->activateBonus();
                $activated++;

                Log::info('CheckBookmakerRegistrationsJob: bonus activé (postback)', [
                    'user_id'    => $affiliation->user_id,
                    'bookmaker'  => $affiliation->bookmaker,
                    'bonus_days' => $affiliation->getBonusDays(),
                ]);
            } catch (\Throwable $e) {
                Log::error('CheckBookmakerRegistrationsJob: erreur activation bonus', [
                    'affiliation_id' => $affiliation->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // ── Chemin 2 : player_id soumis manuellement, en attente de vérification API ──
        if ($affiliateService->isConfigured()) {
            $pendingManual = AffiliationBonus::where('registration_confirmed', false)
                ->whereNotNull('player_id')
                ->where(function ($q) {
                    $q->whereNull('tracking_details->status')
                      ->orWhere('tracking_details->status', 'pending_review');
                })
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->with('user')
                ->get();

            foreach ($pendingManual as $affiliation) {
                if (!$affiliation->user) continue;
                $checked++;

                try {
                    $result = $affiliateService->verifyPlayerConversion(
                        $affiliation->player_id,
                        $affiliation->bookmaker
                    );

                    if ($result['found']) {
                        $affiliation->confirmRegistration([
                            'playerId'   => $affiliation->player_id,
                            'eventType'  => 'registration',
                            'source'     => 'check_job',
                            'raw_data'   => $result['conversion'] ?? [],
                        ]);
                        $affiliation->activateBonus();
                        $activated++;

                        Log::info('CheckBookmakerRegistrationsJob: bonus activé (vérif API)', [
                            'user_id'   => $affiliation->user_id,
                            'bookmaker' => $affiliation->bookmaker,
                            'player_id' => $affiliation->player_id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('CheckBookmakerRegistrationsJob: erreur vérif API', [
                        'affiliation_id' => $affiliation->id,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('CheckBookmakerRegistrationsJob: terminé', [
            'checked'   => $checked,
            'activated' => $activated,
        ]);
    }
}
