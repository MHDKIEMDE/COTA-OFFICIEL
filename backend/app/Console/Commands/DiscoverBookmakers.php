<?php

namespace App\Console\Commands;

use App\Jobs\FetchBookmakerCandidatesJob;
use App\Models\BookmakerCandidate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DiscoverBookmakers extends Command
{
    protected $signature   = 'bookmakers:discover {--notify : Envoyer notification email aux admins}';
    protected $description = 'Auto-découverte des bookmakers depuis les APIs externes (API-Football, OddsAPI)';

    public function handle(): int
    {
        $this->info('Lancement de la découverte des bookmakers...');

        $before = BookmakerCandidate::where('status', 'pending')->count();

        // Exécution synchrone du job pour avoir le résultat en ligne de commande
        (new FetchBookmakerCandidatesJob())->handle();

        $after    = BookmakerCandidate::where('status', 'pending')->count();
        $newCount = max(0, $after - $before);

        $this->line("Candidats en attente : <comment>{$after}</comment> ({$newCount} nouveau(x) détecté(s))");

        Cache::put('last_run_discover_bookmakers', now()->toIso8601String(), now()->addDays(7));

        if ($newCount > 0) {
            $this->info("{$newCount} nouveau(x) bookmaker(s) détecté(s) — notification en cours...");
            $this->notifyAdmins($newCount);
        } else {
            $this->comment('Aucun nouveau bookmaker détecté.');
        }

        return self::SUCCESS;
    }

    private function notifyAdmins(int $count): void
    {
        $admins = User::where('is_super_admin', true)
            ->whereNotNull('email')
            ->get();

        if ($admins->isEmpty()) {
            Log::warning('DiscoverBookmakers: aucun super admin avec email trouvé.');
            return;
        }

        foreach ($admins as $admin) {
            try {
                Mail::raw(
                    $this->buildEmailBody($count),
                    function ($message) use ($admin, $count) {
                        $message
                            ->to($admin->email, $admin->name)
                            ->subject("[COTA Admin] {$count} nouveau(x) bookmaker(s) détecté(s)");
                    }
                );
                $this->line("Email envoyé à {$admin->email}");
            } catch (\Exception $e) {
                Log::error('DiscoverBookmakers: erreur envoi email', [
                    'admin' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('DiscoverBookmakers: notifications envoyées', [
            'count'  => $count,
            'admins' => $admins->pluck('email')->toArray(),
        ]);
    }

    private function buildEmailBody(int $count): string
    {
        $url = url('/admin/bookmaker-candidates');

        return <<<TEXT
Bonjour,

{$count} nouveau(x) bookmaker(s) ont été détectés automatiquement par COTA.

Ces bookmakers attendent votre validation dans le tableau de bord admin :
{$url}

Vous pouvez approuver ou rejeter chaque candidat depuis la liste d'attente.

— COTA Bot
TEXT;
    }
}
