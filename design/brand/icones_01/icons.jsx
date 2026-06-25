/* global React */
// COTA app icon — refined system.
// The master keeps the brand's C + signal yellow, then adds prediction cues:
// confidence ticks, odds rail, coupon notch, and live pulse language.

const BG = '#0b0d10';
const INK = '#f4efe2';
const ACCENT = '#e8ff36';
const FRAME = '#1d2026';
const SURFACE = '#141820';
const MUTED = '#8b8a85';

// ── Variant 01 — Signal frame -------------------------------------------------
// Master direction: COTA's C, framed by a betting-signal rail + confidence ticks.
function IconFrame({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const pad = size * 0.072;
  const inner = size - pad * 2;
  const tick = size * 0.058;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect
        x={size * 0.06} y={size * 0.06}
        width={size * 0.88} height={size * 0.88}
        rx={r * 0.76}
        fill={SURFACE}
      />
      <rect
        x={pad} y={pad} width={inner} height={inner}
        rx={r * 0.58}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.034}
        strokeLinejoin="round"
      />
      {[0, 1, 2].map(i => (
        <rect
          key={i}
          x={size * 0.2 + i * tick * 1.24}
          y={size * 0.19}
          width={tick}
          height={size * 0.024}
          rx={size * 0.012}
          fill={i === 2 ? ACCENT : INK}
          opacity={i === 2 ? 1 : 0.35}
        />
      ))}
      <text
        x={size / 2} y={size * 0.7}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.64}
        fill={INK}
        letterSpacing={-size * 0.026}
      >C</text>
      <rect x={size * 0.255} y={size * 0.806} width={size * 0.405} height={size * 0.04} rx={size * 0.01} fill={ACCENT} />
      <rect x={size * 0.69} y={size * 0.806} width={size * 0.055} height={size * 0.04} rx={size * 0.01} fill={ACCENT} />
    </svg>
  );
}

// ── Variant 02 — Odds rail ----------------------------------------------------
// Bracket frame and bottom ticker rail; more product/app than bookmaker logo.
function IconBleed({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const sw = size * 0.038;
  const l = size * 0.22;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <defs>
        <clipPath id={`clip-rail-${size}`}>
          <rect width={size} height={size} rx={r} />
        </clipPath>
      </defs>
      <g clipPath={`url(#clip-rail-${size})`}>
        <rect width={size} height={size} fill={BG} />
        <rect x={size * 0.08} y={size * 0.08} width={size * 0.84} height={size * 0.84} rx={r * 0.58} fill={SURFACE} />
        <path
          d={`M${size * 0.16} ${size * 0.34}V${size * 0.16}h${l} M${size * 0.84} ${size * 0.34}V${size * 0.16}h${-l} M${size * 0.16} ${size * 0.66}v${size * 0.18}h${l} M${size * 0.84} ${size * 0.66}v${size * 0.18}h${-l}`}
          fill="none" stroke={ACCENT} strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round"
        />
        <text
          x={size * 0.51} y={size * 0.72}
          textAnchor="middle"
          fontFamily='"Archivo Black", "Archivo", sans-serif'
          fontWeight="900"
          fontSize={size * 0.7}
          fill={INK}
          letterSpacing={-size * 0.03}
        >C</text>
        <rect x={size * 0.28} y={size * 0.82} width={size * 0.34} height={size * 0.035} rx={size * 0.01} fill={ACCENT} />
        <text x={size * 0.68} y={size * 0.855} fontFamily='"JetBrains Mono", monospace' fontWeight="700" fontSize={size * 0.075} fill={ACCENT}>2.55</text>
      </g>
    </svg>
  );
}

// ── Variant 03 — Coupon -------------------------------------------------------
// Ticket-like silhouette for the daily combined coupon.
function IconUnderscore({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const notch = size * 0.095;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <defs>
        <clipPath id={`clip-coupon-${size}`}>
          <path d={`M${r} 0H${size - r}Q${size} 0 ${size} ${r}V${size * 0.39}a${notch} ${notch} 0 0 0 0 ${notch * 2}V${size - r}Q${size} ${size} ${size - r} ${size}H${r}Q0 ${size} 0 ${size - r}V${size * 0.39}a${notch} ${notch} 0 0 0 0 ${notch * 2}V${r}Q0 0 ${r} 0Z`} />
        </clipPath>
      </defs>
      <g clipPath={`url(#clip-coupon-${size})`}>
        <rect width={size} height={size} fill={BG} />
        <rect x={size * 0.09} y={size * 0.09} width={size * 0.82} height={size * 0.82} rx={r * 0.45} fill={SURFACE} />
        <text
          x={size / 2} y={size * 0.67}
          textAnchor="middle"
          fontFamily='"Archivo Black", "Archivo", sans-serif'
          fontWeight="900"
          fontSize={size * 0.62}
          fill={INK}
          letterSpacing={-size * 0.03}
        >C</text>
        <path d={`M${size * 0.18} ${size * 0.78}H${size * 0.82}`} stroke={ACCENT} strokeWidth={size * 0.035} strokeLinecap="round" strokeDasharray={`${size * 0.08} ${size * 0.045}`} />
        <rect x={size * 0.71} y={size * 0.185} width={size * 0.12} height={size * 0.055} rx={size * 0.016} fill={ACCENT} />
      </g>
    </svg>
  );
}

// ── Variant 04 — Live orbit ---------------------------------------------------
// Ring borrowed from the wordmark O, with a live marker in motion language.
function IconRing({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <circle
        cx={size / 2} cy={size / 2}
        r={size * 0.39}
        fill={SURFACE} stroke={FRAME} strokeWidth={size * 0.09}
      />
      <circle
        cx={size / 2} cy={size / 2}
        r={size * 0.39}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.034}
        strokeDasharray={`${size * 0.42} ${size * 1.85}`}
        strokeLinecap="round"
        transform={`rotate(-38 ${size / 2} ${size / 2})`}
      />
      <text
        x={size / 2} y={size * 0.7}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.62}
        fill={INK}
        letterSpacing={-size * 0.02}
      >C</text>
      <circle cx={size * 0.78} cy={size * 0.28} r={size * 0.045} fill={ACCENT} />
    </svg>
  );
}

// ── Variant 05 — Confidence ---------------------------------------------------
// Four confidence bars, a direct nod to the 1-4 star prediction level.
function IconYellow({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect x={size * 0.09} y={size * 0.09} width={size * 0.82} height={size * 0.82} rx={r * 0.55} fill={SURFACE} />
      <text
        x={size / 2} y={size * 0.66}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.62}
        fill={INK}
        letterSpacing={-size * 0.028}
      >C</text>
      {[0, 1, 2, 3].map(i => (
        <rect
          key={i}
          x={size * (0.24 + i * 0.13)}
          y={size * (0.8 - i * 0.035)}
          width={size * 0.075}
          height={size * (0.055 + i * 0.035)}
          rx={size * 0.015}
          fill={i === 3 ? ACCENT : INK}
          opacity={i === 3 ? 1 : 0.38}
        />
      ))}
    </svg>
  );
}

// ── FRAME variants — punched up for foot pronostic context -------------------

// FRAME + live dot — pastille jaune coin haut-droit, signal "live"
function IconFrameDot({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const pad = size * 0.067;
  const inner = size - pad * 2;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect
        x={pad} y={pad} width={inner} height={inner}
        rx={r * 0.55}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.025}
      />
      <text
        x={size / 2} y={size * 0.72}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.62}
        fill={INK}
        letterSpacing={-size * 0.02}
      >C</text>
      {/* live dot */}
      <circle cx={size - pad - size * 0.07} cy={pad + size * 0.07} r={size * 0.055} fill={ACCENT} />
    </svg>
  );
}

// FRAME épais — accent plus large, C plus dominant
function IconFrameThick({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const pad = size * 0.067;
  const inner = size - pad * 2;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect
        x={pad} y={pad} width={inner} height={inner}
        rx={r * 0.55}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.05}
      />
      <text
        x={size / 2} y={size * 0.75}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.74}
        fill={INK}
        letterSpacing={-size * 0.03}
      >C</text>
    </svg>
  );
}

// FRAME + barre underscore — mix FRAME × UNDERSCORE
function IconFrameBar({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const pad = size * 0.067;
  const inner = size - pad * 2;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect
        x={pad} y={pad} width={inner} height={inner}
        rx={r * 0.55}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.025}
      />
      <text
        x={size / 2} y={size * 0.68}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.58}
        fill={INK}
        letterSpacing={-size * 0.02}
      >C</text>
      {/* barre + terminal sous le C */}
      <rect x={size * 0.3} y={size * 0.79} width={size * 0.34} height={size * 0.035} fill={ACCENT} />
      <rect x={size * 0.64} y={size * 0.79} width={size * 0.04} height={size * 0.035} fill={ACCENT} />
    </svg>
  );
}

// FRAME pulse — anneau jaune externe à l'icône (effet "en direct")
function IconFramePulse({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  const pad = size * 0.12;
  const inner = size - pad * 2;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <rect width={size} height={size} rx={r} fill={BG} />
      {/* deux cadres concentriques */}
      <rect
        x={pad * 0.55} y={pad * 0.55}
        width={size - pad * 1.1} height={size - pad * 1.1}
        rx={r * 0.7}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.018} opacity="0.35"
      />
      <rect
        x={pad} y={pad} width={inner} height={inner}
        rx={r * 0.5}
        fill="none" stroke={ACCENT} strokeWidth={size * 0.028}
      />
      <text
        x={size / 2} y={size * 0.73}
        textAnchor="middle"
        fontFamily='"Archivo Black", "Archivo", sans-serif'
        fontWeight="900"
        fontSize={size * 0.56}
        fill={INK}
        letterSpacing={-size * 0.02}
      >C</text>
    </svg>
  );
}

// ── Variant 06 — Notch --------------------------------------------------------
// Inverse: C is the negative space, cut out of a yellow plate. Like a ticket stub.
function IconNotch({ size = 120, radius = 0.18 }) {
  const r = size * radius;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
      <defs>
        <mask id={`mask-notch-${size}`}>
          <rect width={size} height={size} fill="white" />
          <text
            x={size / 2} y={size * 0.78}
            textAnchor="middle"
            fontFamily='"Archivo Black", "Archivo", sans-serif'
            fontWeight="900"
            fontSize={size * 0.86}
            fill="black"
            letterSpacing={-size * 0.04}
          >C</text>
        </mask>
      </defs>
      <rect width={size} height={size} rx={r} fill={BG} />
      <rect
        x={size * 0.1} y={size * 0.1}
        width={size * 0.8} height={size * 0.8}
        rx={r * 0.55}
        fill={ACCENT}
        mask={`url(#mask-notch-${size})`}
      />
    </svg>
  );
}

const FRAME_VARIANTS = [
  { id: 'frame-signal', label: 'FRAME · signal',     Comp: IconFrame,      note: 'Master proposé — cadre ferme, ticks de confiance, barre COTA conservée.' },
  { id: 'frame-rail',   label: 'FRAME · odds rail',  Comp: IconBleed,      note: 'Version plus produit : rail de cote, bracket corners, lecture immédiate en 64px.' },
  { id: 'frame-coupon', label: 'FRAME · coupon',     Comp: IconUnderscore, note: 'Silhouette ticket pour le coupon IA combiné, sans tomber dans le billet générique.' },
  { id: 'frame-orbit',  label: 'FRAME · live orbit', Comp: IconRing,       note: 'Anneau actif inspiré du O du logo, utile pour splash et états live.' },
  { id: 'frame-bars',   label: 'FRAME · confiance',  Comp: IconYellow,     note: 'Quatre barres de confiance pour rappeler les niveaux 1 à 4 étoiles.' },
];

const VARIANTS = [
  { id: 'frame',      label: '01 · SIGNAL',     Comp: IconFrame,      note: 'Direction master : C massif, cadre jaune, ticks de confiance et barre terminale.' },
  { id: 'bleed',      label: '02 · ODDS RAIL',  Comp: IconBleed,      note: 'Bracket corners + cote discrète pour ancrer l\'icône dans le pronostic sportif.' },
  { id: 'underscore', label: '03 · COUPON',     Comp: IconUnderscore, note: 'Découpe ticket, idéale pour la promesse coupon IA quotidien.' },
  { id: 'ring',       label: '04 · LIVE ORBIT', Comp: IconRing,       note: 'Anneau actif autour du C : plus premium pour splash, loading et live odds.' },
  { id: 'yellow',     label: '05 · CONFIANCE',  Comp: IconYellow,     note: 'Quatre barres montantes : lecture claire du niveau de confiance.' },
  { id: 'notch',      label: '06 · CUTOUT',     Comp: IconNotch,      note: 'C en négatif dans une plaque jaune. Fort en marketing, à éviter comme favicon.' },
];

// ── Bookmaker context ────────────────────────────────────────────────────────
// Plausible category neighbours (no real logos) so we can see how a candidate
// sits among typical sports-betting visual codes: red, green, yellow/black.
function CategoryGrid({ Comp }) {
  const slot = 76;
  const neighbours = [
    { name: 'Pari A',     bg: '#c8112e', fg: '#fff', glyph: 'B' },
    { name: 'Pari B',     bg: '#e30613', fg: '#fff', glyph: 'W' },
    { name: 'Pari C',     bg: '#0a8a3e', fg: '#fff', glyph: 'U' },
    { name: 'Pari D',     bg: '#fcd000', fg: '#0b0d10', glyph: 'b' },
    { name: 'Pari E',     bg: '#0b1f3b', fg: '#fff', glyph: 'P' },
    { name: 'Pari F',     bg: '#ff5a00', fg: '#fff', glyph: 'N' },
    { name: 'Pari G',     bg: 'linear-gradient(135deg,#1a1a2e,#a60c5e)', fg: '#fff', glyph: 'Z' },
  ];

  const Slot = ({ children, name }) => (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}>
      <div style={{ width: slot, height: slot, borderRadius: slot * 0.225, overflow: 'hidden', boxShadow: '0 2px 6px rgba(0,0,0,0.25)' }}>{children}</div>
      <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 10, color: '#8b8a85', letterSpacing: '0.08em' }}>{name}</div>
    </div>
  );

  return (
    <div style={{
      width: 880, padding: 32, boxSizing: 'border-box',
      background: '#15181d', color: INK,
      fontFamily: '"Space Grotesk", system-ui, sans-serif',
      display: 'flex', flexDirection: 'column', gap: 24,
    }}>
      <div>
        <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 11, letterSpacing: '0.18em', color: ACCENT, marginBottom: 6 }}>DANS LA CATÉGORIE</div>
        <div style={{ fontSize: 15, color: '#a8a79f', maxWidth: 720 }}>
          Au milieu d’icônes typiques de bookmakers (rouges agressifs, verts, jaune/noir).
          COTA en C noir/jaune doit trancher sans crier au casino.
        </div>
      </div>

      <div style={{ display: 'flex', gap: 18, alignItems: 'flex-start', flexWrap: 'wrap' }}>
        {neighbours.slice(0, 3).map(n => (
          <Slot key={n.name} name={n.name}>
            <div style={{ width: '100%', height: '100%', background: n.bg, color: n.fg, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: '"Archivo Black", sans-serif', fontWeight: 900, fontSize: 36 }}>{n.glyph}</div>
          </Slot>
        ))}
        {/* candidate */}
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, position: 'relative' }}>
          <div style={{ position: 'absolute', top: -8, left: -8, right: -8, bottom: 22, border: `2px dashed ${ACCENT}`, borderRadius: slot * 0.27 }} />
          <div style={{ width: slot, height: slot, borderRadius: slot * 0.225, overflow: 'hidden', boxShadow: '0 2px 6px rgba(0,0,0,0.25)' }}>
            <Comp size={slot} radius={0.225} />
          </div>
          <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 10, color: ACCENT, letterSpacing: '0.08em', fontWeight: 700 }}>COTA</div>
        </div>
        {neighbours.slice(3).map(n => (
          <Slot key={n.name} name={n.name}>
            <div style={{ width: '100%', height: '100%', background: n.bg, color: n.fg, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: '"Archivo Black", sans-serif', fontWeight: 900, fontSize: 36 }}>{n.glyph}</div>
          </Slot>
        ))}
      </div>
    </div>
  );
}

// ── Springboard preview -------------------------------------------------------
// A fake iOS home screen page that drops the candidate icon among neighbours.
function Springboard({ Comp, label }) {
  const slot = 62; // app icon size in springboard
  const others = [
    { name: 'Météo',     bg: 'linear-gradient(180deg,#3b82f6,#1d4ed8)', glyph: '☀' },
    { name: 'Calendrier', bg: '#fff', fg: '#ef4444', glyph: '18' },
    { name: 'Photos',    bg: '#fff', glyph: '🌸' },
    { name: 'Plans',     bg: 'linear-gradient(135deg,#86efac,#3b82f6)', glyph: '◧' },
    { name: 'Notes',     bg: '#fff', fg: '#f59e0b', glyph: '✎' },
    { name: 'Bourse',    bg: '#000', fg: '#22c55e', glyph: '📈' },
    { name: 'Wallet',    bg: '#000', glyph: '💳' },
    // COTA slotted in below, then more:
    { name: 'Spotify',   bg: '#000', fg: '#22c55e', glyph: '♫' },
    { name: 'WhatsApp',  bg: '#22c55e', fg: '#fff', glyph: '✆' },
    { name: 'Maps',      bg: '#dbeafe', glyph: '📍' },
  ];

  const IconSlot = ({ children, name }) => (
    <div style={{ width: slot, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
      <div style={{ width: slot, height: slot, borderRadius: slot * 0.225, overflow: 'hidden', boxShadow: '0 1px 2px rgba(0,0,0,0.25)' }}>
        {children}
      </div>
      <div style={{ fontFamily: '-apple-system, system-ui', fontSize: 11, color: '#fff', textShadow: '0 1px 2px rgba(0,0,0,0.4)' }}>{name}</div>
    </div>
  );

  const FakeIcon = ({ bg, fg = '#000', glyph }) => (
    <div style={{
      width: '100%', height: '100%', background: bg, color: fg,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontSize: 28, fontWeight: 700,
    }}>{glyph}</div>
  );

  // Springboard background (a calm gradient that resembles a wallpaper)
  return (
    <div style={{
      width: 340, height: 560, borderRadius: 36, overflow: 'hidden',
      background: 'linear-gradient(160deg,#1e293b 0%, #475569 40%, #94a3b8 80%, #cbd5e1 100%)',
      padding: '52px 16px 16px',
      boxSizing: 'border-box', position: 'relative',
      fontFamily: '-apple-system, system-ui',
    }}>
      {/* status bar mock */}
      <div style={{ position: 'absolute', top: 16, left: 0, right: 0, display: 'flex', justifyContent: 'space-between', padding: '0 28px', color: '#fff', fontSize: 14, fontWeight: 600 }}>
        <span>9:41</span>
        <span>●●● 5G ▮</span>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', rowGap: 18, columnGap: 8, justifyItems: 'center' }}>
        {others.slice(0, 6).map(o => (
          <IconSlot key={o.name} name={o.name}><FakeIcon {...o} /></IconSlot>
        ))}
        {/* candidate — middle of the grid, ringed faintly so the eye finds it */}
        <div style={{ width: slot, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, position: 'relative' }}>
          <div style={{ width: slot + 8, height: slot + 8, borderRadius: slot * 0.27, position: 'absolute', top: -4, border: `2px dashed ${ACCENT}`, opacity: 0.9 }} />
          <div style={{ width: slot, height: slot, borderRadius: slot * 0.225, overflow: 'hidden', boxShadow: '0 1px 2px rgba(0,0,0,0.25)' }}>
            <Comp size={slot} radius={0.225} />
          </div>
          <div style={{ fontFamily: '-apple-system, system-ui', fontSize: 11, color: '#fff', textShadow: '0 1px 2px rgba(0,0,0,0.4)', fontWeight: 600 }}>COTA</div>
        </div>
        {others.slice(6).map(o => (
          <IconSlot key={o.name} name={o.name}><FakeIcon {...o} /></IconSlot>
        ))}
      </div>

      {/* dock */}
      <div style={{
        position: 'absolute', bottom: 14, left: 12, right: 12, height: 80,
        borderRadius: 26, background: 'rgba(255,255,255,0.18)', backdropFilter: 'blur(20px)',
        WebkitBackdropFilter: 'blur(20px)',
        display: 'flex', alignItems: 'center', justifyContent: 'space-around', padding: '0 12px',
      }}>
        {[
          { bg: '#22c55e', glyph: '✆', fg: '#fff' },
          { bg: '#3b82f6', glyph: '✉', fg: '#fff' },
          { bg: '#fff',   glyph: '🧭', fg: '#000' },
          { bg: '#fff',   glyph: '🎵', fg: '#000' },
        ].map((o, i) => (
          <div key={i} style={{ width: 58, height: 58, borderRadius: 14, background: o.bg, color: o.fg, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 26 }}>{o.glyph}</div>
        ))}
      </div>
    </div>
  );
}

// ── Size ladder ---------------------------------------------------------------
// Shows the icon at favicon → home screen → list row → splash, plus a mono label.
function SizeLadder({ Comp, label, note }) {
  const sizes = [
    { px: 16,  caption: 'Favicon' },
    { px: 32,  caption: 'Tab' },
    { px: 60,  caption: 'Springboard' },
    { px: 96,  caption: 'Settings' },
    { px: 180, caption: '@3x' },
  ];

  return (
    <div style={{
      width: 720, padding: 32, boxSizing: 'border-box',
      background: '#15181d',
      color: INK,
      fontFamily: '"Space Grotesk", system-ui, sans-serif',
      display: 'flex', flexDirection: 'column', gap: 24,
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', borderBottom: `1px solid #2a2e36`, paddingBottom: 16 }}>
        <div>
          <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 11, letterSpacing: '0.18em', color: ACCENT, marginBottom: 6 }}>{label}</div>
          <div style={{ fontSize: 15, color: '#a8a79f', maxWidth: 480, lineHeight: 1.4 }}>{note}</div>
        </div>
        <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 10, color: '#5a5d63', letterSpacing: '0.1em' }}>SVG · scalable</div>
      </div>

      <div style={{ display: 'flex', gap: 28, alignItems: 'flex-end' }}>
        {sizes.map(s => (
          <div key={s.px} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10 }}>
            <Comp size={s.px} />
            <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 9, color: '#5a5d63', letterSpacing: '0.1em' }}>{s.px}px</div>
            <div style={{ fontSize: 10, color: '#8b8a85' }}>{s.caption}</div>
          </div>
        ))}
      </div>

      {/* hero size */}
      <div style={{ display: 'flex', gap: 32, alignItems: 'center', padding: '24px 0 8px' }}>
        <Comp size={220} />
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 12 }}>
          {/* settings list row preview */}
          <div style={{ background: '#0b0d10', borderRadius: 12, padding: '14px 14px', display: 'flex', alignItems: 'center', gap: 14 }}>
            <div style={{ borderRadius: 12, overflow: 'hidden' }}><Comp size={44} /></div>
            <div style={{ flex: 1 }}>
              <div style={{ fontWeight: 600, fontSize: 15 }}>COTA</div>
              <div style={{ fontSize: 12, color: '#8b8a85', fontFamily: '"JetBrains Mono", monospace', letterSpacing: '0.05em' }}>v1.0 · 18.4 Mo</div>
            </div>
            <div style={{ fontSize: 11, color: ACCENT, fontFamily: '"JetBrains Mono", monospace', letterSpacing: '0.1em' }}>OUVRIR ›</div>
          </div>
          {/* notification row */}
          <div style={{ background: '#0b0d10', borderRadius: 12, padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12 }}>
            <div style={{ borderRadius: 8, overflow: 'hidden' }}><Comp size={32} /></div>
            <div style={{ flex: 1 }}>
              <div style={{ fontWeight: 600, fontSize: 13 }}>COTA · maintenant</div>
              <div style={{ fontSize: 12, color: '#a8a79f' }}>Cote relevée : PSG–OM @ 2.55 ✓</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Comparison row ------------------------------------------------------------
function Comparison() {
  const slot = 80;
  return (
    <div style={{
      width: 1120, padding: 40, boxSizing: 'border-box',
      background: '#0b0d10', color: INK,
      fontFamily: '"Space Grotesk", system-ui, sans-serif',
      display: 'flex', flexDirection: 'column', gap: 24,
    }}>
      <div>
        <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 11, letterSpacing: '0.18em', color: ACCENT, marginBottom: 6 }}>00 · COMPARATIF</div>
        <div style={{ fontSize: 18, color: '#a8a79f', maxWidth: 800 }}>
          Six variations autour d'un C majuscule. Toutes utilisent Archivo Black,
          le fond <span style={{ fontFamily: 'monospace' }}>#0b0d10</span> et l'accent
          <span style={{ background: ACCENT, color: BG, padding: '0 6px', marginLeft: 4, fontFamily: 'monospace' }}>#e8ff36</span>.
        </div>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: 18 }}>
        {VARIANTS.map(({ id, label, Comp }) => (
          <div key={id} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 10 }}>
            <Comp size={slot} />
            <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 10, color: '#8b8a85', letterSpacing: '0.1em' }}>{label}</div>
          </div>
        ))}
      </div>
      {/* small-size legibility row */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: 18, paddingTop: 8, borderTop: '1px solid #1d2026' }}>
        {VARIANTS.map(({ id, Comp }) => (
          <div key={id} style={{ display: 'flex', gap: 10, alignItems: 'center', justifyContent: 'center' }}>
            <Comp size={16} /><Comp size={24} /><Comp size={32} />
          </div>
        ))}
      </div>
    </div>
  );
}

// ── Design todo ---------------------------------------------------------------
// Interactive handoff checklist. It hides itself once every task is checked.
const DESIGN_TODOS = [
  'Master icon redessiné autour du C, du cadre signal et de la barre terminale',
  'Variantes icônes resserrées : signal, odds rail, coupon, live orbit, confiance',
  'Lisibilité vérifiée dans les tailles 16, 24, 32, 60, 96 et 180 px',
  'Contexte bookmaker et écran iOS conservés pour juger la différenciation',
  'SVG master et pack export synchronisés avec la direction retenue',
];

function DesignTodoBoard() {
  const storageKey = 'cota.icon.design.todo.v2';
  const [done, setDone] = React.useState(() => {
    try {
      const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
      return Array.isArray(saved) && saved.length === DESIGN_TODOS.length
        ? saved
        : DESIGN_TODOS.map(() => false);
    } catch (_) {
      return DESIGN_TODOS.map(() => false);
    }
  });
  const [hidden, setHidden] = React.useState(() => done.every(Boolean));
  const completed = done.filter(Boolean).length;
  const allDone = completed === DESIGN_TODOS.length;

  React.useEffect(() => {
    localStorage.setItem(storageKey, JSON.stringify(done));
    if (done.every(Boolean)) {
      const t = setTimeout(() => setHidden(true), 650);
      return () => clearTimeout(t);
    }
  }, [done]);

  if (hidden) return null;

  return (
    <div style={{
      width: 780,
      minHeight: 420,
      padding: 34,
      boxSizing: 'border-box',
      background: '#101318',
      color: INK,
      fontFamily: '"Space Grotesk", system-ui, sans-serif',
      border: '1px solid #262b33',
      display: 'grid',
      gridTemplateColumns: '1fr 190px',
      gap: 28,
    }}>
      <div>
        <div style={{ fontFamily: '"JetBrains Mono", monospace', fontSize: 11, letterSpacing: '0.18em', color: ACCENT, marginBottom: 8 }}>
          TODO DESIGN · AUTO-SUPPRESSION
        </div>
        <div style={{ fontFamily: '"Archivo Black", sans-serif', fontSize: 34, lineHeight: 1, letterSpacing: '-0.035em', marginBottom: 12 }}>
          Icône 01, propre jusqu'au dernier pixel.
        </div>
        <div style={{ color: '#a8a79f', lineHeight: 1.45, maxWidth: 520, marginBottom: 26, fontSize: 15 }}>
          Coche chaque point au fil de la validation. Quand tout est terminé, cette Todo se masque seule et reste masquée au prochain chargement.
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {DESIGN_TODOS.map((item, index) => (
            <button
              key={item}
              type="button"
              onClick={() => setDone((current) => current.map((value, i) => (i === index ? !value : value)))}
              style={{
                width: '100%',
                display: 'grid',
                gridTemplateColumns: '28px 1fr',
                gap: 12,
                alignItems: 'center',
                textAlign: 'left',
                border: `1px solid ${done[index] ? 'rgba(232,255,54,0.45)' : '#2a2e36'}`,
                background: done[index] ? 'rgba(232,255,54,0.08)' : '#15181d',
                color: done[index] ? INK : '#c7c4b8',
                padding: '12px 14px',
                borderRadius: 8,
                cursor: 'pointer',
                transition: 'transform .18s ease, border-color .18s ease, background .18s ease',
                fontFamily: '"Space Grotesk", system-ui, sans-serif',
                fontSize: 14,
                lineHeight: 1.25,
              }}
            >
              <span style={{
                width: 22,
                height: 22,
                borderRadius: 6,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: done[index] ? ACCENT : 'transparent',
                border: `1px solid ${done[index] ? ACCENT : '#3b414c'}`,
                color: BG,
                fontFamily: '"JetBrains Mono", monospace',
                fontSize: 12,
                fontWeight: 800,
              }}>{done[index] ? '✓' : ''}</span>
              <span style={{ textDecoration: done[index] ? 'line-through' : 'none', textDecorationColor: ACCENT }}>
                {item}
              </span>
            </button>
          ))}
        </div>
      </div>

      <div style={{
        borderLeft: '1px solid #262b33',
        paddingLeft: 26,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'space-between',
      }}>
        <div>
          <IconFrame size={150} radius={0.22} />
          <div style={{ marginTop: 18, fontFamily: '"JetBrains Mono", monospace', fontSize: 11, color: MUTED, letterSpacing: '0.12em' }}>
            {completed}/{DESIGN_TODOS.length} VALIDÉ
          </div>
          <div style={{ height: 8, background: '#242934', marginTop: 10, overflow: 'hidden' }}>
            <div style={{
              width: `${(completed / DESIGN_TODOS.length) * 100}%`,
              height: '100%',
              background: ACCENT,
              transition: 'width .25s ease',
            }} />
          </div>
        </div>
        <div style={{ color: allDone ? ACCENT : MUTED, fontSize: 13, lineHeight: 1.4 }}>
          {allDone ? 'Checklist complète. Suppression visuelle en cours.' : 'La Todo reste visible tant qu’un point n’est pas terminé.'}
        </div>
      </div>
    </div>
  );
}

// ── Canvas mount --------------------------------------------------------------
function App() {
  return (
    <DesignCanvas defaultLayout="grid">
      <DCSection id="todo" title="Todo design" subtitle="Checklist interactive : elle disparaît automatiquement quand tout est coché.">
        <DCArtboard id="todo-icon-01" label="Todo · icône 01" width={780} height={420}>
          <DesignTodoBoard />
        </DCArtboard>
      </DCSection>

      <DCSection id="frame-deep" title="FRAME — variations gaming" subtitle="Tu aimes le 1. Voici 5 manières de le muscler pour le contexte foot pronostic, plus un test parmi des icônes type bookmaker.">
        {FRAME_VARIANTS.map(({ id, label, note, Comp }) => (
          <DCArtboard key={id} id={`fv-${id}`} label={label} width={720} height={560}>
            <SizeLadder Comp={Comp} label={label} note={note} />
          </DCArtboard>
        ))}
        <DCArtboard id="cat-orig"  label="Catégorie · FRAME origine"   width={880} height={300}><CategoryGrid Comp={IconFrame} /></DCArtboard>
        <DCArtboard id="cat-dot"   label="Catégorie · FRAME + dot"     width={880} height={300}><CategoryGrid Comp={IconFrameDot} /></DCArtboard>
        <DCArtboard id="cat-thick" label="Catégorie · FRAME épais"     width={880} height={300}><CategoryGrid Comp={IconFrameThick} /></DCArtboard>
        <DCArtboard id="cat-bar"   label="Catégorie · FRAME × barre"   width={880} height={300}><CategoryGrid Comp={IconFrameBar} /></DCArtboard>
      </DCSection>

      <DCSection id="overview" title="Vue d'ensemble" subtitle="Toutes les options côte à côte, taille moyenne + test de lisibilité petit.">
        <DCArtboard id="cmp" label="Comparatif" width={1120} height={420}>
          <Comparison />
        </DCArtboard>
      </DCSection>

      <DCSection id="ladders" title="Échelles" subtitle="Chaque option déclinée de 16px (favicon) à 220px (splash), plus deux contextes — réglages et notification.">
        {VARIANTS.map(({ id, label, note, Comp }) => (
          <DCArtboard key={id} id={`ladder-${id}`} label={label} width={720} height={560}>
            <SizeLadder Comp={Comp} label={label} note={note} />
          </DCArtboard>
        ))}
      </DCSection>

      <DCSection id="springboard" title="Sur l'écran d'accueil" subtitle="Mise en situation iOS : chaque option insérée au milieu des autres apps, à la taille réelle de l'écran d'accueil (60px).">
        {VARIANTS.map(({ id, label, Comp }) => (
          <DCArtboard key={id} id={`sb-${id}`} label={label} width={340} height={560}>
            <Springboard Comp={Comp} label={label} />
          </DCArtboard>
        ))}
      </DCSection>
    </DesignCanvas>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
