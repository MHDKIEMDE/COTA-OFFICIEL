@extends('admin.layouts.app')

@section('title', 'Parrainages')
@section('page-title', 'Système de Parrainage')

@section('content')
<div class="space-y-6">

    {{-- ── Stats ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([
            ['Total',        $stats['total'],         'var(--ink)'],
            ['Complétés',    $stats['completed'],     'var(--win)'],
            ['En attente',   $stats['pending'],       '#f5a623'],
            ['Récompenses',  $stats['rewards_given'], 'var(--accent)'],
            ['Ce mois',      $stats['this_month'],    'var(--ink-2)'],
        ] as [$label, $val, $color])
            <div class="card text-center">
                <p class="text-2xl font-bold" style="color:{{ $color }};font-family:Archivo,sans-serif">{{ $val }}</p>
                <p class="text-sm mt-1" style="color:var(--dim)">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Top Parrains ────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-trophy mr-2" style="color:#f5a623"></i>Top Parrains</p>
            <div class="space-y-3">
                @forelse($topReferrers as $i => $referrer)
                    <div class="flex items-center gap-3">
                        <span style="width:20px;text-align:center;font-size:12px;font-weight:700;
                            {{ $i === 0 ? 'color:#f5a623' : ($i === 1 ? 'color:var(--dim)' : ($i === 2 ? 'color:#b87333' : 'color:var(--dim-2)')) }}">#{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p style="color:var(--ink);font-size:13px;font-weight:500" class="truncate">{{ $referrer->name }}</p>
                            <p style="color:var(--dim);font-size:11px">Code : {{ $referrer->referral_code }}</p>
                        </div>
                        <span class="badge-win">{{ $referrer->referral_count }} filleuls</span>
                    </div>
                @empty
                    <p style="color:var(--dim);font-size:13px;text-align:center;padding:16px 0">Aucun parrainage complété</p>
                @endforelse
            </div>
        </div>

        {{-- ── Paliers ──────────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-gift mr-2" style="color:var(--accent)"></i>Paliers de récompenses</p>
            <div class="space-y-3">
                @foreach([1 => '3 jours', 3 => '7 jours', 10 => '30 jours', 50 => 'Premium à vie'] as $filleuls => $reward)
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <span style="color:var(--ink-2);font-size:13px">{{ $filleuls }} filleul{{ $filleuls > 1 ? 's' : '' }}</span>
                        <span style="color:var(--accent);font-weight:600;font-size:13px">{{ $reward }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Filtres ──────────────────────────────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-filter mr-2" style="color:var(--dim)"></i>Filtres</p>
            <form method="GET" class="space-y-3">
                <select name="status" class="input-brand w-full" style="height:40px;padding:0 12px">
                    <option value="">Tous les statuts</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>En attente</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher parrain / filleul…"
                       class="input-brand w-full" style="height:40px">
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm flex-1">
                        <i class="fa-solid fa-search mr-1"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.referrals.index') }}" class="btn-secondary btn-sm px-3">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-brand alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Parrain</th>
                        <th class="text-left">Filleul</th>
                        <th class="text-left">Code</th>
                        <th class="text-left">Statut</th>
                        <th class="text-left">Récompense</th>
                        <th class="text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $referral)
                    <tr>
                        <td style="color:var(--dim-2);font-size:12px;font-family:JetBrains Mono,monospace">{{ $referral->id }}</td>
                        <td>
                            <p style="color:var(--ink);font-weight:600;font-size:14px">{{ $referral->referrer->name ?? '—' }}</p>
                            <p style="color:var(--dim);font-size:12px">{{ $referral->referrer->email ?? $referral->referrer->phone ?? '' }}</p>
                        </td>
                        <td>
                            <p style="color:var(--ink-2);font-size:14px">{{ $referral->referred->name ?? '—' }}</p>
                            <p style="color:var(--dim);font-size:12px">{{ $referral->referred->email ?? $referral->referred->phone ?? '' }}</p>
                        </td>
                        <td>
                            <code style="padding:2px 8px;background:var(--bg-3);border:1px solid var(--line-2);border-radius:6px;color:var(--accent);font-family:JetBrains Mono,monospace;font-size:12px">{{ $referral->referral_code ?? '—' }}</code>
                        </td>
                        <td>
                            @php
                                $sMap = ['completed' => 'badge-win', 'pending' => 'badge-pending', 'cancelled' => 'badge-loss'];
                                $lMap = ['completed' => 'Complété', 'pending' => 'En attente', 'cancelled' => 'Annulé'];
                            @endphp
                            <span class="{{ $sMap[$referral->status] ?? '' }}">{{ $lMap[$referral->status] ?? $referral->status }}</span>
                        </td>
                        <td>
                            @if($referral->reward_granted)
                                <span class="badge-accent">{{ $referral->reward_days ?? '?' }}j offerts</span>
                            @else
                                <span style="color:var(--dim-2);font-size:12px">—</span>
                            @endif
                        </td>
                        <td style="color:var(--dim);font-size:12px">{{ $referral->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding:48px;text-align:center;color:var(--dim)">
                            <i class="fa-solid fa-people-arrows" style="font-size:28px;display:block;margin-bottom:12px;color:var(--dim-2)"></i>
                            Aucun parrainage trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $referrals->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection
