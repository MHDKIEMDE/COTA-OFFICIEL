// COTA V5 — design canvas mount.

function App() {
  return (
    <DesignCanvas defaultLayout="grid">
      <DCSection id="v5-admin" title="Admin · MaxLand revisité COTA" subtitle="Sidebar dark gardée, main area cream, titres en serif (Fraunces), KPI cards avec languette colorée, tableaux blancs aérés.">
        <DCArtboard id="v5-overview" label="Dashboard / Vue d'ensemble" width={1440} height={1500}><V5Overview /></DCArtboard>
        <DCArtboard id="v5-coupons"  label="Mes coupons (liste)"        width={1440} height={950}><V5Properties /></DCArtboard>
        <DCArtboard id="v5-add"      label="Ajouter un coupon"           width={1440} height={1100}><V5AddCoupon /></DCArtboard>
        <DCArtboard id="v5-pricing"  label="Plans tarifaires"            width={1440} height={870}><V5Pricing /></DCArtboard>
        <DCArtboard id="v5-profile"  label="Profil utilisateur"          width={1440} height={1100}><V5Profile /></DCArtboard>
      </DCSection>

      <DCSection id="v5-web" title="Landing · sobre" subtitle="Page d'accueil cream, hero en serif éditorial, 9 critères en grille blanche, CTA dark.">
        <DCArtboard id="v5-landing" label="Landing cota.app" width={1440} height={2400}><V5Landing /></DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
