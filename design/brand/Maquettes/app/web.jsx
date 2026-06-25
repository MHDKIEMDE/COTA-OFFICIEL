// COTA — backend / web : landing page hero + dashboard admin (light).

const { BG: lBG, BG2: lBG2, BG3: lBG3, LINE: lLINE, LINE2: lLINE2, INK: lINK, INK2: lINK2, DIM: lDIM, DIM2: lDIM2, ACCENT: lACCENT, WIN: lWIN, font: lFONT } = window.COTA;

// ── Landing page hero — cota.app ─────────────────────────────────────────────
function Landing() {
  const psg = window.TEAMS.PSG;
  const om  = window.TEAMS.OM;
  return (
    <div style={{ width: '100%', height: '100%', background: lBG, color: lINK, fontFamily: lFONT.ui, position: 'relative', overflow: 'hidden' }}>

      {/* Nav */}
      <header style={{ position: 'relative', zIndex: 5, padding: '24px 56px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: `1px solid ${lLINE}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <AppIcon size={32} />
          <Wordmark size={24} underline={false} />
        </div>
        <nav style={{ display: 'flex', gap: 28, alignItems: 'center', fontFamily: lFONT.mono, fontSize: 11, letterSpacing: '0.12em' }}>
          <a style={{ color: lINK }}>MÉTHODE</a>
          <a style={{ color: lDIM }}>STATS</a>
          <a style={{ color: lDIM }}>PRICING</a>
          <a style={{ color: lDIM }}>BLOG</a>
          <button style={{ background: lACCENT, color: lBG, border: 'none', borderRadius: 8, padding: '10px 16px', fontFamily: lFONT.title, fontSize: 11, letterSpacing: '0.08em' }}>
            TÉLÉCHARGER L'APP →
          </button>
        </nav>
      </header>

      {/* Hero */}
      <section style={{ position: 'relative', padding: '64px 56px 80px', display: 'grid', gridTemplateColumns: '1.1fr 1fr', gap: 56, alignItems: 'center' }}>
        {/* background flourish */}
        <div style={{
          position: 'absolute', top: -120, right: -120, width: 600, height: 600,
          background: `radial-gradient(circle, rgba(232,255,54,0.08), transparent 60%)`,
        }} />

        <div style={{ position: 'relative', zIndex: 1 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 22 }}>
            <Pill bg="rgba(232,255,54,0.10)" color={lACCENT} border={lACCENT}>
              <span style={{ width: 6, height: 6, background: lACCENT, borderRadius: 3, animation: 'cota-live-pulse 1.4s ease-in-out infinite' }} />
              247 ÉDITIONS ANALYSÉES CE WEEK-END
            </Pill>
          </div>

          <h1 style={{ fontFamily: lFONT.title, fontSize: 88, lineHeight: 0.96, letterSpacing: '-0.05em', margin: 0 }}>
            Le foot,<br />
            lu par<br />
            une <span style={{ background: lACCENT, color: lBG, padding: '0 14px' }}>IA</span>.
          </h1>

          <p style={{ fontSize: 18, color: lINK2, marginTop: 24, lineHeight: 1.5, maxWidth: 540 }}>
            9 critères, 1 score de confiance, 1 carnet par jour. COTA croise plus de 50 millions de données par match pour livrer une analyse claire — sans baratin, sans émotion.
          </p>

          {/* CTAs */}
          <div style={{ display: 'flex', gap: 14, marginTop: 32 }}>
            <button style={{ background: lACCENT, color: lBG, border: 'none', borderRadius: 10, padding: '16px 24px', fontFamily: lFONT.title, fontSize: 14, letterSpacing: '0.05em', display: 'flex', alignItems: 'center', gap: 10 }}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill={lBG}><path d="M11.5 8.3a3 3 0 0 1 1.5-2.6c-.6-.9-1.5-1.4-2.7-1.5-1.1-.1-2.3.7-2.9.7-.6 0-1.6-.7-2.6-.7-1.4 0-2.7.8-3.4 2.1-1.5 2.6-.4 6.4 1 8.5.7 1 1.5 2.2 2.6 2.1 1-.04 1.4-.7 2.7-.7s1.6.7 2.7.7c1.1 0 1.8-1 2.5-2 .8-1.2 1.1-2.3 1.1-2.4-.02-.02-2.2-.85-2.5-3.2zM9.9 2.7a3 3 0 0 0 .7-2.2 3.1 3.1 0 0 0-2 1c-.4.6-.8 1.4-.7 2.2.8.07 1.6-.4 2-1z"/></svg>
              APP STORE
            </button>
            <button style={{ background: 'transparent', color: lINK, border: `1px solid ${lLINE2}`, borderRadius: 10, padding: '16px 24px', fontFamily: lFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>
              GOOGLE PLAY ↓
            </button>
          </div>

          {/* stats strip */}
          <div style={{ display: 'flex', gap: 36, marginTop: 48 }}>
            {[
              ['+18.5%', 'ROI moyen utilisateur'],
              ['72%', 'taux de réussite'],
              ['47k', 'utilisateurs actifs'],
            ].map(([n, l]) => (
              <div key={l}>
                <div style={{ fontFamily: lFONT.title, fontSize: 32, color: lACCENT, letterSpacing: '-0.03em', lineHeight: 1 }}>{n}</div>
                <div style={{ fontFamily: lFONT.mono, fontSize: 10, color: lDIM, letterSpacing: '0.15em', marginTop: 6 }}>{l.toUpperCase()}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Phone visual */}
        <div style={{ position: 'relative', zIndex: 1, display: 'flex', justifyContent: 'center', alignItems: 'center', height: 580 }}>
          {/* device */}
          <div style={{ width: 320, height: 580, borderRadius: 36, background: lBG2, border: `8px solid ${lBG3}`, overflow: 'hidden', boxShadow: '0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(232,255,54,0.06)' }}>
            <div style={{ width: '100%', height: '100%', position: 'relative' }}>
              {/* fake hero match poster inside */}
              <div style={{ height: 200, position: 'relative' }}>
                <MatchBackdrop home="PSG" away="OM">
                  <div style={{ position: 'absolute', bottom: 14, left: 14, right: 14 }}>
                    <Pill bg="rgba(11,13,16,0.6)" color={lINK} border={lLINE2}>LIGUE 1 · J34</Pill>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginTop: 12 }}>
                      <TeamBadge code={psg.code} color={psg.color} text={psg.text} size={32} />
                      <div style={{ flex: 1, fontFamily: lFONT.title, fontSize: 20, letterSpacing: '-0.03em' }}>PSG <span style={{ color: lDIM2, fontSize: 13 }}>VS</span> OM</div>
                      <TeamBadge code={om.code} color={om.color} text={om.text} size={32} />
                    </div>
                  </div>
                </MatchBackdrop>
              </div>
              <div style={{ padding: 14 }}>
                <div style={{ background: lBG, border: `1px solid ${lLINE}`, borderRadius: 10, padding: 12, display: 'flex', alignItems: 'center', gap: 12 }}>
                  <ConfidenceRing value={87} size={60} stroke={5} />
                  <div style={{ flex: 1 }}>
                    <div style={{ fontFamily: lFONT.mono, fontSize: 9, color: lDIM, letterSpacing: '0.15em' }}>CONSEIL IA</div>
                    <div style={{ fontFamily: lFONT.title, fontSize: 14, marginTop: 4 }}>Victoire PSG</div>
                    <OddsChip value={1.65} highlight size="sm" />
                  </div>
                </div>
                <div style={{ marginTop: 14, fontFamily: lFONT.mono, fontSize: 9, color: lDIM, letterSpacing: '0.15em' }}>9 CRITÈRES ANALYSÉS</div>
                {['Forme PSG', 'H2H', 'Domicile', 'Blessures'].map((c, i) => (
                  <div key={c} style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: `1px solid ${lLINE}` }}>
                    <span style={{ fontSize: 12 }}>{c}</span>
                    <span style={{ fontFamily: lFONT.mono, fontSize: 11, color: lACCENT, fontWeight: 700 }}>{['4V 1N', '6-2-2', '89%', '0 vs 2'][i]}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
          {/* floating odds chip */}
          <div style={{ position: 'absolute', top: 70, left: -40, padding: '12px 18px', background: lBG2, border: `1px solid ${lLINE2}`, borderRadius: 12, boxShadow: '0 12px 30px rgba(0,0,0,0.5)' }}>
            <div style={{ fontFamily: lFONT.mono, fontSize: 9, color: lDIM, letterSpacing: '0.12em' }}>CARNET DU JOUR</div>
            <div style={{ fontFamily: lFONT.title, fontSize: 28, color: lACCENT, marginTop: 4, letterSpacing: '-0.03em' }}>@4.55</div>
          </div>
          <div style={{ position: 'absolute', bottom: 100, right: -30, padding: '10px 14px', background: lACCENT, color: lBG, borderRadius: 10, boxShadow: '0 8px 24px rgba(232,255,54,0.3)' }}>
            <div style={{ fontFamily: lFONT.mono, fontSize: 9, letterSpacing: '0.15em' }}>✓ CARNET VALIDÉ</div>
            <div style={{ fontFamily: lFONT.title, fontSize: 14, marginTop: 2 }}>+44.20€</div>
          </div>
        </div>
      </section>

      {/* Trust bar */}
      <section style={{ padding: '40px 56px', borderTop: `1px solid ${lLINE}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div style={{ fontFamily: lFONT.mono, fontSize: 11, color: lDIM, letterSpacing: '0.18em' }}>ÉDITION IA POUR</div>
        <div style={{ display: 'flex', gap: 36, fontFamily: lFONT.title, fontSize: 14, color: lINK2, letterSpacing: '0.04em' }}>
          <span>LIGUE 1</span>
          <span>UCL</span>
          <span>PREMIER LEAGUE</span>
          <span>LA LIGA</span>
          <span>BUNDESLIGA</span>
          <span>SERIE A</span>
        </div>
      </section>

    </div>
  );
}

// ── Admin / rédacteur dashboard ────────────────────────────────────────────────
function AdminDashboard() {
  return (
    <div style={{ width: '100%', height: '100%', background: lBG, color: lINK, fontFamily: lFONT.ui, position: 'relative', overflow: 'hidden' }}>
      {/* Sidebar */}
      <aside style={{ position: 'absolute', left: 0, top: 0, bottom: 0, width: 220, background: lBG2, borderRight: `1px solid ${lLINE}`, padding: '24px 18px', display: 'flex', flexDirection: 'column', gap: 28 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <AppIcon size={28} />
          <div>
            <Wordmark size={16} underline={false} />
            <div style={{ fontFamily: lFONT.mono, fontSize: 9, color: lDIM, letterSpacing: '0.15em', marginTop: 2 }}>ADMIN</div>
          </div>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          {[
            ['VUE D\'ENSEMBLE', true],
            ['ÉDITIONS', false],
            ['CARNETS', false],
            ['MODÈLE IA', false],
            ['UTILISATEURS', false],
            ['MONÉTISATION', false],
          ].map(([n, on]) => (
            <div key={n} style={{
              padding: '9px 12px', borderRadius: 8,
              background: on ? lBG3 : 'transparent',
              fontFamily: lFONT.mono, fontSize: 11, letterSpacing: '0.1em',
              color: on ? lACCENT : lDIM,
              borderLeft: on ? `2px solid ${lACCENT}` : '2px solid transparent',
            }}>{n}</div>
          ))}
        </div>

        <div style={{ marginTop: 'auto', padding: '12px', borderRadius: 8, background: lBG3, border: `1px solid ${lLINE2}` }}>
          <div style={{ fontFamily: lFONT.mono, fontSize: 9, color: lACCENT, letterSpacing: '0.15em' }}>● MODÈLE OK</div>
          <div style={{ fontSize: 11, color: lINK, marginTop: 4 }}>v1.0.4 · 09:30 UTC</div>
        </div>
      </aside>

      {/* Main */}
      <main style={{ marginLeft: 220, padding: '24px 32px', height: '100%', overflow: 'auto' }}>
        {/* Top bar */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 }}>
          <div>
            <div style={{ fontFamily: lFONT.mono, fontSize: 10, color: lDIM, letterSpacing: '0.18em' }}>MAR 18 MAI 2026</div>
            <h2 style={{ fontFamily: lFONT.title, fontSize: 28, letterSpacing: '-0.03em', margin: '4px 0 0' }}>Vue d'ensemble</h2>
          </div>
          <div style={{ display: 'flex', gap: 10 }}>
            <button style={{ background: 'transparent', color: lINK, border: `1px solid ${lLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: lFONT.mono, fontSize: 11, letterSpacing: '0.1em' }}>EXPORT CSV</button>
            <button style={{ background: lACCENT, color: lBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: lFONT.title, fontSize: 11, letterSpacing: '0.08em' }}>PUBLIER LE CARNET</button>
          </div>
        </div>

        {/* KPIs */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 22 }}>
          {[
            ['247', 'ÉDITIONS ANALYSÉES', null, lACCENT],
            ['12.4k', 'CARNETS GÉNÉRÉS', '+8% vs hier', lINK],
            ['72.1%', 'TAUX SUCCÈS', '+1.2pt', lWIN],
            ['€18.2k', 'GAINS UTILISATEURS', '+€2.4k', lACCENT],
          ].map(([n, l, d, c]) => (
            <div key={l} style={{ padding: 16, background: lBG2, border: `1px solid ${lLINE}`, borderRadius: 10 }}>
              <div style={{ fontFamily: lFONT.title, fontSize: 28, color: c, letterSpacing: '-0.03em' }}>{n}</div>
              <div style={{ fontFamily: lFONT.mono, fontSize: 9, color: lDIM, letterSpacing: '0.15em', marginTop: 6 }}>{l}</div>
              {d && <div style={{ fontFamily: lFONT.mono, fontSize: 10, color: lWIN, marginTop: 4 }}>↗ {d}</div>}
            </div>
          ))}
        </div>

        {/* Today's carnet + Éditions list */}
        <div style={{ display: 'grid', gridTemplateColumns: '1.2fr 1fr', gap: 18 }}>
          <div style={{ padding: 20, background: lBG2, border: `1px solid ${lLINE}`, borderRadius: 12 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 14 }}>
              <div>
                <div style={{ fontFamily: lFONT.mono, fontSize: 10, color: lACCENT, letterSpacing: '0.18em' }}>★ CARNET DU JOUR</div>
                <div style={{ fontFamily: lFONT.title, fontSize: 22, letterSpacing: '-0.03em', marginTop: 4 }}>3 sélections combinées</div>
              </div>
              <div style={{ fontFamily: lFONT.title, fontSize: 32, color: lACCENT, letterSpacing: '-0.03em' }}>@4.55</div>
            </div>

            {[
              ['PSG–OM', 'VICTOIRE PSG', 1.65, 87],
              ['LIV–ARS', 'INDICE +', 1.78, 76],
              ['RMA–BAY', 'NOTE A', 1.55, 91],
            ].map(([m, t, o, c]) => (
              <div key={m} style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '12px 0', borderTop: `1px solid ${lLINE}` }}>
                <div style={{ fontFamily: lFONT.mono, fontSize: 11, color: lDIM, letterSpacing: '0.08em', width: 90 }}>{m}</div>
                <div style={{ flex: 1, fontSize: 13 }}>{t}</div>
                <div style={{ fontFamily: lFONT.mono, fontSize: 12, color: lINK }}>{c}%</div>
                <div style={{ fontFamily: lFONT.mono, fontSize: 12, color: lACCENT, fontWeight: 700, width: 50, textAlign: 'right' }}>@{o}</div>
              </div>
            ))}
          </div>

          <div style={{ padding: 20, background: lBG2, border: `1px solid ${lLINE}`, borderRadius: 12 }}>
            <div style={{ fontFamily: lFONT.mono, fontSize: 10, color: lDIM, letterSpacing: '0.18em', marginBottom: 14 }}>MODÈLE IA — DERNIÈRES PERFORMANCES</div>
            {[
              ['Forme', 92],
              ['Confrontations', 84],
              ['Domicile/Ext', 78],
              ['Blessures', 71],
              ['Indices marché', 88],
              ['xG', 95],
            ].map(([n, v]) => (
              <div key={n} style={{ marginBottom: 12 }}>
                <ConfidenceBar value={v} label={n} />
              </div>
            ))}
          </div>
        </div>

      </main>
    </div>
  );
}

Object.assign(window, { Landing, AdminDashboard });
