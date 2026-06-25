// COTA V2 — Section C: Web responsive (mobile chrome, tablet, desktop landing).

const { BG: wBG, BG2: wBG2, BG3: wBG3, LINE: wLINE, LINE2: wLINE2, INK: wINK, INK2: wINK2, DIM: wDIM, DIM2: wDIM2, ACCENT: wACCENT, WIN: wWIN, font: wFONT } = window.COTA;

// ── C1 · Web mobile chrome — same as app + URL bar + download banner ─────────
function WebMobileHome() {
  return (
    <div style={{ height: '100%', background: wBG, color: wINK, fontFamily: wFONT.ui, position: 'relative' }}>
      <URLBar url="cota.app" />
      {/* Same content as ScreenHome but shifted down to leave room for URL bar */}
      <div style={{ paddingTop: 32, height: '100%' }}>
        <ScreenHome />
      </div>
      {/* Floating download banner above bottom nav */}
      <div style={{ position: 'absolute', left: 12, right: 12, bottom: 96, zIndex: 50, borderRadius: 12, overflow: 'hidden', boxShadow: '0 12px 30px rgba(0,0,0,0.5)' }}>
        <DownloadBanner variant={3} />
      </div>
    </div>
  );
}

// ── C2 · Web tablet — 2-column layout ────────────────────────────────────────
function WebTablet() {
  const M = window.MATCHES;
  const C = window.COUPON;
  return (
    <div style={{ width: '100%', height: '100%', background: wBG, color: wINK, fontFamily: wFONT.ui, position: 'relative', overflow: 'hidden' }}>
      {/* Header tablet */}
      <div style={{ padding: '18px 32px', borderBottom: `1px solid ${wLINE}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <AppIcon size={32} />
          <Wordmark size={22} underline={false} />
        </div>
        <div style={{ display: 'flex', gap: 16, alignItems: 'center', fontFamily: wFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>
          <span style={{ color: wACCENT, borderBottom: `2px solid ${wACCENT}`, paddingBottom: 4 }}>AUJOURD'HUI</span>
          <span style={{ color: wDIM }}>COUPON</span>
          <span style={{ color: wDIM }}>HISTORIQUE</span>
          <span style={{ color: wDIM }}>PROFIL</span>
          <button style={{ background: wACCENT, color: wBG, border: 'none', padding: '8px 14px', borderRadius: 8, fontFamily: wFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>TÉLÉCHARGER</button>
        </div>
      </div>

      {/* 2 columns */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr', height: 'calc(100% - 64px)', overflow: 'hidden' }}>
        {/* LEFT - matches */}
        <div style={{ overflowY: 'auto', padding: '20px 24px', borderRight: `1px solid ${wLINE}` }}>
          <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em', marginBottom: 10 }}>MATCHS DU JOUR · 18 MAI</div>
          <div style={{ fontFamily: wFONT.title, fontSize: 24, letterSpacing: '-0.03em', marginBottom: 16 }}>Aujourd'hui</div>

          {/* Live row */}
          <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wACCENT, letterSpacing: '0.18em', marginBottom: 8 }}>● EN LIVE</div>
          <div style={{ marginBottom: 18 }}>
            <MatchHeroCard match={M[2]} height={140} live={true} score="1–0" minute="34" />
          </div>

          <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em', marginBottom: 8 }}>LIGUE 1 · J34</div>
          {[M[0], M[3], M[4]].map(m => (
            <div key={m.id} style={{ marginBottom: 8 }}>
              <FlashRow match={m} />
            </div>
          ))}

          <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em', margin: '16px 0 8px' }}>CHAMPIONS LEAGUE</div>
          <FlashRow match={M[1]} />
        </div>

        {/* RIGHT - sticky coupon + perf */}
        <div style={{ overflowY: 'auto', padding: '20px 24px', background: wBG }}>
          <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wACCENT, letterSpacing: '0.18em', marginBottom: 8 }}>★ COUPON DU JOUR</div>
          <div style={{ background: wBG2, border: `1px solid ${wLINE}`, borderRadius: 14, padding: 18, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: -40, right: -40, width: 200, height: 200, background: 'radial-gradient(circle, rgba(232,255,54,0.10), transparent 70%)' }} />
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative' }}>
              <div>
                <div style={{ fontFamily: wFONT.title, fontSize: 36, color: wACCENT, letterSpacing: '-0.04em', lineHeight: 1 }}>@{C.total.toFixed(2)}</div>
                <div style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM, letterSpacing: '0.12em', marginTop: 4 }}>COTE COMBINÉE</div>
              </div>
              <ConfidenceRing value={C.confidence} size={60} stroke={5} />
            </div>
            <div style={{ marginTop: 14 }}>
              {C.sélections.map(p => (
                <div key={p.matchId} style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderTop: `1px solid ${wLINE2}`, fontSize: 11, color: wINK }}>
                  <span style={{ fontFamily: wFONT.mono, color: wDIM, letterSpacing: '0.05em' }}>{p.label}</span>
                  <span style={{ fontFamily: wFONT.mono, color: wACCENT, fontWeight: 700 }}>@{p.odds}</span>
                </div>
              ))}
            </div>
            <button style={{ marginTop: 12, width: '100%', height: 38, background: wACCENT, color: wBG, border: 'none', borderRadius: 8, fontFamily: wFONT.title, fontSize: 11, letterSpacing: '0.06em' }}>VOIR L'ANALYSE →</button>
          </div>

          {/* Performance widget */}
          <div style={{ marginTop: 18 }}>
            <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em', marginBottom: 8 }}>TES PERFORMANCES</div>
            <div style={{ background: wBG2, border: `1px solid ${wLINE}`, borderRadius: 12, padding: 14 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
                <div>
                  <div style={{ fontFamily: wFONT.title, fontSize: 22, color: wACCENT, letterSpacing: '-0.03em' }}>+18.5%</div>
                  <div style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM, letterSpacing: '0.12em' }}>ROI SAISON</div>
                </div>
                <div style={{ fontFamily: wFONT.mono, fontSize: 11, color: wWIN }}>+€184</div>
              </div>
              <Sparkline data={[12, 18, 22, 19, 28, 32, 30, 38, 42, 48, 55]} width={260} height={50} color={wACCENT} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Compact match row (Flashscore-style) ─────────────────────────────────────
function FlashRow({ match, dark }) {
  const m = window.TEAMS;
  const h = m[match.home];
  const a = m[match.away];
  const high = match.confidence >= 80;
  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'auto 1fr auto 1fr auto auto auto', alignItems: 'center', gap: 10, padding: '10px 12px', background: wBG2, border: `1px solid ${wLINE}`, borderRadius: 8, fontFamily: wFONT.ui }}>
      <span style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.05em' }}>{match.kickoff}</span>
      <div style={{ display: 'flex', alignItems: 'center', gap: 8, justifyContent: 'flex-end' }}>
        <span style={{ fontSize: 12, color: wINK, fontWeight: 600 }}>{h.short}</span>
        <TeamBadge code={h.code} color={h.color} text={h.text} size={20} />
      </div>
      <span style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM2, letterSpacing: '0.1em' }}>VS</span>
      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
        <TeamBadge code={a.code} color={a.color} text={a.text} size={20} />
        <span style={{ fontSize: 12, color: wINK, fontWeight: 600 }}>{a.short}</span>
      </div>
      <span style={{ fontFamily: wFONT.mono, fontSize: 10, color: high ? wACCENT : wINK, fontWeight: 700 }}>@{match.pick.odds}</span>
      <span style={{ fontFamily: wFONT.mono, fontSize: 9, color: wACCENT, letterSpacing: '0.08em' }}>{'★'.repeat(Math.min(4, Math.floor(match.confidence / 20)))}</span>
      <button style={{ width: 24, height: 24, borderRadius: 12, background: wBG3, color: wINK, border: 'none', fontFamily: wFONT.title, fontSize: 12 }}>+</button>
    </div>
  );
}

// ── C3 · Web desktop landing — DAZN style ────────────────────────────────────
function WebDesktopLanding() {
  const M = window.MATCHES;
  const psg = window.TEAMS.PSG;
  const om  = window.TEAMS.OM;

  return (
    <div style={{ width: '100%', background: wBG, color: wINK, fontFamily: wFONT.ui, position: 'relative', overflow: 'hidden' }}>
      {/* Nav sticky */}
      <header style={{ height: 64, padding: '0 40px', borderBottom: `1px solid ${wLINE}`, background: 'rgba(11,13,16,0.95)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative', zIndex: 10 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <AppIcon size={28} />
          <Wordmark size={20} underline={false} />
        </div>
        <nav style={{ display: 'flex', gap: 28, fontFamily: wFONT.mono, fontSize: 11, letterSpacing: '0.12em' }}>
          <a style={{ color: wACCENT, borderBottom: `2px solid ${wACCENT}`, paddingBottom: 4 }}>AUJOURD'HUI</a>
          <a style={{ color: wDIM }}>MÉTHODE</a>
          <a style={{ color: wDIM }}>STATS</a>
          <a style={{ color: wDIM }}>PRICING</a>
        </nav>
        <div style={{ display: 'flex', gap: 10 }}>
          <button style={{ background: 'transparent', color: wINK, border: `1px solid ${wLINE2}`, padding: '8px 14px', borderRadius: 8, fontFamily: wFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>SE CONNECTER</button>
          <button style={{ background: wACCENT, color: wBG, border: 'none', padding: '8px 14px', borderRadius: 8, fontFamily: wFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>TÉLÉCHARGER →</button>
        </div>
      </header>

      {/* Hero full bleed */}
      <section style={{ position: 'relative', height: 600, overflow: 'hidden' }}>
        <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(108deg, ${psg.color} 0%, ${psg.color} 45%, ${om.color} 55%, ${om.color} 100%)` }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, rgba(0,0,0,0.05) 0%, rgba(11,13,16,0.65) 50%, #0b0d10 92%)' }} />
        {/* Monograms */}
        <div style={{ position: 'absolute', top: 60, left: -40, fontFamily: wFONT.title, fontSize: 380, lineHeight: 0.85, color: wINK, opacity: 0.10, letterSpacing: '-0.06em' }}>PSG</div>
        <div style={{ position: 'absolute', top: 60, right: -40, fontFamily: wFONT.title, fontSize: 380, lineHeight: 0.85, color: wINK, opacity: 0.10, letterSpacing: '-0.06em', textAlign: 'right' }}>OM</div>
        {/* Ticker */}
        <div style={{ position: 'absolute', top: 50, left: 0, right: 0, height: 30, background: 'rgba(11,13,16,0.5)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', borderTop: `1px solid ${wLINE2}`, borderBottom: `1px solid ${wLINE2}`, display: 'flex', alignItems: 'center', overflow: 'hidden' }}>
          <div style={{ whiteSpace: 'nowrap', fontFamily: wFONT.mono, fontSize: 11, color: wDIM, letterSpacing: '0.15em' }}>
            PSG-OM @1.65 · LIV-ARS +2.5 @1.78 · RMA-BAY BTTS @1.55 · COUPON DU JOUR @4.55 · 87% CONFIANCE · ASM-OL +1.5 @1.42 · MAN CITY @1.85
          </div>
        </div>

        {/* Hero content */}
        <div style={{ position: 'absolute', bottom: 60, left: 56, right: 56, display: 'grid', gridTemplateColumns: '1.3fr 1fr', gap: 56, alignItems: 'flex-end' }}>
          <div>
            <Pill bg="rgba(232,255,54,0.10)" color={wACCENT} border={wACCENT}>
              <span style={{ width: 6, height: 6, background: wACCENT, borderRadius: 3, animation: 'cota-live-pulse 1.4s ease-in-out infinite' }} />
              247 MATCHS ANALYSÉS CE WEEK-END
            </Pill>
            <h1 style={{ fontFamily: wFONT.title, fontSize: 92, lineHeight: 0.92, letterSpacing: '-0.05em', margin: '22px 0 0' }}>
              Le foot,<br/>
              lu par<br/>
              une <span style={{ background: wACCENT, color: wBG, padding: '0 14px' }}>IA</span>.
            </h1>
            <p style={{ fontSize: 17, color: wINK2, marginTop: 18, lineHeight: 1.5, maxWidth: 540 }}>
              9 critères, 1 score de confiance, 1 coupon par jour. Pas de baratin.
            </p>
            <div style={{ display: 'flex', gap: 12, marginTop: 22 }}>
              <button style={{ background: wACCENT, color: wBG, border: 'none', borderRadius: 10, padding: '14px 22px', fontFamily: wFONT.title, fontSize: 13, letterSpacing: '0.05em' }}>↓ APP STORE</button>
              <button style={{ background: 'transparent', color: wINK, border: `1px solid ${wLINE2}`, borderRadius: 10, padding: '14px 22px', fontFamily: wFONT.title, fontSize: 13, letterSpacing: '0.05em' }}>↓ GOOGLE PLAY</button>
            </div>
            <div style={{ display: 'flex', gap: 40, marginTop: 32 }}>
              {[['+18.5%', 'ROI MOYEN'], ['72%', 'TAUX RÉUSSITE'], ['47k', 'UTILISATEURS']].map(([n, l]) => (
                <div key={l}>
                  <div style={{ fontFamily: wFONT.title, fontSize: 28, color: wACCENT, letterSpacing: '-0.03em' }}>{n}</div>
                  <div style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM, letterSpacing: '0.15em', marginTop: 4 }}>{l}</div>
                </div>
              ))}
            </div>
          </div>

          {/* Floating coupon card */}
          <div style={{ background: 'rgba(21,24,29,0.85)', backdropFilter: 'blur(20px)', WebkitBackdropFilter: 'blur(20px)', border: `1px solid ${wLINE2}`, borderRadius: 14, padding: 22, boxShadow: '0 20px 60px rgba(0,0,0,0.6)' }}>
            <div style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM, letterSpacing: '0.18em' }}>COUPON DU JOUR · 09:30</div>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 10 }}>
              <div style={{ fontFamily: wFONT.title, fontSize: 44, color: wACCENT, letterSpacing: '-0.04em', lineHeight: 1 }}>@4.55</div>
              <ConfidenceRing value={87} size={52} stroke={4} />
            </div>
            <div style={{ marginTop: 12, fontFamily: wFONT.mono, fontSize: 11, color: wINK2, letterSpacing: '0.04em' }}>3 picks · CONFIANCE 87%</div>
          </div>
        </div>
      </section>

      {/* Content Row 1 — EN LIVE */}
      <section style={{ padding: '56px 56px 0' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 20 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontFamily: wFONT.title, fontSize: 22, letterSpacing: '-0.02em' }}>EN LIVE</span>
            <span style={{ padding: '3px 8px', background: wACCENT, color: wBG, borderRadius: 4, fontFamily: wFONT.mono, fontSize: 9, fontWeight: 700, letterSpacing: '0.12em' }}>● 3</span>
          </div>
          <span style={{ fontFamily: wFONT.mono, fontSize: 10, color: wACCENT, letterSpacing: '0.12em' }}>VOIR TOUT →</span>
        </div>
        <div style={{ display: 'flex', gap: 14, overflowX: 'auto' }}>
          <div style={{ width: 340, flexShrink: 0 }}><MatchHeroCard match={M[2]} height={180} live={true} score="1–0" minute="34" /></div>
          <div style={{ width: 340, flexShrink: 0 }}><MatchHeroCard match={M[1]} height={180} live={true} score="2–2" minute="78" /></div>
          <div style={{ width: 340, flexShrink: 0 }}><MatchHeroCard match={M[3]} height={180} live={true} score="0–0" minute="12" /></div>
        </div>
      </section>

      {/* Content Row 2 — MATCHS DU JOUR */}
      <section style={{ padding: '56px 56px 0' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 16 }}>
          <span style={{ fontFamily: wFONT.title, fontSize: 22, letterSpacing: '-0.02em' }}>MATCHS DU JOUR</span>
          <div style={{ display: 'flex', gap: 8 }}>
            {['TOUS', 'LIGUE 1', 'UCL', 'PREMIER LEAGUE'].map((t, i) => (
              <span key={t} style={{ padding: '5px 10px', borderRadius: 999, background: i === 0 ? wACCENT : wBG2, color: i === 0 ? wBG : wDIM, fontFamily: wFONT.mono, fontSize: 9, letterSpacing: '0.1em', fontWeight: 700, border: i === 0 ? 'none' : `1px solid ${wLINE2}` }}>{t}</span>
            ))}
          </div>
        </div>

        {/* Group: Ligue 1 */}
        <div style={{ marginBottom: 18 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 10, paddingBottom: 8, borderBottom: `1px solid ${wLINE}` }}>
            <div style={{ width: 4, height: 16, background: '#003366' }} />
            <span style={{ fontFamily: wFONT.mono, fontSize: 11, color: wINK, letterSpacing: '0.12em', fontWeight: 700 }}>LIGUE 1 · JOURNÉE 34</span>
            <span style={{ fontFamily: wFONT.mono, fontSize: 9, color: wDIM, letterSpacing: '0.1em' }}>· 3 MATCHS</span>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
            {[M[0], M[3], M[4]].map(m => <FlashRow key={m.id} match={m} />)}
          </div>
        </div>

        {/* Group: UCL */}
        <div style={{ marginBottom: 18 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 10, paddingBottom: 8, borderBottom: `1px solid ${wLINE}` }}>
            <div style={{ width: 4, height: 16, background: '#001f3f' }} />
            <span style={{ fontFamily: wFONT.mono, fontSize: 11, color: wINK, letterSpacing: '0.12em', fontWeight: 700 }}>CHAMPIONS LEAGUE · DEMI-FINALE</span>
          </div>
          <FlashRow match={M[2]} />
        </div>
      </section>

      {/* Coupon IA full-width feature */}
      <section style={{ padding: '56px 56px 0' }}>
        <div style={{ background: wBG2, border: `1px solid ${wLINE}`, borderRadius: 16, padding: 36, display: 'grid', gridTemplateColumns: '1fr 1.4fr', gap: 32, position: 'relative', overflow: 'hidden' }}>
          <div style={{ position: 'absolute', top: -60, right: -60, width: 280, height: 280, background: 'radial-gradient(circle, rgba(232,255,54,0.14), transparent 70%)' }} />
          <div style={{ position: 'relative' }}>
            <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wACCENT, letterSpacing: '0.18em' }}>★ COUPON DU JOUR · 09:30</div>
            <div style={{ fontFamily: wFONT.title, fontSize: 72, color: wACCENT, letterSpacing: '-0.05em', lineHeight: 0.9, marginTop: 8 }}>@4.55</div>
            <div style={{ fontFamily: wFONT.mono, fontSize: 11, color: wDIM, letterSpacing: '0.12em', marginTop: 6 }}>COTE COMBINÉE</div>
            <div style={{ marginTop: 18 }}>
              <ConfidenceRing value={87} size={88} stroke={6} />
            </div>
          </div>
          <div>
            <div style={{ fontFamily: wFONT.title, fontSize: 22, letterSpacing: '-0.02em', marginBottom: 14 }}>3 picks IA combinés</div>
            {[
              ['PSG-OM', 'Victoire PSG', '1.65'],
              ['LIV-ARS', '+2.5 buts',  '1.78'],
              ['RMA-BAY', 'BTTS Oui',   '1.55'],
            ].map(([m, t, o]) => (
              <div key={m} style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '10px 0', borderTop: `1px solid ${wLINE}` }}>
                <span style={{ fontFamily: wFONT.mono, fontSize: 11, color: wDIM, letterSpacing: '0.08em', width: 80 }}>{m}</span>
                <span style={{ flex: 1, fontSize: 13, color: wINK }}>{t}</span>
                <span style={{ fontFamily: wFONT.mono, fontSize: 14, color: wACCENT, fontWeight: 700 }}>@{o}</span>
              </div>
            ))}
            <button style={{ marginTop: 18, background: wACCENT, color: wBG, border: 'none', borderRadius: 10, padding: '13px 22px', fontFamily: wFONT.title, fontSize: 12, letterSpacing: '0.08em' }}>VOIR L'ANALYSE COMPLÈTE →</button>
          </div>
        </div>
      </section>

      {/* Méthode */}
      <section style={{ padding: '96px 56px', background: wBG2, marginTop: 56 }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <h2 style={{ fontFamily: wFONT.title, fontSize: 64, letterSpacing: '-0.04em', lineHeight: 1.0, margin: 0 }}>9 critères.<br/>Chaque match.<br/>Zéro biais.</h2>
          <p style={{ fontFamily: wFONT.ui, fontSize: 17, color: wINK2, maxWidth: 640, margin: '20px auto 0', lineHeight: 1.5 }}>
            L'IA croise plus de 50 millions de données par match. Voici exactement ce qu'elle analyse.
          </p>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }}>
          {[
            ['01', 'Forme actuelle',  '5 derniers'],
            ['02', 'Confrontations',  'h2h 10 ans'],
            ['03', 'Dom/Ext',         'taux W'],
            ['04', 'Blessures clés',  'titulaires'],
            ['05', 'Météo',           'pluie · vent · T°'],
            ['06', 'Cotes marché',    'consensus'],
            ['07', 'Cartons',         'arbitre'],
            ['08', 'Possession',      'style'],
            ['09', 'xG · buts att.',  'modèle'],
          ].map(([n, l, s]) => (
            <div key={n} style={{ padding: 22, background: wBG, border: `1px solid ${wLINE}`, borderRadius: 12 }}>
              <div style={{ fontFamily: wFONT.mono, fontSize: 11, color: wACCENT, letterSpacing: '0.18em' }}>{n}</div>
              <div style={{ fontFamily: wFONT.title, fontSize: 18, color: wINK, letterSpacing: '-0.02em', marginTop: 10 }}>{l}</div>
              <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.1em', marginTop: 6 }}>{s.toUpperCase()}</div>
            </div>
          ))}
        </div>
        <div style={{ marginTop: 36, padding: '20px 24px', background: wBG, border: `1px solid ${wLINE}`, borderRadius: 12, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 20 }}>
          <span style={{ fontFamily: wFONT.mono, fontSize: 13, color: wDIM, letterSpacing: '0.08em' }}>9 CRITÈRES</span>
          <span style={{ color: wACCENT, fontFamily: wFONT.title, fontSize: 20 }}>→</span>
          <span style={{ fontFamily: wFONT.mono, fontSize: 13, color: wINK, letterSpacing: '0.08em' }}>ALGORITHME IA</span>
          <span style={{ color: wACCENT, fontFamily: wFONT.title, fontSize: 20 }}>→</span>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 4 }}>
            <span style={{ fontFamily: wFONT.title, fontSize: 30, color: wACCENT, letterSpacing: '-0.03em' }}>87</span>
            <span style={{ fontFamily: wFONT.mono, fontSize: 13, color: wDIM }}>% CONFIANCE</span>
          </div>
        </div>
      </section>

      {/* Stats publiques */}
      <section style={{ padding: '72px 56px', background: wBG }}>
        <div style={{ textAlign: 'center', marginBottom: 32 }}>
          <div style={{ fontFamily: wFONT.mono, fontSize: 11, color: wACCENT, letterSpacing: '0.18em' }}>STATS RÉELLES — MAI 2026</div>
          <h2 style={{ fontFamily: wFONT.title, fontSize: 56, letterSpacing: '-0.04em', margin: '12px 0 0' }}>Résultats publics.</h2>
        </div>
        <div style={{ display: 'flex', gap: 32, justifyContent: 'center', marginBottom: 30 }}>
          {[['+18.5%', 'ROI MOYEN'], ['72%', 'TAUX RÉUSSITE'], ['47k', 'UTILISATEURS']].map(([n, l]) => (
            <div key={l} style={{ textAlign: 'center' }}>
              <div style={{ fontFamily: wFONT.title, fontSize: 76, color: wACCENT, letterSpacing: '-0.04em', lineHeight: 1 }}>{n}</div>
              <div style={{ fontFamily: wFONT.mono, fontSize: 11, color: wDIM, letterSpacing: '0.18em', marginTop: 8 }}>{l}</div>
            </div>
          ))}
        </div>
        <div style={{ marginTop: 24 }}>
          <Sparkline data={[42, 48, 52, 49, 55, 58, 60, 62, 65, 67, 70, 72]} width={1100} height={120} color={wACCENT} />
          <div style={{ display: 'flex', justifyContent: 'space-between', fontFamily: wFONT.mono, fontSize: 9, color: wDIM2, letterSpacing: '0.1em', marginTop: 4 }}>
            <span>JUIN 25</span><span>SEP 25</span><span>DÉC 25</span><span>MAR 26</span><span>MAI 26</span>
          </div>
        </div>
      </section>

      {/* Téléchargement */}
      <section style={{ padding: '80px 56px', background: wACCENT, color: wBG, position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', top: -60, right: -40, fontFamily: wFONT.title, fontSize: 480, lineHeight: 0.85, color: wBG, opacity: 0.06, letterSpacing: '-0.06em' }}>COTA</div>
        <div style={{ position: 'relative', display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: 40, alignItems: 'center' }}>
          <div>
            <h2 style={{ fontFamily: wFONT.title, fontSize: 56, letterSpacing: '-0.04em', lineHeight: 1.0, margin: 0 }}>
              Ton coupon arrive<br/>à 9h30. Chaque jour.
            </h2>
            <p style={{ fontFamily: wFONT.ui, fontSize: 17, color: 'rgba(11,13,16,0.7)', marginTop: 16, lineHeight: 1.5, maxWidth: 520 }}>
              Notifications push, SMS, email. Tu choisis. L'app est gratuite, l'essai Premium dure 14 jours.
            </p>
            <div style={{ display: 'flex', gap: 12, marginTop: 28 }}>
              <button style={{ background: wBG, color: wACCENT, border: 'none', borderRadius: 10, padding: '14px 22px', fontFamily: wFONT.title, fontSize: 13, letterSpacing: '0.05em' }}>↓ APP STORE</button>
              <button style={{ background: wBG, color: wACCENT, border: 'none', borderRadius: 10, padding: '14px 22px', fontFamily: wFONT.title, fontSize: 13, letterSpacing: '0.05em' }}>↓ GOOGLE PLAY</button>
            </div>
          </div>
          <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
            <div style={{ padding: 14, background: '#fff', borderRadius: 12, boxShadow: '0 12px 30px rgba(0,0,0,0.2)' }}>
              {window.QRCode ? <window.QRCode size={140} /> : <div style={{ width: 140, height: 140 }}>QR</div>}
            </div>
          </div>
        </div>
      </section>

      {/* Trust bar */}
      <section style={{ padding: '32px 56px', borderTop: `1px solid ${wLINE}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em' }}>PRONOSTICS IA POUR</div>
        <div style={{ display: 'flex', gap: 32, fontFamily: wFONT.title, fontSize: 13, color: wINK2, letterSpacing: '0.04em' }}>
          <span>LIGUE 1</span><span>UCL</span><span>PREMIER LEAGUE</span><span>LA LIGA</span><span>BUNDESLIGA</span><span>SERIE A</span>
        </div>
      </section>

      {/* Footer */}
      <footer style={{ padding: '48px 56px', background: wBG2, borderTop: `1px solid ${wLINE}` }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1.5fr 1fr 1fr 1fr 1fr', gap: 28, marginBottom: 36 }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <AppIcon size={32} />
              <Wordmark size={22} underline={true} />
            </div>
            <div style={{ marginTop: 14, fontSize: 12, color: wDIM, lineHeight: 1.5, maxWidth: 240 }}>
              Le foot, lu par une IA. Coupon quotidien à 9h30.
            </div>
          </div>
          {[
            ['PRODUIT', ['Coupons', 'Méthode', 'Stats', 'Premium']],
            ['LÉGAL',   ['CGU', 'Confidentialité', 'Jeu responsable', 'Mentions']],
            ['COMMUNAUTÉ', ['Discord', 'Twitter', 'Instagram', 'TikTok']],
            ['CONTACT', ['Support', 'Partenariats', 'Presse', 'Affiliation']],
          ].map(([h, items]) => (
            <div key={h}>
              <div style={{ fontFamily: wFONT.mono, fontSize: 10, color: wDIM, letterSpacing: '0.18em', marginBottom: 12 }}>{h}</div>
              {items.map(it => (
                <div key={it} style={{ fontSize: 12, color: wINK2, marginBottom: 8 }}>{it}</div>
              ))}
            </div>
          ))}
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: 24, borderTop: `1px solid ${wLINE}`, fontFamily: wFONT.mono, fontSize: 10, color: wDIM2, letterSpacing: '0.12em' }}>
          <div>© 2026 COTA · TOUS DROITS RÉSERVÉS</div>
          <div style={{ display: 'flex', gap: 12 }}>
            <span>X</span><span>IG</span><span>TT</span>
          </div>
        </div>
      </footer>
    </div>
  );
}

Object.assign(window, { WebMobileHome, WebTablet, WebDesktopLanding, FlashRow });
