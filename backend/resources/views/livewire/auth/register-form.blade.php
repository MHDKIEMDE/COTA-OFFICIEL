<div>
    @if (session('error'))
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--cota-loss); padding: 12px 16px; border-radius: var(--cota-spacing-sm); margin-bottom: var(--cota-spacing-md); font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif
    
    <form wire:submit.prevent="register">
        {{-- Name Input --}}
        <div style="margin-bottom: var(--cota-spacing-md);">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-secondary); margin-bottom: 8px;">
                Nom complet
            </label>
            <div style="position: relative;">
                <i class="bi bi-person" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted);"></i>
                <input 
                    type="text" 
                    wire:model="name"
                    placeholder="Jean Dupont"
                    style="width: 100%; padding: 14px 16px 14px 44px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem;"
                >
            </div>
            @error('name')
                <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Phone Input --}}
        <div style="margin-bottom: var(--cota-spacing-md);">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-secondary); margin-bottom: 8px;">
                Numéro de téléphone
            </label>
            <div style="position: relative;">
                <i class="bi bi-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted);"></i>
                <input 
                    type="tel" 
                    wire:model="phone"
                    placeholder="+226 07 00 00 00"
                    style="width: 100%; padding: 14px 16px 14px 44px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem;"
                >
            </div>
            @error('phone')
                <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Email Input (Optional) --}}
        <div style="margin-bottom: var(--cota-spacing-md);">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-secondary); margin-bottom: 8px;">
                Email <span style="color: var(--cota-text-muted); font-weight: 400;">(optionnel)</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted);"></i>
                <input 
                    type="email" 
                    wire:model="email"
                    placeholder="votre@email.com"
                    style="width: 100%; padding: 14px 16px 14px 44px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem;"
                >
            </div>
            @error('email')
                <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Referral Code (Optional) --}}
        <div style="margin-bottom: var(--cota-spacing-lg);">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-secondary); margin-bottom: 8px;">
                Code parrain <span style="color: var(--cota-text-muted); font-weight: 400;">(optionnel)</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-gift" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--cota-text-muted);"></i>
                <input 
                    type="text" 
                    wire:model="referral_code"
                    placeholder="CODE123"
                    style="width: 100%; padding: 14px 16px 14px 44px; background: var(--cota-bg-secondary); border: 1px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary); font-size: 1rem; text-transform: uppercase;"
                >
            </div>
            @error('referral_code')
                <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 6px;">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Submit Button --}}
        <button 
            type="submit" 
            class="btn-cota btn-cota--primary btn-cota--block"
            wire:loading.attr="disabled"
            style="position: relative;"
        >
            <span wire:loading.remove>
                <i class="bi bi-person-plus me-2"></i> Créer mon compte
            </span>
            <span wire:loading>
                <i class="bi bi-arrow-repeat me-2" style="animation: spin 1s linear infinite;"></i> Création en cours...
            </span>
        </button>
    </form>
    
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</div>
