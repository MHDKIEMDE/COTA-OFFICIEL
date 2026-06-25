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
