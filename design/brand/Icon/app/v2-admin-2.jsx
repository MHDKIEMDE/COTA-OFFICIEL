// COTA V2 — Admin pages 7-9 + states.

const { BG: eBG, BG2: eBG2, BG3: eBG3, LINE: eLINE, LINE2: eLINE2, INK: eINK, INK2: eINK2, DIM: eDIM, DIM2: eDIM2, ACCENT: eACCENT, WIN: eWIN, LOSS: eLOSS, font: eFONT } = window.COTA;

// ── D7 · App / Download config ───────────────────────────────────────────────
function AdminAppDownload() {
  const FormField = ({ label, value, hint }) => (
    <div style={{ marginBottom: 12 }}>
      <label style={{ fontFamily: eFONT.mono, fontSize: 9, color: eDIM, letterSpacing: '0.15em', display: 'block', marginBottom: 4 }}>{label}</label>
      <div style={{ padding: '10px 12px', background: eBG2, border: `1px solid ${eLINE2}`, borderRadius: 8, fontSize: 12, color: eINK, fontFamily: eFONT.ui }}>{value}</div>
      {hint && <div style={{ fontFamily: eFONT.mono, fontSize: 9, color: eDIM2, letterSpacing: '0.08em', marginTop: 4 }}>{hint}</div>}
    </div>
  );

  return (
    <AdminFrame
      active="app-download"
      breadcrumb="APP · TÉLÉCHARGEMENT"
      title="Section Télécharger l'App"
      actions={(
        <>
          <button style={{ background: 'transparent', color: eINK, border: `1px solid ${eLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: eFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>PRÉVISUALISER</button>
          <button style={{ background: eACCENT, color: eBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: eFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>ENREGISTRER & PUBLIER</button>
        </>
      )}
    >
      <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr', gap: 22 }}>
        {/* Left — form */}
        <div>
          {/* Texts */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18, marginBottom: 14 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>TEXTES</div>
            <FormField label="TITRE DE LA BANNIÈRE" value="Télécharge l'app COTA" />
            <FormField label="SOUS-TITRE" value="Coupon IA quotidien · 9 critères · 09h30" />
            <FormField label="BOUTON PRINCIPAL" value="Télécharger gratuitement" />
          </div>

          {/* Links */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18, marginBottom: 14 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>LIENS TÉLÉCHARGEMENT</div>
            <FormField label="APP STORE URL"   value="https://apps.apple.com/cota" />
            <FormField label="GOOGLE PLAY URL" value="https://play.google.com/store/apps/cota" />
          </div>

          {/* Display surfaces */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18, marginBottom: 14 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>AFFICHAGE</div>
            {[
              ['Afficher sur la landing page',         true],
              ['Afficher sur le web mobile (bannière)', true],
              ['Afficher dans l\'app (notification)',  false],
            ].map(([l, on]) => (
              <div key={l} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 0', borderBottom: `1px solid ${eLINE}` }}>
                <span style={{ width: 18, height: 18, borderRadius: 4, background: on ? eACCENT : eBG3, border: `1px solid ${on ? eACCENT : eLINE2}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  {on && <svg width="11" height="11" viewBox="0 0 11 11"><path d="M1.5 5.5 L4 8 L9 3" stroke={eBG} strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>}
                </span>
                <span style={{ fontSize: 12, color: eINK }}>{l}</span>
              </div>
            ))}
          </div>

          {/* Style */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>STYLE</div>
            {[
              ['Fond accent jaune (recommandé)', true,  eACCENT],
              ['Fond sombre BG2',                false, eBG2],
              ['Fond transparent glassmorphism',  false, 'rgba(20,23,28,0.5)'],
            ].map(([l, on, c]) => (
              <div key={l} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 0', borderBottom: `1px solid ${eLINE}` }}>
                <span style={{ width: 16, height: 16, borderRadius: 8, border: `2px solid ${on ? eACCENT : eLINE2}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  {on && <span style={{ width: 8, height: 8, borderRadius: 4, background: eACCENT }} />}
                </span>
                <span style={{ fontSize: 12, color: eINK }}>{l}</span>
                <div style={{ marginLeft: 'auto', width: 24, height: 24, borderRadius: 6, background: c, border: `1px solid ${eLINE2}` }} />
              </div>
            ))}
          </div>
        </div>

        {/* Right — preview + analytics */}
        <div>
          {/* Preview */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18, marginBottom: 14 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eDIM, letterSpacing: '0.18em', marginBottom: 14 }}>APERÇU LIVE</div>
            {/* Banner preview — accent variant */}
            <div style={{ background: eACCENT, color: eBG, borderRadius: 12, padding: '18px 18px', display: 'flex', alignItems: 'center', gap: 14, marginBottom: 12 }}>
              <AppIcon size={48} />
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: eFONT.title, fontSize: 14, letterSpacing: '-0.02em' }}>Télécharge l'app COTA</div>
                <div style={{ fontFamily: eFONT.mono, fontSize: 9, color: 'rgba(11,13,16,0.7)', letterSpacing: '0.08em', marginTop: 3 }}>Coupon IA quotidien · 9h30</div>
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
                <button style={{ background: eBG, color: eACCENT, border: 'none', padding: '5px 10px', borderRadius: 5, fontFamily: eFONT.title, fontSize: 8, letterSpacing: '0.08em' }}>↓ APP STORE</button>
                <button style={{ background: eBG, color: eACCENT, border: 'none', padding: '5px 10px', borderRadius: 5, fontFamily: eFONT.title, fontSize: 8, letterSpacing: '0.08em' }}>↓ PLAY STORE</button>
              </div>
            </div>
            <div style={{ fontFamily: eFONT.mono, fontSize: 9, color: eDIM2, letterSpacing: '0.12em', textAlign: 'center' }}>BANNIÈRE ACCENT · MOBILE</div>
          </div>

          {/* QR */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18, marginBottom: 14, display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{ padding: 8, background: '#fff', borderRadius: 6 }}>
              {window.QRCode ? <window.QRCode size={100} /> : <div style={{ width: 100, height: 100 }}/>}
            </div>
            <div>
              <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em' }}>QR CODE AUTO</div>
              <div style={{ fontSize: 12, color: eINK, marginTop: 6, lineHeight: 1.4 }}>Pointe vers la landing.<br/>Régénéré à chaque modification.</div>
            </div>
          </div>

          {/* Analytics */}
          <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 18 }}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 14 }}>ANALYTICS · 30J</div>
            {[
              ['App Store',    '847', dWIN],
              ['Google Play',  '612', '#3b82f6'],
              ['Scans QR',     '203', eACCENT],
            ].map(([l, n, c]) => (
              <div key={l} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 0', borderBottom: `1px solid ${eLINE}` }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <span style={{ width: 8, height: 8, borderRadius: 4, background: c }} />
                  <span style={{ fontSize: 12, color: eINK }}>{l}</span>
                </div>
                <span style={{ fontFamily: eFONT.mono, fontSize: 14, color: c, fontWeight: 700 }}>{n}</span>
              </div>
            ))}
            <div style={{ marginTop: 12 }}>
              <Sparkline data={[5, 8, 6, 12, 10, 18, 14, 22, 18, 24, 28, 32, 30, 36, 42]} width={300} height={50} color={eACCENT} />
            </div>
          </div>
        </div>
      </div>
    </AdminFrame>
  );
}

// ── D8 · Compétitions ────────────────────────────────────────────────────────
function AdminLeagues() {
  return (
    <AdminFrame
      active="leagues"
      breadcrumb="COMPÉTITIONS"
      title="Compétitions"
      actions={<button style={{ background: eACCENT, color: eBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: eFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>+ AJOUTER COMPÉTITION</button>}
    >
      <AdminTable
        columns={[
          { label: '#',       w: '34px' },
          { label: 'NOM',     w: '1fr' },
          { label: 'PAYS',    w: '140px' },
          { label: 'TIER',    w: '90px',  align: 'center' },
          { label: 'MATCHS/J', w: '100px', align: 'right', mono: true },
          { label: 'CONFIANCE MOY.', w: '160px' },
          { label: 'ACTIF',   w: '70px', align: 'center' },
          { label: '',        w: '90px', align: 'right' },
        ]}
        rows={[
          ['1', <LeagueCell color="#001f3f" name="Champions League" />, 'Europe',     <TierBadge tier={1} />, '8',  <ConfBar v={84} />, <Toggle on={true} />, <RowActions />],
          ['2', <LeagueCell color="#37003c" name="Premier League" />,   'Angleterre', <TierBadge tier={1} />, '10', <ConfBar v={78} />, <Toggle on={true} />, <RowActions />],
          ['3', <LeagueCell color="#ee2737" name="La Liga" />,          'Espagne',    <TierBadge tier={1} />, '10', <ConfBar v={76} />, <Toggle on={true} />, <RowActions />],
          ['4', <LeagueCell color="#008fd7" name="Serie A" />,          'Italie',     <TierBadge tier={1} />, '10', <ConfBar v={72} />, <Toggle on={true} />, <RowActions />],
          ['5', <LeagueCell color="#d3010c" name="Bundesliga" />,       'Allemagne',  <TierBadge tier={1} />, '9',  <ConfBar v={75} />, <Toggle on={true} />, <RowActions />],
          ['6', <LeagueCell color="#003366" name="Ligue 1" />,          'France',     <TierBadge tier={1} />, '10', <ConfBar v={81} />, <Toggle on={true} />, <RowActions />],
          ['7', <LeagueCell color="#ff6900" name="Europa League" />,    'Europe',     <TierBadge tier={2} />, '6',  <ConfBar v={68} />, <Toggle on={true} />, <RowActions />],
          ['8', <LeagueCell color="#006400" name="Liga Portugal" />,    'Portugal',   <TierBadge tier={2} />, '5',  <ConfBar v={65} />, <Toggle on={false} />, <RowActions />],
          ['9', <LeagueCell color="#ff6f00" name="Eredivisie" />,       'Pays-Bas',   <TierBadge tier={2} />, '5',  <ConfBar v={62} />, <Toggle on={false} />, <RowActions />],
        ]}
      />
    </AdminFrame>
  );
}

function LeagueCell({ color, name }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
      <div style={{ width: 4, height: 24, background: color, borderRadius: 2 }} />
      <span style={{ fontSize: 13, color: eINK, fontWeight: 600 }}>{name}</span>
    </div>
  );
}
function TierBadge({ tier }) {
  const colors = { 1: eACCENT, 2: '#3b82f6', 3: eDIM, 4: eDIM2 };
  const c = colors[tier] || eDIM;
  return (
    <span style={{ display: 'inline-flex', padding: '3px 9px', borderRadius: 4, background: `${c}22`, color: c, fontFamily: eFONT.mono, fontSize: 10, fontWeight: 700, letterSpacing: '0.12em' }}>T{tier}</span>
  );
}

// ── D9 · Settings ────────────────────────────────────────────────────────────
function AdminSettings() {
  const Row = ({ label, value, monospace = true, action }) => (
    <div style={{ display: 'flex', alignItems: 'center', gap: 16, padding: '14px 0', borderBottom: `1px solid ${eLINE}` }}>
      <div style={{ width: 200, fontSize: 12, color: eINK, fontWeight: 500 }}>{label}</div>
      <div style={{ flex: 1, padding: '8px 12px', background: eBG, border: `1px solid ${eLINE2}`, borderRadius: 6, fontFamily: monospace ? eFONT.mono : eFONT.ui, fontSize: 12, color: eINK }}>{value}</div>
      {action}
    </div>
  );
  return (
    <AdminFrame
      active="settings"
      breadcrumb="PARAMÈTRES"
      title="Paramètres"
      actions={<button style={{ background: eACCENT, color: eBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: eFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>ENREGISTRER TOUT</button>}
    >
      {/* API */}
      <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 22, marginBottom: 18 }}>
        <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>API & INTÉGRATIONS</div>
        <Row label="API-Football Key"    value="••••••••••••••••" action={<><button style={btnSec()}>RÉVÉLER</button><button style={btnPri()}>TESTER</button></>} />
        <Row label="Sportradar Key"      value="••••••••••••••••" action={<><button style={btnSec()}>RÉVÉLER</button><button style={btnPri()}>TESTER</button></>} />
        <Row label="OpenWeatherMap Key"  value="••••••••••••••••" action={<><button style={btnSec()}>RÉVÉLER</button><button style={btnPri()}>TESTER</button></>} />
        <div style={{ display: 'flex', alignItems: 'center', gap: 16, padding: '14px 0' }}>
          <div style={{ width: 200, fontSize: 12, color: eINK, fontWeight: 500 }}>Quota API restant</div>
          <div style={{ flex: 1, display: 'flex', alignItems: 'center', gap: 12 }}>
            <span style={{ fontFamily: eFONT.mono, fontSize: 12, color: eINK }}>847 / 1 000</span>
            <div style={{ flex: 1, height: 6, background: eLINE2, borderRadius: 3, overflow: 'hidden' }}>
              <div style={{ height: '100%', width: '84%', background: eWIN }} />
            </div>
            <span style={{ fontFamily: eFONT.mono, fontSize: 11, color: eWIN, fontWeight: 700, width: 40 }}>84%</span>
          </div>
        </div>
      </div>

      {/* IA */}
      <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 22, marginBottom: 18 }}>
        <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>ALGORITHME IA</div>
        <Row label="Score minimum publication" value="50 pts" action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="Picks minimum coupon"      value="3"      action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="Picks maximum coupon"      value="5"      action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="Confiance minimum coupon"  value="65 %"   action={<button style={btnSec()}>MODIFIER</button>} />
      </div>

      {/* Paydunya */}
      <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 22, marginBottom: 18 }}>
        <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>PAIEMENTS · PAYDUNYA</div>
        <Row label="API Key Live"    value="••••••••••••••••" action={<button style={btnSec()}>RÉVÉLER</button>} />
        <Row label="API Key Test"    value="••••••••••••••••" action={<button style={btnSec()}>RÉVÉLER</button>} />
        <div style={{ display: 'flex', alignItems: 'center', gap: 16, padding: '14px 0', borderBottom: `1px solid ${eLINE}` }}>
          <div style={{ width: 200, fontSize: 12, color: eINK, fontWeight: 500 }}>Mode</div>
          <div style={{ flex: 1, display: 'flex', gap: 10 }}>
            <span style={{ padding: '6px 14px', borderRadius: 6, background: eBG, color: eDIM, border: `1px solid ${eLINE2}`, fontFamily: eFONT.mono, fontSize: 11, letterSpacing: '0.1em' }}>○ TEST</span>
            <span style={{ padding: '6px 14px', borderRadius: 6, background: eACCENT, color: eBG, fontFamily: eFONT.mono, fontSize: 11, letterSpacing: '0.1em', fontWeight: 700 }}>● LIVE</span>
          </div>
        </div>
        <Row label="Prix mensuel" value="4 990 FCFA"  action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="Prix annuel"  value="49 900 FCFA" action={<button style={btnSec()}>MODIFIER</button>} />
      </div>

      {/* Notifications */}
      <div style={{ background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 12, padding: 22 }}>
        <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 12 }}>NOTIFICATIONS</div>
        <Row label="FCM Server Key"     value="••••••••••••••••" action={<button style={btnSec()}>RÉVÉLER</button>} />
        <Row label="Heure coupon"       value="09:30"      action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="Heure rappel live"  value="30 min avant" action={<button style={btnSec()}>MODIFIER</button>} />
        <Row label="SMS OTP provider"   value="Termii"     action={<button style={btnSec()}>MODIFIER</button>} monospace={false} />
      </div>
    </AdminFrame>
  );
}

function btnSec() {
  return { background: 'transparent', color: eDIM, border: `1px solid ${eLINE2}`, padding: '6px 12px', borderRadius: 6, fontFamily: eFONT.mono, fontSize: 9, letterSpacing: '0.12em' };
}
function btnPri() {
  return { background: eACCENT, color: eBG, border: 'none', padding: '6px 12px', borderRadius: 6, fontFamily: eFONT.mono, fontSize: 9, letterSpacing: '0.12em', fontWeight: 700 };
}

// ─────────────────────────────────────────────────────────────────────────────
// SECTION E — Micro-interactions
// ─────────────────────────────────────────────────────────────────────────────

// Pick state rows (En attente / Live / Gagné / Perdu)
function PickStates() {
  return (
    <div style={{ width: '100%', height: '100%', padding: 32, background: eBG, color: eINK, fontFamily: eFONT.ui }}>
      <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 6 }}>ÉTATS DES PICKS</div>
      <div style={{ fontFamily: eFONT.title, fontSize: 22, letterSpacing: '-0.02em', marginBottom: 22 }}>4 états visuels</div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
        {/* WAIT */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px', background: eBG2, border: `1px solid ${eLINE}`, borderRadius: 10 }}>
          <span style={{ fontFamily: eFONT.mono, fontSize: 10, color: eDIM2, letterSpacing: '0.15em', width: 30 }}>01</span>
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontFamily: eFONT.title, fontSize: 15, color: eINK, letterSpacing: '-0.02em' }}>PSG – OM</span>
              <span style={{ fontFamily: eFONT.mono, fontSize: 10, color: eDIM, letterSpacing: '0.05em' }}>21:00</span>
            </div>
            <div style={{ fontSize: 12, color: eINK2, marginTop: 4 }}>Victoire PSG</div>
          </div>
          <span style={{ fontFamily: eFONT.mono, fontSize: 13, color: eINK, fontWeight: 700 }}>@1.65</span>
          <StatusBadge kind="wait" label="EN ATTENTE" />
        </div>

        {/* LIVE */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px', background: 'rgba(232,255,54,0.05)', border: `1px solid ${eACCENT}`, borderRadius: 10 }}>
          <span style={{ width: 8, height: 8, borderRadius: 4, background: eACCENT, animation: 'cota-live-pulse 1.4s ease-in-out infinite' }} />
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontFamily: eFONT.title, fontSize: 15, color: eINK, letterSpacing: '-0.02em' }}>PSG – OM</span>
              <span style={{ fontFamily: eFONT.mono, fontSize: 11, color: eACCENT, fontWeight: 700, letterSpacing: '0.1em' }}>LIVE · 67'</span>
            </div>
            <div style={{ fontSize: 12, color: eINK2, marginTop: 4 }}>Victoire PSG · <span style={{ fontFamily: eFONT.mono, color: eINK, fontWeight: 700 }}>SCORE 1–0</span></div>
          </div>
          <span style={{ fontFamily: eFONT.mono, fontSize: 13, color: eACCENT, fontWeight: 700 }}>@1.65</span>
          <StatusBadge kind="live" label="EN COURS" />
        </div>

        {/* WON */}
        <div style={{ position: 'relative', display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px', background: 'rgba(61,220,145,0.06)', border: `1px solid ${eWIN}`, borderRadius: 10 }}>
          <div style={{ position: 'absolute', left: 0, top: 0, bottom: 0, width: 4, background: eWIN, borderRadius: '10px 0 0 10px' }} />
          <span style={{ width: 22, height: 22, borderRadius: 11, background: `${eWIN}22`, color: eWIN, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="12" height="12" viewBox="0 0 12 12"><path d="M2 6 L5 9 L10 3" stroke={eWIN} strokeWidth="2.2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
          </span>
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontFamily: eFONT.title, fontSize: 15, color: eINK, letterSpacing: '-0.02em' }}>PSG – OM</span>
              <span style={{ fontFamily: eFONT.mono, fontSize: 10, color: eDIM, letterSpacing: '0.05em' }}>SCORE 2–1</span>
            </div>
            <div style={{ fontSize: 12, color: eINK2, marginTop: 4 }}>Victoire PSG · @1.65</div>
          </div>
          <span style={{ fontFamily: eFONT.title, fontSize: 16, color: eWIN, letterSpacing: '-0.02em' }}>+€16.50</span>
          <StatusBadge kind="won" label="✓ GAGNÉ" />
        </div>

        {/* LOST */}
        <div style={{ position: 'relative', display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px', background: 'rgba(255,91,58,0.06)', border: `1px solid ${eLOSS}`, borderRadius: 10 }}>
          <div style={{ position: 'absolute', left: 0, top: 0, bottom: 0, width: 4, background: eLOSS, borderRadius: '10px 0 0 10px' }} />
          <span style={{ width: 22, height: 22, borderRadius: 11, background: `${eLOSS}22`, color: eLOSS, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="11" height="11" viewBox="0 0 11 11"><path d="M2 2 L9 9 M9 2 L2 9" stroke={eLOSS} strokeWidth="2" fill="none" strokeLinecap="round"/></svg>
          </span>
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontFamily: eFONT.title, fontSize: 15, color: eINK, letterSpacing: '-0.02em' }}>OL – Lille</span>
              <span style={{ fontFamily: eFONT.mono, fontSize: 10, color: eDIM, letterSpacing: '0.05em' }}>SCORE 1–1</span>
            </div>
            <div style={{ fontSize: 12, color: eINK2, marginTop: 4 }}>+2.5 buts attendus · @1.92</div>
          </div>
          <span style={{ fontFamily: eFONT.title, fontSize: 16, color: eLOSS, letterSpacing: '-0.02em' }}>-€10</span>
          <StatusBadge kind="lost" label="✗ PERDU" />
        </div>
      </div>
    </div>
  );
}

// Download banner variants (3)
function BannerVariants() {
  return (
    <div style={{ width: '100%', height: '100%', padding: 28, background: eBG, color: eINK, fontFamily: eFONT.ui }}>
      <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 6 }}>BANNIÈRES TÉLÉCHARGEMENT · 3 VARIANTES</div>
      <div style={{ fontFamily: eFONT.title, fontSize: 20, letterSpacing: '-0.02em', marginBottom: 22 }}>Format mobile 402×90</div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        {[1, 2, 3].map(v => (
          <div key={v}>
            <div style={{ fontFamily: eFONT.mono, fontSize: 9, color: eDIM, letterSpacing: '0.15em', marginBottom: 6 }}>VARIANTE 0{v}</div>
            <div style={{ width: 402, borderRadius: 10, overflow: 'hidden' }}>
              <DownloadBanner variant={v} />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// Toast notifications
function ToastVariants() {
  const toasts = [
    { icon: '✓', label: 'Coupon publié avec succès', sub: '3 picks envoyés à 12 472 utilisateurs', bg: eWIN,         fg: eBG },
    { icon: '⚠', label: 'Quota API à 90%',           sub: 'Restant : 100 / 1 000 req. avant 00:00', bg: '#f59e0b',  fg: eBG },
    { icon: '✗', label: 'Erreur paiement',            sub: 'Transaction Wave rejetée · ID #4521',    bg: eLOSS,      fg: eBG },
    { icon: '★', label: 'Nouveau Premium',            sub: 'Karim B. vient de s\'abonner · MENSUEL', bg: eACCENT,   fg: eBG },
  ];

  return (
    <div style={{ width: '100%', height: '100%', padding: 28, background: eBG2, color: eINK, fontFamily: eFONT.ui }}>
      <div style={{ fontFamily: eFONT.mono, fontSize: 10, color: eACCENT, letterSpacing: '0.18em', marginBottom: 6 }}>TOASTS NOTIFICATIONS</div>
      <div style={{ fontFamily: eFONT.title, fontSize: 20, letterSpacing: '-0.02em', marginBottom: 22 }}>4 types système</div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 12, maxWidth: 380 }}>
        {toasts.map(t => (
          <div key={t.label} style={{
            padding: '12px 14px', background: t.bg, color: t.fg,
            borderRadius: 10, display: 'flex', alignItems: 'center', gap: 12,
            boxShadow: `0 12px 30px ${t.bg}40`,
          }}>
            <div style={{ width: 30, height: 30, borderRadius: 15, background: 'rgba(11,13,16,0.18)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: eFONT.title, fontSize: 14 }}>{t.icon}</div>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 13, fontWeight: 700 }}>{t.label}</div>
              <div style={{ fontFamily: eFONT.mono, fontSize: 9, opacity: 0.7, letterSpacing: '0.05em', marginTop: 2 }}>{t.sub}</div>
            </div>
            <span style={{ fontFamily: eFONT.title, fontSize: 14, opacity: 0.5 }}>×</span>
          </div>
        ))}
      </div>
    </div>
  );
}

Object.assign(window, { AdminAppDownload, AdminLeagues, AdminSettings, PickStates, BannerVariants, ToastVariants });
