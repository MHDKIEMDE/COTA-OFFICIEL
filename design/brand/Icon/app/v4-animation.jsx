// COTA V4 — signature animation: AI orb boot + iris ticker.

function V4AnimBoot() {
  const [stage, setStage] = React.useState(0);

  React.useEffect(() => {
    const seq = [
      { d: 200,  v: 1 },   // orb pop
      { d: 800,  v: 2 },   // ticker
      { d: 1400, v: 3 },   // ascii rain
      { d: 2200, v: 4 },   // odds reveal
      { d: 3600, v: 5 },   // greeting
      { d: 5400, v: 0 },   // reset
    ];
    let timers = [];
    function start() {
      timers = seq.map(s => setTimeout(() => setStage(s.v), s.d));
    }
    start();
    const interval = setInterval(() => {
      timers.forEach(clearTimeout);
      setStage(0);
      requestAnimationFrame(() => start());
    }, 5800);
    return () => {
      timers.forEach(clearTimeout);
      clearInterval(interval);
    };
  }, []);

  return (
    <div style={{ width: '100%', height: '100%', background: V4.BG, color: V4.INK, position: 'relative', overflow: 'hidden', fontFamily: V4.font.ui }}>
      <V4Styles />
      <MeshAmbient opacity={0.55} />

      {/* Top status */}
      <div style={{ position: 'absolute', top: 28, left: 28, right: 28, display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 5 }}>
        <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.2em' }}>COTA · MODÈLE 2030</div>
        <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.BLUE, letterSpacing: '0.2em', display: 'flex', alignItems: 'center', gap: 6 }}>
          <span style={{ width: 6, height: 6, borderRadius: 3, background: V4.BLUE, animation: 'v4-blink 1s steps(2) infinite' }} />
          09:30 GMT
        </div>
      </div>

      {/* Center stage */}
      <div style={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 32 }}>
        {/* Orb */}
        <div style={{
          transform: stage >= 1 ? 'scale(1)' : 'scale(0.3)',
          opacity: stage >= 1 ? 1 : 0,
          transition: 'all 0.7s cubic-bezier(0.34, 1.36, 0.64, 1)',
        }}>
          <AIOrb size={120} />
        </div>

        {/* ASCII rain (criteria flying) */}
        {stage >= 3 && (
          <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, pointerEvents: 'none', overflow: 'hidden' }}>
            {['FORME', 'H2H', 'XG', 'POSSESS.', 'CARTONS', 'MÉTÉO', 'MARCHÉ', 'BLESSURES'].map((c, i) => (
              <div key={c} style={{
                position: 'absolute',
                top: -20, left: `${(i * 11 + 5) % 90}%`,
                fontFamily: V4.font.mono, fontSize: 9, color: V4.BLUE, letterSpacing: '0.15em', opacity: 0.55,
                animation: `v4-rain-${i} 1.2s linear forwards`,
              }}>{c}</div>
            ))}
            <style>{Array.from({ length: 8 }).map((_, i) => `
              @keyframes v4-rain-${i} {
                from { transform: translateY(0); opacity: 0.7; }
                to { transform: translateY(${360 + i * 20}px); opacity: 0; }
              }
            `).join('')}</style>
          </div>
        )}

        {/* Headline */}
        <div style={{
          opacity: stage >= 5 ? 1 : 0,
          transform: stage >= 5 ? 'translateY(0)' : 'translateY(8px)',
          transition: 'all 0.5s ease-out',
          textAlign: 'center',
        }}>
          <div style={{ fontFamily: V4.font.title, fontSize: 32, letterSpacing: '-0.04em', color: V4.INK }}>
            <span style={{ background: V4.YELLOW, padding: '0 6px' }}>3 picks</span> prêts.
          </div>
          <div style={{ fontFamily: V4.font.mono, fontSize: 11, color: V4.DIM, letterSpacing: '0.2em', marginTop: 10 }}>
            CONFIANCE MOYENNE 87%
          </div>
        </div>
      </div>

      {/* Big @4.55 reveal — bottom */}
      <div style={{
        position: 'absolute', bottom: 60, left: 0, right: 0, textAlign: 'center',
        opacity: stage >= 4 ? 1 : 0,
        transform: stage >= 4 ? 'translateY(0)' : 'translateY(20px)',
        transition: 'all 0.7s cubic-bezier(0.16, 1.36, 0.36, 1)',
      }}>
        <div style={{
          display: 'inline-block',
          padding: '8px 22px',
          background: V4.INK,
          color: V4.YELLOW,
          fontFamily: V4.font.title,
          fontSize: 36,
          letterSpacing: '-0.04em',
          borderRadius: 10,
        }}>@4.55</div>
      </div>

      {/* Ticker (always running once stage 2) */}
      {stage >= 2 && (
        <div style={{
          position: 'absolute', top: 80, left: 0, right: 0,
          overflow: 'hidden', whiteSpace: 'nowrap',
          background: V4.INK, color: V4.YELLOW,
          padding: '6px 0',
          opacity: stage >= 2 ? 1 : 0, transition: 'opacity 0.4s',
        }}>
          <div style={{
            display: 'inline-block', fontFamily: V4.font.mono, fontSize: 10, letterSpacing: '0.2em',
            animation: 'v4-ticker-flow 12s linear infinite',
          }}>
            ● ANALYSE NEURALE · 247 MATCHS · 9 CRITÈRES · CONFIANCE 87% · IRIDESCENT · @4.55 · PSG–OM · LIV–ARS · RMA–BAY · ANALYSE NEURALE · 247 MATCHS · 9 CRITÈRES · CONFIANCE 87% ·
          </div>
        </div>
      )}
    </div>
  );
}

Object.assign(window, { V4AnimBoot });
