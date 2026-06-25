// COTA Combiné — V2 mobile app (sombre, énergique) + V5 admin/web (cream, sobre).
// Deux univers visuels, un seul canvas.

function PhoneCombo({ children }) {
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

      {/* === MOBILE (V2 — sombre) === */}

      <DCSection id="onb" title="A · Onboarding · mobile sombre (V2)" subtitle="8 écrans dans l'ordre : hero, méthode 9 critères, rituel notification, ligues, niveau de risque, compte, OTP, bookmaker.">
        <DCArtboard id="ob-01" label="01 · Hero"        width={402} height={874}><PhoneCombo><OnboardHero /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-02" label="02 · 9 critères"  width={402} height={874}><PhoneCombo><OnboardCriteria /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-03" label="03 · Rituel 9h30" width={402} height={874}><PhoneCombo><OnboardNotif /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-04" label="04 · Ligues"      width={402} height={874}><PhoneCombo><OnboardLeagues /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-05" label="05 · Niveau"      width={402} height={874}><PhoneCombo><OnboardRisk /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-06" label="06 · Compte"      width={402} height={874}><PhoneCombo><OnboardAccount /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-07" label="07 · OTP"         width={402} height={874}><PhoneCombo><OnboardOTP /></PhoneCombo></DCArtboard>
        <DCArtboard id="ob-08" label="08 · Bookmaker"   width={402} height={874}><PhoneCombo><OnboardBookmaker /></PhoneCombo></DCArtboard>
      </DCSection>

      <DCSection id="app" title="B · App mobile (V2)" subtitle="Home, détail match (Flashscore), historique, détail coupon, Premium, Bookmakers, paramètres, profil, notifs.">
        <DCArtboard id="home"     label="Home — Aujourd'hui"     width={402} height={874}><PhoneCombo><ScreenHome /></PhoneCombo></DCArtboard>
        <DCArtboard id="match"    label="Match · Flashscore"     width={402} height={874}><PhoneCombo><ScreenMatchFlash /></PhoneCombo></DCArtboard>
        <DCArtboard id="coupon"   label="Coupon du jour"          width={402} height={874}><PhoneCombo><ScreenCoupon /></PhoneCombo></DCArtboard>
        <DCArtboard id="history"  label="Historique"              width={402} height={874}><PhoneCombo><ScreenHistory /></PhoneCombo></DCArtboard>
        <DCArtboard id="detail"   label="Coupon gagné"            width={402} height={874}><PhoneCombo><ScreenCouponDetail /></PhoneCombo></DCArtboard>
        <DCArtboard id="premium"  label="Premium"                 width={402} height={874}><PhoneCombo><ScreenPremium /></PhoneCombo></DCArtboard>
        <DCArtboard id="books"    label="Bookmakers"              width={402} height={874}><PhoneCombo><ScreenBookmakers /></PhoneCombo></DCArtboard>
        <DCArtboard id="notif"    label="Notifications"           width={402} height={874}><PhoneCombo><ScreenNotif /></PhoneCombo></DCArtboard>
        <DCArtboard id="profile"  label="Profil mobile"           width={402} height={874}><PhoneCombo><ScreenProfile /></PhoneCombo></DCArtboard>
        <DCArtboard id="settings" label="Paramètres"              width={402} height={874}><PhoneCombo><ScreenSettings /></PhoneCombo></DCArtboard>
      </DCSection>

      <DCSection id="anims" title="C · Animations" subtitle="6 animations en boucle — splash IA, score de confiance, coupon validé, cote style Netflix, et le ballon Coupe du Monde 2026 (2 variantes) + son écran splash mobile.">
        <DCArtboard id="anim-1" label="1 · Splash IA"           width={600} height={500}><AnimBox><AnimSplash /></AnimBox></DCArtboard>
        <DCArtboard id="anim-2" label="2 · Score de confiance"  width={600} height={500}><AnimBox><AnimConfidenceReveal /></AnimBox></DCArtboard>
        <DCArtboard id="anim-3" label="3 · Coupon validé"       width={600} height={500}><AnimBox><AnimCouponValid /></AnimBox></DCArtboard>
        <DCArtboard id="anim-4" label="4 · Cote style Netflix"  width={600} height={500}><AnimBox><AnimNetflixOdds /></AnimBox></DCArtboard>
        <DCArtboard id="anim-wc-a" label="5 · Mondial · le ballon roule"  width={600} height={500}><AnimBox><AnimWorldCupRoll /></AnimBox></DCArtboard>
        <DCArtboard id="anim-wc-b" label="6 · Mondial · le ballon tourne" width={600} height={500}><AnimBox><AnimWorldCupSpin /></AnimBox></DCArtboard>
        <DCArtboard id="anim-wc-splash" label="Splash mobile · Mode Coupe du Monde" width={402} height={874}><PhoneCombo><WCSplashMobile /></PhoneCombo></DCArtboard>
      </DCSection>

      {/* === ADMIN + WEB (V5 — cream / serif / sobre) === */}

      <DCSection id="admin" title="D · Dashboard admin (V5 · MaxLand)" subtitle="Univers visuel distinct de l'app — sidebar dark, main area cream, titres en serif Fraunces, KPI cards à languette colorée, tableaux blancs aérés.">
        <DCArtboard id="v5-overview" label="Dashboard / Vue d'ensemble" width={1440} height={1500}><V5Overview /></DCArtboard>
        <DCArtboard id="v5-coupons"  label="Mes coupons (liste)"        width={1440} height={950}><V5Properties /></DCArtboard>
        <DCArtboard id="v5-add"      label="Ajouter un coupon"           width={1440} height={1100}><V5AddCoupon /></DCArtboard>
        <DCArtboard id="v5-pricing"  label="Plans tarifaires"            width={1440} height={870}><V5Pricing /></DCArtboard>
        <DCArtboard id="v5-profile"  label="Profil admin"                width={1440} height={1100}><V5Profile /></DCArtboard>
      </DCSection>

      <DCSection id="web" title="E · Landing web (V5 · sobre)" subtitle="cota.app — hero éditorial en serif, mockup phone, méthode en 9 cards blanches, CTA dark.">
        <DCArtboard id="v5-landing" label="Landing cota.app" width={1440} height={2400}><V5Landing /></DCArtboard>
      </DCSection>

    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
