@extends('admin.layouts.app')

@section('title', 'Modifier — ' . $bookmaker->name)
@section('page-title', 'Modifier le bookmaker')

@section('content')
<div style="max-width:720px">

    @if(session('success'))
        <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px;margin-bottom:20px">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px;margin-bottom:20px">
            <ul style="margin:0;padding-left:16px">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.bookmakers.update', $bookmaker) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        {{-- ── Identité ──────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px">
                <i class="fa-solid fa-id-card mr-2"></i>Identité
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="fl">Nom affiché *</label>
                    <input type="text" name="name" value="{{ old('name', $bookmaker->name) }}" required class="fi">
                </div>
                <div>
                    <label class="fl">Couleur de marque (hex)</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="color" id="color_picker" value="{{ $bookmaker->primary_color ?? '#E8FF36' }}"
                               oninput="document.getElementById('color_text').value=this.value"
                               style="width:40px;height:38px;border:1px solid var(--line);border-radius:8px;background:var(--bg-2);cursor:pointer;padding:2px">
                        <input type="text" name="primary_color" id="color_text" value="{{ old('primary_color', $bookmaker->primary_color ?? '#E8FF36') }}"
                               maxlength="20" class="fi" style="flex:1"
                               oninput="document.getElementById('color_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="fl">Note (/ 5)</label>
                    <input type="number" name="rating" value="{{ old('rating', $bookmaker->rating) }}" min="0" max="5" step="0.1" class="fi">
                </div>
                <div class="col-span-2">
                    <label class="fl">URL du logo</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url', $bookmaker->logo_url) }}" class="fi" placeholder="https://...">
                </div>
                <div class="col-span-2">
                    <label class="fl">Description courte</label>
                    <textarea name="description" rows="2" class="fi">{{ old('description', $bookmaker->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Liens ─────────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px">
                <i class="fa-solid fa-link mr-2"></i>Liens
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="fl">Lien d'inscription (affiliation) <span style="color:var(--dim)">(avec ID affilié COTA)</span></label>
                    <input type="url" name="affiliate_link" value="{{ old('affiliate_link', $bookmaker->affiliate_link) }}" class="fi"
                           placeholder="https://1xbet.com/register?affid=XXXXX">
                </div>
                <div>
                    <label class="fl">Lien téléchargement application <span style="color:var(--dim)">(APK, App Store ou Play Store)</span></label>
                    <input type="url" name="download_link" value="{{ old('download_link', $bookmaker->download_link) }}" class="fi"
                           placeholder="https://play.google.com/store/apps/...">
                </div>
            </div>
        </div>

        {{-- ── Code promo & Bonus ───────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px">
                <i class="fa-solid fa-gift mr-2"></i>Code promo & Bonus
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="fl">Code promo exclusif COTA</label>
                    <input type="text" name="promo_code" value="{{ old('promo_code', $blog?->promo_code) }}"
                           maxlength="50" placeholder="ex: COTA2024" class="fi"
                           style="text-transform:uppercase;letter-spacing:3px;font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--accent)">
                </div>
                <div>
                    <label class="fl">Label bonus <span style="color:var(--dim)">(résumé court pour la card)</span></label>
                    <input type="text" name="bonus_label" value="{{ old('bonus_label', $bookmaker->bonus_label) }}"
                           maxlength="255" placeholder="ex: 200% jusqu'à 100 000 FCFA" class="fi">
                </div>
                <div>
                    <label class="fl">Description complète du bonus <span style="color:var(--dim)">(affichée dans le guide)</span></label>
                    <textarea name="bonus_description" rows="4" class="fi"
                              placeholder="Décris les conditions du bonus : montant, conditions de mise, délai...">{{ old('bonus_description', $blog?->bonus_description) }}</textarea>
                </div>
                <div>
                    <label class="fl">Label bouton CTA</label>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $blog?->cta_label ?? "S'inscrire et obtenir le bonus") }}"
                           maxlength="150" class="fi">
                </div>
            </div>
        </div>

        {{-- ── Paiements ────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px">
                <i class="fa-solid fa-wallet mr-2"></i>Paiements
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fl">Dépôt minimum (FCFA)</label>
                    <input type="number" name="min_deposit" value="{{ old('min_deposit', $bookmaker->min_deposit) }}" min="0" class="fi">
                </div>
                <div>
                    <label class="fl">Retrait minimum (FCFA)</label>
                    <input type="number" name="min_withdrawal" value="{{ old('min_withdrawal', $bookmaker->min_withdrawal) }}" min="0" class="fi">
                </div>
            </div>
        </div>

        {{-- ── Paramètres ───────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px">
                <i class="fa-solid fa-sliders mr-2"></i>Paramètres
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fl">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $bookmaker->sort_order) }}" min="0" class="fi">
                </div>
                <div style="display:flex;align-items:center;gap:12px;padding-top:20px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $bookmaker->is_active ? 'checked' : '' }}
                               style="width:18px;height:18px;accent-color:var(--win)">
                        <span style="font-size:14px;color:var(--ink)">Actif dans l'application</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div style="display:flex;gap:12px">
            <a href="{{ route('admin.bookmakers.list') }}"
               style="padding:12px 20px;background:var(--bg-2);border:1px solid var(--line);border-radius:10px;color:var(--dim);font-size:14px;font-weight:600;text-decoration:none">
                Annuler
            </a>
            <button type="submit"
                    style="flex:1;padding:12px;background:var(--accent);border:none;border-radius:10px;color:#0b0d10;font-family:Archivo,sans-serif;font-weight:900;font-size:14px;cursor:pointer">
                <i class="fa-solid fa-save mr-2"></i>ENREGISTRER
            </button>
        </div>
    </form>
</div>

<style>
.fl { display:block;font-size:12px;color:var(--dim);margin-bottom:4px }
.fi {
    width:100%;padding:10px 12px;
    background:var(--bg-2);border:1px solid var(--line);
    border-radius:8px;color:var(--ink);font-size:14px;
    box-sizing:border-box;outline:none;
}
.fi:focus { border-color:rgba(232,255,54,.4) }
textarea.fi { resize:vertical;line-height:1.5 }
</style>
@endsection
