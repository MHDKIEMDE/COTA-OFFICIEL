@extends('admin.layouts.app')
@section('title', 'Nouvel article bookmaker')
@section('page-title', 'Créer un article / média bookmaker')

@section('content')
<div style="max-width:760px">

    @if($errors->any())
        <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px;margin-bottom:20px">
            <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.bookmaker-blogs.store') }}" method="POST" class="space-y-6" id="blog-form">
        @csrf

        {{-- ── Méta ─────────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <div class="flex items-center justify-between mb-4">
                <h3 class="section-title mb-0"><i class="fa-solid fa-pen-nib mr-2"></i>Article</h3>
                <button type="button" onclick="generateWithAI()"
                    style="display:flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.25);border-radius:8px;color:var(--accent);font-size:12px;font-weight:700;cursor:pointer"
                    id="ai-btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec IA
                </button>
            </div>
            <div id="ai-status" class="hidden mb-3 text-sm" style="color:var(--dim)"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fl">Bookmaker *</label>
                    <select name="bookmaker_id" required class="fi">
                        <option value="">— Choisir —</option>
                        @foreach($bookmakers as $bm)
                        <option value="{{ $bm->id }}" {{ old('bookmaker_id') == $bm->id ? 'selected' : '' }}>{{ $bm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="fl">Catégorie *</label>
                    <select name="category" required class="fi" id="cat-select" onchange="updateCategoryHints()">
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', 'guide') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="fl">Titre de l'article</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="fi"
                           placeholder="ex: Comment s'inscrire sur 1xBet et obtenir 200% de bonus">
                </div>
                <div class="col-span-2">
                    <label class="fl">Accroche courte <span style="color:var(--dim)">(affiché dans la liste)</span></label>
                    <textarea name="excerpt" rows="2" class="fi"
                              placeholder="Résumé en 1-2 phrases...">{{ old('excerpt') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Média ────────────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px" id="media-section">
            <h3 class="section-title"><i class="fa-solid fa-photo-film mr-2"></i>Média</h3>
            <div class="space-y-4">
                <div id="media-hint" style="padding:10px 14px;background:rgba(232,255,54,.06);border:1px solid rgba(232,255,54,.15);border-radius:8px;font-size:12px;color:var(--dim)">
                    Sélectionne une catégorie pour voir le type de média attendu.
                </div>
                <div>
                    <label class="fl" id="media-label">URL du média</label>
                    <input type="url" name="media_url" value="{{ old('media_url') }}" class="fi"
                           placeholder="https://..." id="media-input">
                </div>
                <div>
                    <label class="fl">URL de la miniature <span style="color:var(--dim)">(image d'aperçu)</span></label>
                    <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url') }}" class="fi"
                           placeholder="https://...">
                </div>
            </div>
        </div>

        {{-- ── Code promo & Bonus ───────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 class="section-title"><i class="fa-solid fa-gift mr-2"></i>Code promo & Bonus</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fl">Code promo COTA</label>
                        <input type="text" name="promo_code" value="{{ old('promo_code') }}"
                               maxlength="50" class="fi"
                               style="text-transform:uppercase;letter-spacing:3px;font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--accent)"
                               placeholder="COTA2024">
                    </div>
                    <div>
                        <label class="fl">Label du bouton CTA</label>
                        <input type="text" name="cta_label" value="{{ old('cta_label', "S'inscrire et obtenir le bonus") }}"
                               maxlength="150" class="fi">
                    </div>
                </div>
                <div>
                    <label class="fl">Titre du bonus</label>
                    <input type="text" name="bonus_title" value="{{ old('bonus_title') }}" maxlength="255" class="fi"
                           placeholder="ex: Bonus 200% jusqu'à 100 000 FCFA">
                </div>
                <div>
                    <label class="fl">Description du bonus</label>
                    <textarea name="bonus_description" rows="4" class="fi"
                              placeholder="Conditions détaillées...">{{ old('bonus_description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Étapes guide (affiché uniquement si guide/tutoriel) ─────────── --}}
        <div class="card" style="padding:20px" id="steps-section">
            <h3 class="section-title"><i class="fa-solid fa-list-ol mr-2"></i>Étapes <span style="color:var(--dim);font-size:12px;font-weight:400">(optionnel)</span></h3>
            <div id="steps-container" class="space-y-3"></div>
            <button type="button" onclick="addStep()"
                    style="margin-top:12px;padding:8px 16px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);font-size:12px;font-weight:700;cursor:pointer">
                <i class="fa-solid fa-plus mr-2"></i>Ajouter une étape
            </button>
        </div>

        {{-- ── Publication ──────────────────────────────────────────────────── --}}
        <div class="card" style="padding:20px">
            <h3 class="section-title"><i class="fa-solid fa-calendar mr-2"></i>Publication</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="fl">Date de publication</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="fi">
                </div>
                <div style="display:flex;gap:16px;align-items:center;padding-top:20px">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:var(--win)">
                        <span style="font-size:13px;color:var(--ink)">Actif</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" name="is_featured" value="1" style="width:16px;height:16px;accent-color:var(--accent)">
                        <span style="font-size:13px;color:var(--ink)">⭐ Mis en avant</span>
                    </label>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px">
            <a href="{{ route('admin.bookmaker-blogs.index') }}"
               style="padding:12px 20px;background:var(--bg-2);border:1px solid var(--line);border-radius:10px;color:var(--dim);font-size:14px;font-weight:600;text-decoration:none">Annuler</a>
            <button type="submit"
                    style="flex:1;padding:12px;background:var(--accent);border:none;border-radius:10px;color:#0b0d10;font-family:Archivo,sans-serif;font-weight:900;font-size:14px;cursor:pointer">
                <i class="fa-solid fa-save mr-2"></i>PUBLIER L'ARTICLE
            </button>
        </div>
    </form>
</div>

<style>
.section-title { font-family:Archivo,sans-serif;font-weight:900;font-size:15px;color:var(--accent);margin-bottom:16px;text-transform:uppercase;letter-spacing:1px }
.fl { display:block;font-size:12px;color:var(--dim);margin-bottom:4px }
.fi { width:100%;padding:10px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px;box-sizing:border-box;outline:none }
.fi:focus { border-color:rgba(232,255,54,.4) }
textarea.fi { resize:vertical;line-height:1.5 }
</style>

<script>
let stepCount = 0;

const hints = {
    guide:     { label: 'URL non requise (guide textuel)', placeholder: '', show: false },
    tutoriel:  { label: 'URL vidéo tutoriel (YouTube, Vimeo…)', placeholder: 'https://youtube.com/watch?v=...', show: true },
    video:     { label: 'URL de la vidéo (YouTube, Vimeo…)', placeholder: 'https://youtube.com/watch?v=...', show: true },
    photo:     { label: 'URL de l\'image / infographie', placeholder: 'https://...', show: true },
    promotion: { label: 'URL de la landing page promo', placeholder: 'https://...', show: true },
    actualite: { label: 'URL de la source', placeholder: 'https://...', show: false },
};

const hintTexts = {
    guide:     '📖 Guide textuel avec étapes — le média est optionnel.',
    tutoriel:  '🎓 Ajoute l\'URL YouTube/Vimeo du tutoriel vidéo.',
    video:     '🎬 Ajoute l\'URL YouTube/Vimeo de la vidéo.',
    photo:     '📸 Ajoute l\'URL directe de l\'image ou infographie.',
    promotion: '🎁 Ajoute l\'URL vers la page de promotion.',
    actualite: '📰 Article de texte — le lien source est optionnel.',
};

function updateCategoryHints() {
    const cat = document.getElementById('cat-select').value;
    const h = hints[cat] || {};
    document.getElementById('media-label').textContent = h.label || 'URL du média';
    document.getElementById('media-input').placeholder = h.placeholder || 'https://...';
    document.getElementById('media-hint').textContent = hintTexts[cat] || '';
    document.getElementById('steps-section').style.display =
        (cat === 'guide' || cat === 'tutoriel') ? 'block' : 'none';
}

function addStep() {
    const i = stepCount++;
    const div = document.createElement('div');
    div.style = 'background:var(--bg-2);border:1px solid var(--line);border-radius:8px;padding:12px';
    div.innerHTML = `
        <div style="display:flex;gap:8px;margin-bottom:8px;align-items:start">
            <input type="text" name="steps[${i}][icon]" placeholder="emoji" maxlength="10"
                   style="width:52px;padding:8px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-size:16px;text-align:center">
            <input type="text" name="steps[${i}][title]" placeholder="Titre de l'étape" required
                   style="flex:1;padding:8px 10px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-size:13px">
            <button type="button" onclick="this.closest('div[style]').remove()"
                    style="padding:8px 10px;background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.2);border-radius:6px;color:var(--loss);cursor:pointer;font-size:12px">✕</button>
        </div>
        <textarea name="steps[${i}][body]" rows="2" placeholder="Description de l'étape..."
                  style="width:100%;padding:8px 10px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-size:12px;resize:vertical;box-sizing:border-box"></textarea>
    `;
    document.getElementById('steps-container').appendChild(div);
}

updateCategoryHints();

function generateWithAI() {
    const bmId    = document.querySelector('[name="bookmaker_id"]').value;
    const cat     = document.querySelector('[name="category"]').value;
    const btn     = document.getElementById('ai-btn');
    const status  = document.getElementById('ai-status');

    if (!bmId) { alert('Veuillez sélectionner un bookmaker d\'abord.'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Génération en cours…';
    status.textContent = '';
    status.classList.add('hidden');

    fetch('{{ route("admin.bookmaker-blogs.generate-ai") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ bookmaker_id: bmId, category: cat }),
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec IA';
        if (res.success) {
            const d = res.data;
            if (d.title)             document.querySelector('[name="title"]').value             = d.title;
            if (d.excerpt)           document.querySelector('[name="excerpt"]').value           = d.excerpt;
            if (d.bonus_description) document.querySelector('[name="bonus_description"]').value = d.bonus_description;
            status.textContent = '✓ Contenu généré par Claude Haiku — relisez avant de publier.';
            status.style.color = 'var(--win)';
            status.classList.remove('hidden');
        } else {
            status.textContent = res.message || 'Erreur lors de la génération.';
            status.style.color = 'var(--loss)';
            status.classList.remove('hidden');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Générer avec IA';
        status.textContent = 'Erreur réseau.';
        status.style.color = 'var(--loss)';
        status.classList.remove('hidden');
    });
}
</script>
@endsection
