// COTA Web Dashboard — graphes SVG maison (responsive, sans librairie).
// PerfChart : barres / aire / ligne. SummaryBars : barres horizontales façon "Summary".

const { BG: cBG, BG2: cBG2, BG3: cBG3, LINE: cLINE, LINE2: cLINE2, INK: cINK, INK2: cINK2, DIM: cDIM, DIM2: cDIM2, ACCENT: cACCENT, WIN: cWIN, LOSS: cLOSS, font: cFONT } = window.COTA;

// Graphe de performance. mode = 'bars' | 'area' | 'line'. accent = couleur courbe.
function PerfChart({ data, mode = 'bars', accent = cACCENT, height = 230 }) {
  const W = 640, H = 260;
  const pL = 34, pR = 10, pT = 14, pB = 26;
  const innerW = W - pL - pR;
  const innerH = H - pT - pB;
  const n = data.length;

  // Cumulatif (pour aire/ligne).
  const cum = [];
  data.reduce((acc, v, i) => { const s = acc + v; cum[i] = s; return s; }, 0);

  const isCum = mode !== 'bars';
  const vals = isCum ? cum : data;
  const maxV = isCum ? Math.max(...cum, 1) : Math.max(...data.map(Math.abs), 1);
  const minV = isCum ? Math.min(0, ...cum) : -maxV;
  const span = maxV - minV || 1;

  const x = (i) => pL + (innerW / n) * (i + 0.5);
  const y = (v) => pT + innerH * (1 - (v - minV) / span);

  // Lignes de grille horizontales.
  const grid = [];
  const steps = 4;
  for (let s = 0; s <= steps; s++) {
    const gy = pT + (innerH / steps) * s;
    const gv = maxV - (span / steps) * s;
    grid.push({ gy, gv });
  }

  const gid = `cota-area-${mode}`;

  return (
    <svg viewBox={`0 0 ${W} ${H}`} width="100%" height={height} preserveAspectRatio="none" style={{ display: 'block', overflow: 'visible' }}>
      <defs>
        <linearGradient id={gid} x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stopColor={accent} stopOpacity="0.34" />
          <stop offset="100%" stopColor={accent} stopOpacity="0" />
        </linearGradient>
      </defs>

      {/* grille + labels Y */}
      {grid.map((g, i) => (
        <g key={i}>
          <line x1={pL} y1={g.gy} x2={W - pR} y2={g.gy} stroke={cLINE} strokeWidth="1" strokeDasharray="2 4" />
          <text x={pL - 8} y={g.gy + 3} textAnchor="end" fontFamily={cFONT.mono} fontSize="9" fill={cDIM2}>
            {Math.round(g.gv)}
          </text>
        </g>
      ))}

      {/* ligne du zéro (mode barres) */}
      {!isCum && (
        <line x1={pL} y1={y(0)} x2={W - pR} y2={y(0)} stroke={cLINE2} strokeWidth="1.4" />
      )}

      {/* BARRES */}
      {mode === 'bars' && data.map((v, i) => {
        const bw = (innerW / n) * 0.58;
        const cx = x(i);
        const y0 = y(0), yv = y(v);
        const top = Math.min(y0, yv), h = Math.abs(yv - y0);
        const col = v >= 0 ? accent : cLOSS;
        return (
          <rect key={i} x={cx - bw / 2} y={top} width={bw} height={Math.max(h, 1)} rx="2"
            fill={col} opacity={v >= 0 ? 0.92 : 0.8} />
        );
      })}

      {/* AIRE */}
      {mode === 'area' && (
        <>
          <path
            d={`M ${x(0)} ${y(cum[0])} ` + cum.map((v, i) => `L ${x(i)} ${y(v)}`).join(' ') +
               ` L ${x(n - 1)} ${y(minV)} L ${x(0)} ${y(minV)} Z`}
            fill={`url(#${gid})`} />
          <path
            d={`M ${x(0)} ${y(cum[0])} ` + cum.map((v, i) => `L ${x(i)} ${y(v)}`).join(' ')}
            fill="none" stroke={accent} strokeWidth="2.4" strokeLinejoin="round" strokeLinecap="round" />
        </>
      )}

      {/* LIGNE */}
      {mode === 'line' && (
        <>
          <path
            d={`M ${x(0)} ${y(cum[0])} ` + cum.map((v, i) => `L ${x(i)} ${y(v)}`).join(' ')}
            fill="none" stroke={accent} strokeWidth="2.4" strokeLinejoin="round" strokeLinecap="round" />
          {cum.map((v, i) => (i % 4 === 0 || i === n - 1) && (
            <circle key={i} cx={x(i)} cy={y(v)} r="3" fill={cBG} stroke={accent} strokeWidth="2" />
          ))}
        </>
      )}

      {/* labels X (1 sur 7) */}
      {data.map((v, i) => (i % 7 === 0) && (
        <text key={i} x={x(i)} y={H - 8} textAnchor="middle" fontFamily={cFONT.mono} fontSize="9" fill={cDIM2}>
          J{i + 1}
        </text>
      ))}
    </svg>
  );
}

// Barres horizontales du panneau "Résumé".
function SummaryBars({ items, max, toneMap }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
      {items.map((it) => {
        const pct = Math.min(100, (it.value / max) * 100);
        const col = toneMap[it.tone] || cINK;
        return (
          <div key={it.label}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
              <span style={{ fontSize: 13, color: cINK2 }}>{it.label}</span>
              <span style={{ fontFamily: cFONT.mono, fontSize: 13, fontWeight: 700, color: col, whiteSpace: 'nowrap' }}>{it.display}</span>
            </div>
            <div style={{ height: 8, background: cBG3, borderRadius: 4, overflow: 'hidden' }}>
              <div style={{ height: '100%', width: `${pct}%`, background: col, borderRadius: 4 }} />
            </div>
          </div>
        );
      })}
    </div>
  );
}

window.PerfChart = PerfChart;
window.SummaryBars = SummaryBars;
