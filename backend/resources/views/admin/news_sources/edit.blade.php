@extends('admin.layouts.app')
@section('title', 'Modifier — '.$newsSource->name)
@section('page-title', 'Modifier la source : '.$newsSource->name)

@section('content')
<div class="space-y-6" style="max-width:600px">

    @if(session('success'))
    <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px">
        <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="card">
        <form action="{{ route('admin.news-sources.update', $newsSource) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $newsSource->name) }}"
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:14px">
                </div>
                <div>
                    <label class="tag-mono block mb-2">Slug</label>
                    <input type="text" value="{{ $newsSource->slug }}" disabled
                        style="background:var(--bg-2);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--dim);font-size:13px;font-family:'JetBrains Mono',monospace;width:100%">
                </div>
            </div>

            <div>
                <label class="tag-mono block mb-2">URL RSS *</label>
                <input type="url" name="rss_url" value="{{ old('rss_url', $newsSource->rss_url) }}"
                    class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;font-family:'JetBrains Mono',monospace">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Site web</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $newsSource->website_url) }}"
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px">
                </div>
                <div>
                    <label class="tag-mono block mb-2">URL logo</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url', $newsSource->logo_url) }}"
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Langue *</label>
                    <select name="language" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                        <option value="fr" {{ old('language',$newsSource->language)==='fr'?'selected':'' }}>Français</option>
                        <option value="en" {{ old('language',$newsSource->language)==='en'?'selected':'' }}>English</option>
                        <option value="ar" {{ old('language',$newsSource->language)==='ar'?'selected':'' }}>العربية</option>
                        <option value="pt" {{ old('language',$newsSource->language)==='pt'?'selected':'' }}>Português</option>
                    </select>
                </div>
                <div>
                    <label class="tag-mono block mb-2">Catégorie *</label>
                    <select name="category" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                        <option value="football" {{ old('category',$newsSource->category)==='football'?'selected':'' }}>Football</option>
                        <option value="sport" {{ old('category',$newsSource->category)==='sport'?'selected':'' }}>Sport général</option>
                        <option value="local" {{ old('category',$newsSource->category)==='local'?'selected':'' }}>Local / Afrique</option>
                    </select>
                </div>
                <div>
                    <label class="tag-mono block mb-2">Intervalle (min)</label>
                    <input type="number" name="fetch_interval" value="{{ old('fetch_interval',$newsSource->fetch_interval) }}" min="15" max="1440"
                        style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ $newsSource->is_active ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <label for="is_active" style="color:var(--ink);font-size:14px">Source active</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('admin.news-sources.index') }}"
                   style="padding:10px 20px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:13px;text-decoration:none">
                    Retour
                </a>
            </div>
        </form>
    </div>

    {{-- Derniers articles --}}
    @if($recentArticles->isNotEmpty())
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--line)">
            <p class="tag-mono">Derniers articles récupérés</p>
        </div>
        @foreach($recentArticles as $art)
        <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px">
            @if($art->image_url)
            <img src="{{ $art->image_url }}" style="width:50px;height:50px;object-fit:cover;border-radius:6px;flex-shrink:0" onerror="this.style.display='none'">
            @endif
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;color:var(--ink);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $art->title }}</div>
                <div style="font-size:11px;color:var(--dim);margin-top:2px;font-family:'JetBrains Mono',monospace">
                    {{ $art->published_at?->diffForHumans() ?? '—' }}
                </div>
            </div>
            <a href="{{ $art->url }}" target="_blank" style="font-size:11px;color:var(--accent)">
                <i class="fa-solid fa-external-link-alt"></i>
            </a>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
