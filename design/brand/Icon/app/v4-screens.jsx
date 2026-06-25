// COTA V4 — 4 screens: Welcome 2030, Home, Match, Coupon validated.

// ─────────────────────────────────────────────────────────────────────────────
// 1. WELCOME 2030 — immersive AI greeting
// ─────────────────────────────────────────────────────────────────────────────
function V4Welcome() {
  return (
    <div style={{ height: '100%', background: V4.BG, color: V4.INK, fontFamily: V4.font.ui, position: 'relative', overflow: 'hidden' }}>
      <V4Styles />
      <MeshAmbient opacity={0.6} />

      {/* Status row */}
      <div style={{ position: 'absolute', top: 56, left: 24, right: 24, display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 5 }}>
        <IrisPill>★ MODÈLE 2030 · v3</IrisPill>
        <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.18em' }}>09:30 GMT</div>
      </div>

      {/* AI Orb hero */}
      <div style={{ position: 'absolute', top: 130, left: 0, right: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', zIndex: 3 }}>
        <AIOrb size={140} />
        <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.3em', marginTop: 14 }}>COTA · IA NEURALE</div>
      </div>

      {/* Greeting */}
      <div style={{ position: 'absolute', top: 320, left: 24, right: 24, zIndex: 3 }}>
        <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.BLUE, letterSpacing: '0.2em', marginBottom: 10 }}>● ANALYSE TERMINÉE</div>
        <div style={{ fontFamily: V4.font.title, fontSize: 36, letterSpacing: '-0.04em', lineHeight: 1.0, color: V4.INK }}>
          Bonjour Karim.<br/>
          <span style={{ background: V4.YELLOW, padding: '0 6px' }}>3 picks</span> aujourd'hui.
        </div>
        <div style={{ fontSize: 14, color: V4.INK2, marginTop: 14, lineHeight: 1.5, maxWidth: 320 }}>
          247 matchs analysés cette nuit. Cote combinée <strong>@4.55</strong>, confiance moyenne <strong>87%</strong>.
        </div>
      </div>

      {/* Quick predictions strip */}
      <div style={{ position: 'absolute', top: 524, left: 24, right: 24, zIndex: 3 }}>
        <HoloCard padding={16} glow>
          <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.2em', marginBottom: 12 }}>★ COUPON DU JOUR</div>
          {[
            ['PSG–OM',   'Victoire PSG', '1.65', 87],
            ['LIV–ARS',  '+2.5 buts',    '1.78', 76],
            ['RMA–BAY',  'BTTS Oui',     '1.55', 91],
          ].map(([m, t, o, c]) => (
            <div key={m} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 0', borderTop: `1px solid ${V4.LINE}` }}>
              <span style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.08em', width: 60 }}>{m}</span>
              <span style={{ flex: 1, fontSize: 12, color: V4.INK, fontWeight: 500 }}>{t}</span>
              <span style={{ fontFamily: V4.font.mono, fontSize: 12, color: V4.INK, fontWeight: 700 }}>@{o}</span>
              <span style={{ fontFamily: V4.font.mono, fontSize: 10, color: c >= 85 ? V4.BLUE : V4.DIM, fontWeight: 700 }}>{c}%</span>
            </div>
          ))}
        </HoloCard>
      </div>

      {/* CTA bottom */}
      <div style={{ position: 'absolute', bottom: 40, left: 24, right: 24, zIndex: 5 }}>
        <button style={{
          width: '100%', height: 56, background: V4.INK, color: V4.YELLOW, border: 'none', borderRadius: 14,
          fontFamily: V4.font.title, fontSize: 15, letterSpacing: '0.05em',
        }}>OUVRIR LE COUPON →</button>
        <div style={{ marginTop: 10 }}>
          <VoicePrompt text="« Pourquoi PSG est-il favori ? »" />
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. HOME 2030 — futurist matches feed
// ─────────────────────────────────────────────────────────────────────────────
function V4Home() {
  const M = window.MATCHES;
  return (
    <div style={{ height: '100%', background: V4.BG, color: V4.INK, fontFamily: V4.font.ui, position: 'relative' }}>
      <V4Styles />
      <MeshAmbient opacity={0.35} />

      <div style={{ height: '100%', overflowY: 'auto', position: 'relative', zIndex: 2 }}>
        {/* Header */}
        <div style={{ padding: '60px 22px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.2em' }}>MAR 18 MAI 2026</div>
            <div style={{ fontFamily: V4.font.title, fontSize: 30, letterSpacing: '-0.03em', marginTop: 4 }}>Aujourd'hui</div>
          </div>
          <AIOrb size={48} />
        </div>

        {/* Coupon card hero */}
        <div style={{ padding: '0 22px 22px' }}>
          <HoloCard padding={20} glow>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 14 }}>
              <div>
                <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.BLUE, letterSpacing: '0.2em' }}>★ COUPON · 09:30</div>
                <div style={{ fontFamily: V4.font.title, fontSize: 44, letterSpacing: '-0.04em', lineHeight: 1, marginTop: 8 }}>@4.55</div>
              </div>
              <HoloGauge value={87} size={70} stroke={5} />
            </div>
            <div style={{ fontSize: 13, color: V4.INK2, marginBottom: 14 }}>3 picks combinés · PSG, Liverpool, Real Madrid</div>
            <button style={{
              width: '100%', height: 44, background: V4.INK, color: V4.YELLOW, border: 'none', borderRadius: 10,
              fontFamily: V4.font.title, fontSize: 12, letterSpacing: '0.06em',
            }}>VOIR L'ANALYSE</button>
          </HoloCard>
        </div>

        {/* Live data ticker */}
        <DataTicker />

        {/* Match cards */}
        <div style={{ padding: '20px 22px 24px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 12 }}>
            <div style={{ fontFamily: V4.font.title, fontSize: 14, letterSpacing: '-0.01em' }}>Tous les matchs</div>
            <span style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.12em' }}>6 →</span>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {M.slice(0, 4).map(m => {
              const h = window.TEAMS[m.home];
              const a = window.TEAMS[m.away];
              const high = m.confidence >= 85;
              return (
                <HoloCard key={m.id} padding={14}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    {/* mini team avatars */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: -10 }}>
                      <TeamBadge code={h.code} color={h.color} text={h.text} size={32} />
                      <div style={{ marginLeft: -8 }}><TeamBadge code={a.code} color={a.color} text={a.text} size={32} /></div>
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontFamily: V4.font.title, fontSize: 14, color: V4.INK, letterSpacing: '-0.02em' }}>{h.short} – {a.short}</div>
                      <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.08em', marginTop: 2 }}>{m.kickoff} · {m.competition.toUpperCase()}</div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                      <div style={{ fontFamily: V4.font.mono, fontSize: 14, color: V4.INK, fontWeight: 700 }}>@{m.pick.odds}</div>
                      <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: high ? V4.BLUE : V4.DIM, letterSpacing: '0.1em', fontWeight: 700, marginTop: 2 }}>{m.confidence}%</div>
                    </div>
                  </div>
                </HoloCard>
              );
            })}
          </div>
        </div>

        {/* Voice */}
        <div style={{ padding: '0 22px 30px' }}>
          <VoicePrompt text="« Compare PSG et OM pour ce soir »" />
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. MATCH DETAIL 2030 — holographic predictions
// ─────────────────────────────────────────────────────────────────────────────
function V4Match() {
  const m = window.MATCHES[0];
  const h = window.TEAMS[m.home];
  const a = window.TEAMS[m.away];
  return (
    <div style={{ height: '100%', background: V4.BG, color: V4.INK, fontFamily: V4.font.ui, position: 'relative', overflow: 'hidden' }}>
      <V4Styles />
      <MeshAmbient opacity={0.55} />

      <div style={{ height: '100%', overflowY: 'auto', position: 'relative', zIndex: 2 }}>
        {/* Header */}
        <div style={{ padding: '60px 22px 14px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.06)', border: `1px solid ${V4.LINE2}`, color: V4.INK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="13" height="13" viewBox="0 0 13 13"><path d="M8 2 L4 6.5 L8 11" stroke={V4.INK} strokeWidth="1.5" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
          </button>
          <IrisPill>{m.competition.toUpperCase()} · {m.round}</IrisPill>
          <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.06)', border: `1px solid ${V4.LINE2}`, color: V4.INK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="12" height="12" viewBox="0 0 12 12"><circle cx="3" cy="3" r="1.4" fill={V4.INK}/><circle cx="9" cy="3" r="1.4" fill={V4.INK}/><circle cx="9" cy="9" r="1.4" fill={V4.INK}/></svg>
          </button>
        </div>

        {/* Match arena — floating team plates */}
        <div style={{ padding: '0 22px 22px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 14 }}>
          <HoloCard padding={16} style={{ flex: 1, transform: 'rotate(-2deg)' }}>
            <TeamBadge code={h.code} color={h.color} text={h.text} size={52} />
            <div style={{ fontFamily: V4.font.title, fontSize: 16, marginTop: 10, letterSpacing: '-0.02em' }}>{h.short}</div>
            <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.08em', marginTop: 4 }}>DOMICILE</div>
          </HoloCard>

          <div style={{ textAlign: 'center' }}>
            <div style={{ fontFamily: V4.font.title, fontSize: 32, color: V4.INK, letterSpacing: '-0.04em' }}>{m.score}</div>
            <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.BLUE, letterSpacing: '0.18em', marginTop: 4, fontWeight: 700 }}>SCORE IA</div>
            <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.12em', marginTop: 2 }}>{m.kickoff}</div>
          </div>

          <HoloCard padding={16} style={{ flex: 1, transform: 'rotate(2deg)' }}>
            <TeamBadge code={a.code} color={a.color} text={a.text} size={52} />
            <div style={{ fontFamily: V4.font.title, fontSize: 16, marginTop: 10, letterSpacing: '-0.02em' }}>{a.short}</div>
            <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.DIM, letterSpacing: '0.08em', marginTop: 4 }}>EXTÉRIEUR</div>
          </HoloCard>
        </div>

        {/* AI verdict — iridescent strip */}
        <div style={{ padding: '0 22px 18px' }}>
          <div style={{ position: 'relative', borderRadius: 16, overflow: 'hidden', padding: 18, background: V4.INK, color: V4.BG }}>
            <div style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 4, background: IRIS_GRADIENT }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
              <HoloGauge value={m.confidence} size={88} stroke={6} />
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: V4.font.mono, fontSize: 9, color: V4.YELLOW, letterSpacing: '0.2em' }}>● VERDICT IA</div>
                <div style={{ fontFamily: V4.font.title, fontSize: 22, color: V4.BG, marginTop: 6, letterSpacing: '-0.02em' }}>{m.pick.type}</div>
                <div style={{ marginTop: 8, display: 'inline-flex', alignItems: 'center', gap: 8, padding: '5px 12px', background: V4.YELLOW, color: V4.INK, borderRadius: 6, fontFamily: V4.font.mono, fontSize: 13, fontWeight: 700 }}>@{m.pick.odds}</div>
              </div>
            </div>
          </div>
        </div>

        {/* Neural criteria flow */}
        <div style={{ padding: '0 22px 22px' }}>
          <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.18em', marginBottom: 10 }}>FLUX NEURAL · 9 CRITÈRES</div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 6 }}>
            {[
              ['01', 'Forme',      '4V 1N',   'pro'],
              ['02', 'H2H',        '6-2-2',   'pro'],
              ['03', 'Dom',         '89%',    'pro'],
              ['04', 'Blessures',   '0 vs 2', 'pro'],
              ['05', 'Météo',       '14°',    'neutral'],
              ['06', 'Marché',      '1.65',   'neutral'],
              ['07', 'Cartons',     '2.8',    'neutral'],
              ['08', 'Possession',  '64%',    'pro'],
              ['09', 'xG',          '2.8',    'pro'],
            ].map(([n, l, v, s]) => {
              const c = s === 'pro' ? V4.BLUE : V4.DIM;
              return (
                <div key={n} style={{ padding: 10, background: V4.BG2, borderRadius: 10, position: 'relative' }}>
                  <div style={{ position: 'absolute', top: 8, right: 8, width: 5, height: 5, borderRadius: 3, background: c, animation: 'v4-data-pulse 2s ease-in-out infinite' }} />
                  <div style={{ fontFamily: V4.font.mono, fontSize: 8, color: V4.DIM, letterSpacing: '0.15em' }}>{n}</div>
                  <div style={{ fontSize: 11, color: V4.INK, marginTop: 4, fontWeight: 500 }}>{l}</div>
                  <div style={{ fontFamily: V4.font.mono, fontSize: 11, color: c, fontWeight: 700, marginTop: 4 }}>{v}</div>
                </div>
              );
            })}
          </div>
        </div>

        {/* CTAs */}
        <div style={{ padding: '0 22px 30px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          <button style={{ width: '100%', height: 52, background: V4.INK, color: V4.YELLOW, border: 'none', borderRadius: 12, fontFamily: V4.font.title, fontSize: 14, letterSpacing: '0.05em' }}>
            AJOUTER AU COUPON
          </button>
          <button style={{ width: '100%', height: 44, background: 'transparent', color: V4.INK, border: `1.5px solid ${V4.INK}`, borderRadius: 12, fontFamily: V4.font.mono, fontSize: 11, letterSpacing: '0.15em', fontWeight: 700 }}>
            DISCUTER AVEC COTA
          </button>
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. COUPON VALIDATED — celebration moment
// ─────────────────────────────────────────────────────────────────────────────
function V4Validated() {
  return (
    <div style={{ height: '100%', background: V4.BG, color: V4.INK, fontFamily: V4.font.ui, position: 'relative', overflow: 'hidden' }}>
      <V4Styles />
      <MeshAmbient opacity={0.7} />

      {/* Confetti dots */}
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none' }}>
        {Array.from({ length: 24 }).map((_, i) => {
          const colors = [V4.YELLOW, V4.BLUE, V4.MAGENTA, V4.INK];
          const c = colors[i % colors.length];
          return (
            <div key={i} style={{
              position: 'absolute',
              top: `${(i * 17) % 90 + 5}%`,
              left: `${(i * 23) % 90 + 5}%`,
              width: 6 + (i % 3) * 2,
              height: 6 + (i % 3) * 2,
              background: c,
              transform: `rotate(${i * 25}deg)`,
              opacity: 0.6,
            }} />
          );
        })}
      </div>

      {/* Top bar */}
      <div style={{ position: 'absolute', top: 56, left: 24, right: 24, display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 5 }}>
        <IrisPill>★ MOMENT DÉCISIF</IrisPill>
        <button style={{ background: 'transparent', border: 'none', color: V4.DIM, fontFamily: V4.font.mono, fontSize: 10, letterSpacing: '0.15em' }}>FERMER ×</button>
      </div>

      {/* Validated stamp center */}
      <div style={{ position: 'absolute', top: '24%', left: 0, right: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', zIndex: 4 }}>
        <AIOrb size={80} />

        <div style={{ marginTop: 28, padding: '10px 20px', background: V4.INK, color: V4.YELLOW, borderRadius: 10, display: 'flex', alignItems: 'center', gap: 12, boxShadow: '0 16px 40px rgba(11,13,16,0.25)' }}>
          <svg width="18" height="18" viewBox="0 0 18 18"><path d="M3 9 L7 13 L15 5" stroke={V4.YELLOW} strokeWidth="2.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
          <span style={{ fontFamily: V4.font.title, fontSize: 16, letterSpacing: '0.08em' }}>COUPON VALIDÉ</span>
        </div>

        {/* Gain hero */}
        <div style={{ marginTop: 26, textAlign: 'center' }}>
          <div style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.25em' }}>GAIN NET</div>
          <div style={{ fontFamily: V4.font.title, fontSize: 96, color: V4.INK, lineHeight: 0.95, letterSpacing: '-0.05em', marginTop: 6 }}>
            +44<span style={{ color: V4.WIN }}>,20€</span>
          </div>
          <div style={{ marginTop: 4, fontFamily: V4.font.mono, fontSize: 11, color: V4.DIM, letterSpacing: '0.15em' }}>3 / 3 PICKS GAGNANTS</div>
        </div>
      </div>

      {/* Picks recap card */}
      <div style={{ position: 'absolute', bottom: 110, left: 22, right: 22, zIndex: 5 }}>
        <HoloCard padding={16}>
          {[
            ['PSG–OM',  'Victoire PSG', '@1.65', '2–1'],
            ['LIV–ARS', '+2.5 buts',    '@1.78', '3–2'],
            ['RMA–BAY', 'BTTS Oui',     '@1.55', '2–1'],
          ].map(([m, t, o, s], i, arr) => (
            <div key={m} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '7px 0', borderBottom: i < arr.length - 1 ? `1px solid ${V4.LINE}` : 'none' }}>
              <span style={{ width: 14, height: 14, borderRadius: 7, background: V4.WIN, color: V4.BG, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <svg width="8" height="8" viewBox="0 0 8 8"><path d="M1 4 L3 6 L7 2" stroke={V4.BG} strokeWidth="1.6" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </span>
              <span style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.DIM, letterSpacing: '0.08em', width: 60 }}>{m}</span>
              <span style={{ flex: 1, fontSize: 12, color: V4.INK }}>{t}</span>
              <span style={{ fontFamily: V4.font.mono, fontSize: 11, color: V4.INK, fontWeight: 700 }}>{o}</span>
              <span style={{ fontFamily: V4.font.mono, fontSize: 10, color: V4.WIN, letterSpacing: '0.05em', width: 30 }}>{s}</span>
            </div>
          ))}
        </HoloCard>
      </div>

      {/* CTAs */}
      <div style={{ position: 'absolute', bottom: 36, left: 22, right: 22, display: 'flex', gap: 8, zIndex: 5 }}>
        <button style={{ flex: 1, height: 50, background: V4.INK, color: V4.YELLOW, border: 'none', borderRadius: 12, fontFamily: V4.font.title, fontSize: 13, letterSpacing: '0.05em' }}>RETIRER</button>
        <button style={{ flex: 1, height: 50, background: 'transparent', color: V4.INK, border: `1.5px solid ${V4.INK}`, borderRadius: 12, fontFamily: V4.font.mono, fontSize: 11, letterSpacing: '0.12em', fontWeight: 700 }}>PARTAGER</button>
      </div>
    </div>
  );
}

Object.assign(window, { V4Welcome, V4Home, V4Match, V4Validated });
