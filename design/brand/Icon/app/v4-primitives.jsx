// COTA V4 · INVERSE 2030 — sober futurist sports betting.
// Inverted palette: cream is primary bg, dark is accent.

const V4 = {
  // Inverted
  BG:     '#f4efe2',          // primary cream (was secondary ink)
  BG2:    '#ece6d2',          // softer surface
  BG3:    '#dfd8c3',          // deeper surface
  INK:    '#0b0d10',          // primary text + dark accent (was bg)
  INK2:   '#3a3c40',
  DIM:    '#777670',
  DIM2:   '#a8a79f',
  LINE:   'rgba(11,13,16,0.10)',
  LINE2:  'rgba(11,13,16,0.18)',

  // Color system 2030
  YELLOW: '#e8ff36',          // block-only accent (no longer signal fine)
  BLUE:   '#1e1cff',          // signal IA (live, neural)
  MAGENTA: '#ff2e85',         // decisive moments
  CYAN:   '#00e0ff',          // data flow
  WIN:    '#0fb46b',          // muted on cream
  LOSS:   '#e63946',

  font: {
    title: '"Archivo Black", sans-serif',
    ui:    '"Space Grotesk", system-ui, sans-serif',
    mono:  '"JetBrains Mono", monospace',
  },
};
window.V4 = V4;

// ── Iridescent gradient used on hero / "signal" elements ─────────────────────
const IRIS = 'linear-gradient(135deg, #1e1cff 0%, #ff2e85 50%, #e8ff36 100%)';

// ── Mesh blob background (ambient noise of color) ────────────────────────────
function MeshAmbient({ tint = 'iris', opacity = 0.5 }) {
  return (
    <>
      <div style={{
        position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden',
      }}>
        <div style={{ position: 'absolute', top: -100, left: -80, width: 360, height: 360, borderRadius: '50%', background: V4.BLUE, opacity: opacity * 0.35, filter: 'blur(80px)' }} />
        <div style={{ position: 'absolute', bottom: -120, right: -100, width: 420, height: 420, borderRadius: '50%', background: V4.MAGENTA, opacity: opacity * 0.30, filter: 'blur(90px)' }} />
        <div style={{ position: 'absolute', top: '40%', right: -60, width: 240, height: 240, borderRadius: '50%', background: V4.YELLOW, opacity: opacity * 0.40, filter: 'blur(70px)' }} />
      </div>
      {/* Grain */}
      <div style={{
        position: 'absolute', inset: 0, pointerEvents: 'none', opacity: 0.6, mixBlendMode: 'multiply',
        background: 'repeating-radial-gradient(circle at 20% 30%, rgba(0,0,0,0.02) 0, rgba(0,0,0,0.02) 1px, transparent 1px, transparent 3px)',
      }} />
    </>
  );
}

// ── AI Orb — the personality of the app ──────────────────────────────────────
function AIOrb({ size = 120, pulsing = true, label }) {
  return (
    <div style={{ width: size, height: size, position: 'relative', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
      {/* outer halo */}
      <div style={{
        position: 'absolute', inset: -16, borderRadius: '50%',
        background: IRIS, opacity: 0.25, filter: 'blur(18px)',
        animation: pulsing ? 'v4-orb-halo 3s ease-in-out infinite' : 'none',
      }} />
      {/* iridescent ring */}
      <div style={{
        position: 'absolute', inset: 0, borderRadius: '50%',
        padding: 2, background: IRIS,
      }}>
        <div style={{ width: '100%', height: '100%', borderRadius: '50%', background: V4.BG }} />
      </div>
      {/* core */}
      <div style={{
        width: size - 24, height: size - 24, borderRadius: '50%',
        background: `radial-gradient(circle at 35% 30%, #fff 0%, ${V4.CYAN} 25%, ${V4.BLUE} 65%, ${V4.INK} 100%)`,
        position: 'relative',
        boxShadow: `inset 0 -8px 20px rgba(255,255,255,0.3), 0 0 30px ${V4.BLUE}55`,
      }}>
        {/* shine */}
        <div style={{ position: 'absolute', top: '14%', left: '24%', width: '36%', height: '20%', background: 'rgba(255,255,255,0.55)', borderRadius: '50%', filter: 'blur(3px)' }} />
      </div>
      {label && (
        <div style={{ position: 'absolute', bottom: -22, fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.25em' }}>{label}</div>
      )}
    </div>
  );
}

// ── Holo card — "floating" with iridescent border ────────────────────────────
function HoloCard({ children, glow, style = {}, padding = 18 }) {
  return (
    <div style={{
      position: 'relative', borderRadius: 18,
      background: V4.BG,
      boxShadow: `0 8px 24px rgba(11,13,16,0.06), 0 24px 60px rgba(11,13,16,0.08)`,
      ...style,
    }}>
      {glow && (
        <div style={{
          position: 'absolute', inset: -1, borderRadius: 18,
          padding: 1, background: IRIS,
          WebkitMask: 'linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0)',
          WebkitMaskComposite: 'xor', maskComposite: 'exclude',
          pointerEvents: 'none',
        }} />
      )}
      <div style={{ padding, position: 'relative', borderRadius: 18, overflow: 'hidden' }}>
        {children}
      </div>
    </div>
  );
}

// ── Holographic gauge ────────────────────────────────────────────────────────
function HoloGauge({ value = 87, size = 100, stroke = 6, label }) {
  const r = (size - stroke) / 2;
  const c = 2 * Math.PI * r;
  const dash = (value / 100) * c;
  return (
    <div style={{ position: 'relative', width: size, height: size, display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
      <svg width={size} height={size} style={{ transform: 'rotate(-90deg)', position: 'absolute' }}>
        <defs>
          <linearGradient id={`iris-${size}-${value}`} x1="0" x2="1">
            <stop offset="0%"   stopColor={V4.BLUE} />
            <stop offset="50%"  stopColor={V4.MAGENTA} />
            <stop offset="100%" stopColor={V4.YELLOW} />
          </linearGradient>
        </defs>
        <circle cx={size/2} cy={size/2} r={r} fill="none" stroke={V4.LINE} strokeWidth={stroke} />
        <circle cx={size/2} cy={size/2} r={r} fill="none"
          stroke={`url(#iris-${size}-${value})`} strokeWidth={stroke}
          strokeDasharray={`${dash} ${c}`} strokeLinecap="round"
        />
      </svg>
      <div style={{ textAlign: 'center', position: 'relative', zIndex: 1 }}>
        <div style={{ fontFamily: V4.font.title, fontSize: size * 0.30, color: V4.INK, lineHeight: 1, letterSpacing: '-0.04em' }}>{value}</div>
        <div style={{ fontFamily: V4.font.mono, fontSize: 8, color: V4.DIM, letterSpacing: '0.2em', marginTop: 4 }}>{label || '%'}</div>
      </div>
    </div>
  );
}

// ── Iridescent pill ──────────────────────────────────────────────────────────
function IrisPill({ children, dark }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 6,
      padding: '5px 12px', borderRadius: 999,
      background: dark ? V4.INK : 'transparent',
      color: dark ? V4.YELLOW : V4.INK,
      border: dark ? 'none' : `1px solid ${V4.LINE2}`,
      fontFamily: V4.font.mono, fontSize: 10, fontWeight: 600, letterSpacing: '0.15em',
    }}>{children}</span>
  );
}

// ── Voice/AI input prompt ────────────────────────────────────────────────────
function VoicePrompt({ text }) {
  return (
    <div style={{
      padding: '14px 16px', borderRadius: 999, background: V4.BG2,
      border: `1px solid ${V4.LINE2}`,
      display: 'flex', alignItems: 'center', gap: 12,
    }}>
      {/* mic indicator */}
      <div style={{ width: 28, height: 28, borderRadius: 14, background: V4.INK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
          <rect x="4.5" y="2" width="3" height="6" rx="1.5" fill={V4.YELLOW}/>
          <path d="M2.5 6 a3.5 3.5 0 0 0 7 0 M6 9.5 V11" stroke={V4.YELLOW} strokeWidth="1.2" strokeLinecap="round"/>
        </svg>
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.2em', marginBottom: 2 }}>DEMANDE À COTA</div>
        <div style={{ fontSize: 13, color: V4.INK }}>{text || '« Quels matchs as-tu sélectionnés ? »'}</div>
      </div>
      {/* audio levels */}
      <div style={{ display: 'flex', gap: 2, alignItems: 'flex-end', height: 18 }}>
        {[8, 14, 18, 12, 16, 10, 6].map((h, i) => (
          <div key={i} style={{ width: 2, height: h, background: V4.BLUE, borderRadius: 1, opacity: 0.6 + (i * 0.05) }} />
        ))}
      </div>
    </div>
  );
}

// ── Tickertape that flows iridescent ──────────────────────────────────────────
function DataTicker() {
  return (
    <div style={{
      padding: '8px 0',
      background: V4.INK, color: V4.YELLOW,
      overflow: 'hidden', whiteSpace: 'nowrap',
    }}>
      <div style={{ fontFamily: V4.font.mono, fontSize: 11, letterSpacing: '0.2em', display: 'inline-block', paddingLeft: 14 }}>
        ★ PSG–OM @1.65 · UCONF 87% — RMA–BAY @1.55 · UCONF 91% — LIV–ARS @1.78 · UCONF 76% — ASM–OL @1.42 · UCONF 68% — LIL–NIC @1.92 · UCONF 62% ★
      </div>
    </div>
  );
}

// Inject V4 keyframes
function V4Styles() {
  React.useEffect(() => {
    if (document.getElementById('v4-keyframes')) return;
    const s = document.createElement('style');
    s.id = 'v4-keyframes';
    s.textContent = `
      @keyframes v4-orb-halo { 0%, 100% { opacity: 0.22; transform: scale(1); } 50% { opacity: 0.45; transform: scale(1.08); } }
      @keyframes v4-iris-shift { 0% { background-position: 0% 50%; } 100% { background-position: 200% 50%; } }
      @keyframes v4-blink { 0%, 50% { opacity: 1; } 50.01%, 100% { opacity: 0.2; } }
      @keyframes v4-ticker-flow { from { transform: translateX(0); } to { transform: translateX(-50%); } }
      @keyframes v4-data-pulse { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }
    `;
    document.head.appendChild(s);
  }, []);
  return null;
}

Object.assign(window, { MeshAmbient, AIOrb, HoloCard, HoloGauge, IrisPill, VoicePrompt, DataTicker, V4Styles, IRIS_GRADIENT: IRIS });
