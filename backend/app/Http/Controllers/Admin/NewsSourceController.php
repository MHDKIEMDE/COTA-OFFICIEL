<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchNewsSourcesJob;
use App\Models\NewsArticle;
use App\Models\NewsSource;
use App\Services\RssFetcherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsSourceController extends Controller
{
    public function index(): View
    {
        $sources = NewsSource::withCount('articles')->orderBy('name')->get();
        $totalArticles = NewsArticle::count();

        return view('admin.news_sources.index', compact('sources', 'totalArticles'));
    }

    public function create(): View
    {
        return view('admin.news_sources.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'slug'           => 'required|string|max:60|unique:news_sources,slug',
            'rss_url'        => 'required|url|max:500',
            'website_url'    => 'nullable|url|max:500',
            'logo_url'       => 'nullable|url|max:500',
            'language'       => 'required|in:fr,en,ar,pt',
            'category'       => 'required|in:football,sport,local',
            'fetch_interval' => 'required|integer|min:15|max:1440',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        NewsSource::create($data);

        return redirect()->route('admin.news-sources.index')
            ->with('success', "✅ Source \"{$data['name']}\" ajoutée.");
    }

    public function edit(NewsSource $newsSource): View
    {
        $recentArticles = $newsSource->articles()
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('admin.news_sources.edit', compact('newsSource', 'recentArticles'));
    }

    public function update(Request $request, NewsSource $newsSource): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'rss_url'        => 'required|url|max:500',
            'website_url'    => 'nullable|url|max:500',
            'logo_url'       => 'nullable|url|max:500',
            'language'       => 'required|in:fr,en,ar,pt',
            'category'       => 'required|in:football,sport,local',
            'fetch_interval' => 'required|integer|min:15|max:1440',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $newsSource->update($data);

        return back()->with('success', '✅ Source mise à jour.');
    }

    public function destroy(NewsSource $newsSource): RedirectResponse
    {
        $newsSource->delete();
        return redirect()->route('admin.news-sources.index')
            ->with('success', "Source \"{$newsSource->name}\" supprimée.");
    }

    public function toggle(NewsSource $newsSource): RedirectResponse
    {
        $newsSource->update(['is_active' => !$newsSource->is_active]);
        $state = $newsSource->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Source {$state}.");
    }

    public function fetchNow(NewsSource $newsSource, RssFetcherService $fetcher): RedirectResponse
    {
        $count = $fetcher->fetchSource($newsSource);
        return back()->with('success', "🔄 {$count} nouveaux articles récupérés depuis {$newsSource->name}.");
    }

    public function fetchAll(): RedirectResponse
    {
        FetchNewsSourcesJob::dispatch();
        return back()->with('success', '🔄 Fetch de toutes les sources lancé en arrière-plan.');
    }

    public function articles(NewsSource $newsSource): View
    {
        $articles = $newsSource->articles()
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('admin.news_sources.articles', compact('newsSource', 'articles'));
    }

    public function toggleArticle(NewsArticle $article): RedirectResponse
    {
        $article->update(['is_active' => !$article->is_active]);
        return back()->with('success', 'Article ' . ($article->is_active ? 'activé' : 'masqué') . '.');
    }
}
