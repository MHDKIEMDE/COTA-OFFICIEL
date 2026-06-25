// COTA — merchandising designs.
// Approche : ICÔNE UNIQUEMENT + QR code à un endroit discret. Aucun texte de copywriting.

const { BG: kBG, BG2: kBG2, BG3: kBG3, LINE: kLINE, LINE2: kLINE2, INK: kINK, INK2: kINK2, DIM: kDIM, DIM2: kDIM2, ACCENT: kACCENT, WIN: kWIN, LOSS: kLOSS, font: kFONT } = window.COTA;

const CREAM = '#e8e0c8';
const STAGE = '#1a1d22';          // photo studio backdrop for merch shots
const STAGE_LIGHT = '#272a30';

// ─────────────────────────────────────────────────────────────────────────────
// QR CODE — plausible-looking 25×25 module SVG. Deterministic random pattern
// so the picture stays stable across renders. Three finder patterns in corners,
// optional centered logo cutout (carries the COTA C).
// ─────────────────────────────────────────────────────────────────────────────
function QRCode({ size = 80, dark = '#0b0d10', light = '#ffffff', radius = 0, logo = true }) {
  const mods = 25;
  const cell = size / mods;
  const rects = [];

  // Deterministic data modules.
  for (let y = 0; y < mods; y++) {
    for (let x = 0; x < mods; x++) {
      // Skip finder regions (top-left, top-right, bottom-left 8x8 areas).
      if ((x < 8 && y < 8) || (x > mods - 9 && y < 8) || (x < 8 && y > mods - 9)) continue;
      // Skip center area where the logo will sit.
      if (logo && x >= mods/2 - 3 && x <= mods/2 + 2 && y >= mods/2 - 3 && y <= mods/2 + 2) continue;
      const h = ((x * 31 + y * 47 + x * y * 17 + (x + y) * 5) >>> 0) % 100;
      if (h < 48) rects.push(<rect key={`d-${x}-${y}`} x={x * cell} y={y * cell} width={cell} height={cell} fill={dark} />);
    }
  }

  const Finder = ({ x, y, k }) => (
    <g key={k}>
      <rect x={x * cell} y={y * cell} width={cell * 7} height={cell * 7} fill={dark} rx={cell * 1.2} />
      <rect x={(x + 1) * cell} y={(y + 1) * cell} width={cell * 5} height={cell * 5} fill={light} rx={cell * 0.6} />
      <rect x={(x + 2) * cell} y={(y + 2) * cell} width={cell * 3} height={cell * 3} fill={dark} rx={cell * 0.5} />
    </g>
  );

  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} style={{ borderRadius: radius, overflow: 'hidden' }}>
      <rect width={size} height={size} fill={light} rx={radius} />
      <g>
        {rects}
      </g>
      <Finder x={0} y={0} k="tl" />
      <Finder x={mods - 7} y={0} k="tr" />
      <Finder x={0} y={mods - 7} k="bl" />
      {logo && (
        <>
          <rect
            x={(mods / 2 - 3) * cell} y={(mods / 2 - 3) * cell}
            width={cell * 6} height={cell * 6}
            fill={light}
          />
          <rect
            x={(mods / 2 - 2.5) * cell} y={(mods / 2 - 2.5) * cell}
            width={cell * 5} height={cell * 5}
            fill={dark} rx={cell * 0.8}
          />
          <text
            x={size / 2} y={size / 2 + cell * 1.8}
            textAnchor="middle"
            fontFamily={kFONT.title} fontWeight="900"
            fontSize={cell * 4.5} fill={light}
            letterSpacing={-cell * 0.15}
          >C</text>
        </>
      )}
    </svg>
  );
}

// QR plate — a small "scan me" plate with quiet zone, fitted to dark/light bg.
function QRPlate({ size = 64, theme = 'light', radius = 4 }) {
  const dark = '#0b0d10';
  const light = theme === 'dark' ? '#ffffff' : theme === 'cream' ? '#e8e0c8' : '#ffffff';
  return (
    <div style={{
      padding: 6, background: light, borderRadius: radius,
      display: 'inline-flex',
      boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
    }}>
      <QRCode size={size} dark={dark} light={light} />
    </div>
  );
}

// Reusable "studio" — a soft seamless backdrop that gives each merch piece weight.
function Studio({ children, width = 720, height = 560, hangtag, bg = STAGE }) {
  return (
    <div style={{
      width, height, position: 'relative', overflow: 'hidden',
      background: `radial-gradient(ellipse at 50% 30%, ${STAGE_LIGHT} 0%, ${bg} 70%)`,
      fontFamily: kFONT.ui, color: kINK,
    }}>
      <div style={{ position: 'absolute', left: 0, right: 0, bottom: '32%', height: 1, background: 'rgba(255,255,255,0.04)' }} />
      <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        {children}
      </div>
      {hangtag && (
        <div style={{ position: 'absolute', top: 16, left: 16, padding: '8px 12px', background: 'rgba(0,0,0,0.35)', backdropFilter: 'blur(8px)', WebkitBackdropFilter: 'blur(8px)', border: `1px solid ${kLINE2}`, borderRadius: 6 }}>
            <div style={{ fontFamily: kFONT.mono, fontSize: 9, color: kACCENT, letterSpacing: '0.18em' }}>{hangtag.code}</div>
            <div style={{ fontFamily: kFONT.title, fontSize: 14, color: kINK, marginTop: 2, letterSpacing: '-0.02em' }}>{hangtag.title}</div>
            {hangtag.note && <div style={{ fontFamily: kFONT.mono, fontSize: 9, color: kDIM, letterSpacing: '0.1em', marginTop: 2 }}>{hangtag.note}</div>}
        </div>
      )}
    </div>
  );
}

// Subtle annotation arrow + dot for pointing at the QR placement on each piece.
function Callout({ x, y, label, dx = 60, dy = -20, side = 'right' }) {
  return (
    <div style={{ position: 'absolute', left: x, top: y, zIndex: 5 }}>
      <div style={{ position: 'absolute', left: 0, top: 0, width: 6, height: 6, borderRadius: 3, background: kACCENT, boxShadow: '0 0 0 4px rgba(232,255,54,0.18)' }} />
      <div style={{ position: 'absolute', left: side === 'right' ? dx : -dx - 100, top: dy, width: 110, fontFamily: kFONT.mono, fontSize: 8, color: kDIM, letterSpacing: '0.18em', textAlign: side === 'right' ? 'left' : 'right' }}>
        <div style={{
          position: 'absolute', top: 5,
          left: side === 'right' ? -dx + 3 : 100 + 3,
          width: dx - 6, height: 1, background: kLINE2,
        }} />
        {label}
      </div>
    </div>
  );
}

function FacingLabels() {
  return (
    <div style={{ position: 'absolute', bottom: 20, left: 0, right: 0, display: 'flex', justifyContent: 'center', gap: 320 }}>
      <div style={{ fontFamily: kFONT.mono, fontSize: 9, color: kDIM2, letterSpacing: '0.25em' }}>FRONT</div>
      <div style={{ fontFamily: kFONT.mono, fontSize: 9, color: kDIM2, letterSpacing: '0.25em' }}>BACK</div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// T-SHIRTS — 4 directions, icon-only + QR au hem dos
// ═════════════════════════════════════════════════════════════════════════════

// ── 01 · Chest petite ── icône discrète sur le cœur, QR au care-label intérieur.
function TeeSmallChest() {
  return (
    <Studio hangtag={{ code: '01 · CHEST', title: 'Icône petite, cœur gauche', note: 'noir · 180g cot. bio' }}>
      <div style={{ display: 'flex', gap: 32 }}>
        <Tee color={kBG} view="front">
          <div style={{ position: 'absolute', top: 100, left: 70 }}>
            <AppIcon size={26} />
          </div>
        </Tee>
        <Tee color={kBG} view="back">
          <div style={{ position: 'absolute', bottom: 60, right: 60 }}>
            <QRPlate size={48} />
          </div>
        </Tee>
      </div>
      <Callout x={156} y={222} label="ICÔNE · 26mm" side="left" dx={70} dy={-12} />
      <Callout x={612} y={336} label="QR · 48mm" side="right" dx={70} dy={-12} />
      <FacingLabels />
    </Studio>
  );
}

// ── 02 · Centered ── icône moyenne centrée poitrine.
function TeeCenter() {
  return (
    <Studio hangtag={{ code: '02 · CENTERED', title: 'Icône moyenne centrée' }}>
      <div style={{ display: 'flex', gap: 32 }}>
        <Tee color={kBG} view="front">
          <div style={{ position: 'absolute', top: 110, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
            <AppIcon size={64} />
          </div>
        </Tee>
        <Tee color={kBG} view="back">
          <div style={{ position: 'absolute', bottom: 60, right: 60 }}>
            <QRPlate size={48} />
          </div>
        </Tee>
      </div>
      <FacingLabels />
    </Studio>
  );
}

// ── 03 · Back-only ── icône grosse dans le dos, devant nu.
function TeeBackBig() {
  return (
    <Studio hangtag={{ code: '03 · BACK', title: 'Icône grosse dos', note: 'devant nu' }}>
      <div style={{ display: 'flex', gap: 32 }}>
        <Tee color={kBG} view="front" />
        <Tee color={kBG} view="back">
          <div style={{ position: 'absolute', top: 100, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
            <AppIcon size={140} />
          </div>
          <div style={{ position: 'absolute', bottom: 50, left: '50%', transform: 'translateX(-50%)' }}>
            <QRPlate size={42} />
          </div>
        </Tee>
      </div>
      <FacingLabels />
    </Studio>
  );
}

// ── 04 · Cream ── tee crème, icône cœur gauche, QR hem.
function TeeCream() {
  return (
    <Studio hangtag={{ code: '04 · CREAM', title: 'Tee crème, icône cœur', note: 'ton sur ton noir' }}>
      <div style={{ display: 'flex', gap: 32 }}>
        <Tee color={CREAM} view="front">
          <div style={{ position: 'absolute', top: 100, left: 70 }}>
            <AppIcon size={26} />
          </div>
        </Tee>
        <Tee color={CREAM} view="back">
          <div style={{ position: 'absolute', bottom: 60, right: 60 }}>
            <QRPlate size={48} theme="cream" />
          </div>
        </Tee>
      </div>
      <FacingLabels />
    </Studio>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// HOODIE
// ═════════════════════════════════════════════════════════════════════════════

function HoodieMain() {
  return (
    <Studio hangtag={{ code: 'A · HOODIE', title: 'Sweat à capuche', note: '380g coton lourd' }} width={720} height={560}>
      <div style={{ display: 'flex', gap: 28 }}>
        <Hoodie color={kBG} view="front">
          <div style={{ position: 'absolute', top: 130, left: 70 }}>
            <AppIcon size={32} />
          </div>
        </Hoodie>
        <Hoodie color={kBG} view="back">
          <div style={{ position: 'absolute', top: 140, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
            <AppIcon size={110} />
          </div>
          <div style={{ position: 'absolute', bottom: 50, left: '50%', transform: 'translateX(-50%)' }}>
            <QRPlate size={42} />
          </div>
        </Hoodie>
      </div>
      <FacingLabels />
    </Studio>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// CAP & TOTE & MUG
// ═════════════════════════════════════════════════════════════════════════════

function CapMain() {
  return (
    <Studio hangtag={{ code: 'B · CAP', title: 'Casquette', note: 'QR au panneau latéral' }} width={400} height={320}>
      <div style={{ position: 'relative' }}>
        <Cap color={kBG}>
          <div style={{ position: 'absolute', top: 75, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
            <AppIcon size={42} />
          </div>
          {/* QR sur panneau latéral droit */}
          <div style={{ position: 'absolute', top: 90, right: 60 }}>
            <QRPlate size={26} />
          </div>
        </Cap>
      </div>
    </Studio>
  );
}

function ToteMain() {
  return (
    <Studio hangtag={{ code: 'C · TOTE', title: 'Sac coton', note: '36×40cm' }} width={420} height={480}>
      <Tote color={kBG}>
        <div style={{ position: 'absolute', top: 130, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
          <AppIcon size={88} />
        </div>
        <div style={{ position: 'absolute', bottom: 50, right: 30 }}>
          <QRPlate size={44} />
        </div>
      </Tote>
    </Studio>
  );
}

function Mug() {
  return (
    <Studio hangtag={{ code: 'H · MUG', title: 'Mug céramique 330ml' }} width={420} height={400}>
      <div style={{ position: 'relative' }}>
        <div style={{ width: 220, height: 230, background: kBG, borderRadius: 8, position: 'relative', boxShadow: '0 24px 48px rgba(0,0,0,0.4)' }}>
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 8, background: 'rgba(255,255,255,0.04)', borderRadius: '8px 8px 0 0' }} />
          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <AppIcon size={80} />
          </div>
          {/* QR petit en bas droite */}
          <div style={{ position: 'absolute', bottom: 18, right: 18 }}>
            <QRPlate size={32} />
          </div>
        </div>
        <div style={{ position: 'absolute', right: -50, top: 50, width: 56, height: 100, border: `18px solid ${kBG}`, borderRadius: 30, borderLeft: 'none' }} />
      </div>
    </Studio>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// STICKERS — icône uniquement, en plusieurs formats. Une seule pièce = QR.
// ═════════════════════════════════════════════════════════════════════════════

function StickersPack() {
  return (
    <div style={{
      width: 1100, height: 600, padding: 36, boxSizing: 'border-box',
      background: '#f4efe2',
      fontFamily: kFONT.ui, position: 'relative', overflow: 'hidden',
    }}>
      <div style={{ position: 'absolute', inset: 0, background: 'repeating-linear-gradient(45deg, rgba(0,0,0,0.02) 0 2px, transparent 2px 6px)', pointerEvents: 'none' }} />

      <div style={{ position: 'relative', marginBottom: 30 }}>
        <div style={{ fontFamily: kFONT.mono, fontSize: 11, color: '#777', letterSpacing: '0.2em' }}>STICKER PACK · COTA</div>
        <div style={{ fontFamily: kFONT.title, fontSize: 32, color: kBG, letterSpacing: '-0.03em', marginTop: 4 }}>6 pièces · die-cut</div>
      </div>

      <div style={{ display: 'flex', gap: 28, alignItems: 'center', justifyContent: 'center', flexWrap: 'wrap' }}>
        {/* 01 — icône grande, rounded square */}
        <Sticker shape="rounded" radius={28} rotate={-6} width={150} height={150}>
          <AppIcon size={134} radius={0.21} />
        </Sticker>

        {/* 02 — icône moyenne, square */}
        <Sticker shape="rounded" radius={20} rotate={4} width={110} height={110}>
          <AppIcon size={98} radius={0.21} />
        </Sticker>

        {/* 03 — icône cercle (rond jaune signal) */}
        <Sticker shape="circle" rotate={-8} width={130} height={130}>
          <div style={{ width: '100%', height: '100%', background: kACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div style={{ width: 86, height: 86, borderRadius: 18, overflow: 'hidden' }}>
              <AppIcon size={86} radius={0.21} />
            </div>
          </div>
        </Sticker>

        {/* 04 — QR sticker */}
        <Sticker shape="rounded" radius={14} rotate={6} width={130} height={130}>
          <div style={{ width: '100%', height: '100%', background: '#ffffff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <QRCode size={104} />
          </div>
        </Sticker>

        {/* 05 — icône petite, square */}
        <Sticker shape="rounded" radius={14} rotate={-3} width={80} height={80}>
          <AppIcon size={72} radius={0.21} />
        </Sticker>

        {/* 06 — pure jaune dot (signal isolé) */}
        <Sticker shape="circle" rotate={3} width={70} height={70}>
          <div style={{ width: '100%', height: '100%', background: kACCENT }} />
        </Sticker>
      </div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// BUSINESS CARD — icône + nom + contact. Dos = QR.
// ═════════════════════════════════════════════════════════════════════════════

function BizCards() {
  return (
    <Studio hangtag={{ code: 'D · BUSINESS CARD', title: '85×54mm · pelliculage soft-touch' }} width={920} height={500}>
      <div style={{ display: 'flex', gap: 40, transform: 'rotate(-2deg)' }}>
        {/* FRONT — icône + identité, sobre */}
        <BizCard width={360} height={228}>
          <div style={{ width: '100%', height: '100%', background: kBG, padding: 24, boxSizing: 'border-box', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', position: 'relative' }}>
            <AppIcon size={36} />
            <div>
              <div style={{ fontFamily: kFONT.title, fontSize: 22, color: kINK, letterSpacing: '-0.02em' }}>Karim Bouchareb</div>
              <div style={{ fontFamily: kFONT.mono, fontSize: 9, color: kDIM, letterSpacing: '0.15em', marginTop: 8 }}>
                karim@cota.app<br/>+33 6 12 34 56 78
              </div>
            </div>
          </div>
        </BizCard>
        {/* BACK — QR plein cadre */}
        <BizCard width={360} height={228}>
          <div style={{ width: '100%', height: '100%', background: kBG, position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <div style={{ padding: 12, background: '#ffffff', borderRadius: 6 }}>
              <QRCode size={140} />
            </div>
          </div>
        </BizCard>
      </div>
      <FacingLabels />
    </Studio>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// PRESS PASS / LANYARD — icône + photo + QR
// ═════════════════════════════════════════════════════════════════════════════

function PressPass() {
  return (
    <Studio hangtag={{ code: 'E · PRESS PASS', title: 'Lanyard + badge', note: 'pour stadium / events' }} width={520} height={620}>
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
        {/* Lanyard noir uni — sobre */}
        <div style={{ width: 320, height: 28, background: kBG, borderRadius: '2px 2px 0 0', boxShadow: '0 2px 6px rgba(0,0,0,0.3)' }} />
        <div style={{ width: 50, height: 14, background: '#999', borderRadius: 2, marginTop: -2 }} />

        {/* Pass card */}
        <div style={{ marginTop: 8, width: 240, background: kBG, borderRadius: 10, overflow: 'hidden', boxShadow: '0 16px 32px rgba(0,0,0,0.5)', position: 'relative', transform: 'rotate(-3deg)' }}>
          <div style={{ position: 'absolute', top: 8, left: '50%', transform: 'translateX(-50%)', width: 30, height: 6, background: '#444', borderRadius: 3 }} />
          <div style={{ padding: 20, paddingTop: 24, boxSizing: 'border-box' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
              <AppIcon size={28} />
              <QRPlate size={36} />
            </div>
            <div style={{ width: '100%', aspectRatio: '1 / 1', background: '#333', borderRadius: 6, marginBottom: 14, position: 'relative', overflow: 'hidden' }}>
              <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(135deg, #444, #222)' }} />
            </div>
            <div style={{ fontFamily: kFONT.title, fontSize: 18, color: kINK, letterSpacing: '-0.02em' }}>Karim B.</div>
          </div>
        </div>
      </div>
    </Studio>
  );
}

// ═════════════════════════════════════════════════════════════════════════════
// DIGITAL — Phone wallpaper, social cover
// ═════════════════════════════════════════════════════════════════════════════

function PhoneWallpaper() {
  return (
    <Studio hangtag={{ code: 'F · WALLPAPER', title: 'Fond d\'écran iPhone', note: '1290×2796' }} width={320} height={560}>
      <div style={{ width: 220, height: 480, borderRadius: 32, overflow: 'hidden', background: kBG, position: 'relative', boxShadow: '0 24px 48px rgba(0,0,0,0.6), 0 0 0 6px #15181d, 0 0 0 7px rgba(255,255,255,0.1)' }}>
        {/* status bar */}
        <div style={{ position: 'absolute', top: 14, left: 18, right: 18, display: 'flex', justifyContent: 'space-between', color: '#fff', fontSize: 11, fontWeight: 600, fontFamily: '-apple-system, system-ui' }}>
          <span>9:41</span>
          <span style={{ fontSize: 10 }}>●●● 5G ▮</span>
        </div>

        {/* clock */}
        <div style={{ position: 'absolute', top: '14%', left: 0, right: 0, textAlign: 'center' }}>
          <div style={{ fontFamily: '-apple-system, system-ui', fontSize: 60, fontWeight: 200, color: '#fff', letterSpacing: '-0.04em' }}>09:31</div>
          <div style={{ fontFamily: '-apple-system, system-ui', fontSize: 11, color: 'rgba(255,255,255,0.7)', marginTop: 2 }}>mardi 18 mai</div>
        </div>

        {/* icone centrée */}
        <div style={{ position: 'absolute', top: '46%', left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
          <AppIcon size={84} />
        </div>

        {/* QR petit en bas */}
        <div style={{ position: 'absolute', bottom: 90, left: 0, right: 0, display: 'flex', justifyContent: 'center' }}>
          <QRPlate size={42} />
        </div>

        {/* home indicator */}
        <div style={{ position: 'absolute', bottom: 8, left: '50%', transform: 'translateX(-50%)', width: 84, height: 4, background: 'rgba(255,255,255,0.7)', borderRadius: 2 }} />
      </div>
    </Studio>
  );
}

function SocialCover() {
  return (
    <Studio hangtag={{ code: 'G · SOCIAL COVER', title: 'X / LinkedIn / GitHub', note: '1500×500' }} width={920} height={400}>
      <div style={{ width: 800, height: 268, position: 'relative', overflow: 'hidden', borderRadius: 8, boxShadow: '0 20px 40px rgba(0,0,0,0.4)' }}>
        <div style={{ position: 'absolute', inset: 0, background: kBG }} />
        {/* icône centrée */}
        <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <AppIcon size={128} />
        </div>
        {/* QR coin bas droit */}
        <div style={{ position: 'absolute', bottom: 18, right: 18 }}>
          <QRPlate size={50} />
        </div>
      </div>
    </Studio>
  );
}

Object.assign(window, {
  TeeSmallChest, TeeCenter, TeeBackBig, TeeCream,
  HoodieMain, CapMain, ToteMain, Mug, StickersPack,
  BizCards, PressPass, PhoneWallpaper, SocialCover,
  QRCode, QRPlate,
});
