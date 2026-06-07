<?php

namespace App\Jobs;

use App\Services\RssFetcherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewsSourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RssFetcherService $fetcher): void
    {
        $results = $fetcher->fetchAll();
        $total   = array_sum($results);

        Log::info('FetchNewsSourcesJob: terminé', [
            'sources' => count($results),
            'new_articles' => $total,
            'detail' => $results,
        ]);
    }
}
