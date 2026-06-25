// COTA V5 — MaxLand-inspired admin & web.
// Dark sidebar kept, main area light cream, serif headlines, pill statuses,
// KPI cards with vertical color tab.

const V5 = {
  // Sidebar (kept dark, brand-consistent)
  SIDE_BG:   '#0d1117',
  SIDE_BG2:  '#15181d',
  SIDE_LINE: 'rgba(244,239,226,0.10)',

  // Main area — light
  MAIN_BG:   '#f4efe2',         // brand cream
  CARD_BG:   '#ffffff',
  ALT_BG:    '#fafaf3',
  LINE:      'rgba(11,13,16,0.08)',
  LINE2:     'rgba(11,13,16,0.14)',

  INK:       '#0b0d10',
  INK2:      '#2a2c30',
  DIM:       '#6b6c70',
  DIM2:      '#a0a09b',

  ACCENT:    '#e8ff36',         // brand yellow (sidebar / actif only)
  BLUE:      '#2563eb',         // CTA / link primary
  WIN:       '#0fb46b',
  WIN_BG:    '#e8f5ee',
  LOSS:      '#e63946',
  LOSS_BG:   '#fceaec',
  ORANGE:    '#f59e0b',
  ORANGE_BG: '#fcf2e0',
  ROSE:      '#ec4899',
  ROSE_BG:   '#fce7f1',
  PURPLE:    '#7c3aed',
  PURPLE_BG: '#f1ecfe',

  font: {
    serif: '"Fraunces", "DM Serif Display", Georgia, serif',
    ui:    '"Space Grotesk", system-ui, sans-serif',
    mono:  '"JetBrains Mono", monospace',
  },
};
window.V5 = V5;

// ── Sidebar (dark, brand) ────────────────────────────────────────────────────
function V5Sidebar({ active = 'dashboard' }) {
  const items = [
    { id: 'dashboard',     label: 'Dashboard',     icon: '◧' },
    { id: 'users',         label: 'Utilisateurs',  icon: '◉' },
    { id: 'predictions',   label: 'Prédictions',   icon: '◐' },
    { id: 'coupons',       label: 'Coupons',       icon: '✓' },
    { id: 'subscriptions', label: 'Abonnements',   icon: '€' },
    { id: 'bookmakers',    label: 'Bookmakers',    icon: 'B' },
    { id: 'app-download',  label: 'App / Download', icon: '↓' },
    { id: 'leagues',       label: 'Compétitions',  icon: '⚽' },
    { id: 'settings',      label: 'Paramètres',    icon: '⚙' },
  ];
  return (
    <aside style={{
      position: 'absolute', left: 0, top: 0, bottom: 0, width: 260,
      background: V5.SIDE_BG, borderRight: `1px solid ${V5.SIDE_LINE}`,
      padding: '28px 18px', display: 'flex', flexDirection: 'column',
      fontFamily: V5.font.ui, color: V5.MAIN_BG,
    }}>
      {/* Logo */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '0 6px 22px' }}>
        <AppIcon size={32} />
        <div>
          <div style={{ fontFamily: V5.font.serif, fontSize: 20, color: V5.ACCENT, letterSpacing: '-0.01em', fontWeight: 600 }}>COTA</div>
          <div style={{ fontFamily: V5.font.mono, fontSize: 9, color: 'rgba(244,239,226,0.45)', letterSpacing: '0.18em' }}>ADMIN</div>
        </div>
      </div>

      {/* Avatar block (MaxLand vibe — glowing circle) */}
      <div style={{ padding: '18px 0', textAlign: 'center', borderTop: `1px solid ${V5.SIDE_LINE}`, borderBottom: `1px solid ${V5.SIDE_LINE}`, position: 'relative' }}>
        <div style={{ position: 'relative', display: 'inline-block' }}>
          <div style={{
            width: 76, height: 76, borderRadius: 38,
            background: 'linear-gradient(135deg, #2c3340, #0d1117)',
            border: `2px solid ${V5.ACCENT}`,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            color: V5.ACCENT, fontFamily: V5.font.serif, fontSize: 26, fontWeight: 600,
            boxShadow: `0 0 32px rgba(232,255,54,0.20)`,
          }}>KB</div>
          <div style={{ position: 'absolute', bottom: 0, right: 0, width: 22, height: 22, borderRadius: 11, background: '#fff', border: `2px solid ${V5.SIDE_BG}`, display: 'flex', alignItems: 'center', justifyContent: 'center', color: V5.SIDE_BG, fontSize: 10 }}>📷</div>
        </div>
        <div style={{ fontFamily: V5.font.serif, fontSize: 18, color: V5.ACCENT, marginTop: 12, fontWeight: 600 }}>Karim B.</div>
        <div style={{ fontSize: 12, color: 'rgba(244,239,226,0.55)', marginTop: 2 }}>Paris, France</div>
      </div>

      {/* Menu */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 2, marginTop: 18, flex: 1 }}>
        {items.map(it => {
          const on = it.id === active;
          return (
            <div key={it.id} style={{
              padding: '11px 14px', borderRadius: 8,
              background: on ? V5.SIDE_BG2 : 'transparent',
              color: on ? V5.ACCENT : 'rgba(244,239,226,0.7)',
              fontSize: 13, fontWeight: 500,
              borderLeft: on ? `2px solid ${V5.ACCENT}` : '2px solid transparent',
              display: 'flex', alignItems: 'center', gap: 12,
            }}>
              <span style={{ width: 14, textAlign: 'center', fontSize: 13, opacity: 0.9 }}>{it.icon}</span>
              {it.label}
            </div>
          );
        })}
      </div>
    </aside>
  );
}

// ── Frame (sidebar + main with light bg) ─────────────────────────────────────
function V5Frame({ active, title, actions, children }) {
  return (
    <div style={{ width: '100%', height: '100%', background: V5.MAIN_BG, color: V5.INK, fontFamily: V5.font.ui, position: 'relative', overflow: 'hidden' }}>
      <V5Sidebar active={active} />
      <main style={{ marginLeft: 260, height: '100%', overflow: 'auto', padding: '36px 42px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 }}>
          <h1 style={{ fontFamily: V5.font.serif, fontSize: 32, color: V5.INK, fontWeight: 600, letterSpacing: '-0.01em', margin: 0 }}>{title}</h1>
          <div style={{ display: 'flex', gap: 10 }}>{actions}</div>
        </div>
        {children}
      </main>
    </div>
  );
}

// ── KPI Card (MaxLand: vertical colored tab on left) ─────────────────────────
function V5KPICard({ value, label, color, bg, icon }) {
  return (
    <div style={{
      position: 'relative', background: bg, borderRadius: 10, overflow: 'hidden',
      padding: '22px 22px 22px 56px', minHeight: 130, display: 'flex', flexDirection: 'column', justifyContent: 'space-between',
    }}>
      {/* Left vertical tab */}
      <div style={{
        position: 'absolute', left: 0, top: 0, bottom: 0, width: 36,
        background: color,
        clipPath: 'polygon(0 0, 100% 0, 100% 100%, 50% 88%, 0 100%)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>
        <span style={{ color: '#fff', fontSize: 18 }}>{icon}</span>
      </div>
      <div>
        <div style={{ fontFamily: V5.font.serif, fontSize: 32, color: V5.INK, lineHeight: 1, fontWeight: 700 }}>{value}</div>
      </div>
      <div style={{ fontSize: 13, color: V5.DIM, fontWeight: 500 }}>{label}</div>
    </div>
  );
}

// ── Pill status (MaxLand style) ──────────────────────────────────────────────
function V5Pill({ label, color, bg }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center',
      padding: '5px 14px', borderRadius: 4,
      background: bg, color,
      fontSize: 12, fontWeight: 500,
    }}>{label}</span>
  );
}

// ── Light table card ─────────────────────────────────────────────────────────
function V5Table({ columns, rows }) {
  return (
    <div style={{ background: V5.CARD_BG, borderRadius: 10, overflow: 'hidden', boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
      <div style={{
        display: 'grid', gridTemplateColumns: columns.map(c => c.w || '1fr').join(' '),
        padding: '16px 22px', borderBottom: `1px solid ${V5.LINE}`,
        background: V5.ALT_BG,
      }}>
        {columns.map((c, i) => (
          <div key={i} style={{ fontSize: 13, color: V5.INK, fontWeight: 600, textAlign: c.align || 'left' }}>{c.label}</div>
        ))}
      </div>
      {rows.map((r, i) => (
        <div key={i} style={{
          display: 'grid', gridTemplateColumns: columns.map(c => c.w || '1fr').join(' '),
          padding: '16px 22px', borderBottom: i < rows.length - 1 ? `1px solid ${V5.LINE}` : 'none',
          alignItems: 'center', fontSize: 13, color: V5.INK,
        }}>
          {r.map((cell, j) => (
            <div key={j} style={{ textAlign: columns[j].align || 'left' }}>{cell}</div>
          ))}
        </div>
      ))}
    </div>
  );
}

// ── Button (MaxLand: rectangle, primary blue or accent) ──────────────────────
function V5Button({ children, variant = 'primary', onClick, full = false }) {
  const styles = {
    primary: { bg: V5.BLUE,    fg: '#fff' },
    accent:  { bg: V5.ACCENT,  fg: V5.INK },
    dark:    { bg: V5.INK,     fg: '#fff' },
    ghost:   { bg: 'transparent', fg: V5.INK, border: `1px solid ${V5.LINE2}` },
  };
  const s = styles[variant];
  return (
    <button onClick={onClick} style={{
      padding: '12px 22px',
      background: s.bg, color: s.fg,
      border: s.border || 'none', borderRadius: 6,
      fontFamily: V5.font.ui, fontSize: 13, fontWeight: 600,
      cursor: 'pointer', width: full ? '100%' : 'auto',
    }}>{children}</button>
  );
}

// ── Avatar (light theme) ─────────────────────────────────────────────────────
function V5Avatar({ name, size = 36 }) {
  const initials = name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
  return (
    <div style={{
      width: size, height: size, borderRadius: size/2,
      background: V5.INK, color: V5.MAIN_BG,
      display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
      fontSize: size * 0.36, fontWeight: 600, fontFamily: V5.font.ui,
      flexShrink: 0,
    }}>{initials}</div>
  );
}

// ── Stars (MaxLand reviews) ──────────────────────────────────────────────────
function V5Stars({ value = 5, size = 14 }) {
  return (
    <span style={{ display: 'inline-flex', gap: 2 }}>
      {Array.from({ length: 5 }).map((_, i) => (
        <svg key={i} width={size} height={size} viewBox="0 0 12 12">
          <path d="M6 1 L7.5 4.5 L11 5 L8.5 7.5 L9 11 L6 9.5 L3 11 L3.5 7.5 L1 5 L4.5 4.5 Z" fill={i < value ? V5.ORANGE : V5.LINE2} />
        </svg>
      ))}
    </span>
  );
}

Object.assign(window, { V5Sidebar, V5Frame, V5KPICard, V5Pill, V5Table, V5Button, V5Avatar, V5Stars });
