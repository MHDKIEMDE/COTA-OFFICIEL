@extends('admin.layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="space-y-6">

    {{-- ── Flash messages ──────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="rounded-lg px-4 py-3 text-sm font-medium" style="background:rgba(var(--win-rgb,74,222,128),0.12);color:var(--win);border:1px solid rgba(var(--win-rgb,74,222,128),0.3)">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg px-4 py-3 text-sm font-medium" style="background:rgba(var(--loss-rgb,239,68,68),0.12);color:var(--loss);border:1px solid rgba(var(--loss-rgb,239,68,68),0.3)">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ number_format($stats['total']) }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Total</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--accent);font-family:Archivo,sans-serif">{{ number_format($stats['premium']) }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Premium</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--win);font-family:Archivo,sans-serif">{{ $stats['new_today'] }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Nouveaux aujourd'hui</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ number_format($stats['active_7_days']) }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Actifs (7j)</p>
        </div>
    </div>

    {{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les statuts</option>
                <option value="premium" {{ request('status') == 'premium' ? 'selected' : '' }}>Premium</option>
                <option value="free"    {{ request('status') == 'free'    ? 'selected' : '' }}>Gratuit</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-brand" style="height:40px">
            <input type="date" name="date_to"   value="{{ request('date_to') }}"   class="input-brand" style="height:40px">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…" class="input-brand" style="height:40px">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.users.export') }}" class="btn-secondary btn-sm px-3" title="Exporter CSV">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full min-w-[700px]">
                <thead>
                    <tr>
                        <th class="text-left">Utilisateur</th>
                        <th class="text-left">Contact</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Premium expire</th>
                        <th class="text-center">Code parrainage</th>
                        <th class="text-center">Filleuls</th>
                        <th class="text-center">Inscrit le</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                         style="background:rgba(232,255,54,.12);border:1px solid rgba(232,255,54,.2);font-family:Archivo,sans-serif;font-weight:700;font-size:14px;color:var(--accent)">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="color:var(--ink);font-weight:600;font-size:14px">{{ $user->name ?? 'Sans nom' }}</div>
                                        <div style="color:var(--dim-2);font-size:12px">#{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="color:var(--ink-2);font-size:13px">{{ $user->phone }}</div>
                                @if($user->email)
                                    <div style="color:var(--dim);font-size:12px">{{ $user->email }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->is_premium)
                                    <span class="badge-accent"><i class="fa-solid fa-crown mr-1"></i>Premium</span>
                                @else
                                    <span style="font-size:11px;color:var(--dim)">Gratuit</span>
                                @endif
                            </td>
                            <td class="text-center" style="font-size:13px;color:var(--ink-2)">
                                @if($user->is_premium && $user->premium_expires_at)
                                    {{ $user->premium_expires_at->format('d/m/Y') }}
                                @elseif($user->is_premium)
                                    <span style="color:var(--accent)">∞ À vie</span>
                                @else
                                    <span style="color:var(--dim-2)">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <code style="padding:2px 8px;background:var(--bg-3);border:1px solid var(--line-2);border-radius:6px;color:var(--accent);font-family:JetBrains Mono,monospace;font-size:12px">{{ $user->referral_code }}</code>
                            </td>
                            <td class="text-center" style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $user->referrals_count ?? 0 }}</td>
                            <td class="text-center" style="color:var(--dim);font-size:13px">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" title="Voir"
                                       style="padding:6px 10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent)">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" title="Modifier"
                                       style="padding:6px 10px;background:rgba(61,220,145,.08);border:1px solid rgba(61,220,145,.2);border-radius:8px;color:var(--win)">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    @if(!$user->is_super_admin)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Supprimer"
                                                    style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:32px;text-align:center;color:var(--dim)">Aucun utilisateur trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div style="padding:16px 24px;border-top:1px solid var(--line)">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
