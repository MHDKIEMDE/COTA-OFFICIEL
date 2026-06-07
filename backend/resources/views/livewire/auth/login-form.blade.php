<div>
    @if(session('error'))
        <div style="background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.3);color:var(--loss);padding:12px 14px;border-radius:8px;margin-bottom:16px;font-family:'Space Grotesk',sans-serif;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div style="background:rgba(61,220,145,.10);border:1px solid rgba(61,220,145,.3);color:var(--win);padding:12px 14px;border-radius:8px;margin-bottom:16px;font-family:'Space Grotesk',sans-serif;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Toggle méthode --}}
    <div style="display:flex;gap:6px;background:var(--bg2);border:1px solid var(--line);border-radius:10px;padding:4px;margin-bottom:20px;">
        <button type="button" wire:click="switchMethod('phone')"
            style="flex:1;padding:9px;border-radius:7px;border:none;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;
            {{ $method === 'phone' ? 'background:var(--acc);color:var(--bg);' : 'background:transparent;color:var(--dim);' }}">
            <i class="bi bi-phone"></i> Téléphone
        </button>
        <button type="button" wire:click="switchMethod('email')"
            style="flex:1;padding:9px;border-radius:7px;border:none;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;
            {{ $method === 'email' ? 'background:var(--acc);color:var(--bg);' : 'background:transparent;color:var(--dim);' }}">
            <i class="bi bi-envelope"></i> Email
        </button>
    </div>

    <form wire:submit.prevent="login">
        @if($method === 'phone')
            <div style="margin-bottom:16px;">
                <label style="display:block;font-family:'Space Grotesk',sans-serif;font-size:11px;font-weight:700;color:var(--dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:8px;">
                    Numéro de téléphone
                </label>
                <div style="display:flex;gap:8px;">
                    <div style="position:relative;flex-shrink:0;">
                        <select wire:model.live="selected_country"
                            style="padding:13px 32px 13px 12px;background:var(--bg2);border:1px solid var(--line);border-radius:9px;color:var(--ink);font-family:'Space Grotesk',sans-serif;font-size:14px;appearance:none;cursor:pointer;min-width:90px;">
                            @foreach($countries as $country)
                                <option value="{{ $country['country'] }}">{{ $country['flag'] }} +{{ $country['code'] }}</option>
                            @endforeach
                        </select>
                        <i class="bi bi-chevron-down" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--dim);pointer-events:none;font-size:11px;"></i>
                    </div>
                    <input type="tel" wire:model="phone" placeholder="07 XX XX XX XX"
                        style="flex:1;padding:13px 14px;background:var(--bg2);border:1px solid var(--line);border-radius:9px;color:var(--ink);font-family:'Space Grotesk',sans-serif;font-size:15px;outline:none;"
                        onfocus="this.style.borderColor='var(--acc)'" onblur="this.style.borderColor='var(--line)'">
                </div>
                @error('phone')
                    <p style="color:var(--loss);font-family:'Space Grotesk',sans-serif;font-size:12px;margin-top:5px;">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div style="margin-bottom:16px;">
                <label style="display:block;font-family:'Space Grotesk',sans-serif;font-size:11px;font-weight:700;color:var(--dim);letter-spacing:.5px;text-transform:uppercase;margin-bottom:8px;">
                    Adresse email
                </label>
                <div style="position:relative;">
                    <i class="bi bi-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--dim);font-size:15px;"></i>
                    <input type="email" wire:model="email" placeholder="votre@email.com"
                        style="width:100%;padding:13px 14px 13px 42px;background:var(--bg2);border:1px solid var(--line);border-radius:9px;color:var(--ink);font-family:'Space Grotesk',sans-serif;font-size:15px;outline:none;"
                        onfocus="this.style.borderColor='var(--acc)'" onblur="this.style.borderColor='var(--line)'">
                </div>
                @error('email')
                    <p style="color:var(--loss);font-family:'Space Grotesk',sans-serif;font-size:12px;margin-top:5px;">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button type="submit" wire:loading.attr="disabled"
            style="width:100%;padding:14px;background:var(--acc);border:none;border-radius:10px;font-family:'Archivo',sans-serif;font-size:15px;font-weight:900;color:var(--bg);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:20px;">
            <span wire:loading.remove>Recevoir le code <i class="bi bi-arrow-right"></i></span>
            <span wire:loading style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite;"></i> Envoi...
            </span>
        </button>
    </form>

    {{-- Séparateur --}}
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <div style="flex:1;height:1px;background:var(--line);"></div>
        <span style="font-family:'Space Grotesk',sans-serif;font-size:11px;color:var(--dim);">ou continuer avec</span>
        <div style="flex:1;height:1px;background:var(--line);"></div>
    </div>

    {{-- Boutons sociaux --}}
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
        {{-- Google --}}
        <a href="{{ route('auth.social.redirect', 'google') }}"
            style="display:flex;align-items:center;justify-content:center;gap:10px;padding:13px;background:var(--bg2);border:1px solid var(--line);border-radius:10px;text-decoration:none;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:700;color:var(--ink);transition:border-color .15s;"
            onmouseover="this.style.borderColor='var(--acc)'" onmouseout="this.style.borderColor='var(--line)'">
            <svg width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuer avec Google
        </a>

        {{-- Facebook --}}
        <a href="{{ route('auth.social.redirect', 'facebook') }}"
            style="display:flex;align-items:center;justify-content:center;gap:10px;padding:13px;background:var(--bg2);border:1px solid var(--line);border-radius:10px;text-decoration:none;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:700;color:var(--ink);transition:border-color .15s;"
            onmouseover="this.style.borderColor='#1877F2'" onmouseout="this.style.borderColor='var(--line)'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Continuer avec Facebook
        </a>
    </div>

    <p style="text-align:center;font-family:'Space Grotesk',sans-serif;font-size:13px;color:var(--dim);">
        Pas encore de compte ?
        <a href="{{ route('register') }}" style="color:var(--acc);font-weight:700;text-decoration:none;">Créer un compte</a>
    </p>

    <p style="text-align:center;font-family:'Space Grotesk',sans-serif;font-size:10px;color:var(--dim);margin-top:16px;line-height:1.6;">
        En continuant, tu acceptes nos
        <a href="{{ route('privacy') }}" style="color:var(--dim);text-decoration:underline;">Conditions d'utilisation</a>
        et notre
        <a href="{{ route('privacy') }}" style="color:var(--dim);text-decoration:underline;">Politique de confidentialité</a>
    </p>

    <style>
        @keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const map = { 'Africa/Abidjan':'CI','Africa/Porto-Novo':'BJ','Africa/Ouagadougou':'BF','Africa/Bamako':'ML','Africa/Dakar':'SN','Africa/Lome':'TG','Africa/Niamey':'NE','Europe/Paris':'FR' };
            if (map[tz]) @this.set('selected_country', map[tz]);
        });
    </script>
</div>
