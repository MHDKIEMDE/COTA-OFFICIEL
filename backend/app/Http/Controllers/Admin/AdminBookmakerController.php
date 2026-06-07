<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmaker;
use App\Models\BookmakerBlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminBookmakerController extends Controller
{
    // ── API JSON (pour le frontend admin React si besoin) ──────────────────────

    public function index(): JsonResponse
    {
        $bookmakers = Bookmaker::ordered()->get();

        return response()->json(['success' => true, 'data' => $bookmakers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug']         = $data['slug'] ?? Str::slug($data['name']);
        $data['primary_color']= $data['primary_color'] ?? '#E8FF36';
        $data['is_active']    = $data['is_active'] ?? true;
        $data['sort_order']   = $data['sort_order'] ?? Bookmaker::max('sort_order') + 1;

        $bookmaker = Bookmaker::create($data);

        return response()->json(['success' => true, 'data' => $bookmaker], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bookmaker = Bookmaker::findOrFail($id);
        $data      = $this->validated($request, $id);
        $bookmaker->update($data);

        return response()->json(['success' => true, 'data' => $bookmaker->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        Bookmaker::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Bookmaker supprimé.']);
    }

    // ── Vues Blade ─────────────────────────────────────────────────────────────

    public function listView(): View
    {
        $bookmakers = Bookmaker::withCount('clicks')->ordered()->get();

        return view('admin.bookmakers.index', [
            'bookmakers'       => $bookmakers,
            'defaultBonusDays' => 7,
        ]);
    }

    public function editView(Bookmaker $bookmaker): View
    {
        $blog = BookmakerBlog::where('bookmaker_id', $bookmaker->id)
            ->where('is_active', true)
            ->first();

        return view('admin.bookmakers.edit', compact('bookmaker', 'blog'));
    }

    public function updateFull(Request $request, Bookmaker $bookmaker): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'primary_color'     => 'nullable|string|max:20',
            'affiliate_link'    => 'nullable|url|max:500',
            'download_link'     => 'nullable|url|max:500',
            'description'       => 'nullable|string|max:1000',
            'logo_url'          => 'nullable|url|max:500',
            'bonus_label'       => 'nullable|string|max:255',
            'min_deposit'       => 'nullable|integer|min:0',
            'min_withdrawal'    => 'nullable|integer|min:0',
            'rating'            => 'nullable|numeric|min:0|max:5',
            'sort_order'        => 'nullable|integer|min:0',
            'is_active'         => 'boolean',
            // Code promo / blog
            'promo_code'        => 'nullable|string|max:50',
            'bonus_description' => 'nullable|string|max:2000',
            'cta_label'         => 'nullable|string|max:150',
        ]);

        $bookmaker->update([
            'name'           => $data['name'],
            'primary_color'  => $data['primary_color'] ?? $bookmaker->primary_color,
            'affiliate_link' => $data['affiliate_link'] ?? null,
            'download_link'  => $data['download_link'] ?? null,
            'description'    => $data['description'] ?? null,
            'logo_url'       => $data['logo_url'] ?? null,
            'bonus_label'    => $data['bonus_label'] ?? null,
            'min_deposit'    => $data['min_deposit'] ?? null,
            'min_withdrawal' => $data['min_withdrawal'] ?? null,
            'rating'         => $data['rating'] ?? null,
            'sort_order'     => $data['sort_order'] ?? $bookmaker->sort_order,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        // Sync blog (code promo)
        if (!empty($data['promo_code'])) {
            BookmakerBlog::updateOrCreate(
                ['bookmaker_id' => $bookmaker->id, 'is_active' => true],
                [
                    'promo_code'       => strtoupper($data['promo_code']),
                    'bonus_title'      => $data['bonus_label'] ?? null,
                    'bonus_description'=> $data['bonus_description'] ?? null,
                    'cta_label'        => $data['cta_label'] ?? "S'inscrire sur {$bookmaker->name}",
                    'steps'            => [],
                ]
            );
        }

        return redirect()
            ->route('admin.bookmakers.list')
            ->with('success', "✅ {$bookmaker->name} mis à jour.");
    }

    public function toggleActive(Bookmaker $bookmaker): RedirectResponse
    {
        $bookmaker->update(['is_active' => !$bookmaker->is_active]);

        $label = $bookmaker->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "{$bookmaker->name} {$label}.");
    }

    // ── Validation partagée ────────────────────────────────────────────────────

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $unique = $ignoreId ? ',slug,' . $ignoreId : '';

        return $request->validate([
            'name'           => 'sometimes|required|string|max:150',
            'slug'           => "nullable|string|max:150|unique:bookmakers,slug{$unique}",
            'primary_color'  => 'nullable|string|max:20',
            'affiliate_link' => 'nullable|url|max:500',
            'download_link'  => 'nullable|url|max:500',
            'description'    => 'nullable|string|max:1000',
            'logo_url'       => 'nullable|url|max:500',
            'bonus_label'    => 'nullable|string|max:255',
            'min_deposit'    => 'nullable|integer|min:0',
            'min_withdrawal' => 'nullable|integer|min:0',
            'rating'         => 'nullable|numeric|min:0|max:5',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer|min:0',
        ]);
    }
}
