<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private array $allowedProviders = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->allowedProviders), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, $this->allowedProviders), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['social' => 'Connexion ' . ucfirst($provider) . ' annulée ou échouée.']);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Mettre à jour le social_id si pas encore enregistré
            $idField = $provider . '_id';
            if (!$user->{$idField}) {
                $user->update([$idField => $socialUser->getId()]);
            }
        } else {
            $user = User::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                'email'             => $socialUser->getEmail(),
                "{$provider}_id"    => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
                'referral_code'     => strtoupper(Str::random(8)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }
}
