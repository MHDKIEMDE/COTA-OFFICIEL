// COTA — merchandise silhouettes (svg shapes for tees, hoodies, caps, totes, mugs).
// Each silhouette takes a fill color and renders its design via children, positioned
// absolutely within the silhouette's bounding box.

const { BG: zBG, BG2: zBG2, BG3: zBG3, LINE: zLINE, LINE2: zLINE2, INK: zINK, INK2: zINK2, DIM: zDIM, DIM2: zDIM2, ACCENT: zACCENT, font: zFONT } = window.COTA;

// ── T-SHIRT ───────────────────────────────────────────────────────────────────
// A clean adult tee silhouette. `view` is 'front' or 'back'.
function Tee({ color = '#0b0d10', stitch = 'rgba(255,255,255,0.06)', view = 'front', children, width = 320, height = 360 }) {
  const cream = '#e8e0c8';
  const isLight = color === cream || color === '#fffdf7' || color === '#f4efe2' || color === zACCENT;
  const seam = isLight ? 'rgba(0,0,0,0.08)' : stitch;
  return (
    <div style={{ position: 'relative', width, height }}>
      <svg viewBox="0 0 320 360" width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
        <defs>
          <linearGradient id={`tee-shade-${color}`} x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stopColor="rgba(0,0,0,0.18)" />
            <stop offset="0.1" stopColor="rgba(0,0,0,0)" />
            <stop offset="0.9" stopColor="rgba(0,0,0,0)" />
            <stop offset="1" stopColor="rgba(0,0,0,0.18)" />
          </linearGradient>
        </defs>
        {/* body */}
        <path d="
          M 110 24
          C 140 38 180 38 210 24
          L 240 32
          L 290 60
          L 310 105
          L 270 130
          L 256 122
          L 252 340
          L 68 340
          L 64 122
          L 50 130
          L 10 105
          L 30 60
          L 80 32
          Z
        " fill={color} />
        <path d="M 50 130 L 64 122 L 68 340 M 270 130 L 256 122 L 252 340 M 110 24 C 140 38 180 38 210 24" fill="none" stroke={seam} strokeWidth="1.2" />
        {/* shading */}
        <rect x="0" y="0" width="320" height="360" fill={`url(#tee-shade-${color})`} />
        {/* tag */}
        {view === 'back' && (
          <rect x="148" y="40" width="24" height="10" rx="1" fill={isLight ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.55)'} />
        )}
      </svg>
      <div style={{ position: 'absolute', inset: 0 }}>{children}</div>
    </div>
  );
}

// ── HOODIE ────────────────────────────────────────────────────────────────────
function Hoodie({ color = '#0b0d10', stitch = 'rgba(255,255,255,0.08)', view = 'front', children, width = 320, height = 380 }) {
  const isLight = color === '#e8e0c8' || color === zACCENT || color === '#f4efe2';
  const seam = isLight ? 'rgba(0,0,0,0.10)' : stitch;
  return (
    <div style={{ position: 'relative', width, height }}>
      <svg viewBox="0 0 320 380" width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
        <defs>
          <linearGradient id={`hood-shade-${color}`} x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stopColor="rgba(0,0,0,0.18)" />
            <stop offset="0.1" stopColor="rgba(0,0,0,0)" />
            <stop offset="0.9" stopColor="rgba(0,0,0,0)" />
            <stop offset="1" stopColor="rgba(0,0,0,0.18)" />
          </linearGradient>
        </defs>
        {/* hood drape (back of neck) */}
        <path d="M 105 50 C 130 30 190 30 215 50 C 230 70 230 90 215 100 C 195 90 125 90 105 100 C 90 90 90 70 105 50 Z" fill={color} stroke={seam} strokeWidth="1" />
        {/* body */}
        <path d="
          M 100 80
          L 220 80
          L 250 100
          L 300 130
          L 270 155
          L 252 145
          L 252 360
          L 68 360
          L 68 145
          L 50 155
          L 20 130
          L 70 100
          Z
        " fill={color} />
        <path d="M 50 155 L 68 145 L 68 360 M 270 155 L 252 145 L 252 360" fill="none" stroke={seam} strokeWidth="1.2" />
        {/* kangaroo pocket */}
        {view === 'front' && (
          <path d="M 100 230 L 220 230 L 240 290 L 80 290 Z M 130 250 L 130 270 L 190 270 L 190 250" fill="none" stroke={seam} strokeWidth="1.4" />
        )}
        {/* drawstrings */}
        {view === 'front' && (
          <>
            <line x1="145" y1="100" x2="148" y2="155" stroke={seam} strokeWidth="2" strokeLinecap="round" />
            <line x1="175" y1="100" x2="172" y2="155" stroke={seam} strokeWidth="2" strokeLinecap="round" />
          </>
        )}
        {/* neckline cut */}
        <path d="M 130 80 C 145 95 175 95 190 80" fill={zBG} stroke={seam} strokeWidth="1" />
        {/* shading */}
        <rect x="0" y="0" width="320" height="380" fill={`url(#hood-shade-${color})`} />
      </svg>
      <div style={{ position: 'absolute', inset: 0 }}>{children}</div>
    </div>
  );
}

// ── CAP (front view) ──────────────────────────────────────────────────────────
function Cap({ color = '#0b0d10', children, width = 320, height = 200 }) {
  return (
    <div style={{ position: 'relative', width, height }}>
      <svg viewBox="0 0 320 200" width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
        {/* crown */}
        <path d="M 40 130 C 40 60 280 60 280 130 L 40 130 Z" fill={color} />
        <path d="M 40 130 C 40 60 280 60 280 130" fill="none" stroke="rgba(255,255,255,0.08)" strokeWidth="1.2" />
        {/* panels lines */}
        <line x1="160" y1="65" x2="160" y2="130" stroke="rgba(255,255,255,0.06)" strokeWidth="1" />
        <line x1="100" y1="80" x2="120" y2="130" stroke="rgba(255,255,255,0.06)" strokeWidth="1" />
        <line x1="220" y1="80" x2="200" y2="130" stroke="rgba(255,255,255,0.06)" strokeWidth="1" />
        {/* visor */}
        <path d="M 30 130 C 100 165 220 165 290 130 L 280 138 C 220 175 100 175 40 138 Z" fill={color} />
        {/* button */}
        <circle cx="160" cy="62" r="3" fill="rgba(255,255,255,0.2)" />
      </svg>
      <div style={{ position: 'absolute', inset: 0 }}>{children}</div>
    </div>
  );
}

// ── TOTE BAG ──────────────────────────────────────────────────────────────────
function Tote({ color = '#0b0d10', children, width = 280, height = 320 }) {
  return (
    <div style={{ position: 'relative', width, height }}>
      <svg viewBox="0 0 280 320" width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
        {/* handles */}
        <path d="M 80 30 C 80 5 130 5 130 30" fill="none" stroke={color} strokeWidth="6" strokeLinecap="round" />
        <path d="M 150 30 C 150 5 200 5 200 30" fill="none" stroke={color} strokeWidth="6" strokeLinecap="round" />
        {/* body */}
        <path d="M 20 30 L 260 30 L 270 310 L 10 310 Z" fill={color} />
        {/* seam */}
        <path d="M 20 50 L 260 50" fill="none" stroke="rgba(255,255,255,0.08)" strokeWidth="1" />
      </svg>
      <div style={{ position: 'absolute', inset: 0, paddingTop: 50 }}>{children}</div>
    </div>
  );
}

// ── STICKER (die-cut) ─────────────────────────────────────────────────────────
// White peel border, drop shadow, slight rotation.
function Sticker({ shape = 'rounded', radius = 18, rotate = 0, children, width = 200, height = 200, padding = 8 }) {
  const r = shape === 'circle' ? '50%' : radius;
  return (
    <div style={{
      width, height, position: 'relative',
      transform: `rotate(${rotate}deg)`,
      filter: 'drop-shadow(0 8px 16px rgba(0,0,0,0.4))',
    }}>
      <div style={{
        position: 'absolute', inset: 0,
        background: 'white',
        borderRadius: r,
        padding,
        boxSizing: 'border-box',
      }}>
        <div style={{ width: '100%', height: '100%', borderRadius: shape === 'circle' ? '50%' : Math.max(0, radius - padding), overflow: 'hidden', position: 'relative' }}>
          {children}
        </div>
      </div>
    </div>
  );
}

// ── BUSINESS CARD ─────────────────────────────────────────────────────────────
function BizCard({ children, width = 360, height = 220 }) {
  return (
    <div style={{
      width, height, borderRadius: 8, overflow: 'hidden',
      boxShadow: '0 12px 30px rgba(0,0,0,0.4)',
      position: 'relative',
    }}>
      {children}
    </div>
  );
}

Object.assign(window, { Tee, Hoodie, Cap, Tote, Sticker, BizCard });
