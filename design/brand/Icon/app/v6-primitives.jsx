// COTA V6 — DAZN-ified.
// Rules:
//   1. NO JetBrains Mono anywhere except for odds (@1.65) and time codes.
//   2. NO "01 —" / "02 —" section prefixes.
//   3. NO tickers (or one max, very small).
//   4. NO ConfidenceRing → use horizontal bars or prose.
//   5. NO ALL CAPS labels with letter-spacing 0.18em+
//   6. Match posters are the hero, odds chip is small.
//   7. One pulse only — on the live match itself.

const V6 = {
  BG:     '#0b0d10',
  BG2:    '#13161b',
  BG3:    '#1c1f25',
  LINE:   'rgba(244,239,226,0.08)',
  LINE2:  'rgba(244,239,226,0.16)',
  INK:    '#f4efe2',
  INK2:   'rgba(244,239,226,0.82)',
  DIM:    'rgba(244,239,226,0.55)',
  DIM2:   'rgba(244,239,226,0.35)',
  ACCENT: '#e8ff36',
  WIN:    '#3ddc91',
  LOSS:   '#ff5b3a',
  font: {
    title: '"Archivo Black", sans-serif',
    ui:    '"Space Grotesk", system-ui, sans-serif',
    mono:  '"JetBrains Mono", monospace', // ONLY for odds + scores + time
  },
};
window.V6 = V6;

// ─────────────────────────────────────────────────────────────────────────────
// Primitives — sober, editorial.
// ─────────────────────────────────────────────────────────────────────────────

function V6AppHeader({ right }) {
  return (
    <div style={{ padding: '14px 20px 12px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: V6.BG }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
        <AppIcon size={26} />
        <Wordmark size={18} underline={false} />
      </div>
      {right}
    </div>
  );
}

function V6BottomNav({ active = 0 }) {
  const items = [
    { label: 'Accueil',     i: 0 },
    { label: 'Coupon',      i: 1 },
    { label: 'Historique',  i: 2 },
    { label: 'Profil',      i: 3 },
  ];
  const Icon = ({ kind, on }) => {
    const c = on ? V6.INK : V6.DIM;
    const sw = 1.5;
    return (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        {kind === 0 && <><path d="M3 10 L10 3 L17 10" stroke={c} strokeWidth={sw} strokeLinejoin="round"/><path d="M5 9 V17 H15 V9" stroke={c} strokeWidth={sw} strokeLinejoin="round"/></>}
        {kind === 1 && <><rect x="3" y="5" width="14" height="12" rx="1.5" stroke={c} strokeWidth={sw}/><path d="M3 9 H17 M7 13 H13" stroke={c} strokeWidth={sw}/></>}
        {kind === 2 && <><circle cx="10" cy="10" r="7" stroke={c} strokeWidth={sw}/><path d="M10 6 V10 L12.5 12" stroke={c} strokeWidth={sw} strokeLinecap="round"/></>}
        {kind === 3 && <><circle cx="10" cy="7" r="3" stroke={c} strokeWidth={sw}/><path d="M4 17 C5 13 15 13 16 17" stroke={c} strokeWidth={sw} strokeLinecap="round"/></>}
      </svg>
    );
  };
  return (
    <div style={{
      position: 'absolute', bottom: 0, left: 0, right: 0,
      background: V6.BG, borderTop: `1px solid ${V6.LINE}`,
      padding: '12px 8px 30px', display: 'flex', justifyContent: 'space-around',
    }}>
      {items.map(it => (
        <div key={it.label} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
          <Icon kind={it.i} on={it.i === active} />
          <span style={{ fontSize: 10, color: it.i === active ? V6.INK : V6.DIM, fontWeight: 500 }}>{it.label}</span>
        </div>
      ))}
    </div>
  );
}

function V6OddsChip({ value, label, prominent }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'baseline', gap: 5,
      padding: prominent ? '8px 14px' : '4px 10px',
      borderRadius: 6,
      background: prominent ? V6.ACCENT : 'transparent',
      border: prominent ? 'none' : `1px solid ${V6.LINE2}`,
      color: prominent ? V6.BG : V6.INK,
    }}>
      {label && <span style={{ fontSize: 10, fontWeight: 500, opacity: 0.7 }}>{label}</span>}
      <span style={{ fontFamily: V6.font.mono, fontSize: prominent ? 14 : 12, fontWeight: 700, letterSpacing: '-0.02em' }}>@{value}</span>
    </span>
  );
}

// Confidence as a thin horizontal bar with number — replaces ConfidenceRing.
function V6Confidence({ value, label = 'Confiance' }) {
  const high = value >= 80;
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between' }}>
        <span style={{ fontSize: 11, color: V6.DIM }}>{label}</span>
        <span style={{ fontSize: 13, color: V6.INK, fontWeight: 600 }}>{value}%</span>
      </div>
      <div style={{ height: 3, background: V6.LINE2, borderRadius: 2, overflow: 'hidden' }}>
        <div style={{ height: '100%', width: `${value}%`, background: high ? V6.ACCENT : V6.INK2 }} />
      </div>
    </div>
  );
}

// Diagonal match poster — kept from V2 (this part is very DAZN already).
function V6Poster({ home, away, height = 200, children, intensity = 1, dim = false }) {
  const h = window.TEAMS[home], a = window.TEAMS[away];
  return (
    <div style={{ position: 'relative', height, borderRadius: 12, overflow: 'hidden' }}>
      <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(105deg, ${h.color} 0%, ${h.color} 46%, ${a.color} 54%, ${a.color} 100%)`, opacity: intensity }} />
      <div style={{ position: 'absolute', inset: 0, background: dim ? 'linear-gradient(180deg, rgba(0,0,0,0.30), rgba(11,13,16,0.92))' : 'linear-gradient(180deg, rgba(0,0,0,0.20), rgba(11,13,16,0.78))' }} />
      {/* Subtle monogram bleed */}
      <div style={{ position: 'absolute', top: 6, left: 10, fontFamily: V6.font.title, fontSize: height * 0.62, lineHeight: 0.85, color: h.text, opacity: 0.10, letterSpacing: '-0.06em' }}>{h.short}</div>
      <div style={{ position: 'absolute', top: 6, right: 10, fontFamily: V6.font.title, fontSize: height * 0.62, lineHeight: 0.85, color: a.text, opacity: 0.10, letterSpacing: '-0.06em', textAlign: 'right' }}>{a.short}</div>
      {children}
    </div>
  );
}

function V6LiveDot() {
  return (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5 }}>
      <span style={{ width: 6, height: 6, borderRadius: 3, background: V6.ACCENT, animation: 'cota-live-pulse 1.4s ease-in-out infinite' }} />
      <span style={{ fontSize: 10, fontWeight: 600, color: V6.ACCENT, letterSpacing: '0.04em' }}>LIVE</span>
    </span>
  );
}

Object.assign(window, { V6AppHeader, V6BottomNav, V6OddsChip, V6Confidence, V6Poster, V6LiveDot });
