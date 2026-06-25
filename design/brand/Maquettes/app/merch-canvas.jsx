// COTA — merch canvas mount.

function App() {
  return (
    <DesignCanvas defaultLayout="grid">

      <DCSection id="tees" title="01 · T-shirts" subtitle="4 placements de l'icône, sans texte. QR code discret au hem dos.">
        <DCArtboard id="tee-1" label="01 · CHEST petite"   width={720} height={560}><TeeSmallChest /></DCArtboard>
        <DCArtboard id="tee-2" label="02 · CENTERED"       width={720} height={560}><TeeCenter /></DCArtboard>
        <DCArtboard id="tee-3" label="03 · BACK grosse"    width={720} height={560}><TeeBackBig /></DCArtboard>
        <DCArtboard id="tee-4" label="04 · CREAM"          width={720} height={560}><TeeCream /></DCArtboard>
      </DCSection>

      <DCSection id="apparel" title="02 · Sweat, casquette, totebag, mug" subtitle="Icône uniquement. QR au panneau latéral / hem / coin selon la pièce.">
        <DCArtboard id="hoodie" label="A · HOODIE" width={720} height={560}><HoodieMain /></DCArtboard>
        <DCArtboard id="cap"    label="B · CAP"    width={400} height={320}><CapMain /></DCArtboard>
        <DCArtboard id="tote"   label="C · TOTE"   width={420} height={480}><ToteMain /></DCArtboard>
        <DCArtboard id="mug"    label="H · MUG"    width={420} height={400}><Mug /></DCArtboard>
      </DCSection>

      <DCSection id="stickers" title="03 · Stickers" subtitle="6 pièces die-cut. Cinq variations d'icône en taille/forme + un QR sticker.">
        <DCArtboard id="stickers-1" label="Sticker pack · 6 pièces" width={1100} height={600}><StickersPack /></DCArtboard>
      </DCSection>

      <DCSection id="paper" title="04 · Papier et pass" subtitle="Carte de visite (dos = QR plein cadre), badge presse avec QR au coin.">
        <DCArtboard id="biz"   label="D · CARTE DE VISITE" width={920} height={500}><BizCards /></DCArtboard>
        <DCArtboard id="press" label="E · PRESS PASS"      width={520} height={620}><PressPass /></DCArtboard>
      </DCSection>

      <DCSection id="digital" title="05 · Digital" subtitle="Fond d'écran iPhone (icône + QR), social cover (icône + QR coin).">
        <DCArtboard id="wall"   label="F · WALLPAPER iPhone" width={320} height={560}><PhoneWallpaper /></DCArtboard>
        <DCArtboard id="social" label="G · SOCIAL COVER"     width={920} height={400}><SocialCover /></DCArtboard>
      </DCSection>

    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
