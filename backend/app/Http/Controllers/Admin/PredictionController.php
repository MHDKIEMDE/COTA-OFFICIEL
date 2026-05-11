<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prediction;
use App\Models\FootballMatch;

class PredictionController extends Controller
{
    /**
     * Liste des pronostics
     */
    public function index(Request $request)
    {
        $query = Prediction::query()->with('match');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('prediction_type')) {
            $query->where('prediction_type', $request->prediction_type);
        }

        if ($request->filled('is_premium')) {
            $query->where('is_premium', $request->is_premium === 'true');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('match_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('match_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                  ->orWhere('away_team', 'like', "%{$search}%")
                  ->orWhere('competition', 'like', "%{$search}%");
            });
        }

        // Stats pour les filtres
        $stats = [
            'total' => Prediction::count(),
            'pending' => Prediction::where('status', 'pending')->count(),
            'won' => Prediction::where('status', 'won')->count(),
            'lost' => Prediction::where('status', 'lost')->count(),
        ];

        $predictions = $query->latest('match_date')->paginate(20);

        return view('admin.predictions.index', compact('predictions', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $matches = FootballMatch::where('match_date', '>', now())
            ->orderBy('match_date')
            ->take(50)
            ->get();

        return view('admin.predictions.create', compact('matches'));
    }

    /**
     * Enregistrer un nouveau pronostic
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'competition' => 'required|string|max:255',
            'match_date' => 'required|date|after:now',
            'prediction_type' => 'required|string|in:1X2,Over/Under,BTTS,Double Chance,HT/FT',
            'prediction_value' => 'required|string|max:255',
            'odds' => 'required|numeric|min:1.01',
            'confidence' => 'required|integer|min:1|max:4',
            'analysis' => 'nullable|string',
            'is_premium' => 'boolean',
            'is_combined' => 'boolean',
        ]);

        $validated['status'] = 'pending';
        $validated['is_premium'] = $request->boolean('is_premium');
        $validated['is_combined'] = $request->boolean('is_combined');

        $prediction = Prediction::create($validated);

        return redirect()->route('admin.predictions.index')
            ->with('success', 'Pronostic créé avec succès.');
    }

    /**
     * Afficher un pronostic
     */
    public function show(Prediction $prediction)
    {
        return view('admin.predictions.show', compact('prediction'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Prediction $prediction)
    {
        return view('admin.predictions.edit', compact('prediction'));
    }

    /**
     * Mettre à jour un pronostic
     */
    public function update(Request $request, Prediction $prediction)
    {
        $validated = $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'competition' => 'required|string|max:255',
            'match_date' => 'required|date',
            'prediction_type' => 'required|string',
            'prediction_value' => 'required|string|max:255',
            'odds' => 'required|numeric|min:1.01',
            'confidence' => 'required|integer|min:1|max:4',
            'analysis' => 'nullable|string',
            'is_premium' => 'boolean',
            'is_combined' => 'boolean',
            'status' => 'required|string|in:pending,won,lost,cancelled',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $validated['is_premium'] = $request->boolean('is_premium');
        $validated['is_combined'] = $request->boolean('is_combined');

        $prediction->update($validated);

        return redirect()->route('admin.predictions.index')
            ->with('success', 'Pronostic mis à jour avec succès.');
    }

    /**
     * Supprimer un pronostic
     */
    public function destroy(Prediction $prediction)
    {
        $prediction->delete();

        return redirect()->route('admin.predictions.index')
            ->with('success', 'Pronostic supprimé avec succès.');
    }

    /**
     * Mettre à jour le statut rapidement
     */
    public function updateStatus(Request $request, Prediction $prediction)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,won,lost,cancelled',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $prediction->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $prediction->status]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Mise à jour en masse des statuts
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'prediction_ids' => 'required|array',
            'prediction_ids.*' => 'exists:predictions,id',
            'status' => 'required|in:pending,won,lost,cancelled',
        ]);

        Prediction::whereIn('id', $validated['prediction_ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', count($validated['prediction_ids']) . ' pronostics mis à jour.');
    }
}

