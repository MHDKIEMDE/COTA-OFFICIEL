// COTA — match-card primitives in a DAZN-inspired language.
// Adds big-imagery match cards, league chips, live badges, score rows.

const { BG: mBG, BG2: mBG2, BG3: mBG3, LINE: mLINE, LINE2: mLINE2, INK: mINK, INK2: mINK2, DIM: mDIM, DIM2: mDIM2, ACCENT: mACCENT, WIN: mWIN, LOSS: mLOSS, font: mFONT } = window.COTA;

// ── Diagonal "match poster" — coloured split with monogram bleed-through ─────
function MatchBackdrop({ home, away, intensity = 1, children }) {
  const h = window.TEAMS[home];
  const a = window.TEAMS[away];
  return (
    <div style={{ position: 'absolute', inset: 0 }}>
      {/* split */}
      <div style={{
        position: 'absolute', inset: 0,
        background: `linear-gradient(102deg, ${h.color} 0%, ${h.color} 46%, ${a.color} 54%, ${a.color} 100%)`,
        opacity: intensity,
      }} />
      {/* dark overlay for legibility */}
      <div style={{
        position: 'absolute', inset: 0,
        background: 'linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(11,13,16,0.85) 78%, #0b0d10 100%)',
      }} />
      {/* monograms */}
      <div style={{
        position: 'absolute', top: 8, left: 12, fontFamily: mFONT.title,
        fontSize: 132, lineHeight: 0.85, color: h.text, opacity: 0.15, letterSpacing: '-0.06em',
      }}>{h.short}</div>
      <div style={{
        position: 'absolute', top: 8, right: 12, fontFamily: mFONT.title,
        fontSize: 132, lineHeight: 0.85, color: a.text, opacity: 0.15, letterSpacing: '-0.06em',
        textAlign: 'right',
      }}>{a.short}</div>
      {children}
    </div>
  );
}

// ── Big featured match card (DAZN hero) ───────────────────────────────────────
function MatchHeroCard({ match, height = 200, live = false, score, minute }) {
  const h = window.TEAMS[match.home];
  const a = window.TEAMS[match.away];
  return (
    <div style={{
      position: 'relative', height, borderRadius: 14, overflow: 'hidden',
      background: mBG2,
    }}>
      <MatchBackdrop home={match.home} away={match.away}>
        <div style={{ position: 'absolute', top: 14, left: 14, right: 14, display: 'flex', justifyContent: 'space-between' }}>
          <Pill bg={live ? mACCENT : 'rgba(11,13,16,0.6)'} color={live ? mBG : mINK} border={live ? null : mLINE2}>
            {live ? <><span style={{ width: 6, height: 6, background: mBG, borderRadius: 3 }} /> LIVE · {minute}'</> : `${match.competition.toUpperCase()} · ${match.round}`}
          </Pill>
          <Pill bg="rgba(11,13,16,0.6)" color={mDIM} border={mLINE2}>{match.kickoff} · {match.date.toUpperCase()}</Pill>
        </div>
        <div style={{ position: 'absolute', bottom: 14, left: 14, right: 14 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 10 }}>
            <TeamBadge code={h.code} color={h.color} text={h.text} size={36} />
            <div style={{ flex: 1, fontFamily: mFONT.title, fontSize: 24, letterSpacing: '-0.03em', color: mINK }}>
              {h.short} <span style={{ color: mDIM2, fontFamily: mFONT.mono, fontSize: 16, padding: '0 6px' }}>VS</span> {a.short}
            </div>
            <TeamBadge code={a.code} color={a.color} text={a.text} size={36} />
          </div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div style={{ fontFamily: mFONT.mono, fontSize: 11, color: mINK2, letterSpacing: '0.05em' }}>
              {live ? `${score}` : `Score IA: ${match.score}`} · <span style={{ color: mDIM }}>Confiance</span> <span style={{ color: match.confidence >= 80 ? mACCENT : mINK, fontWeight: 700 }}>{match.confidence}%</span>
            </div>
            <OddsChip value={match.pick.odds} prediction={match.pick.type} size="sm" highlight={match.confidence >= 85} />
          </div>
        </div>
      </MatchBackdrop>
    </div>
  );
}

// ── Compact match card (rows) ─────────────────────────────────────────────────
function MatchRowCard({ match, width = 220 }) {
  const h = window.TEAMS[match.home];
  const a = window.TEAMS[match.away];
  const high = match.confidence >= 85;
  return (
    <div style={{ width, flexShrink: 0, borderRadius: 12, overflow: 'hidden', background: mBG2, border: `1px solid ${mLINE}` }}>
      <div style={{ height: 96, position: 'relative' }}>
        <MatchBackdrop home={match.home} away={match.away} intensity={0.95}>
          <div style={{ position: 'absolute', top: 8, left: 10 }}>
            <Pill bg="rgba(11,13,16,0.7)" color={mINK} border={mLINE2}>{match.competition.toUpperCase()}</Pill>
          </div>
          <div style={{ position: 'absolute', bottom: 8, left: 10, right: 10, display: 'flex', alignItems: 'center', gap: 8 }}>
            <TeamBadge code={h.code} color={h.color} text={h.text} size={24} />
            <div style={{ fontFamily: mFONT.title, fontSize: 16, color: mINK, letterSpacing: '-0.02em', flex: 1 }}>{h.short} – {a.short}</div>
            <TeamBadge code={a.code} color={a.color} text={a.text} size={24} />
          </div>
        </MatchBackdrop>
      </div>
      <div style={{ padding: '10px 12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <div style={{ fontFamily: mFONT.mono, fontSize: 10, color: mDIM, letterSpacing: '0.1em' }}>{match.kickoff} · {match.date.split(' ')[0]}</div>
          <div style={{ fontSize: 12, color: mINK, marginTop: 3, fontWeight: 600 }}>{match.pick.type}</div>
        </div>
        <div style={{ textAlign: 'right' }}>
          <div style={{ fontFamily: mFONT.mono, fontSize: 14, color: high ? mACCENT : mINK, fontWeight: 700 }}>@{match.pick.odds}</div>
          <div style={{ fontFamily: mFONT.mono, fontSize: 9, color: mDIM, letterSpacing: '0.1em', marginTop: 2 }}>{match.confidence}%</div>
        </div>
      </div>
    </div>
  );
}

// ── Live indicator (animated dot) ─────────────────────────────────────────────
function LiveBadge({ minute }) {
  return (
    <div style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontFamily: mFONT.mono, fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: mACCENT }}>
      <span style={{
        width: 7, height: 7, borderRadius: 4, background: mACCENT,
        animation: 'cota-live-pulse 1.4s ease-in-out infinite',
      }} />
      LIVE · {minute}'
    </div>
  );
}

// ── Horizontal scroll row ─────────────────────────────────────────────────────
function ContentRow({ title, more, children }) {
  return (
    <div style={{ marginBottom: 22 }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', padding: '0 20px', marginBottom: 12 }}>
        <div style={{ fontFamily: mFONT.title, fontSize: 16, letterSpacing: '-0.02em', color: mINK }}>{title}</div>
        {more && <div style={{ fontFamily: mFONT.mono, fontSize: 10, color: mDIM, letterSpacing: '0.12em' }}>{more}</div>}
      </div>
      <div style={{ display: 'flex', gap: 10, padding: '0 20px', overflowX: 'auto' }}>
        {children}
      </div>
    </div>
  );
}

// ── Stat block ────────────────────────────────────────────────────────────────
function StatBlock({ value, label, accent }) {
  return (
    <div style={{ flex: 1, padding: '14px 12px', background: mBG2, borderRadius: 10, border: `1px solid ${mLINE}` }}>
      <div style={{ fontFamily: mFONT.title, fontSize: 28, letterSpacing: '-0.03em', color: accent ? mACCENT : mINK, lineHeight: 1 }}>{value}</div>
      <div style={{ fontFamily: mFONT.mono, fontSize: 9, color: mDIM, letterSpacing: '0.12em', marginTop: 5 }}>{label}</div>
    </div>
  );
}

Object.assign(window, { MatchBackdrop, MatchHeroCard, MatchRowCard, LiveBadge, ContentRow, StatBlock });
