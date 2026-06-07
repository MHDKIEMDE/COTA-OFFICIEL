@extends('admin.layouts.app')

@section('title', 'Modifier Pronostic')
@section('page-title', 'Modifier le Pronostic')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.predictions.update', $prediction) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── Match ──────────────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-6"><i class="fa-solid fa-futbol mr-2" style="color:var(--accent)"></i>Informations du match</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Équipe domicile *</label>
                    <input type="text" name="home_team" value="{{ old('home_team', $prediction->home_team) }}" required
                           class="input-brand w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Équipe extérieur *</label>
                    <input type="text" name="away_team" value="{{ old('away_team', $prediction->away_team) }}" required
                           class="input-brand w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Compétition *</label>
                    <input type="text" name="competition" value="{{ old('competition', $prediction->competition) }}" required
                           class="input-brand w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Date & heure du match *</label>
                    <input type="datetime-local" name="match_date"
                           value="{{ old('match_date', \Carbon\Carbon::parse($prediction->match_date)->format('Y-m-d\TH:i')) }}" required
                           class="input-brand w-full">
                </div>
            </div>
        </div>

        {{-- ── Pronostic & Résultat ────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-6"><i class="fa-solid fa-chart-line mr-2" style="color:var(--win)"></i>Pronostic & Résultat</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Type de pronostic *</label>
                    <select name="prediction_type" required class="input-brand w-full">
                        <option value="1X2"           {{ old('prediction_type', $prediction->prediction_type) == '1X2'           ? 'selected' : '' }}>1X2</option>
                        <option value="Over/Under"    {{ old('prediction_type', $prediction->prediction_type) == 'Over/Under'    ? 'selected' : '' }}>Over/Under</option>
                        <option value="BTTS"          {{ old('prediction_type', $prediction->prediction_type) == 'BTTS'          ? 'selected' : '' }}>BTTS</option>
                        <option value="Double Chance" {{ old('prediction_type', $prediction->prediction_type) == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                        <option value="HT/FT"         {{ old('prediction_type', $prediction->prediction_type) == 'HT/FT'         ? 'selected' : '' }}>HT/FT</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Pronostic *</label>
                    <input type="text" name="prediction_value" value="{{ old('prediction_value', $prediction->prediction_value) }}" required
                           class="input-brand w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Cote *</label>
                    <input type="number" step="0.01" min="1.01" name="odds" value="{{ old('odds', $prediction->odds) }}" required
                           class="input-brand w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Confiance *</label>
                    <select name="confidence" required class="input-brand w-full">
                        @for($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ old('confidence', $prediction->confidence) == $i ? 'selected' : '' }}>
                                {{ str_repeat('★', $i) }} — {{ ['Faible','Moyen','Élevé','Très élevé'][$i-1] }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Statut *</label>
                    <select name="status" required class="input-brand w-full">
                        <option value="pending"   {{ old('status', $prediction->status) == 'pending'   ? 'selected' : '' }}>En attente</option>
                        <option value="won"       {{ old('status', $prediction->status) == 'won'       ? 'selected' : '' }}>Gagné</option>
                        <option value="lost"      {{ old('status', $prediction->status) == 'lost'      ? 'selected' : '' }}>Perdu</option>
                        <option value="cancelled" {{ old('status', $prediction->status) == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Score dom.</label>
                        <input type="number" min="0" name="home_score" value="{{ old('home_score', $prediction->home_score) }}"
                               class="input-brand w-full" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Score ext.</label>
                        <input type="number" min="0" name="away_score" value="{{ old('away_score', $prediction->away_score) }}"
                               class="input-brand w-full" placeholder="0">
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Analyse</label>
                <textarea name="analysis" rows="4" class="input-brand w-full" style="height:auto;padding:12px">{{ old('analysis', $prediction->analysis) }}</textarea>
            </div>

            <div class="mt-5 flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_premium" value="1" {{ old('is_premium', $prediction->is_premium) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer">
                    <span style="font-size:14px;color:var(--ink-2)">
                        <i class="fa-solid fa-crown mr-1" style="color:var(--accent)"></i>Pronostic Premium
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_combined" value="1" {{ old('is_combined', $prediction->is_combined) ? 'checked' : '' }}
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
                <i class="fa-solid fa-save mr-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('admin.predictions.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
