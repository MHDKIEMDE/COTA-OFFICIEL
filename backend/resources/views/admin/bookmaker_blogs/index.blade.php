@extends('admin.layouts.app')

@section('title', 'Blogs Bookmakers')
@section('page-title', 'Marketing Bookmakers — Articles & Médias')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p style="color:var(--dim);font-size:14px">Créez des guides, vidéos et promotions pour chaque bookmaker partenaire.</p>
        <a href="{{ route('admin.bookmaker-blogs.create') }}" class="btn-primary btn-sm">
            <i class="fa-solid fa-plus mr-2"></i>Nouvel article
        </a>
    </div>

    {{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
        <select name="category" onchange="this.form.submit()"
                style="padding:8px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:13px">
            <option value="">Toutes catégories</option>
            @foreach(\App\Models\BookmakerBlog::CATEGORIES as $key => $label)
            <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="bookmaker_id" onchange="this.form.submit()"
                style="padding:8px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:13px">
            <option value="">Tous les bookmakers</option>
            @foreach($bookmakers as $bm)
            <option value="{{ $bm->id }}" {{ $bmId == $bm->id ? 'selected' : '' }}>{{ $bm->name }}</option>
            @endforeach
        </select>
    </form>

    {{-- ── Grille articles ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($blogs as $blog)
        <div class="card" style="padding:0;overflow:hidden;position:relative;{{ !$blog->is_active ? 'opacity:.55' : '' }}">

            @if($blog->is_featured)
            <div style="position:absolute;top:10px;right:10px;background:var(--accent);color:#0b0d10;font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;font-family:'JetBrains Mono',monospace">
                ⭐ MIS EN AVANT
            </div>
            @endif

            {{-- Miniature / catégorie --}}
            <div style="height:90px;background:linear-gradient(135deg,rgba(232,255,54,.12),var(--bg-2));display:flex;align-items:center;justify-content:center;border-bottom:1px solid var(--line);position:relative">
                @if($blog->thumbnail_url)
                    <img src="{{ $blog->thumbnail_url }}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;opacity:.4">
                @endif
                <div style="z-index:1;text-align:center">
                    <div style="font-size:28px">
                        @switch($blog->category)
                            @case('video') 🎬 @break
                            @case('photo') 📸 @break
                            @case('tutoriel') 🎓 @break
                            @case('promotion') 🎁 @break
                            @case('actualite') 📰 @break
                            @default 📖
                        @endswitch
                    </div>
                    <div style="font-size:9px;color:var(--accent);font-family:'JetBrains Mono',monospace;font-weight:700;letter-spacing:1px;margin-top:2px">
                        {{ strtoupper($blog->category_label) }}
                    </div>
                </div>
            </div>

            {{-- Contenu --}}
            <div style="padding:14px 16px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:rgba(232,255,54,.08);color:var(--accent);font-family:'JetBrains Mono',monospace;font-weight:700">
                        {{ $blog->bookmaker->name ?? '—' }}
                    </span>
                    @if($blog->promo_code)
                    <span style="font-size:10px;color:var(--dim);font-family:'JetBrains Mono',monospace">{{ $blog->promo_code }}</span>
                    @endif
                </div>

                <h4 style="font-family:Archivo,sans-serif;font-weight:700;font-size:14px;color:var(--ink);margin-bottom:4px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                    {{ $blog->title ?? $blog->bonus_title ?? 'Guide '.$blog->bookmaker?->name }}
                </h4>

                @if($blog->excerpt)
                <p style="font-size:12px;color:var(--dim);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                    {{ $blog->excerpt }}
                </p>
                @endif

                <div style="font-size:10px;color:var(--dim-2);font-family:'JetBrains Mono',monospace;margin-top:8px">
                    {{ $blog->published_at?->format('d/m/Y') ?? 'Brouillon' }}
                </div>
            </div>

            {{-- Actions --}}
            <div style="padding:10px 14px;border-top:1px solid var(--line);display:flex;gap:8px">
                <a href="{{ route('admin.bookmaker-blogs.edit', $blog) }}"
                   style="flex:1;text-align:center;padding:7px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);font-size:11px;font-weight:700;text-decoration:none">
                    MODIFIER
                </a>
                <form action="{{ route('admin.bookmaker-blogs.toggle-featured', $blog) }}" method="POST">
                    @csrf
                    <button type="submit" title="{{ $blog->is_featured ? 'Retirer' : 'Mettre en avant' }}"
                            style="padding:7px 10px;background:{{ $blog->is_featured ? 'rgba(232,255,54,.15)' : 'var(--bg-2)' }};border:1px solid {{ $blog->is_featured ? 'rgba(232,255,54,.3)' : 'var(--line)' }};border-radius:8px;color:{{ $blog->is_featured ? 'var(--accent)' : 'var(--dim)' }};font-size:12px;cursor:pointer">
                        ⭐
                    </button>
                </form>
                <form action="{{ route('admin.bookmaker-blogs.destroy', $blog) }}" method="POST"
                      onsubmit="return confirm('Supprimer cet article ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="padding:7px 10px;background:rgba(255,91,58,.08);border:1px solid rgba(255,91,58,.2);border-radius:8px;color:var(--loss);font-size:12px;cursor:pointer">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 card" style="padding:60px;text-align:center">
            <div style="font-size:44px;margin-bottom:16px">📝</div>
            <h3 style="color:var(--ink);font-weight:700;font-size:18px;margin-bottom:8px">Aucun article</h3>
            <p style="color:var(--dim);margin-bottom:20px">Crée ton premier guide ou article promotionnel.</p>
            <a href="{{ route('admin.bookmaker-blogs.create') }}" class="btn-primary btn-sm">
                <i class="fa-solid fa-plus mr-2"></i>Créer un article
            </a>
        </div>
        @endforelse
    </div>

    @if($blogs->hasPages())
    <div>{{ $blogs->appends(request()->query())->links() }}</div>
    @endif

</div>
@endsection
