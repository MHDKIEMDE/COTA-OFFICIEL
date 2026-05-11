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
    
    <form wire:submit.prevent="verify">
        {{-- OTP Input --}}
        <div style="margin-bottom: var(--cota-spacing-lg);">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--cota-text-secondary); margin-bottom: 12px; text-align: center;">
                Entrez le code à 6 chiffres
            </label>
            
            <div style="display: flex; gap: 8px; justify-content: center;">
                @for($i = 0; $i < 6; $i++)
                    <input 
                        type="text" 
                        maxlength="1"
                        class="otp-input"
                        data-index="{{ $i }}"
                        style="width: 48px; height: 56px; text-align: center; font-size: 1.5rem; font-weight: 700; background: var(--cota-bg-secondary); border: 2px solid var(--cota-border); border-radius: var(--cota-spacing-sm); color: var(--cota-text-primary);"
                        oninput="handleOtpInput(this)"
                        onkeydown="handleOtpKeydown(event, this)"
                    >
                @endfor
            </div>
            
            <input type="hidden" wire:model="otp" id="otpHidden">
            
            @error('otp')
                <p style="color: var(--cota-loss); font-size: 0.8125rem; margin-top: 12px; text-align: center;">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Submit Button --}}
        <button 
            type="submit" 
            class="btn-cota btn-cota--primary btn-cota--block"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>
                <i class="bi bi-check-lg me-2"></i> Vérifier
            </span>
            <span wire:loading>
                <i class="bi bi-arrow-repeat me-2" style="animation: spin 1s linear infinite;"></i> Vérification...
            </span>
        </button>
        
        {{-- Resend --}}
        <div style="text-align: center; margin-top: var(--cota-spacing-lg);">
            <p style="color: var(--cota-text-muted); font-size: 0.875rem; margin-bottom: 8px;">
                Vous n'avez pas reçu le code ?
            </p>
            <button 
                type="button" 
                wire:click="resendOtp"
                wire:loading.attr="disabled"
                style="background: none; border: none; color: var(--cota-accent); font-weight: 600; cursor: pointer; font-size: 0.9375rem;"
            >
                <span wire:loading.remove wire:target="resendOtp">
                    Renvoyer le code
                </span>
                <span wire:loading wire:target="resendOtp">
                    Envoi en cours...
                </span>
            </button>
        </div>
    </form>
    
    <script>
        function handleOtpInput(input) {
            const value = input.value.replace(/\D/g, '');
            input.value = value;
            
            if (value.length === 1) {
                const nextInput = input.nextElementSibling;
                if (nextInput && nextInput.classList.contains('otp-input')) {
                    nextInput.focus();
                }
            }
            
            updateHiddenOtp();
        }
        
        function handleOtpKeydown(event, input) {
            if (event.key === 'Backspace' && input.value === '') {
                const prevInput = input.previousElementSibling;
                if (prevInput && prevInput.classList.contains('otp-input')) {
                    prevInput.focus();
                }
            }
        }
        
        function updateHiddenOtp() {
            const inputs = document.querySelectorAll('.otp-input');
            let otp = '';
            inputs.forEach(input => otp += input.value);
            document.getElementById('otpHidden').value = otp;
            @this.set('otp', otp);
        }
    </script>
    
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .otp-input:focus {
            outline: none;
            border-color: var(--cota-accent);
        }
    </style>
</div>
