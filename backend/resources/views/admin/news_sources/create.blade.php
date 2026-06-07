@extends('admin.layouts.app')
@section('title', 'Ajouter une source')
@section('page-title', 'Ajouter une source RSS')

@section('content')
<div class="space-y-6" style="max-width:600px">

    @if($errors->any())
    <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px">
        <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="card">
        <form action="{{ route('admin.news-sources.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Nom de la source *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="ex: L'Équipe"
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:14px">
                </div>
                <div>
                    <label class="tag-mono block mb-2">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="ex: lequipe"
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:14px;font-family:'JetBrains Mono',monospace">
                </div>
            </div>

            <div>
                <label class="tag-mono block mb-2">URL du flux RSS *</label>
                <input type="url" name="rss_url" value="{{ old('rss_url') }}" placeholder="https://..."
                    class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;font-family:'JetBrains Mono',monospace">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Site web</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}" placeholder="https://..."
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px">
                </div>
                <div>
                    <label class="tag-mono block mb-2">URL logo (optionnel)</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://..."
                        class="w-full" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="tag-mono block mb-2">Langue *</label>
                    <select name="language" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                        <option value="fr" {{ old('language','fr')==='fr'?'selected':'' }}>Français</option>
                        <option value="en" {{ old('language')==='en'?'selected':'' }}>English</option>
                        <option value="ar" {{ old('language')==='ar'?'selected':'' }}>العربية</option>
                        <option value="pt" {{ old('language')==='pt'?'selected':'' }}>Português</option>
                    </select>
                </div>
                <div>
                    <label class="tag-mono block mb-2">Catégorie *</label>
                    <select name="category" style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                        <option value="football" {{ old('category','football')==='football'?'selected':'' }}>Football</option>
                        <option value="sport" {{ old('category')==='sport'?'selected':'' }}>Sport général</option>
                        <option value="local" {{ old('category')==='local'?'selected':'' }}>Local / Afrique</option>
                    </select>
                </div>
                <div>
                    <label class="tag-mono block mb-2">Intervalle (min) *</label>
                    <input type="number" name="fetch_interval" value="{{ old('fetch_interval', 30) }}" min="15" max="1440"
                        style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:10px 14px;color:var(--ink);font-size:13px;width:100%">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked style="width:16px;height:16px;accent-color:var(--accent)">
                <label for="is_active" style="color:var(--ink);font-size:14px">Activer cette source immédiatement</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter la source
                </button>
                <a href="{{ route('admin.news-sources.index') }}"
                   style="padding:10px 20px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:13px;text-decoration:none">
                    Annuler
                </a>
            </div>
        </form>
    </div>

    {{-- Sources populaires suggérées --}}
    <div class="card">
        <p class="tag-mono mb-4">Sources suggérées — clic pour pré-remplir</p>
        <div class="space-y-2">
            @foreach([
                ['L\'Équipe', 'lequipe', 'https://www.lequipe.fr/rss/actu-100_Football.xml', 'https://www.lequipe.fr', 'fr', 'football'],
                ['RMC Sport', 'rmc-sport', 'https://rmcsport.bfmtv.com/rss/football/', 'https://rmcsport.bfmtv.com', 'fr', 'football'],
                ['Eurosport', 'eurosport', 'https://www.eurosport.fr/rss.xml', 'https://www.eurosport.fr', 'fr', 'sport'],
                ['Africa Top Sports', 'africatopsports', 'https://www.africatopsports.com/feed/', 'https://www.africatopsports.com', 'fr', 'local'],
                ['Wiwsport', 'wiwsport', 'https://www.wiwsport.com/feed/', 'https://www.wiwsport.com', 'fr', 'local'],
                ['BBC Sport', 'bbc-sport', 'https://feeds.bbci.co.uk/sport/football/rss.xml', 'https://www.bbc.com/sport/football', 'en', 'football'],
                ['Goal.com', 'goal', 'https://www.goal.com/feeds/fr/news', 'https://www.goal.com/fr', 'fr', 'football'],
            ] as [$name, $slug, $rss, $site, $lang, $cat])
            <div onclick="fillForm('{{ $name }}','{{ $slug }}','{{ $rss }}','{{ $site }}','{{ $lang }}','{{ $cat }}')"
                 style="padding:10px 14px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:10px"
                 onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'">
                <img src="https://www.google.com/s2/favicons?domain={{ parse_url($site, PHP_URL_HOST) }}&sz=32"
                     width="20" height="20" onerror="this.style.display='none'">
                <span style="font-weight:700;color:var(--ink);font-size:13px">{{ $name }}</span>
                <span style="font-size:11px;color:var(--dim);font-family:'JetBrains Mono',monospace;margin-left:auto">{{ strtoupper($lang) }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function fillForm(name, slug, rss, site, lang, cat) {
    document.querySelector('[name=name]').value = name;
    document.querySelector('[name=slug]').value = slug;
    document.querySelector('[name=rss_url]').value = rss;
    document.querySelector('[name=website_url]').value = site;
    document.querySelector('[name=language]').value = lang;
    document.querySelector('[name=category]').value = cat;
}
</script>
@endpush
