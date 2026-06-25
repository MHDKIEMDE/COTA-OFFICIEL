// COTA — design canvas mount, wires the full ecosystem.

const { BG: cBG, BG2: cBG2, BG3: cBG3, LINE: cLINE, LINE2: cLINE2, INK: cINK, INK2: cINK2, DIM: cDIM, DIM2: cDIM2, ACCENT: cACCENT, WIN: cWIN, font: cFONT } = window.COTA;

// ── Identity recap card (just visual, for the canvas top-left) ────────────────
function IdentityCard() {
  return (
    <div style={{ width: 720, padding: 32, background: cBG2, color: cINK, fontFamily: cFONT.ui }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 24 }}>
        <AppIcon size={72} />
        <div>
          <Wordmark size={36} underline={true} />
          <div style={{ fontFamily: cFONT.mono, fontSize: 11, color: cDIM, letterSpacing: '0.18em', marginTop: 10 }}>APP DE SÉLECTIONS FOOT · IA</div>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 18, marginTop: 18 }}>
        {/* type */}
        <div style={{ background: cBG, borderRadius: 10, padding: 14, border: `1px solid ${cLINE}` }}>
          <div style={{ fontFamily: cFONT.mono, fontSize: 9, color: cDIM, letterSpacing: '0.15em', marginBottom: 10 }}>TYPE</div>
          <div style={{ fontFamily: cFONT.title, fontSize: 22, lineHeight: 1.1, color: cINK }}>Archivo Black</div>
          <div style={{ fontFamily: cFONT.ui, fontSize: 13, color: cINK2, marginTop: 6, fontWeight: 500 }}>Space Grotesk 500/600/700</div>
          <div style={{ fontFamily: cFONT.mono, fontSize: 12, color: cACCENT, marginTop: 6, letterSpacing: '0.08em' }}>JetBrains Mono · @1.65</div>
        </div>

        {/* tokens */}
        <div style={{ background: cBG, borderRadius: 10, padding: 14, border: `1px solid ${cLINE}` }}>
          <div style={{ fontFamily: cFONT.mono, fontSize: 9, color: cDIM, letterSpacing: '0.15em', marginBottom: 10 }}>TOKENS</div>
          <div style={{ display: 'flex', gap: 6 }}>
            {[
              [cBG, '#0b0d10'],
              [cBG2, '#15181d'],
              [cINK, '#f4efe2'],
              [cACCENT, '#e8ff36'],
              [cWIN, '#3ddc91'],
              ['#ff5b3a', '#ff5b3a'],
            ].map(([c, h]) => (
              <div key={h} style={{ flex: 1 }}>
                <div style={{ height: 34, background: c, borderRadius: 6, border: `1px solid ${cLINE2}` }} />
                <div style={{ fontFamily: cFONT.mono, fontSize: 7, color: cDIM, letterSpacing: '0.05em', marginTop: 4 }}>{h}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* mini icon scale */}
      <div style={{ marginTop: 18, padding: 16, background: cBG, borderRadius: 10, border: `1px solid ${cLINE}` }}>
        <div style={{ fontFamily: cFONT.mono, fontSize: 9, color: cDIM, letterSpacing: '0.15em', marginBottom: 12 }}>ICÔNE — ÉCHELLE</div>
        <div style={{ display: 'flex', gap: 24, alignItems: 'flex-end' }}>
          {[16, 32, 60, 96, 180].map(s => (
            <div key={s} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
              <AppIcon size={s} />
              <div style={{ fontFamily: cFONT.mono, fontSize: 8, color: cDIM, letterSpacing: '0.08em' }}>{s}PX</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ── Phone artboard wrapper ────────────────────────────────────────────────────
function Phone({ children }) {
  return (
    <IOSDevice width={402} height={874} dark={true}>
      {children}
    </IOSDevice>
  );
}

// ── Animation artboard wrapper ────────────────────────────────────────────────
function AnimBox({ children, height = 500 }) {
  return (
    <div style={{ width: 600, height, borderRadius: 16, overflow: 'hidden', position: 'relative' }}>
      {children}
    </div>
  );
}

// ── App ───────────────────────────────────────────────────────────────────────
function App() {
  return (
    <DesignCanvas defaultLayout="grid">

      <DCSection id="brand" title="00 · Identité" subtitle="Icône, tokens, typographie. La base de tout.">
        <DCArtboard id="ident" label="Identité" width={720} height={460}>
          <IdentityCard />
        </DCArtboard>
      </DCSection>

      <DCSection id="onboarding" title="01 · Onboarding" subtitle="3 écrans : hero cinématique, les 9 critères, le rituel du carnet.">
        <DCArtboard id="ob-1" label="01 · Hero" width={402} height={874}><Phone><OnboardHero /></Phone></DCArtboard>
        <DCArtboard id="ob-2" label="02 · 9 critères" width={402} height={874}><Phone><OnboardCriteria /></Phone></DCArtboard>
        <DCArtboard id="ob-3" label="03 · Rituel" width={402} height={874}><Phone><OnboardNotif /></Phone></DCArtboard>
      </DCSection>

      <DCSection id="app" title="02 · App mobile" subtitle="Home avec hero carnet, détail match avec 9 critères, carnet combiné, notifs, profil.">
        <DCArtboard id="home"  label="Home — Aujourd'hui" width={402} height={874}><Phone><ScreenHome /></Phone></DCArtboard>
        <DCArtboard id="match" label="Match — 9 critères"  width={402} height={874}><Phone><ScreenMatch /></Phone></DCArtboard>
        <DCArtboard id="coupon"label="Carnet du jour"    width={402} height={874}><Phone><ScreenCoupon /></Phone></DCArtboard>
        <DCArtboard id="notif" label="Notifications"      width={402} height={874}><Phone><ScreenNotif /></Phone></DCArtboard>
        <DCArtboard id="prof"  label="Profil + Stats"     width={402} height={874}><Phone><ScreenProfile /></Phone></DCArtboard>
      </DCSection>

      <DCSection id="anim" title="03 · Animations" subtitle="Splash édition, révélation de confiance, validation du carnet. En boucle dans leurs cadres.">
        <DCArtboard id="anim-1" label="Splash · analyse IA"     width={600} height={500}><AnimBox><AnimSplash /></AnimBox></DCArtboard>
        <DCArtboard id="anim-2" label="Score de confiance"      width={600} height={500}><AnimBox><AnimConfidenceReveal /></AnimBox></DCArtboard>
        <DCArtboard id="anim-3" label="Carnet validé"           width={600} height={500}><AnimBox><AnimCouponValid /></AnimBox></DCArtboard>
      </DCSection>

      <DCSection id="web" title="04 · Web" subtitle="Landing cota.app + dashboard admin / modèle IA.">
        <DCArtboard id="land" label="Landing — cota.app"  width={1280} height={820}><Landing /></DCArtboard>
        <DCArtboard id="adm"  label="Dashboard admin"      width={1280} height={820}><AdminDashboard /></DCArtboard>
      </DCSection>

    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
