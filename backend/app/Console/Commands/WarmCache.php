<?php

namespace App\Console\Commands;

use App\Models\AppConfig;
use App\Models\Bookmaker;
use App\Models\BookmakerBlog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmCache extends Command
{
    protected $signature   = 'cache:warm';
    protected $description = 'Pré-chauffe les clés Redis les plus fréquentes (bookmakers, config)';

    public function handle(): int
    {
        $this->info('Démarrage du cache warming…');

        // Config app — TTL 10 min
        Cache::put('app:config', AppConfig::allAsArray(), 600);
        $this->line('  ✓ app:config');

        // Bookmakers actifs — TTL 1h
        $bookmakers = Bookmaker::active()->ordered()->get()->map(fn($b) => [
            'id'              => $b->id,
            'name'            => $b->name,
            'slug'            => $b->slug,
            'primary_color'   => $b->primary_color,
            'secondary_color' => $b->secondary_color,
            'affiliate_link'  => $b->affiliate_link,
            'download_link'   => $b->download_link,
            'logo_url'        => $b->logo_url,
            'description'     => $b->description,
        ]);
        Cache::put('bookmakers:list:global', $bookmakers, 3600);
        $this->line('  ✓ bookmakers:list:global (' . $bookmakers->count() . ' entrées)');

        // Blogs bookmakers actifs — TTL 24h
        $blogs = BookmakerBlog::with('bookmaker')->active()
            ->whereHas('bookmaker', fn($q) => $q->where('is_active', true))
            ->get();
        foreach ($blogs as $blog) {
            Cache::put("bookmaker:blog:{$blog->bookmaker_id}", $blog, 86400);
        }
        $this->line('  ✓ bookmaker:blog:{id} (' . $blogs->count() . ' blogs)');

        $this->info('Cache warming terminé.');
        return self::SUCCESS;
    }
}
