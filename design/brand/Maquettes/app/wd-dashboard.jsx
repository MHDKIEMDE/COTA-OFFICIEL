// COTA Web Dashboard — espace membre (structure MaxLand, ADN COTA sombre + citron).
// Sidebar · barre de filtres · cartes KPI · sections · tableau historique · colonne droite.

const { BG: dBG, BG2: dBG2, BG3: dBG3, LINE: dLINE, LINE2: dLINE2, INK: dINK, INK2: dINK2, DIM: dDIM, DIM2: dDIM2, ACCENT: dACCENT, ACCENT_DIM: dACCENT_DIM, COOL: dCOOL, COOL_DIM: dCOOL_DIM, WIN: dWIN, LOSS: dLOSS, font: dFONT } = window.COTA;

// Palette sobre : citron (accent), bleu froid (secondaire discret), vert/rouge réservés aux résultats.
const WD_TONES = { lime: dACCENT, cool: dCOOL, mint: dWIN, blue: dCOOL, amber: dCOOL, red: dLOSS };

// ── Glyphes ───────────────────────────────────────────────────────────────────
function WdIcon({ name, size = 18, color = 'currentColor', sw = 1.7 }) {
  const p = { fill: 'none', stroke: color, strokeWidth: sw, strokeLinecap: 'round', strokeLinejoin: 'round' };
  const M = {
    grid:    <><rect x="3" y="3" width="7" height="7" rx="1.5" {...p}/><rect x="14" y="3" width="7" height="7" rx="1.5" {...p}/><rect x="3" y="14" width="7" height="7" rx="1.5" {...p}/><rect x="14" y="14" width="7" height="7" rx="1.5" {...p}/></>,
    coupon:  <><rect x="3" y="5" width="18" height="14" rx="2" {...p}/><path d="M3 10h18" {...p}/><path d="M7 14h7" {...p}/></>,
    ball:    <><circle cx="12" cy="12" r="9" {...p}/><path d="M12 7l4 3-1.5 5h-5L8 10z" {...p}/></>,
    chart:   <><path d="M4 20V10M10 20V4M16 20v-7M22 20H2" {...p}/></>,
    clock:   <><circle cx="12" cy="12" r="9" {...p}/><path d="M12 7v5l3.5 2" {...p}/></>,
    heart:   <><path d="M12 20s-7-4.6-9.2-9C1.3 8 3 4.5 6.4 4.5c2 0 3.2 1.2 3.6 2 .4-.8 1.6-2 3.6-2 3.4 0 5.1 3.5 3.6 6.5C19 15.4 12 20 12 20Z" {...p}/></>,
    crown:   <><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8Z" {...p}/></>,
    logout:  <><path d="M14 4h-7v16h7M10 12h10m0 0-3-3m3 3-3 3" {...p}/></>,
    trend:   <><path d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5" {...p}/></>,
    target:  <><circle cx="12" cy="12" r="8" {...p}/><circle cx="12" cy="12" r="3.6" {...p}/><circle cx="12" cy="12" r="0.6" fill={color} stroke="none"/></>,
    wallet:  <><rect x="3" y="6" width="18" height="13" rx="2.5" {...p}/><path d="M3 9h18" {...p}/><circle cx="17" cy="13.5" r="1.3" fill={color} stroke="none"/></>,
    star:    <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L4.5 9.7l5.9-.9L12 3.5Z" fill={color} stroke="none"/>,
    search:  <><circle cx="11" cy="11" r="7" {...p}/><path d="m20 20-3.2-3.2" {...p}/></>,
    bell:    <><path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" {...p}/><path d="M10 19a2 2 0 0 0 4 0" {...p}/></>,
    chevron: <path d="m6 9 6 6 6-6" {...p}/>,
    chevronR:<path d="m9 6 6 6-6 6" {...p}/>,
    bolt:    <path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z" {...p}/>,
    trophy:  <><path d="M6 4h12v4a6 6 0 0 1-12 0V4Z" {...p}/><path d="M6 6H3v2a3 3 0 0 0 3 3M18 6h3v2a3 3 0 0 1-3 3M9 20h6M12 14v6" {...p}/></>,
    gift:    <><rect x="3" y="8" width="18" height="4" rx="1" {...p}/><path d="M5 12v8h14v-8M12 8v12" {...p}/><path d="M12 8S10.5 3 8 4.5 9.5 8 12 8Zm0 0s1.5-5 4-3.5S14.5 8 12 8Z" {...p}/></>,
    user:    <><circle cx="12" cy="8" r="4" {...p}/><path d="M4 21a8 8 0 0 1 16 0" {...p}/></>,
    play:    <><circle cx="12" cy="12" r="9" {...p}/><path d="M10 8.5l6 3.5-6 3.5z" fill={color} stroke="none"/></>,
    check:   <path d="M5 12.5l4.5 4.5L19 7" {...p}/>,
    flame:   <path d="M12 3s5 4 5 9a5 5 0 0 1-10 0c0-1.5.7-2.8 1.5-3.6C8.5 9 9 11 9 11s.5-5 3-8Z" {...p}/>,
    arrowUp: <path d="M12 19V5m0 0-6 6m6-6 6 6" {...p}/>,
    filter:  <path d="M3 5h18l-7 8v6l-4-2v-4z" {...p}/>,
  };
  return <svg width={size} height={size} viewBox="0 0 24 24">{M[name]}</svg>;
}

// ── Avatar à initiales ────────────────────────────────────────────────────────
function Avatar({ initials, tone = 'lime', size = 40 }) {
  const col = WD_TONES[tone] || dACCENT;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%', flexShrink: 0,
      background: col, color: dBG, display: 'flex', alignItems: 'center', justifyContent: 'center',
      fontFamily: dFONT.title, fontSize: size * 0.36, letterSpacing: '-0.02em',
    }}>{initials}</div>
  );
}

// ── Sidebar (routeur) ───────────────────────────────────────────────────────
const WD_NAV = [
  { id: 'dash',    label: 'Dashboard',    icon: 'grid' },
  { id: 'predict', label: 'Prédictions',  icon: 'coupon' },
  { id: 'live',    label: 'Matchs live',  icon: 'play' },
  { id: 'compet',  label: 'Compétitions', icon: 'trophy' },
  { id: 'fav',     label: 'Favoris',      icon: 'heart' },
  { id: 'hist',    label: 'Historique',   icon: 'clock' },
  { id: 'stats',   label: 'Statistiques', icon: 'chart' },
];
const WD_NAV2 = [
  { id: 'abo',     label: 'Abonnement',   icon: 'crown' },
  { id: 'parrain', label: 'Parrainage',   icon: 'gift' },
  { id: 'profil',  label: 'Profil',       icon: 'user' },
];

function Sidebar({ user, active, onNav }) {
  const Item = (it) => {
    const on = it.id === active;
    return (
      <a key={it.id} className={'wd-navitem' + (on ? ' on' : '')} onClick={() => onNav(it.id)}>
        <WdIcon name={it.icon} size={18} color={on ? dACCENT : dDIM} />
        <span>{it.label}</span>
      </a>
    );
  };
  return (
    <aside className="wd-sidebar">
      <div className="wd-brand" onClick={() => onNav('dash')} style={{ cursor: 'pointer' }}>
        <AppIcon size={30} />
        <div>
          <Wordmark size={19} underline={false} />
          <div style={{ fontFamily: dFONT.mono, fontSize: 8.5, color: dDIM, letterSpacing: '0.22em', marginTop: 3 }}>ESPACE MEMBRE</div>
        </div>
      </div>

      <div className="wd-profile" onClick={() => onNav('profil')} style={{ cursor: 'pointer' }}>
        <div style={{ position: 'relative' }}>
          <Avatar initials={user.initials} tone="lime" size={72} />
          <span style={{ position: 'absolute', bottom: 2, right: 2, width: 16, height: 16, borderRadius: 8, background: dWIN, border: `3px solid ${dBG2}` }} />
        </div>
        <div style={{ fontFamily: dFONT.title, fontSize: 18, color: dACCENT, letterSpacing: '-0.02em', marginTop: 12 }}>{user.name}</div>
        <div style={{ fontSize: 12, color: dINK2, marginTop: 3 }}>{user.city}</div>
        <div style={{ marginTop: 12 }}>
          <Pill bg={dACCENT_DIM} color={dACCENT} border={null}><WdIcon name="bolt" size={11} color={dACCENT}/> {user.plan}</Pill>
        </div>
      </div>

      <nav className="wd-nav">
        {WD_NAV.map(Item)}
        <div className="wd-navsep" />
        {WD_NAV2.map(Item)}
      </nav>

      <a className="wd-navitem wd-logout" onClick={() => onNav('dash')}>
        <WdIcon name="logout" size={18} color={dDIM} />
        <span>Déconnexion</span>
      </a>
    </aside>
  );
}

// ── Barre du haut + filtres (hero MaxLand) ─────────────────────────────────────
function Topbar({ user }) {
  return (
    <div className="wd-topbar">
      <div>
        <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: dDIM, letterSpacing: '0.2em' }}>MAR. 2 JUIN 2026</div>
        <h1 style={{ fontFamily: dFONT.title, fontSize: 30, letterSpacing: '-0.03em', margin: '6px 0 0', color: dINK }}>
          Bon retour, {user.name.split(' ')[0]}.
        </h1>
      </div>
      <div className="wd-topactions">
        <button className="wd-iconbtn" aria-label="Notifications">
          <WdIcon name="bell" size={17} color={dINK} />
          <span className="wd-dot" />
        </button>
        <button className="wd-cta"><WdIcon name="bolt" size={14} color={dBG}/> Carnet du jour</button>
      </div>
    </div>
  );
}

function FilterBar() {
  const Field = ({ label, value, grow }) => (
    <button className="wd-filter" style={grow ? { flex: 1.4 } : null}>
      <span style={{ fontFamily: dFONT.mono, fontSize: 8.5, color: dDIM, letterSpacing: '0.15em' }}>{label}</span>
      <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 4 }}>
        <span style={{ fontSize: 13, color: dINK, fontWeight: 500 }}>{value}</span>
        <WdIcon name="chevron" size={14} color={dDIM} />
      </span>
    </button>
  );
  return (
    <div className="wd-filterbar">
      <div className="wd-search">
        <WdIcon name="search" size={16} color={dDIM} />
        <input placeholder="Rechercher un match, une équipe…" />
      </div>
      <Field label="COMPÉTITION" value="Toutes" />
      <Field label="DATE" value="Cette semaine" />
      <Field label="CONFIANCE MIN." value="70 %+" />
      <button className="wd-filterbtn"><WdIcon name="bolt" size={13} color={dBG}/> Filtrer</button>
    </div>
  );
}

// ── En-tête de page interne (réutilisable) ─────────────────────────────────────
function PageHeader({ kicker, title, desc, right }) {
  return (
    <div className="wd-topbar">
      <div style={{ maxWidth: 640 }}>
        {kicker && <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: dDIM, letterSpacing: '0.2em' }}>{kicker}</div>}
        <h1 style={{ fontFamily: dFONT.title, fontSize: 30, letterSpacing: '-0.03em', margin: '6px 0 0', color: dINK }}>{title}</h1>
        {desc && <p style={{ fontSize: 13.5, color: dINK2, lineHeight: 1.5, margin: '10px 0 0', textWrap: 'pretty' }}>{desc}</p>}
      </div>
      {right && <div className="wd-topactions">{right}</div>}
    </div>
  );
}

// ── Cartes KPI (sobres : tuile neutre, icône citron) ────────────────────────────
function KpiCard({ value, label, delta, up, tone, icon }) {
  const iconCol = tone === 'cool' ? dCOOL : dACCENT;
  return (
    <div className="wd-kpi">
      <div className="wd-kpi-shape">
        <WdIcon name={icon} size={21} color={iconCol} sw={1.8} />
      </div>
      <div className="wd-kpi-body">
        <div style={{ fontFamily: dFONT.title, fontSize: 28, letterSpacing: '-0.03em', color: dINK, lineHeight: 1 }}>{value}</div>
        <div style={{ fontFamily: dFONT.mono, fontSize: 9.5, color: dDIM, letterSpacing: '0.12em', marginTop: 7, whiteSpace: 'nowrap' }}>{label.toUpperCase()}</div>
        {delta && <div style={{ fontFamily: dFONT.mono, fontSize: 10.5, color: up ? dWIN : dLOSS, marginTop: 6, display: 'flex', alignItems: 'center', gap: 4, whiteSpace: 'nowrap' }}>
          {up ? '▲' : '▼'} {delta}
        </div>}
      </div>
    </div>
  );
}

// ── Carte panneau générique ────────────────────────────────────────────────────
function Panel({ title, sub, right, children, pad = 22 }) {
  return (
    <div className="wd-panel">
      {(title || right) && (
        <div className="wd-panelhead">
          <div>
            <div style={{ fontFamily: dFONT.title, fontSize: 16, letterSpacing: '-0.02em', color: dINK }}>{title}</div>
            {sub && <div style={{ fontFamily: dFONT.mono, fontSize: 9.5, color: dDIM, letterSpacing: '0.12em', marginTop: 4, whiteSpace: 'nowrap' }}>{sub}</div>}
          </div>
          {right}
        </div>
      )}
      <div style={{ padding: pad, paddingTop: (title || right) ? 0 : pad }}>{children}</div>
    </div>
  );
}

// ── Section matchs "À l'affiche" ───────────────────────────────────────────────
function MatchCards() {
  const ids = ['psg-om', 'rma-bay', 'liv-ars'];
  const matches = ids.map(id => window.MATCHES.find(m => m.id === id));
  return (
    <div className="wd-matches">
      {matches.map(m => (
        <div key={m.id} className="wd-matchcard">
          <div style={{ height: 132, position: 'relative' }}>
            <MatchBackdrop home={m.home} away={m.away}>
              <div style={{ position: 'absolute', top: 12, left: 12 }}>
                <Pill bg="rgba(11,13,16,0.62)" color={dINK} border={dLINE2}>{m.competition.toUpperCase()} · {m.round}</Pill>
              </div>
              <div style={{ position: 'absolute', bottom: 12, left: 12, right: 12, display: 'flex', alignItems: 'center', gap: 10 }}>
                <TeamBadge code={window.TEAMS[m.home].code} color={window.TEAMS[m.home].color} text={window.TEAMS[m.home].text} size={28} />
                <div style={{ flex: 1, fontFamily: dFONT.title, fontSize: 18, color: dINK, letterSpacing: '-0.02em' }}>
                  {window.TEAMS[m.home].short} <span style={{ color: dDIM2, fontSize: 12 }}>·</span> {window.TEAMS[m.away].short}
                </div>
                <TeamBadge code={window.TEAMS[m.away].code} color={window.TEAMS[m.away].color} text={window.TEAMS[m.away].text} size={28} />
              </div>
            </MatchBackdrop>
          </div>
          <div style={{ padding: '12px 14px', display: 'flex', alignItems: 'center', gap: 12 }}>
            <ConfidenceRing value={m.confidence} size={46} stroke={4} />
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontFamily: dFONT.mono, fontSize: 9, color: dDIM, letterSpacing: '0.14em' }}>CONSEIL IA · {m.kickoff}</div>
              <div style={{ fontSize: 13, color: dINK, fontWeight: 600, marginTop: 3, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{m.pick.type}</div>
            </div>
            <OddsChip value={m.pick.odds} size="sm" highlight={m.confidence >= 85} />
          </div>
        </div>
      ))}
    </div>
  );
}

// ── Tableau "Mes carnets" ──────────────────────────────────────────────────────
function HistoryTable() {
  const STAT = {
    win:  { label: 'Gagné',    color: dWIN,  bg: 'rgba(61,220,145,0.12)' },
    live: { label: 'En cours', color: dACCENT, bg: dACCENT_DIM },
    loss: { label: 'Perdu',    color: dLOSS, bg: 'rgba(255,91,58,0.12)' },
  };
  return (
    <div className="wd-table">
      <div className="wd-tr wd-thead">
        <div className="wd-cell wd-c-match">Match</div>
        <div className="wd-cell wd-c-pick">Sélection</div>
        <div className="wd-cell wd-c-odds">Cote</div>
        <div className="wd-cell wd-c-stat">Statut</div>
        <div className="wd-cell wd-c-gain">Gain</div>
      </div>
      {window.WD_HISTORY.map((r, i) => {
        const m = window.MATCHES.find(x => x.id === r.matchId);
        const h = window.TEAMS[m.home], a = window.TEAMS[m.away];
        const st = STAT[r.statut];
        const gainCol = r.statut === 'win' ? dWIN : r.statut === 'loss' ? dLOSS : dDIM;
        return (
          <div key={i} className="wd-tr">
            <div className="wd-cell wd-c-match">
              <div className="wd-thumb">
                <MatchBackdrop home={m.home} away={m.away} intensity={0.95} />
                <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4 }}>
                  <TeamBadge code={h.code} color={h.color} text={h.text} size={20} />
                  <TeamBadge code={a.code} color={a.color} text={a.text} size={20} />
                </div>
              </div>
              <div style={{ minWidth: 0 }}>
                <div style={{ fontFamily: dFONT.title, fontSize: 14, color: dINK, letterSpacing: '-0.01em' }}>{h.short} – {a.short}</div>
                <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: dDIM, marginTop: 3, whiteSpace: 'nowrap' }}>{r.date} · mise {r.stake} €</div>
              </div>
            </div>
            <div className="wd-cell wd-c-pick" style={{ fontSize: 13, color: dINK2 }}>{r.type}</div>
            <div className="wd-cell wd-c-odds" style={{ fontFamily: dFONT.mono, fontSize: 13, fontWeight: 700, color: dINK }}>@{r.odds}</div>
            <div className="wd-cell wd-c-stat">
              <span className="wd-badge" style={{ color: st.color, background: st.bg }}>
                {r.statut === 'live' && <span className="wd-livedot" />}{st.label}
              </span>
            </div>
            <div className="wd-cell wd-c-gain" style={{ fontFamily: dFONT.mono, fontSize: 13, fontWeight: 700, color: gainCol, whiteSpace: 'nowrap' }}>{r.gain}</div>
          </div>
        );
      })}
    </div>
  );
}

// ── Colonne droite : avis + meilleurs carnets ──────────────────────────────────
function ReviewList() {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
      {window.WD_REVIEWS.map((r, i) => (
        <div key={i} style={{ borderTop: i ? `1px solid ${dLINE}` : 'none', paddingTop: i ? 18 : 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
            <Avatar initials={r.initials} tone={r.tone} size={38} />
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontFamily: dFONT.title, fontSize: 14, color: dINK, letterSpacing: '-0.01em' }}>{r.name}</div>
              <div style={{ fontFamily: dFONT.mono, fontSize: 9.5, color: dDIM, letterSpacing: '0.08em', marginTop: 2 }}>{r.city} · {r.date}</div>
            </div>
            <div style={{ display: 'flex', gap: 1 }}>
              {[0,1,2,3,4].map(s => <WdIcon key={s} name="star" size={12} color={s < r.stars ? dACCENT : dLINE2} />)}
            </div>
          </div>
          <p style={{ fontSize: 12.5, color: dINK2, lineHeight: 1.5, margin: '10px 0 0', textWrap: 'pretty' }}>{r.text}</p>
        </div>
      ))}
    </div>
  );
}

function TipsterList() {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
      {window.WD_TIPSTERS.map((t) => {
        const col = WD_TONES[t.tone];
        return (
          <div key={t.id} style={{ display: 'flex', alignItems: 'center', gap: 13, padding: 13, background: dBG3, borderRadius: 11, border: `1px solid ${dLINE}` }}>
            <div style={{ width: 42, height: 42, borderRadius: 11, background: 'rgba(255,255,255,0.04)', border: `1px solid ${dLINE2}`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              <WdIcon name="coupon" size={19} color={col} />
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontFamily: dFONT.title, fontSize: 14, color: dINK, letterSpacing: '-0.01em' }}>{t.name}</div>
              <div style={{ fontFamily: dFONT.mono, fontSize: 9.5, color: dDIM, letterSpacing: '0.1em', marginTop: 3 }}>{t.tag} · {t.odds} · {t.win}</div>
            </div>
            <div style={{ fontFamily: dFONT.title, fontSize: 17, color: col, letterSpacing: '-0.02em' }}>{t.roi}</div>
          </div>
        );
      })}
    </div>
  );
}

Object.assign(window, {
  WD_TONES, WdIcon, Avatar, Sidebar, Topbar, FilterBar, PageHeader,
  KpiCard, Panel, MatchCards, HistoryTable, ReviewList, TipsterList,
});
