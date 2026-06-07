<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmaker;
use App\Models\BookmakerBlog;
use App\Services\BookmakerEnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmakerBlogController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->get('category');
        $bmId     = $request->get('bookmaker_id');

        $query = BookmakerBlog::with('bookmaker')->latest('published_at');

        if ($category) $query->where('category', $category);
        if ($bmId)     $query->where('bookmaker_id', $bmId);

        $blogs      = $query->paginate(20);
        $bookmakers = Bookmaker::ordered()->get(['id', 'name']);

        return view('admin.bookmaker_blogs.index', compact('blogs', 'bookmakers', 'category', 'bmId'));
    }

    public function create(): View
    {
        $bookmakers = Bookmaker::active()->ordered()->get(['id', 'name', 'bonus_label']);
        $categories = BookmakerBlog::CATEGORIES;

        return view('admin.bookmaker_blogs.create', compact('bookmakers', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['published_at'] = $data['published_at'] ?? now();

        BookmakerBlog::create($data);

        return redirect()
            ->route('admin.bookmaker-blogs.index')
            ->with('success', '✅ Article créé avec succès.');
    }

    public function edit(BookmakerBlog $bookmakerBlog): View
    {
        $bookmakers = Bookmaker::active()->ordered()->get(['id', 'name']);
        $categories = BookmakerBlog::CATEGORIES;

        return view('admin.bookmaker_blogs.edit', [
            'blog'       => $bookmakerBlog,
            'bookmakers' => $bookmakers,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, BookmakerBlog $bookmakerBlog): RedirectResponse
    {
        $data = $this->validated($request);
        $bookmakerBlog->update($data);

        return redirect()
            ->route('admin.bookmaker-blogs.index')
            ->with('success', '✅ Article mis à jour.');
    }

    public function destroy(BookmakerBlog $bookmakerBlog): RedirectResponse
    {
        $bookmakerBlog->delete();

        return back()->with('success', 'Article supprimé.');
    }

    public function toggleFeatured(BookmakerBlog $bookmakerBlog): RedirectResponse
    {
        $bookmakerBlog->update(['is_featured' => !$bookmakerBlog->is_featured]);

        return back()->with('success', $bookmakerBlog->is_featured ? '⭐ Mis en avant.' : 'Retiré des mis en avant.');
    }

    /**
     * POST /admin/bookmaker-blogs/generate-ai
     * Génère title, excerpt et bonus_description via Claude Haiku.
     */
    public function generateWithAI(Request $request, BookmakerEnrichmentService $enrichment): JsonResponse
    {
        $validated = $request->validate([
            'bookmaker_id' => 'required|exists:bookmakers,id',
            'category'     => 'required|string',
        ]);

        $bookmaker = Bookmaker::findOrFail($validated['bookmaker_id']);

        $content = $enrichment->generateBlogContent(
            $bookmaker->name,
            $validated['category'],
            $bookmaker->bonus_label ?? '',
        );

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Génération impossible. Vérifiez que ANTHROPIC_API_KEY est configurée.',
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $content]);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'bookmaker_id'     => 'required|exists:bookmakers,id',
            'category'         => 'required|in:' . implode(',', array_keys(BookmakerBlog::CATEGORIES)),
            'title'            => 'nullable|string|max:255',
            'excerpt'          => 'nullable|string|max:500',
            'promo_code'       => 'nullable|string|max:50',
            'bonus_title'      => 'nullable|string|max:255',
            'bonus_description'=> 'nullable|string|max:3000',
            'cta_label'        => 'nullable|string|max:150',
            'media_url'        => 'nullable|url|max:500',
            'thumbnail_url'    => 'nullable|url|max:500',
            'steps'            => 'nullable|array',
            'steps.*.icon'     => 'nullable|string|max:10',
            'steps.*.title'    => 'nullable|string|max:200',
            'steps.*.body'     => 'nullable|string|max:1000',
            'is_active'        => 'boolean',
            'is_featured'      => 'boolean',
            'published_at'     => 'nullable|date',
        ]);
    }
}
