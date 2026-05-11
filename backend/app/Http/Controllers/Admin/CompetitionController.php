<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    /**
     * Liste toutes les compétitions
     */
    public function index(Request $request)
    {
        $query = Competition::query();

        // Filtres
        if ($request->filled('trending')) {
            if ($request->trending === '1') {
                $query->trending();
            } else {
                $query->where('is_trending', false)
                      ->where(function ($q) {
                          $q->whereNull('trending_start')
                            ->orWhereNull('trending_end')
                            ->orWhereRaw('? NOT BETWEEN trending_start AND trending_end', [now()->toDateString()]);
                      });
            }
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->active === '1');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $competitions = $query->ordered()->paginate(20);

        // Stats
        $stats = [
            'total' => Competition::count(),
            'active' => Competition::active()->count(),
            'trending' => Competition::active()->trending()->count(),
        ];

        return view('admin.competitions.index', compact('competitions', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.competitions.create');
    }

    /**
     * Enregistrer une nouvelle compétition
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sportradar_id' => 'required|string|unique:competitions,sportradar_id',
            'name' => 'required|string|max:100',
            'full_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:10',
            'priority' => 'required|integer|min:1|max:99',
            'is_active' => 'boolean',
            'is_trending' => 'boolean',
            'trending_start' => 'nullable|date',
            'trending_end' => 'nullable|date|after_or_equal:trending_start',
            'description' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_trending'] = $request->boolean('is_trending');

        Competition::create($validated);

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Compétition créée avec succès!');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Competition $competition)
    {
        return view('admin.competitions.edit', compact('competition'));
    }

    /**
     * Mettre à jour une compétition
     */
    public function update(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'sportradar_id' => 'required|string|unique:competitions,sportradar_id,' . $competition->id,
            'name' => 'required|string|max:100',
            'full_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:10',
            'priority' => 'required|integer|min:1|max:99',
            'is_active' => 'boolean',
            'is_trending' => 'boolean',
            'trending_start' => 'nullable|date',
            'trending_end' => 'nullable|date|after_or_equal:trending_start',
            'description' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_trending'] = $request->boolean('is_trending');

        $competition->update($validated);

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Compétition mise à jour avec succès!');
    }

    /**
     * Supprimer une compétition
     */
    public function destroy(Competition $competition)
    {
        $competition->delete();

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Compétition supprimée avec succès!');
    }

    /**
     * Activer/désactiver le statut tendance rapidement
     */
    public function toggleTrending(Competition $competition)
    {
        $competition->update(['is_trending' => !$competition->is_trending]);

        $status = $competition->is_trending ? 'activée' : 'désactivée';
        return back()->with('success', "Tendance {$status} pour {$competition->name}");
    }

    /**
     * Activer/désactiver le statut actif rapidement
     */
    public function toggleActive(Competition $competition)
    {
        $competition->update(['is_active' => !$competition->is_active]);

        $status = $competition->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Compétition {$status}: {$competition->name}");
    }

    /**
     * Définir les dates de tendance
     */
    public function setTrendingPeriod(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'trending_start' => 'required|date',
            'trending_end' => 'required|date|after_or_equal:trending_start',
        ]);

        $competition->update($validated);

        return back()->with('success', "Période tendance définie pour {$competition->name}");
    }

    /**
     * Vider le cache des compétitions
     */
    public function clearCache()
    {
        Competition::clearCache();
        return back()->with('success', 'Cache des compétitions vidé');
    }
}

