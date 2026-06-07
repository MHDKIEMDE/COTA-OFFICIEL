@extends('admin.layouts.app')
@section('title', 'Sources Actualités')
@section('page-title', 'Sources Actualités')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p style="color:var(--dim);font-size:14px">{{ $sources->count() }} sources configurées · {{ number_format($totalArticles) }} articles en base</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('admin.news-sources.fetch-all') }}" method="POST">
                @csrf
                <button type="submit" style="padding:9px 16px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);font-size:13px;font-weight:600;cursor:pointer">
                    <i class="fa-solid fa-rotate mr-2"></i>Fetch toutes les sources
                </button>
            </form>
            <a href="{{ route('admin.news-sources.create') }}" class="btn-primary btn-sm">
                <i class="fa-solid fa-plus mr-2"></i>Ajouter une source
            </a>
        </div>
    </div>

    {{-- ── Liste des sources ────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        @forelse($sources as $src)
        <div style="padding:16px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px">

            {{-- Logo --}}
            <div style="width:42px;height:42px;border-radius:10px;background:var(--bg-3);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                @if($src->logo_url)
                    <img src="{{ $src->logo_url }}" style="width:28px;height:28px;object-fit:contain" onerror="this.style.display='none'">
                @elseif($src->website_url)
                    <img src="https://www.google.com/s2/favicons?domain={{ parse_url($src->website_url, PHP_URL_HOST) }}&sz=32"
                         style="width:24px;height:24px" onerror="this.style.display='none'">
                @else
                    <i class="fa-solid fa-newspaper" style="color:var(--accent);font-size:16px"></i>
                @endif
            </div>

            {{-- Infos --}}
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-family:Archivo,sans-serif;font-weight:700;font-size:15px;color:var(--ink)">{{ $src->name }}</span>
                    <span style="font-size:10px;padding:2px 7px;border-radius:20px;font-family:'JetBrains Mono',monospace;font-weight:700;
                        {{ $src->is_active ? 'background:rgba(61,220,145,.12);color:var(--win)' : 'background:rgba(255,91,58,.12);color:var(--loss)' }}">
                        {{ $src->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                    <span style="font-size:10px;padding:2px 7px;border-radius:20px;background:rgba(232,255,54,.08);color:var(--accent);font-family:'JetBrains Mono',monospace">
                        {{ strtoupper($src->language) }} · {{ $src->category }}
                    </span>
                </div>
                <div style="font-size:11px;color:var(--dim);margin-top:3px;font-family:'JetBrains Mono',monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:400px">
                    {{ $src->rss_url }}
                </div>
                <div style="font-size:11px;color:var(--dim-2);margin-top:2px">
                    {{ $src->articles_count }} articles ·
                    @if($src->last_fetched_at)
                        Dernier fetch {{ $src->last_fetched_at->diffForHumans() }}
                    @else
                        Jamais fetchée
                    @endif
                    · Intervalle {{ $src->fetch_interval }}min
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:6px;flex-shrink:0">
                <form action="{{ route('admin.news-sources.fetch-now', $src) }}" method="POST">
                    @csrf
                    <button type="submit" title="Fetch maintenant" style="padding:7px 12px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);font-size:12px;cursor:pointer">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </form>
                <a href="{{ route('admin.news-sources.articles', $src) }}"
                   style="padding:7px 12px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;text-decoration:none">
                    <i class="fa-solid fa-list"></i>
                </a>
                <a href="{{ route('admin.news-sources.edit', $src) }}"
                   style="padding:7px 12px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;text-decoration:none">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form action="{{ route('admin.news-sources.toggle', $src) }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:7px 12px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;cursor:pointer"
                            title="{{ $src->is_active ? 'Désactiver' : 'Activer' }}">
                        <i class="fa-solid {{ $src->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                </form>
                <form action="{{ route('admin.news-sources.destroy', $src) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette source et ses articles ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="padding:7px 12px;background:rgba(255,91,58,.08);border:1px solid rgba(255,91,58,.2);border-radius:8px;color:var(--loss);font-size:12px;cursor:pointer">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center">
            <div style="font-size:40px;margin-bottom:16px">📰</div>
            <p style="color:var(--dim);font-size:15px">Aucune source configurée.</p>
            <a href="{{ route('admin.news-sources.create') }}" class="btn-primary btn-sm" style="display:inline-block;margin-top:12px">
                Ajouter une source RSS
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
