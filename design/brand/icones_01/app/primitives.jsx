// COTA — shared UI primitives.
// All exported on window so they're available across script tags.

const { BG, BG2, BG3, LINE, LINE2, INK, INK2, DIM, DIM2, ACCENT, ACCENT_DIM, WIN, LOSS, font } = window.COTA;

// ── Icon (FRAME × UNDERSCORE) ─────────────────────────────────────────────────
function AppIcon({ size = 48, radius = 0.18 }) {
  const r = size * radius;
  return (
    <svg width={size} height={size} viewBox="0 0 1024 1024">
      <rect width="1024" height="1024" rx={r * (1024 / size)} fill={BG} />
      <rect x="69" y="69" width="886" height="886" rx="102" fill="none" stroke={ACCENT} strokeWidth="26" />
      <text x="512" y="696" textAnchor="middle" fontFamily={font.title} fontWeight="900" fontSize="594" fill={INK} letterSpacing="-20">C</text>
      <rect x="307" y="809" width="348" height="36" fill={ACCENT} />
      <rect x="655" y="809" width="41" height="36" fill={ACCENT} />
    </svg>
  );
}

// ── Wordmark (small, inline) ──────────────────────────────────────────────────
function Wordmark({ size = 22, color = INK, underline = true }) {
  return (
    <span style={{
      fontFamily: font.title, fontSize: size, lineHeight: 1, letterSpacing: '-0.04em',
      color, display: 'inline-flex', alignItems: 'baseline', position: 'relative',
    }}>
      COTA
      {underline && (
        <span style={{
          position: 'absolute', left: 0, right: 0, bottom: -4, height: 2, background: ACCENT,
        }} />
      )}
    </span>
  );
}

// ── Team badge ────────────────────────────────────────────────────────────────
function TeamBadge({ code, color = '#222', text = '#fff', size = 36 }) {
  return (
    <div style={{
      width: size, height: size, borderRadius: size * 0.22,
      background: color, color: text,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontFamily: font.title, fontSize: size * 0.36, letterSpacing: '-0.02em',
      flexShrink: 0,
    }}>{code}</div>
  );
}

// ── Confidence ring ───────────────────────────────────────────────────────────
function ConfidenceRing({ value = 87, size = 88, stroke = 6, label = 'CONFIANCE' }) {
  const r = (size - stroke) / 2;
  const c = 2 * Math.PI * r;
  const dash = (value / 100) * c;
  const high = value >= 80;
  return (
    <div style={{ position: 'relative', width: size, height: size }}>
      <svg width={size} height={size} style={{ transform: 'rotate(-90deg)' }}>
        <circle cx={size/2} cy={size/2} r={r} fill="none" stroke={LINE2} strokeWidth={stroke} />
        <circle cx={size/2} cy={size/2} r={r} fill="none"
          stroke={high ? ACCENT : INK} strokeWidth={stroke}
          strokeDasharray={`${dash} ${c}`} strokeLinecap="round"
        />
      </svg>
      <div style={{
        position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column',
        alignItems: 'center', justifyContent: 'center',
      }}>
        <div style={{ fontFamily: font.mono, fontWeight: 700, fontSize: size * 0.28, color: INK, letterSpacing: '-0.02em' }}>{value}</div>
        <div style={{ fontFamily: font.mono, fontSize: size * 0.09, color: DIM, letterSpacing: '0.15em' }}>%</div>
      </div>
    </div>
  );
}

// ── Confidence bar (horizontal) ───────────────────────────────────────────────
function ConfidenceBar({ value, label, max = 100, color, height = 6 }) {
  const pct = Math.min(100, Math.max(0, (value / max) * 100));
  const high = pct >= 70;
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
      {label && (
        <div style={{ display: 'flex', justifyContent: 'space-between', fontFamily: font.mono, fontSize: 10, letterSpacing: '0.1em' }}>
          <span style={{ color: DIM }}>{label}</span>
          <span style={{ color: INK, fontWeight: 600 }}>{value}{max === 100 ? '%' : ''}</span>
        </div>
      )}
      <div style={{ height, background: LINE, borderRadius: height/2, overflow: 'hidden' }}>
        <div style={{ height: '100%', width: `${pct}%`, background: color || (high ? ACCENT : INK), borderRadius: height/2, transition: 'width .4s' }} />
      </div>
    </div>
  );
}

// ── Criterion row (one of the 9) ──────────────────────────────────────────────
function CriterionRow({ index, name, value, signal = 'neutral', detail }) {
  const sigColor = signal === 'pro' ? ACCENT : signal === 'con' ? LOSS : INK;
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 0', borderBottom: `1px solid ${LINE}` }}>
      <div style={{ fontFamily: font.mono, fontSize: 10, color: DIM2, width: 18 }}>{String(index).padStart(2, '0')}</div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, color: INK, fontWeight: 500 }}>{name}</div>
        {detail && <div style={{ fontSize: 11, color: DIM, marginTop: 1 }}>{detail}</div>}
      </div>
      <div style={{ fontFamily: font.mono, fontSize: 12, fontWeight: 700, color: sigColor, letterSpacing: '0.05em' }}>{value}</div>
    </div>
  );
}

// ── Odds chip ─────────────────────────────────────────────────────────────────
function OddsChip({ value, prediction, size = 'md', highlight = false }) {
  const small = size === 'sm';
  const fs = small ? 11 : 13;
  return (
    <div style={{
      display: 'inline-flex', alignItems: 'center', gap: 8,
      padding: small ? '5px 9px' : '7px 11px',
      borderRadius: 999,
      background: highlight ? ACCENT : 'transparent',
      border: highlight ? 'none' : `1px solid ${LINE2}`,
      color: highlight ? BG : INK,
      fontFamily: font.mono, fontSize: fs, fontWeight: 700, letterSpacing: '0.04em',
    }}>
      {prediction && <span style={{ opacity: highlight ? 0.7 : 0.6, fontWeight: 500 }}>{prediction}</span>}
      <span>@{value}</span>
    </div>
  );
}

// ── Pill ──────────────────────────────────────────────────────────────────────
function Pill({ children, color, bg, border }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 6,
      padding: '4px 9px', borderRadius: 999,
      background: bg || 'transparent',
      border: border ? `1px solid ${border}` : 'none',
      color: color || INK,
      fontFamily: font.mono, fontSize: 10, fontWeight: 600, letterSpacing: '0.1em',
      whiteSpace: 'nowrap',
    }}>{children}</span>
  );
}

// ── Bottom nav ────────────────────────────────────────────────────────────────
function BottomNav({ active = 0 }) {
  const items = [
    { label: 'Aujourd\'hui', icon: 'home' },
    { label: 'Coupon',       icon: 'coupon' },
    { label: 'Historique',   icon: 'history' },
    { label: 'Profil',       icon: 'profile' },
  ];
  const Glyph = ({ kind, on }) => {
    const stroke = on ? ACCENT : DIM;
    const sw = 1.8;
    return (
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
        {kind === 'home' && (
          <>
            <path d="M3 11 L11 3 L19 11" stroke={stroke} strokeWidth={sw} strokeLinejoin="round"/>
            <path d="M5 10 V18 H17 V10" stroke={stroke} strokeWidth={sw} strokeLinejoin="round"/>
          </>
        )}
        {kind === 'coupon' && (
          <>
            <rect x="3" y="5" width="16" height="14" rx="2" stroke={stroke} strokeWidth={sw}/>
            <path d="M3 9 H19" stroke={stroke} strokeWidth={sw}/>
            <path d="M7 13 H15 M7 16 H12" stroke={stroke} strokeWidth={sw} strokeLinecap="round"/>
          </>
        )}
        {kind === 'history' && (
          <>
            <circle cx="11" cy="11" r="8" stroke={stroke} strokeWidth={sw}/>
            <path d="M11 6 V11 L14 13" stroke={stroke} strokeWidth={sw} strokeLinecap="round"/>
          </>
        )}
        {kind === 'profile' && (
          <>
            <circle cx="11" cy="8" r="3.5" stroke={stroke} strokeWidth={sw}/>
            <path d="M4 19 C4 14.5 17 14.5 18 19" stroke={stroke} strokeWidth={sw} strokeLinecap="round"/>
          </>
        )}
      </svg>
    );
  };
  return (
    <div style={{
      position: 'absolute', bottom: 0, left: 0, right: 0,
      background: BG, borderTop: `1px solid ${LINE}`,
      padding: '10px 8px 28px', display: 'flex', justifyContent: 'space-around',
    }}>
      {items.map((it, i) => (
        <div key={it.label} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4, flex: 1 }}>
          <Glyph kind={it.icon} on={i === active} />
          <div style={{ fontFamily: font.mono, fontSize: 9, letterSpacing: '0.1em', color: i === active ? ACCENT : DIM }}>{it.label.toUpperCase()}</div>
        </div>
      ))}
    </div>
  );
}

// ── App header (with COTA wordmark + bell) ────────────────────────────────────
function AppHeader({ right }) {
  return (
    <div style={{
      padding: '14px 20px 12px', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      background: BG,
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
        <AppIcon size={28} />
        <Wordmark size={20} underline={false} />
      </div>
      {right || (
        <div style={{ display: 'flex', gap: 8 }}>
          <button style={{
            width: 36, height: 36, borderRadius: 18, background: BG2, border: `1px solid ${LINE}`, color: INK,
            display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative',
          }}>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M3 6 a5 5 0 0 1 10 0 v3 l1.5 2 H1.5 L3 9 Z" stroke={INK} strokeWidth="1.4"/>
              <path d="M6 13 a2 2 0 0 0 4 0" stroke={INK} strokeWidth="1.4"/>
            </svg>
            <span style={{ position: 'absolute', top: 8, right: 9, width: 6, height: 6, borderRadius: 3, background: ACCENT }} />
          </button>
        </div>
      )}
    </div>
  );
}

// ── Section header (inside-screen) ────────────────────────────────────────────
function SectionLabel({ children, right }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 20px', marginBottom: 10 }}>
      <div style={{ fontFamily: font.mono, fontSize: 10, letterSpacing: '0.18em', color: DIM }}>{children}</div>
      {right && <div style={{ fontFamily: font.mono, fontSize: 10, letterSpacing: '0.1em', color: ACCENT }}>{right}</div>}
    </div>
  );
}

Object.assign(window, {
  AppIcon, Wordmark, TeamBadge, ConfidenceRing, ConfidenceBar, CriterionRow,
  OddsChip, Pill, BottomNav, AppHeader, SectionLabel,
});
