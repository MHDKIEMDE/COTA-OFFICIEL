<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssFetcherService
{
    /**
     * Fetch et stocke les articles d'une source RSS.
     * Retourne le nombre de nouveaux articles créés.
     */
    public function fetchSource(NewsSource $source): int
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'COTA News Bot/1.0'])
                ->get($source->rss_url);

            if (!$response->successful()) {
                Log::warning("RssFetcher: erreur {$response->status()} pour {$source->name}");
                return 0;
            }

            $xml     = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            $items   = $xml->channel->item ?? $xml->entry ?? [];
            $created = 0;

            foreach ($items as $item) {
                $guid  = (string) ($item->guid ?? $item->id ?? $item->link ?? '');
                $title = html_entity_decode(strip_tags((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $url   = (string) ($item->link ?? $item->id ?? '');

                if (empty($title) || empty($url)) continue;

                // Skip si déjà en base
                if (NewsArticle::where('guid', $guid ?: $url)->exists()) continue;

                $description = html_entity_decode(
                    strip_tags((string) ($item->description ?? $item->summary ?? '')),
                    ENT_QUOTES | ENT_HTML5, 'UTF-8'
                );

                $imageUrl    = $this->extractImage($item);
                $publishedAt = $this->parseDate((string) ($item->pubDate ?? $item->published ?? $item->updated ?? ''));
                $tags        = $this->extractTags($title . ' ' . $description);

                NewsArticle::create([
                    'news_source_id' => $source->id,
                    'title'          => Str::limit($title, 255),
                    'url'            => $url,
                    'guid'           => $guid ?: $url,
                    'description'    => Str::limit($description, 500),
                    'image_url'      => $imageUrl,
                    'published_at'   => $publishedAt,
                    'tags'           => $tags,
                    'is_active'      => true,
                ]);

                $created++;
            }

            $source->update([
                'last_fetched_at' => now(),
                'articles_count'  => NewsArticle::where('news_source_id', $source->id)->count(),
            ]);

            Log::info("RssFetcher: {$source->name} → {$created} nouveaux articles");
            return $created;

        } catch (\Throwable $e) {
            Log::error("RssFetcher: erreur pour {$source->name}", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Fetch toutes les sources actives.
     */
    public function fetchAll(): array
    {
        $results = [];
        $sources = NewsSource::active()->get();

        foreach ($sources as $source) {
            $results[$source->slug] = $this->fetchSource($source);
        }

        return $results;
    }

    // ── Helpers privés ─────────────────────────────────────────────────────────

    private function extractImage(\SimpleXMLElement $item): ?string
    {
        // media:content
        $namespaces = $item->getNamespaces(true);
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if (isset($media->content) && isset($media->content->attributes()['url'])) {
                return (string) $media->content->attributes()['url'];
            }
        }

        // enclosure
        if (isset($item->enclosure)) {
            $type = (string) $item->enclosure->attributes()['type'] ?? '';
            if (str_starts_with($type, 'image/')) {
                return (string) $item->enclosure->attributes()['url'];
            }
        }

        // img dans description
        $desc = (string) ($item->description ?? $item->summary ?? '');
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $desc, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function parseDate(string $dateStr): ?\Carbon\Carbon
    {
        if (empty($dateStr)) return null;
        try {
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractTags(string $text): array
    {
        // Mots-clés football courants pour le matching
        $footballTerms = [
            'Real Madrid', 'Barcelona', 'Arsenal', 'Chelsea', 'Manchester',
            'PSG', 'Liverpool', 'Juventus', 'Bayern', 'Ligue 1', 'Premier League',
            'Champions League', 'La Liga', 'Serie A', 'Bundesliga',
            'Sénégal', 'Côte d\'Ivoire', 'Maroc', 'AFCON', 'CAN',
        ];

        $found = [];
        foreach ($footballTerms as $term) {
            if (stripos($text, $term) !== false) {
                $found[] = $term;
            }
        }

        return array_slice($found, 0, 10);
    }
}
