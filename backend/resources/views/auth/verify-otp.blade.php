@extends('layouts.auth')

@section('content')

<a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:8px;padding:16px;color:var(--dim);text-decoration:none;font-family:'Space Grotesk',sans-serif;font-size:13px;">
    <i class="bi bi-arrow-left"></i> Retour
</a>

<div style="flex:1;padding:0 16px 32px;display:flex;flex-direction:column;">
    <div style="margin-bottom:32px;text-align:center;">
        <div style="width:68px;height:68px;background:rgba(61,220,145,.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:2px solid rgba(61,220,145,.3);">
            <i class="bi bi-shield-lock-fill" style="font-size:28px;color:var(--win);"></i>
        </div>
        <div style="font-family:'Archivo',sans-serif;font-size:26px;font-weight:900;color:var(--ink);margin-bottom:6px;">
            Vérification OTP
        </div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;color:var(--dim);">
            Code envoyé au<br>
            <strong style="color:var(--ink);">{{ session('otp_identifier', 'votre numéro') }}</strong>
        </div>
    </div>

    <livewire:auth.verify-otp-form :identifier="session('otp_identifier')" />

    <p style="margin-top:auto;padding-top:24px;text-align:center;font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--dim);letter-spacing:.5px;">
        Le code expire dans 5 minutes
    </p>
</div>
@endsection
