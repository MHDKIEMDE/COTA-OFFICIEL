// COTA V2 — shared primitives for the post-onboarding, app extras, web, and admin sections.

const { BG: vBG, BG2: vBG2, BG3: vBG3, LINE: vLINE, LINE2: vLINE2, INK: vINK, INK2: vINK2, DIM: vDIM, DIM2: vDIM2, ACCENT: vACCENT, WIN: vWIN, LOSS: vLOSS, font: vFONT } = window.COTA;

// Slightly differentiated admin main background (a touch lighter than BG).
const ADMIN_MAIN = '#0f1117';

// ── Toggle ────────────────────────────────────────────────────────────────────
function Toggle({ on = false, size = 'md' }) {
  const w = size === 'sm' ? 32 : 42, h = size === 'sm' ? 18 : 24;
  return (
    <div style={{
      width: w, height: h, borderRadius: h / 2,
      background: on ? vACCENT : vLINE2,
      position: 'relative', transition: 'background 0.15s',
    }}>
      <div style={{
        position: 'absolute', top: 2, left: on ? w - h + 2 : 2,
        width: h - 4, height: h - 4, borderRadius: '50%',
        background: on ? vBG : vDIM, transition: 'left 0.15s',
      }} />
    </div>
  );
}

// ── Status badge (Actif / Inactif / En attente / Gagné / Perdu) ──────────────
function StatusBadge({ kind, label }) {
  const palette = {
    actif:   { bg: 'rgba(61,220,145,0.12)', fg: vWIN },
    inactif: { bg: 'rgba(255,91,58,0.12)',  fg: vLOSS },
    wait:    { bg: 'rgba(139,138,133,0.12)', fg: vDIM },
    won:     { bg: 'rgba(61,220,145,0.12)', fg: vWIN },
    lost:    { bg: 'rgba(255,91,58,0.12)',  fg: vLOSS },
    live:    { bg: 'rgba(232,255,54,0.12)', fg: vACCENT },
    premium: { bg: 'rgba(232,255,54,0.15)', fg: vACCENT },
    free:    { bg: 'rgba(139,138,133,0.12)', fg: vDIM },
  };
  const c = palette[kind] || palette.wait;
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 5,
      padding: '3px 8px', borderRadius: 4,
      background: c.bg, color: c.fg,
      fontFamily: vFONT.mono, fontSize: 9, fontWeight: 700, letterSpacing: '0.12em',
    }}>{label}</span>
  );
}

// ── Chip (league multi-select) ───────────────────────────────────────────────
function LeagueChip({ name, country, color, selected = false, onClick }) {
  return (
    <div onClick={onClick} style={{
      background: selected ? vBG3 : vBG2,
      border: `1px solid ${selected ? vACCENT : vLINE}`,
      borderRadius: 10, padding: '10px 12px',
      display: 'flex', alignItems: 'center', gap: 10,
      position: 'relative', cursor: 'pointer',
    }}>
      <div style={{ width: 4, height: 30, background: color, borderRadius: 2 }} />
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 12, color: vINK, fontWeight: 600, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{name}</div>
        <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.08em', marginTop: 1 }}>{country.toUpperCase()}</div>
      </div>
      {selected && (
        <div style={{ width: 16, height: 16, borderRadius: 8, background: vACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <svg width="9" height="9" viewBox="0 0 9 9"><path d="M1 4.5 L3.5 7 L8 2" stroke={vBG} strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
        </div>
      )}
    </div>
  );
}

// ── KPI Card (MaxLand-style with coloured icon badge) ────────────────────────
function KPICard({ icon, iconColor, value, label, delta, deltaColor }) {
  return (
    <div style={{
      padding: 16, background: vBG2, border: `1px solid ${vLINE}`, borderRadius: 12,
      display: 'flex', flexDirection: 'column', gap: 12,
    }}>
      <div style={{
        width: 40, height: 40, borderRadius: 10,
        background: `${iconColor}22`, color: iconColor,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        fontFamily: vFONT.title, fontSize: 16, fontWeight: 700,
      }}>{icon}</div>
      <div>
        <div style={{ fontFamily: vFONT.title, fontSize: 26, color: vINK, letterSpacing: '-0.02em', lineHeight: 1 }}>{value}</div>
        <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.15em', marginTop: 6 }}>{label}</div>
        {delta && <div style={{ fontFamily: vFONT.mono, fontSize: 10, color: deltaColor || vWIN, marginTop: 6 }}>↗ {delta}</div>}
      </div>
    </div>
  );
}

// ── Admin sidebar ─────────────────────────────────────────────────────────────
function AdminSidebar({ active = 'dashboard' }) {
  const items = [
    { id: 'dashboard',     label: 'Dashboard',     icon: '◧' },
    { id: 'users',         label: 'Utilisateurs',  icon: '◉' },
    { id: 'predictions',   label: 'Prédictions',   icon: '◐' },
    { id: 'coupons',       label: 'Coupons',       icon: '☑' },
    { id: 'subscriptions', label: 'Abonnements',   icon: '€' },
    { id: 'bookmakers',    label: 'Bookmakers',    icon: 'B' },
    { id: 'app-download',  label: 'App / Download', icon: '↓' },
    { id: 'leagues',       label: 'Compétitions',  icon: '⚽' },
    { id: 'settings',      label: 'Paramètres',    icon: '⚙' },
  ];
  return (
    <aside style={{
      position: 'absolute', left: 0, top: 0, bottom: 0, width: 240,
      background: vBG2, borderRight: `1px solid ${vLINE}`,
      padding: '24px 16px', display: 'flex', flexDirection: 'column',
      fontFamily: vFONT.ui,
    }}>
      {/* logo */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '0 4px 22px' }}>
        <AppIcon size={32} />
        <div>
          <Wordmark size={18} underline={false} />
          <div style={{ fontFamily: vFONT.mono, fontSize: 8, color: vDIM, letterSpacing: '0.18em', marginTop: 1 }}>ADMIN</div>
        </div>
      </div>

      {/* admin user */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '12px', background: vBG3, borderRadius: 10, border: `1px solid ${vLINE2}` }}>
        <div style={{ width: 32, height: 32, borderRadius: 16, background: vACCENT, color: vBG, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: vFONT.title, fontSize: 12 }}>KB</div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontSize: 12, color: vINK, fontWeight: 600 }}>Karim B.</div>
          <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.1em' }}>SUPER ADMIN</div>
        </div>
      </div>

      <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM2, letterSpacing: '0.18em', marginTop: 22, marginBottom: 8, padding: '0 4px' }}>MENU</div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 2, flex: 1 }}>
        {items.map(it => {
          const on = it.id === active;
          return (
            <div key={it.id} style={{
              padding: '10px 12px', borderRadius: 8,
              background: on ? vBG3 : 'transparent',
              fontFamily: vFONT.mono, fontSize: 11, letterSpacing: '0.08em',
              color: on ? vACCENT : vDIM,
              borderLeft: on ? `2px solid ${vACCENT}` : '2px solid transparent',
              display: 'flex', alignItems: 'center', gap: 10,
            }}>
              <span style={{ width: 14, textAlign: 'center', fontSize: 12, opacity: 0.85 }}>{it.icon}</span>
              {it.label.toUpperCase()}
            </div>
          );
        })}
      </div>

      <div style={{ marginTop: 16, padding: 12, borderRadius: 8, background: vBG3, border: `1px solid ${vLINE2}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
          <span style={{ width: 7, height: 7, borderRadius: 4, background: vWIN }} />
          <span style={{ fontFamily: vFONT.mono, fontSize: 9, color: vWIN, letterSpacing: '0.15em' }}>API OK</span>
        </div>
        <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.1em', marginTop: 4 }}>v1.0.4 · 09:30 UTC</div>
      </div>
    </aside>
  );
}

// ── Admin topbar ─────────────────────────────────────────────────────────────
function AdminTopbar({ breadcrumb, title, actions }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '20px 28px', borderBottom: `1px solid ${vLINE}`, background: ADMIN_MAIN, position: 'sticky', top: 0, zIndex: 5 }}>
      <div>
        <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.15em' }}>{breadcrumb}</div>
        <div style={{ fontFamily: vFONT.title, fontSize: 22, color: vINK, letterSpacing: '-0.02em', marginTop: 4 }}>{title}</div>
      </div>
      <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
        {actions}
        <button style={{ width: 36, height: 36, borderRadius: 18, background: vBG2, border: `1px solid ${vLINE2}`, color: vINK, position: 'relative' }}>
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style={{ display: 'block', margin: 'auto' }}>
            <path d="M3 6 a5 5 0 0 1 10 0 v3 l1.5 2 H1.5 L3 9 Z" stroke={vINK} strokeWidth="1.4"/>
          </svg>
          <span style={{ position: 'absolute', top: 7, right: 8, width: 6, height: 6, borderRadius: 3, background: vACCENT }} />
        </button>
        <div style={{ width: 36, height: 36, borderRadius: 18, background: vACCENT, color: vBG, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: vFONT.title, fontSize: 12 }}>KB</div>
      </div>
    </div>
  );
}

// ── Admin table primitives ───────────────────────────────────────────────────
function AdminTable({ columns, rows }) {
  return (
    <div style={{ background: vBG2, border: `1px solid ${vLINE}`, borderRadius: 12, overflow: 'hidden' }}>
      {/* header */}
      <div style={{
        display: 'grid', gridTemplateColumns: columns.map(c => c.w || '1fr').join(' '),
        padding: '12px 16px', borderBottom: `1px solid ${vLINE}`,
        background: vBG3,
      }}>
        {columns.map((c, i) => (
          <div key={i} style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.15em', textAlign: c.align || 'left' }}>{c.label}</div>
        ))}
      </div>
      {/* rows */}
      {rows.map((r, i) => (
        <div key={i} style={{
          display: 'grid', gridTemplateColumns: columns.map(c => c.w || '1fr').join(' '),
          padding: '14px 16px', borderBottom: i < rows.length - 1 ? `1px solid ${vLINE}` : 'none',
          alignItems: 'center', fontSize: 12, color: vINK,
        }}>
          {r.map((cell, j) => (
            <div key={j} style={{ textAlign: columns[j].align || 'left', fontFamily: columns[j].mono ? vFONT.mono : vFONT.ui }}>{cell}</div>
          ))}
        </div>
      ))}
    </div>
  );
}

// ── Bottom bar with text + button (for app banners) ──────────────────────────
function DownloadBanner({ variant = 1, onClose }) {
  if (variant === 1) {
    return (
      <div style={{
        background: vACCENT, color: vBG,
        padding: '10px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12,
        fontFamily: vFONT.ui,
      }}>
        <button onClick={onClose} style={{ background: 'transparent', border: 'none', color: vBG, fontSize: 16, padding: 0, lineHeight: 1 }}>×</button>
        <div style={{ flex: 1, fontSize: 12, fontWeight: 600 }}>Pour une meilleure expérience</div>
        <button style={{ background: vBG, color: vACCENT, border: 'none', padding: '7px 12px', borderRadius: 6, fontFamily: vFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>TÉLÉCHARGER L'APP</button>
      </div>
    );
  }
  if (variant === 2) {
    return (
      <div style={{
        background: 'rgba(21,24,29,0.85)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)',
        color: vINK, padding: '10px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12,
        border: `1px solid ${vLINE2}`, fontFamily: vFONT.ui,
      }}>
        <button onClick={onClose} style={{ background: 'transparent', border: 'none', color: vDIM, fontSize: 16, padding: 0, lineHeight: 1 }}>×</button>
        <div style={{ flex: 1, fontSize: 12 }}><span style={{ fontWeight: 600 }}>COTA</span> est disponible sur iOS & Android</div>
        <button style={{ background: vACCENT, color: vBG, border: 'none', padding: '7px 12px', borderRadius: 6, fontFamily: vFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>OUVRIR</button>
      </div>
    );
  }
  // variant 3: full with QR
  return (
    <div style={{
      background: vBG2, border: `1px solid ${vLINE2}`,
      padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12,
      fontFamily: vFONT.ui,
    }}>
      <button onClick={onClose} style={{ background: 'transparent', border: 'none', color: vDIM, fontSize: 16, padding: 0, lineHeight: 1 }}>×</button>
      <AppIcon size={36} />
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 12, fontWeight: 700, color: vINK }}>Télécharge l'app COTA</div>
        <div style={{ fontFamily: vFONT.mono, fontSize: 9, color: vDIM, letterSpacing: '0.08em', marginTop: 2 }}>Coupon IA · 9h30 chaque jour</div>
      </div>
      <div style={{ width: 50, height: 50, background: '#fff', borderRadius: 4, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        {window.QRCode ? <window.QRCode size={42} /> : <span style={{ color: '#0b0d10', fontSize: 9, fontFamily: vFONT.mono }}>QR</span>}
      </div>
    </div>
  );
}

// ── Simple URL bar (chrome simulation) ───────────────────────────────────────
function URLBar({ url = 'cota.app' }) {
  return (
    <div style={{
      background: '#2c2c2e',
      borderBottom: '1px solid #1c1c1e',
      padding: '8px 12px 6px',
      display: 'flex', alignItems: 'center', gap: 8,
      position: 'absolute', top: 0, left: 0, right: 0, zIndex: 100,
    }}>
      <div style={{ display: 'flex', gap: 4 }}>
        <span style={{ width: 8, height: 8, borderRadius: 4, background: '#666' }} />
        <span style={{ width: 8, height: 8, borderRadius: 4, background: '#666' }} />
      </div>
      <div style={{ flex: 1, background: '#1c1c1e', borderRadius: 10, padding: '4px 10px', fontFamily: '-apple-system, system-ui', fontSize: 11, color: '#aaa', display: 'flex', alignItems: 'center', gap: 6 }}>
        <svg width="9" height="9" viewBox="0 0 9 9"><rect x="2" y="4" width="5" height="4" rx="0.5" stroke="#aaa" fill="none" strokeWidth="0.8" /><path d="M3 4 v-1 a1.5 1.5 0 0 1 3 0 v1" stroke="#aaa" fill="none" strokeWidth="0.8" /></svg>
        {url}
      </div>
      <span style={{ color: '#666', fontSize: 14 }}>⋮</span>
    </div>
  );
}

// ── Mini sparkline ───────────────────────────────────────────────────────────
function Sparkline({ data, color = vACCENT, width = 280, height = 60, fill = true }) {
  const max = Math.max(...data);
  const min = Math.min(...data);
  const range = max - min || 1;
  const step = width / (data.length - 1);
  const points = data.map((d, i) => [i * step, height - ((d - min) / range) * height * 0.9 - height * 0.05]);
  const path = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p[0]} ${p[1]}`).join(' ');
  return (
    <svg width={width} height={height} style={{ display: 'block' }}>
      {fill && (
        <>
          <defs>
            <linearGradient id={`spark-${color.replace(/[^a-z0-9]/gi, '')}`} x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stopColor={color} stopOpacity="0.3" />
              <stop offset="100%" stopColor={color} stopOpacity="0" />
            </linearGradient>
          </defs>
          <path d={`${path} L ${width} ${height} L 0 ${height} Z`} fill={`url(#spark-${color.replace(/[^a-z0-9]/gi, '')})`} />
        </>
      )}
      <path d={path} stroke={color} strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

// ── Bar chart (admin) ────────────────────────────────────────────────────────
function BarChart({ data, width = 480, height = 200, max }) {
  // data: [{ won, lost, pending }]
  const cmax = max || Math.max(...data.map(d => d.won + d.lost + (d.pending || 0)));
  const barWidth = (width - 40) / data.length;
  const innerW = barWidth * 0.7;
  return (
    <svg width={width} height={height} style={{ display: 'block' }}>
      {[0.25, 0.5, 0.75, 1].map(p => (
        <line key={p} x1={28} x2={width} y1={height - 24 - (height - 40) * p} y2={height - 24 - (height - 40) * p} stroke={vLINE} strokeDasharray="2 3" />
      ))}
      {data.map((d, i) => {
        const x = 28 + i * barWidth + (barWidth - innerW) / 2;
        const total = d.won + d.lost + (d.pending || 0);
        const sc = (v) => (v / cmax) * (height - 40);
        let yCursor = height - 24;
        const bars = [];
        if (d.won) { yCursor -= sc(d.won); bars.push(<rect key="w" x={x} y={yCursor} width={innerW} height={sc(d.won)} fill={vWIN} rx="2" />); }
        if (d.lost) { yCursor -= sc(d.lost); bars.push(<rect key="l" x={x} y={yCursor} width={innerW} height={sc(d.lost)} fill={vLOSS} rx="2" />); }
        if (d.pending) { yCursor -= sc(d.pending); bars.push(<rect key="p" x={x} y={yCursor} width={innerW} height={sc(d.pending)} fill={vDIM2} rx="2" />); }
        return (
          <g key={i}>
            {bars}
            {i % 5 === 0 && <text x={x + innerW / 2} y={height - 8} textAnchor="middle" fontFamily={vFONT.mono} fontSize="9" fill={vDIM2} letterSpacing="0.05em">{d.label}</text>}
          </g>
        );
      })}
    </svg>
  );
}

// ── Donut chart ──────────────────────────────────────────────────────────────
function Donut({ segments, size = 160, stroke = 18 }) {
  const r = (size - stroke) / 2;
  const c = 2 * Math.PI * r;
  const total = segments.reduce((s, v) => s + v.value, 0);
  let cursor = 0;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} style={{ transform: 'rotate(-90deg)' }}>
      <circle cx={size / 2} cy={size / 2} r={r} fill="none" stroke={vLINE2} strokeWidth={stroke} />
      {segments.map((s, i) => {
        const len = (s.value / total) * c;
        const start = cursor;
        cursor += len;
        return (
          <circle key={i} cx={size / 2} cy={size / 2} r={r} fill="none"
            stroke={s.color} strokeWidth={stroke}
            strokeDasharray={`${len} ${c - len}`}
            strokeDashoffset={-start} />
        );
      })}
    </svg>
  );
}

window.V2 = { ADMIN_MAIN };

Object.assign(window, {
  Toggle, StatusBadge, LeagueChip, KPICard, AdminSidebar, AdminTopbar,
  AdminTable, DownloadBanner, URLBar, Sparkline, BarChart, Donut,
});
