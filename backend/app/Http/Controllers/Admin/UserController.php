<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount(['subscriptions', 'referrals', 'feedbacks']);

        // Filtres
        if ($request->filled('status')) {
            if ($request->status === 'premium') {
                $query->where('is_premium', true);
            } elseif ($request->status === 'free') {
                $query->where('is_premium', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Stats
        $stats = [
            'total' => User::count(),
            'premium' => User::where('is_premium', true)->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'active_7_days' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $user->loadCount(['subscriptions', 'referrals', 'feedbacks', 'affiliationBonus']);
        $user->load(['subscriptions' => fn($q) => $q->latest()->take(5)]);
        $user->load(['referrals' => fn($q) => $q->with('referredUser')->latest()->take(10)]);
        $user->load(['affiliationBonus' => fn($q) => $q->latest()]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'is_premium' => 'boolean',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
        ]);

        $validated['is_premium'] = $request->boolean('is_premium');
        $validated['is_admin'] = $request->boolean('is_admin');

        // Seul un super_admin peut promouvoir un autre super_admin
        if (isset($validated['is_super_admin']) && $validated['is_super_admin']) {
            abort_unless(auth()->user()?->is_super_admin, 403, 'Action réservée aux super-admins.');
        }
        $validated['is_super_admin'] = $request->boolean('is_super_admin');

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Ajouter des jours premium
     */
    public function addPremiumDays(Request $request, User $user)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:255',
        ]);

        $user->addPremiumDays($validated['days'], 'admin_grant');

        return back()->with('success', "{$validated['days']} jours premium ajoutés.");
    }

    /**
     * Accorder le premium à vie
     */
    public function grantLifetimePremium(User $user)
    {
        $user->grantLifetimePremium('admin_grant');

        return back()->with('success', 'Premium à vie accordé.');
    }

    /**
     * Révoquer le premium
     */
    public function revokePremium(User $user)
    {
        $user->revokePremium();

        return back()->with('success', 'Premium révoqué.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        if ($user->is_super_admin) {
            return back()->with('error', 'Impossible de supprimer un super admin.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    /**
     * Exporter les utilisateurs (CSV)
     */
    public function export(Request $request)
    {
        Log::info('Admin CSV export', [
            'admin_id'   => auth()->id(),
            'admin_name' => auth()->user()?->name,
            'ip'         => request()->ip(),
            'at'         => now()->toIso8601String(),
        ]);

        $users = User::select(
            'id','name','email','phone','is_premium','premium_expires_at',
            'referral_code','created_at','last_login_at'
        )->withCount('referrals as referral_count')->lazy(500);

        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // En-têtes
        fputcsv($handle, [
            'ID', 'Nom', 'Email', 'Téléphone', 'Premium', 'Premium Expire', 
            'Code Parrainage', 'Parrainages', 'Inscrit le', 'Dernière connexion'
        ]);

        // Données
        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->is_premium ? 'Oui' : 'Non',
                $user->premium_expires_at?->format('d/m/Y'),
                $user->referral_code,
                $user->referral_count,
                $user->created_at->format('d/m/Y H:i'),
                $user->last_login_at?->format('d/m/Y H:i'),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}

