<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\PromoCodeUse;
use App\Services\Sms\SmsService;

class AuthController extends Controller
{
    /**
     * Envoyer un code OTP par SMS
     * POST /api/auth/send-otp
     */
    public function sendOtp(Request $request)
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20',
            'country_code' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            Log::warning('OTP send validation failed', [
                'phone' => $request->phone,
                'errors' => $validator->errors()->toArray(),
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        Log::info('OTP send request', [
            'phone' => $request->phone,
            'country_code' => $request->country_code,
        ]);

        $phone = $request->phone;
        
        // Nettoyer le numéro (enlever espaces, tirets, etc.)
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Rate limiting: 1 SMS par minute par numéro (utiliser le numéro nettoyé)
        $key = 'otp:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ], 429);
        }

        // Générer code OTP à 6 chiffres
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $ttlMinutes = (int) config('sms.otp_ttl_minutes', 5);
        $expiresAt = Carbon::now()->addMinutes($ttlMinutes);

        // Extraire le code pays du numéro si présent dans le format +22670123456
        $countryCode = $request->country_code;
        $cleanPhone = $phone;
        
        // Si le numéro commence par +, extraire le code pays
        if (preg_match('/^\+(\d{1,3})(.+)$/', $phone, $matches)) {
            $countryCode = $matches[1];
            $cleanPhone = $matches[2];
        }
        
        // Si pas de code pays fourni, utiliser SN par défaut
        $countryCode = $countryCode ?? 'SN';

        // Trouver ou créer l'utilisateur (chercher les deux formats : avec et sans préfixe)
        $user = User::where('phone', $cleanPhone)
                    ->orWhere('phone', $phone)
                    ->orWhere('phone', '+' . $countryCode . $cleanPhone)
                    ->first();

        if (!$user) {
            // Créer un nouvel utilisateur
            $user = User::create([
                'phone' => $cleanPhone,
                'name' => 'User_' . substr($cleanPhone, -4),
                'country_code' => $countryCode,
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
                'otp_attempts' => 0,
            ]);

            // Enregistrer l'utilisation du code promo si fourni
            $promoCode = $request->input('promo_code');
            $activeCode = \App\Models\AppConfig::where('key', 'promo_code')->value('value') ?? 'CMD1122';
            if ($promoCode && strtoupper(trim($promoCode)) === strtoupper(trim($activeCode))) {
                PromoCodeUse::create([
                    'promo_code' => strtoupper(trim($promoCode)),
                    'user_id'    => $user->id,
                    'bookmaker'  => $request->input('bookmaker'),
                    'phone'      => $cleanPhone,
                    'used_at'    => now(),
                ]);
            }
        } else {
            // Mettre à jour l'OTP pour utilisateur existant
            $user->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
                'otp_attempts' => 0,
            ]);
        }

        // Envoyer le SMS via provider configuré
        // Par défaut: provider=log (dev). En prod: SMS_PROVIDER=termii + TERMII_API_KEY.
        try {
            /** @var SmsService $sms */
            $sms = app(SmsService::class);
            // Utiliser le numéro complet avec code pays pour l'envoi SMS
            $fullPhoneNumber = '+' . $countryCode . $cleanPhone;
            $sms->sendOtp($fullPhoneNumber, $otpCode, $ttlMinutes);
        } catch (\Throwable $e) {
            \Log::error('Erreur envoi OTP SMS', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible d’envoyer le code OTP pour le moment. Réessayez plus tard.',
            ], 500);
        }

        RateLimiter::hit($key, 60); // 60 secondes

        return response()->json([
            'success' => true,
            'message' => 'Code OTP envoyé avec succès.',
            'expires_in' => $ttlMinutes * 60,
        ]);
    }

    /**
     * Vérifier le code OTP et connecter l'utilisateur
     * POST /api/auth/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp_code' => 'required|string|size:6',
            'device_type' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('OTP verify validation failed', [
                'phone' => $request->phone,
                'errors' => $validator->errors()->toArray(),
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        Log::info('OTP verify request', [
            'phone' => $request->phone,
        ]);

        $rawPhone  = preg_replace('/[\s\-\(\)]/', '', $request->phone);
        $cleanVerify = $rawPhone;
        if (preg_match('/^\+(\d{1,3})(.+)$/', $rawPhone, $vm)) {
            $cleanVerify = $vm[2];
        }
        $user = User::where('phone', $rawPhone)
                    ->orWhere('phone', $cleanVerify)
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone non trouvé.',
            ], 404);
        }

        // Vérifier si OTP expiré
        if (!$user->otp_expires_at || Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Le code OTP a expiré. Demandez un nouveau code.',
            ], 400);
        }

        // Vérifier le code OTP
        if ($user->otp_code !== $request->otp_code) {
            // Incrémenter tentatives
            $user->increment('otp_attempts');

            if ($user->otp_attempts >= 3) {
                // Bloquer après 3 tentatives échouées
                $user->update([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Trop de tentatives échouées. Demandez un nouveau code.',
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Code OTP incorrect.',
                'attempts_left' => 3 - $user->otp_attempts,
            ], 400);
        }

        // Code valide - connecter l'utilisateur
        $user->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
            'otp_attempts'   => 0,
            'last_login_at'  => Carbon::now(),
            'device_type'    => $request->device_type,
            'fcm_token'      => $request->fcm_token,
        ]);

        // Générer token Sanctum
        $token = $user->createToken('mobile-app')->plainTextToken;

        $needsRegistration = !$user->registration_completed || !$user->pin_set;

        return response()->json([
            'success'              => true,
            'message'              => $needsRegistration ? 'OTP vérifié. Complétez votre inscription.' : 'Connexion réussie.',
            'needs_registration'   => $needsRegistration,
            'user'  => $this->userArray($user),
            'token' => $token,
        ]);
    }

    /**
     * Connexion via Facebook OAuth
     * POST /api/auth/facebook
     */
    public function loginWithFacebook(Request $request)
    {
        $startTime = microtime(true);
        
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
            'device_type' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Facebook login validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        Log::info('Facebook login request');

        // Vérifier le token Facebook via Graph API
        try {
            $response = Http::get('https://graph.facebook.com/me', [
                'fields' => 'id,name,email',
                'access_token' => $request->access_token,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Facebook invalide.',
                ], 401);
            }

            $facebookUser = $response->json();

            // Trouver ou créer l'utilisateur
            $user = User::where('facebook_id', $facebookUser['id'])->first();

            if (!$user) {
                // Vérifier si email existe déjà
                if (isset($facebookUser['email'])) {
                    $user = User::where('email', $facebookUser['email'])->first();
                }

                if (!$user) {
                    // Créer nouvel utilisateur
                    $user = User::create([
                        'facebook_id' => $facebookUser['id'],
                        'name' => $facebookUser['name'],
                        'email' => $facebookUser['email'] ?? null,
                        'device_type' => $request->device_type,
                        'fcm_token' => $request->fcm_token,
                        'last_login_at' => Carbon::now(),
                    ]);
                } else {
                    // Lier Facebook ID à utilisateur existant
                    $user->update([
                        'facebook_id' => $facebookUser['id'],
                        'last_login_at' => Carbon::now(),
                    ]);
                }
            } else {
                // Mettre à jour dernière connexion
                $user->update([
                    'last_login_at' => Carbon::now(),
                    'device_type' => $request->device_type,
                    'fcm_token' => $request->fcm_token,
                ]);
            }

            // Générer token Sanctum
            $token = $user->createToken('mobile-app')->plainTextToken;

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("POST /api/auth/facebook - {$duration}ms", [
                'user_id' => $user->id,
                'facebook_id' => $facebookUser['id'],
                'success' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion Facebook réussie.',
                'user'  => $this->userArray($user),
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("POST /api/auth/facebook - {$duration}ms - Error: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion Facebook.',
            ], 500);
        }
    }

    /**
     * Déconnexion (révocation du token)
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Obtenir le profil de l'utilisateur connecté
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->userArray($user),
        ]);
    }

    /**
     * Vérifier si un numéro a déjà un compte
     * POST /api/auth/check-phone
     */
    public function checkPhone(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $phone = preg_replace('/[\s\-\(\)]/', '', $request->phone);
        $clean = $phone;
        if (preg_match('/^\+(\d{1,3})(.+)$/', $phone, $m)) {
            $clean = $m[2];
        }

        $user = User::where('phone', $phone)->orWhere('phone', $clean)->first();

        return response()->json([
            'success'       => true,
            'exists'        => (bool) $user,
            'pin_set'       => $user ? (bool) $user->pin_set : false,
            'registration_completed' => $user ? (bool) $user->registration_completed : false,
        ]);
    }

    /**
     * Compléter l'inscription (nom + PIN)
     * POST /api/auth/complete-registration
     */
    public function completeRegistration(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:50',
            'pin'  => 'required|string|size:4|regex:/^\d{4}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->update([
            'name'                    => $request->name,
            'pin'                     => Hash::make($request->pin),
            'pin_set'                 => true,
            'registration_completed'  => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription complétée.',
            'user'    => [
                'id'                     => $user->id,
                'name'                   => $user->name,
                'phone'                  => $user->phone,
                'is_premium'             => $user->isPremium(),
                'registration_completed' => true,
                'pin_set'                => true,
            ],
        ]);
    }

    /**
     * Connexion avec PIN (après saisie du numéro)
     * POST /api/auth/login-pin
     */
    public function loginWithPin(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:20',
            'pin'   => 'required|string|size:4|regex:/^\d{4}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $phone = preg_replace('/[\s\-\(\)]/', '', $request->phone);
        $clean = $phone;
        if (preg_match('/^\+(\d{1,3})(.+)$/', $phone, $m)) {
            $clean = $m[2];
        }

        $user = User::where('phone', $phone)->orWhere('phone', $clean)->first();

        if (!$user || !$user->pin_set) {
            return response()->json([
                'success' => false,
                'message' => 'Compte introuvable ou PIN non configuré.',
            ], 404);
        }

        if (!Hash::check($request->pin, $user->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN incorrect.',
            ], 401);
        }

        $user->update(['last_login_at' => Carbon::now()]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'user'    => [
                'id'                     => $user->id,
                'name'                   => $user->name,
                'phone'                  => $user->phone,
                'is_premium'             => $user->isPremium(),
                'premium_expires_at'     => $user->premium_expires_at,
                'referral_code'          => $user->referral_code,
                'registration_completed' => $user->registration_completed,
                'pin_set'                => $user->pin_set,
                'can_access_welcome_combined' => $user->canAccessWelcomeCombined(),
            ],
            'token' => $token,
        ]);
    }

    private function userArray(User $user): array
    {
        return [
            'id'                          => $user->id,
            'name'                        => $user->name,
            'email'                       => $user->email,
            'phone'                       => $user->phone,
            'is_premium'                  => $user->isPremium(),
            'premium_expires_at'          => $user->premium_expires_at,
            'premium_source'              => $user->premium_source,
            'referral_code'               => $user->referral_code ?? '',
            'referral_count'              => $user->referral_count ?? 0,
            'referral_days_earned'        => $user->referral_days_earned ?? 0,
            'registration_completed'      => (bool) $user->registration_completed,
            'pin_set'                     => (bool) $user->pin_set,
            'can_access_welcome_combined' => $user->canAccessWelcomeCombined(),
            'last_login_at'               => $user->last_login_at,
        ];
    }

    /**
     * Méthode privée pour envoyer SMS (à implémenter avec Twilio/Termii)
     */
    private function sendSms($phone, $message)
    {
        // Deprecated: l'envoi SMS est désormais géré par App\Services\Sms\SmsService + provider (Termii/Log).
        \Log::warning('AuthController::sendSms est dépréciée; utiliser SmsService', ['to' => $phone]);
        return false;
    }
}
