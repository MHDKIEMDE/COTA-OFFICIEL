<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show register page
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Show OTP verification page
     */
    public function showVerifyOtp()
    {
        $contact = session('otp_contact');
        $method = session('otp_method', 'phone');

        if (!$contact) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', compact('contact', 'method'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Facebook OAuth callback
     */
    public function facebookCallback(Request $request)
    {
        // This would be implemented with Laravel Socialite
        return redirect('/login')->withErrors(['error' => 'Facebook login à configurer.']);
    }
}
