// COTA Web — vues (1/2) : Dashboard · Prédictions liste · Prédiction détail · Live.

const { BG: vBG, BG2: vBG2, BG3: vBG3, LINE: vLINE, LINE2: vLINE2, INK: vINK, INK2: vINK2, DIM: vDIM, DIM2: vDIM2, ACCENT: vACC, ACCENT_DIM: vACC_DIM, COOL: vCOOL, COOL_DIM: vCOOL_DIM, WIN: vWIN, LOSS: vLOSS, font: vF } = window.COTA;

// Petit indicateur de "value" (sobre).
function ValuePill({ level }) {
  const map = {
    'ÉLEVÉE':  { c: vACC,  b: vACC_DIM },
    'MOYENNE': { c: vCOOL, b: vCOOL_DIM },
    'FAIBLE':  { c: vDIM,  b: 'rgba(255,255,255,0.05)' },
  };
  const s = map[level] || map['FAIBLE'];
  return <Pill bg={s.b} color={s.c}>VALUE {level}</Pill>;
}

function GhostBtn({ children, onClick, accent }) {
  return (
    <button onClick={onClick} style={{
      display: 'inline-flex', alignItems: 'center', gap: 7, background: 'transparent',
      border: `1px solid ${accent ? vACC : vLINE2}`, color: accent ? vACC : vINK,
      borderRadius: 10, padding: '9px 15px', fontFamily: vF.ui, fontSize: 12.5, fontWeight: 600, cursor: 'pointer', whiteSpace: 'nowrap',
    }}>{children}</button>
  );
}

// ── Carte de prédiction (façon "property card" MaxLand) ────────────────────────
function PredictionCard({ pred, onOpen }) {
  const m = window.MATCHES.find(x => x.id === pred.matchId);
  const h = window.TEAMS[m.home], a = window.TEAMS[m.away];
  const isLive = pred.statut === 'live';
  return (
    <div className="wd-pcard" onClick={() => onOpen(pred.matchId)}>
      <div style={{ height: 150, position: 'relative' }}>
        <MatchBackdrop home={m.home} away={m.away}>
          <div style={{ position: 'absolute', top: 12, left: 12, right: 12, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Pill bg="rgba(11,13,16,0.62)" color={vINK} border={vLINE2}>{m.competition.toUpperCase()} · {m.round}</Pill>
            {isLive
              ? <span className="wd-badge" style={{ color: vLOSS, background: 'rgba(255,91,58,0.16)' }}><span className="wd-livedot" />LIVE {pred.live.minute}</span>
              : <Pill bg="rgba(11,13,16,0.62)" color={vINK2} border={vLINE2}>{pred.kickoff}</Pill>}
          </div>
          <div style={{ position: 'absolute', bottom: 14, left: 14, right: 14, display: 'flex', alignItems: 'center', gap: 12 }}>
            <TeamBadge code={h.code} color={h.color} text={h.text} size={34} />
            <div style={{ flex: 1, fontFamily: vF.title, fontSize: 19, color: vINK, letterSpacing: '-0.02em' }}>
              {h.short} <span style={{ color: vDIM2, fontSize: 13 }}>vs</span> {a.short}
            </div>
            <TeamBadge code={a.code} color={a.color} text={a.text} size={34} />
          </div>
        </MatchBackdrop>
      </div>
      <div style={{ padding: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <ConfidenceRing value={pred.conf} size={52} stroke={4.5} />
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{ fontFamily: vF.mono, fontSize: 9, color: vDIM, letterSpacing: '0.14em' }}>CONSEIL IA</div>
            <div style={{ fontSize: 14.5, color: vINK, fontWeight: 600, marginTop: 4 }}>{pred.pickType}</div>
            <div style={{ marginTop: 8 }}><ValuePill level={pred.value} /></div>
          </div>
          <div style={{ textAlign: 'right' }}>
            <OddsChip value={pred.odds} highlight={pred.conf >= 85} />
          </div>
        </div>
        <div className="wd-pcard-foot">
          <span style={{ fontFamily: vF.mono, fontSize: 10.5, color: vDIM, letterSpacing: '0.08em' }}>9 CRITÈRES ANALYSÉS</span>
          <span style={{ fontFamily: vF.mono, fontSize: 11, color: vACC, fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
            Voir l'analyse <WdIcon name="chevronR" size={13} color={vACC} />
          </span>
        </div>
      </div>
    </div>
  );
}

// ════════════════ DASHBOARD ════════════════
function DashboardView({ t, onNav }) {
  const PERIOD = { '7 j': 7, '14 j': 14, '28 j': 28 };
  const MODE = { 'Barres': 'bars', 'Aire': 'area', 'Ligne': 'line' };
  const accent = t.chartAccent || vACC;
  const days = PERIOD[t.period] || 28;
  const series = window.WD_SERIES.slice(-days);
  const toneMap = { ink: vINK2, mint: vWIN, lime: vACC };

  return (
    <>
      <Topbar user={window.WD_USER} />
      <FilterBar />
      <div className="wd-kpis">
        {window.WD_KPIS.map(k => <KpiCard key={k.id} {...k} />)}
      </div>

      <div className="wd-cols">
        <div className="wd-maincol">
          <div className="wd-chartrow">
            <Panel title="Évolution des gains" sub={`PROFIT NET · ${days} DERNIERS JOURS`}
              right={<Pill bg="rgba(61,220,145,0.12)" color={vWIN}>▲ ROI +18.5%</Pill>}>
              <PerfChart data={series} mode={MODE[t.chartStyle]} accent={accent} height={210} />
            </Panel>
            <Panel title="Résumé" sub="CE MOIS-CI">
              <SummaryBars items={window.WD_SUMMARY} max={window.WD_SUMMARY_MAX} toneMap={toneMap} />
              <div style={{ marginTop: 22, paddingTop: 18, borderTop: `1px solid ${vLINE}`, display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                <span style={{ fontFamily: vF.mono, fontSize: 10, color: vDIM, letterSpacing: '0.12em' }}>RENDEMENT</span>
                <span style={{ fontFamily: vF.title, fontSize: 24, color: vACC, letterSpacing: '-0.03em' }}>+52%</span>
              </div>
            </Panel>
          </div>

          <Panel title="À l'affiche aujourd'hui" sub="ANALYSÉS PAR L'IA"
            right={<span className="wd-morelink" onClick={() => onNav('predict')}>Toutes les prédictions →</span>}>
            <MatchCards />
          </Panel>

          <Panel title="Mes prédictions" sub="HISTORIQUE RÉCENT"
            right={<span className="wd-morelink" onClick={() => onNav('hist')}>Historique complet →</span>}>
            <HistoryTable />
          </Panel>
        </div>

        <div className="wd-rail">
          <Panel title="Avis récents" right={<span className="wd-morelink">Tout →</span>}>
            <ReviewList />
          </Panel>
          <Panel title="Top combinés" sub="CETTE SEMAINE">
            <TipsterList />
          </Panel>
        </div>
      </div>
    </>
  );
}

// ════════════════ PRÉDICTIONS (liste) ════════════════
function PredictionsView({ onOpen }) {
  return (
    <>
      <PageHeader kicker="MAR. 3 JUIN 2026" title="Prédictions du jour"
        desc="Chaque match est passé au crible de 9 critères par le modèle COTA. Le score de confiance résume la conviction de l'IA."
        right={<button className="wd-cta"><WdIcon name="bolt" size={14} color={vBG}/> Combiné du jour</button>} />
      <FilterBar />
      <div className="wd-pgrid">
        {window.WD_PREDICTIONS.map(p => <PredictionCard key={p.matchId} pred={p} onOpen={onOpen} />)}
      </div>
    </>
  );
}

// Génère 9 critères cohérents pour un match qui n'en a pas (utilise ses propres équipes).
function genCriteria(m) {
  if (m.criteria) return m.criteria;
  const h = window.TEAMS[m.home].short, a = window.TEAMS[m.away].short;
  const strong = m.confidence >= 75;
  return [
    { name: 'Forme (5 derniers)',       value: strong ? '4V 1N' : '2V 2N 1D', signal: strong ? 'pro' : 'neutral', detail: `${h} sur une bonne dynamique` },
    { name: 'Confrontations directes',  value: strong ? '6-2-2' : '4-3-3',     signal: strong ? 'pro' : 'neutral', detail: `Bilan des 10 derniers ${h}-${a}` },
    { name: 'Domicile vs Extérieur',    value: strong ? '82% V' : '61% V',     signal: strong ? 'pro' : 'neutral', detail: `${h} à domicile cette saison` },
    { name: 'Blessures clés',           value: '1 vs 2',  signal: 'pro',     detail: `${a} diminué dans l'entrejeu` },
    { name: 'Météo',                    value: 'Sec, 16°', signal: 'neutral' },
    { name: 'Indices du marché',        value: `${m.odds.home} / ${m.odds.draw} / ${m.odds.away}`, signal: 'neutral' },
    { name: 'Cartons (moy.)',           value: strong ? '2.4' : '3.1', signal: 'neutral', detail: `sur les 5 derniers ${h}` },
    { name: 'Possession attendue',      value: strong ? '58%' : '49%', signal: strong ? 'pro' : 'neutral' },
    { name: 'Buts attendus (xG)',       value: strong ? '2.1 - 1.0' : '1.4 - 1.3', signal: strong ? 'pro' : 'neutral' },
  ];
}

// ════════════════ PRÉDICTION (détail — 9 critères) ════════════════
function PredictionDetailView({ matchId, onBack }) {
  const m = window.MATCHES.find(x => x.id === matchId) || window.MATCHES[0];
  const h = window.TEAMS[m.home], a = window.TEAMS[m.away];
  const crit = genCriteria(m);
  const pros = crit.filter(c => c.signal === 'pro').length;
  // Probabilités déduites des cotes.
  const inv = { h: 1 / m.odds.home, d: 1 / m.odds.draw, a: 1 / m.odds.away };
  const sum = inv.h + inv.d + inv.a;
  const prob = { h: Math.round(inv.h / sum * 100), d: Math.round(inv.d / sum * 100), a: Math.round(inv.a / sum * 100) };

  return (
    <>
      <div style={{ marginBottom: 18 }}>
        <button className="wd-back" onClick={onBack}><span style={{ display: 'inline-flex', transform: 'rotate(90deg)' }}><WdIcon name="chevron" size={15} color={vDIM} /></span> Retour aux prédictions</button>
      </div>

      <div className="wd-detail">
        <div className="wd-detail-main">
          <Panel pad={0}>
            <div style={{ height: 190, position: 'relative', borderRadius: '16px 16px 0 0', overflow: 'hidden' }}>
              <MatchBackdrop home={m.home} away={m.away}>
                <div style={{ position: 'absolute', top: 16, left: 16 }}>
                  <Pill bg="rgba(11,13,16,0.62)" color={vINK} border={vLINE2}>{m.competition.toUpperCase()} · {m.round} · {m.date}</Pill>
                </div>
                <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 22 }}>
                  <div style={{ textAlign: 'center' }}>
                    <TeamBadge code={h.code} color={h.color} text={h.text} size={56} />
                    <div style={{ fontFamily: vF.title, fontSize: 15, color: vINK, marginTop: 8 }}>{h.short}</div>
                  </div>
                  <div style={{ fontFamily: vF.mono, fontSize: 13, color: vDIM, letterSpacing: '0.2em' }}>{m.kickoff}</div>
                  <div style={{ textAlign: 'center' }}>
                    <TeamBadge code={a.code} color={a.color} text={a.text} size={56} />
                    <div style={{ fontFamily: vF.title, fontSize: 15, color: vINK, marginTop: 8 }}>{a.short}</div>
                  </div>
                </div>
              </MatchBackdrop>
            </div>
            <div style={{ padding: 22 }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 18 }}>
                <div style={{ fontFamily: vF.title, fontSize: 17, color: vINK, letterSpacing: '-0.02em' }}>Les 9 critères du modèle</div>
                <Pill bg={vACC_DIM} color={vACC}>{pros}/9 EN FAVEUR</Pill>
              </div>
              {crit.map((c, i) => <CriterionRow key={i} index={i + 1} {...c} />)}
            </div>
          </Panel>
        </div>

        <div className="wd-detail-rail">
          <Panel title="Recommandation IA">
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 14, paddingBottom: 4 }}>
              <ConfidenceRing value={m.confidence} size={108} stroke={8} />
              <div style={{ textAlign: 'center' }}>
                <div style={{ fontFamily: vF.mono, fontSize: 10, color: vDIM, letterSpacing: '0.14em' }}>SÉLECTION CONSEILLÉE</div>
                <div style={{ fontFamily: vF.title, fontSize: 20, color: vINK, letterSpacing: '-0.02em', marginTop: 6 }}>{m.pick.type}</div>
              </div>
              <OddsChip value={m.pick.odds} highlight />
            </div>
          </Panel>

          <Panel title="Probabilités" sub="DÉDUITES DU MARCHÉ">
            <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
              <ConfidenceBar label={`1 · ${h.short}`} value={prob.h} color={vACC} />
              <ConfidenceBar label="N · Nul" value={prob.d} color={vINK} />
              <ConfidenceBar label={`2 · ${a.short}`} value={prob.a} color={vCOOL} />
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 18, paddingTop: 16, borderTop: `1px solid ${vLINE}` }}>
              {[['1', m.odds.home], ['N', m.odds.draw], ['2', m.odds.away]].map(([k, v]) => (
                <div key={k} style={{ textAlign: 'center' }}>
                  <div style={{ fontFamily: vF.mono, fontSize: 10, color: vDIM }}>{k}</div>
                  <div style={{ fontFamily: vF.mono, fontSize: 15, fontWeight: 700, color: vINK, marginTop: 4 }}>{v}</div>
                </div>
              ))}
            </div>
          </Panel>

          <button className="wd-cta wd-cta-block"><WdIcon name="check" size={15} color={vBG}/> Ajouter au combiné</button>
        </div>
      </div>
    </>
  );
}

// ════════════════ LIVE ════════════════
function LiveView({ onOpen }) {
  return (
    <>
      <PageHeader kicker="EN DIRECT" title="Matchs en live"
        desc="Suivez l'évolution de vos prédictions minute par minute, avec l'indice de momentum du modèle."
        right={<Pill bg="rgba(255,91,58,0.16)" color={vLOSS}><span className="wd-livedot" />{window.WD_LIVE.length} EN COURS</Pill>} />
      <div className="wd-livegrid">
        {window.WD_LIVE.map(L => {
          const m = window.MATCHES.find(x => x.id === L.matchId);
          const h = window.TEAMS[m.home], a = window.TEAMS[m.away];
          const tone = L.statusTone === 'win' ? vWIN : L.statusTone === 'loss' ? vLOSS : vCOOL;
          return (
            <div key={L.matchId} className="wd-panel wd-livecard" onClick={() => onOpen(L.matchId)}>
              <div style={{ padding: '16px 18px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: `1px solid ${vLINE}` }}>
                <Pill bg="rgba(255,91,58,0.16)" color={vLOSS}><span className="wd-livedot" />{L.minute} · {L.period}</Pill>
                <span style={{ fontFamily: vF.mono, fontSize: 10, color: vDIM, letterSpacing: '0.1em' }}>{m.competition.toUpperCase()}</span>
              </div>
              <div style={{ padding: '20px 18px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 18 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <TeamBadge code={h.code} color={h.color} text={h.text} size={36} />
                  <span style={{ fontFamily: vF.title, fontSize: 16, color: vINK }}>{h.short}</span>
                </div>
                <div style={{ fontFamily: vF.title, fontSize: 30, color: vINK, letterSpacing: '-0.03em' }}>{L.score}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <span style={{ fontFamily: vF.title, fontSize: 16, color: vINK }}>{a.short}</span>
                  <TeamBadge code={a.code} color={a.color} text={a.text} size={36} />
                </div>
              </div>
              <div style={{ padding: '0 18px 18px' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
                  <div>
                    <div style={{ fontFamily: vF.mono, fontSize: 9, color: vDIM, letterSpacing: '0.14em', lineHeight: 1.4 }}>VOTRE PRÉDICTION</div>
                    <div style={{ fontSize: 13.5, color: vINK, fontWeight: 600, marginTop: 4 }}>{L.pickType}</div>
                  </div>
                  <span className="wd-badge" style={{ color: tone, background: 'rgba(255,255,255,0.04)', whiteSpace: 'nowrap' }}>{L.status}</span>
                </div>
                <div style={{ fontFamily: vF.mono, fontSize: 9, color: vDIM, letterSpacing: '0.14em', marginBottom: 6 }}>MOMENTUM {h.short}</div>
                <div style={{ height: 8, borderRadius: 4, overflow: 'hidden', display: 'flex', background: vBG3 }}>
                  <div style={{ width: `${L.momentum}%`, background: vACC }} />
                  <div style={{ flex: 1, background: vCOOL, opacity: 0.5 }} />
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </>
  );
}

Object.assign(window, { ValuePill, GhostBtn, PredictionCard, DashboardView, PredictionsView, PredictionDetailView, LiveView });
