<?php

namespace App\Livewire\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Referral;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $method = 'phone';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $referral_code = '';
    public bool $isLoading = false;

    public function mount()
    {
        // Check if there's a referral code in the URL
        $this->referral_code = request()->query('ref', '');
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|min:2',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ];

        if ($this->method === 'phone') {
            $rules['phone'] = 'required|string|min:8|unique:users,phone';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'Le nom est requis.',
        'name.min' => 'Le nom doit contenir au moins 2 caractères.',
        'phone.required' => 'Le numéro de téléphone est requis.',
        'phone.min' => 'Le numéro de téléphone doit contenir au moins 8 chiffres.',
        'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'Veuillez entrer une adresse email valide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',
        'referral_code.exists' => 'Ce code de parrainage n\'existe pas.',
    ];

    public function switchMethod(string $method)
    {
        $this->method = $method;
        $this->resetErrorBag();
    }

    public function register()
    {
        $this->validate();
        $this->isLoading = true;

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        // Create user
        $user = User::create([
            'name' => $this->name,
            'phone' => $this->method === 'phone' ? $this->phone : null,
            'email' => $this->method === 'email' ? $this->email : null,
            'referral_code' => strtoupper(Str::random(8)),
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Handle referral
        if (!empty($this->referral_code)) {
            $referrer = User::where('referral_code', $this->referral_code)->first();
            if ($referrer) {
                Referral::create([
                    'referrer_user_id' => $referrer->id,
                    'referred_user_id' => $user->id,
                    'referral_code' => $referrer->referral_code,
                    'status' => 'pending',
                ]);
            }
        }

        $contact = $this->method === 'phone' ? $this->phone : $this->email;

        $this->sendOtp($this->method, $contact, $otp);

        session(['otp_contact' => $contact, 'otp_method' => $this->method]);

        $this->isLoading = false;

        // Redirect to OTP verification page
        return redirect()->route('auth.verify-otp');
    }

    /**
     * Envoyer le code OTP par SMS (téléphone) ou email selon la méthode.
     * Fallback sur le log si l'envoi échoue, pour ne pas bloquer l'inscription.
     */
    private function sendOtp(string $method, string $contact, string $code): void
    {
        $ttlMinutes = (int) config('sms.otp_ttl_minutes', 10);

        try {
            if ($method === 'phone') {
                $e164 = preg_replace('/\s+/', '', $contact);
                app(SmsService::class)->sendOtp($e164, $code, $ttlMinutes);
            } else {
                Mail::to($contact)->send(new OtpMail($code, $ttlMinutes));
            }
        } catch (\Throwable $e) {
            logger()->error("Échec envoi OTP {$method} à {$contact}", ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.auth.register-form');
    }
}
