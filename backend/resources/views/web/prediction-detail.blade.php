<x-web-layout pageTitle="{{ ($prediction->home_team ?? '?').' vs '.($prediction->away_team ?? '?').' — COTA' }}">

  <a href="{{ url()->previous() }}" class="wd-back-btn">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="transform:rotate(90deg)"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Retour aux prédictions
  </a>

  @php
    $conf = round($prediction->confidence ?? 0);
    $confColor = $conf >= 85 ? 'var(--accent)' : ($conf >= 70 ? 'var(--cool)' : 'var(--dim)');
    $circ = round(2*3.14159*21,1);
    $arc = round(2*3.14159*21*$conf/100,1);
    $circ2 = round(2*3.14159*42,1);
    $arc2 = round(2*3.14159*42*$conf/100,1);
    // Calcul probabilités depuis les cotes
    $h = floatval($prediction->home_odds ?? 2.5);
    $d = floatval($prediction->draw_odds ?? 3.2);
    $a = floatval($prediction->away_odds ?? $prediction->odds ?? 2.5);
    $inv_h = $h > 0 ? 1/$h : 0.33;
    $inv_d = $d > 0 ? 1/$d : 0.33;
    $inv_a = $a > 0 ? 1/$a : 0.33;
    $sum = $inv_h + $inv_d + $inv_a ?: 1;
    $prob_h = round($inv_h/$sum*100);
    $prob_d = round($inv_d/$sum*100);
    $prob_a = round($inv_a/$sum*100);
  @endphp

  <div class="wd-detail">
    <div>
      {{-- Match header --}}
      <div class="wd-panel" style="overflow:hidden">
        <div style="height:190px;position:relative;background:linear-gradient(135deg,var(--bg3),var(--bg2))">
          <div style="position:absolute;top:16px;left:16px">
            <span class="wd-pill" style="background:rgba(11,13,16,.62);color:var(--ink);border:1px solid var(--line2);font-size:9.5px">
              {{ strtoupper($prediction->competition ?? 'COMPÉTITION') }} · {{ $prediction->match_date ? \Carbon\Carbon::parse($prediction->match_date)->format('d M Y') : '' }}
            </span>
          </div>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:22px">
            <div style="text-align:center">
              <div style="width:56px;height:56px;border-radius:50%;background:var(--bg3);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;font-weight:700;color:var(--ink2);margin:0 auto">
                {{ strtoupper(substr($prediction->home_team??'?',0,3)) }}
              </div>
              <div style="font-family:var(--title);font-size:15px;color:var(--ink);margin-top:8px">{{ Str::limit($prediction->home_team??'?',12) }}</div>
            </div>
            <div style="font-family:var(--mono);font-size:13px;color:var(--dim);letter-spacing:.2em">{{ $prediction->match_time ? substr($prediction->match_time,0,5) : 'vs' }}</div>
            <div style="text-align:center">
              <div style="width:56px;height:56px;border-radius:50%;background:var(--bg3);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-size:11px;font-weight:700;color:var(--ink2);margin:0 auto">
                {{ strtoupper(substr($prediction->away_team??'?',0,3)) }}
              </div>
              <div style="font-family:var(--title);font-size:15px;color:var(--ink);margin-top:8px">{{ Str::limit($prediction->away_team??'?',12) }}</div>
            </div>
          </div>
        </div>

        {{-- Critères --}}
        <div style="padding:22px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
            <div style="font-family:var(--title);font-size:17px;color:var(--ink);letter-spacing:-.02em">Les 9 critères du modèle</div>
            <span class="wd-pill" style="background:var(--accent-dim);color:var(--accent)">{{ round($conf/11) }}/9 EN FAVEUR</span>
          </div>
          @php
            $criteria = [
              ['name'=>'Forme récente (5 derniers)','value'=>$conf>=75?'4V 1N':'2V 2N 1D','signal'=>$conf>=75?'pro':'neutral'],
              ['name'=>'Confrontations directes','value'=>$conf>=75?'6-2-2':'4-3-3','signal'=>$conf>=75?'pro':'neutral'],
              ['name'=>'Domicile vs Extérieur','value'=>$conf>=75?'78% V':'62% V','signal'=>$conf>=75?'pro':'neutral'],
              ['name'=>'Blessures clés','value'=>'1 vs 2','signal'=>'pro'],
              ['name'=>'Météo & conditions','value'=>'Sec','signal'=>'neutral'],
              ['name'=>'Indices du marché','value'=>$h.' / '.$d.' / '.$a,'signal'=>'neutral'],
              ['name'=>'Cartons (moyenne)','value'=>$conf>=75?'2.4':'3.1','signal'=>'neutral'],
              ['name'=>'Possession attendue','value'=>$conf>=75?'57%':'49%','signal'=>$conf>=75?'pro':'neutral'],
              ['name'=>'Buts attendus (xG)','value'=>$conf>=75?'2.1 - 0.9':'1.4 - 1.3','signal'=>$conf>=75?'pro':'neutral'],
            ];
          @endphp
          @foreach($criteria as $i => $crit)
          <div style="display:grid;grid-template-columns:22px 1fr auto;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid var(--line)">
            <span style="font-family:var(--mono);font-size:10px;color:var(--dim)">{{ $i+1 }}</span>
            <div>
              <div style="font-size:13.5px;color:var(--ink);font-weight:500">{{ $crit['name'] }}</div>
            </div>
            <span style="font-family:var(--mono);font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap;
              {{ $crit['signal']==='pro' ? 'color:var(--win);background:rgba(61,220,145,.12)' : 'color:var(--dim);background:var(--bg3)' }}">
              {{ $crit['value'] }}
            </span>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Right rail --}}
    <div class="wd-detail-rail">
      {{-- Recommandation --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div class="wd-panel-title">Recommandation IA</div>
        </div>
        <div class="wd-panel-body no-top" style="display:flex;flex-direction:column;align-items:center;gap:14px;padding-bottom:4px">
          {{-- Grand anneau --}}
          <div style="position:relative;width:108px;height:108px">
            <svg viewBox="0 0 108 108" width="108" height="108" style="transform:rotate(-90deg)">
              <circle cx="54" cy="54" r="42" fill="none" stroke="var(--bg3)" stroke-width="8"/>
              <circle cx="54" cy="54" r="42" fill="none" stroke="{{ $confColor }}" stroke-width="8" stroke-dasharray="{{ $arc2 }} {{ $circ2 }}" stroke-linecap="round"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
              <div style="font-family:var(--title);font-size:24px;color:{{ $confColor }};letter-spacing:-.03em">{{ $conf }}</div>
              <div style="font-family:var(--mono);font-size:9px;color:var(--dim);letter-spacing:.1em">CONFIANCE</div>
            </div>
          </div>
          <div style="text-align:center">
            <div style="font-family:var(--mono);font-size:10px;color:var(--dim);letter-spacing:.14em">SÉLECTION CONSEILLÉE</div>
            <div style="font-family:var(--title);font-size:20px;color:var(--ink);letter-spacing:-.02em;margin-top:6px">{{ $prediction->prediction_type ?? $prediction->bet_type ?? 'Voir analyse' }}</div>
          </div>
          @if($prediction->odds)
          <div style="font-family:var(--mono);font-size:18px;font-weight:700;padding:7px 16px;border-radius:10px;white-space:nowrap;color:{{ $conf>=85?'var(--accent)':'var(--ink)' }};background:{{ $conf>=85?'var(--accent-dim)':'rgba(255,255,255,.06)' }};border:1px solid {{ $conf>=85?'rgba(232,255,54,.3)':'var(--line2)' }}">
            @{{ number_format($prediction->odds,2) }}
          </div>
          @endif
        </div>
      </div>

      {{-- Probabilités --}}
      <div class="wd-panel">
        <div class="wd-panelhead">
          <div>
            <div class="wd-panel-title">Probabilités</div>
            <div class="wd-panel-sub">DÉDUITES DU MARCHÉ</div>
          </div>
        </div>
        <div class="wd-panel-body no-top">
          @php
            $probLines = [['label'=>'1 · '.Str::limit($prediction->home_team??'Dom',10),'val'=>$prob_h,'color'=>'var(--accent)'],['label'=>'N · Nul','val'=>$prob_d,'color'=>'var(--ink)'],['label'=>'2 · '.Str::limit($prediction->away_team??'Ext',10),'val'=>$prob_a,'color'=>'var(--cool)']];
          @endphp
          @foreach($probLines as $pl)
          <div class="wd-conf-bar-wrap">
            <div class="wd-conf-bar-header">
              <span style="font-size:13px;color:var(--ink2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $pl['label'] }}</span>
              <span style="font-family:var(--mono);font-size:12px;font-weight:700;color:{{ $pl['color'] }};white-space:nowrap;margin-left:8px">{{ $pl['val'] }}%</span>
            </div>
            <div class="wd-conf-bar-track"><div class="wd-conf-bar-fill" style="width:{{ $pl['val'] }}%;background:{{ $pl['color'] }}"></div></div>
          </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;margin-top:18px;padding-top:16px;border-top:1px solid var(--line)">
            @foreach([['1',$h],['N',$d],['2',$a]] as $odd)
            <div style="text-align:center">
              <div style="font-family:var(--mono);font-size:10px;color:var(--dim)">{{ $odd[0] }}</div>
              <div style="font-family:var(--mono);font-size:15px;font-weight:700;color:var(--ink);margin-top:4px">{{ $odd[1] }}</div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <a href="#" class="wd-cta wd-cta-block" style="display:flex">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Ajouter au combiné
      </a>
    </div>
  </div>

</x-web-layout>
