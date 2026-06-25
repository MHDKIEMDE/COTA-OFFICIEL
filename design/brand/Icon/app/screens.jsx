// COTA — core app screens (DAZN aesthetic).
// All take no props; they render content inside an iOS device frame.

const { BG: sBG, BG2: sBG2, BG3: sBG3, LINE: sLINE, LINE2: sLINE2, INK: sINK, INK2: sINK2, DIM: sDIM, DIM2: sDIM2, ACCENT: sACCENT, WIN: sWIN, LOSS: sLOSS, font: sFONT } = window.COTA;

// Shared scroll-content wrapper (between status bar / nav and bottom tab).
function ScrollContent({ children, paddingBottom = 90, paddingTop = 60 }) {
  return (
    <div style={{ height: '100%', overflowY: 'auto', paddingBottom, paddingTop, background: sBG }}>
      {children}
    </div>
  );
}

// ──────────────────────────────────────────────────────────────────────────────
// 1. HOME / AUJOURD'HUI — DAZN-style content rows
// ──────────────────────────────────────────────────────────────────────────────
function ScreenHome() {
  const M = window.MATCHES;
  const C = window.COUPON;
  return (
    <div style={{ height: '100%', background: sBG, color: sINK, position: 'relative', fontFamily: sFONT.ui }}>
      <ScrollContent>
        <AppHeader />

        {/* Tab strip */}
        <div style={{ display: 'flex', gap: 16, padding: '4px 20px 18px', borderBottom: `1px solid ${sLINE}` }}>
          {['AUJOURD\'HUI', 'DEMAIN', 'SEMAINE', 'COMPÉTITIONS'].map((t, i) => (
            <div key={t} style={{
              fontFamily: sFONT.mono, fontSize: 11, letterSpacing: '0.12em',
              color: i === 0 ? sACCENT : sDIM, paddingBottom: 6,
              borderBottom: i === 0 ? `2px solid ${sACCENT}` : '2px solid transparent',
            }}>{t}</div>
          ))}
        </div>

        {/* Hero — carnet du jour */}
        <div style={{ padding: '18px 20px 22px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: sACCENT, marginBottom: 10 }}>★ CARNET DU JOUR · 9:30</div>
          <div style={{ position: 'relative', borderRadius: 16, overflow: 'hidden', background: sBG2, padding: 18, border: `1px solid ${sLINE}` }}>
            {/* faint diagonal accent */}
            <div style={{ position: 'absolute', top: -40, right: -40, width: 200, height: 200, background: 'radial-gradient(circle, rgba(232,255,54,0.10) 0%, transparent 70%)' }} />
            <div style={{ position: 'relative', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 14 }}>
              <div>
                <div style={{ fontFamily: sFONT.title, fontSize: 30, color: sACCENT, lineHeight: 0.95, letterSpacing: '-0.04em' }}>@{C.total.toFixed(2)}</div>
                <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.12em', marginTop: 4 }}>INDICE COMBINÉ</div>
              </div>
              <ConfidenceRing value={C.confidence} size={64} stroke={5} />
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              {C.sélections.map((p) => (
                <div key={p.matchId} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 0', borderTop: `1px solid ${sLINE2}` }}>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM, letterSpacing: '0.06em', width: 80 }}>{p.label}</div>
                  <div style={{ flex: 1, fontSize: 12, color: sINK, fontWeight: 600 }}>{p.type}</div>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 12, color: p.confidence >= 85 ? sACCENT : sINK, fontWeight: 700 }}>@{p.odds}</div>
                </div>
              ))}
            </div>
            <button style={{
              marginTop: 14, width: '100%', height: 42, background: sACCENT, color: sBG, border: 'none', borderRadius: 10,
              fontFamily: sFONT.title, fontSize: 12, letterSpacing: '0.08em',
            }}>VOIR L'ÉDITION COMPLÈTE →</button>
          </div>
        </div>

        {/* Live row */}
        <ContentRow title="EN LIVE" more="3 matches">
          <div style={{ width: 280, flexShrink: 0 }}>
            <MatchHeroCard match={M[2]} height={150} live={true} score="1–0" minute="34" />
          </div>
          <div style={{ width: 280, flexShrink: 0 }}>
            <MatchHeroCard match={M[1]} height={150} live={true} score="2–2" minute="78" />
          </div>
        </ContentRow>

        {/* Today */}
        <ContentRow title="TOUTES LES ÉDITIONS DU JOUR" more="6 →">
          <MatchRowCard match={M[0]} />
          <MatchRowCard match={M[3]} />
          <MatchRowCard match={M[4]} />
        </ContentRow>

        {/* Compétition row */}
        <div style={{ padding: '4px 20px 8px' }}>
          <div style={{ fontFamily: sFONT.title, fontSize: 16, letterSpacing: '-0.02em', marginBottom: 12 }}>COMPÉTITIONS SUIVIES</div>
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
            {[
              ['LIGUE 1', 6, '#003366'],
              ['UCL', 4, '#001f3f'],
              ['PREMIER LEAGUE', 5, '#37003c'],
              ['LIGA', 3, '#ee2737'],
              ['BUNDESLIGA', 4, '#d3010c'],
              ['SERIE A', 3, '#008fd7'],
            ].map(([n, c, col]) => (
              <div key={n} style={{
                background: sBG2, border: `1px solid ${sLINE}`, borderRadius: 8,
                padding: '8px 10px', display: 'flex', alignItems: 'center', gap: 8,
              }}>
                <div style={{ width: 6, height: 16, background: col, borderRadius: 1 }} />
                <div>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sINK, letterSpacing: '0.05em', fontWeight: 700 }}>{n}</div>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: sDIM, letterSpacing: '0.08em' }}>{c} sélections</div>
                </div>
              </div>
            ))}
          </div>
        </div>

      </ScrollContent>
      <BottomNav active={0} />
    </div>
  );
}

// ──────────────────────────────────────────────────────────────────────────────
// 2. MATCH DETAIL — hero poster + 9 critères + IA verdict
// ──────────────────────────────────────────────────────────────────────────────
function ScreenMatch() {
  const match = window.MATCHES[0];
  return (
    <div style={{ height: '100%', background: sBG, color: sINK, position: 'relative', fontFamily: sFONT.ui }}>
      <ScrollContent paddingTop={0}>
        {/* Hero — full bleed */}
        <div style={{ position: 'relative', height: 280 }}>
          <MatchBackdrop home={match.home} away={match.away}>
            {/* top nav */}
            <div style={{ position: 'absolute', top: 60, left: 20, right: 20, display: 'flex', alignItems: 'center', justifyContent: 'space-between', zIndex: 5 }}>
              <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.5)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: `1px solid ${sLINE2}`, color: sINK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="14" height="14" viewBox="0 0 14 14"><path d="M9 2 L4 7 L9 12" stroke={sINK} strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </button>
              <Pill bg="rgba(11,13,16,0.5)" color={sINK} border={sLINE2}>{match.competition.toUpperCase()} · {match.round}</Pill>
              <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.5)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: `1px solid ${sLINE2}`, color: sINK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="14" height="14" viewBox="0 0 14 14"><path d="M3 5 a4 4 0 1 1 0 4 M3 5 L3 2 M3 5 L6 5" stroke={sINK} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </button>
            </div>

            {/* match info */}
            <div style={{ position: 'absolute', bottom: 24, left: 20, right: 20 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
                <div style={{ flex: 1, textAlign: 'right' }}>
                  <TeamBadge code={window.TEAMS[match.home].code} color={window.TEAMS[match.home].color} text={window.TEAMS[match.home].text} size={56} />
                  <div style={{ fontFamily: sFONT.title, fontSize: 18, marginTop: 8, letterSpacing: '-0.02em' }}>{window.TEAMS[match.home].name}</div>
                </div>
                <div style={{ textAlign: 'center' }}>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM, letterSpacing: '0.1em' }}>{match.kickoff}</div>
                  <div style={{ fontFamily: sFONT.title, fontSize: 22, color: sDIM2 }}>VS</div>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sACCENT, letterSpacing: '0.1em', marginTop: 4 }}>SCORE IA<br/>{match.score}</div>
                </div>
                <div style={{ flex: 1 }}>
                  <TeamBadge code={window.TEAMS[match.away].code} color={window.TEAMS[match.away].color} text={window.TEAMS[match.away].text} size={56} />
                  <div style={{ fontFamily: sFONT.title, fontSize: 18, marginTop: 8, letterSpacing: '-0.02em' }}>{window.TEAMS[match.away].name}</div>
                </div>
              </div>
            </div>
          </MatchBackdrop>
        </div>

        {/* Tabs */}
        <div style={{ display: 'flex', gap: 18, padding: '14px 20px', borderBottom: `1px solid ${sLINE}`, background: sBG, position: 'sticky', top: 0, zIndex: 10 }}>
          {['ANALYSE', '9 CRITÈRES', 'MARCHÉ', 'H2H'].map((t, i) => (
            <div key={t} style={{ fontFamily: sFONT.mono, fontSize: 11, letterSpacing: '0.12em', color: i === 1 ? sACCENT : sDIM, paddingBottom: 6, borderBottom: i === 1 ? `2px solid ${sACCENT}` : '2px solid transparent' }}>{t}</div>
          ))}
        </div>

        {/* IA verdict card */}
        <div style={{ padding: '20px 20px 14px' }}>
          <div style={{ background: sBG2, border: `1px solid ${sLINE}`, borderRadius: 14, padding: 18, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: -40, right: -40, width: 160, height: 160, background: 'radial-gradient(circle, rgba(232,255,54,0.10), transparent 70%)' }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, position: 'relative' }}>
              <ConfidenceRing value={match.confidence} size={76} stroke={6} />
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.15em' }}>CONSEIL IA</div>
                <div style={{ fontFamily: sFONT.title, fontSize: 22, color: sINK, marginTop: 4, lineHeight: 1.1, letterSpacing: '-0.02em' }}>{match.pick.type}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginTop: 8 }}>
                  <OddsChip value={match.pick.odds} highlight={true} />
                  <span style={{ fontFamily: sFONT.mono, fontSize: 11, color: sACCENT, letterSpacing: '0.05em' }}>CONFIANCE FORTE</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* 9 critères */}
        <div style={{ padding: '6px 20px 16px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 6 }}>
            <div style={{ fontFamily: sFONT.title, fontSize: 16, letterSpacing: '-0.02em' }}>9 critères analysés</div>
            <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.1em' }}>7 PRO · 2 NEUTRES</div>
          </div>
          <div>
            {match.criteria.map((c, i) => (
              <CriterionRow key={c.name} index={i + 1} name={c.name} value={c.value} signal={c.signal} detail={c.detail} />
            ))}
          </div>
        </div>

        {/* CTA */}
        <div style={{ padding: '8px 20px 20px' }}>
          <button style={{ width: '100%', height: 52, background: sACCENT, color: sBG, border: 'none', borderRadius: 12, fontFamily: sFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>
            AJOUTER AU CARNET →
          </button>
        </div>
      </ScrollContent>
      <BottomNav active={0} />
    </div>
  );
}

// ──────────────────────────────────────────────────────────────────────────────
// 3. CARNET DU JOUR — the 3-pick combo, fully detailed
// ──────────────────────────────────────────────────────────────────────────────
function ScreenCoupon() {
  const C = window.COUPON;
  const M = window.MATCHES;
  const pickMatches = C.sélections.map(p => M.find(m => m.id === p.matchId));
  return (
    <div style={{ height: '100%', background: sBG, color: sINK, position: 'relative', fontFamily: sFONT.ui }}>
      <ScrollContent>
        <AppHeader />

        {/* Header */}
        <div style={{ padding: '6px 20px 14px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sACCENT, letterSpacing: '0.18em' }}>CARNET · {C.date.toUpperCase()}</div>
          <div style={{ fontFamily: sFONT.title, fontSize: 32, letterSpacing: '-0.04em', marginTop: 6 }}>3 sélections IA<br/>combinés.</div>
        </div>

        {/* Hero card */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ background: sBG2, border: `1px solid ${sLINE}`, borderRadius: 16, padding: 20, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: -50, right: -30, width: 220, height: 220, background: 'radial-gradient(circle, rgba(232,255,54,0.14), transparent 70%)' }} />
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative' }}>
              <div>
                <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.15em' }}>INDICE COMBINÉ</div>
                <div style={{ fontFamily: sFONT.title, fontSize: 56, color: sACCENT, lineHeight: 0.9, letterSpacing: '-0.05em', marginTop: 4 }}>@{C.total.toFixed(2)}</div>
              </div>
              <ConfidenceRing value={C.confidence} size={88} stroke={6} />
            </div>
            {/* mise / gain */}
            <div style={{ display: 'flex', marginTop: 18, gap: 12, position: 'relative' }}>
              <div style={{ flex: 1, padding: '12px 14px', background: sBG, border: `1px solid ${sLINE2}`, borderRadius: 10 }}>
                <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: sDIM, letterSpacing: '0.15em' }}>ENGAGEMENT</div>
                <div style={{ fontFamily: sFONT.mono, fontSize: 18, color: sINK, fontWeight: 700, marginTop: 4 }}>{C.stake}€</div>
              </div>
              <div style={{ flex: 1, padding: '12px 14px', background: sBG, border: `1px solid ${sACCENT}`, borderRadius: 10 }}>
                <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: sACCENT, letterSpacing: '0.15em' }}>RETOUR POSSIBLE</div>
                <div style={{ fontFamily: sFONT.mono, fontSize: 18, color: sACCENT, fontWeight: 700, marginTop: 4 }}>{(C.stake * C.total).toFixed(2)}€</div>
              </div>
            </div>
          </div>
        </div>

        {/* Picks */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.18em', marginBottom: 10 }}>LES 3 SÉLECTIONS</div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            {pickMatches.map((m, i) => {
              const p = C.sélections[i];
              const h = window.TEAMS[m.home];
              const a = window.TEAMS[m.away];
              return (
                <div key={p.matchId} style={{ background: sBG2, border: `1px solid ${sLINE}`, borderRadius: 12, overflow: 'hidden' }}>
                  <div style={{ height: 60, position: 'relative' }}>
                    <MatchBackdrop home={m.home} away={m.away}>
                      <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', padding: '0 14px', gap: 10 }}>
                        <TeamBadge code={h.code} color={h.color} text={h.text} size={28} />
                        <div style={{ fontFamily: sFONT.title, fontSize: 14, color: sINK, letterSpacing: '-0.02em' }}>{h.short} – {a.short}</div>
                        <TeamBadge code={a.code} color={a.color} text={a.text} size={28} />
                        <div style={{ flex: 1 }} />
                        <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.1em' }}>{m.kickoff}</div>
                      </div>
                    </MatchBackdrop>
                  </div>
                  <div style={{ padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12 }}>
                    <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: sDIM2, letterSpacing: '0.15em', width: 20 }}>0{i+1}</div>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontSize: 13, color: sINK, fontWeight: 600 }}>{p.type}</div>
                      <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, marginTop: 2, letterSpacing: '0.08em' }}>{m.competition.toUpperCase()} · CONF. {p.confidence}%</div>
                    </div>
                    <OddsChip value={p.odds} highlight={p.confidence >= 85} />
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Multiplier viz */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ padding: '14px 16px', background: sBG2, borderRadius: 12, border: `1px solid ${sLINE}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM, letterSpacing: '0.05em' }}>1.65 × 1.78 × 1.55</div>
            <div style={{ fontFamily: sFONT.title, fontSize: 16, color: sACCENT, letterSpacing: '-0.02em' }}>= @{C.total.toFixed(2)}</div>
          </div>
        </div>

        {/* Place */}
        <div style={{ padding: '0 20px 20px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          <button style={{ width: '100%', height: 54, background: sACCENT, color: sBG, border: 'none', borderRadius: 12, fontFamily: sFONT.title, fontSize: 14, letterSpacing: '0.06em' }}>
            ARCHIVER LE CARNET →
          </button>
          <button style={{ width: '100%', height: 42, background: 'transparent', color: sINK, border: `1px solid ${sLINE2}`, borderRadius: 12, fontFamily: sFONT.mono, fontSize: 11, letterSpacing: '0.12em' }}>
            PARTAGER LE CARNET
          </button>
        </div>
      </ScrollContent>
      <BottomNav active={1} />
    </div>
  );
}

// ──────────────────────────────────────────────────────────────────────────────
// 4. NOTIFICATIONS — feed
// ──────────────────────────────────────────────────────────────────────────────
function ScreenNotif() {
  const items = [
    { kind: 'coupon', t: '09:30',     d: 'COTA',    title: 'Carnet du jour disponible', sub: '3 sélections combinées · @4.55 · confiance 87%', new: true },
    { kind: 'live',   t: 'il y a 12min', d: 'LIVE',  title: 'PSG-OM commence dans 30min', sub: 'Indice PSG passé de 1.72 → 1.65', new: true },
    { kind: 'win',    t: 'Hier 22:48', d: '✓ GAGNÉ', title: 'Carnet validé : +44.20€', sub: 'PSG · LIV · RMA — tous les sélections validées' },
    { kind: 'win',    t: 'Hier 21:35', d: '✓ VALIDÉ', title: 'PSG–OM : Victoire PSG 2–1', sub: 'Sélection @1.65 validé · retour individuel +€16.50' },
    { kind: 'live',   t: 'Hier 20:50', d: 'LIVE',  title: 'PSG–OM : but de Mbappé (62\')', sub: '2–1 PSG · sélection sur la voie de la validation' },
    { kind: 'cote',   t: '17 mai 14:00', d: 'COTE',  title: 'Real–Bayern : NOTE passe à @1.55', sub: 'Indice favorable détecté par l\'IA' },
    { kind: 'loss',   t: '16 mai 23:10', d: '✗ PERDU', title: 'Carnet manqué : -10€', sub: 'OL–Lille : 1–1 alors que indice + attendu' },
  ];

  const tone = (k) => k === 'win' ? sWIN : k === 'loss' ? sLOSS : sACCENT;

  return (
    <div style={{ height: '100%', background: sBG, color: sINK, position: 'relative', fontFamily: sFONT.ui }}>
      <ScrollContent>
        <AppHeader right={
          <button style={{ background: 'transparent', border: 'none', color: sDIM, fontFamily: sFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>TOUT LIRE</button>
        } />

        <div style={{ padding: '4px 20px 12px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sACCENT, letterSpacing: '0.18em' }}>05 — NOTIFICATIONS</div>
          <div style={{ fontFamily: sFONT.title, fontSize: 28, letterSpacing: '-0.03em', marginTop: 4 }}>Activité</div>
        </div>

        <div style={{ padding: '0 20px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          {items.map((it, i) => (
            <div key={i} style={{
              display: 'flex', gap: 12, padding: '14px 14px',
              background: it.new ? sBG2 : sBG,
              borderRadius: 12, border: `1px solid ${it.new ? sLINE2 : sLINE}`,
              position: 'relative',
            }}>
              <div style={{
                width: 38, height: 38, borderRadius: 10,
                background: it.kind === 'win' ? 'rgba(61,220,145,0.15)' : it.kind === 'loss' ? 'rgba(255,91,58,0.15)' : sBG3,
                color: tone(it.kind),
                display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                fontFamily: sFONT.mono, fontSize: 14, fontWeight: 700,
              }}>
                {it.kind === 'win' && '✓'}
                {it.kind === 'loss' && '✗'}
                {it.kind === 'live' && '◉'}
                {it.kind === 'coupon' && '★'}
                {it.kind === 'cote' && '↗'}
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: tone(it.kind), letterSpacing: '0.15em', fontWeight: 700 }}>{it.d}</div>
                  <div style={{ fontFamily: sFONT.mono, fontSize: 9, color: sDIM, letterSpacing: '0.05em' }}>{it.t}</div>
                </div>
                <div style={{ fontSize: 13, fontWeight: 600, marginTop: 4, color: sINK }}>{it.title}</div>
                <div style={{ fontSize: 11, color: sDIM, marginTop: 2 }}>{it.sub}</div>
              </div>
              {it.new && <div style={{ position: 'absolute', top: 14, right: 14, width: 7, height: 7, borderRadius: 4, background: sACCENT }} />}
            </div>
          ))}
        </div>
      </ScrollContent>
      <BottomNav active={2} />
    </div>
  );
}

// ──────────────────────────────────────────────────────────────────────────────
// 5. PROFILE / STATS
// ──────────────────────────────────────────────────────────────────────────────
function ScreenProfile() {
  return (
    <div style={{ height: '100%', background: sBG, color: sINK, position: 'relative', fontFamily: sFONT.ui }}>
      <ScrollContent>
        <AppHeader />

        <div style={{ padding: '6px 20px 18px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sACCENT, letterSpacing: '0.18em' }}>PROFIL</div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginTop: 12 }}>
            <div style={{ width: 60, height: 60, borderRadius: 30, background: sBG2, border: `2px solid ${sACCENT}`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: sFONT.title, fontSize: 22, color: sACCENT }}>K</div>
            <div>
              <div style={{ fontFamily: sFONT.title, fontSize: 22, letterSpacing: '-0.02em' }}>Karim B.</div>
              <div style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM, letterSpacing: '0.1em', marginTop: 2 }}>MEMBRE DEPUIS NOV. 2025</div>
            </div>
          </div>
        </div>

        {/* Hero stats */}
        <div style={{ padding: '0 20px 20px', display: 'flex', gap: 8 }}>
          <StatBlock value="+18.5%" label="ROI SAISON" accent={true} />
          <StatBlock value="47/59" label="SÉLECTIONS VALIDÉES" />
          <StatBlock value="4" label="STREAK ✓" accent={true} />
        </div>

        {/* Performance graph */}
        <div style={{ padding: '0 20px 18px' }}>
          <div style={{ background: sBG2, borderRadius: 12, padding: 16, border: `1px solid ${sLINE}` }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 10 }}>
              <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.15em' }}>PERFORMANCE — 30 JOURS</div>
              <div style={{ fontFamily: sFONT.mono, fontSize: 12, color: sACCENT, fontWeight: 700 }}>+184€</div>
            </div>
            {/* simple sparkline */}
            <svg viewBox="0 0 300 80" width="100%" height="80" style={{ display: 'block' }}>
              <defs>
                <linearGradient id="spark-grad" x1="0" x2="0" y1="0" y2="1">
                  <stop offset="0%" stopColor={sACCENT} stopOpacity="0.4" />
                  <stop offset="100%" stopColor={sACCENT} stopOpacity="0" />
                </linearGradient>
              </defs>
              <path d="M0 60 L20 55 L40 58 L60 50 L80 52 L100 45 L120 48 L140 38 L160 42 L180 30 L200 33 L220 24 L240 28 L260 18 L280 22 L300 12 L300 80 L0 80 Z" fill="url(#spark-grad)" />
              <path d="M0 60 L20 55 L40 58 L60 50 L80 52 L100 45 L120 48 L140 38 L160 42 L180 30 L200 33 L220 24 L240 28 L260 18 L280 22 L300 12" stroke={sACCENT} strokeWidth="1.8" fill="none" />
            </svg>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontFamily: sFONT.mono, fontSize: 9, color: sDIM2, letterSpacing: '0.08em', marginTop: 4 }}>
              <span>15 AVR</span><span>1 MAI</span><span>15 MAI</span>
            </div>
          </div>
        </div>

        {/* Distribution */}
        <div style={{ padding: '0 20px 18px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.18em', marginBottom: 10 }}>RÉPARTITION PAR COMPÉTITION</div>
          {[
            ['Ligue 1', 32, 21, sACCENT],
            ['Champions League', 14, 11, sINK],
            ['Premier League', 8, 5, sDIM],
            ['Liga', 5, 3, sDIM],
          ].map(([n, total, w, color]) => (
            <div key={n} style={{ padding: '10px 0', borderBottom: `1px solid ${sLINE}` }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
                <span style={{ fontSize: 12, color: sINK, fontWeight: 600 }}>{n}</span>
                <span style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM }}>{w}/{total}</span>
              </div>
              <div style={{ height: 4, background: sLINE, borderRadius: 2, overflow: 'hidden' }}>
                <div style={{ height: '100%', width: `${(w / total) * 100}%`, background: color, borderRadius: 2 }} />
              </div>
            </div>
          ))}
        </div>

        {/* Settings */}
        <div style={{ padding: '0 20px 8px' }}>
          <div style={{ fontFamily: sFONT.mono, fontSize: 10, color: sDIM, letterSpacing: '0.18em', marginBottom: 10 }}>RÉGLAGES</div>
          {[
            ['Notifications', 'À 9h30'],
            ['Compétitions suivies', '6 ligues'],
            ['Source préférée', 'Aucun'],
            ['Mode carnet', 'Combiné 3 sélections'],
          ].map(([k, v]) => (
            <div key={k} style={{ display: 'flex', justifyContent: 'space-between', padding: '14px 0', borderBottom: `1px solid ${sLINE}` }}>
              <span style={{ fontSize: 13, color: sINK }}>{k}</span>
              <span style={{ fontFamily: sFONT.mono, fontSize: 11, color: sDIM, display: 'flex', alignItems: 'center', gap: 6 }}>
                {v}
                <svg width="10" height="10" viewBox="0 0 10 10"><path d="M3 1 L7 5 L3 9" stroke={sDIM} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </span>
            </div>
          ))}
        </div>
      </ScrollContent>
      <BottomNav active={3} />
    </div>
  );
}

Object.assign(window, { ScreenHome, ScreenMatch, ScreenCoupon, ScreenNotif, ScreenProfile });
