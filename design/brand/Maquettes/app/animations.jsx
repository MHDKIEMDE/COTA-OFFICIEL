// COTA — looping animations.
// Three self-contained animated boxes used in their own design-canvas artboards.

const { BG: aBG, BG2: aBG2, BG3: aBG3, LINE: aLINE, LINE2: aLINE2, INK: aINK, DIM: aDIM, DIM2: aDIM2, ACCENT: aACCENT, WIN: aWIN, font: aFONT } = window.COTA;

// Inject keyframes once.
const ANIM_CSS = `
@keyframes cota-ring-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes cota-dots-fade { 0%, 20% { opacity: 0.25; } 50% { opacity: 1; } 80%, 100% { opacity: 0.25; } }
@keyframes cota-flash-row {
  0%, 100% { background: transparent; color: ${aDIM}; }
  10%, 30% { background: rgba(232,255,54,0.10); color: ${aACCENT}; }
}
@keyframes cota-pick-slide {
  0%   { opacity: 0; transform: translateY(24px); }
  60%  { opacity: 1; transform: translateY(-2px); }
  100% { opacity: 1; transform: translateY(0); }
}
@keyframes cota-validate-pulse {
  0%   { transform: scale(0.6); opacity: 0; }
  20%  { transform: scale(1.08); opacity: 1; }
  35%  { transform: scale(1); opacity: 1; }
  85%  { transform: scale(1); opacity: 1; }
  100% { transform: scale(1); opacity: 0; }
}
@keyframes cota-ring-expand {
  0%   { transform: scale(0.4); opacity: 0.7; }
  100% { transform: scale(2.2); opacity: 0; }
}
@keyframes cota-live-pulse {
  0%, 100% { opacity: 1; }
  50%      { opacity: 0.35; }
}
@keyframes cota-ticker {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}
@keyframes cota-fade-cycle {
  0%, 90% { opacity: 1; }
  95%, 100% { opacity: 0; }
}
`;

function AnimStylesOnce() {
  React.useEffect(() => {
    if (document.getElementById('cota-anim-css')) return;
    const s = document.createElement('style');
    s.id = 'cota-anim-css';
    s.textContent = ANIM_CSS;
    document.head.appendChild(s);
  }, []);
  return null;
}

// ── 1 · SPLASH ANALYSE ────────────────────────────────────────────────────────
// COTA wordmark centered, rotating ring, 9 critères flashing in the corner.
function AnimSplash() {
  const criteria = ['FORME', 'H2H', 'DOM/EXT', 'BLESSURES', 'MÉTÉO', 'MARCHÉ', 'CARTONS', 'POSSESS.', 'XG'];
  return (
    <div style={{
      width: '100%', height: '100%', background: aBG, color: aINK,
      position: 'relative', overflow: 'hidden', fontFamily: aFONT.ui,
    }}>
      <AnimStylesOnce />

      {/* corners — 9 critères "processing" */}
      <div style={{ position: 'absolute', top: 20, left: 20, display: 'flex', flexDirection: 'column', gap: 6 }}>
        {criteria.slice(0, 5).map((c, i) => (
          <div key={c} style={{
            fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.15em',
            padding: '3px 8px', borderRadius: 4,
            animation: `cota-flash-row 2.4s ease-in-out infinite ${i * 0.18}s`,
          }}>0{i+1} · {c}</div>
        ))}
      </div>
      <div style={{ position: 'absolute', top: 20, right: 20, display: 'flex', flexDirection: 'column', gap: 6, alignItems: 'flex-end' }}>
        {criteria.slice(5).map((c, i) => (
          <div key={c} style={{
            fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.15em',
            padding: '3px 8px', borderRadius: 4,
            animation: `cota-flash-row 2.4s ease-in-out infinite ${(i + 5) * 0.18}s`,
          }}>0{i+6} · {c}</div>
        ))}
      </div>

      {/* center — ring + wordmark */}
      <div style={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 28 }}>
        <div style={{ position: 'relative', width: 200, height: 200 }}>
          <svg width="200" height="200" style={{ position: 'absolute', inset: 0, animation: 'cota-ring-spin 1.5s linear infinite' }}>
            <circle cx="100" cy="100" r="92" fill="none" stroke={aLINE2} strokeWidth="3" />
            <circle cx="100" cy="100" r="92" fill="none" stroke={aACCENT} strokeWidth="3" strokeLinecap="round" strokeDasharray="80 600" />
          </svg>
          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Wordmark size={44} underline={false} />
          </div>
        </div>
        <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.25em', display: 'flex', gap: 4 }}>
          ÉDITION EN COURS
          <span style={{ display: 'inline-flex', gap: 2 }}>
            <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0s', color: aACCENT }}>.</span>
            <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0.18s', color: aACCENT }}>.</span>
            <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0.36s', color: aACCENT }}>.</span>
          </span>
        </div>
      </div>

      {/* bottom — fake telemetry */}
      <div style={{ position: 'absolute', bottom: 20, left: 0, right: 0, display: 'flex', justifyContent: 'space-between', padding: '0 24px', fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.15em' }}>
        <span>v1.0.4</span>
        <span>247 ÉDITIONS SOURCES</span>
        <span>UCT 09:31</span>
      </div>
    </div>
  );
}

// ── 2 · CONFIDENCE REVEAL ─────────────────────────────────────────────────────
// Number rolls from 0 → 87, locks, pulses. Loops every 4s.
function AnimConfidenceReveal() {
  const target = 87;
  const [value, setValue] = React.useState(0);
  const [locked, setLocked] = React.useState(false);
  const [pulse, setPulse] = React.useState(false);

  React.useEffect(() => {
    let raf;
    let start = null;
    const cycleMs = 4000;
    const rollMs = 1800;

    function frame(t) {
      if (!start) start = t;
      const elapsed = (t - start) % cycleMs;
      if (elapsed < rollMs) {
        // ease-out
        const p = elapsed / rollMs;
        const eased = 1 - Math.pow(1 - p, 3);
        setValue(Math.floor(eased * target));
        setLocked(false);
        setPulse(false);
      } else if (elapsed < rollMs + 200) {
        setValue(target);
        setLocked(true);
        setPulse(true);
      } else {
        setValue(target);
        setLocked(true);
        setPulse(false);
      }
      raf = requestAnimationFrame(frame);
    }
    raf = requestAnimationFrame(frame);
    return () => cancelAnimationFrame(raf);
  }, []);

  const r = 90;
  const c = 2 * Math.PI * r;
  const dash = (value / 100) * c;
  const high = locked;

  return (
    <div style={{ width: '100%', height: '100%', background: aBG, color: aINK, position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: aFONT.ui }}>
      <AnimStylesOnce />

      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 22 }}>

        <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aDIM, letterSpacing: '0.2em' }}>
          {locked ? '✓  SCORE DE CONFIANCE' : 'CALCUL DU SCORE...'}
        </div>

        <div style={{ position: 'relative', width: 220, height: 220 }}>
          {/* expanding pulse */}
          {pulse && (
            <div style={{
              position: 'absolute', inset: 0, borderRadius: '50%',
              border: `2px solid ${aACCENT}`,
              animation: 'cota-ring-expand 0.8s ease-out',
            }} />
          )}
          <svg width="220" height="220" viewBox="0 0 220 220" style={{ transform: 'rotate(-90deg)' }}>
            <circle cx="110" cy="110" r={r} fill="none" stroke={aLINE2} strokeWidth="6" />
            <circle cx="110" cy="110" r={r} fill="none"
              stroke={high ? aACCENT : aINK} strokeWidth="6" strokeLinecap="round"
              strokeDasharray={`${dash} ${c}`}
              style={{ transition: locked ? 'stroke 0.2s' : 'none' }}
            />
          </svg>
          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'baseline', justifyContent: 'center' }}>
            <div style={{ fontFamily: aFONT.title, fontSize: 72, color: high ? aACCENT : aINK, letterSpacing: '-0.04em', lineHeight: 1, transition: 'color 0.2s' }}>{value}</div>
            <div style={{ fontFamily: aFONT.mono, fontSize: 16, color: aDIM, letterSpacing: '0.1em', marginLeft: 6 }}>%</div>
          </div>
        </div>

        {/* dynamic label */}
        <div style={{ height: 22, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
          {locked ? (
            <div style={{ fontFamily: aFONT.mono, fontSize: 12, color: aACCENT, letterSpacing: '0.15em', fontWeight: 700 }}>
              CONFIANCE FORTE
            </div>
          ) : (
            <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.15em' }}>
              LECTURE DES 9 CRITÈRES
            </div>
          )}
        </div>

        <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
          <Pill bg={aBG2} color={locked ? aACCENT : aDIM} border={aLINE2}>PSG–OM</Pill>
          <Pill bg={aBG2} color={locked ? aACCENT : aDIM} border={aLINE2}>VICTOIRE PSG</Pill>
          <Pill bg={locked ? aACCENT : aBG2} color={locked ? aBG : aDIM} border={locked ? null : aLINE2}>@1.65</Pill>
        </div>
      </div>
    </div>
  );
}

// ── 3 · CARNET VALIDÉ ─────────────────────────────────────────────────────────
// 3 sélections slide in stacked, multiplier resolves, ✓ CARNET VALIDÉ stamps.
function AnimCouponValid() {
  const sélections = [
    { label: 'PSG – OM',   type: 'VICTOIRE PSG', odds: 1.65 },
    { label: 'LIV – ARS',  type: 'INDICE +',    odds: 1.78 },
    { label: 'RMA – BAY',  type: 'NOTE A',     odds: 1.55 },
  ];
  const [stage, setStage] = React.useState(0);

  React.useEffect(() => {
    const seq = [
      { delay: 200,  to: 1 },   // pick 1
      { delay: 1000, to: 2 },   // pick 2
      { delay: 1800, to: 3 },   // pick 3
      { delay: 2700, to: 4 },   // multiplier
      { delay: 3500, to: 5 },   // ✓ validé
      { delay: 5500, to: 0 },   // reset
    ];
    let timers = [];
    function start() {
      timers = seq.map((s) => setTimeout(() => setStage(s.to), s.delay));
    }
    start();
    const interval = setInterval(() => {
      timers.forEach(clearTimeout);
      setStage(0);
      requestAnimationFrame(() => start());
    }, 6000);
    return () => {
      timers.forEach(clearTimeout);
      clearInterval(interval);
    };
  }, []);

  return (
    <div style={{ width: '100%', height: '100%', background: aBG, color: aINK, position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 32, fontFamily: aFONT.ui }}>
      <AnimStylesOnce />

      <div style={{ width: 360 }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aACCENT, letterSpacing: '0.2em', marginBottom: 12 }}>CARNET DU JOUR</div>

        {/* sélections */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 16 }}>
          {sélections.map((p, i) => (
            <div key={p.label} style={{
              display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px',
              background: aBG2, border: `1px solid ${aLINE}`, borderRadius: 10,
              opacity: stage > i ? 1 : 0,
              transform: stage > i ? 'translateY(0)' : 'translateY(24px)',
              transition: 'all 0.4s cubic-bezier(0.34, 1.36, 0.64, 1)',
            }}>
              <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.15em', width: 22 }}>0{i+1}</div>
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.05em' }}>{p.label}</div>
                <div style={{ fontSize: 13, color: aINK, fontWeight: 600, marginTop: 2 }}>{p.type}</div>
              </div>
              <div style={{ fontFamily: aFONT.mono, fontSize: 14, color: aACCENT, fontWeight: 700 }}>@{p.odds}</div>
            </div>
          ))}
        </div>

        {/* multiplier */}
        <div style={{
          padding: '14px 16px', background: aBG2, borderRadius: 10, border: `1px solid ${aLINE}`,
          display: 'flex', justifyContent: 'space-between', alignItems: 'center',
          opacity: stage >= 4 ? 1 : 0.2,
          transition: 'opacity 0.4s',
        }}>
          <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM }}>
            {stage >= 4 ? '1.65 × 1.78 × 1.55' : '— × — × —'}
          </div>
          <div style={{ fontFamily: aFONT.title, fontSize: 24, color: aACCENT, letterSpacing: '-0.03em' }}>
            {stage >= 4 ? '@4.55' : '@---'}
          </div>
        </div>

        {/* validé stamp */}
        {stage >= 5 && (
          <div style={{
            position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
            display: 'flex', alignItems: 'center', gap: 12,
            padding: '14px 22px', background: aACCENT, borderRadius: 10,
            boxShadow: '0 12px 40px rgba(232, 255, 54, 0.3), 0 0 0 8px rgba(232,255,54,0.06)',
            animation: 'cota-validate-pulse 2s ease-out forwards',
          }}>
            <div style={{ width: 28, height: 28, borderRadius: 14, background: aBG, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <svg width="16" height="16" viewBox="0 0 16 16"><path d="M3 8 L7 12 L13 4" stroke={aACCENT} strokeWidth="2.5" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
            </div>
            <div style={{ fontFamily: aFONT.title, fontSize: 18, color: aBG, letterSpacing: '0.06em' }}>CARNET VALIDÉ</div>
          </div>
        )}
      </div>
    </div>
  );
}

// ── 4 · NETFLIX-STYLE ODDS DROP ──────────────────────────────────────────────
// Big bold letters/characters "@", "2", ".", "5", "5" drop in one by one
// (Netflix-style title reveal), then the full odds locks in yellow accent.
function AnimNetflixOdds() {
  const chars = ['@', '2', '.', '5', '5'];
  const [stage, setStage] = React.useState(0);

  React.useEffect(() => {
    const seq = [
      { delay: 200,  to: 1 },
      { delay: 500,  to: 2 },
      { delay: 800,  to: 3 },
      { delay: 1100, to: 4 },
      { delay: 1400, to: 5 },
      { delay: 1900, to: 6 },   // lock + label
      { delay: 3800, to: 0 },   // reset
    ];
    let timers = [];
    function start() {
      timers = seq.map(s => setTimeout(() => setStage(s.to), s.delay));
    }
    start();
    const interval = setInterval(() => {
      timers.forEach(clearTimeout);
      setStage(0);
      requestAnimationFrame(() => start());
    }, 4200);
    return () => {
      timers.forEach(clearTimeout);
      clearInterval(interval);
    };
  }, []);

  return (
    <div style={{
      width: '100%', height: '100%', background: aBG, color: aINK,
      position: 'relative', overflow: 'hidden', fontFamily: aFONT.ui,
      display: 'flex', alignItems: 'center', justifyContent: 'center',
    }}>
      <AnimStylesOnce />

      {/* Top label */}
      <div style={{
        position: 'absolute', top: 32, left: 0, right: 0, textAlign: 'center',
        fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.3em',
        opacity: stage > 0 ? 1 : 0, transition: 'opacity 0.4s',
      }}>
        COTE DU JOUR
      </div>

      {/* Big letters dropping in */}
      <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'center', gap: 4 }}>
        {chars.map((c, i) => {
          const shown = stage > i;
          const locked = stage >= 6;
          return (
            <span key={i} style={{
              fontFamily: aFONT.title, fontWeight: 900,
              fontSize: c === '.' ? 140 : 180,
              color: locked ? aACCENT : aINK,
              letterSpacing: '-0.05em', lineHeight: 1,
              display: 'inline-block',
              transform: shown
                ? 'translateY(0) scale(1) rotate(0deg)'
                : 'translateY(-80px) scale(1.5) rotate(-12deg)',
              opacity: shown ? 1 : 0,
              filter: shown ? 'blur(0px)' : 'blur(6px)',
              transition: 'transform 0.45s cubic-bezier(0.16, 1.36, 0.36, 1), opacity 0.35s, color 0.3s, filter 0.35s',
            }}>{c}</span>
          );
        })}
      </div>

      {/* Underline bar — sweeps in once all letters land */}
      <div style={{
        position: 'absolute', left: '50%', bottom: '32%', transform: 'translateX(-50%)',
        width: stage >= 5 ? 240 : 0, height: 6, background: aACCENT,
        transition: 'width 0.6s cubic-bezier(0.65, 0, 0.35, 1) 0.05s',
      }} />

      {/* Bottom label — appears after lock */}
      <div style={{
        position: 'absolute', bottom: 60, left: 0, right: 0, textAlign: 'center',
        opacity: stage >= 6 ? 1 : 0, transform: stage >= 6 ? 'translateY(0)' : 'translateY(10px)',
        transition: 'all 0.4s ease-out 0.1s',
      }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 12, color: aACCENT, letterSpacing: '0.25em', fontWeight: 700 }}>
          ✓ COUPON VALIDÉ
        </div>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aDIM, letterSpacing: '0.2em', marginTop: 6 }}>
          3 PICKS · CONFIANCE 87%
        </div>
      </div>

      {/* Ambient corner ticks (cinema feel) */}
      <div style={{ position: 'absolute', top: 20, left: 20, fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.2em' }}>COTA</div>
      <div style={{ position: 'absolute', top: 20, right: 20, fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.2em' }}>09:30</div>
    </div>
  );
}

Object.assign(window, { AnimSplash, AnimConfidenceReveal, AnimCouponValid, AnimNetflixOdds });

// ═══════════════════════════════════════════════════════════════════════════════
// COUPE DU MONDE 2026 — ballon animé (2 variantes) + splash mobile
// ═══════════════════════════════════════════════════════════════════════════════

const WC_CSS = `
@keyframes wc-spin  { to { transform: rotate(360deg); } }
@keyframes wc-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
@keyframes wc-ring  { 0% { transform: scale(0.55); opacity: 0.5; } 100% { transform: scale(2.3); opacity: 0; } }
@keyframes wc-orbit { to { transform: rotate(360deg); } }
@keyframes wc-rollA {
  0%   { transform: translateX(-300px); opacity: 0; }
  9%   { transform: translateX(-220px); opacity: 1; }
  37%  { transform: translateX(0); }
  80%  { transform: translateX(0); opacity: 1; }
  92%  { transform: translateX(46px); opacity: 1; }
  100% { transform: translateX(320px); opacity: 0; }
}
@keyframes wc-textA {
  0%, 36%   { opacity: 0; transform: translateY(14px); }
  47%, 78%  { opacity: 1; transform: translateY(0); }
  88%, 100% { opacity: 0; transform: translateY(-8px); }
}
.wc-spin  { animation: wc-spin 1.2s linear infinite; }
.wc-float { animation: wc-float 2.6s ease-in-out infinite; }
.wc-ring  { animation: wc-ring 2.4s ease-out infinite; }
.wc-orbit { animation: wc-orbit 11s linear infinite; }
.wc-rollA { animation: wc-rollA 5s cubic-bezier(0.5,0,0.2,1) infinite; }
.wc-textA { animation: wc-textA 5s ease-in-out infinite; }
@media (prefers-reduced-motion: reduce) {
  .wc-spin, .wc-float, .wc-ring, .wc-orbit, .wc-rollA, .wc-textA { animation: none !important; }
}
`;

function WCStylesOnce() {
  React.useEffect(() => {
    if (document.getElementById('cota-wc-css')) return;
    const s = document.createElement('style');
    s.id = 'cota-wc-css';
    s.textContent = WC_CSS;
    document.head.appendChild(s);
  }, []);
  return null;
}

// ── Ballon (SVG) — motif pentagone classique. Le MOTIF tourne sous une lumière FIXE
//    (le reflet reste en haut-à-gauche) → vrai rendu de sphère qui roule.
// Ballon de foot classique : sphère blanche, motif pentagones. Le MOTIF tourne
// sous une lumière FIXE (le reflet reste en haut-à-gauche) → vrai rendu de
// sphère qui roule sur elle-même (360°).
function Football({ size = 200, spin = '1.2s' }) {
  const uid = React.useId().replace(/[:]/g, '');
  const b = `wcb-${uid}`;
  const penta = (cx, cy, r, rot) => {
    const p = [];
    for (let i = 0; i < 5; i++) {
      const a = (-90 + rot + i * 72) * Math.PI / 180;
      p.push(`${(cx + r * Math.cos(a)).toFixed(1)},${(cy + r * Math.sin(a)).toFixed(1)}`);
    }
    return p.join(' ');
  };
  const rim = [];
  for (let i = 0; i < 5; i++) {
    const phi = -90 + i * 72;
    const rad = phi * Math.PI / 180;
    rim.push({ phi, cx: 100 + 62 * Math.cos(rad), cy: 100 + 62 * Math.sin(rad), rad });
  }
  const blk = '#16191e';
  return (
    <svg width={size} height={size} viewBox="0 0 200 200" style={{ display: 'block' }}>
      <defs>
        <radialGradient id={`${b}-base`} cx="37%" cy="30%" r="78%">
          <stop offset="0%" stopColor="#ffffff" />
          <stop offset="55%" stopColor="#edeade" />
          <stop offset="100%" stopColor="#c2bfb1" />
        </radialGradient>
        <radialGradient id={`${b}-shade`} cx="37%" cy="30%" r="82%">
          <stop offset="0%" stopColor="rgba(255,255,255,0.60)" />
          <stop offset="42%" stopColor="rgba(255,255,255,0)" />
          <stop offset="100%" stopColor="rgba(0,0,0,0.44)" />
        </radialGradient>
        <clipPath id={`${b}-clip`}><circle cx="100" cy="100" r="86" /></clipPath>
      </defs>

      {/* base sphère (lumière fixe) */}
      <circle cx="100" cy="100" r="86" fill={`url(#${b}-base)`} />

      {/* motif qui tourne, clippé au ballon */}
      <g clipPath={`url(#${b}-clip)`}>
        <g className="wc-spin" style={{ transformBox: 'fill-box', transformOrigin: 'center', animationDuration: spin }}>
          <circle cx="100" cy="100" r="86" fill="transparent" />
          {rim.map((s, i) => (
            <line key={'l' + i}
              x1={(100 + 28 * Math.cos(s.rad)).toFixed(1)} y1={(100 + 28 * Math.sin(s.rad)).toFixed(1)}
              x2={(100 + 90 * Math.cos(s.rad)).toFixed(1)} y2={(100 + 90 * Math.sin(s.rad)).toFixed(1)}
              stroke={blk} strokeWidth="4.2" strokeLinecap="round" />
          ))}
          <polygon points={penta(100, 100, 30, 0)} fill={blk} />
          {rim.map((s, i) => <polygon key={'p' + i} points={penta(s.cx, s.cy, 23, s.phi + 90)} fill={blk} />)}
        </g>
      </g>

      {/* ombrage volume + liseré (fixes) */}
      <circle cx="100" cy="100" r="86" fill={`url(#${b}-shade)`} />
      <circle cx="100" cy="100" r="86" fill="none" stroke="rgba(0,0,0,0.22)" strokeWidth="1.5" />
    </svg>
  );
}

// ── VARIANTE A · LE BALLON ROULE ───────────────────────────────────────────────
function AnimWorldCupRoll() {
  return (
    <div style={{ width: '100%', height: '100%', background: aBG, color: aINK, position: 'relative', overflow: 'hidden', fontFamily: aFONT.ui }}>
      <WCStylesOnce />

      <div style={{ position: 'absolute', top: 30, left: 0, right: 0, textAlign: 'center', fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.32em' }}>COTA × MONDIAL 2026</div>

      {/* sol */}
      <div style={{ position: 'absolute', left: 0, right: 0, bottom: '32%', height: 1, background: `linear-gradient(90deg, transparent, ${aLINE2} 30%, ${aLINE2} 70%, transparent)` }} />

      {/* ballon qui roule */}
      <div className="wc-rollA" style={{ position: 'absolute', left: '50%', top: '40%', width: 176, height: 176, marginLeft: -88, marginTop: -88 }}>
        <div style={{ position: 'absolute', left: '50%', bottom: -20, transform: 'translateX(-50%)', width: 150, height: 26, borderRadius: '50%', background: 'radial-gradient(ellipse, rgba(0,0,0,0.55), transparent 70%)' }} />
        <div style={{ position: 'absolute', left: '50%', bottom: -14, transform: 'translateX(-50%)', width: 120, height: 14, borderRadius: '50%', background: `radial-gradient(ellipse, ${aACCENT}38, transparent 72%)` }} />
        <Football size={176} spin="0.85s" />
      </div>

      {/* reveal texte */}
      <div className="wc-textA" style={{ position: 'absolute', left: 0, right: 0, bottom: '11%', textAlign: 'center' }}>
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, background: aACCENT, color: aBG, fontFamily: aFONT.mono, fontSize: 11, fontWeight: 700, letterSpacing: '0.18em', padding: '6px 14px', borderRadius: 999 }}>
          <span style={{ width: 6, height: 6, borderRadius: 3, background: aBG }} /> MODE COUPE DU MONDE
        </div>
        <div style={{ fontFamily: aFONT.title, fontSize: 32, color: aINK, letterSpacing: '-0.03em', marginTop: 16 }}>Le Mondial, analysé.</div>
        <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.2em', marginTop: 10 }}>9 CRITÈRES · 48 ÉQUIPES · 104 MATCHS</div>
      </div>
    </div>
  );
}

// ── VARIANTE B · LE BALLON TOURNE (splash héro) ────────────────────────────────
function AnimWorldCupSpin() {
  return (
    <div style={{ width: '100%', height: '100%', background: aBG, color: aINK, position: 'relative', overflow: 'hidden', fontFamily: aFONT.ui, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 30 }}>
      <WCStylesOnce />

      <div style={{ position: 'absolute', top: 30, left: 0, right: 0, textAlign: 'center', fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.25em', display: 'flex', justifyContent: 'center', gap: 5 }}>
        ANALYSE DU MONDIAL
        <span style={{ display: 'inline-flex', gap: 2 }}>
          <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0s', color: aACCENT }}>.</span>
          <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0.18s', color: aACCENT }}>.</span>
          <span style={{ animation: 'cota-dots-fade 1.4s ease-in-out infinite 0.36s', color: aACCENT }}>.</span>
        </span>
      </div>

      <div style={{ position: 'relative', width: 260, height: 260, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        {[0, 1, 2].map(i => (
          <div key={i} className="wc-ring" style={{ position: 'absolute', width: 200, height: 200, borderRadius: '50%', border: `2px solid ${aACCENT}`, animationDelay: `${i * 0.8}s` }} />
        ))}
        <div className="wc-orbit" style={{ position: 'absolute', width: 260, height: 260 }}>
          {[0, 1, 2, 3, 4].map(i => {
            const a = (i * 72 - 90) * Math.PI / 180;
            const x = 130 + 122 * Math.cos(a), y = 130 + 122 * Math.sin(a);
            return <div key={i} style={{ position: 'absolute', left: x - 3.5, top: y - 3.5, width: 7, height: 7, borderRadius: 4, background: i % 2 ? aACCENT : aDIM }} />;
          })}
        </div>
        <div className="wc-float">
          <Football size={172} spin="1.35s" />
        </div>
      </div>

      <div style={{ textAlign: 'center' }}>
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, background: aACCENT, color: aBG, fontFamily: aFONT.mono, fontSize: 11, fontWeight: 700, letterSpacing: '0.18em', padding: '6px 14px', borderRadius: 999 }}>COUPE DU MONDE 2026</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 24, color: aINK, letterSpacing: '-0.03em', marginTop: 14 }}>Pronostics du Mondial</div>
      </div>
    </div>
  );
}

// ── SPLASH MOBILE · MODE COUPE DU MONDE (dans l'iPhone) ────────────────────────
function WCSplashMobile() {
  return (
    <div style={{ width: '100%', height: '100%', background: aBG, color: aINK, position: 'relative', overflow: 'hidden', fontFamily: aFONT.ui, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
      <WCStylesOnce />

      {/* glow d'ambiance */}
      <div style={{ position: 'absolute', top: '18%', left: '50%', transform: 'translateX(-50%)', width: 320, height: 320, borderRadius: '50%', background: `radial-gradient(circle, ${aACCENT}1f, transparent 65%)`, pointerEvents: 'none' }} />

      <div style={{ marginTop: 70, zIndex: 2 }}><Wordmark size={22} underline={false} /></div>

      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative', width: '100%' }}>
        <div style={{ position: 'relative', width: 230, height: 230, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          {[0, 1].map(i => (
            <div key={i} className="wc-ring" style={{ position: 'absolute', width: 180, height: 180, borderRadius: '50%', border: `2px solid ${aACCENT}`, animationDelay: `${i * 1.2}s` }} />
          ))}
          <div className="wc-float"><Football size={168} spin="1.25s" /></div>
        </div>
      </div>

      <div style={{ padding: '0 30px 46px', textAlign: 'center', zIndex: 2, width: '100%', boxSizing: 'border-box' }}>
        <div style={{ display: 'inline-flex', alignItems: 'center', gap: 7, background: aACCENT, color: aBG, fontFamily: aFONT.mono, fontSize: 10.5, fontWeight: 700, letterSpacing: '0.16em', padding: '6px 13px', borderRadius: 999 }}>MODE COUPE DU MONDE</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 27, color: aINK, letterSpacing: '-0.03em', marginTop: 18, lineHeight: 1.1 }}>Le Mondial 2026,<br />analysé.</div>
        <div style={{ fontSize: 14, color: aDIM, lineHeight: 1.5, marginTop: 12, textWrap: 'pretty' }}>Chaque match passé au crible des 9 critères COTA, du match d'ouverture à la finale.</div>

        <button style={{ width: '100%', height: 52, marginTop: 24, background: aACCENT, color: aBG, border: 'none', borderRadius: 14, fontFamily: aFONT.title, fontSize: 15, letterSpacing: '0.02em' }}>Voir les pronos du Mondial</button>
        <div style={{ fontFamily: aFONT.mono, fontSize: 11, color: aDIM2, letterSpacing: '0.18em', marginTop: 18 }}>104 MATCHS · 48 ÉQUIPES</div>
      </div>
    </div>
  );
}

Object.assign(window, { Football, AnimWorldCupRoll, AnimWorldCupSpin, WCSplashMobile });
