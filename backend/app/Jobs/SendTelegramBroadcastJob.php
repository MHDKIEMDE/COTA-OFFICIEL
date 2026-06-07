<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Models\Prediction;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(TelegramService $telegram): void
    {
        // Récupérer les picks du jour
        $picks = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->where('confidence_stars', '>=', 2)
            ->orderByDesc('total_score')
            ->limit(5)
            ->get();

        if ($picks->isEmpty()) {
            Log::info('SendTelegramBroadcast: aucun pick publié, broadcast annulé');
            return;
        }

        // Construire le message
        $totalOdds = $picks->reduce(fn($carry, $p) => $carry * (float) $p->odds, 1.0);
        $gain      = number_format((int) round($totalOdds * 1000), 0, '.', ' ');
        $totalOdds = number_format($totalOdds, 2);

        $msg = "⚽ <b>PICKS COTA DU JOUR</b>\n\n";

        foreach ($picks as $p) {
            $stars = str_repeat('⭐', $p->confidence_stars);
            $odds  = number_format((float) $p->odds, 2);
            $msg  .= "• <b>{$p->home_team} vs {$p->away_team}</b>\n"
                   . "  🎯 {$p->prediction} @{$odds} {$stars}\n\n";
        }

        $msg .= "🎟 Coupon : <b>@{$totalOdds}</b> → {$gain} FCFA pour 1 000 misés\n\n"
              . "📲 <a href=\"https://cotafoot.com\">Voir l'analyse complète</a>";

        // Envoyer à tous les utilisateurs avec telegram_id
        $users = User::whereNotNull('telegram_id')->get();

        $sent   = 0;
        $failed = 0;

        foreach ($users as $user) {
            $ok = $telegram->sendMessage((int) $user->telegram_id, $msg);
            $ok ? $sent++ : $failed++;

            // Pause 50ms entre chaque envoi (limite Telegram : 30 msg/sec)
            usleep(50_000);
        }

        Log::info('SendTelegramBroadcast: terminé', [
            'picks'  => $picks->count(),
            'sent'   => $sent,
            'failed' => $failed,
        ]);
    }
}
