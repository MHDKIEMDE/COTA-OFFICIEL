<x-web-layout pageTitle="Profil — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">MON COMPTE</div>
      <h1 class="wd-h1">Profil</h1>
    </div>
    <div class="wd-topactions">
      <button class="wd-ghost-btn accent">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Enregistrer
      </button>
    </div>
  </div>

  <div class="wd-cols">
    <div class="wd-maincol">
      {{-- Informations --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Informations</div>
        </div>
        <div class="wd-panel-body no-top">
          @auth
          @php $user = auth()->user(); @endphp
          <div class="wd-field"><span class="wd-field-label">NOM</span><span class="wd-field-val">{{ $user->name ?? '—' }}</span></div>
          <div class="wd-field"><span class="wd-field-label">E-MAIL</span><span class="wd-field-val">{{ $user->email ?? '—' }}</span></div>
          <div class="wd-field"><span class="wd-field-label">TÉLÉPHONE</span><span class="wd-field-val">{{ $user->phone ?? '—' }}</span></div>
          <div class="wd-field"><span class="wd-field-label">VILLE</span><span class="wd-field-val">{{ $user->city ?? 'Non renseigné' }}</span></div>
          <div class="wd-field"><span class="wd-field-label">MEMBRE DEPUIS</span><span class="wd-field-val">{{ $user->created_at ? $user->created_at->locale('fr')->isoFormat('MMMM YYYY') : '—' }}</span></div>
          <div class="wd-field"><span class="wd-field-label">STATUT</span><span class="wd-field-val" style="color:{{ $user->is_premium ? 'var(--accent)' : 'var(--ink2)' }}">{{ $user->is_premium ? 'Abonné Premium' : 'Découverte' }}</span></div>
          @endauth
        </div>
      </div>

      {{-- Préférences --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Préférences</div>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $prefs = [
              ['label'=>'Notifications push','on'=>true,'key'=>'notif_push'],
              ['label'=>'Résumé quotidien par e-mail','on'=>true,'key'=>'notif_email'],
              ['label'=>'Alertes matchs live','on'=>false,'key'=>'notif_live'],
              ['label'=>'Jeu responsable — limite de mise','on'=>true,'key'=>'responsible_gaming'],
            ];
          @endphp
          @foreach($prefs as $pref)
          <div class="wd-pref-row">
            <span style="font-size:13.5px;color:var(--ink)">{{ $pref['label'] }}</span>
            <div class="wd-toggle {{ $pref['on'] ? 'on' : '' }}"><span class="wd-toggle-knob"></span></div>
          </div>
          @endforeach
        </div>
      </div>

    </div>
    <div class="wd-rail">

      {{-- Card profil --}}
      @auth
      @php $user = auth()->user(); $initials = strtoupper(substr($user->name??'U',0,2)); @endphp
      <div class="wd-panel" style="padding:22px">
        <div style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;padding:6px 0">
          <div style="width:84px;height:84px;border-radius:50%;background:var(--accent);color:var(--bg);display:flex;align-items:center;justify-content:center;font-family:var(--title);font-size:30px;letter-spacing:-.02em">{{ $initials }}</div>
          <div>
            <div style="font-family:var(--title);font-size:18px;color:var(--ink)">{{ $user->name }}</div>
            <div style="font-size:12.5px;color:var(--dim);margin-top:3px">{{ $user->email }}</div>
          </div>
          <span class="wd-pill" style="background:var(--accent-dim);color:var(--accent)">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ $user->is_premium ? 'ABONNÉ PRO' : 'DÉCOUVERTE' }}
          </span>
        </div>
      </div>
      @endauth

      {{-- Compétitions suivies --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Compétitions suivies</div>
        </div>
        <div class="wd-panel-body no-top">
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            @php $comps = ['Ligue 1','Champions League','Premier League']; @endphp
            @foreach($comps as $comp)
            <span class="wd-pill" style="background:var(--bg3);color:var(--ink2);border:1px solid var(--line2)">{{ $comp }}</span>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Gérer abonnement --}}
      <a href="{{ route('subscription') }}" class="wd-plan-btn" style="display:flex;text-align:center;cursor:pointer;text-decoration:none">
        Gérer mon abonnement
      </a>

    </div>
  </div>

</x-web-layout>
