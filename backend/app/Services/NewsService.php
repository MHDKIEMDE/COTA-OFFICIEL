<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Actualités sportives gratuites depuis Google News RSS et BBC Sport RSS.
 * Aucune clé API requise — parsing XML direct.
 *
 * Endpoints disponibles :
 *   - getTopSportNews(lang)         → top 20 articles sport en FR ou EN
 *   - getFootballNews(lang)         → football général
 *   - getTeamNews(teamName, lang)   → articles liés à une équipe/joueur
 *   - getPlayerNews(playerName)     → articles sur un joueur
 *   - getCompetitionNews(name)      → articles sur une compétition
 */
class NewsService
{
    private const CACHE_TTL = 1800; // 30 min

    // ── Sources RSS ──────────────────────────────────────────────────────────

    private const RSS_SOURCES = [
        'fr' => [
            'football_general' => 'https://news.google.com/rss/search?q=football+sport&hl=fr&gl=FR&ceid=FR:fr',
            'foot_ligue1'      => 'https://news.google.com/rss/search?q=ligue+1+football&hl=fr&gl=FR&ceid=FR:fr',
            'foot_euro'        => 'https://news.google.com/rss/search?q=football+champions+league&hl=fr&gl=FR&ceid=FR:fr',
            'sport_general'    => 'https://news.google.com/rss/search?q=sport+actualite&hl=fr&gl=FR&ceid=FR:fr',
        ],
        'en' => [
            'football_general' => 'https://feeds.bbci.co.uk/sport/football/rss.xml',
            'sport_general'    => 'https://feeds.bbci.co.uk/sport/rss.xml',
        ],
    ];

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Articles football de la journée (plusieurs sources).
     *
     * @param string $lang  'fr' | 'en'
     * @param int    $limit Nombre d'articles max
     */
    public function getFootballNews(string $lang = 'fr', int $limit = 20): array
    {
        $cacheKey = "news_football_{$lang}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lang, $limit) {
            $urls = $lang === 'en'
                ? [self::RSS_SOURCES['en']['football_general']]
                : [
                    self::RSS_SOURCES['fr']['football_general'],
                    self::RSS_SOURCES['fr']['foot_ligue1'],
                    self::RSS_SOURCES['fr']['foot_euro'],
                ];

            $articles = $this->fetchMultipleFeeds($urls);
            $articles = $this->deduplicateByTitle($articles);

            return array_slice($articles, 0, $limit);
        });
    }

    /**
     * Articles sport général (football + autres sports).
     */
    public function getTopSportNews(string $lang = 'fr', int $limit = 20): array
    {
        $cacheKey = "news_sport_top_{$lang}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lang, $limit) {
            $urls = $lang === 'en'
                ? [self::RSS_SOURCES['en']['sport_general']]
                : [
                    self::RSS_SOURCES['fr']['sport_general'],
                    self::RSS_SOURCES['fr']['football_general'],
                ];

            $articles = $this->fetchMultipleFeeds($urls);
            $articles = $this->deduplicateByTitle($articles);

            return array_slice($articles, 0, $limit);
        });
    }

    /**
     * Articles sur une équipe ou un joueur.
     *
     * @param string $query  Nom d'équipe ou joueur (ex: "PSG", "Mbappé")
     * @param string $lang   'fr' | 'en'
     * @param int    $limit  Nombre max d'articles
     */
    public function searchNews(string $query, string $lang = 'fr', int $limit = 15): array
    {
        $slug     = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $query));
        $cacheKey = "news_search_{$slug}_{$lang}_{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $lang, $limit) {
            $encoded = urlencode($query . ' football sport');

            if ($lang === 'en') {
                $url = "https://news.google.com/rss/search?q={$encoded}&hl=en&gl=US&ceid=US:en";
            } else {
                $url = "https://news.google.com/rss/search?q={$encoded}&hl=fr&gl=FR&ceid=FR:fr";
            }

            $articles = $this->parseFeed($url);

            return array_slice($articles, 0, $limit);
        });
    }

    /**
     * Articles sur une compétition (Champions League, Ligue 1, etc.)
     */
    public function getCompetitionNews(string $competition, string $lang = 'fr', int $limit = 15): array
    {
        return $this->searchNews($competition, $lang, $limit);
    }

    /**
     * Articles sur un joueur spécifique.
     */
    public function getPlayerNews(string $playerName, string $lang = 'fr', int $limit = 10): array
    {
        return $this->searchNews($playerName, $lang, $limit);
    }

    // ── Parsing RSS interne ───────────────────────────────────────────────────

    private function fetchMultipleFeeds(array $urls): array
    {
        $all = [];
        foreach ($urls as $url) {
            try {
                $articles = $this->parseFeed($url);
                $all      = array_merge($all, $articles);
            } catch (\Throwable $e) {
                Log::warning("NewsService::fetchMultipleFeeds failed [{$url}]: " . $e->getMessage());
            }
        }

        // Trier par date décroissante
        usort($all, fn($a, $b) => strtotime($b['published_at'] ?? '0') - strtotime($a['published_at'] ?? '0'));

        return $all;
    }

    private function parseFeed(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout'     => 8,
                'user_agent'  => 'Mozilla/5.0 (compatible; COTA/1.0)',
                'header'      => "Accept: application/rss+xml, application/xml, text/xml\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || empty($raw)) {
            return [];
        }

        $xml = @simplexml_load_string($raw);
        if ($xml === false) {
            return [];
        }

        $items    = $xml->channel->item ?? $xml->entry ?? [];
        $articles = [];

        foreach ($items as $item) {
            $title    = (string) ($item->title ?? '');
            $link     = (string) ($item->link ?? '');
            $pubDate  = (string) ($item->pubDate ?? $item->updated ?? '');
            $desc     = strip_tags((string) ($item->description ?? $item->summary ?? ''));
            $source   = (string) ($item->source ?? '');
            $imgUrl   = null;

            // Extraire image si disponible (enclosure ou media:content)
            if (isset($item->enclosure) && !empty((string) $item->enclosure['url'])) {
                $imgUrl = (string) $item->enclosure['url'];
            } elseif (isset($item->children('media', true)->content)) {
                $imgUrl = (string) $item->children('media', true)->content['url'];
            }

            if (empty($title) || empty($link)) {
                continue;
            }

            $articles[] = [
                'title'        => $this->cleanTitle($title),
                'url'          => $link,
                'description'  => mb_substr($desc, 0, 280),
                'published_at' => $pubDate ? date('c', strtotime($pubDate)) : null,
                'source'       => $source ?: $this->extractDomain($link),
                'image_url'    => $imgUrl,
            ];
        }

        return $articles;
    }

    private function deduplicateByTitle(array $articles): array
    {
        $seen = [];
        $out  = [];

        foreach ($articles as $article) {
            $key = md5(mb_strtolower($article['title']));
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $out[]      = $article;
            }
        }

        return $out;
    }

    private function cleanTitle(string $title): string
    {
        // Google News ajoute " - Source" à la fin
        $title = preg_replace('/\s+-\s+[^-]+$/', '', $title);
        return html_entity_decode(trim($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        return str_replace('www.', '', $host);
    }
}
