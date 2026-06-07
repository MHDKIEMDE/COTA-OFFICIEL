@extends('admin.layouts.app')

@section('title', 'Examiner — ' . $candidate->name)
@section('page-title', 'Examiner le candidat')

@section('content')
<div class="space-y-6" style="max-width:760px">

    @if(session('success'))
        <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px">{{ session('error') }}</div>
    @endif

    {{-- ── En-tête candidat ──────────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px">
        <div style="display:flex;align-items:center;gap:16px">
            <div style="width:60px;height:60px;border-radius:12px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.25);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                @if($candidate->logo_url)
                    <img src="{{ $candidate->logo_url }}" style="width:100%;height:100%;object-fit:contain">
                @else
                    <span style="font-family:Archivo,sans-serif;font-weight:900;font-size:24px;color:var(--accent)">
                        {{ strtoupper(substr($candidate->name,0,1)) }}
                    </span>
                @endif
            </div>
            <div style="flex:1">
                <h2 style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--ink)">{{ $candidate->name }}</h2>
                <div style="display:flex;gap:8px;margin-top:4px;flex-wrap:wrap">
                    <span style="font-size:10px;padding:2px 8px;border-radius:20px;font-family:'JetBrains Mono',monospace;font-weight:700;
                        {{ $candidate->status === 'pending' ? 'background:rgba(232,255,54,.12);color:var(--accent)' : ($candidate->status === 'approved' ? 'background:rgba(61,220,145,.12);color:var(--win)' : 'background:rgba(255,91,58,.12);color:var(--loss)') }}">
                        {{ strtoupper($candidate->status) }}
                    </span>
                    <span style="font-size:11px;color:var(--dim-2);font-family:'JetBrains Mono',monospace">
                        {{ $candidate->api_source }} · ID {{ $candidate->api_id }}
                    </span>
                    @if($candidate->country)
                    <span style="font-size:11px;color:var(--dim)">🌍 {{ $candidate->country }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.bookmaker-candidates.index') }}"
               style="padding:8px 14px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;text-decoration:none">
                ← Retour
            </a>
        </div>

        @if($candidate->description)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line)">
            <p style="color:var(--dim);font-size:12px;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;font-family:'JetBrains Mono',monospace">Description API</p>
            <p style="color:var(--ink-2);font-size:14px;line-height:1.6">{{ $candidate->description }}</p>
        </div>
        @endif

        @if($candidate->bonus_label)
        <div style="margin-top:12px;padding:12px;background:rgba(232,255,54,.06);border:1px solid rgba(232,255,54,.2);border-radius:8px">
            <span style="font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:1px;font-family:'JetBrains Mono',monospace">Bonus détecté ·</span>
            <span style="color:var(--accent);font-size:14px;font-weight:700;margin-left:6px">{{ $candidate->bonus_label }}</span>
        </div>
        @endif

        @if($candidate->bonus_description)
        <div style="margin-top:8px;padding:12px;background:var(--bg-2);border-radius:8px;border:1px solid var(--line)">
            <p style="color:var(--dim);font-size:13px;line-height:1.6">{{ $candidate->bonus_description }}</p>
        </div>
        @endif
    </div>

    @if($candidate->isPending())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- ── APPROUVER ──────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px;border-color:rgba(61,220,145,.3)">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:16px;color:var(--win);margin-bottom:16px">
                <i class="fa-solid fa-check-circle mr-2"></i>Valider & Publier
            </h3>
            <form action="{{ route('admin.bookmaker-candidates.approve', $candidate) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Nom affiché *</label>
                    <input type="text" name="name" value="{{ $candidate->name }}" required
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">URL Logo</label>
                    <input type="url" name="logo_url" value="{{ $candidate->logo_url }}"
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Lien d'inscription (affiliation)</label>
                    <input type="url" name="affiliate_link" value="{{ $candidate->website_url }}"
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Lien téléchargement app</label>
                    <input type="url" name="download_link"
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Code promo COTA</label>
                    <input type="text" name="promo_code" placeholder="ex: COTA2024" maxlength="50"
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--accent);font-family:'JetBrains Mono',monospace;font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Couleur marque</label>
                        <input type="text" name="primary_color" value="{{ $candidate->primary_color ?? '#E8FF36' }}" maxlength="20"
                               placeholder="#E8FF36"
                               style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Note (/ 5)</label>
                        <input type="number" name="rating" min="0" max="5" step="0.1"
                               style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Dépôt min. (FCFA)</label>
                        <input type="number" name="min_deposit" min="0"
                               style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Retrait min. (FCFA)</label>
                        <input type="number" name="min_withdrawal" min="0"
                               style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                    </div>
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Label bonus</label>
                    <input type="text" name="bonus_label" value="{{ $candidate->bonus_label }}" maxlength="255"
                           placeholder="ex: 200% jusqu'à 100 000 FCFA"
                           style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Description bonus</label>
                    <textarea name="bonus_description" rows="3"
                              style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:13px;resize:vertical">{{ $candidate->bonus_description }}</textarea>
                </div>

                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Description bookmaker</label>
                    <textarea name="description" rows="2"
                              style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:13px;resize:vertical">{{ $candidate->description }}</textarea>
                </div>

                <button type="submit"
                        style="width:100%;padding:12px;background:var(--win);border:none;border-radius:10px;color:#0b0d10;font-family:Archivo,sans-serif;font-weight:900;font-size:14px;cursor:pointer">
                    <i class="fa-solid fa-check mr-2"></i>VALIDER & PUBLIER
                </button>
            </form>
        </div>

        {{-- ── REJETER ─────────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px;border-color:rgba(255,91,58,.3)">
            <h3 style="font-family:Archivo,sans-serif;font-weight:900;font-size:16px;color:var(--loss);margin-bottom:16px">
                <i class="fa-solid fa-times-circle mr-2"></i>Rejeter
            </h3>
            <form action="{{ route('admin.bookmaker-candidates.reject', $candidate) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label style="font-size:12px;color:var(--dim);display:block;margin-bottom:4px">Raison (optionnel)</label>
                    <textarea name="rejection_reason" rows="4"
                              placeholder="Ex: Bookmaker non disponible en Afrique de l'Ouest, manque d'informations..."
                              style="width:100%;padding:9px 12px;background:var(--bg-2);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--ink);font-size:13px;resize:vertical"></textarea>
                </div>
                <button type="submit"
                        style="width:100%;padding:12px;background:rgba(255,91,58,.15);border:1px solid rgba(255,91,58,.3);border-radius:10px;color:var(--loss);font-family:Archivo,sans-serif;font-weight:900;font-size:14px;cursor:pointer">
                    <i class="fa-solid fa-times mr-2"></i>REJETER
                </button>
            </form>

            {{-- Données brutes API --}}
            @if($candidate->raw_data)
            <div style="margin-top:24px">
                <p style="font-size:11px;color:var(--dim);text-transform:uppercase;letter-spacing:1px;font-family:'JetBrains Mono',monospace;margin-bottom:8px">Données brutes API</p>
                <pre style="background:var(--bg);border:1px solid var(--line);border-radius:8px;padding:12px;font-size:10px;color:var(--dim);overflow:auto;max-height:200px;white-space:pre-wrap">{{ json_encode($candidate->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>

    </div>
    @else
    {{-- ── Déjà traité ──────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px;text-align:center">
        @if($candidate->isApproved())
        <i class="fa-solid fa-check-circle" style="font-size:36px;color:var(--win);margin-bottom:12px;display:block"></i>
        <p style="color:var(--win);font-weight:700">Approuvé le {{ $candidate->reviewed_at?->format('d/m/Y à H:i') }}</p>
        @if($candidate->bookmaker_id)
        <a href="{{ route('admin.bookmakers.edit', $candidate->bookmaker) }}"
           style="display:inline-block;margin-top:12px;padding:9px 18px;background:rgba(61,220,145,.12);border:1px solid rgba(61,220,145,.3);border-radius:8px;color:var(--win);font-size:13px;font-weight:700;text-decoration:none">
            Modifier le bookmaker →
        </a>
        @endif
        @else
        <i class="fa-solid fa-times-circle" style="font-size:36px;color:var(--loss);margin-bottom:12px;display:block"></i>
        <p style="color:var(--loss);font-weight:700">Rejeté le {{ $candidate->reviewed_at?->format('d/m/Y à H:i') }}</p>
        @if($candidate->rejection_reason)
        <p style="color:var(--dim);font-size:13px;margin-top:8px">{{ $candidate->rejection_reason }}</p>
        @endif
        <form action="{{ route('admin.bookmaker-candidates.reset', $candidate) }}" method="POST" style="margin-top:16px;display:inline">
            @csrf
            <button type="submit" style="padding:9px 18px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:13px;cursor:pointer">
                <i class="fa-solid fa-rotate-left mr-2"></i>Remettre en attente
            </button>
        </form>
        @endif
    </div>
    @endif

</div>
@endsection
