@extends('admin.layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres du système')

@section('content')
<div class="space-y-6">

    {{-- Onglets --}}
    <div class="border-b border-gray-700/50 overflow-x-auto">
        <nav class="flex gap-1 -mb-px min-w-max" id="settingsTabs">
            @php $tabs = ['payment' => ['icon' => 'fa-credit-card', 'label' => 'Paiement'], 'apikeys' => ['icon' => 'fa-key', 'label' => 'Clés API'], 'bookmakers' => ['icon' => 'fa-link', 'label' => 'Bookmakers'], 'app' => ['icon' => 'fa-sliders', 'label' => 'Application']]; @endphp
            @foreach($tabs as $key => $tab)
                <button onclick="switchTab('{{ $key }}')" id="tab-{{ $key }}"
                    class="tab-btn flex items-center gap-2 px-5 py-3 text-sm font-medium border-b-2 transition
                           {{ $loop->first ? 'border-primary text-primary' : 'border-transparent text-gray-400 hover:text-white' }}">
                    <i class="fa-solid {{ $tab['icon'] }}"></i>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── PAIEMENT ────────────────────────────────────────────── --}}
    <div id="panel-payment" class="tab-panel space-y-6">
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-5">
                <i class="fa-solid fa-credit-card mr-2 text-primary"></i>
                Configuration du paiement
            </h3>
            <form id="formPayment" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Provider actif</label>
                        <select name="active_provider" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                            <option value="">— Aucun —</option>
                            @foreach($paymentData['available_drivers'] ?? [] as $driver)
                                <option value="{{ $driver }}" {{ $paymentData['active_provider'] === $driver ? 'selected' : '' }}>{{ ucfirst($driver) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Devise</label>
                        <input type="text" name="currency" value="{{ $paymentData['currency'] }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Webhook secret</label>
                        <input type="password" name="webhook_secret" value="{{ $paymentData['webhook_secret'] }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary"
                               autocomplete="new-password">
                    </div>
                </div>

                {{-- Providers --}}
                @if(!empty($paymentData['providers']))
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-300 mb-3">Providers configurés</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($paymentData['providers'] as $i => $prov)
                                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700/50 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-white">{{ $prov['label'] ?? $prov['slug'] }}</span>
                                        <span class="px-2 py-0.5 rounded text-xs {{ ($prov['env'] ?? 'test') === 'live' ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }}">
                                            {{ strtoupper($prov['env'] ?? 'test') }}
                                        </span>
                                    </div>
                                    <input type="hidden" name="providers[{{ $i }}][slug]" value="{{ $prov['slug'] }}">
                                    <input type="hidden" name="providers[{{ $i }}][label]" value="{{ $prov['label'] ?? $prov['slug'] }}">
                                    <div class="grid grid-cols-1 xs:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">API Key</label>
                                            <input type="text" name="providers[{{ $i }}][api_key]" value="{{ $prov['api_key'] ?? '' }}"
                                                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">API Secret</label>
                                            <input type="password" name="providers[{{ $i }}][api_secret]" value="{{ $prov['api_secret'] ?? '' }}"
                                                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary"
                                                   autocomplete="new-password">
                                        </div>
                                    </div>
                                    <select name="providers[{{ $i }}][env]" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white">
                                        <option value="test" {{ ($prov['env'] ?? 'test') === 'test' ? 'selected' : '' }}>Test</option>
                                        <option value="live" {{ ($prov['env'] ?? '') === 'live' ? 'selected' : '' }}>Live</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-2">
                    <button type="button" onclick="saveSettings('payment', 'formPayment')"
                            class="bg-primary hover:bg-primary/80 text-white px-6 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CLÉS API ─────────────────────────────────────────────── --}}
    <div id="panel-apikeys" class="tab-panel space-y-6 hidden">
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-5">
                <i class="fa-solid fa-key mr-2 text-warning"></i>
                Clés API externes
            </h3>
            <form id="formApikeys" class="space-y-4">
                @csrf
                @php
                    $apiFields = [
                        'football_api_key'   => ['label' => 'API-Football Key',       'type' => 'password'],
                        'openweather_key'    => ['label' => 'OpenWeatherMap Key',      'type' => 'password'],
                        'termii_key'         => ['label' => 'Termii API Key (SMS OTP)','type' => 'password'],
                        'termii_sender_id'   => ['label' => 'Termii Sender ID',        'type' => 'text'],
                        'facebook_app_id'    => ['label' => 'Facebook App ID',         'type' => 'text'],
                        'facebook_app_secret'=> ['label' => 'Facebook App Secret',     'type' => 'password'],
                    ];
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($apiFields as $name => $field)
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">{{ $field['label'] }}</label>
                            <input type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $apiKeys[$name] ?? '' }}"
                                   class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary"
                                   autocomplete="new-password">
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" onclick="saveSettings('api-keys', 'formApikeys')"
                            class="bg-primary hover:bg-primary/80 text-white px-6 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── BOOKMAKERS ───────────────────────────────────────────── --}}
    <div id="panel-bookmakers" class="tab-panel space-y-6 hidden">
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-5">
                <i class="fa-solid fa-link mr-2 text-secondary"></i>
                Bookmakers & liens affiliés
            </h3>
            <form id="formBookmakers" class="space-y-4">
                @csrf
                <div id="bookmakersContainer" class="space-y-4">
                    @forelse($bookmakers as $i => $bk)
                        <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700/50 space-y-3" id="bk-{{ $i }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-white">{{ $bk['name'] ?? $bk['id'] }}</span>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" name="bookmakers[{{ $i }}][is_active]" value="1"
                                           {{ ($bk['is_active'] ?? true) ? 'checked' : '' }}
                                           class="w-4 h-4 accent-primary">
                                    <span class="text-gray-300">Actif</span>
                                </label>
                            </div>
                            <input type="hidden" name="bookmakers[{{ $i }}][id]" value="{{ $bk['id'] }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Nom</label>
                                    <input type="text" name="bookmakers[{{ $i }}][name]" value="{{ $bk['name'] ?? '' }}"
                                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">URL d'affiliation</label>
                                    <input type="url" name="bookmakers[{{ $i }}][url]" value="{{ $bk['url'] ?? '' }}"
                                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Tracking ID</label>
                                    <input type="text" name="bookmakers[{{ $i }}][tracking_id]" value="{{ $bk['tracking_id'] ?? '' }}"
                                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Logo emoji</label>
                                    <input type="text" name="bookmakers[{{ $i }}][logo_emoji]" value="{{ $bk['logo_emoji'] ?? '' }}"
                                           maxlength="5"
                                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Couleur hex</label>
                                    <input type="text" name="bookmakers[{{ $i }}][color]" value="{{ $bk['color'] ?? '#6366F1' }}"
                                           maxlength="7" placeholder="#6366F1"
                                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-6">Aucun bookmaker configuré.</p>
                    @endforelse
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" onclick="saveSettings('bookmakers', 'formBookmakers')"
                            class="bg-primary hover:bg-primary/80 text-white px-6 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── APPLICATION ─────────────────────────────────────────── --}}
    <div id="panel-app" class="tab-panel space-y-6 hidden">
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-5">
                <i class="fa-solid fa-sliders mr-2 text-success"></i>
                Configuration de l'application
            </h3>
            <form id="formApp" class="space-y-6">
                @csrf

                {{-- Heures de publication --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">
                        Heures de publication des prédictions
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @foreach(range(0, 23) as $h)
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="prediction_publish_hours[]" value="{{ $h }}"
                                       {{ in_array($h, $appConfig['prediction_publish_hours'] ?? [8, 20]) ? 'checked' : '' }}
                                       class="w-4 h-4 accent-primary">
                                <span class="text-sm text-gray-300">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Plans premium --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Plans Premium</label>
                    @if(!empty($appConfig['premium_plans']))
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($appConfig['premium_plans'] as $j => $plan)
                                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700/50 space-y-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Label</label>
                                        <input type="text" name="premium_plans[{{ $j }}][label]" value="{{ $plan['label'] }}"
                                               class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Prix (FCFA)</label>
                                        <input type="number" name="premium_plans[{{ $j }}][price]" value="{{ $plan['price'] }}"
                                               class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Durée (jours)</label>
                                        <input type="number" name="premium_plans[{{ $j }}][days]" value="{{ $plan['days'] }}"
                                               class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-white focus:ring-1 focus:ring-primary">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Aucun plan configuré dans AppConfig.</p>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" onclick="saveSettings('app', 'formApp')"
                            class="bg-primary hover:bg-primary/80 text-white px-6 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Enregistrer
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
        b.classList.remove('border-primary', 'text-primary');
        b.classList.add('border-transparent', 'text-gray-400');
    });
    document.getElementById('panel-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.classList.add('border-primary', 'text-primary');
    btn.classList.remove('border-transparent', 'text-gray-400');
}

function saveSettings(endpoint, formId) {
    const form = document.getElementById(formId);
    const data = Object.fromEntries(new FormData(form).entries());

    // Checkboxes tableau (heures de publication)
    if (formId === 'formApp') {
        const hours = [...form.querySelectorAll('input[name="prediction_publish_hours[]"]:checked')].map(el => parseInt(el.value));
        data['prediction_publish_hours'] = hours;
        delete data['prediction_publish_hours[]'];
    }

    // Rebuild providers / bookmakers arrays
    const arrays = {};
    for (const [key, val] of new FormData(form).entries()) {
        const match = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
        if (match) {
            const [, arr, idx, field] = match;
            if (!arrays[arr]) arrays[arr] = {};
            if (!arrays[arr][idx]) arrays[arr][idx] = {};
            arrays[arr][idx][field] = val;
        }
    }
    for (const [arr, items] of Object.entries(arrays)) {
        data[arr] = Object.values(items);
        delete data[arr + '[]'];
    }
    delete data['_token'];

    fetch('/api/admin/settings/' + endpoint, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Authorization': 'Bearer ' + (document.cookie.match(/sanctum_token=([^;]+)/) || ['',''])[1],
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(res => {
        const msg = res.message || (res.success ? 'Enregistré !' : 'Erreur');
        showToast(res.success ? 'success' : 'error', msg);
    })
    .catch(() => showToast('error', 'Erreur réseau.'));
}

function showToast(type, msg) {
    const colors = { success: 'bg-success/20 border-success/50 text-success', error: 'bg-danger/20 border-danger/50 text-danger' };
    const icons  = { success: 'fa-check-circle', error: 'fa-exclamation-circle' };
    const el = document.createElement('div');
    el.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-xl border backdrop-blur ${colors[type]}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}
</script>
@endpush
