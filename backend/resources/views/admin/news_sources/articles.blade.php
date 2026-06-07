@extends('admin.layouts.app')
@section('title', 'Articles — '.$newsSource->name)
@section('page-title', 'Articles : '.$newsSource->name)

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <p style="color:var(--dim);font-size:14px">{{ $articles->total() }} articles · dernière mise à jour {{ $newsSource->last_fetched_at?->diffForHumans() ?? 'jamais' }}</p>
        <a href="{{ route('admin.news-sources.index') }}"
           style="padding:8px 16px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:13px;text-decoration:none">
            ← Retour sources
        </a>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        @forelse($articles as $art)
        <div style="padding:14px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:14px">

            @if($art->image_url)
            <img src="{{ $art->image_url }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;flex-shrink:0" onerror="this.style.display='none'">
            @else
            <div style="width:64px;height:64px;background:var(--bg-3);border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                <i class="fa-solid fa-newspaper" style="color:var(--dim);font-size:20px"></i>
            </div>
            @endif

            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $art->title }}
                </div>
                @if($art->tags && count($art->tags))
                <div style="margin-top:4px;display:flex;gap:4px;flex-wrap:wrap">
                    @foreach(array_slice($art->tags, 0, 4) as $tag)
                    <span style="font-size:9px;padding:2px 6px;border-radius:20px;background:rgba(232,255,54,.08);color:var(--accent);font-family:'JetBrains Mono',monospace">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
                <div style="font-size:11px;color:var(--dim);margin-top:3px;font-family:'JetBrains Mono',monospace">
                    {{ $art->published_at?->format('d/m/Y H:i') ?? '—' }}
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                <span style="font-size:10px;padding:2px 7px;border-radius:20px;font-family:'JetBrains Mono',monospace;font-weight:700;
                    {{ $art->is_active ? 'background:rgba(61,220,145,.12);color:var(--win)' : 'background:rgba(255,91,58,.12);color:var(--loss)' }}">
                    {{ $art->is_active ? 'VISIBLE' : 'MASQUÉ' }}
                </span>
                <form action="{{ route('admin.news-sources.toggle-article', $art) }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:6px 10px;background:var(--bg-3);border:1px solid var(--line);border-radius:6px;color:var(--dim);font-size:11px;cursor:pointer">
                        {{ $art->is_active ? 'Masquer' : 'Afficher' }}
                    </button>
                </form>
                <a href="{{ $art->url }}" target="_blank"
                   style="padding:6px 10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:6px;color:var(--accent);font-size:11px;text-decoration:none">
                    <i class="fa-solid fa-external-link-alt"></i>
                </a>
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center;color:var(--dim)">Aucun article pour cette source.</div>
        @endforelse
    </div>

    @if($articles->hasPages())
    <div>{{ $articles->links() }}</div>
    @endif

</div>
@endsection
