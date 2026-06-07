@extends('admin.layouts.app')

@section('title', 'Détails Utilisateur')
@section('page-title', 'Détails Utilisateur')

@section('content')
<div class="space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="flex flex-col md:flex-row gap-6 items-start">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center shrink-0"
                 style="background:rgba(232,255,54,.12);border:1px solid rgba(232,255,54,.2);font-family:Archivo,sans-serif;font-weight:900;font-size:28px;color:var(--accent)">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <h2 style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--ink)">{{ $user->name ?? 'Sans nom' }}</h2>
                    @if($user->is_premium)
                        <span class="badge-accent"><i class="fa-solid fa-crown mr-1"></i>Premium</span>
                    @endif
                    @if($user->is_super_admin)
                        <span class="badge-loss"><i class="fa-solid fa-shield mr-1"></i>Super Admin</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([['Téléphone', $user->phone], ['Email', $user->email ?? '—'], ['Inscrit le', $user->created_at->format('d/m/Y H:i')], ['Dernière connexion', $user->last_login_at?->format('d/m/Y H:i') ?? '—']] as [$label, $val])
                        <div>
                            <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">{{ $label }}</p>
                            <p style="color:var(--ink-2);font-size:14px;font-weight:500">{{ $val }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary btn-sm">
                <i class="fa-solid fa-edit mr-1"></i> Modifier
            </a>
        </div>
    </div>

    {{-- ── Stats & Premium ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card">
            <p class="tag-mono mb-4">Statistiques</p>
            <div class="space-y-3">
                @foreach([['Abonnements', $user->subscriptions_count ?? 0], ['Parrainages', $user->referrals_count ?? 0], ['Feedbacks', $user->feedbacks_count ?? 0], ['Affiliations', $user->affiliation_bonus_count ?? 0]] as [$label, $val])
                    <div class="flex justify-between items-center">
                        <span style="color:var(--ink-2);font-size:13px">{{ $label }}</span>
                        <span style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card md:col-span-2">
            <p class="tag-mono mb-4"><i class="fa-solid fa-crown mr-2" style="color:var(--accent)"></i>Gestion Premium</p>
            <div class="mb-4">
                @if($user->is_premium)
                    <div class="p-4 rounded-lg" style="background:rgba(232,255,54,.06);border:1px solid rgba(232,255,54,.2)">
                        <div class="flex items-center gap-2 mb-2" style="color:var(--accent);font-weight:600">
                            <i class="fa-solid fa-check-circle"></i>Abonnement Premium actif
                        </div>
                        @if($user->premium_expires_at)
                            <p style="font-size:13px;color:var(--ink-2)">
                                Expire le <strong>{{ $user->premium_expires_at->format('d/m/Y H:i') }}</strong>
                                ({{ $user->premium_expires_at->diffForHumans() }})
                            </p>
                        @else
                            <p style="font-size:13px;color:var(--accent)">Premium à vie</p>
                        @endif
                        <p style="font-size:12px;color:var(--dim);margin-top:4px">Source : {{ $user->premium_source ?? 'Non spécifié' }}</p>
                    </div>
                @else
                    <div class="p-4 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <p style="color:var(--dim);font-size:14px">Pas d'abonnement Premium actif.</p>
                    </div>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <form action="{{ route('admin.users.addPremium', $user) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="number" name="days" min="1" max="365" value="7" class="input-brand flex-1" style="height:40px">
                    <button type="submit" class="btn-primary btn-sm">+ Jours</button>
                </form>
                <form action="{{ route('admin.users.lifetimePremium', $user) }}" method="POST"
                      onsubmit="return confirm('Accorder le premium à vie ?')">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm w-full">
                        <i class="fa-solid fa-infinity mr-1"></i> À vie
                    </button>
                </form>
                @if($user->is_premium)
                    <form action="{{ route('admin.users.revokePremium', $user) }}" method="POST"
                          onsubmit="return confirm('Révoquer le premium ?')">
                        @csrf
                        <button type="submit" class="btn-danger btn-sm w-full">
                            <i class="fa-solid fa-ban mr-1"></i> Révoquer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Parrainage ───────────────────────────────────────────────────────── --}}
    <div class="card">
        <p class="tag-mono mb-4"><i class="fa-solid fa-gift mr-2" style="color:var(--accent)"></i>Parrainage</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['Code parrain', $user->referral_code, true],
                ['Filleuls', $user->referral_count ?? 0, false],
                ['Jours gagnés', ($user->referral_days_earned ?? 0) . 'j', false],
                ['Parrainé par', $user->referred_by ? '#' . $user->referred_by : '—', false],
            ] as [$label, $val, $mono])
                <div class="p-4 text-center rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                    <p class="text-xs mb-2" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">{{ $label }}</p>
                    <p style="font-weight:700;{{ $mono ? 'font-family:JetBrains Mono,monospace;color:var(--accent)' : 'color:var(--ink)' }};font-size:16px">{{ $val }}</p>
                </div>
            @endforeach
        </div>

        @if($user->referrals->count() > 0)
            <p class="text-xs mb-3" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">Derniers filleuls</p>
            <div class="space-y-2">
                @foreach($user->referrals as $referral)
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                 style="background:rgba(232,255,54,.1);color:var(--accent);font-size:12px;font-weight:700">
                                {{ strtoupper(substr($referral->referredUser->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p style="color:var(--ink);font-size:13px">{{ $referral->referredUser->name ?? 'Sans nom' }}</p>
                                <p style="color:var(--dim);font-size:11px">{{ $referral->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span style="color:var(--win);font-size:13px;font-family:JetBrains Mono,monospace">+{{ $referral->bonus_days ?? 0 }}j</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Affiliations ─────────────────────────────────────────────────────── --}}
    @if($user->affiliationBonus->count() > 0)
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-handshake mr-2" style="color:var(--accent)"></i>Affiliations</p>
            <div class="space-y-2">
                @foreach($user->affiliationBonus as $bonus)
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <div>
                            <span style="color:var(--ink);font-weight:600">{{ ucfirst($bonus->bookmaker) }}</span>
                            <span style="color:var(--dim);font-size:12px;margin-left:8px">ID : {{ $bonus->player_id }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span style="color:var(--win);font-family:JetBrains Mono,monospace;font-size:13px">+{{ $bonus->bonus_days }}j</span>
                            @if($bonus->is_verified)
                                <span class="badge-win">Vérifié</span>
                            @else
                                <span class="badge-pending">En attente</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <a href="{{ route('admin.users.index') }}" style="font-size:13px;color:var(--dim)">
        <i class="fa-solid fa-arrow-left mr-2"></i> Retour à la liste
    </a>

</div>
@endsection
