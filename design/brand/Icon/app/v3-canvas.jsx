// COTA V3 — design canvas mount.

function V3Phone({ children }) {
  return (
    <IOSDevice width={402} height={874} dark={true}>
      {children}
    </IOSDevice>
  );
}

function App() {
  return (
    <DesignCanvas defaultLayout="grid">

      <DCSection id="v3-mobile" title="Mobile · sobre" subtitle="DAZN × The Athletic dark — moins de jaune, typo plus light, plus de respiration. Pas de gradient de match, pas de hero plein écran.">
        <DCArtboard id="v3-profile"  label="Profil détaillé"      width={402} height={874}><V3Phone><V3Profile /></V3Phone></DCArtboard>
        <DCArtboard id="v3-search"   label="Recherche"            width={402} height={874}><V3Phone><V3Search /></V3Phone></DCArtboard>
        <DCArtboard id="v3-team"     label="Page Équipe · PSG"    width={402} height={874}><V3Phone><V3Team /></V3Phone></DCArtboard>
        <DCArtboard id="v3-league"   label="Page Ligue · Ligue 1" width={402} height={874}><V3Phone><V3League /></V3Phone></DCArtboard>
        <DCArtboard id="v3-wallet"   label="Wallet"               width={402} height={874}><V3Phone><V3Wallet /></V3Phone></DCArtboard>
      </DCSection>

      <DCSection id="v3-web" title="Web · éditorial" subtitle="Pages utilitaires sobres, beaucoup de blanc, typo régulière. Pas de héro pleine largeur.">
        <DCArtboard id="v3-help"     label="Centre d'aide"     width={1200} height={1240}><V3HelpCenter /></DCArtboard>
        <DCArtboard id="v3-tipster"  label="Profil tipster public" width={1200} height={1080}><V3TipsterPublic /></DCArtboard>
      </DCSection>

    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
