@extends('admin.layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres du système')

@section('content')
<div class="space-y-6">

    {{-- ── Onglets ──────────────────────────────────────────────────────────── --}}
    <div style="border-bottom:1px solid var(--line);overflow-x:auto">
        <nav class="flex gap-1 -mb-px min-w-max" id="settingsTabs">
            @php $tabs = ['payment' => ['icon' => 'fa-credit-card', 'label' => 'Paiement'], 'apikeys' => ['icon' => 'fa-key', 'label' => 'Clés API'], 'apisources' => ['icon' => 'fa-toggle-on', 'label' => 'Sources API'], 'bookmakers' => ['icon' => 'fa-link', 'label' => 'Bookmakers'], 'app' => ['icon' => 'fa-sliders', 'label' => 'Application']]; @endphp
            @foreach($tabs as $key => $tab)
                <button onclick="switchTab('{{ $key }}')" id="tab-{{ $key }}"
                    class="tab-btn flex items-center gap-2 px-5 py-3 text-sm font-medium transition"
                    style="{{ $loop->first ? 'border-bottom:2px solid var(--accent);color:var(--accent)' : 'border-bottom:2px solid transparent;color:var(--dim)' }}">
                    <i class="fa-solid {{ $tab['icon'] }}"></i>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── PAIEMENT ─────────────────────────────────────────────────────────── --}}
    <div id="panel-payment" class="tab-panel space-y-6">
        <div class="card">
            <p class="tag-mono mb-5"><i class="fa-solid fa-credit-card mr-2" style="color:var(--accent)"></i>Configuration du paiement</p>
            <form id="formPayment" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Provider actif</label>
                        <select name="active_provider" class="input-brand w-full" style="height:40px;padding:0 12px">
                            <option value="">— Aucun —</option>
                            @foreach($paymentData['available_drivers'] ?? [] as $driver)
                                <option value="{{ $driver }}" {{ $paymentData['active_provider'] === $driver ? 'selected' : '' }}>{{ ucfirst($driver) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Devise</label>
                        <input type="text" name="currency" value="{{ $paymentData['currency'] }}" class="input-brand w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Webhook secret</label>
                        <input type="password" name="webhook_secret" value="{{ $paymentData['webhook_secret'] }}"
                               class="input-brand w-full" autocomplete="new-password">
                    </div>
                </div>
                @if(!empty($paymentData['providers']))
                    <div>
                        <p class="text-xs font-semibold mb-3" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Providers configurés</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($paymentData['providers'] as $i => $prov)
                                <div class="p-4 rounded-lg space-y-3" style="background:var(--bg-3);border:1px solid var(--line)">
                                    <div class="flex items-center justify-between">
                                        <span style="color:var(--ink);font-weight:600">{{ $prov['label'] ?? $prov['slug'] }}</span>
                                        <span class="{{ ($prov['env'] ?? 'test') === 'live' ? 'badge-win' : 'badge-pending' }}">
                                            {{ strtoupper($prov['env'] ?? 'test') }}
                                        </span>
                                    </div>
                                    <input type="hidden" name="providers[{{ $i }}][slug]"  value="{{ $prov['slug'] }}">
                                    <input type="hidden" name="providers[{{ $i }}][label]" value="{{ $prov['label'] ?? $prov['slug'] }}">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs mb-1" style="color:var(--dim)">API Key</label>
                                            <input type="text" name="providers[{{ $i }}][api_key]" value="{{ $prov['api_key'] ?? '' }}"
                                                   class="input-brand w-full" style="height:36px;font-size:12px">
                                        </div>
                                        <div>
                                            <label class="block text-xs mb-1" style="color:var(--dim)">API Secret</label>
                                            <input type="password" name="providers[{{ $i }}][api_secret]" value="{{ $prov['api_secret'] ?? '' }}"
                                                   class="input-brand w-full" style="height:36px;font-size:12px" autocomplete="new-password">
                                        </div>
                                    </div>
                                    <select name="providers[{{ $i }}][env]" class="input-brand w-full" style="height:36px;padding:0 10px;font-size:12px">
                                        <option value="test" {{ ($prov['env'] ?? 'test') === 'test' ? 'selected' : '' }}>Test</option>
                                        <option value="live" {{ ($prov['env'] ?? '') === 'live' ? 'selected' : '' }}>Live</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="flex justify-end">
                    <button type="button" onclick="saveSettings('payment', 'formPayment')" class="btn-primary">
                        <i class="fa-solid fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CLÉS API ─────────────────────────────────────────────────────────── --}}
    <div id="panel-apikeys" class="tab-panel space-y-6 hidden">
        <div class="card">
            <p class="tag-mono mb-5"><i class="fa-solid fa-key mr-2" style="color:#f5a623"></i>Clés API externes</p>
            <form id="formApikeys" class="space-y-4">
                @csrf
                @php
                    $apiFields = [
                        'football_api_key'    => ['label' => 'API-Football Key',        'type' => 'password'],
                        'openweather_key'     => ['label' => 'OpenWeatherMap Key',       'type' => 'password'],
                        'termii_key'          => ['label' => 'Termii API Key (SMS OTP)', 'type' => 'password'],
                        'termii_sender_id'    => ['label' => 'Termii Sender ID',         'type' => 'text'],
                        'facebook_app_id'     => ['label' => 'Facebook App ID',          'type' => 'text'],
                        'facebook_app_secret' => ['label' => 'Facebook App Secret',      'type' => 'password'],
                    ];
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($apiFields as $name => $field)
                        <div>
                            <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">{{ $field['label'] }}</label>
                            <input type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $apiKeys[$name] ?? '' }}"
                                   class="input-brand w-full" autocomplete="new-password">
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="saveSettings('api-keys', 'formApikeys')" class="btn-primary">
                        <i class="fa-solid fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── SOURCES API (toggle activer/désactiver) ─────────────────────────── --}}
    <div id="panel-apisources" class="tab-panel space-y-6 hidden">
        <div class="card mt-6">
            <p class="tag-mono mb-5"><i class="fa-solid fa-toggle-on mr-2" style="color:#06b6d4"></i>Sources API actives</p>
            <p class="text-sm mb-5" style="color:var(--dim)">Activez ou désactivez chaque source de données. Les sources désactivées sont ignorées par l'algorithme.</p>
            @php
                $apiSources = [
                    'apifootball'  => ['label' => 'API-Football',    'icon' => 'fa-futbol',       'desc' => 'Fixtures, résultats, statistiques'],
                    'sportradar'   => ['label' => 'Sportradar',       'icon' => 'fa-chart-line',   'desc' => 'Données avancées en temps réel'],
                    'oddsapi'      => ['label' => 'Odds API',         'icon' => 'fa-coins',        'desc' => 'Cotes bookmakers en direct'],
                    'openweather'  => ['label' => 'OpenWeatherMap',   'icon' => 'fa-cloud-sun',    'desc' => 'Météo matchs (critère algo)'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($apiSources as $slug => $src)
                    @php $isEnabled = \App\Models\AppConfig::get("api.source.{$slug}_enabled", true); @endphp
                    <div class="flex items-center justify-between p-4 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid {{ $src['icon'] }} w-5 text-center" style="color:var(--accent)"></i>
                            <div>
                                <p style="color:var(--ink);font-weight:600;font-size:14px">{{ $src['label'] }}</p>
                                <p style="color:var(--dim);font-size:12px">{{ $src['desc'] }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only api-source-toggle"
                                   data-source="{{ $slug }}"
                                   {{ $isEnabled ? 'checked' : '' }}>
                            <div class="toggle-track w-11 h-6 rounded-full transition-colors duration-200"
                                 style="{{ $isEnabled ? 'background:#22c55e' : 'background:var(--line)' }}">
                                <div class="toggle-thumb absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full shadow transition-transform duration-200"
                                     style="{{ $isEnabled ? 'transform:translateX(20px)' : '' }}"></div>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── BOOKMAKERS ───────────────────────────────────────────────────────── --}}
    <div id="panel-bookmakers" class="tab-panel space-y-6 hidden">
        <div class="card">
            <p class="tag-mono mb-5"><i class="fa-solid fa-link mr-2" style="color:var(--accent)"></i>Bookmakers & liens affiliés</p>
            <form id="formBookmakers" class="space-y-4">
                @csrf
                <div class="space-y-4">
                    @forelse($bookmakers as $i => $bk)
                        <div class="p-4 rounded-lg space-y-3" style="background:var(--bg-3);border:1px solid var(--line)">
                            <div class="flex items-center justify-between">
                                <span style="color:var(--ink);font-weight:600">{{ $bk['name'] ?? $bk['id'] }}</span>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="bookmakers[{{ $i }}][is_active]" value="1"
                                           {{ ($bk['is_active'] ?? true) ? 'checked' : '' }}
                                           style="width:14px;height:14px;accent-color:var(--accent)">
                                    <span style="font-size:13px;color:var(--dim)">Actif</span>
                                </label>
                            </div>
                            <input type="hidden" name="bookmakers[{{ $i }}][id]" value="{{ $bk['id'] }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs mb-1" style="color:var(--dim)">Nom</label>
                                    <input type="text" name="bookmakers[{{ $i }}][name]" value="{{ $bk['name'] ?? '' }}"
                                           class="input-brand w-full" style="height:36px;font-size:12px">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs mb-1" style="color:var(--dim)">URL d'affiliation</label>
                                    <input type="url" name="bookmakers[{{ $i }}][url]" value="{{ $bk['url'] ?? '' }}"
                                           class="input-brand w-full" style="height:36px;font-size:12px">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1" style="color:var(--dim)">Tracking ID</label>
                                    <input type="text" name="bookmakers[{{ $i }}][tracking_id]" value="{{ $bk['tracking_id'] ?? '' }}"
                                           class="input-brand w-full" style="height:36px;font-size:12px">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1" style="color:var(--dim)">Logo emoji</label>
                                    <input type="text" name="bookmakers[{{ $i }}][logo_emoji]" value="{{ $bk['logo_emoji'] ?? '' }}" maxlength="5"
                                           class="input-brand w-full" style="height:36px;font-size:20px;text-align:center">
                                </div>
                                <div>
                                    <label class="block text-xs mb-1" style="color:var(--dim)">Couleur hex</label>
                                    <input type="text" name="bookmakers[{{ $i }}][color]" value="{{ $bk['color'] ?? '#6366F1' }}" maxlength="7"
                                           class="input-brand w-full" style="height:36px;font-size:12px">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="color:var(--dim);text-align:center;padding:24px 0">Aucun bookmaker configuré.</p>
                    @endforelse
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="saveSettings('bookmakers', 'formBookmakers')" class="btn-primary">
                        <i class="fa-solid fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── APPLICATION ──────────────────────────────────────────────────────── --}}
    <div id="panel-app" class="tab-panel space-y-6 hidden">
        <div class="card">
            <p class="tag-mono mb-5"><i class="fa-solid fa-sliders mr-2" style="color:var(--win)"></i>Configuration de l'application</p>
            <form id="formApp" class="space-y-6">
                @csrf
                <div>
                    <p class="text-xs font-semibold mb-3" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Heures de publication des prédictions</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach(range(0, 23) as $h)
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="prediction_publish_hours[]" value="{{ $h }}"
                                       {{ in_array($h, $appConfig['prediction_publish_hours'] ?? [8, 20]) ? 'checked' : '' }}
                                       style="width:14px;height:14px;accent-color:var(--accent)">
                                <span style="font-size:13px;color:var(--ink-2)">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                {{-- ── Plans Premium + Avantages ─────────────────────────── --}}
                <div>
                    <p class="text-xs font-semibold mb-3" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Plans Premium & Avantages</p>
                    <p style="color:var(--dim);font-size:12px;margin-bottom:16px">Les avantages sont affichés dynamiquement dans l'app mobile selon le plan sélectionné.</p>
                    @php
                        $defaultPlans = [
                            ['id' => 'weekly',    'label' => 'Hebdo',       'price' => 2500,  'days' => 7,  'features' => [
                                ['title' => 'Pronostics 3–4 étoiles',   'description' => 'Accès aux prédictions premium pendant 7 jours'],
                                ['title' => 'Coupon combiné quotidien',  'description' => '4–5 meilleurs picks combinés chaque jour'],
                                ['title' => 'Alertes push',              'description' => 'Notifications en temps réel sur vos matchs'],
                            ]],
                            ['id' => 'monthly',   'label' => 'Mensuel',     'price' => 8000,  'days' => 30, 'features' => [
                                ['title' => 'Pronostics 3–4 étoiles illimités', 'description' => 'Accès complet à toutes les prédictions premium'],
                                ['title' => 'Coupon combiné quotidien',          'description' => '4–5 meilleurs picks combinés chaque jour'],
                                ['title' => 'Analyses détaillées',               'description' => 'Critères, cotes estimées, historique complet'],
                                ['title' => 'Alertes push',                      'description' => 'Notifications en temps réel sur vos matchs'],
                                ['title' => 'Historique 30 jours',               'description' => 'Toutes vos prédictions et résultats du mois'],
                            ]],
                            ['id' => 'quarterly', 'label' => 'Trimestriel', 'price' => 20000, 'days' => 90, 'features' => [
                                ['title' => 'Pronostics 3–4 étoiles illimités',    'description' => 'Accès complet à toutes les prédictions premium'],
                                ['title' => 'Coupon combiné quotidien',             'description' => '4–5 meilleurs picks combinés chaque jour'],
                                ['title' => 'Analyses détaillées',                  'description' => 'Critères, cotes estimées, historique complet'],
                                ['title' => 'Alertes push prioritaires',            'description' => 'Notifications en temps réel + alertes VIP'],
                                ['title' => 'Historique illimité',                  'description' => 'Toutes les prédictions passées et résultats'],
                                ['title' => 'Accès prioritaire nouvelles features', 'description' => 'Fonctionnalités bêta en avant-première'],
                            ]],
                        ];
                        $savedPlans = collect($appConfig['premium_plans'] ?? [])->keyBy('id')->all();
                        $plans = array_map(function($p) use ($savedPlans) {
                            return array_merge($p, $savedPlans[$p['id']] ?? []);
                        }, $defaultPlans);
                    @endphp
                    <div id="plansContainer" class="space-y-6">
                        @foreach($plans as $j => $plan)
                            <div class="rounded-xl p-5" style="background:var(--bg-3);border:1px solid var(--line)">
                                {{-- En-tête plan --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold" style="background:var(--accent);color:var(--bg)">{{ strtoupper($plan['id']) }}</span>
                                    <input type="hidden" name="premium_plans[{{ $j }}][id]" value="{{ $plan['id'] }}">
                                    <div class="flex gap-3 flex-1">
                                        <div class="flex-1">
                                            <label class="block text-xs mb-1" style="color:var(--dim)">Label</label>
                                            <input type="text" name="premium_plans[{{ $j }}][label]" value="{{ $plan['label'] }}"
                                                   class="input-brand w-full" style="height:34px;font-size:12px">
                                        </div>
                                        <div style="width:120px">
                                            <label class="block text-xs mb-1" style="color:var(--dim)">Prix (FCFA)</label>
                                            <input type="number" name="premium_plans[{{ $j }}][price]" value="{{ $plan['price'] }}"
                                                   class="input-brand w-full" style="height:34px;font-size:12px">
                                        </div>
                                        <div style="width:100px">
                                            <label class="block text-xs mb-1" style="color:var(--dim)">Jours</label>
                                            <input type="number" name="premium_plans[{{ $j }}][days]" value="{{ $plan['days'] }}"
                                                   class="input-brand w-full" style="height:34px;font-size:12px">
                                        </div>
                                    </div>
                                </div>
                                {{-- Avantages --}}
                                <p class="text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.05em;text-transform:uppercase">Avantages affichés dans l'app</p>
                                <div id="features-{{ $plan['id'] }}" class="space-y-2">
                                    @foreach($plan['features'] as $k => $feat)
                                        <div class="flex gap-2 items-start feature-row" data-plan="{{ $j }}">
                                            <div class="flex-1">
                                                <input type="text" name="premium_plans[{{ $j }}][features][{{ $k }}][title]"
                                                       value="{{ $feat['title'] }}" placeholder="Titre de l'avantage"
                                                       class="input-brand w-full" style="height:32px;font-size:12px">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" name="premium_plans[{{ $j }}][features][{{ $k }}][description]"
                                                       value="{{ $feat['description'] }}" placeholder="Description (optionnel)"
                                                       class="input-brand w-full" style="height:32px;font-size:12px">
                                            </div>
                                            <button type="button" onclick="removeFeatureRow(this)"
                                                    class="flex-shrink-0 mt-1 text-xs px-2 py-1 rounded"
                                                    style="background:rgba(255,91,58,0.15);color:#ff5b3a;border:1px solid rgba(255,91,58,0.3)">
                                                ✕
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" onclick="addFeatureRow({{ $j }}, '{{ $plan['id'] }}')"
                                        class="mt-3 text-xs px-3 py-1.5 rounded"
                                        style="background:rgba(232,255,54,0.1);color:var(--accent);border:1px solid rgba(232,255,54,0.3)">
                                    + Ajouter un avantage
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="saveSettings('app', 'formApp')" class="btn-primary">
                        <i class="fa-solid fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.borderBottom = '2px solid transparent';
        b.style.color = 'var(--dim)';
    });
    document.getElementById('panel-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.style.borderBottom = '2px solid var(--accent)';
    btn.style.color = 'var(--accent)';
}

function saveSettings(endpoint, formId) {
    const form = document.getElementById(formId);
    const data = {};

    if (formId === 'formApp') {
        data['prediction_publish_hours'] = [...form.querySelectorAll('input[name="prediction_publish_hours[]"]:checked')].map(el => parseInt(el.value));
    }

    // Parsing générique : gère premium_plans[j][features][k][field] (3 niveaux)
    for (const [key, val] of new FormData(form).entries()) {
        // Niveau 3 : arr[i][sub][j][field]
        const m3 = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]\[(\d+)\]\[(\w+)\]$/);
        if (m3) {
            const [, arr, i, sub, j, field] = m3;
            if (!data[arr]) data[arr] = {};
            if (!data[arr][i]) data[arr][i] = {};
            if (!data[arr][i][sub]) data[arr][i][sub] = {};
            if (!data[arr][i][sub][j]) data[arr][i][sub][j] = {};
            data[arr][i][sub][j][field] = val;
            continue;
        }
        // Niveau 2 : arr[i][field]
        const m2 = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
        if (m2) {
            const [, arr, i, field] = m2;
            if (!data[arr]) data[arr] = {};
            if (!data[arr][i]) data[arr][i] = {};
            data[arr][i][field] = isNaN(val) ? val : (field === 'price' || field === 'days' ? parseInt(val) : val);
            continue;
        }
    }

    // Convertir objets imbriqués → tableaux
    if (data['premium_plans']) {
        data['premium_plans'] = Object.values(data['premium_plans']).map(plan => {
            if (plan.features) plan.features = Object.values(plan.features);
            return plan;
        });
    }

    fetch('/api/admin/settings/' + endpoint, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(res => showToast(res.success ? 'success' : 'error', res.message || (res.success ? 'Enregistré !' : 'Erreur')))
    .catch(() => showToast('error', 'Erreur réseau.'));
}

// ── Gestion dynamique des avantages ─────────────────────────────────────────
const featureCounters = {};

function addFeatureRow(planIdx, planId) {
    const container = document.getElementById('features-' + planId);
    if (!featureCounters[planIdx]) {
        featureCounters[planIdx] = container.querySelectorAll('.feature-row').length;
    }
    const k = featureCounters[planIdx]++;
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-start feature-row';
    row.dataset.plan = planIdx;
    row.innerHTML = `
        <div class="flex-1">
            <input type="text" name="premium_plans[${planIdx}][features][${k}][title]"
                   placeholder="Titre de l'avantage" class="input-brand w-full" style="height:32px;font-size:12px">
        </div>
        <div class="flex-1">
            <input type="text" name="premium_plans[${planIdx}][features][${k}][description]"
                   placeholder="Description (optionnel)" class="input-brand w-full" style="height:32px;font-size:12px">
        </div>
        <button type="button" onclick="removeFeatureRow(this)"
                class="flex-shrink-0 mt-1 text-xs px-2 py-1 rounded"
                style="background:rgba(255,91,58,0.15);color:#ff5b3a;border:1px solid rgba(255,91,58,0.3)">✕</button>`;
    container.appendChild(row);
}

function removeFeatureRow(btn) {
    btn.closest('.feature-row').remove();
}

// ── Toggle sources API ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.api-source-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const source  = this.dataset.source;
            const enabled = this.checked;
            const track   = this.parentElement.querySelector('.toggle-track');
            const thumb   = this.parentElement.querySelector('.toggle-thumb');

            track.style.background = enabled ? '#22c55e' : 'var(--line)';
            thumb.style.transform  = enabled ? 'translateX(20px)' : '';

            fetch('{{ route("admin.settings.api-source-toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ source: source, enabled: enabled }),
            })
            .then(r => r.json())
            .then(res => showToast(res.success ? 'success' : 'error', res.success ? (enabled ? source + ' activé' : source + ' désactivé') : 'Erreur'))
            .catch(() => showToast('error', 'Erreur réseau.'));
        });
    });
});

function showToast(type, msg) {
    const colors = { success: 'rgba(61,220,145,.12)', error: 'rgba(255,91,58,.12)' };
    const borders = { success: 'rgba(61,220,145,.3)', error: 'rgba(255,91,58,.3)' };
    const textColors = { success: 'var(--win)', error: 'var(--loss)' };
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle' };
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:12px;padding:14px 20px;border-radius:12px;background:${colors[type]};border:1px solid ${borders[type]};color:${textColors[type]};font-family:Space Grotesk,sans-serif;font-size:14px;font-weight:500`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}
</script>
@endpush
