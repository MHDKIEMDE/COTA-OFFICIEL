<?php

namespace App\Livewire\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Referral;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class VerifyOtpForm extends Component
{
    public string $contact = '';
    public string $method = 'phone';
    public string $otp = '';
    public bool $isLoading = false;
    public bool $canResend = false;
    public int $countdown = 60;

    public function mount($contact = null, $method = null)
    {
        $this->contact = $contact ?? session('otp_contact', '');
        $this->method = $method ?? session('otp_method', 'phone');

        if (empty($this->contact)) {
            return redirect()->route('login');
        }

        // Start countdown timer
        $this->startCountdown();
    }

    public function startCountdown()
    {
        $this->canResend = false;
        $this->countdown = 60;
    }

    public function decrementCountdown()
    {
        if ($this->countdown > 0) {
            $this->countdown--;
        } else {
            $this->canResend = true;
        }
    }

    protected $rules = [
        'otp' => 'required|string|size:6',
    ];

    protected $messages = [
        'otp.required' => 'Le code OTP est requis.',
        'otp.size' => 'Le code OTP doit contenir 6 chiffres.',
    ];

    public function verifyOtp()
    {
        $this->validate();
        $this->isLoading = true;

        $user = User::where($this->method, $this->contact)->first();

        if (!$user) {
            $this->addError('otp', 'Utilisateur non trouvé.');
            $this->isLoading = false;
            return;
        }

        // Verify OTP
        if ($user->otp_code !== $this->otp) {
            $this->addError('otp', 'Code OTP invalide.');
            $this->isLoading = false;
            return;
        }

        if ($user->otp_expires_at < now()) {
            $this->addError('otp', 'Code OTP expiré. Veuillez en demander un nouveau.');
            $this->isLoading = false;
            return;
        }

        // Clear OTP
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'email_verified_at' => $this->method === 'email' ? now() : $user->email_verified_at,
            'phone_verified_at' => $this->method === 'phone' ? now() : $user->phone_verified_at,
        ]);

        // Process pending referral
        $pendingReferral = Referral::where('referred_user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingReferral) {
            $pendingReferral->update(['status' => 'completed']);
            
            // Grant premium days to both users
            $referrer = User::find($pendingReferral->referrer_user_id);
            if ($referrer) {
                $referrer->addPremiumDays(7, 'referral');
            }
            $user->addPremiumDays(7, 'referral_welcome');
        }

        // Login user
        Auth::login($user, true);

        // Clear session
        session()->forget(['otp_contact', 'otp_method']);

        $this->isLoading = false;

        return redirect()->intended('/');
    }

    public function resendOtp()
    {
        if (!$this->canResend) {
            return;
        }

        $user = User::where($this->method, $this->contact)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        $this->sendOtp($this->method, $this->contact, $otp);

        // Reset countdown
        $this->startCountdown();

        session()->flash('message', 'Un nouveau code OTP a été envoyé.');
    }

    /**
     * Envoyer le code OTP par SMS (téléphone) ou email selon la méthode.
     * Fallback sur le log si l'envoi échoue, pour ne pas bloquer le renvoi.
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
            logger()->error("Échec renvoi OTP {$method} à {$contact}", ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.auth.verify-otp-form');
    }
}
