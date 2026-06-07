@extends('admin.layouts.app')

@section('title', 'Ajouter Bookmaker')
@section('page-title', 'Ajouter un Bookmaker')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.bookmakers.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card space-y-5">
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Identifiant (slug) *</label>
                <input type="text" name="id" value="{{ old('id') }}" required pattern="[a-z0-9_-]+"
                       class="input-brand w-full" placeholder="ex: betwinner, 1xbet, melbet">
                <p style="font-size:11px;color:var(--dim-2);margin-top:4px">Minuscules, chiffres, tirets et underscores uniquement</p>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Nom affiché *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="input-brand w-full" placeholder="ex: BetWinner">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Lien d'affiliation *</label>
                <input type="url" name="affiliate_url" value="{{ old('affiliate_url') }}" required
                       class="input-brand w-full" placeholder="https://betwinner.com/ref/XXXXX">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Jours Premium offerts *</label>
                <input type="number" name="bonus_days" value="{{ old('bonus_days', 7) }}" required min="1" max="365"
                       class="input-brand w-full">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Emoji Logo</label>
                    <input type="text" name="logo_emoji" value="{{ old('logo_emoji', '🎰') }}" maxlength="10"
                           class="input-brand w-full text-center" style="font-size:24px">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Couleur</label>
                    <input type="color" name="color" value="{{ old('color', '#6A1B9A') }}"
                           style="width:100%;height:44px;background:var(--bg-2);border:1px solid var(--line-2);border-radius:10px;cursor:pointer;padding:4px">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Description</label>
                <textarea name="description" rows="3" class="input-brand w-full" style="height:auto;padding:12px"
                          placeholder="Description affichée aux utilisateurs…">{{ old('description') }}</textarea>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <span style="font-size:14px;color:var(--ink-2)">Bookmaker actif (visible pour les utilisateurs)</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">
                <i class="fa-solid fa-save mr-2"></i>Créer le bookmaker
            </button>
            <a href="{{ route('admin.bookmakers.index') }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
