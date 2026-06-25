// COTA Web — vues (2/2) : Compétitions · Favoris · Historique · Stats · Abonnement · Profil · Parrainage.

const { BG: wBG, BG2: wBG2, BG3: wBG3, LINE: wLINE, LINE2: wLINE2, INK: wINK, INK2: wINK2, DIM: wDIM, DIM2: wDIM2, ACCENT: wACC, ACCENT_DIM: wACC_DIM, COOL: wCOOL, COOL_DIM: wCOOL_DIM, WIN: wWIN, LOSS: wLOSS, font: wF } = window.COTA;

// Barre horizontale simple (réutilisée stats).
function StatBar({ label, sub, pct, color }) {
  return (
    <div style={{ padding: '13px 0', borderBottom: `1px solid ${wLINE}` }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
        <span style={{ fontSize: 13.5, color: wINK, fontWeight: 500 }}>{label}</span>
        <span style={{ fontFamily: wF.mono, fontSize: 12, color: wINK2, whiteSpace: 'nowrap' }}>{sub}</span>
      </div>
      <div style={{ height: 8, background: wBG3, borderRadius: 4, overflow: 'hidden' }}>
        <div style={{ height: '100%', width: `${pct}%`, background: color, borderRadius: 4 }} />
      </div>
    </div>
  );
}

// ════════════════ COMPÉTITIONS ════════════════
function CompetitionsView() {
  return (
    <>
      <PageHeader kicker="COUVERTURE" title="Compétitions"
        desc="Le modèle COTA couvre les grands championnats européens. Explorez les matchs analysés par compétition." />
      <div className="wd-cgrid">
        {window.WD_COMPETS.map(c => (
          <div key={c.id} className="wd-panel wd-ccard">
            <div className="wd-cbanner" style={{ background: `linear-gradient(135deg, ${c.color}, ${wBG2})` }}>
              <span style={{ fontFamily: wF.title, fontSize: 30, color: wINK, letterSpacing: '-0.03em', opacity: 0.9 }}>{c.mono}</span>
              {c.hot && <span style={{ position: 'absolute', top: 12, right: 12 }}><Pill bg="rgba(232,255,54,0.16)" color={wACC}><WdIcon name="flame" size={11} color={wACC} />HOT</Pill></span>}
            </div>
            <div style={{ padding: 16 }}>
              <div style={{ fontFamily: wF.title, fontSize: 16, color: wINK, letterSpacing: '-0.02em' }}>{c.name}</div>
              <div style={{ fontFamily: wF.mono, fontSize: 10, color: wDIM, letterSpacing: '0.1em', marginTop: 4 }}>{c.pays.toUpperCase()}</div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 16, paddingTop: 14, borderTop: `1px solid ${wLINE}` }}>
                <div>
                  <div style={{ fontFamily: wF.title, fontSize: 18, color: wINK }}>{c.matches}</div>
                  <div style={{ fontFamily: wF.mono, fontSize: 9, color: wDIM, letterSpacing: '0.12em', marginTop: 2 }}>MATCHS</div>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <div style={{ fontFamily: wF.title, fontSize: 18, color: wACC }}>{c.win}</div>
                  <div style={{ fontFamily: wF.mono, fontSize: 9, color: wDIM, letterSpacing: '0.12em', marginTop: 2 }}>RÉUSSITE</div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </>
  );
}

// ════════════════ FAVORIS ════════════════
function FavoritesView({ onOpen }) {
  const preds = window.WD_FAV_PREDS.map(id => window.WD_PREDICTIONS.find(p => p.matchId === id));
  return (
    <>
      <PageHeader kicker="VOTRE SÉLECTION" title="Favoris"
        desc="Vos équipes suivies et les prédictions que vous avez mises de côté." />
      <Panel title="Équipes suivies" sub={`${window.WD_FAV_TEAMS.length} ÉQUIPES`}>
        <div className="wd-teamrow">
          {window.WD_FAV_TEAMS.map(code => {
            const tm = window.TEAMS[code];
            return (
              <div key={code} className="wd-teamchip">
                <TeamBadge code={tm.code} color={tm.color} text={tm.text} size={40} />
                <span style={{ fontFamily: wF.title, fontSize: 13, color: wINK }}>{tm.short}</span>
                <WdIcon name="heart" size={14} color={wLOSS} />
              </div>
            );
          })}
          <div className="wd-teamchip wd-teamadd">
            <span style={{ fontFamily: wF.title, fontSize: 22, color: wDIM }}>+</span>
            <span style={{ fontSize: 11, color: wDIM }}>Ajouter</span>
          </div>
        </div>
      </Panel>
      <div style={{ height: 22 }} />
      <Panel title="Prédictions sauvegardées" sub={`${preds.length} EN ATTENTE`}>
        <div className="wd-pgrid">
          {preds.map(p => <PredictionCard key={p.matchId} pred={p} onOpen={onOpen} />)}
        </div>
      </Panel>
    </>
  );
}

// ════════════════ HISTORIQUE ════════════════
function HistoryView() {
  const won = window.WD_HISTORY.filter(r => r.statut === 'win').length;
  const lost = window.WD_HISTORY.filter(r => r.statut === 'loss').length;
  const kpis = [
    { id: 'won',  value: String(won),  label: 'Gagnées',    icon: 'check',  tone: 'lime' },
    { id: 'lost', value: String(lost), label: 'Perdues',    icon: 'arrowUp', tone: 'cool' },
    { id: 'rate', value: '72%',        label: 'Réussite',   icon: 'target', tone: 'lime' },
    { id: 'net',  value: '+49 €',      label: 'Profit récent', icon: 'wallet', tone: 'cool' },
  ];
  return (
    <>
      <PageHeader kicker="VOTRE PARCOURS" title="Historique"
        desc="Toutes vos prédictions suivies, gagnées comme perdues — en toute transparence."
        right={<GhostBtn><WdIcon name="filter" size={14} color={wINK} /> Filtrer</GhostBtn>} />
      <div className="wd-kpis">
        {kpis.map(k => <KpiCard key={k.id} {...k} />)}
      </div>
      <Panel title="Toutes mes prédictions" sub="ORDRE ANTÉCHRONOLOGIQUE">
        <HistoryTable />
      </Panel>
    </>
  );
}

// ════════════════ STATISTIQUES ════════════════
function StatsView({ t }) {
  const MODE = { 'Barres': 'bars', 'Aire': 'area', 'Ligne': 'line' };
  const accent = t.chartAccent || wACC;
  return (
    <>
      <PageHeader kicker="VOS PERFORMANCES" title="Statistiques"
        desc="Le détail de vos résultats : rendement dans le temps, par compétition et par type de pari." />
      <div className="wd-kpis">
        {window.WD_STATS_KPIS.map(k => <KpiCard key={k.id} {...k} />)}
      </div>
      <div className="wd-cols">
        <div className="wd-maincol">
          <Panel title="Rendement mensuel" sub="ROI PAR MOIS (%)"
            right={<Pill bg="rgba(61,220,145,0.12)" color={wWIN}>▲ +18.5% CUMULÉ</Pill>}>
            <PerfChart data={window.WD_STATS_MONTHLY.map(x => x.roi)} mode={MODE[t.chartStyle]} accent={accent} height={220} />
          </Panel>
          <Panel title="Réussite par compétition">
            {window.WD_STATS_BY_COMP.map(c => (
              <StatBar key={c.name} label={c.name} sub={`${c.win}% · ${c.n} paris`} pct={c.win} color={wACC} />
            ))}
          </Panel>
        </div>
        <div className="wd-rail">
          <Panel title="Réussite par type de pari">
            {window.WD_STATS_BY_TYPE.map(c => (
              <StatBar key={c.name} label={c.name} sub={`${c.win}% · ${c.n}`} pct={c.win} color={wCOOL} />
            ))}
          </Panel>
          <Panel title="Discipline" sub="CONSEIL COTA">
            <p style={{ fontSize: 13, color: wINK2, lineHeight: 1.6, margin: 0, textWrap: 'pretty' }}>
              Vous suivez le bankroll conseillé sur <strong style={{ color: wINK }}>89%</strong> de vos mises. Continuez : la régularité prime sur les coups d'éclat.
            </p>
            <div style={{ marginTop: 14 }}>
              <ConfidenceBar label="Bankroll suivi" value={89} color={wACC} />
            </div>
          </Panel>
        </div>
      </div>
    </>
  );
}

// ════════════════ ABONNEMENT ════════════════
function SubscriptionView() {
  return (
    <>
      <PageHeader kicker="VOTRE PLAN" title="Abonnement"
        desc="Choisissez la formule qui correspond à votre pratique. Sans engagement, résiliable à tout moment." />
      <div className="wd-plans">
        {window.WD_PLANS.map(p => (
          <div key={p.id} className={'wd-panel wd-plan' + (p.highlight ? ' wd-plan-hot' : '')}>
            {p.highlight && <div className="wd-plan-ribbon">POPULAIRE</div>}
            <div style={{ fontFamily: wF.title, fontSize: 20, color: p.highlight ? wACC : wINK, letterSpacing: '-0.02em' }}>{p.name}</div>
            <div style={{ fontSize: 12.5, color: wDIM, marginTop: 4 }}>{p.tagline}</div>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 4, margin: '18px 0' }}>
              <span style={{ fontFamily: wF.title, fontSize: 40, color: wINK, letterSpacing: '-0.04em' }}>{p.price}€</span>
              <span style={{ fontFamily: wF.mono, fontSize: 12, color: wDIM }}>{p.period}</span>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 11, marginBottom: 22 }}>
              {p.features.map(f => (
                <div key={f} style={{ display: 'flex', alignItems: 'center', gap: 9 }}>
                  <WdIcon name="check" size={15} color={p.highlight ? wACC : wWIN} sw={2.2} />
                  <span style={{ fontSize: 13, color: wINK2 }}>{f}</span>
                </div>
              ))}
            </div>
            <button className={p.current ? 'wd-plan-btn wd-plan-current' : (p.highlight ? 'wd-plan-btn wd-plan-cta' : 'wd-plan-btn')} disabled={p.current}>
              {p.cta}
            </button>
          </div>
        ))}
      </div>
    </>
  );
}

// ════════════════ PROFIL ════════════════
function ProfileView({ onNav }) {
  const u = window.WD_USER, p = window.WD_PROFILE;
  const Field = ({ label, value }) => (
    <div style={{ padding: '13px 0', borderBottom: `1px solid ${wLINE}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
      <span style={{ fontFamily: wF.mono, fontSize: 10, color: wDIM, letterSpacing: '0.12em', whiteSpace: 'nowrap' }}>{label}</span>
      <span style={{ fontSize: 13.5, color: wINK, textAlign: 'right', whiteSpace: 'nowrap' }}>{value}</span>
    </div>
  );
  return (
    <>
      <PageHeader kicker="MON COMPTE" title="Profil"
        right={<GhostBtn accent><WdIcon name="check" size={14} color={wACC} /> Enregistrer</GhostBtn>} />
      <div className="wd-cols">
        <div className="wd-maincol">
          <Panel title="Informations">
            <Field label="NOM" value={u.name} />
            <Field label="E-MAIL" value={p.email} />
            <Field label="TÉLÉPHONE" value={p.phone} />
            <Field label="VILLE" value={u.city} />
            <Field label="MEMBRE DEPUIS" value={p.joined} />
          </Panel>
          <Panel title="Préférences">
            {p.prefs.map(pr => (
              <div key={pr.label} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '13px 0', borderBottom: `1px solid ${wLINE}` }}>
                <span style={{ fontSize: 13.5, color: wINK }}>{pr.label}</span>
                <span className={'wd-toggle' + (pr.on ? ' on' : '')}><span className="wd-toggle-knob" /></span>
              </div>
            ))}
          </Panel>
        </div>
        <div className="wd-rail">
          <Panel>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center', gap: 12, padding: '6px 0' }}>
              <Avatar initials={u.initials} tone="lime" size={84} />
              <div>
                <div style={{ fontFamily: wF.title, fontSize: 18, color: wINK }}>{u.name}</div>
                <div style={{ fontSize: 12.5, color: wDIM, marginTop: 3 }}>{p.email}</div>
              </div>
              <Pill bg={wACC_DIM} color={wACC}><WdIcon name="bolt" size={11} color={wACC} /> {u.plan}</Pill>
            </div>
          </Panel>
          <Panel title="Compétitions suivies">
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
              {p.competitions.map(c => <Pill key={c} bg={wBG3} color={wINK2} border={wLINE2}>{c}</Pill>)}
            </div>
          </Panel>
          <button className="wd-plan-btn" onClick={() => onNav('abo')} style={{ cursor: 'pointer' }}>Gérer mon abonnement</button>
        </div>
      </div>
    </>
  );
}

// ════════════════ PARRAINAGE ════════════════
function ReferralView() {
  const r = window.WD_REFERRAL;
  const toneCol = { win: wWIN, cool: wCOOL, dim: wDIM };
  return (
    <>
      <PageHeader kicker="GAGNEZ ENSEMBLE" title="Parrainage"
        desc="Invitez vos amis : 30 € pour vous, 30 € pour eux dès leur premier abonnement Pro." />
      <div className="wd-cols">
        <div className="wd-maincol">
          <div className="wd-panel wd-refhero">
            <div style={{ fontFamily: wF.mono, fontSize: 10, color: wDIM, letterSpacing: '0.16em' }}>VOTRE CODE DE PARRAINAGE</div>
            <div className="wd-refcode">
              <span style={{ fontFamily: wF.title, fontSize: 26, color: wACC, letterSpacing: '0.02em' }}>{r.code}</span>
              <button className="wd-cta"><WdIcon name="check" size={14} color={wBG} /> Copier</button>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 14, marginTop: 22 }}>
              {[['Gagné', r.earned, wACC], ['En attente', r.pending, wCOOL], ['Invités', r.invited, wINK], ['Convertis', r.converted, wINK]].map(([l, v, c]) => (
                <div key={l} style={{ textAlign: 'center', padding: '14px 8px', background: wBG3, borderRadius: 12, border: `1px solid ${wLINE}` }}>
                  <div style={{ fontFamily: wF.title, fontSize: 22, color: c, letterSpacing: '-0.02em' }}>{v}</div>
                  <div style={{ fontFamily: wF.mono, fontSize: 9, color: wDIM, letterSpacing: '0.1em', marginTop: 5 }}>{l.toUpperCase()}</div>
                </div>
              ))}
            </div>
          </div>
          <div style={{ height: 22 }} />
          <Panel title="Vos filleuls" sub={`${r.friends.length} PERSONNES`}>
            <div style={{ display: 'flex', flexDirection: 'column' }}>
              {r.friends.map((f, i) => (
                <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 13, padding: '13px 0', borderTop: i ? `1px solid ${wLINE}` : 'none' }}>
                  <Avatar initials={f.initials} tone={f.tone === 'win' ? 'lime' : 'cool'} size={38} />
                  <div style={{ flex: 1 }}>
                    <div style={{ fontFamily: wF.title, fontSize: 14, color: wINK }}>{f.name}</div>
                    <div style={{ fontFamily: wF.mono, fontSize: 10, color: wDIM, letterSpacing: '0.06em', marginTop: 2 }}>{f.status.toUpperCase()}</div>
                  </div>
                  <span style={{ fontFamily: wF.mono, fontSize: 13, fontWeight: 700, color: toneCol[f.tone], whiteSpace: 'nowrap' }}>{f.reward}</span>
                </div>
              ))}
            </div>
          </Panel>
        </div>
        <div className="wd-rail">
          <Panel title="Comment ça marche">
            {[['1', 'Partagez votre code', 'Envoyez-le à vos amis ou copiez le lien.'],
              ['2', 'Ils s\u2019abonnent', 'Votre filleul prend un abonnement Pro ou Elite.'],
              ['3', 'Vous gagnez tous les deux', '30 € crédités sur chaque compte.']].map(([n, t, d]) => (
              <div key={n} style={{ display: 'flex', gap: 13, padding: '12px 0', borderBottom: n !== '3' ? `1px solid ${wLINE}` : 'none' }}>
                <div style={{ width: 30, height: 30, borderRadius: 9, background: wACC_DIM, color: wACC, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: wF.title, fontSize: 14, flexShrink: 0 }}>{n}</div>
                <div>
                  <div style={{ fontSize: 13.5, color: wINK, fontWeight: 600 }}>{t}</div>
                  <div style={{ fontSize: 12, color: wDIM, marginTop: 2, lineHeight: 1.45 }}>{d}</div>
                </div>
              </div>
            ))}
          </Panel>
        </div>
      </div>
    </>
  );
}

Object.assign(window, {
  StatBar, CompetitionsView, FavoritesView, HistoryView, StatsView,
  SubscriptionView, ProfileView, ReferralView,
});
