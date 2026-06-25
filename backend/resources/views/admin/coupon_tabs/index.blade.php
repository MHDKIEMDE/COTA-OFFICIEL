@extends('admin.layouts.app')
@section('title', 'Onglets Coupon')
@section('page-title', 'Onglets Coupon')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">{{ session('success') }}</div>
    @endif

    <div>
        <p style="color:var(--dim);font-size:14px">
            Personnalisez les onglets affichés sur l'écran Coupon de l'app.
            Le libellé, le sous-titre et l'activation sont pris en compte dynamiquement.
            L'onglet « Vedette » affiche automatiquement le nom de la compétition en cours
            (Mondial, Ligue des Champions, CAN, Copa…) et disparaît s'il n'y en a aucune.
        </p>
    </div>

    <div class="space-y-4">
        @foreach($tabs as $tab)
        <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
            <form action="{{ route('admin.coupon-tabs.update', $tab) }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                @method('PUT')

                <div style="min-width:90px">
                    <label style="display:block;color:var(--dim);font-size:11px;margin-bottom:4px">Clé</label>
                    <span style="font-family:monospace;font-size:13px;color:var(--accent)">{{ $tab->key }}</span>
                </div>

                <div style="flex:1;min-width:140px">
                    <label style="display:block;color:var(--dim);font-size:11px;margin-bottom:4px">Libellé</label>
                    <input type="text" name="label" value="{{ $tab->label }}" maxlength="40" required
                        style="width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div style="flex:1;min-width:120px">
                    <label style="display:block;color:var(--dim);font-size:11px;margin-bottom:4px">Sous-titre</label>
                    <input type="text" name="subtitle" value="{{ $tab->subtitle }}" maxlength="40"
                        style="width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div style="width:80px">
                    <label style="display:block;color:var(--dim);font-size:11px;margin-bottom:4px">Ordre</label>
                    <input type="number" name="sort_order" value="{{ $tab->sort_order }}" min="0" max="99" required
                        style="width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px">
                </div>

                <div style="display:flex;align-items:center;gap:6px;padding-bottom:9px">
                    <input type="checkbox" name="is_active" value="1" id="active-{{ $tab->id }}" {{ $tab->is_active ? 'checked' : '' }}>
                    <label for="active-{{ $tab->id }}" style="color:var(--dim);font-size:13px">Actif</label>
                </div>

                <button type="submit"
                    style="padding:9px 16px;background:var(--accent);border:none;border-radius:8px;color:var(--bg);font-size:13px;font-weight:700;cursor:pointer">
                    Enregistrer
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
