<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        if (auth()->check() && auth()->user()->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Les identifiants sont incorrects.',
            ])->withInput($request->only('email'));
        }

        if (!$user->is_super_admin) {
            return back()->withErrors([
                'email' => 'Vous n\'avez pas les droits d\'accès.',
            ])->withInput($request->only('email'));
        }

        Auth::login($user, $request->filled('remember'));
        
        $user->update(['admin_last_login_at' => now()]);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login')->with('success', 'Déconnexion réussie.');
    }
}

