<?php

namespace App\Livewire\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginForm extends Component
{
    public string $method = 'phone';
    public string $phone = '';
    public string $email = '';
    public string $identifier = ''; // Pour le formulaire unifié
    public string $country_code = '221'; // Code pays par défaut (Sénégal) - sans le +
    public string $selected_country = 'SN'; // Code ISO du pays
    public bool $isLoading = false;
    
    // Liste des pays avec codes et timezones
    public array $countries = [
        ['code' => '225', 'country' => 'CI', 'name' => 'Côte d\'Ivoire', 'flag' => '🇨🇮', 'timezone' => 'Africa/Abidjan'],
        ['code' => '229', 'country' => 'BJ', 'name' => 'Bénin', 'flag' => '🇧🇯', 'timezone' => 'Africa/Porto-Novo'],
        ['code' => '226', 'country' => 'BF', 'name' => 'Burkina Faso', 'flag' => '🇧🇫', 'timezone' => 'Africa/Ouagadougou'],
        ['code' => '223', 'country' => 'ML', 'name' => 'Mali', 'flag' => '🇲🇱', 'timezone' => 'Africa/Bamako'],
        ['code' => '221', 'country' => 'SN', 'name' => 'Sénégal', 'flag' => '🇸🇳', 'timezone' => 'Africa/Dakar'],
        ['code' => '228', 'country' => 'TG', 'name' => 'Togo', 'flag' => '🇹🇬', 'timezone' => 'Africa/Lome'],
        ['code' => '227', 'country' => 'NE', 'name' => 'Niger', 'flag' => '🇳🇪', 'timezone' => 'Africa/Niamey'],
        ['code' => '33', 'country' => 'FR', 'name' => 'France', 'flag' => '🇫🇷', 'timezone' => 'Europe/Paris'],
    ];
    
    public function mount()
    {
        // Détecter le pays par défaut basé sur la timezone du serveur ou la langue
        $this->detectDefaultCountry();
    }
    
    /**
     * Détecter le pays par défaut basé sur la timezone
     */
    protected function detectDefaultCountry(): void
    {
        try {
            $timezone = date_default_timezone_get();
            
            // Mapper les timezones aux pays
            $timezoneMap = [
                'Africa/Abidjan' => 'CI',
                'Africa/Porto-Novo' => 'BJ',
                'Africa/Ouagadougou' => 'BF',
                'Africa/Bamako' => 'ML',
                'Africa/Dakar' => 'SN',
                'Africa/Lome' => 'TG',
                'Africa/Niamey' => 'NE',
                'Europe/Paris' => 'FR',
            ];
            
            // Chercher le pays correspondant à la timezone
            foreach ($this->countries as $country) {
                if (isset($timezoneMap[$timezone]) && $country['country'] === $timezoneMap[$timezone]) {
                    $this->selected_country = $country['country'];
                    $this->country_code = $country['code'];
                    return;
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, garder les valeurs par défaut (SN)
        }
    }
    
    /**
     * Mettre à jour le code pays quand le pays est sélectionné
     */
    public function updatedSelectedCountry($value): void
    {
        foreach ($this->countries as $country) {
            if ($country['country'] === $value) {
                $this->country_code = $country['code'];
                break;
            }
        }
    }

    protected function rules()
    {
        if ($this->method === 'phone') {
            return [
                'phone' => 'required|string|min:8|max:20',
                'country_code' => 'nullable|string|max:5',
            ];
        }
        return ['email' => 'required|email'];
    }

    protected $messages = [
        'phone.required' => 'Le numéro de téléphone est requis.',
        'phone.min' => 'Le numéro de téléphone doit contenir au moins 8 chiffres.',
        'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'Veuillez entrer une adresse email valide.',
    ];

    public function switchMethod(string $method)
    {
        $this->method = $method;
        $this->resetErrorBag();
        $this->identifier = '';
    }

    /**
     * Méthode login appelée par le formulaire (alias pour sendOtp)
     */
    public function login()
    {
        return $this->sendOtp();
    }

    public function sendOtp()
    {
        // Si identifier est utilisé (ancien format), extraire phone/email
        if (!empty($this->identifier)) {
            if (filter_var($this->identifier, FILTER_VALIDATE_EMAIL)) {
                $this->email = $this->identifier;
                $this->method = 'email';
            } else {
                // Extraire le code pays du numéro si présent (format +226 70 00 00 00)
                $phone = preg_replace('/\s+/', '', $this->identifier);
                if (preg_match('/^\+(\d{1,3})(.+)$/', $phone, $matches)) {
                    $this->country_code = $matches[1];
                    $this->phone = $matches[2];
                } else {
                    // Si pas de code pays, utiliser le numéro tel quel avec le code par défaut
                    $this->phone = $phone;
                }
                $this->method = 'phone';
            }
        }

        $this->validate();
        $this->isLoading = true;

        try {
            if ($this->method === 'phone') {
                // Nettoyer le numéro de téléphone (enlever espaces, tirets, etc.)
                $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $this->phone);
                
                // Appeler l'API pour envoyer l'OTP avec timeout réduit
                $response = \Illuminate\Support\Facades\Http::timeout(10)->post(url('/api/auth/send-otp'), [
                    'phone' => $cleanPhone,
                    'country_code' => $this->country_code,
                ]);

                if ($response->successful()) {
                    session()->flash('success', 'Code OTP envoyé avec succès.');
                    session(['otp_phone' => $this->phone, 'otp_country_code' => $this->country_code]);
                    $this->isLoading = false;
                    return redirect()->route('auth.verify-otp');
                } else {
                    $error = $response->json('message') ?? 'Erreur lors de l\'envoi du code OTP.';
                    $this->addError('phone', $error);
                }
            } else {
                // Pour l'email, utiliser la logique existante
                $user = User::where('email', $this->email)->first();

                if (!$user) {
                    $user = User::create([
                        'email' => $this->email,
                        'name' => 'Utilisateur',
                        'referral_code' => strtoupper(Str::random(8)),
                    ]);
                }

                $ttlMinutes = (int) config('sms.otp_ttl_minutes', 10);
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = now()->addMinutes($ttlMinutes);

                $user->update([
                    'otp_code' => $otp,
                    'otp_expires_at' => $expiresAt,
                ]);

                try {
                    Mail::to($this->email)->send(new OtpMail($otp, $ttlMinutes));
                } catch (\Throwable $e) {
                    logger()->error("Échec envoi OTP email à {$this->email}", ['error' => $e->getMessage()]);
                }
                session(['otp_contact' => $this->email, 'otp_method' => 'email']);
                session()->flash('success', 'Code OTP envoyé par email.');
                $this->isLoading = false;
                return redirect()->route('auth.verify-otp');
            }
        } catch (\Exception $e) {
            $this->addError('identifier', 'Erreur: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
