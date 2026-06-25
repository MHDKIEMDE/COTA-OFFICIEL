// COTA V6 — DAZN-ified screens.

const T = window.TEAMS;
const M = window.MATCHES;

// ─────────────────────────────────────────────────────────────────────────────
// 1. HOME — poster-led, sober.
// ─────────────────────────────────────────────────────────────────────────────
function V6Home() {
  const live  = M[2]; // RMA-BAY
  const today = [M[0], M[3], M[4], M[1]];
  return (
    <div style={{ height: '100%', background: V6.BG, color: V6.INK, fontFamily: V6.font.ui, position: 'relative' }}>
      <div style={{ height: '100%', overflowY: 'auto', paddingTop: 50, paddingBottom: 90 }}>
        <V6AppHeader right={
          <button aria-label="notifs" style={{ width: 34, height: 34, borderRadius: 17, background: V6.BG2, border: `1px solid ${V6.LINE}`, color: V6.INK }}>
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style={{ margin: 'auto', display: 'block' }}>
              <path d="M3 6 a5 5 0 0 1 10 0 v3 l1.5 2 H1.5 L3 9 Z" stroke={V6.INK} strokeWidth="1.4"/>
            </svg>
          </button>
        } />

        {/* Day pills — sober, no all caps */}
        <div style={{ display: 'flex', gap: 18, padding: '0 20px 14px', borderBottom: `1px solid ${V6.LINE}` }}>
          {['Aujourd\'hui', 'Demain', 'Semaine'].map((t, i) => (
            <span key={t} style={{ fontSize: 14, color: i === 0 ? V6.INK : V6.DIM, fontWeight: i === 0 ? 600 : 500, paddingBottom: 6, borderBottom: i === 0 ? `2px solid ${V6.ACCENT}` : '2px solid transparent' }}>{t}</span>
          ))}
        </div>

        {/* Live featured — the only place with pulse */}
        <div style={{ padding: '20px 20px 8px' }}>
          <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 10 }}>
            <h2 style={{ fontFamily: V6.font.title, fontSize: 20, letterSpacing: '-0.03em', margin: 0 }}>En direct</h2>
            <V6LiveDot />
          </div>
          <V6Poster home={live.home} away={live.away} height={220}>
            <div style={{ position: 'absolute', top: 16, left: 16, right: 16, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: 11, color: V6.INK, fontWeight: 500, background: 'rgba(11,13,16,0.55)', padding: '4px 9px', borderRadius: 4 }}>{live.competition}</span>
              <span style={{ fontFamily: V6.font.mono, fontSize: 11, color: V6.ACCENT, fontWeight: 700 }}>34'</span>
            </div>
            <div style={{ position: 'absolute', bottom: 16, left: 16, right: 16 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 8 }}>
                <TeamBadge code={T[live.home].code} color={T[live.home].color} text={T[live.home].text} size={36} />
                <div style={{ flex: 1, fontFamily: V6.font.title, fontSize: 22, letterSpacing: '-0.02em' }}>
                  {T[live.home].short} <span style={{ fontFamily: V6.font.mono, color: V6.ACCENT, margin: '0 8px' }}>1</span>—<span style={{ fontFamily: V6.font.mono, color: V6.INK, margin: '0 8px' }}>0</span> {T[live.away].short}
                </div>
                <TeamBadge code={T[live.away].code} color={T[live.away].color} text={T[live.away].text} size={36} />
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: 12, color: V6.INK2 }}>{live.pick.type}</span>
                <V6OddsChip value={live.pick.odds} />
              </div>
            </div>
          </V6Poster>
        </div>

        {/* Today list — full-width rows, posters small */}
        <div style={{ padding: '18px 20px 8px' }}>
          <h2 style={{ fontFamily: V6.font.title, fontSize: 20, letterSpacing: '-0.03em', margin: '0 0 12px' }}>Matchs du jour</h2>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {today.map(m => (
              <V6MatchRow key={m.id} match={m} />
            ))}
          </div>
        </div>

        {/* Coupon — sober card at the bottom */}
        <div style={{ padding: '18px 20px 8px' }}>
          <div style={{
            padding: '20px', background: V6.BG2, border: `1px solid ${V6.LINE}`,
            borderRadius: 14, display: 'flex', alignItems: 'center', gap: 16,
          }}>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 12, color: V6.DIM, marginBottom: 4 }}>Coupon du jour · 09:30</div>
              <div style={{ fontFamily: V6.font.title, fontSize: 22, letterSpacing: '-0.02em' }}>3 picks combinés</div>
              <div style={{ marginTop: 10 }}><V6Confidence value={87} /></div>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{ fontFamily: V6.font.mono, fontSize: 28, fontWeight: 700, color: V6.ACCENT, letterSpacing: '-0.03em', lineHeight: 1 }}>@4.55</div>
              <button style={{ marginTop: 12, padding: '8px 14px', background: V6.ACCENT, color: V6.BG, border: 'none', borderRadius: 8, fontFamily: V6.font.ui, fontSize: 12, fontWeight: 600 }}>Ouvrir</button>
            </div>
          </div>
        </div>
      </div>
      <V6BottomNav active={0} />
    </div>
  );
}

// Single row card for a match — much sober than the V2 row.
function V6MatchRow({ match }) {
  const h = T[match.home], a = T[match.away];
  return (
    <div style={{ display: 'grid', gridTemplateColumns: '56px 1fr auto', gap: 14, padding: '12px 14px', background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 10, alignItems: 'center' }}>
      <div style={{ position: 'relative', width: 56, height: 56, borderRadius: 8, overflow: 'hidden' }}>
        <V6Poster home={match.home} away={match.away} height={56} intensity={1} dim={true} />
      </div>
      <div>
        <div style={{ fontFamily: V6.font.title, fontSize: 14, letterSpacing: '-0.02em' }}>{h.short} – {a.short}</div>
        <div style={{ fontSize: 11, color: V6.DIM, marginTop: 3 }}>{match.competition} · <span style={{ fontFamily: V6.font.mono }}>{match.kickoff}</span></div>
        <div style={{ fontSize: 12, color: V6.INK2, marginTop: 6 }}>{match.pick.type}</div>
      </div>
      <div style={{ textAlign: 'right' }}>
        <V6OddsChip value={match.pick.odds} />
        <div style={{ fontSize: 10, color: V6.DIM, marginTop: 6 }}>Confiance {match.confidence}%</div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. MATCH DETAIL — narrative, prose, no SQL grid.
// ─────────────────────────────────────────────────────────────────────────────
function V6Match() {
  const m = M[0]; // PSG-OM
  const h = T[m.home], a = T[m.away];
  return (
    <div style={{ height: '100%', background: V6.BG, color: V6.INK, fontFamily: V6.font.ui, position: 'relative' }}>
      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 90 }}>
        {/* Hero poster — full bleed, dominant */}
        <div style={{ position: 'relative', height: 320 }}>
          <V6Poster home={m.home} away={m.away} height={320}>
            <div style={{ position: 'absolute', top: 60, left: 16, right: 16, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.5)', border: `1px solid ${V6.LINE2}`, color: V6.INK }}>
                <svg width="13" height="13" viewBox="0 0 13 13" style={{ margin: 'auto', display: 'block' }}><path d="M8 2 L4 6.5 L8 11" stroke={V6.INK} strokeWidth="1.5" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </button>
              <span style={{ fontSize: 12, color: V6.INK, background: 'rgba(11,13,16,0.55)', padding: '6px 12px', borderRadius: 4 }}>{m.competition} · {m.round}</span>
              <button style={{ width: 36, height: 36, borderRadius: 18, background: 'rgba(11,13,16,0.5)', border: `1px solid ${V6.LINE2}`, color: V6.INK }}>
                <svg width="13" height="13" viewBox="0 0 12 12" style={{ margin: 'auto', display: 'block' }}><circle cx="3" cy="3" r="1.4" fill={V6.INK}/><circle cx="9" cy="3" r="1.4" fill={V6.INK}/><circle cx="9" cy="9" r="1.4" fill={V6.INK}/></svg>
              </button>
            </div>
            <div style={{ position: 'absolute', bottom: 30, left: 16, right: 16, textAlign: 'center' }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-around' }}>
                <div>
                  <TeamBadge code={h.code} color={h.color} text={h.text} size={62} />
                  <div style={{ fontFamily: V6.font.title, fontSize: 18, marginTop: 10, letterSpacing: '-0.02em' }}>{h.name}</div>
                </div>
                <div>
                  <div style={{ fontFamily: V6.font.mono, fontSize: 13, color: V6.DIM }}>{m.kickoff}</div>
                  <div style={{ fontFamily: V6.font.title, fontSize: 22, color: V6.DIM2, marginTop: 4 }}>—</div>
                </div>
                <div>
                  <TeamBadge code={a.code} color={a.color} text={a.text} size={62} />
                  <div style={{ fontFamily: V6.font.title, fontSize: 18, marginTop: 10, letterSpacing: '-0.02em' }}>{a.name}</div>
                </div>
              </div>
            </div>
          </V6Poster>
        </div>

        {/* Tabs — sober */}
        <div style={{ display: 'flex', gap: 22, padding: '14px 20px', borderBottom: `1px solid ${V6.LINE}` }}>
          {['Analyse', 'Statistiques', 'H2H', 'Cotes'].map((t, i) => (
            <span key={t} style={{ fontSize: 14, fontWeight: i === 0 ? 600 : 500, color: i === 0 ? V6.INK : V6.DIM, paddingBottom: 6, borderBottom: i === 0 ? `2px solid ${V6.ACCENT}` : '2px solid transparent' }}>{t}</span>
          ))}
        </div>

        {/* Pick prose — DAZN style, narrative */}
        <div style={{ padding: '24px 20px 16px' }}>
          <div style={{ fontSize: 13, color: V6.DIM, marginBottom: 6 }}>La sélection</div>
          <div style={{ fontFamily: V6.font.title, fontSize: 28, letterSpacing: '-0.02em', lineHeight: 1.1 }}>{m.pick.type}.</div>
          <div style={{ marginTop: 14, display: 'flex', alignItems: 'center', gap: 14 }}>
            <V6OddsChip value={m.pick.odds} prominent />
            <div style={{ flex: 1 }}><V6Confidence value={m.confidence} /></div>
          </div>
        </div>

        {/* Narrative criteria — prose, not grid */}
        <div style={{ padding: '6px 20px 20px' }}>
          <h3 style={{ fontFamily: V6.font.title, fontSize: 17, letterSpacing: '-0.02em', margin: '14px 0 12px' }}>Pourquoi le PSG</h3>
          <p style={{ fontSize: 14, color: V6.INK2, lineHeight: 1.6, margin: '0 0 16px' }}>
            Le club parisien reste invaincu sur ses cinq dernières sorties, contre deux défaites pour l'OM. À domicile, son taux de victoire atteint 89% cette saison.
          </p>
          <p style={{ fontSize: 14, color: V6.INK2, lineHeight: 1.6, margin: '0 0 16px' }}>
            Marseille est privé d'Aubameyang et Veretout — deux titulaires sur trois en attaque. Sur les dix dernières confrontations, le PSG mène 6 à 2.
          </p>
          <p style={{ fontSize: 14, color: V6.INK2, lineHeight: 1.6, margin: 0 }}>
            Le modèle xG donne 2.8 buts attendus côté parisien, contre 1.1 pour les visiteurs. La possession devrait avoisiner les 64%.
          </p>

          {/* Compact criteria list */}
          <ul style={{ listStyle: 'none', padding: 0, margin: '20px 0 0' }}>
            {[
              ['Forme PSG · OM',         '4V 1N · 3V 2D'],
              ['Confrontations 10 ans',  '6–2–2 pour le PSG'],
              ['Domicile saison',        '89% de victoires'],
              ['Blessures clés',         '0 vs 2'],
              ['Possession attendue',    '64%'],
              ['xG (buts attendus)',     '2.8 — 1.1'],
            ].map(([label, value]) => (
              <li key={label} style={{ display: 'flex', justifyContent: 'space-between', padding: '11px 0', borderTop: `1px solid ${V6.LINE}` }}>
                <span style={{ fontSize: 13, color: V6.INK2 }}>{label}</span>
                <span style={{ fontSize: 13, color: V6.INK, fontWeight: 500 }}>{value}</span>
              </li>
            ))}
          </ul>
        </div>

        <div style={{ padding: '0 20px 24px' }}>
          <button style={{ width: '100%', height: 52, background: V6.ACCENT, color: V6.BG, border: 'none', borderRadius: 12, fontFamily: V6.font.ui, fontSize: 14, fontWeight: 600 }}>
            Ajouter au coupon
          </button>
        </div>
      </div>
      <V6BottomNav active={0} />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. COUPON — sober, picks as list.
// ─────────────────────────────────────────────────────────────────────────────
function V6Coupon() {
  const C = window.COUPON;
  const picks = C.picks || C['sélections'] || [];
  return (
    <div style={{ height: '100%', background: V6.BG, color: V6.INK, fontFamily: V6.font.ui, position: 'relative' }}>
      <div style={{ height: '100%', overflowY: 'auto', paddingTop: 50, paddingBottom: 90 }}>
        <V6AppHeader />

        <div style={{ padding: '8px 20px 6px' }}>
          <div style={{ fontSize: 13, color: V6.DIM, marginBottom: 6 }}>Coupon du {C.date}</div>
          <h1 style={{ fontFamily: V6.font.title, fontSize: 32, letterSpacing: '-0.03em', margin: 0 }}>3 picks combinés.</h1>
        </div>

        {/* Hero numbers — calm */}
        <div style={{ padding: '20px 20px 0' }}>
          <div style={{ display: 'flex', gap: 12 }}>
            <div style={{ flex: 1, padding: '18px 16px', background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 12 }}>
              <div style={{ fontSize: 12, color: V6.DIM, marginBottom: 8 }}>Cote combinée</div>
              <div style={{ fontFamily: V6.font.mono, fontSize: 30, color: V6.ACCENT, fontWeight: 700, letterSpacing: '-0.03em', lineHeight: 1 }}>@{C.total.toFixed(2)}</div>
            </div>
            <div style={{ flex: 1, padding: '18px 16px', background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 12 }}>
              <div style={{ fontSize: 12, color: V6.DIM, marginBottom: 8 }}>Confiance</div>
              <V6Confidence value={C.confidence} label={null} />
            </div>
          </div>
        </div>

        {/* Picks list */}
        <div style={{ padding: '24px 20px 8px' }}>
          <h2 style={{ fontFamily: V6.font.title, fontSize: 17, letterSpacing: '-0.02em', margin: '0 0 12px' }}>Les sélections</h2>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {picks.map((p, i) => {
              const match = M.find(x => x.id === p.matchId);
              const h = T[match.home], a = T[match.away];
              return (
                <div key={p.matchId} style={{ background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 10, overflow: 'hidden' }}>
                  <div style={{ height: 70, position: 'relative' }}>
                    <V6Poster home={match.home} away={match.away} height={70} dim={true}>
                      <div style={{ position: 'absolute', inset: 0, padding: '0 14px', display: 'flex', alignItems: 'center', gap: 10 }}>
                        <TeamBadge code={h.code} color={h.color} text={h.text} size={26} />
                        <span style={{ fontFamily: V6.font.title, fontSize: 14, letterSpacing: '-0.02em' }}>{h.short} – {a.short}</span>
                        <TeamBadge code={a.code} color={a.color} text={a.text} size={26} />
                        <span style={{ marginLeft: 'auto', fontSize: 11, color: V6.DIM2, fontFamily: V6.font.mono }}>{match.kickoff}</span>
                      </div>
                    </V6Poster>
                  </div>
                  <div style={{ padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12 }}>
                    <span style={{ fontSize: 13, color: V6.INK, flex: 1 }}>{p.type}</span>
                    <V6OddsChip value={p.odds} prominent={p.confidence >= 85} />
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Stake / gain — calm */}
        <div style={{ padding: '20px 20px 0' }}>
          <div style={{ padding: '16px 18px', background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 12, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div>
              <div style={{ fontSize: 12, color: V6.DIM }}>Mise · {C.stake}€</div>
              <div style={{ fontFamily: V6.font.title, fontSize: 22, letterSpacing: '-0.02em', marginTop: 4 }}>Gain possible</div>
            </div>
            <div style={{ fontFamily: V6.font.mono, fontSize: 26, color: V6.ACCENT, fontWeight: 700 }}>{(C.stake * C.total).toFixed(2)}€</div>
          </div>
        </div>

        <div style={{ padding: '20px 20px 8px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          <button style={{ width: '100%', height: 52, background: V6.ACCENT, color: V6.BG, border: 'none', borderRadius: 12, fontFamily: V6.font.ui, fontSize: 14, fontWeight: 600 }}>
            Jouer ce coupon
          </button>
          <button style={{ width: '100%', height: 44, background: 'transparent', color: V6.INK, border: `1px solid ${V6.LINE2}`, borderRadius: 12, fontFamily: V6.font.ui, fontSize: 13, fontWeight: 500 }}>
            Partager
          </button>
        </div>
      </div>
      <V6BottomNav active={1} />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. PROFILE — calm stats, no orbs, no rings everywhere.
// ─────────────────────────────────────────────────────────────────────────────
function V6Profile() {
  return (
    <div style={{ height: '100%', background: V6.BG, color: V6.INK, fontFamily: V6.font.ui, position: 'relative' }}>
      <div style={{ height: '100%', overflowY: 'auto', paddingTop: 50, paddingBottom: 90 }}>
        <V6AppHeader />

        <div style={{ padding: '10px 20px 20px', display: 'flex', alignItems: 'center', gap: 14 }}>
          <div style={{ width: 60, height: 60, borderRadius: 30, background: V6.BG2, border: `1px solid ${V6.LINE2}`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: V6.font.title, fontSize: 22, color: V6.INK }}>K</div>
          <div>
            <div style={{ fontFamily: V6.font.title, fontSize: 22, letterSpacing: '-0.02em' }}>Karim Bouchareb</div>
            <div style={{ fontSize: 12, color: V6.DIM, marginTop: 2 }}>Membre depuis novembre 2025</div>
          </div>
        </div>

        {/* Stats — calm row, no rings, no big mono */}
        <div style={{ padding: '0 20px 20px', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', borderTop: `1px solid ${V6.LINE}`, borderBottom: `1px solid ${V6.LINE}` }}>
          {[
            ['+18,5%', 'ROI saison'],
            ['47/59',  'Picks gagnants'],
            ['4',      'Série en cours'],
          ].map(([n, l], i) => (
            <div key={l} style={{ textAlign: 'center', padding: '16px 0', borderRight: i < 2 ? `1px solid ${V6.LINE}` : 'none' }}>
              <div style={{ fontFamily: V6.font.title, fontSize: 22, letterSpacing: '-0.02em' }}>{n}</div>
              <div style={{ fontSize: 11, color: V6.DIM, marginTop: 4 }}>{l}</div>
            </div>
          ))}
        </div>

        {/* Performance — sparkline, no mono labels */}
        <div style={{ padding: '20px 20px 0' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 10 }}>
            <h2 style={{ fontFamily: V6.font.title, fontSize: 17, letterSpacing: '-0.02em', margin: 0 }}>Performance · 30 jours</h2>
            <span style={{ fontSize: 13, color: V6.WIN, fontWeight: 600 }}>+184€</span>
          </div>
          <div style={{ background: V6.BG2, border: `1px solid ${V6.LINE}`, borderRadius: 12, padding: 16 }}>
            <svg viewBox="0 0 300 70" width="100%" height="70" style={{ display: 'block' }}>
              <defs>
                <linearGradient id="v6-spark" x1="0" x2="0" y1="0" y2="1">
                  <stop offset="0%" stopColor={V6.ACCENT} stopOpacity="0.35" />
                  <stop offset="100%" stopColor={V6.ACCENT} stopOpacity="0" />
                </linearGradient>
              </defs>
              <path d="M0 55 L20 50 L40 53 L60 45 L80 47 L100 40 L120 43 L140 32 L160 36 L180 24 L200 28 L220 18 L240 22 L260 12 L280 16 L300 6 L300 70 L0 70 Z" fill="url(#v6-spark)" />
              <path d="M0 55 L20 50 L40 53 L60 45 L80 47 L100 40 L120 43 L140 32 L160 36 L180 24 L200 28 L220 18 L240 22 L260 12 L280 16 L300 6" stroke={V6.ACCENT} strokeWidth="1.6" fill="none" />
            </svg>
          </div>
        </div>

        {/* Settings — calm rows */}
        <div style={{ padding: '24px 20px 8px' }}>
          <h2 style={{ fontFamily: V6.font.title, fontSize: 17, letterSpacing: '-0.02em', margin: '0 0 12px' }}>Réglages</h2>
          {[
            ['Notifications',    '9h30 chaque matin'],
            ['Ligues suivies',   '6 ligues'],
            ['Bookmaker',        '1xBet'],
            ['Mode coupon',      '3 picks combinés'],
            ['Compte',           'Premium'],
          ].map(([k, v]) => (
            <div key={k} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '15px 0', borderTop: `1px solid ${V6.LINE}` }}>
              <span style={{ fontSize: 14, color: V6.INK }}>{k}</span>
              <span style={{ fontSize: 13, color: V6.DIM, display: 'flex', alignItems: 'center', gap: 8 }}>
                {v}
                <svg width="9" height="9" viewBox="0 0 9 9"><path d="M3 1 L6 4.5 L3 8" stroke={V6.DIM} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
              </span>
            </div>
          ))}
        </div>
      </div>
      <V6BottomNav active={3} />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. ONBOARDING HERO — calmer, no ticker, no "01 — XXX".
// ─────────────────────────────────────────────────────────────────────────────
function V6OnboardHero() {
  const psg = T.PSG, om = T.OM;
  return (
    <div style={{ height: '100%', background: V6.BG, color: V6.INK, fontFamily: V6.font.ui, position: 'relative', overflow: 'hidden' }}>
      {/* Full bleed poster — calmer, no ticker */}
      <div style={{ position: 'absolute', inset: 0 }}>
        <div style={{ position: 'absolute', inset: 0, background: `linear-gradient(108deg, ${psg.color} 0%, ${psg.color} 45%, ${om.color} 55%, ${om.color} 100%)` }} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, rgba(0,0,0,0.10) 0%, rgba(11,13,16,0.65) 50%, #0b0d10 92%)' }} />
        <div style={{ position: 'absolute', top: 100, left: 0, right: 0, display: 'flex', justifyContent: 'space-between', padding: '0 8px', fontFamily: V6.font.title, fontSize: 200, lineHeight: 0.85, opacity: 0.10, letterSpacing: '-0.06em' }}>
          <span>PSG</span>
          <span style={{ textAlign: 'right' }}>OM</span>
        </div>
      </div>

      {/* Skip */}
      <div style={{ position: 'absolute', top: 60, left: 20, right: 20, display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 5 }}>
        <span style={{ fontSize: 11, color: V6.INK, background: 'rgba(11,13,16,0.55)', padding: '5px 10px', borderRadius: 4 }}>Ligue 1 · ce soir 21h</span>
        <button style={{ background: 'transparent', border: 'none', color: V6.INK, fontSize: 12, fontWeight: 500 }}>Passer</button>
      </div>

      {/* Bottom block */}
      <div style={{ position: 'absolute', bottom: 0, left: 0, right: 0, padding: '0 24px 50px', display: 'flex', flexDirection: 'column', gap: 22 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <AppIcon size={48} />
          <Wordmark size={36} underline={true} />
        </div>

        <h1 style={{ fontFamily: V6.font.title, fontSize: 38, letterSpacing: '-0.03em', lineHeight: 1.02, margin: 0 }}>
          Le foot, lu par une <span style={{ background: V6.ACCENT, color: V6.BG, padding: '0 10px' }}>IA</span>.
        </h1>

        <p style={{ fontSize: 15, color: V6.INK2, lineHeight: 1.5, margin: 0 }}>
          Neuf critères, un score de confiance, un coupon par jour. Sans baratin.
        </p>

        <button style={{ width: '100%', height: 54, background: V6.ACCENT, color: V6.BG, border: 'none', borderRadius: 12, fontFamily: V6.font.ui, fontSize: 14, fontWeight: 600 }}>
          Commencer
        </button>

        <div style={{ display: 'flex', justifyContent: 'center', gap: 6 }}>
          <span style={{ width: 22, height: 3, background: V6.ACCENT, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: V6.LINE2, borderRadius: 2 }} />
          <span style={{ width: 8, height: 3, background: V6.LINE2, borderRadius: 2 }} />
        </div>
      </div>
    </div>
  );
}

Object.assign(window, { V6Home, V6Match, V6Coupon, V6Profile, V6OnboardHero });
