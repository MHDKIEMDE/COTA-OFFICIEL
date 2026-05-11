<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliationBonus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    /**
     * Liste des affiliations
     */
    public function index(Request $request)
    {
        $query = AffiliationBonus::with('user');

        // Filtres
        if ($request->filled('bookmaker')) {
            $query->where('bookmaker', $request->bookmaker);
        }

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_verified', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('player_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Stats par bookmaker
        $bookmakerStats = AffiliationBonus::select('bookmaker', DB::raw('count(*) as total'))
            ->selectRaw('SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified')
            ->groupBy('bookmaker')
            ->get()
            ->keyBy('bookmaker');

        // Stats globales
        $stats = [
            'total' => AffiliationBonus::count(),
            'verified' => AffiliationBonus::where('is_verified', true)->count(),
            'pending' => AffiliationBonus::where('is_verified', false)->count(),
            'premium_given' => AffiliationBonus::where('bonus_applied', true)->sum('bonus_days'),
        ];

        $affiliations = $query->latest()->paginate(20);

        return view('admin.affiliates.index', compact('affiliations', 'stats', 'bookmakerStats'));
    }

    /**
     * Afficher une affiliation
     */
    public function show(AffiliationBonus $affiliation)
    {
        $affiliation->load('user');

        return view('admin.affiliates.show', compact('affiliation'));
    }

    /**
     * Vérifier manuellement une affiliation
     */
    public function verify(Request $request, AffiliationBonus $affiliation)
    {
        if ($affiliation->is_verified) {
            return back()->with('warning', 'Cette affiliation est déjà vérifiée.');
        }

        $affiliation->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        // Appliquer le bonus si pas déjà fait
        if (!$affiliation->bonus_applied && $affiliation->user) {
            $affiliation->user->addPremiumDays($affiliation->bonus_days, "affiliation_{$affiliation->bookmaker}");
            $affiliation->update(['bonus_applied' => true]);
        }

        return back()->with('success', 'Affiliation vérifiée et bonus appliqué.');
    }

    /**
     * Rejeter une affiliation
     */
    public function reject(Request $request, AffiliationBonus $affiliation)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $affiliation->update([
            'is_verified' => false,
            'rejection_reason' => $validated['reason'] ?? 'Rejeté par l\'administrateur',
        ]);

        return back()->with('success', 'Affiliation rejetée.');
    }

    /**
     * Supprimer une affiliation
     */
    public function destroy(AffiliationBonus $affiliation)
    {
        $affiliation->delete();

        return redirect()->route('admin.affiliates.index')
            ->with('success', 'Affiliation supprimée.');
    }

    /**
     * Vérification en masse
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'affiliation_ids' => 'required|array',
            'affiliation_ids.*' => 'exists:affiliations_bonus,id',
        ]);

        $affiliations = AffiliationBonus::whereIn('id', $validated['affiliation_ids'])
            ->where('is_verified', false)
            ->with('user')
            ->get();

        $count = 0;
        foreach ($affiliations as $affiliation) {
            $affiliation->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            if (!$affiliation->bonus_applied && $affiliation->user) {
                $affiliation->user->addPremiumDays($affiliation->bonus_days, "affiliation_{$affiliation->bookmaker}");
                $affiliation->update(['bonus_applied' => true]);
            }

            $count++;
        }

        return back()->with('success', "{$count} affiliations vérifiées.");
    }
}

