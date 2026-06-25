// COTA V2 — Section B: new app screens (History, Coupon detail, Premium, Bookmakers, Settings).

const { BG: bBG, BG2: bBG2, BG3: bBG3, LINE: bLINE, LINE2: bLINE2, INK: bINK, INK2: bINK2, DIM: bDIM, DIM2: bDIM2, ACCENT: bACCENT, WIN: bWIN, LOSS: bLOSS, font: bFONT } = window.COTA;

function ScrollPage({ children, paddingBottom = 90, paddingTop = 60 }) {
  return <div style={{ height: '100%', overflowY: 'auto', paddingTop, paddingBottom, background: bBG }}>{children}</div>;
}

// ── Flashscore-style comparative stat row ────────────────────────────────────
function StatBar({ home, away, label, max = 100, units = '' }) {
  // Treat home/away as numbers when possible — otherwise fall back to neutral split.
  const hn = parseFloat(String(home).replace(',', '.'));
  const an = parseFloat(String(away).replace(',', '.'));
  let homePct = 50;
  if (!isNaN(hn) && !isNaN(an) && hn + an > 0) {
    homePct = hn / (hn + an) * 100;
  }
  return (
    <div style={{ padding: '10px 0', borderBottom: `1px solid ${bLINE}` }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 5 }}>
        <span style={{ fontFamily: bFONT.mono, fontSize: 12, color: bINK, fontWeight: 700 }}>{home}{units}</span>
        <span style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.12em' }}>{label.toUpperCase()}</span>
        <span style={{ fontFamily: bFONT.mono, fontSize: 12, color: bINK, fontWeight: 700 }}>{away}{units}</span>
      </div>
      <div style={{ display: 'flex', height: 4, borderRadius: 2, overflow: 'hidden', background: bLINE }}>
        <div style={{ width: `${homePct}%`, background: bACCENT, transition: 'width 0.3s' }} />
        <div style={{ width: `${100 - homePct}%`, background: bDIM2 }} />
      </div>
    </div>);

}

// ── B0 · Match detail (Flashscore-style, STATS tab active) ───────────────────
function ScreenMatchFlash() {
  const match = window.MATCHES[0]; // PSG-OM
  const h = window.TEAMS[match.home];
  const a = window.TEAMS[match.away];

  // Tabs (active = STATS)
  const Tabs = () =>
  <div style={{ display: 'flex', gap: 18, padding: '12px 20px', borderBottom: `1px solid ${bLINE}`, background: bBG, position: 'sticky', top: 0, zIndex: 10, overflowX: 'auto' }}>
      {['ANALYSE', 'STATS', 'H2H', 'COMPOS', 'COTES'].map((t, i) =>
    <div key={t} style={{ fontFamily: bFONT.mono, fontSize: 11, letterSpacing: '0.12em', color: i === 1 ? bACCENT : bDIM, paddingBottom: 8, borderBottom: i === 1 ? `2px solid ${bACCENT}` : '2px solid transparent', flexShrink: 0 }}>{t}</div>
    )}
    </div>;


  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage paddingTop={0}>
        {/* Hero DAZN poster */}
        <div style={{ position: 'relative', height: 260 }}>
          <MatchBackdrop home={match.home} away={match.away}>
            <div style={{ position: 'absolute', top: 60, left: 16, right: 16, display: 'flex', alignItems: 'center', justifyContent: 'space-between', zIndex: 5 }}>
              <button style={{ width: 34, height: 34, borderRadius: 17, background: 'rgba(11,13,16,0.5)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: `1px solid ${bLINE2}`, color: bINK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="13" height="13" viewBox="0 0 14 14"><path d="M9 2 L4 7 L9 12" stroke={bINK} strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
              </button>
              <Pill bg="rgba(11,13,16,0.5)" color={bINK} border={bLINE2}>{match.competition.toUpperCase()} · {match.round}</Pill>
              <button style={{ width: 34, height: 34, borderRadius: 17, background: 'rgba(11,13,16,0.5)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: `1px solid ${bLINE2}`, color: bINK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="13" height="13" viewBox="0 0 14 14"><circle cx="4" cy="4" r="1.5" fill={bINK} /><circle cx="11" cy="4" r="1.5" fill={bINK} /><circle cx="11" cy="11" r="1.5" fill={bINK} /><path d="M5 5 L10 4 M5 10 L10 10.5" stroke={bINK} strokeWidth="1" /></svg>
              </button>
            </div>
            <div style={{ position: 'absolute', bottom: 22, left: 16, right: 16 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                <div style={{ flex: 1, textAlign: 'right' }}>
                  <TeamBadge code={h.code} color={h.color} text={h.text} size={50} />
                  <div style={{ fontFamily: bFONT.title, fontSize: 16, marginTop: 6, letterSpacing: '-0.02em' }}>{h.short}</div>
                </div>
                <div style={{ textAlign: 'center' }}>
                  <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.12em' }}>{match.kickoff}</div>
                  <div style={{ fontFamily: bFONT.title, fontSize: 18, color: bDIM2, marginTop: 2 }}>VS</div>
                  <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bACCENT, letterSpacing: '0.1em', marginTop: 4 }}>SCORE IA · {match.score}</div>
                </div>
                <div style={{ flex: 1 }}>
                  <TeamBadge code={a.code} color={a.color} text={a.text} size={50} />
                  <div style={{ fontFamily: bFONT.title, fontSize: 16, marginTop: 6, letterSpacing: '-0.02em' }}>{a.short}</div>
                </div>
              </div>
            </div>
          </MatchBackdrop>
        </div>

        <Tabs />

        {/* Verdict IA card */}
        <div style={{ padding: '14px 20px 12px' }}>
          <div style={{ background: bBG2, border: `1px solid ${bLINE}`, borderRadius: 12, padding: 14, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: -30, right: -30, width: 120, height: 120, background: 'radial-gradient(circle, rgba(232,255,54,0.10), transparent 70%)' }} />
            <div style={{ display: 'flex', alignItems: 'center', gap: 14, position: 'relative' }}>
              <ConfidenceRing value={match.confidence} size={64} stroke={5} />
              <div style={{ flex: 1 }}>
                <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.15em' }}>VERDICT IA</div>
                <div style={{ fontFamily: bFONT.title, fontSize: 17, color: bINK, marginTop: 3, letterSpacing: '-0.02em' }}>{match.pick.type}</div>
                <OddsChip value={match.pick.odds} highlight={true} size="sm" />
              </div>
            </div>
            <div style={{ marginTop: 12, height: 4, background: bLINE, borderRadius: 2, overflow: 'hidden' }}>
              <div style={{ width: `${match.confidence}%`, height: '100%', background: bACCENT }} />
            </div>
          </div>
        </div>

        {/* Comparative stats — Flashscore style */}
        <div style={{ padding: '6px 20px 18px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 4 }}>
            <span style={{ fontFamily: bFONT.title, fontSize: 14, letterSpacing: '0.02em', color: bINK }}>{h.short}</span>
            <span style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em' }}>STATISTIQUES</span>
            <span style={{ fontFamily: bFONT.title, fontSize: 14, letterSpacing: '0.02em', color: bINK }}>{a.short}</span>
          </div>
          <StatBar home="64" away="36" label="Possession" units="%" />
          <StatBar home="8" away="4" label="Tirs cadrés" />
          <StatBar home="2.8" away="1.1" label="xG (buts attendus)" />
          <StatBar home="89" away="54" label="Dom / Ext" units="%" />
          <StatBar home="0" away="2" label="Blessures clés" />
          <StatBar home="14" away="11" label="Corners moy." />
          <StatBar home="2.4" away="3.1" label="Cartons moy." />
        </div>

        {/* H2H mini block */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
            <span style={{ fontFamily: bFONT.title, fontSize: 14, letterSpacing: '-0.02em', color: bINK }}>10 confrontations</span>
            <div style={{ display: 'flex', gap: 4 }}>
              <Pill bg="rgba(61,220,145,0.12)" color={bWIN}>6V</Pill>
              <Pill bg="rgba(139,138,133,0.12)" color={bDIM}>2N</Pill>
              <Pill bg="rgba(255,91,58,0.12)" color={bLOSS}>2D</Pill>
            </div>
          </div>
          {[
          { d: '18 mai 25', score: '2–0', h: 'PSG', a: 'OM', won: 'h', comp: 'L1' },
          { d: '12 oct 24', score: '1–1', h: 'PSG', a: 'OM', won: 'd', comp: 'L1' },
          { d: '5 mar 24', score: '3–1', h: 'PSG', a: 'OM', won: 'h', comp: 'CdF' },
          { d: '22 oct 23', score: '2–1', h: 'OM', a: 'PSG', won: 'a', comp: 'L1' }].
          map((r, i) => {
            const c = r.won === 'h' ? bWIN : r.won === 'a' ? bLOSS : bDIM;
            return (
              <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 0', borderBottom: `1px solid ${bLINE}` }}>
                <div style={{ width: 3, height: 28, background: c, borderRadius: 2 }} />
                <span style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.05em', width: 70 }}>{r.d}</span>
                <span style={{ flex: 1, fontFamily: bFONT.mono, fontSize: 12, color: bINK, fontWeight: 700, textAlign: 'center', letterSpacing: '0.05em' }}>{r.h} {r.score} {r.a}</span>
                <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.1em' }}>{r.comp}</span>
              </div>);

          })}
        </div>

        {/* Cotes — Flashscore-style 3 colonnes */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em', marginBottom: 10 }}>COTES PAR BOOKMAKER</div>
          <div style={{ background: bBG2, border: `1px solid ${bLINE}`, borderRadius: 10, overflow: 'hidden' }}>
            {/* Header */}
            <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr 1fr 1fr', padding: '10px 14px', background: bBG3, borderBottom: `1px solid ${bLINE}` }}>
              <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.18em' }}>BOOKMAKER</span>
              <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.18em', textAlign: 'center' }}>1</span>
              <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.18em', textAlign: 'center' }}>X</span>
              <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.18em', textAlign: 'center' }}>2</span>
            </div>
            {[
            ['1xBet', '1.65', '4.20', '4.50', true],
            ['Betwinner', '1.68', '4.10', '4.45'],
            ['Melbet', '1.62', '4.30', '4.60'],
            ['Bet365', '1.72', '4.00', '4.20']].
            map(([n, x, y, z, best], i) =>
            <div key={n} style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr 1fr 1fr', padding: '11px 14px', borderTop: i ? `1px solid ${bLINE}` : 'none', alignItems: 'center' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                  <span style={{ fontSize: 12, color: bINK, fontWeight: 600 }}>{n}</span>
                  {best && <Pill bg={bACCENT} color={bBG}>MEILLEURE</Pill>}
                </div>
                <span style={{ fontFamily: bFONT.mono, fontSize: 12, color: best ? bACCENT : bINK, fontWeight: 700, textAlign: 'center' }}>{x}</span>
                <span style={{ fontFamily: bFONT.mono, fontSize: 12, color: bDIM, fontWeight: 500, textAlign: 'center' }}>{y}</span>
                <span style={{ fontFamily: bFONT.mono, fontSize: 12, color: bDIM, fontWeight: 500, textAlign: 'center' }}>{z}</span>
              </div>
            )}
          </div>
          <button style={{ width: '100%', height: 48, background: bACCENT, color: bBG, border: 'none', borderRadius: 10, fontFamily: bFONT.title, fontSize: 13, letterSpacing: '0.06em', marginTop: 14 }}>
            PLACER CE PARI →
          </button>
        </div>
      </ScrollPage>
      <BottomNav active={0} />
    </div>);

}


// ── B1 · History ─────────────────────────────────────────────────────────────
function ScreenHistory() {
  const items = [
  { kind: 'won', date: 'MAR 18 MAI', picks: 3, odds: '@4.55', detail: 'PSG ✓ · LIV ✓ · RMA ✓', gain: '+44.20€', stake: 'Mise 10€' },
  { kind: 'won', date: 'LUN 17 MAI', picks: 3, odds: '@3.12', detail: 'OM ✓ · BAY ✓ · JUV ✓', gain: '+31.20€', stake: 'Mise 10€' },
  { kind: 'lost', date: 'DIM 16 MAI', picks: 3, odds: '@5.10', detail: 'OL ✗ · LIV ✓ · MCI ✓', gain: '-10€', stake: 'Mise 10€' },
  { kind: 'wait', date: 'MER 21 MAI', picks: 3, odds: '@4.21', detail: 'RMA ? · ARS ? · PSG ?', gain: 'Ce soir', stake: 'Mise 10€' },
  { kind: 'won', date: 'SAM 15 MAI', picks: 4, odds: '@6.80', detail: 'BAY ✓ · LIV ✓ · MCI ✓ · PSG ✓', gain: '+68.00€', stake: 'Mise 10€' }];

  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage>
        <AppHeader />
        <div style={{ padding: '4px 20px 12px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bACCENT, letterSpacing: '0.18em' }}>HISTORIQUE</div>
          <div style={{ fontFamily: bFONT.title, fontSize: 28, letterSpacing: '-0.03em', marginTop: 4 }}>Tes coupons</div>
        </div>

        {/* Filters */}
        <div style={{ display: 'flex', gap: 8, padding: '6px 20px 16px', overflowX: 'auto' }}>
          {['TOUS', 'GAGNÉS', 'PERDUS', 'EN COURS'].map((t, i) =>
          <div key={t} style={{
            padding: '7px 12px', borderRadius: 999, flexShrink: 0,
            background: i === 0 ? bACCENT : bBG2,
            color: i === 0 ? bBG : bDIM,
            border: i === 0 ? 'none' : `1px solid ${bLINE2}`,
            fontFamily: bFONT.mono, fontSize: 10, letterSpacing: '0.1em', fontWeight: 700
          }}>{t}</div>
          )}
        </div>

        {/* Stats */}
        <div style={{ display: 'flex', gap: 8, padding: '0 20px 18px' }}>
          {[
          ['12', 'CE MOIS', null],
          ['72%', 'TAUX RÉUSSITE', bACCENT],
          ['+€84', 'GAIN NET', bACCENT]].
          map(([v, l, c]) =>
          <div key={l} style={{ flex: 1, padding: '12px 10px', background: bBG2, borderRadius: 10, border: `1px solid ${bLINE}` }}>
              <div style={{ fontFamily: bFONT.title, fontSize: 22, color: c || bINK, letterSpacing: '-0.02em' }}>{v}</div>
              <div style={{ fontFamily: bFONT.mono, fontSize: 8, color: bDIM, letterSpacing: '0.15em', marginTop: 4 }}>{l}</div>
            </div>
          )}
        </div>

        {/* List */}
        <div style={{ padding: '0 20px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          {items.map((it, i) => {
            const colors = it.kind === 'won' ? { bg: 'rgba(61,220,145,0.06)', bd: bWIN, fg: bWIN, badge: '✓ GAGNÉ' } :
            it.kind === 'lost' ? { bg: 'rgba(255,91,58,0.06)', bd: bLOSS, fg: bLOSS, badge: '✗ PERDU' } :
            { bg: bBG2, bd: bLINE2, fg: bDIM, badge: '⏳ EN COURS' };
            return (
              <div key={i} style={{ background: colors.bg, border: `1px solid ${colors.bd}`, borderRadius: 12, padding: '14px 14px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
                  <div>
                    <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: colors.fg, letterSpacing: '0.15em', fontWeight: 700 }}>{colors.badge}</div>
                    <div style={{ fontFamily: bFONT.mono, fontSize: 11, color: bDIM, letterSpacing: '0.08em', marginTop: 4 }}>{it.date}</div>
                  </div>
                  <div style={{ fontFamily: bFONT.title, fontSize: 18, color: it.kind === 'won' ? bWIN : it.kind === 'lost' ? bLOSS : bINK, letterSpacing: '-0.02em' }}>{it.gain}</div>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', borderTop: `1px solid ${bLINE}`, paddingTop: 8 }}>
                  <div style={{ fontSize: 12, color: bINK }}>{it.picks} picks · <span style={{ fontFamily: bFONT.mono, color: bACCENT, fontWeight: 700 }}>{it.odds}</span></div>
                  <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.05em' }}>{it.stake}</div>
                </div>
                <div style={{ fontSize: 11, color: bINK2, marginTop: 6 }}>{it.detail}</div>
              </div>);

          })}
        </div>
      </ScrollPage>
      <BottomNav active={2} />
    </div>);

}

// ── B2 · Detail coupon (won state) ───────────────────────────────────────────
function ScreenCouponDetail() {
  const picks = [
  { n: '01', m: 'PSG–OM', type: 'Victoire PSG', odds: '@1.65', score: '2–1' },
  { n: '02', m: 'LIV–ARS', type: '+2.5 buts', odds: '@1.78', score: '3–2' },
  { n: '03', m: 'RMA–BAY', type: 'BTTS Oui', odds: '@1.55', score: '2–1' }];

  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '0 20px 12px' }}>
          <button style={{ width: 32, height: 32, borderRadius: 16, background: bBG2, border: `1px solid ${bLINE2}`, color: bINK, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="12" height="12" viewBox="0 0 12 12"><path d="M8 2 L4 6 L8 10" stroke={bINK} strokeWidth="1.5" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
          </button>
          <div>
            <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.15em' }}>COUPON HISTORIQUE</div>
            <div style={{ fontFamily: bFONT.title, fontSize: 20, letterSpacing: '-0.02em' }}>Coupon du 18 mai</div>
          </div>
        </div>

        {/* Hero won card */}
        <div style={{ padding: '0 20px 18px' }}>
          <div style={{ background: 'rgba(61,220,145,0.08)', border: `1px solid ${bWIN}`, borderRadius: 16, padding: 22, position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: -40, right: -40, width: 200, height: 200, background: 'radial-gradient(circle, rgba(61,220,145,0.2), transparent 70%)' }} />

            {/* stamp */}
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '6px 12px', background: bWIN, color: bBG, borderRadius: 6, boxShadow: '0 6px 16px rgba(61,220,145,0.3)', marginBottom: 16 }}>
              <svg width="12" height="12" viewBox="0 0 12 12"><path d="M2 6 L5 9 L10 3" stroke={bBG} strokeWidth="2.2" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
              <span style={{ fontFamily: bFONT.title, fontSize: 11, letterSpacing: '0.1em' }}>COUPON VALIDÉ</span>
            </div>

            <div style={{ fontFamily: bFONT.title, fontSize: 56, color: bWIN, letterSpacing: '-0.05em', lineHeight: 0.9 }}>+44.20€</div>
            <div style={{ fontFamily: bFONT.mono, fontSize: 11, color: bINK2, letterSpacing: '0.08em', marginTop: 8 }}>
              Mise 10€ · Cote @4.55 · 3/3 picks gagnants
            </div>
          </div>
        </div>

        {/* Picks */}
        <div style={{ padding: '0 20px 16px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em', marginBottom: 10 }}>LES 3 PICKS · TOUS VALIDÉS</div>
          {picks.map((p, i) =>
          <div key={p.n} style={{ background: bBG2, border: `1px solid ${bLINE}`, borderRadius: 10, padding: 14, marginBottom: 8 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 6 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM2, letterSpacing: '0.15em' }}>{p.n}</span>
                  <span style={{ fontFamily: bFONT.title, fontSize: 15, color: bINK, letterSpacing: '-0.02em' }}>{p.m}</span>
                </div>
                <span style={{ fontFamily: bFONT.mono, fontSize: 14, color: bACCENT, fontWeight: 700 }}>{p.odds}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: `1px solid ${bLINE2}`, paddingTop: 8, marginTop: 6 }}>
                <div style={{ fontSize: 12, color: bINK2 }}>{p.type}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.05em' }}>SCORE {p.score}</span>
                  <span style={{ width: 18, height: 18, borderRadius: 9, background: 'rgba(61,220,145,0.15)', color: bWIN, display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
                    <svg width="10" height="10" viewBox="0 0 10 10"><path d="M1.5 5 L4 7.5 L8.5 2.5" stroke={bWIN} strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
                  </span>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* CTA */}
        <div style={{ padding: '4px 20px 20px' }}>
          <button style={{ width: '100%', height: 52, background: bACCENT, color: bBG, border: 'none', borderRadius: 12, fontFamily: bFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>REJOUER CE TYPE DE COUPON →</button>
          <button style={{ width: '100%', height: 42, background: 'transparent', color: bINK, border: `1px solid ${bLINE2}`, borderRadius: 12, fontFamily: bFONT.mono, fontSize: 11, letterSpacing: '0.12em', marginTop: 8 }}>PARTAGER</button>
        </div>
      </ScrollPage>
      <BottomNav active={2} />
    </div>);

}

// ── B3 · Premium ─────────────────────────────────────────────────────────────
function ScreenPremium() {
  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage>
        <AppHeader />

        <div style={{ padding: '4px 20px 16px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bACCENT, letterSpacing: '0.18em' }}>PREMIUM</div>
          <div style={{ fontFamily: bFONT.title, fontSize: 32, letterSpacing: '-0.04em', lineHeight: 1.0, marginTop: 4 }}>Débloque tout.</div>
        </div>

        {/* Comparison */}
        <div style={{ display: 'flex', gap: 8, padding: '0 20px 18px' }}>
          {/* Gratuit */}
          <div style={{ flex: 1, padding: '14px 12px', background: bBG2, border: `1px solid ${bLINE}`, borderRadius: 12 }}>
            <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.15em' }}>GRATUIT</div>
            <div style={{ fontFamily: bFONT.title, fontSize: 20, color: bINK, letterSpacing: '-0.02em', marginTop: 4 }}>0€</div>
            <div style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 8 }}>
              {[
              ['★★ max', false],
              ['Pas de coupon IA', false],
              ['Historique 7j', false],
              ['Sans alertes cotes', false]].
              map(([l, ok]) =>
              <div key={l} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 11, color: bDIM }}>
                  <span style={{ width: 14, height: 14, borderRadius: 7, background: bBG3, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <svg width="7" height="7" viewBox="0 0 7 7"><path d="M1 1 L6 6 M6 1 L1 6" stroke={bDIM2} strokeWidth="1.4" strokeLinecap="round" /></svg>
                  </span>
                  {l}
                </div>
              )}
            </div>
          </div>
          {/* Premium */}
          <div style={{ flex: 1, padding: '14px 12px', background: bBG3, border: `1.5px solid ${bACCENT}`, borderRadius: 12, position: 'relative', boxShadow: '0 8px 30px rgba(232,255,54,0.08)' }}>
            <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bACCENT, letterSpacing: '0.15em' }}>PREMIUM</div>
            <div style={{ fontFamily: bFONT.title, fontSize: 20, color: bACCENT, letterSpacing: '-0.02em', marginTop: 4 }}>Tout débloqué</div>
            <div style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 8 }}>
              {[
              '★★★★ (toutes)',
              'Coupon IA quotidien',
              'Historique illimité',
              'Alertes cotes live'].
              map((l) =>
              <div key={l} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 11, color: bINK, fontWeight: 600 }}>
                  <span style={{ width: 14, height: 14, borderRadius: 7, background: bACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <svg width="8" height="8" viewBox="0 0 8 8"><path d="M1 4 L3 6 L7 2" stroke={bBG} strokeWidth="1.8" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>
                  </span>
                  {l}
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Trial banner */}
        <div style={{ padding: '0 20px 14px' }}>
          <div style={{ padding: '10px 14px', background: 'rgba(232,255,54,0.10)', border: `1px solid ${bACCENT}`, borderRadius: 8, display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontFamily: bFONT.title, fontSize: 18, color: bACCENT }}>★</span>
            <div style={{ fontFamily: bFONT.mono, fontSize: 11, color: bACCENT, letterSpacing: '0.1em', fontWeight: 700 }}>ESSAI 14 JOURS OFFERTS</div>
          </div>
        </div>

        {/* Plans */}
        <div style={{ padding: '0 20px 18px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          <div style={{ padding: '14px 16px', background: bBG2, border: `1px solid ${bLINE}`, borderRadius: 12 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
              <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.15em' }}>MENSUEL</div>
              <div style={{ fontFamily: bFONT.title, fontSize: 20, color: bINK, letterSpacing: '-0.02em' }}>4 990 <span style={{ fontFamily: bFONT.mono, fontSize: 11, color: bDIM }}>FCFA / mois</span></div>
            </div>
            <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.05em', marginTop: 6 }}>Wave · Orange Money · MTN · Moov</div>
          </div>
          <div style={{ padding: '14px 16px', background: bBG2, border: `1.5px solid ${bACCENT}`, borderRadius: 12, position: 'relative' }}>
            <div style={{ position: 'absolute', top: -10, right: 14, padding: '3px 8px', background: bACCENT, color: bBG, borderRadius: 4, fontFamily: bFONT.mono, fontSize: 9, fontWeight: 700, letterSpacing: '0.1em' }}>ÉCONOMIE 2 MOIS</div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
              <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bACCENT, letterSpacing: '0.15em' }}>ANNUEL</div>
              <div style={{ fontFamily: bFONT.title, fontSize: 20, color: bACCENT, letterSpacing: '-0.02em' }}>49 900 <span style={{ fontFamily: bFONT.mono, fontSize: 11, color: bDIM }}>FCFA / an</span></div>
            </div>
            <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.05em', marginTop: 6 }}>≈ 4 158 / mois</div>
          </div>
        </div>

        {/* CTA */}
        <div style={{ padding: '0 20px 16px' }}>
          <button style={{ width: '100%', height: 54, background: bACCENT, color: bBG, border: 'none', borderRadius: 12, fontFamily: bFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>COMMENCER L'ESSAI GRATUIT →</button>
          <div style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.12em', textAlign: 'center', marginTop: 8 }}>SANS ENGAGEMENT · ANNULE QUAND TU VEUX</div>
        </div>

        {/* Payment logos */}
        <div style={{ padding: '0 20px 20px', display: 'flex', justifyContent: 'center', gap: 14, alignItems: 'center' }}>
          {['Wave', 'Orange', 'MTN', 'Moov'].map((p) =>
          <div key={p} style={{ padding: '6px 10px', background: bBG2, borderRadius: 6, border: `1px solid ${bLINE}`, fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.1em' }}>{p}</div>
          )}
        </div>
      </ScrollPage>
      <BottomNav active={3} />
    </div>);

}

// ── B4 · Bookmakers ──────────────────────────────────────────────────────────
function ScreenBookmakers() {
  const REGIONS = ['AFRIQUE DE L\'OUEST', 'EUROPE', 'MONDE'];
  const books = [
  { name: '1xBET', odds: '@1.65', bonus: '100% jusqu\'à 100€', cta: 'OUVRIR LE COMPTE', recommended: true },
  { name: 'Betwinner', odds: '@1.68', bonus: '130% jusqu\'à 130€', cta: 'OBTENIR LE BONUS' },
  { name: 'Melbet', odds: '@1.62', bonus: '100% jusqu\'à 100€', cta: 'VOIR L\'OFFRE' },
  { name: 'Premier Bet', odds: '@1.60', bonus: '50% jusqu\'à 50€', cta: 'VOIR L\'OFFRE' }];

  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage>
        <AppHeader />
        <div style={{ padding: '4px 20px 14px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bACCENT, letterSpacing: '0.18em' }}>BOOKMAKERS</div>
          <div style={{ fontFamily: bFONT.title, fontSize: 24, letterSpacing: '-0.03em', lineHeight: 1.1, marginTop: 4 }}>Les meilleures cotes,<br />par région.</div>
        </div>

        {/* Region pills */}
        <div style={{ display: 'flex', gap: 8, padding: '0 20px 18px', overflowX: 'auto' }}>
          {REGIONS.map((r, i) =>
          <div key={r} style={{
            padding: '8px 14px', borderRadius: 999, flexShrink: 0,
            background: i === 0 ? bACCENT : bBG2,
            color: i === 0 ? bBG : bDIM,
            border: i === 0 ? 'none' : `1px solid ${bLINE2}`,
            fontFamily: bFONT.mono, fontSize: 10, letterSpacing: '0.1em', fontWeight: 700
          }}>{r}</div>
          )}
        </div>

        {/* Cards */}
        <div style={{ padding: '0 20px 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          {books.map((b) =>
          <div key={b.name} style={{
            padding: 16, background: bBG2,
            border: `1px solid ${b.recommended ? bACCENT : bLINE}`,
            borderRadius: 12, position: 'relative'
          }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                <div style={{ width: 44, height: 44, borderRadius: 8, background: bBG3, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: bFONT.title, fontSize: 16, color: bINK, border: `1px solid ${bLINE2}` }}>{b.name[0]}</div>
                <div style={{ flex: 1 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{ fontFamily: bFONT.title, fontSize: 16, color: bINK, letterSpacing: '-0.02em' }}>{b.name}</span>
                    {b.recommended && <Pill bg={bACCENT} color={bBG}>RECOMMANDÉ</Pill>}
                  </div>
                  <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.08em', marginTop: 3 }}>Cote PSG win : <span style={{ color: bACCENT, fontWeight: 700 }}>{b.odds}</span></div>
                </div>
              </div>
              <div style={{ fontSize: 12, color: bINK2, marginBottom: 12, padding: '8px 10px', background: bBG, borderRadius: 6 }}>
                <span style={{ fontFamily: bFONT.mono, fontSize: 9, color: bDIM, letterSpacing: '0.15em' }}>BONUS · </span>{b.bonus}
              </div>
              <button style={{
              width: '100%', height: 42,
              background: b.recommended ? bACCENT : 'transparent',
              color: b.recommended ? bBG : bINK,
              border: b.recommended ? 'none' : `1px solid ${bLINE2}`,
              borderRadius: 10,
              fontFamily: bFONT.title, fontSize: 11, letterSpacing: '0.08em'
            }}>{b.cta} →</button>
            </div>
          )}
        </div>

        {/* Disclaimer */}
        <div style={{ padding: '0 20px 20px', fontFamily: bFONT.mono, fontSize: 9, color: bDIM2, letterSpacing: '0.1em', textAlign: 'center', lineHeight: 1.5 }}>
          LIENS AFFILIÉS · JEU RESPONSABLE · 18+
        </div>
      </ScrollPage>
      <BottomNav active={3} />
    </div>);

}

// ── B5 · Settings ────────────────────────────────────────────────────────────
function ScreenSettings() {
  const SectionsRow = ({ label, value, danger, dim }) =>
  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '13px 0', borderBottom: `1px solid ${bLINE}` }}>
      <span style={{ fontSize: 13, color: danger ? bLOSS : dim ? bDIM2 : bINK }}>{label}</span>
      <span style={{ fontFamily: bFONT.mono, fontSize: 11, color: bDIM, display: 'flex', alignItems: 'center', gap: 8 }}>
        {value}
        {value !== undefined && <svg width="9" height="9" viewBox="0 0 9 9"><path d="M3 1 L6 4.5 L3 8" stroke={bDIM} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round" /></svg>}
      </span>
    </div>;


  return (
    <div style={{ height: '100%', background: bBG, color: bINK, fontFamily: bFONT.ui, position: 'relative' }}>
      <ScrollPage>
        <AppHeader />

        <div style={{ padding: '4px 20px 18px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bACCENT, letterSpacing: '0.18em' }}>PARAMÈTRES</div>
          <div style={{ fontFamily: bFONT.title, fontSize: 28, letterSpacing: '-0.03em', marginTop: 4 }}>Réglages</div>
        </div>

        {/* COMPTE */}
        <div style={{ padding: '0 20px 16px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em', marginBottom: 6 }}>COMPTE</div>
          <SectionsRow label="Modifier le profil" value="" />
          <SectionsRow label="Changer le numéro" value="+226 70 12…" />
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '13px 0', borderBottom: `1px solid ${bLINE}` }}>
            <span style={{ fontSize: 13, color: bINK }}>Notifications push</span>
            <Toggle on={true} />
          </div>
          <SectionsRow label="Langue" value="Français" />
        </div>

        {/* COUPON */}
        <div style={{ padding: '0 20px 16px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em', marginBottom: 6 }}>COUPON</div>
          <SectionsRow label="Ligues suivies" value="6 ligues" />
          <SectionsRow label="Niveau de risque" value="Équilibré" />
          <SectionsRow label="Heure de réception" value="09:30" />
          <SectionsRow label="Mode coupon" value="Combiné 3 picks" />
        </div>

        {/* ABO */}
        <div style={{ padding: '0 20px 16px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bDIM, letterSpacing: '0.18em', marginBottom: 6 }}>ABONNEMENT</div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '13px 0', borderBottom: `1px solid ${bLINE}` }}>
            <span style={{ fontSize: 13, color: bINK }}>Statut</span>
            <StatusBadge kind="premium" label="PREMIUM" />
          </div>
          <SectionsRow label="Renouvellement" value="18 juin 2026" />
          <SectionsRow label="Gérer l'abonnement" value="" />
        </div>

        {/* DANGER */}
        <div style={{ padding: '0 20px 20px' }}>
          <div style={{ fontFamily: bFONT.mono, fontSize: 10, color: bLOSS, letterSpacing: '0.18em', marginBottom: 6 }}>ZONE DANGER</div>
          <SectionsRow label="Se déconnecter" danger />
          <SectionsRow label="Supprimer le compte" dim />
        </div>

        {/* Version */}
        <div style={{ textAlign: 'center', padding: '0 20px 24px', fontFamily: bFONT.mono, fontSize: 9, color: bDIM2, letterSpacing: '0.12em' }}>
          COTA v1.0.4 · MAI 2026
        </div>
      </ScrollPage>
      <BottomNav active={3} />
    </div>);

}

Object.assign(window, { ScreenMatchFlash, ScreenHistory, ScreenCouponDetail, ScreenPremium, ScreenBookmakers, ScreenSettings });