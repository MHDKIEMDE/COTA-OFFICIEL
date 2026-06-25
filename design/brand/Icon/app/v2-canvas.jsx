// COTA V2 — design canvas mount.

function Phone({ children }) {
  return (
    <IOSDevice width={402} height={874} dark={true}>
      {children}
    </IOSDevice>
  );
}

function AnimBox({ children, height = 500 }) {
  return (
    <div style={{ width: 600, height, borderRadius: 16, overflow: 'hidden', position: 'relative' }}>
      {children}
    </div>
  );
}

function App() {
  return (
    <DesignCanvas defaultLayout="grid">

      {/* SECTION A — POST-ONBOARDING */}
      <DCSection id="onboarding-extra" title="A · Onboarding complet" subtitle="8 écrans dans l'ordre : hero, méthode 9 critères, rituel notification, ligues, niveau de risque, compte, OTP, bookmaker.">
        <DCArtboard id="ob-01" label="01 · Hero"        width={402} height={874}><Phone><OnboardHero /></Phone></DCArtboard>
        <DCArtboard id="ob-02" label="02 · 9 critères"  width={402} height={874}><Phone><OnboardCriteria /></Phone></DCArtboard>
        <DCArtboard id="ob-03" label="03 · Rituel 9h30" width={402} height={874}><Phone><OnboardNotif /></Phone></DCArtboard>
        <DCArtboard id="ob-04" label="04 · Ligues"      width={402} height={874}><Phone><OnboardLeagues /></Phone></DCArtboard>
        <DCArtboard id="ob-05" label="05 · Niveau"      width={402} height={874}><Phone><OnboardRisk /></Phone></DCArtboard>
        <DCArtboard id="ob-06" label="06 · Compte"      width={402} height={874}><Phone><OnboardAccount /></Phone></DCArtboard>
        <DCArtboard id="ob-07" label="07 · OTP"         width={402} height={874}><Phone><OnboardOTP /></Phone></DCArtboard>
        <DCArtboard id="ob-08" label="08 · Bookmaker"   width={402} height={874}><Phone><OnboardBookmaker /></Phone></DCArtboard>
      </DCSection>

      {/* SECTION B — APP MOBILE (nouveaux écrans) */}
      <DCSection id="app-extras" title="B · App mobile · nouveaux écrans" subtitle="Détail match Flashscore, historique, détail coupon gagné, Premium, Bookmakers, Paramètres.">
        <DCArtboard id="b0-match"  label="B0 · Match (Flashscore)" width={402} height={874}><Phone><ScreenMatchFlash /></Phone></DCArtboard>
        <DCArtboard id="b1-hist"   label="B1 · Historique"          width={402} height={874}><Phone><ScreenHistory /></Phone></DCArtboard>
        <DCArtboard id="b2-detail" label="B2 · Coupon gagné"        width={402} height={874}><Phone><ScreenCouponDetail /></Phone></DCArtboard>
        <DCArtboard id="b3-prem"   label="B3 · Premium"             width={402} height={874}><Phone><ScreenPremium /></Phone></DCArtboard>
        <DCArtboard id="b4-book"   label="B4 · Bookmakers"          width={402} height={874}><Phone><ScreenBookmakers /></Phone></DCArtboard>
        <DCArtboard id="b5-set"    label="B5 · Paramètres"          width={402} height={874}><Phone><ScreenSettings /></Phone></DCArtboard>
      </DCSection>

      {/* SECTION C — WEB RESPONSIVE */}
      <DCSection id="web" title="C · Web responsive" subtitle="Mobile = identique à l'app + URL bar + bannière téléchargement. Tablet 2-colonnes. Desktop landing DAZN.">
        <DCArtboard id="web-m"  label="C1 · Web Mobile · cota.app" width={402}  height={874}><Phone><WebMobileHome /></Phone></DCArtboard>
        <DCArtboard id="web-t"  label="C2 · Web Tablet"             width={900}  height={1100}><WebTablet /></DCArtboard>
        <DCArtboard id="web-d"  label="C3 · Landing Desktop · DAZN" width={1280} height={3600}><WebDesktopLanding /></DCArtboard>
      </DCSection>

      {/* SECTION D — ADMIN */}
      <DCSection id="admin" title="D · Dashboard admin" subtitle="9 pages style MaxLand adapté COTA. Sidebar sombre, KPI cards icônes colorées, tableaux propres.">
        <DCArtboard id="d1" label="D1 · Vue d'ensemble"   width={1280} height={1100}><AdminOverview /></DCArtboard>
        <DCArtboard id="d2" label="D2 · Utilisateurs"     width={1280} height={1100}><AdminUsers /></DCArtboard>
        <DCArtboard id="d3" label="D3 · Prédictions"      width={1280} height={1000}><AdminPredictions /></DCArtboard>
        <DCArtboard id="d4" label="D4 · Coupons"          width={1280} height={1100}><AdminCoupons /></DCArtboard>
        <DCArtboard id="d5" label="D5 · Abonnements"      width={1280} height={1100}><AdminSubs /></DCArtboard>
        <DCArtboard id="d6" label="D6 · Bookmakers"       width={1280} height={900}><AdminBookmakers /></DCArtboard>
        <DCArtboard id="d7" label="D7 · App / Download"   width={1280} height={1100}><AdminAppDownload /></DCArtboard>
        <DCArtboard id="d8" label="D8 · Compétitions"     width={1280} height={1000}><AdminLeagues /></DCArtboard>
        <DCArtboard id="d9" label="D9 · Paramètres"       width={1280} height={1400}><AdminSettings /></DCArtboard>
      </DCSection>

      {/* SECTION E — MICRO-INTERACTIONS */}
      <DCSection id="states" title="E · États & micro-interactions" subtitle="États des picks (4), bannières téléchargement (3 variantes), toasts notifications (4).">
        <DCArtboard id="states-1" label="États des picks"      width={900} height={520}><PickStates /></DCArtboard>
        <DCArtboard id="states-2" label="Bannières téléchargement" width={500} height={520}><BannerVariants /></DCArtboard>
        <DCArtboard id="states-3" label="Toasts notifications" width={500} height={520}><ToastVariants /></DCArtboard>
      </DCSection>

      {/* SECTION F — ANIMATIONS */}
      <DCSection id="animations" title="F · Animations" subtitle="4 animations en boucle — splash IA, score de confiance, coupon validé, cote style Netflix (lettres qui chutent).">
        <DCArtboard id="anim-1" label="1 · Splash · analyse IA"     width={600} height={500}><AnimBox><AnimSplash /></AnimBox></DCArtboard>
        <DCArtboard id="anim-2" label="2 · Score de confiance"      width={600} height={500}><AnimBox><AnimConfidenceReveal /></AnimBox></DCArtboard>
        <DCArtboard id="anim-3" label="3 · Coupon validé"           width={600} height={500}><AnimBox><AnimCouponValid /></AnimBox></DCArtboard>
        <DCArtboard id="anim-4" label="4 · Cote style Netflix"      width={600} height={500}><AnimBox><AnimNetflixOdds /></AnimBox></DCArtboard>
      </DCSection>

    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
