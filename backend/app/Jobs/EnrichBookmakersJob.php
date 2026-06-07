<?php

namespace App\Jobs;

use App\Models\Bookmaker;
use App\Services\BookmakerEnrichmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job hebdomadaire : enrichit les fiches bookmakers via Claude.
 * Dispatch : EnrichBookmakersJob::dispatch()
 * Scheduler : weekly (dimanche minuit)
 */
class EnrichBookmakersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout    = 300;
    public int $tries      = 1;

    public function __construct(
        private readonly bool $force = false,
    ) {}

    public function handle(BookmakerEnrichmentService $service): void
    {
        $bookmakers = Bookmaker::active()->get();

        Log::info("[EnrichBookmakersJob] Démarrage — {$bookmakers->count()} bookmakers à traiter.");

        $updated = 0;
        foreach ($bookmakers as $bm) {
            $ok = $service->enrich($bm, $this->force);
            if ($ok) $updated++;
            sleep(1); // éviter le rate-limit Claude
        }

        Log::info("[EnrichBookmakersJob] Terminé — {$updated}/{$bookmakers->count()} mis à jour.");
    }
}
