<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private readonly NewsService $news) {}

    /**
     * GET /api/news
     * Articles sport général (football + autres sports).
     *
     * Params : lang (fr|en), limit (max 40)
     */
    public function index(Request $request): JsonResponse
    {
        $lang    = in_array($request->query('lang', 'fr'), ['fr', 'en']) ? $request->query('lang', 'fr') : 'fr';
        $limit   = min((int) $request->query('limit', 20), 40);
        $type    = $request->query('type', 'sport'); // 'sport' | 'football'

        $articles = $type === 'football'
            ? $this->news->getFootballNews($lang, $limit)
            : $this->news->getTopSportNews($lang, $limit);

        return response()->json([
            'success' => true,
            'data'    => $articles,
            'meta'    => [
                'count' => count($articles),
                'lang'  => $lang,
                'type'  => $type,
            ],
        ]);
    }

    /**
     * GET /api/news/football
     * Articles football uniquement.
     */
    public function football(Request $request): JsonResponse
    {
        $lang    = in_array($request->query('lang', 'fr'), ['fr', 'en']) ? $request->query('lang', 'fr') : 'fr';
        $limit   = min((int) $request->query('limit', 20), 40);
        $articles = $this->news->getFootballNews($lang, $limit);

        return response()->json([
            'success' => true,
            'data'    => $articles,
            'meta'    => ['count' => count($articles), 'lang' => $lang],
        ]);
    }

    /**
     * GET /api/news/search?q=...&lang=fr&limit=15
     * Recherche d'articles par équipe, joueur ou compétition.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['success' => false, 'message' => 'Paramètre q requis (min 2 caractères)'], 422);
        }

        $lang    = in_array($request->query('lang', 'fr'), ['fr', 'en']) ? $request->query('lang', 'fr') : 'fr';
        $limit   = min((int) $request->query('limit', 15), 30);
        $articles = $this->news->searchNews($query, $lang, $limit);

        return response()->json([
            'success' => true,
            'data'    => $articles,
            'meta'    => ['count' => count($articles), 'query' => $query, 'lang' => $lang],
        ]);
    }

    /**
     * GET /api/news/player/{name}
     * Articles sur un joueur.
     */
    public function player(Request $request, string $name): JsonResponse
    {
        $lang     = in_array($request->query('lang', 'fr'), ['fr', 'en']) ? $request->query('lang', 'fr') : 'fr';
        $limit    = min((int) $request->query('limit', 10), 20);
        $articles = $this->news->getPlayerNews(urldecode($name), $lang, $limit);

        return response()->json([
            'success' => true,
            'data'    => $articles,
            'meta'    => ['count' => count($articles), 'player' => $name],
        ]);
    }

    /**
     * GET /api/news/competition/{name}
     * Articles sur une compétition (Champions League, Ligue 1...).
     */
    public function competition(Request $request, string $name): JsonResponse
    {
        $lang     = in_array($request->query('lang', 'fr'), ['fr', 'en']) ? $request->query('lang', 'fr') : 'fr';
        $limit    = min((int) $request->query('limit', 15), 30);
        $articles = $this->news->getCompetitionNews(urldecode($name), $lang, $limit);

        return response()->json([
            'success' => true,
            'data'    => $articles,
            'meta'    => ['count' => count($articles), 'competition' => $name],
        ]);
    }
}
