@extends('admin.layouts.app')

@section('title', 'Nouveau Pronostic')
@section('page-title', 'Ajouter un Pronostic')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.predictions.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- ── Match ──────────────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-6"><i class="fa-solid fa-futbol mr-2" style="color:var(--accent)"></i>Informations du match</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Équipe domicile *</label>
                    <input type="text" name="home_team" value="{{ old('home_team') }}" required
                           class="input-brand w-full" placeholder="Ex: Paris Saint-Germain">
                    @error('home_team')<p style="font-size:12px;color:var(--loss);margin-top:4px">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Équipe extérieur *</label>
                    <input type="text" name="away_team" value="{{ old('away_team') }}" required
                           class="input-brand w-full" placeholder="Ex: Olympique de Marseille">
                    @error('away_team')<p style="font-size:12px;color:var(--loss);margin-top:4px">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Compétition *</label>
                    <input type="text" name="competition" value="{{ old('competition') }}" required
                           class="input-brand w-full" placeholder="Ex: Ligue 1">
                    @error('competition')<p style="font-size:12px;color:var(--loss);margin-top:4px">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Date & heure du match *</label>
                    <input type="datetime-local" name="match_date" value="{{ old('match_date') }}" required
                           class="input-brand w-full">
                    @error('match_date')<p style="font-size:12px;color:var(--loss);margin-top:4px">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ── Pronostic ───────────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-6"><i class="fa-solid fa-chart-line mr-2" style="color:var(--win)"></i>Pronostic</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Type de pronostic *</label>
                    <select name="prediction_type" required class="input-brand w-full">
                        <option value="">Sélectionner…</option>
                        <option value="1X2"           {{ old('prediction_type') == '1X2'           ? 'selected' : '' }}>1X2 (Résultat final)</option>
                        <option value="Over/Under"    {{ old('prediction_type') == 'Over/Under'    ? 'selected' : '' }}>Over/Under</option>
                        <option value="BTTS"          {{ old('prediction_type') == 'BTTS'          ? 'selected' : '' }}>BTTS (Les deux équipes marquent)</option>
                        <option value="Double Chance" {{ old('prediction_type') == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                        <option value="HT/FT"         {{ old('prediction_type') == 'HT/FT'         ? 'selected' : '' }}>Mi-temps / Fin de match</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Pronostic *</label>
                    <input type="text" name="prediction_value" value="{{ old('prediction_value') }}" required
                           class="input-brand w-full" placeholder="Ex: 1, X, 2, Over 2.5, BTTS Oui…">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Cote *</label>
                    <input type="number" step="0.01" min="1.01" name="odds" value="{{ old('odds') }}" required
                           class="input-brand w-full" placeholder="Ex: 1.85">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Niveau de confiance *</label>
                    <select name="confidence" required class="input-brand w-full">
                        <option value="1" {{ old('confidence') == '1' ? 'selected' : '' }}>★ — Faible</option>
                        <option value="2" {{ old('confidence') == '2' ? 'selected' : '' }}>★★ — Moyen</option>
                        <option value="3" {{ old('confidence', '3') == '3' ? 'selected' : '' }}>★★★ — Élevé</option>
                        <option value="4" {{ old('confidence') == '4' ? 'selected' : '' }}>★★★★ — Très élevé</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Analyse (optionnel)</label>
                <textarea name="analysis" rows="4" class="input-brand w-full" style="height:auto;padding:12px"
                          placeholder="Justification du pronostic…">{{ old('analysis') }}</textarea>
            </div>

            <div class="mt-5 flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer">
                    <span style="font-size:14px;color:var(--ink-2)">
                        <i class="fa-solid fa-crown mr-1" style="color:var(--accent)"></i>Pronostic Premium
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_combined" value="1" {{ old('is_combined') ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer">
                    <span style="font-size:14px;color:var(--ink-2)">
                        <i class="fa-solid fa-layer-group mr-1" style="color:var(--accent)"></i>Inclure dans le combiné
                    </span>
                </label>
            </div>
        </div>

        {{-- ── Actions ─────────────────────────────────────────────────────────── --}}
        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">
                <i class="fa-solid fa-save mr-2"></i>Enregistrer le pronostic
            </button>
            <a href="{{ route('admin.predictions.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
