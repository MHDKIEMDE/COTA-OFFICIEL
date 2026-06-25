// COTA V6 — canvas mount.

function Phone6({ children }) {
  return (
    <IOSDevice width={402} height={874} dark={true}>
      {children}
    </IOSDevice>
  );
}

function App() {
  return (
    <DesignCanvas defaultLayout="grid">
      <DCSection id="v6-app" title="V6 · App DAZN-ifiée" subtitle="Mono retiré (sauf cotes), labels '01 — XXX' supprimés, tickers absents, ConfidenceRing remplacé par barres fines, critères en prose narrative, posters de matchs dominants. Une seule pulse — sur le match LIVE.">
        <DCArtboard id="v6-onb"     label="Onboarding hero"     width={402} height={874}><Phone6><V6OnboardHero /></Phone6></DCArtboard>
        <DCArtboard id="v6-home"    label="Home — Aujourd'hui"  width={402} height={874}><Phone6><V6Home /></Phone6></DCArtboard>
        <DCArtboard id="v6-match"   label="Match — Analyse"     width={402} height={874}><Phone6><V6Match /></Phone6></DCArtboard>
        <DCArtboard id="v6-coupon"  label="Coupon du jour"      width={402} height={874}><Phone6><V6Coupon /></Phone6></DCArtboard>
        <DCArtboard id="v6-profile" label="Profil"              width={402} height={874}><Phone6><V6Profile /></Phone6></DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
