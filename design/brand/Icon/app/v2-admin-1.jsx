// COTA V2 — Section D: Admin dashboard (9 pages).

const { BG: dBG, BG2: dBG2, BG3: dBG3, LINE: dLINE, LINE2: dLINE2, INK: dINK, INK2: dINK2, DIM: dDIM, DIM2: dDIM2, ACCENT: dACCENT, WIN: dWIN, LOSS: dLOSS, font: dFONT } = window.COTA;

const D_MAIN = '#0f1117';

function AdminFrame({ active, breadcrumb, title, actions, children }) {
  return (
    <div style={{ width: '100%', height: '100%', background: D_MAIN, color: dINK, fontFamily: dFONT.ui, position: 'relative', overflow: 'hidden' }}>
      <AdminSidebar active={active} />
      <main style={{ marginLeft: 240, height: '100%', display: 'flex', flexDirection: 'column' }}>
        <AdminTopbar breadcrumb={breadcrumb} title={title} actions={actions} />
        <div style={{ flex: 1, overflow: 'auto', padding: '24px 28px' }}>
          {children}
        </div>
      </main>
    </div>
  );
}

// ── D1 · Vue d'ensemble ──────────────────────────────────────────────────────
function AdminOverview() {
  const barData = Array.from({ length: 30 }, (_, i) => ({
    label: `${i+1}`, won: Math.floor(Math.random() * 4 + 2), lost: Math.floor(Math.random() * 2), pending: i > 25 ? 1 : 0
  }));
  return (
    <AdminFrame
      active="dashboard"
      breadcrumb="DASHBOARD > VUE D'ENSEMBLE · MAR 18 MAI 2026"
      title="Vue d'ensemble"
      actions={(
        <>
          <button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>EXPORT CSV</button>
          <button style={{ background: dACCENT, color: dBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>PUBLIER LE COUPON</button>
        </>
      )}
    >
      {/* KPI Row */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 22 }}>
        <KPICard icon="≡" iconColor={dWIN}     value="12 472" label="COUPONS GÉNÉRÉS"  delta="+8% vs hier" />
        <KPICard icon="◉" iconColor="#3b82f6"  value="247"    label="MATCHS ANALYSÉS"  delta="aujourd'hui" />
        <KPICard icon="%" iconColor="#f59e0b"  value="72.1%"  label="TAUX RÉUSSITE"    delta="+1.2pt" />
        <KPICard icon="€" iconColor={dACCENT}  value="€18 240" label="GAINS UTILISATEURS" delta="+€2 400" />
      </div>

      {/* Charts row */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: 16, marginBottom: 22 }}>
        <div style={{ background: dBG2, border: `1px solid ${dLINE}`, borderRadius: 12, padding: 18 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 12 }}>
            <span style={{ fontFamily: dFONT.mono, fontSize: 11, color: dDIM, letterSpacing: '0.18em' }}>PICKS JOUR PAR JOUR</span>
            <div style={{ display: 'flex', gap: 4 }}>
              {['7J', '30J', '12M'].map((t, i) => (
                <span key={t} style={{ padding: '3px 8px', borderRadius: 4, fontFamily: dFONT.mono, fontSize: 9, color: i === 1 ? dBG : dDIM, background: i === 1 ? dACCENT : dBG3, letterSpacing: '0.1em', fontWeight: 700 }}>{t}</span>
              ))}
            </div>
          </div>
          <BarChart data={barData} width={600} height={220} />
          <div style={{ display: 'flex', gap: 14, marginTop: 12, fontFamily: dFONT.mono, fontSize: 10, color: dDIM, letterSpacing: '0.08em' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}><span style={{ width: 8, height: 8, background: dWIN, borderRadius: 2 }} /> GAGNÉS</div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}><span style={{ width: 8, height: 8, background: dLOSS, borderRadius: 2 }} /> PERDUS</div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}><span style={{ width: 8, height: 8, background: dDIM2, borderRadius: 2 }} /> EN ATTENTE</div>
          </div>
        </div>

        <div style={{ background: dBG2, border: `1px solid ${dLINE}`, borderRadius: 12, padding: 18, display: 'flex', flexDirection: 'column' }}>
          <div style={{ fontFamily: dFONT.mono, fontSize: 11, color: dDIM, letterSpacing: '0.18em', marginBottom: 14 }}>RÉPARTITION RÉSULTATS</div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 18 }}>
            <Donut segments={[{ value: 847, color: dWIN }, { value: 258, color: dLOSS }, { value: 71, color: dDIM2 }]} size={140} stroke={18} />
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
              {[
                ['847', 'Gagnés', dWIN, '✓'],
                ['258', 'Perdus', dLOSS, '✗'],
                ['71',  'Nuls',   dDIM, '—'],
              ].map(([n, l, c, i]) => (
                <div key={l} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ width: 18, height: 18, borderRadius: 4, background: `${c}22`, color: c, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: dFONT.mono, fontSize: 9, fontWeight: 700 }}>{i}</span>
                  <span style={{ fontFamily: dFONT.title, fontSize: 18, color: dINK, letterSpacing: '-0.02em' }}>{n}</span>
                  <span style={{ fontFamily: dFONT.mono, fontSize: 10, color: dDIM, letterSpacing: '0.1em' }}>{l}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Coupon du jour */}
      <div style={{ background: dBG2, border: `1px solid ${dLINE}`, borderRadius: 12, padding: 18, marginBottom: 22, position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', top: -30, right: -30, width: 200, height: 200, background: 'radial-gradient(circle, rgba(232,255,54,0.08), transparent 70%)' }} />
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', position: 'relative' }}>
          <div>
            <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: dACCENT, letterSpacing: '0.18em' }}>★ COUPON DU JOUR · EN ATTENTE DE PUBLICATION</div>
            <div style={{ fontFamily: dFONT.title, fontSize: 32, color: dACCENT, letterSpacing: '-0.04em', marginTop: 4 }}>@4.55</div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '8px 14px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.1em' }}>MODIFIER</button>
            <button style={{ background: dACCENT, color: dBG, border: 'none', padding: '8px 14px', borderRadius: 8, fontFamily: dFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>PUBLIER</button>
          </div>
        </div>
        <div style={{ marginTop: 14, display: 'grid', gridTemplateColumns: '1.5fr 1fr 1.5fr 80px 80px 80px', gap: 12, fontFamily: dFONT.mono, fontSize: 9, color: dDIM, letterSpacing: '0.15em', padding: '8px 0 6px', borderBottom: `1px solid ${dLINE}` }}>
          <span>MATCH</span><span>COMPÉTITION</span><span>PICK</span><span style={{ textAlign: 'right' }}>CONF.</span><span style={{ textAlign: 'right' }}>COTE</span><span style={{ textAlign: 'right' }}>STATUT</span>
        </div>
        {[
          ['PSG-OM',   'Ligue 1',   'Victoire PSG',  '87%', '1.65'],
          ['LIV-ARS',  'Premier L.', '+2.5 buts',     '76%', '1.78'],
          ['RMA-BAY',  'UCL',       'BTTS Oui',      '91%', '1.55'],
        ].map(([m, c, p, cf, o]) => (
          <div key={m} style={{ display: 'grid', gridTemplateColumns: '1.5fr 1fr 1.5fr 80px 80px 80px', gap: 12, padding: '12px 0', borderBottom: `1px solid ${dLINE}`, alignItems: 'center', fontSize: 12, color: dINK }}>
            <span style={{ fontWeight: 600 }}>{m}</span>
            <span style={{ color: dDIM }}>{c}</span>
            <span>{p}</span>
            <span style={{ fontFamily: dFONT.mono, color: parseInt(cf) >= 85 ? dACCENT : dINK, textAlign: 'right' }}>{cf}</span>
            <span style={{ fontFamily: dFONT.mono, color: dACCENT, textAlign: 'right', fontWeight: 700 }}>@{o}</span>
            <span style={{ textAlign: 'right' }}><StatusBadge kind="wait" label="EN ATTENTE" /></span>
          </div>
        ))}
      </div>

      {/* Users table */}
      <div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 12 }}>
          <span style={{ fontFamily: dFONT.title, fontSize: 18, letterSpacing: '-0.02em' }}>Derniers utilisateurs actifs</span>
          <span style={{ fontFamily: dFONT.mono, fontSize: 10, color: dACCENT, letterSpacing: '0.12em' }}>VOIR TOUS →</span>
        </div>
        <AdminTable
          columns={[
            { label: '#',           w: '36px' },
            { label: 'UTILISATEUR', w: '1.4fr' },
            { label: 'PLAN',        w: '110px' },
            { label: 'DERNIER ACCÈS', w: '160px', mono: true },
            { label: 'STATUT',      w: '110px' },
            { label: '',            w: '120px', align: 'right' },
          ]}
          rows={[
            ['1', <UserCell name="Karim B." sub="+226 70 12 34 56" />, <StatusBadge kind="premium" label="PREMIUM" />, '18 mai 10:31', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
            ['2', <UserCell name="Aminata D." sub="+221 77 45 89 23" />, <StatusBadge kind="free" label="GRATUIT" />, '18 mai 09:12', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
            ['3', <UserCell name="Ibrahim S." sub="+225 07 88 12 34" />, <StatusBadge kind="premium" label="PREMIUM" />, '17 mai 22:45', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
            ['4', <UserCell name="Fatou K." sub="+221 78 22 11 90" />, <StatusBadge kind="free" label="GRATUIT" />, '16 mai 18:20', <StatusBadge kind="inactif" label="INACTIF" />, <RowActions />],
            ['5', <UserCell name="Modou T." sub="+221 76 88 44 12" />, <StatusBadge kind="premium" label="PREMIUM" />, '18 mai 08:50', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ]}
        />
      </div>
    </AdminFrame>
  );
}

function UserCell({ name, sub }) {
  const initials = name.split(' ').map(n => n[0]).join('');
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
      <div style={{ width: 30, height: 30, borderRadius: 15, background: dBG3, color: dINK, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: dFONT.title, fontSize: 11, border: `1px solid ${dLINE2}` }}>{initials}</div>
      <div>
        <div style={{ fontSize: 12, fontWeight: 600 }}>{name}</div>
        <div style={{ fontFamily: dFONT.mono, fontSize: 9, color: dDIM, letterSpacing: '0.06em', marginTop: 1 }}>{sub}</div>
      </div>
    </div>
  );
}

function RowActions() {
  return (
    <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end' }}>
      <button style={{ background: 'transparent', color: dACCENT, border: 'none', fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em', padding: 0 }}>VOIR</button>
      <span style={{ color: dDIM2 }}>·</span>
      <button style={{ background: 'transparent', color: dDIM, border: 'none', fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em', padding: 0 }}>···</button>
    </div>
  );
}

// ── D2 · Users ───────────────────────────────────────────────────────────────
function AdminUsers() {
  return (
    <AdminFrame
      active="users"
      breadcrumb="UTILISATEURS"
      title="Utilisateurs"
      actions={<button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>↓ EXPORTER</button>}
    >
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14, marginBottom: 22 }}>
        <KPICard icon="◉" iconColor="#3b82f6" value="47 284" label="TOTAL UTILISATEURS" delta="+842 ce mois" />
        <KPICard icon="★" iconColor={dACCENT} value="3 142"  label="PREMIUM ACTIFS"     delta="6.6% du total" />
        <KPICard icon="↗" iconColor={dWIN}    value="+842"   label="NOUVEAUX CE MOIS"   delta="+18% vs avril" />
      </div>

      {/* Filters */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 14 }}>
        <div style={{ display: 'flex', gap: 6 }}>
          {['TOUS', 'PREMIUM', 'GRATUITS', 'INACTIFS'].map((t, i) => (
            <span key={t} style={{ padding: '6px 12px', borderRadius: 999, background: i === 0 ? dACCENT : dBG2, color: i === 0 ? dBG : dDIM, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.1em', fontWeight: 700, border: i === 0 ? 'none' : `1px solid ${dLINE2}` }}>{t}</span>
          ))}
        </div>
        <div style={{ flex: 1 }} />
        <div style={{ padding: '8px 12px', background: dBG2, border: `1px solid ${dLINE2}`, borderRadius: 8, fontFamily: dFONT.mono, fontSize: 11, color: dDIM, display: 'flex', alignItems: 'center', gap: 8, width: 220 }}>
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="5" cy="5" r="3.5" stroke={dDIM} strokeWidth="1.2"/><path d="M8 8 L11 11" stroke={dDIM} strokeWidth="1.2" strokeLinecap="round"/></svg>
          Rechercher un utilisateur...
        </div>
        <select style={{ padding: '8px 12px', background: dBG2, color: dINK, border: `1px solid ${dLINE2}`, borderRadius: 8, fontFamily: dFONT.mono, fontSize: 11, letterSpacing: '0.1em' }}>
          <option>TOUS PAYS</option>
        </select>
      </div>

      <AdminTable
        columns={[
          { label: '#', w: '40px' },
          { label: 'NOM',        w: '1.5fr' },
          { label: 'TÉL',        w: '1.2fr', mono: true },
          { label: 'PLAN',       w: '110px' },
          { label: 'PARRAIN',    w: '1fr' },
          { label: 'INSCRIPTION', w: '1.2fr', mono: true },
          { label: 'DERNIER ACCÈS', w: '1fr', mono: true },
          { label: 'STATUT',     w: '100px' },
          { label: '',           w: '90px', align: 'right' },
        ]}
        rows={[
          ['1', <UserCell name="Karim B." sub="ID #00471" />,    '+226 70 12 34 56', <StatusBadge kind="premium" label="PREMIUM" />, 'Ibrahim S.', '12 nov 25', 'Aujourd\'hui', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ['2', <UserCell name="Aminata D." sub="ID #00472" />,  '+221 77 45 89 23', <StatusBadge kind="free" label="GRATUIT" />, '—',          '3 janv 26', 'Hier',         <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ['3', <UserCell name="Ibrahim S." sub="ID #00473" />,  '+225 07 88 12 34', <StatusBadge kind="premium" label="PREMIUM" />, '—',         '14 août 25', 'Hier',         <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ['4', <UserCell name="Fatou K." sub="ID #00474" />,    '+221 78 22 11 90', <StatusBadge kind="free" label="GRATUIT" />, 'Karim B.',    '20 fév 26',  '16 mai 18:20', <StatusBadge kind="inactif" label="INACTIF" />, <RowActions />],
          ['5', <UserCell name="Modou T." sub="ID #00475" />,    '+221 76 88 44 12', <StatusBadge kind="premium" label="PREMIUM" />, 'Fatou K.',  '8 mars 26',  'Aujourd\'hui', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ['6', <UserCell name="Aïssa L." sub="ID #00476" />,    '+228 90 12 67 33', <StatusBadge kind="free" label="GRATUIT" />, 'Modou T.',    '1 mai 26',  '17 mai 14:00', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
          ['7', <UserCell name="Boris W." sub="ID #00477" />,    '+229 91 33 22 11', <StatusBadge kind="premium" label="PREMIUM" />, '—',         '10 avr 26', 'Aujourd\'hui', <StatusBadge kind="actif" label="ACTIF" />, <RowActions />],
        ]}
      />

      {/* Pagination */}
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: 6, marginTop: 18, fontFamily: dFONT.mono, fontSize: 11, color: dDIM, letterSpacing: '0.08em' }}>
        <span style={{ padding: '6px 10px', borderRadius: 6, background: dBG2, border: `1px solid ${dLINE2}` }}>←</span>
        {['1', '2', '3', '...', '47'].map((p, i) => (
          <span key={p + i} style={{ padding: '6px 10px', borderRadius: 6, background: p === '1' ? dACCENT : dBG2, color: p === '1' ? dBG : dDIM, fontWeight: 700, border: p === '1' ? 'none' : `1px solid ${dLINE2}` }}>{p}</span>
        ))}
        <span style={{ padding: '6px 10px', borderRadius: 6, background: dBG2, border: `1px solid ${dLINE2}` }}>→</span>
      </div>
    </AdminFrame>
  );
}

// ── D3 · Prédictions ─────────────────────────────────────────────────────────
function AdminPredictions() {
  return (
    <AdminFrame
      active="predictions"
      breadcrumb="PRÉDICTIONS"
      title="Prédictions"
      actions={(
        <>
          <button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>↻ RAFRAÎCHIR</button>
          <button style={{ background: dACCENT, color: dBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>GÉNÉRER PRÉDICTIONS</button>
        </>
      )}
    >
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 22 }}>
        <KPICard icon="◐" iconColor="#3b82f6"  value="247" label="ANALYSÉS" delta="aujourd'hui" />
        <KPICard icon="✓" iconColor={dWIN}    value="89"  label="PUBLIÉES" delta="36% du total" />
        <KPICard icon="%" iconColor="#f59e0b" value="67%" label="CONFIANCE MOY." delta="≥65% requis" />
        <KPICard icon="●" iconColor={dACCENT} value="12"  label="EN LIVE" delta="à ce moment" />
      </div>

      <div style={{ display: 'flex', gap: 6, marginBottom: 14 }}>
        {['AUJOURD\'HUI', 'CETTE SEMAINE', 'PAR COMPÉT.', '★', '★★', '★★★', '★★★★'].map((t, i) => (
          <span key={t} style={{ padding: '6px 12px', borderRadius: 999, background: i === 0 ? dACCENT : dBG2, color: i === 0 ? dBG : dDIM, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.1em', fontWeight: 700, border: i === 0 ? 'none' : `1px solid ${dLINE2}` }}>{t}</span>
        ))}
      </div>

      <AdminTable
        columns={[
          { label: 'MATCH', w: '1.6fr' },
          { label: 'COMPÉTITION', w: '1fr' },
          { label: 'PICK',  w: '1.4fr' },
          { label: 'CONFIANCE', w: '160px' },
          { label: 'COTE',  w: '90px',  align: 'right', mono: true },
          { label: 'ÉTOILES', w: '120px' },
          { label: 'STATUT', w: '120px' },
          { label: '',      w: '70px',  align: 'right' },
        ]}
        rows={[
          ['PSG – OM',  'Ligue 1',  'Victoire PSG', <ConfBar v={87} />, '@1.65', '★★★★', <StatusBadge kind="live" label="EN COURS" />, <RowActions />],
          ['LIV – ARS', 'Premier L.', '+2.5 buts',   <ConfBar v={76} />, '@1.78', '★★★',  <StatusBadge kind="live" label="EN COURS" />, <RowActions />],
          ['RMA – BAY', 'UCL',      'BTTS Oui',     <ConfBar v={91} />, '@1.55', '★★★★', <StatusBadge kind="live" label="EN COURS" />, <RowActions />],
          ['ASM – OL',  'Ligue 1',  'Monaco +1.5',  <ConfBar v={68} />, '@1.42', '★★',   <StatusBadge kind="wait" label="À VENIR" />, <RowActions />],
          ['LIL – NIC', 'Ligue 1',  '-2.5 buts',    <ConfBar v={62} />, '@1.92', '★★',   <StatusBadge kind="wait" label="À VENIR" />, <RowActions />],
          ['MCI – TOT', 'Premier L.', 'Man City win', <ConfBar v={82} />, '@1.55', '★★★', <StatusBadge kind="wait" label="À VENIR" />, <RowActions />],
        ]}
      />
    </AdminFrame>
  );
}

function ConfBar({ v }) {
  const high = v >= 85;
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <div style={{ flex: 1, height: 6, background: dLINE2, borderRadius: 3, overflow: 'hidden', maxWidth: 80 }}>
        <div style={{ height: '100%', width: `${v}%`, background: high ? dACCENT : dINK, borderRadius: 3 }} />
      </div>
      <span style={{ fontFamily: dFONT.mono, fontSize: 11, color: high ? dACCENT : dINK, fontWeight: 700, width: 36, textAlign: 'right' }}>{v}%</span>
    </div>
  );
}

// ── D4 · Coupons ─────────────────────────────────────────────────────────────
function AdminCoupons() {
  return (
    <AdminFrame
      active="coupons"
      breadcrumb="COUPONS"
      title="Coupons"
      actions={<Pill bg={dACCENT} color={dBG}>PUBLIÉ · 09:30</Pill>}
    >
      {/* Coupon du jour */}
      <div style={{ background: dBG2, border: `1px solid ${dACCENT}`, borderRadius: 12, padding: 22, marginBottom: 22, position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', top: -40, right: -40, width: 220, height: 220, background: 'radial-gradient(circle, rgba(232,255,54,0.10), transparent 70%)' }} />
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', position: 'relative' }}>
          <div>
            <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: dACCENT, letterSpacing: '0.18em' }}>COUPON DU 18 MAI</div>
            <div style={{ fontFamily: dFONT.title, fontSize: 32, color: dACCENT, letterSpacing: '-0.04em', marginTop: 4 }}>@4.55 · 87%</div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '8px 12px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.1em' }}>REPUBLIER</button>
            <button style={{ background: 'transparent', color: dDIM, border: `1px solid ${dLINE2}`, padding: '8px 12px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.1em' }}>ARCHIVER</button>
          </div>
        </div>
        {/* Timeline */}
        <div style={{ marginTop: 18, padding: '14px 16px', background: dBG, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'relative' }}>
          {[
            ['08:47', 'GÉNÉRÉ',     dWIN],
            ['08:52', 'VALIDÉ IA',  dWIN],
            ['09:30', 'PUBLIÉ',     dACCENT],
            ['09:31', 'NOTIFIÉ',    dWIN],
          ].map(([t, l, c], i, arr) => (
            <React.Fragment key={l}>
              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                <div style={{ width: 12, height: 12, borderRadius: 6, background: c, boxShadow: `0 0 0 4px ${c}33` }} />
                <div style={{ fontFamily: dFONT.mono, fontSize: 10, color: c, fontWeight: 700, letterSpacing: '0.1em', marginTop: 4 }}>{t}</div>
                <div style={{ fontFamily: dFONT.mono, fontSize: 9, color: dDIM, letterSpacing: '0.15em' }}>{l}</div>
              </div>
              {i < arr.length - 1 && <div style={{ flex: 1, height: 1, background: dLINE2, margin: '0 8px' }} />}
            </React.Fragment>
          ))}
        </div>
      </div>

      <div style={{ fontFamily: dFONT.title, fontSize: 18, letterSpacing: '-0.02em', marginBottom: 12 }}>Historique des coupons</div>

      <AdminTable
        columns={[
          { label: 'DATE',    w: '110px', mono: true },
          { label: 'PICKS',   w: '70px',  align: 'center' },
          { label: 'COTE',    w: '90px',  align: 'right', mono: true },
          { label: 'CONFIANCE', w: '100px', align: 'right', mono: true },
          { label: 'RÉSULTAT', w: '140px' },
          { label: 'GAINS USERS', w: '120px', align: 'right', mono: true },
          { label: '',       w: '70px',  align: 'right' },
        ]}
        rows={[
          ['18 mai', '3', '@4.55', '87%', <StatusBadge kind="wait" label="EN ATTENTE" />, '—',         <RowActions />],
          ['17 mai', '3', '@3.12', '79%', <StatusBadge kind="won"  label="✓ GAGNÉ" />,     '+€3 120',  <RowActions />],
          ['16 mai', '3', '@5.10', '71%', <StatusBadge kind="lost" label="✗ PERDU" />,     '-€1 200',  <RowActions />],
          ['15 mai', '4', '@6.80', '68%', <StatusBadge kind="won"  label="✓ GAGNÉ" />,     '+€8 160',  <RowActions />],
          ['14 mai', '3', '@4.20', '82%', <StatusBadge kind="won"  label="✓ GAGNÉ" />,     '+€4 200',  <RowActions />],
          ['13 mai', '3', '@5.50', '69%', <StatusBadge kind="lost" label="✗ PERDU" />,     '-€1 100',  <RowActions />],
        ]}
      />
    </AdminFrame>
  );
}

// ── D5 · Subscriptions ───────────────────────────────────────────────────────
function AdminSubs() {
  const months = ['JUIN', 'JUIL', 'AOÛT', 'SEPT', 'OCT', 'NOV', 'DÉC', 'JANV', 'FÉV', 'MARS', 'AVR', 'MAI'];
  const rev = [8.2, 9.4, 10.1, 11.2, 11.8, 12.4, 13.5, 14.2, 14.8, 15.1, 15.4, 15.68];
  return (
    <AdminFrame
      active="subscriptions"
      breadcrumb="ABONNEMENTS"
      title="Abonnements"
      actions={<button style={{ background: 'transparent', color: dINK, border: `1px solid ${dLINE2}`, padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.mono, fontSize: 10, letterSpacing: '0.12em' }}>ACCORDER PREMIUM MANUEL</button>}
    >
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 22 }}>
        <KPICard icon="★" iconColor={dACCENT} value="3 142"   label="ABONNÉS ACTIFS"     delta="+118 ce mois" />
        <KPICard icon="€" iconColor={dWIN}    value="15.68M"  label="REVENUS CE MOIS"    delta="+12% MRR" />
        <KPICard icon="↓" iconColor={dLOSS}   value="2.1%"    label="CHURN"              delta="-0.3pt" deltaColor={dWIN} />
        <KPICard icon="↗" iconColor="#3b82f6" value="+12%"    label="MRR CROISSANCE"     delta="vs avril" />
      </div>

      {/* Revenue chart */}
      <div style={{ background: dBG2, border: `1px solid ${dLINE}`, borderRadius: 12, padding: 20, marginBottom: 22 }}>
        <div style={{ fontFamily: dFONT.mono, fontSize: 11, color: dDIM, letterSpacing: '0.18em', marginBottom: 14 }}>REVENUS MENSUELS · 12 MOIS · MILLIONS FCFA</div>
        <svg width="100%" height="180" viewBox="0 0 880 180">
          {[0.25, 0.5, 0.75, 1].map(p => (
            <line key={p} x1={40} x2={880} y1={160 - 140 * p} y2={160 - 140 * p} stroke={dLINE} strokeDasharray="2 3" />
          ))}
          {rev.map((v, i) => {
            const x = 50 + i * 68;
            const h = (v / 16) * 140;
            return (
              <g key={i}>
                <rect x={x} y={160 - h} width={48} height={h} fill={i === rev.length - 1 ? dACCENT : '#3b82f6'} rx="2" />
                <text x={x + 24} y={176} textAnchor="middle" fontFamily={dFONT.mono} fontSize="9" fill={dDIM2} letterSpacing="0.1em">{months[i]}</text>
                {i === rev.length - 1 && <text x={x + 24} y={160 - h - 6} textAnchor="middle" fontFamily={dFONT.mono} fontSize="10" fill={dACCENT} fontWeight="700">{v}M</text>}
              </g>
            );
          })}
        </svg>
      </div>

      <AdminTable
        columns={[
          { label: 'USER',     w: '1.4fr' },
          { label: 'PLAN',     w: '110px' },
          { label: 'MONTANT',  w: '130px', mono: true, align: 'right' },
          { label: 'MÉTHODE',  w: '110px' },
          { label: 'DÉBUT',    w: '90px',  mono: true },
          { label: 'FIN',      w: '110px', mono: true },
          { label: 'STATUT',   w: '100px' },
          { label: '',         w: '110px', align: 'right' },
        ]}
        rows={[
          ['Karim B.',   'MENSUEL', '4 990',  'Wave',         '12 avr', '12 mai',      <StatusBadge kind="actif" label="ACTIF" />,   <RowActions />],
          ['Aminata D.', 'ANNUEL',  '49 900', 'Orange Money', '3 janv',  '3 janv 27',  <StatusBadge kind="actif" label="ACTIF" />,   <RowActions />],
          ['Ibrahim S.', 'MENSUEL', '4 990',  'MTN Money',    '1 mars',  '1 juin',     <StatusBadge kind="actif" label="ACTIF" />,   <RowActions />],
          ['Fatou K.',   'MENSUEL', '4 990',  'Moov',         '5 fév',   '5 mars',     <StatusBadge kind="inactif" label="EXPIRÉ" />, <RowActions />],
          ['Modou T.',   'ANNUEL',  '49 900', 'Wave',         '20 mars', '20 mars 27', <StatusBadge kind="actif" label="ACTIF" />,   <RowActions />],
          ['Boris W.',   'MENSUEL', '4 990',  'Orange Money', '10 avr',  '10 mai',     <StatusBadge kind="actif" label="ACTIF" />,   <RowActions />],
        ]}
      />
    </AdminFrame>
  );
}

// ── D6 · Bookmakers ──────────────────────────────────────────────────────────
function AdminBookmakers() {
  return (
    <AdminFrame
      active="bookmakers"
      breadcrumb="BOOKMAKERS & AFFILIATIONS"
      title="Bookmakers"
      actions={<button style={{ background: dACCENT, color: dBG, border: 'none', padding: '9px 14px', borderRadius: 8, fontFamily: dFONT.title, fontSize: 10, letterSpacing: '0.08em' }}>+ AJOUTER BOOKMAKER</button>}
    >
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14, marginBottom: 22 }}>
        <KPICard icon="↗" iconColor="#3b82f6"  value="2 847" label="CLICS CE MOIS"    delta="+14% vs avril" />
        <KPICard icon="✓" iconColor={dWIN}    value="142"   label="CONVERSIONS"      delta="≈5% taux" />
        <KPICard icon="★" iconColor={dACCENT} value="1xBet" label="TOP BOOKMAKER"    delta="1 204 clics" />
      </div>

      <div style={{ fontFamily: dFONT.title, fontSize: 18, letterSpacing: '-0.02em', marginBottom: 12 }}>Liste des bookmakers</div>
      <AdminTable
        columns={[
          { label: '#',      w: '34px' },
          { label: 'NOM',    w: '160px' },
          { label: 'RÉGION', w: '150px' },
          { label: 'LIEN AFFILIÉ', w: '1.4fr', mono: true },
          { label: 'COTE EX.', w: '90px', align: 'right', mono: true },
          { label: 'CLICS',  w: '90px', align: 'right', mono: true },
          { label: 'ORDRE', w: '70px', align: 'center' },
          { label: 'ACTIF',  w: '70px', align: 'center' },
          { label: '',       w: '120px', align: 'right' },
        ]}
        rows={[
          ['1', <BookCell n="1xBet" />,     'Afrique Ouest', 'https://1xbet.com/?ref=cota',     '@1.65', '1 204', '1', <Toggle on={true} />,  <RowActions />],
          ['2', <BookCell n="Betwinner" />, 'Afrique Ouest', 'https://betwinner.com/?ref=cota', '@1.68', '687',   '2', <Toggle on={true} />,  <RowActions />],
          ['3', <BookCell n="Melbet" />,    'Afrique Ouest', 'https://melbet.com/?ref=cota',    '@1.62', '412',   '3', <Toggle on={true} />,  <RowActions />],
          ['4', <BookCell n="Bet365" />,    'Europe',        'https://bet365.com/?ref=cota',    '@1.72', '298',   '1', <Toggle on={true} />,  <RowActions />],
          ['5', <BookCell n="Premier Bet" />, 'Afrique Ouest', 'https://premierbet.cm/?ref=cota', '@1.60', '246', '4', <Toggle on={false} />, <RowActions />],
        ]}
      />
    </AdminFrame>
  );
}

function BookCell({ n }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
      <div style={{ width: 28, height: 28, borderRadius: 6, background: dBG3, color: dINK, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: dFONT.title, fontSize: 12, border: `1px solid ${dLINE2}` }}>{n[0]}</div>
      <span style={{ fontSize: 12, fontWeight: 600 }}>{n}</span>
    </div>
  );
}

Object.assign(window, { AdminOverview, AdminUsers, AdminPredictions, AdminCoupons, AdminSubs, AdminBookmakers, AdminFrame, UserCell, RowActions, BookCell, ConfBar });
