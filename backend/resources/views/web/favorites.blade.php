<x-web-layout pageTitle="Favoris — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">VOTRE SÉLECTION</div>
      <h1 class="wd-h1">Favoris</h1>
      <p class="wd-desc">Vos équipes suivies et les prédictions que vous avez mises de côté.</p>
    </div>
  </div>

  @guest
  <div style="text-align:center;padding:64px 32px;color:var(--dim)">
    <div style="font-family:var(--title);font-size:20px;margin-bottom:10px;color:var(--ink)">Fonctionnalité membre</div>
    <div style="font-size:13.5px;margin-bottom:24px">Connectez-vous pour sauvegarder vos équipes et prédictions favorites.</div>
    <a href="{{ route('login') }}" class="wd-cta" style="display:inline-flex">Se connecter</a>
  </div>
  @else
  {{-- Équipes suivies --}}
  <div class="wd-panel">
    <div class="wd-panelhead">
      <div>
        <div class="wd-panel-title">Équipes suivies</div>
        <div class="wd-panel-sub">VOTRE SÉLECTION</div>
      </div>
    </div>
    <div class="wd-panel-body no-top">
      <div class="wd-teamrow">
        @php $favTeams = [['name'=>'PSG','color'=>'#004170'],['name'=>'Real Madrid','color'=>'#febe10'],['name'=>'Liverpool','color'=>'#c8102e'],['name'=>'Monaco','color'=>'#e2001a']]; @endphp
        @foreach($favTeams as $team)
        <div class="wd-teamchip">
          <div style="width:40px;height:40px;border-radius:50%;background:{{ $team['color'] }};display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:10px;font-weight:700;color:white">{{ strtoupper(substr($team['name'],0,3)) }}</div>
          <span style="font-family:var(--title);font-size:13px;color:var(--ink)">{{ $team['name'] }}</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:var(--loss)"><path d="M12 20s-7-4.6-9.2-9C1.3 8 3 4.5 6.4 4.5c2 0 3.2 1.2 3.6 2 .4-.8 1.6-2 3.6-2 3.4 0 5.1 3.5 3.6 6.5C19 15.4 12 20 12 20Z" fill="currentColor" stroke="none"/></svg>
        </div>
        @endforeach
        <div class="wd-teamchip" style="border-style:dashed;cursor:pointer;color:var(--dim)">
          <span style="font-family:var(--title);font-size:22px">+</span>
          <span style="font-size:11px">Ajouter</span>
        </div>
      </div>
    </div>
  </div>

  <div style="height:22px"></div>

  {{-- Prédictions sauvegardées --}}
  <div class="wd-panel">
    <div class="wd-panelhead">
      <div>
        <div class="wd-panel-title">Prédictions sauvegardées</div>
        <div class="wd-panel-sub">EN ATTENTE</div>
      </div>
    </div>
    <div class="wd-panel-body no-top">
      <div style="text-align:center;padding:24px;color:var(--dim);font-size:13.5px">
        Aucune prédiction sauvegardée. Parcourez les <a href="{{ route('predictions.index') }}" style="color:var(--accent)">prédictions du jour</a> et sauvegardez vos favoris.
      </div>
    </div>
  </div>
  @endguest

</x-web-layout>
