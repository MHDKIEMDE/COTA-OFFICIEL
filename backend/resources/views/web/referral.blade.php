<x-web-layout pageTitle="Parrainage — COTA">

  <div class="wd-topbar">
    <div>
      <div class="wd-date">GAGNEZ ENSEMBLE</div>
      <h1 class="wd-h1">Parrainage</h1>
      <p class="wd-desc">Invitez vos amis et gagnez des jours Premium pour chaque parrainage converti.</p>
    </div>
  </div>

  <div class="wd-cols">
    <div class="wd-maincol">

      {{-- Hero code --}}
      @auth
      <div class="wd-panel wd-refhero">
        <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.16em">VOTRE CODE DE PARRAINAGE</div>
        <div class="wd-refcode">
          <span style="font-family:var(--title);font-size:26px;color:var(--accent);letter-spacing:.02em" id="refCode">{{ auth()->user()->referral_code ?? 'COTA-'.strtoupper(substr(auth()->user()->name??'USER',0,4)) }}</span>
          <button class="wd-cta" onclick="copyCode()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span id="copyLabel">Copier</span>
          </button>
        </div>
        <div class="wd-ref-kpis">
          @php
            $refStats = [['val'=>$stats['total_referrals'],'label'=>'INVITÉS','col'=>'var(--ink)'],['val'=>$stats['premium_days_earned'].'j','label'=>'JOURS GAGNÉS','col'=>'var(--accent)'],['val'=>$stats['pending_rewards'],'label'=>'EN ATTENTE','col'=>'var(--cool)'],['val'=>$stats['total_referrals'],'label'=>'CONVERTIS','col'=>'var(--ink)']];
          @endphp
          @foreach($refStats as $rs)
          <div class="wd-ref-kpi">
            <div style="font-family:var(--title);font-size:22px;color:{{ $rs['col'] }};letter-spacing:-.02em">{{ $rs['val'] }}</div>
            <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.1em;margin-top:5px">{{ $rs['label'] }}</div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Liste filleuls --}}
      @if($referrals->isNotEmpty())
      <div style="height:22px"></div>
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Vos filleuls</div>
            <div class="wd-panel-sub">{{ $referrals->count() }} PERSONNES</div>
          </div>
        </div>
        <div class="wd-panel-body no-top">
          @foreach($referrals as $ref)
          @php $initial = strtoupper(substr($ref['name']??'U',0,2)); $isConverted = ($ref['status']??'') === 'completed'; @endphp
          <div style="display:flex;align-items:center;gap:13px;padding:13px 0;{{ !$loop->first ? 'border-top:1px solid var(--line)' : '' }}">
            <div style="width:38px;height:38px;border-radius:50%;background:{{ $isConverted ? 'var(--accent)' : 'var(--cool-dim)' }};color:{{ $isConverted ? 'var(--bg)' : 'var(--cool)' }};display:flex;align-items:center;justify-content:center;font-family:var(--title);font-size:14px;flex-shrink:0">{{ $initial }}</div>
            <div style="flex:1">
              <div style="font-family:var(--title);font-size:14px;color:var(--ink)">{{ $ref['name'] }}</div>
              <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.06em;margin-top:2px">
                {{ $isConverted ? 'ABONNÉ' : 'INSCRIT' }} · {{ \Carbon\Carbon::parse($ref['created_at'])->format('d M Y') }}
              </div>
            </div>
            <span style="font-family:var(--mono);font-size:13px;font-weight:700;color:{{ $isConverted ? 'var(--win)' : 'var(--dim)' }};white-space:nowrap">
              {{ $isConverted ? '+7 jours' : 'En attente' }}
            </span>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      @else
      {{-- Guest message --}}
      <div class="wd-panel" style="padding:32px;text-align:center">
        <div style="font-family:var(--title);font-size:18px;color:var(--ink);margin-bottom:10px">Connectez-vous pour parrainer</div>
        <div style="font-size:13.5px;color:var(--dim);margin-bottom:20px">Créez un compte pour obtenir votre code de parrainage et gagner des jours Premium.</div>
        <a href="{{ route('register') }}" class="wd-cta wd-cta-block" style="display:flex">Créer un compte</a>
      </div>
      @endauth

    </div>
    <div class="wd-rail">

      {{-- Comment ça marche --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Comment ça marche</div>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $steps = [
              ['n'=>'1','title'=>'Partagez votre code','desc'=>'Envoyez votre code à vos amis ou partagez le lien.'],
              ['n'=>'2','title'=>'Ils s\'inscrivent','desc'=>'Votre filleul crée son compte avec votre code.'],
              ['n'=>'3','title'=>'Vous gagnez tous les deux','desc'=>'+7 jours Premium crédités sur chaque compte.'],
            ];
          @endphp
          @foreach($steps as $step)
          <div class="wd-step">
            <div class="wd-step-num">{{ $step['n'] }}</div>
            <div>
              <div style="font-size:13.5px;color:var(--ink);font-weight:600">{{ $step['title'] }}</div>
              <div style="font-size:12px;color:var(--dim);margin-top:2px;line-height:1.45">{{ $step['desc'] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Récompenses --}}
      <div class="wd-panel" style="padding:22px">
        <div style="font-family:var(--mono);font-size:9.5px;color:var(--dim);letter-spacing:.12em;margin-bottom:14px">RÉCOMPENSES</div>
        <div style="display:flex;flex-direction:column;gap:12px">
          @foreach([['label'=>'1 filleul converti','val'=>'+7 jours'],['label'=>'3 filleuls convertis','val'=>'+30 jours'],['label'=>'5 filleuls convertis','val'=>'+2 mois'],] as $reward)
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:var(--bg3);border-radius:10px;border:1px solid var(--line)">
            <span style="font-size:13px;color:var(--ink2)">{{ $reward['label'] }}</span>
            <span style="font-family:var(--title);font-size:15px;color:var(--accent)">{{ $reward['val'] }}</span>
          </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>

  @auth
  <script>
  function copyCode() {
    var code = document.getElementById('refCode').textContent.trim();
    navigator.clipboard.writeText(code).then(function() {
      var btn = document.getElementById('copyLabel');
      btn.textContent = 'Copié !';
      setTimeout(function() { btn.textContent = 'Copier'; }, 2000);
    });
  }
  </script>
  @endauth

</x-web-layout>
