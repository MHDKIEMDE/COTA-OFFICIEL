// COTA — onboarding (DAZN-inspired, 3 screens).
// Cinematic full-bleed hero, content row preview, big rituel.

const { BG: oBG, BG2: oBG2, BG3: oBG3, LINE: oLINE, LINE2: oLINE2, INK: oINK, INK2: oINK2, DIM: oDIM, DIM2: oDIM2, ACCENT: oACCENT, WIN: oWIN, font: oFONT } = window.COTA;

// ── 01 · Hero ─────────────────────────────────────────────────────────────────
// Inspired by DAZN's pre-onboarding hero: full-bleed match poster, app brand
// over the top, big CTA at bottom.
function OnboardHero() {
  const psg = window.TEAMS.PSG;
  const om  = window.TEAMS.OM;
  return (
    <div style={{ background: oBG, color: oINK, height: '100%', position: 'relative', fontFamily: oFONT.ui }}>
      {/* Full-bleed cinematic hero — match poster + heavy gradient */}
      <div style={{ position: 'absolute', inset: 0 }}>
        <div style={{
          position: 'absolute', inset: 0,
          background: `linear-gradient(108deg, ${psg.color} 0%, ${psg.color} 45%, ${om.color} 55%, ${om.color} 100%)`,
        }} />
        <div style={{
          position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(0,0,0,0.05) 0%, rgba(11,13,16,0.6) 50%, #0b0d10 90%)',
        }} />
        {/* big team monograms */}
        <div style={{ position: 'absolute', top: 110, left: 0, right: 0, display: 'flex', justifyContent: 'space-between', padding: '0 8px', fontFamily: oFONT.title, fontSize: 200, lineHeight: 0.78, opacity: 0.13, letterSpacing: '-0.06em', color: oINK }}>
          <span>PSG</span>
          <span style={{ textAlign: 'right' }}>OM</span>
        </div>

        {/* tickertape */}
        <div style={{
          position: 'absolute', top: 78, left: 0, right: 0, height: 26,
          background: 'rgba(11,13,16,0.55)',
          backdropFilter: 'blur(8px)', WebkitBackdropFilter: 'blur(8px)',
          borderTop: `1px solid ${oLINE2}`, borderBottom: `1px solid ${oLINE2}`,
          overflow: 'hidden', display: 'flex', alignItems: 'center',
        }}>
          <div style={{ whiteSpace: 'nowrap', fontFamily: oFONT.mono, fontSize: 10, color: oDIM, letterSpacing: '0.12em' }}>
            {'  '}PSG–OM @1.65{'   ·   '}LIV–ARS +2.5 @1.78{'   ·   '}RMA–BAY NOTE @1.55{'   ·   '}CARNET DU JOUR @4.55{'   ·   '}87% CONFIANCE{'   ·   '}PSG–OM @1.65{'   ·   '}
          </div>
        </div>
      </div>

      {/* Top — skip + match meta */}
      <div style={{ position: 'absolute', top: 56, left: 20, right: 20, display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 5 }}>
        <Pill bg="rgba(11,13,16,0.5)" color={oINK} border={oLINE2}>L1 · J34 · CE SOIR 21H</Pill>
        <button style={{ background: 'transparent', border: 'none', color: oINK, fontFamily: oFONT.mono, fontSize: 10, letterSpacing: '0.15em', padding: 0 }}>PASSER →</button>
      </div>

      {/* Bottom block — brand + tagline + stat strip + CTA */}
      <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: '0 24px 50px', display: 'flex', flexDirection: 'column', gap: 22 }}>

        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <AppIcon size={56} />
          <Wordmark size={42} underline={true} />
        </div>

        <div style={{ fontFamily: oFONT.title, fontSize: 30, letterSpacing: '-0.03em', lineHeight: 1.02 }}>
          Le foot, lu<br />par une <span style={{ background: oACCENT, color: oBG, padding: '0 8px' }}>IA</span>.
        </div>

        <div style={{ fontSize: 14, color: oINK2, lineHeight: 1.45 }}>
          9 critères, 1 score de confiance, 1 carnet par jour. Pas de baratin, pas d'émotion : que de la donnée.
        </div>

        {/* stat strip */}
        <div style={{ display: 'flex', gap: 12 }}>
          {[['9', 'critères IA'], ['1', 'carnet / jour'], ['+18%', 'ROI saison']].map(([n, l]) => (
            <div key={l} style={{ flex: 1, padding: '10px 12px', background: 'rgba(21,24,29,0.65)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', borderRadius: 10, border: `1px solid ${oLINE2}` }}>
              <div style={{ fontFamily: oFONT.title, fontSize: 22, color: oACCENT, lineHeight: 1, letterSpacing: '-0.03em' }}>{n}</div>
              <div style={{ fontFamily: oFONT.mono, fontSize: 9, color: oDIM, letterSpacing: '0.12em', marginTop: 4 }}>{l.toUpperCase()}</div>
            </div>
          ))}
        </div>

        <button style={{
          width: '100%', height: 54, background: oACCENT, color: oBG,
          border: 'none', borderRadius: 12,
          fontFamily: oFONT.title, fontSize: 15, letterSpacing: '0.05em',
          display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
        }}>
          COMMENCER GRATUITEMENT
          <span style={{ fontFamily: oFONT.mono, fontWeight: 700 }}>→</span>
        </button>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 6 }}>
          <span style={{ width: 24, height: 3, background: oACCENT, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
        </div>
      </div>
    </div>
  );
}

// ── 02 · 9 critères ───────────────────────────────────────────────────────────
function OnboardCriteria() {
  const C = [
    { n: '01', l: 'Forme actuelle',       v: '5 derniers' },
    { n: '02', l: 'Confrontations',       v: 'h2h 10 ans' },
    { n: '03', l: 'Dom / Ext',            v: 'taux W' },
    { n: '04', l: 'Blessures',            v: 'titulaires' },
    { n: '05', l: 'Météo',                v: 'pluie · vent · T°' },
    { n: '06', l: 'Indices marché',         v: 'consensus' },
    { n: '07', l: 'Cartons',              v: 'arbitre' },
    { n: '08', l: 'Possession',           v: 'style' },
    { n: '09', l: 'Buts attendus',        v: 'xG' },
  ];
  return (
    <div style={{ background: oBG, color: oINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: oFONT.ui, paddingTop: 80 }}>
      <div style={{ padding: '20px 24px 0' }}>
        <div style={{ fontFamily: oFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: oACCENT, marginBottom: 14 }}>02 — MÉTHODE</div>
        <div style={{ fontFamily: oFONT.title, fontSize: 36, letterSpacing: '-0.04em', lineHeight: 1.0 }}>
          9 critères.<br />
          Chaque<br />
          match.
        </div>
        <div style={{ fontSize: 14, color: oINK2, marginTop: 14, lineHeight: 1.5 }}>
          Notre IA croise ces 9 données pour chaque match. Sans biais, sans favori.
        </div>
      </div>

      {/* 3x3 grid card */}
      <div style={{ flex: 1, padding: '24px 20px 12px' }}>
        <div style={{ background: oBG2, borderRadius: 16, border: `1px solid ${oLINE}`, padding: 14, display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 8 }}>
          {C.map(c => (
            <div key={c.n} style={{ padding: '14px 10px', background: oBG, borderRadius: 10, border: `1px solid ${oLINE2}`, display: 'flex', flexDirection: 'column', gap: 6 }}>
              <div style={{ fontFamily: oFONT.mono, fontSize: 9, color: oACCENT, letterSpacing: '0.15em' }}>{c.n}</div>
              <div style={{ fontSize: 11, fontWeight: 600, color: oINK, lineHeight: 1.2 }}>{c.l}</div>
              <div style={{ fontFamily: oFONT.mono, fontSize: 9, color: oDIM, letterSpacing: '0.08em' }}>{c.v}</div>
            </div>
          ))}
        </div>

        {/* score formula visual */}
        <div style={{ marginTop: 16, padding: '14px 16px', background: oBG2, borderRadius: 12, border: `1px solid ${oLINE}`, display: 'flex', alignItems: 'center', gap: 14 }}>
          <div style={{ fontFamily: oFONT.mono, fontSize: 11, color: oDIM, letterSpacing: '0.05em' }}>
            9 critères → analyse IA →
          </div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 4 }}>
            <span style={{ fontFamily: oFONT.title, fontSize: 28, color: oACCENT, letterSpacing: '-0.03em' }}>87</span>
            <span style={{ fontFamily: oFONT.mono, fontSize: 10, color: oDIM }}>%</span>
          </div>
        </div>
      </div>

      <div style={{ padding: '12px 24px 50px' }}>
        <button style={{
          width: '100%', height: 54, background: oACCENT, color: oBG,
          border: 'none', borderRadius: 12,
          fontFamily: oFONT.title, fontSize: 15, letterSpacing: '0.05em',
        }}>SUIVANT →</button>
        <div style={{ display: 'flex', justifyContent: 'center', gap: 6, marginTop: 16 }}>
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
          <span style={{ width: 24, height: 3, background: oACCENT, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
        </div>
      </div>
    </div>
  );
}

// ── 03 · Notifications ────────────────────────────────────────────────────────
function OnboardNotif() {
  return (
    <div style={{ background: oBG, color: oINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: oFONT.ui, paddingTop: 80 }}>
      <div style={{ padding: '20px 24px 0' }}>
        <div style={{ fontFamily: oFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: oACCENT, marginBottom: 14 }}>03 — RITUEL</div>
        <div style={{ fontFamily: oFONT.title, fontSize: 32, letterSpacing: '-0.04em', lineHeight: 1.02 }}>
          Ton carnet,<br />
          chaque<br />
          matin <span style={{ color: oACCENT }}>9h30</span>.
        </div>
      </div>

      {/* Notification preview floating */}
      <div style={{ flex: 1, padding: '24px 20px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <div style={{ position: 'relative', width: '100%' }}>
          {/* faux earlier notification (faded) */}
          <div style={{
            position: 'absolute', top: -34, left: 8, right: 8, height: 50,
            background: 'rgba(20, 23, 28, 0.45)',
            backdropFilter: 'blur(20px)', WebkitBackdropFilter: 'blur(20px)',
            border: `1px solid ${oLINE2}`,
            borderRadius: 18,
            transform: 'scale(0.94)', opacity: 0.5,
          }} />
          <div style={{
            background: 'rgba(20, 23, 28, 0.92)',
            backdropFilter: 'blur(20px)', WebkitBackdropFilter: 'blur(20px)',
            border: `1px solid ${oLINE2}`,
            borderRadius: 22, padding: '16px',
            boxShadow: '0 16px 50px rgba(0,0,0,0.7)',
            position: 'relative',
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
              <div style={{ borderRadius: 10, overflow: 'hidden' }}><AppIcon size={40} /></div>
              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                  <div style={{ fontSize: 13, fontWeight: 700 }}>COTA</div>
                  <div style={{ fontSize: 11, color: oDIM, fontFamily: oFONT.mono }}>maintenant</div>
                </div>
                <div style={{ fontSize: 11, color: oDIM, marginTop: 1 }}>Ton carnet est prêt</div>
              </div>
            </div>
            <div style={{ fontSize: 14, fontWeight: 600, lineHeight: 1.4 }}>
              3 sélections combinées · confiance <span style={{ color: oACCENT, fontFamily: oFONT.mono }}>87%</span>
            </div>
            <div style={{ marginTop: 4, fontSize: 12, color: oDIM2 }}>
              Indice <span style={{ fontFamily: oFONT.mono, color: oINK, fontWeight: 700 }}>@4.55</span> · PSG · LIV · RMA
            </div>
          </div>
        </div>
      </div>

      {/* benefits */}
      <div style={{ padding: '0 24px 20px', display: 'flex', flexDirection: 'column', gap: 12 }}>
        {[
          ['Carnet à 9h30', 'avant l\'ouverture des cotes'],
          ['Indice en direct', 'alerte quand un indice bouge'],
          ['Résultat instantané', 'validé ou manqué, dès la fin'],
        ].map(([t, s]) => (
          <div key={t} style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ width: 22, height: 22, borderRadius: 11, background: oACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <svg width="12" height="12" viewBox="0 0 12 12"><path d="M2 6.5 L5 9.5 L10 3.5" stroke={oBG} strokeWidth="2.2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
            </div>
            <div>
              <div style={{ fontSize: 13, fontWeight: 600 }}>{t}</div>
              <div style={{ fontSize: 11, color: oDIM }}>{s}</div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ padding: '8px 24px 50px' }}>
        <button style={{ width: '100%', height: 54, background: oACCENT, color: oBG, border: 'none', borderRadius: 12, fontFamily: oFONT.title, fontSize: 15, letterSpacing: '0.05em' }}>ACTIVER LES NOTIFS</button>
        <button style={{ width: '100%', height: 38, background: 'transparent', color: oDIM, border: 'none', fontFamily: oFONT.mono, fontSize: 10, letterSpacing: '0.15em', marginTop: 4 }}>PLUS TARD</button>
        <div style={{ display: 'flex', justifyContent: 'center', gap: 6, marginTop: 10 }}>
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: oLINE2, borderRadius: 2 }} />
          <span style={{ width: 24, height: 3, background: oACCENT, borderRadius: 2 }} />
        </div>
      </div>
    </div>
  );
}

Object.assign(window, { OnboardHero, OnboardCriteria, OnboardNotif });
