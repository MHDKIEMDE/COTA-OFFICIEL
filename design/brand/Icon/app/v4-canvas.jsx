// COTA V4 — design canvas mount.

function Phone4({ children }) {
  return (
    <IOSDevice width={402} height={874} dark={false}>
      {children}
    </IOSDevice>
  );
}

function App() {
  return (
    <DesignCanvas defaultLayout="grid">
      <DCSection id="v4-screens" title="2030 · INVERSE" subtitle="Palette inversée — cream primaire, dark accent. AI orb, mesh ambiants, gauges iridescentes, ticker neural. La cote n'est plus la même app.">
        <DCArtboard id="v4-welcome" label="Welcome 2030"     width={402} height={874}><Phone4><V4Welcome /></Phone4></DCArtboard>
        <DCArtboard id="v4-home"    label="Home futurist"    width={402} height={874}><Phone4><V4Home /></Phone4></DCArtboard>
        <DCArtboard id="v4-match"   label="Match holographique" width={402} height={874}><Phone4><V4Match /></Phone4></DCArtboard>
        <DCArtboard id="v4-valid"   label="Coupon validé"    width={402} height={874}><Phone4><V4Validated /></Phone4></DCArtboard>
      </DCSection>

      <DCSection id="v4-anim" title="Animation signature" subtitle="Le boot 2030 — AI Orb pop, ASCII des 9 critères en pluie, ticker neural, @4.55 stamp, greeting iridescent. Loop 5.8s.">
        <DCArtboard id="v4-boot" label="Boot 2030" width={600} height={520}>
          <div style={{ width: 600, height: 520, borderRadius: 18, overflow: 'hidden' }}><V4AnimBoot /></div>
        </DCArtboard>
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
