@extends('admin.layouts.app')

@section('title', 'Modifier Utilisateur')
@section('page-title', "Modifier l'Utilisateur")

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── Infos de base ────────────────────────────────────────────────── --}}
        <div class="card space-y-5">
            <p class="tag-mono"><i class="fa-solid fa-user mr-2" style="color:var(--accent)"></i>Informations de base</p>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-brand w-full">
                @error('name')<p style="font-size:12px;color:var(--loss);margin-top:4px">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Téléphone *</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="input-brand w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-2" style="color:var(--dim);letter-spacing:.06em;text-transform:uppercase">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-brand w-full">
            </div>
        </div>

        {{-- ── Permissions ──────────────────────────────────────────────────── --}}
        <div class="card space-y-4">
            <p class="tag-mono"><i class="fa-solid fa-shield mr-2" style="color:var(--accent)"></i>Permissions</p>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_premium" value="1" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <span style="font-size:14px;color:var(--ink-2)">
                    <i class="fa-solid fa-crown mr-1" style="color:var(--accent)"></i>Compte Premium
                </span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--accent)">
                <span style="font-size:14px;color:var(--ink-2)">
                    <i class="fa-solid fa-user-tie mr-1" style="color:var(--accent)"></i>Administrateur
                </span>
            </label>
            @if(auth()->user()->is_super_admin)
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_super_admin" value="1" {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                               style="width:16px;height:16px;accent-color:var(--loss)">
                        <span style="font-size:14px;color:var(--ink-2)">
                            <i class="fa-solid fa-shield mr-1" style="color:var(--loss)"></i>Super Administrateur (accès complet)
                        </span>
                    </label>
                    <p style="font-size:11px;color:var(--dim);margin-top:4px;margin-left:28px">Donne accès au panel d'administration complet</p>
                </div>
            @endif
        </div>

        {{-- ── Infos système (lecture seule) ───────────────────────────────── --}}
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-info-circle mr-2" style="color:var(--dim)"></i>Informations système</p>
            <div class="grid grid-cols-2 gap-4">
                @foreach([['ID', '#' . $user->id], ['Code parrainage', $user->referral_code], ['Inscrit le', $user->created_at->format('d/m/Y H:i')], ['Dernière connexion', $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais']] as [$label, $val])
                    <div>
                        <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">{{ $label }}</p>
                        <p style="color:var(--ink-2);font-size:14px">{{ $val }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div class="flex gap-3">
            <button type="submit" class="btn-primary flex-1">
                <i class="fa-solid fa-save mr-2"></i>Enregistrer les modifications
            </button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
