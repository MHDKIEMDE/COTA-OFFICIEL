<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchBookmakerCandidatesJob;
use App\Models\Bookmaker;
use App\Models\BookmakerCandidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookmakerCandidateController extends Controller
{
    // ── Liste d'attente ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $candidates = BookmakerCandidate::with('reviewer')
            ->where('status', $status)
            ->latest()
            ->paginate(20);

        $counts = [
            'pending'  => BookmakerCandidate::where('status', 'pending')->count(),
            'approved' => BookmakerCandidate::where('status', 'approved')->count(),
            'rejected' => BookmakerCandidate::where('status', 'rejected')->count(),
        ];

        return view('admin.bookmaker_candidates.index', compact('candidates', 'counts', 'status'));
    }

    // ── Détail / fiche de révision ─────────────────────────────────────────────

    public function show(BookmakerCandidate $candidate): View
    {
        return view('admin.bookmaker_candidates.show', compact('candidate'));
    }

    // ── Valider → crée ou lie un Bookmaker ────────────────────────────────────

    public function approve(Request $request, BookmakerCandidate $candidate): RedirectResponse
    {
        if (!$candidate->isPending()) {
            return back()->with('error', 'Ce candidat a déjà été traité.');
        }

        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'affiliate_link'    => 'nullable|url|max:500',
            'download_link'     => 'nullable|url|max:500',
            'promo_code'        => 'nullable|string|max:50',
            'primary_color'     => 'nullable|string|max:20',
            'bonus_label'       => 'nullable|string|max:255',
            'bonus_description' => 'nullable|string|max:2000',
            'description'       => 'nullable|string|max:1000',
            'logo_url'          => 'nullable|url|max:500',
            'min_deposit'       => 'nullable|integer|min:0',
            'min_withdrawal'    => 'nullable|integer|min:0',
            'rating'            => 'nullable|numeric|min:0|max:5',
        ]);

        // Crée le bookmaker dans la table principale
        $bookmaker = Bookmaker::create([
            'name'            => $data['name'],
            'slug'            => Str::slug($data['name']),
            'primary_color'   => $data['primary_color'] ?? $candidate->primary_color ?? '#E8FF36',
            'affiliate_link'  => $data['affiliate_link'] ?? null,
            'download_link'   => $data['download_link'] ?? null,
            'description'     => $data['description'] ?? $candidate->description,
            'logo_url'        => $data['logo_url'] ?? $candidate->logo_url,
            'bonus_label'     => $data['bonus_label'] ?? $candidate->bonus_label,
            'min_deposit'     => $data['min_deposit'] ?? null,
            'min_withdrawal'  => $data['min_withdrawal'] ?? null,
            'rating'          => $data['rating'] ?? null,
            'is_active'       => true,
            'sort_order'      => Bookmaker::max('sort_order') + 1,
        ]);

        // Si un code promo est fourni, créer un blog
        if (!empty($data['promo_code'])) {
            \App\Models\BookmakerBlog::create([
                'bookmaker_id'     => $bookmaker->id,
                'promo_code'       => strtoupper($data['promo_code']),
                'bonus_title'      => $data['bonus_label'] ?? null,
                'bonus_description'=> $data['bonus_description'] ?? null,
                'cta_label'        => "S'inscrire sur {$bookmaker->name}",
                'steps'            => [],
                'is_active'        => true,
            ]);
        }

        // Marquer le candidat comme approuvé
        $candidate->update([
            'status'       => 'approved',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'bookmaker_id' => $bookmaker->id,
        ]);

        return redirect()
            ->route('admin.bookmaker-candidates.index')
            ->with('success', "✅ {$bookmaker->name} approuvé et ajouté aux bookmakers.");
    }

    // ── Rejeter ────────────────────────────────────────────────────────────────

    public function reject(Request $request, BookmakerCandidate $candidate): RedirectResponse
    {
        if (!$candidate->isPending()) {
            return back()->with('error', 'Ce candidat a déjà été traité.');
        }

        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $candidate->update([
            'status'           => 'rejected',
            'rejection_reason' => $data['rejection_reason'] ?? 'Non pertinent pour COTA.',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('success', "❌ Candidat rejeté.");
    }

    // ── Remettre en attente ────────────────────────────────────────────────────

    public function resetStatus(BookmakerCandidate $candidate): RedirectResponse
    {
        $candidate->update([
            'status'           => 'pending',
            'rejection_reason' => null,
            'reviewed_by'      => null,
            'reviewed_at'      => null,
            'bookmaker_id'     => null,
        ]);

        return back()->with('success', 'Candidat remis en attente.');
    }

    // ── Lancer le fetch manuel ─────────────────────────────────────────────────

    public function fetch(): RedirectResponse
    {
        FetchBookmakerCandidatesJob::dispatch();

        return back()->with('success', '🔄 Récupération lancée en arrière-plan. Revenez dans 1 minute.');
    }

    // ── Supprimer un candidat rejeté ───────────────────────────────────────────

    public function destroy(BookmakerCandidate $candidate): RedirectResponse
    {
        if ($candidate->isApproved()) {
            return back()->with('error', 'Impossible de supprimer un candidat approuvé.');
        }

        $candidate->delete();

        return back()->with('success', 'Candidat supprimé.');
    }
}
