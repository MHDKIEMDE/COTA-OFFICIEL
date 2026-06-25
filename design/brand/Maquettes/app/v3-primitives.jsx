// COTA V3 — sober token layer and shared primitives.
// Direction: DAZN editorial × The Athletic dark. Less yellow, lighter type, more breath.

const V3 = (() => {
  const t = window.COTA;
  return {
    BG:    t.BG,
    BG2:   t.BG2,
    BG3:   '#1a1c20',     // softer surface
    LINE:  'rgba(244,239,226,0.06)',  // hair-thin border
    LINE2: 'rgba(244,239,226,0.10)',
    INK:   t.INK,
    INK2:  'rgba(244,239,226,0.78)',
    DIM:   'rgba(244,239,226,0.48)',
    DIM2:  'rgba(244,239,226,0.30)',
    ACCENT: t.ACCENT,
    WIN:   '#5ad29a',     // softer green
    LOSS:  '#f06d4e',     // softer red
    NEUTRAL: 'rgba(244,239,226,0.55)',
    font: {
      title: 'Space Grotesk',           // sober: no Archivo Black by default
      hero:  '"Archivo Black", sans-serif', // reserved for big numbers only
      ui:    'Space Grotesk',
      mono:  'JetBrains Mono',
    },
  };
})();
window.V3 = V3;

// ── Screen scaffold ──────────────────────────────────────────────────────────
function V3Screen({ children, bg }) {
  return (
    <div style={{ height: '100%', background: bg || V3.BG, color: V3.INK, fontFamily: V3.font.ui, position: 'relative' }}>
      {children}
    </div>
  );
}

// ── Sober header (no big monogram, no underline) ─────────────────────────────
function V3Header({ title, back, right, subtitle }) {
  return (
    <div style={{ padding: '52px 22px 14px', background: V3.BG, position: 'sticky', top: 0, zIndex: 5 }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        {back && (
          <button style={{ width: 32, height: 32, borderRadius: 16, background: 'transparent', border: 'none', color: V3.INK, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 0 }}>
            <svg width="14" height="14" viewBox="0 0 14 14"><path d="M9 2 L4 7 L9 12" stroke={V3.INK} strokeWidth="1.5" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
          </button>
        )}
        <div style={{ flex: 1 }}>
          {subtitle && <div style={{ fontSize: 11, color: V3.DIM, fontWeight: 500, marginBottom: 2 }}>{subtitle}</div>}
          <div style={{ fontSize: 22, fontWeight: 500, color: V3.INK, letterSpacing: '-0.01em' }}>{title}</div>
        </div>
        {right}
      </div>
    </div>
  );
}

// ── Sober list row ───────────────────────────────────────────────────────────
function V3Row({ label, value, sub, onPress, danger, chevron = true, leading, trailing }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 0', borderBottom: `1px solid ${V3.LINE}` }}>
      {leading && <div>{leading}</div>}
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 14, color: danger ? V3.LOSS : V3.INK, fontWeight: 400 }}>{label}</div>
        {sub && <div style={{ fontSize: 12, color: V3.DIM, marginTop: 3 }}>{sub}</div>}
      </div>
      {trailing}
      {value !== undefined && <div style={{ fontSize: 13, color: V3.DIM, fontWeight: 400 }}>{value}</div>}
      {chevron && !trailing && (
        <svg width="9" height="9" viewBox="0 0 9 9"><path d="M3 1 L6 4.5 L3 8" stroke={V3.DIM2} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
      )}
    </div>
  );
}

// ── Section label (no caps, no tracking, just soft uppercase mono) ───────────
function V3Section({ label, action, children }) {
  return (
    <div style={{ padding: '22px 22px 0' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 10 }}>
        <div style={{ fontSize: 11, color: V3.DIM, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em' }}>{label}</div>
        {action && <div style={{ fontSize: 11, color: V3.INK2, fontWeight: 500 }}>{action}</div>}
      </div>
      {children}
    </div>
  );
}

// ── Soft button ──────────────────────────────────────────────────────────────
function V3Button({ children, primary, full = true, size = 'md', onClick }) {
  const sizes = {
    sm: { h: 36, fs: 12, px: 14 },
    md: { h: 48, fs: 14, px: 18 },
    lg: { h: 54, fs: 15, px: 22 },
  };
  const s = sizes[size];
  return (
    <button onClick={onClick} style={{
      width: full ? '100%' : 'auto', height: s.h,
      padding: `0 ${s.px}px`,
      background: primary ? V3.INK : 'transparent',
      color: primary ? V3.BG : V3.INK,
      border: primary ? 'none' : `1px solid ${V3.LINE2}`,
      borderRadius: 10,
      fontFamily: V3.font.ui, fontSize: s.fs, fontWeight: 500,
      cursor: 'pointer',
    }}>{children}</button>
  );
}

// ── Avatar (no decorative icons — just initials) ─────────────────────────────
function V3Avatar({ name, size = 40, accent = false }) {
  const initials = name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
  return (
    <div style={{
      width: size, height: size, borderRadius: size / 2,
      background: accent ? V3.ACCENT : V3.BG3,
      color: accent ? V3.BG : V3.INK2,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontSize: size * 0.36, fontWeight: 500, letterSpacing: '-0.02em',
      flexShrink: 0,
    }}>{initials}</div>
  );
}

// ── Bottom nav (sober, no glyphs heavy) ──────────────────────────────────────
function V3BottomNav({ active = 0 }) {
  const items = ['Aujourd\'hui', 'Coupon', 'Recherche', 'Profil'];
  const Icon = ({ kind, on }) => {
    const stroke = on ? V3.INK : V3.DIM;
    const sw = 1.4;
    return (
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        {kind === 0 && <><path d="M3 10 L10 3 L17 10" stroke={stroke} strokeWidth={sw}/><path d="M5 9 V17 H15 V9" stroke={stroke} strokeWidth={sw}/></>}
        {kind === 1 && <><rect x="3" y="5" width="14" height="12" rx="1.5" stroke={stroke} strokeWidth={sw}/><path d="M3 9 H17 M7 13 H13" stroke={stroke} strokeWidth={sw}/></>}
        {kind === 2 && <><circle cx="9" cy="9" r="5" stroke={stroke} strokeWidth={sw}/><path d="M13 13 L17 17" stroke={stroke} strokeWidth={sw} strokeLinecap="round"/></>}
        {kind === 3 && <><circle cx="10" cy="7" r="3" stroke={stroke} strokeWidth={sw}/><path d="M4 17 C5 13 15 13 16 17" stroke={stroke} strokeWidth={sw} strokeLinecap="round"/></>}
      </svg>
    );
  };
  return (
    <div style={{
      position: 'absolute', bottom: 0, left: 0, right: 0,
      background: V3.BG, borderTop: `1px solid ${V3.LINE}`,
      padding: '12px 8px 30px',
      display: 'flex', justifyContent: 'space-around',
    }}>
      {items.map((it, i) => (
        <div key={it} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4, flex: 1 }}>
          <Icon kind={i} on={i === active} />
          <div style={{ fontSize: 10, color: i === active ? V3.INK : V3.DIM, fontWeight: 500 }}>{it}</div>
        </div>
      ))}
    </div>
  );
}

// ── Number presenter — accepts a value to be displayed restrained ────────────
function V3Number({ value, color, size = 22 }) {
  return (
    <span style={{ fontFamily: V3.font.mono, fontSize: size, color: color || V3.INK, fontWeight: 500, letterSpacing: '-0.01em' }}>{value}</span>
  );
}

// ── Big hero number (only place Archivo Black is allowed) ────────────────────
function V3Hero({ value, color }) {
  return (
    <span style={{ fontFamily: V3.font.hero, fontSize: 64, color: color || V3.INK, letterSpacing: '-0.04em', lineHeight: 0.95, fontWeight: 900 }}>{value}</span>
  );
}

// ── Tab strip (sober — no underline gimmick) ─────────────────────────────────
function V3Tabs({ items, active = 0 }) {
  return (
    <div style={{ display: 'flex', gap: 22, padding: '0 22px', borderBottom: `1px solid ${V3.LINE}` }}>
      {items.map((t, i) => (
        <div key={t} style={{
          padding: '14px 0',
          fontSize: 13,
          color: i === active ? V3.INK : V3.DIM,
          fontWeight: 500,
          borderBottom: i === active ? `2px solid ${V3.INK}` : '2px solid transparent',
        }}>{t}</div>
      ))}
    </div>
  );
}

// ── Soft chip (for pills, filters) ────────────────────────────────────────────
function V3Chip({ label, on, accent }) {
  return (
    <span style={{
      padding: '6px 12px', borderRadius: 999,
      background: on ? (accent ? V3.ACCENT : V3.BG2) : 'transparent',
      color: on ? (accent ? V3.BG : V3.INK) : V3.DIM,
      border: on ? 'none' : `1px solid ${V3.LINE2}`,
      fontSize: 12, fontWeight: 500,
      whiteSpace: 'nowrap',
    }}>{label}</span>
  );
}

Object.assign(window, { V3Screen, V3Header, V3Row, V3Section, V3Button, V3Avatar, V3BottomNav, V3Number, V3Hero, V3Tabs, V3Chip });
