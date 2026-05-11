<div>
    @if (session('error'))
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--cota-loss); padding: 12px 16px; border-radius: var(--cota-spacing-sm); margin-bottom: var(--cota-spacing-md); font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif
    
    @if (session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--cota-win); padding: 12px 16px; border-radius: var(--cota-spacing-sm); margin-bottom: var(--cota-spacing-md); font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif
    
    <form wire:submit.prevent="login">
        @if($method === 'phone')
            {{-- Champ téléphone avec sélecteur de pays --}}
            <div style="margin-bottom: var(--cota-spacing-lg);">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-primary); margin-bottom: 8px;">
                    Numéro de téléphone
                </label>
                <div style="display: flex; gap: 8px;">
                    {{-- Sélecteur de pays --}}
                    <div style="position: relative; flex-shrink: 0;">
                        <select 
                            wire:model.live="selected_country"
                            style="padding: 14px 12px; padding-right: 32px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 0.9375rem; appearance: none; cursor: pointer; min-width: 100px;"
                        >
                            @foreach($countries as $country)
                                <option value="{{ $country['country'] }}">
                                    {{ $country['flag'] }} +{{ $country['code'] }}
                                </option>
                            @endforeach
                        </select>
                        <i class="bi bi-chevron-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted); pointer-events: none; font-size: 0.75rem;"></i>
                    </div>
                    
                    {{-- Champ téléphone --}}
                    <div style="flex: 1; position: relative;">
                        <input 
                            type="tel" 
                            wire:model="phone"
                            placeholder="07 XX XX XX XX"
                            style="width: 100%; padding: 14px 16px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem;"
                        >
                    </div>
                </div>
                @error('phone')
                    <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
                @enderror
            </div>
        @else
            {{-- Champ email --}}
            <div style="margin-bottom: var(--cota-spacing-lg);">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-primary); margin-bottom: 8px;">
                    Adresse email
                </label>
                <div style="position: relative;">
                    <i class="bi bi-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted);"></i>
                    <input 
                        type="email" 
                        wire:model="email"
                        placeholder="votre@email.com"
                        style="width: 100%; padding: 14px 16px; padding-left: 44px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem;"
                    >
                </div>
                @error('email')
                    <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
                @enderror
            </div>
        @endif
        
        {{-- Bouton Recevoir le code --}}
        <button 
            type="submit" 
            wire:loading.attr="disabled"
            style="width: 100%; padding: 14px; background: var(--cota-accent); border: none; border-radius: var(--cota-spacing-sm); color: var(--cota-on-accent); font-size: 1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: var(--cota-spacing-lg); transition: opacity 0.2s;"
            onmouseover="this.style.opacity='0.9'" 
            onmouseout="this.style.opacity='1'"
        >
            <span wire:loading.remove>
                Recevoir le code
                <i class="bi bi-arrow-right" style="margin-left: 4px;"></i>
            </span>
            <span wire:loading style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite;"></i>
                Envoi en cours...
            </span>
        </button>
    </form>
    
    {{-- Séparateur --}}
    <div style="display: flex; align-items: center; gap: 12px; margin: var(--cota-spacing-lg) 0;">
        <div style="flex: 1; height: 1px; background: var(--cota-border);"></div>
        <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">ou continuer avec</span>
        <div style="flex: 1; height: 1px; background: var(--cota-border);"></div>
    </div>
    
    {{-- Boutons sociaux --}}
    <div style="display: flex; gap: 12px; margin-bottom: var(--cota-spacing-lg);">
        {{-- Facebook --}}
        <button 
            type="button"
            onclick="window.location.href='{{ route('auth.facebook') }}'"
            style="flex: 1; padding: 14px; background: white; border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: #1b1b18; font-size: 0.9375rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877F2">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
        </button>
        
        {{-- Email --}}
        <button 
            type="button"
            wire:click="switchMethod('email')"
            style="flex: 1; padding: 14px; background: white; border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: #1b1b18; font-size: 0.9375rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"
        >
            <i class="bi bi-envelope" style="font-size: 1.125rem; color: var(--cota-text-muted);"></i>
            Email
        </button>
    </div>
    
    {{-- Lien création de compte --}}
    <p style="text-align: center; color: var(--cota-text-muted); font-size: 0.9375rem; margin-bottom: var(--cota-spacing-md);">
        Pas encore de compte ? 
        <a href="{{ route('register') }}" style="color: var(--cota-accent); font-weight: 700; text-decoration: none;">
            Créer un compte
        </a>
    </p>
    
    {{-- Texte légal --}}
    <p style="text-align: center; color: var(--cota-text-muted); font-size: 0.75rem; line-height: 1.5;">
        En vous connectant, vous acceptez nos 
        <a href="#" style="color: var(--cota-text-muted); text-decoration: underline;">Conditions d'utilisation</a> 
        et notre 
        <a href="#" style="color: var(--cota-text-muted); text-decoration: underline;">Politique de confidentialité</a>
    </p>
    
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    
    <script>
        // Détection intelligente du pays côté client
        document.addEventListener('DOMContentLoaded', function() {
            // Détecter via la timezone du navigateur
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            
            // Mapper les timezones aux codes pays
            const timezoneMap = {
                'Africa/Abidjan': 'CI',
                'Africa/Porto-Novo': 'BJ',
                'Africa/Ouagadougou': 'BF',
                'Africa/Bamako': 'ML',
                'Africa/Dakar': 'SN',
                'Africa/Lome': 'TG',
                'Africa/Niamey': 'NE',
                'Europe/Paris': 'FR',
            };
            
            const detectedCountry = timezoneMap[timezone];
            
            if (detectedCountry) {
                // Mettre à jour le sélecteur via Livewire
                @this.set('selected_country', detectedCountry);
            }
        });
    </script>
</div>
