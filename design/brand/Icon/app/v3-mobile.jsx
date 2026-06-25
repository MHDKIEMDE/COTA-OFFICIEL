// COTA V3 — mobile screens (sober DAZN × Athletic dark).
// 5 screens: Profil, Recherche, Équipe, Ligue, Wallet.

// ─────────────────────────────────────────────────────────────────────────────
// 1. PROFIL DÉTAILLÉ — édition compte
// ─────────────────────────────────────────────────────────────────────────────
function V3Profile() {
  return (
    <V3Screen>
      <V3Header title="Profil" back={true} right={<span style={{ fontSize: 13, color: V3.INK2, fontWeight: 500 }}>Modifier</span>} />

      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 100 }}>
        {/* Photo + identity */}
        <div style={{ padding: '12px 22px 28px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 14 }}>
          <V3Avatar name="Karim B." size={88} accent={false} />
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 22, fontWeight: 500, letterSpacing: '-0.01em', color: V3.INK }}>Karim Bouchareb</div>
            <div style={{ fontSize: 13, color: V3.DIM, marginTop: 4 }}>Membre depuis novembre 2025</div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '5px 12px', background: 'rgba(232,255,54,0.08)', borderRadius: 999 }}>
            <span style={{ width: 6, height: 6, borderRadius: 3, background: V3.ACCENT }} />
            <span style={{ fontSize: 11, color: V3.ACCENT, fontWeight: 500 }}>Premium</span>
          </div>
        </div>

        {/* Quick stats */}
        <div style={{ padding: '0 22px 8px', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 0 }}>
          {[['47', 'coupons'], ['72%', 'réussite'], ['+€184', 'gain net']].map(([n, l], i) => (
            <div key={l} style={{ textAlign: 'center', padding: '14px 0', borderRight: i < 2 ? `1px solid ${V3.LINE}` : 'none' }}>
              <div style={{ fontSize: 20, fontWeight: 500, color: V3.INK, letterSpacing: '-0.01em' }}>{n}</div>
              <div style={{ fontSize: 11, color: V3.DIM, marginTop: 4 }}>{l}</div>
            </div>
          ))}
        </div>

        <V3Section label="Compte">
          <V3Row label="Prénom"    value="Karim" />
          <V3Row label="Nom"       value="Bouchareb" />
          <V3Row label="Téléphone" value="+226 70 12 34 56" />
          <V3Row label="Email"     value="karim@cota.app" />
          <V3Row label="Pays"      value="Burkina Faso" />
        </V3Section>

        <V3Section label="Préférences">
          <V3Row label="Ligues suivies"     value="6 ligues" />
          <V3Row label="Niveau de risque"   value="Équilibré" />
          <V3Row label="Heure du coupon"    value="09:30" />
          <V3Row label="Langue"             value="Français" />
        </V3Section>

        <V3Section label="Compte">
          <V3Row label="Sécurité & connexion" />
          <V3Row label="Méthodes de paiement" value="Wave" />
          <V3Row label="Confidentialité" />
          <V3Row label="Se déconnecter" danger chevron={false} />
        </V3Section>
      </div>

      <V3BottomNav active={3} />
    </V3Screen>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. RECHERCHE / DÉCOUVERTE
// ─────────────────────────────────────────────────────────────────────────────
function V3Search() {
  return (
    <V3Screen>
      <div style={{ padding: '52px 22px 8px' }}>
        <div style={{ fontSize: 22, fontWeight: 500, letterSpacing: '-0.01em' }}>Recherche</div>
        {/* Search field */}
        <div style={{ marginTop: 16, padding: '12px 14px', background: V3.BG2, borderRadius: 12, display: 'flex', alignItems: 'center', gap: 10 }}>
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
            <circle cx="7" cy="7" r="5" stroke={V3.DIM} strokeWidth="1.5"/>
            <path d="M11 11 L14.5 14.5" stroke={V3.DIM} strokeWidth="1.5" strokeLinecap="round"/>
          </svg>
          <span style={{ flex: 1, fontSize: 14, color: V3.DIM }}>Équipe, match, compétition…</span>
          <span style={{ fontSize: 11, color: V3.DIM, padding: '3px 8px', border: `1px solid ${V3.LINE2}`, borderRadius: 6 }}>⌘K</span>
        </div>
      </div>

      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 100 }}>
        {/* Filter chips */}
        <div style={{ padding: '14px 22px', display: 'flex', gap: 8, overflowX: 'auto' }}>
          {['Tout', 'Équipes', 'Compétitions', 'Matchs', 'Joueurs'].map((t, i) => (
            <V3Chip key={t} label={t} on={i === 0} />
          ))}
        </div>

        <V3Section label="Récentes">
          <V3Row leading={<V3Avatar name="PSG" size={32} />} label="Paris Saint-Germain" sub="Ligue 1 · France" />
          <V3Row leading={<V3Avatar name="OM" size={32} />}  label="Olympique de Marseille" sub="Ligue 1 · France" />
          <V3Row leading={<div style={{ width: 32, height: 32, borderRadius: 16, background: V3.BG3, color: V3.INK2, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12 }}>UCL</div>} label="Champions League" sub="Compétition · Europe" />
        </V3Section>

        <V3Section label="Suggéré pour toi">
          <V3Row leading={<V3Avatar name="RM" size={32} />}  label="Real Madrid" sub="La Liga · Espagne" />
          <V3Row leading={<V3Avatar name="LIV" size={32} />} label="Liverpool" sub="Premier League · Angleterre" />
          <V3Row leading={<V3Avatar name="BA" size={32} />}  label="Bayern Munich" sub="Bundesliga · Allemagne" />
          <V3Row leading={<V3Avatar name="MC" size={32} />}  label="Manchester City" sub="Premier League · Angleterre" />
        </V3Section>

        <V3Section label="Tendances aujourd'hui">
          <V3Row label="PSG – OM"   sub="Ligue 1 · 21:00" value="@1.65" />
          <V3Row label="Real – Bayern" sub="UCL · demi-finale · 21:00" value="@1.95" />
          <V3Row label="Liverpool – Arsenal" sub="Premier League · 18:30" value="@2.30" />
        </V3Section>
      </div>

      <V3BottomNav active={2} />
    </V3Screen>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. PAGE ÉQUIPE — PSG fiche saison
// ─────────────────────────────────────────────────────────────────────────────
function V3Team() {
  return (
    <V3Screen>
      <V3Header back={true} subtitle="Ligue 1" title="Paris Saint-Germain" right={<span style={{ fontSize: 13, color: V3.INK2, fontWeight: 500 }}>Suivre</span>} />

      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 100 }}>
        {/* Crest + name (sober — no full-bleed) */}
        <div style={{ padding: '4px 22px 22px', display: 'flex', alignItems: 'center', gap: 16 }}>
          <V3Avatar name="PSG" size={64} />
          <div>
            <div style={{ fontSize: 13, color: V3.DIM }}>Position</div>
            <div style={{ fontSize: 28, fontWeight: 500, letterSpacing: '-0.02em', color: V3.INK }}>1<span style={{ color: V3.DIM, fontSize: 16, marginLeft: 6 }}>· 87 pts</span></div>
          </div>
        </div>

        <V3Tabs items={['Aperçu', 'Calendrier', 'Effectif', 'Stats']} active={0} />

        {/* Form */}
        <V3Section label="Forme · 5 derniers">
          <div style={{ display: 'flex', gap: 10, alignItems: 'center', padding: '8px 0' }}>
            {['V', 'V', 'V', 'N', 'V'].map((r, i) => (
              <div key={i} style={{
                width: 30, height: 30, borderRadius: 6,
                background: r === 'V' ? 'rgba(90,210,154,0.12)' : r === 'N' ? V3.BG2 : 'rgba(240,109,78,0.12)',
                color: r === 'V' ? V3.WIN : r === 'N' ? V3.DIM : V3.LOSS,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: 12, fontWeight: 600,
              }}>{r}</div>
            ))}
            <div style={{ marginLeft: 'auto', fontSize: 12, color: V3.DIM }}>4V · 1N</div>
          </div>
        </V3Section>

        {/* Stats key */}
        <V3Section label="Saison 2025-26">
          <V3Row label="Buts marqués"    value="68" />
          <V3Row label="Buts encaissés"  value="22" />
          <V3Row label="Clean sheets"    value="14" />
          <V3Row label="xG moyenne"      value="2.4" />
          <V3Row label="Possession moy."  value="63%" />
          <V3Row label="Domicile"        value="14V · 2N · 1D" />
          <V3Row label="Extérieur"       value="11V · 4N · 2D" />
        </V3Section>

        {/* Prochains matches */}
        <V3Section label="Prochains matchs" action="Tout voir →">
          {[
            { vs: 'OM',  date: 'Mar 18 mai', time: '21:00', loc: 'Domicile' },
            { vs: 'OL',  date: 'Sam 22 mai', time: '17:00', loc: 'Extérieur' },
            { vs: 'LIL', date: 'Mer 26 mai', time: '21:00', loc: 'Domicile' },
          ].map(m => (
            <V3Row key={m.vs} leading={<V3Avatar name={m.vs} size={32} />} label={`vs ${m.vs}`} sub={`${m.date} · ${m.time} · ${m.loc}`} />
          ))}
        </V3Section>
      </div>

      <V3BottomNav active={0} />
    </V3Screen>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. PAGE LIGUE — Ligue 1 classement
// ─────────────────────────────────────────────────────────────────────────────
function V3League() {
  const table = [
    ['1',  'Paris Saint-Germain', 34, '24-9-1',  '87', true],
    ['2',  'Monaco',               34, '20-8-6',  '68'],
    ['3',  'Lille',                34, '19-9-6',  '66'],
    ['4',  'Marseille',            34, '18-8-8',  '62'],
    ['5',  'Lyon',                 34, '16-10-8', '58'],
    ['6',  'Nice',                 34, '15-11-8', '56'],
    ['7',  'Rennes',               34, '14-10-10','52'],
    ['8',  'Lens',                 34, '13-10-11','49'],
    ['9',  'Strasbourg',           34, '12-11-11','47'],
    ['10', 'Reims',                34, '11-12-11','45'],
  ];
  return (
    <V3Screen>
      <V3Header back={true} subtitle="France" title="Ligue 1" right={<span style={{ fontSize: 13, color: V3.INK2, fontWeight: 500 }}>Suivre</span>} />

      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 100 }}>
        <V3Tabs items={['Classement', 'Calendrier', 'Buteurs', 'Stats']} active={0} />

        {/* Season selector */}
        <div style={{ padding: '14px 22px', display: 'flex', gap: 8, overflowX: 'auto' }}>
          {['2025-26', '2024-25', '2023-24'].map((s, i) => (
            <V3Chip key={s} label={s} on={i === 0} />
          ))}
        </div>

        {/* Table */}
        <div style={{ padding: '0 22px' }}>
          {/* Header row */}
          <div style={{ display: 'grid', gridTemplateColumns: '24px 1fr 36px 70px 40px', gap: 12, padding: '10px 0', fontSize: 11, color: V3.DIM, fontWeight: 600 }}>
            <span>#</span>
            <span>ÉQUIPE</span>
            <span style={{ textAlign: 'right' }}>J</span>
            <span style={{ textAlign: 'right' }}>V-N-D</span>
            <span style={{ textAlign: 'right' }}>PTS</span>
          </div>
          {table.map(([r, n, j, vnd, pts, hl]) => (
            <div key={n} style={{
              display: 'grid', gridTemplateColumns: '24px 1fr 36px 70px 40px',
              gap: 12, padding: '12px 0', alignItems: 'center',
              borderTop: `1px solid ${V3.LINE}`,
              background: hl ? 'rgba(232,255,54,0.04)' : 'transparent',
              borderRadius: hl ? 6 : 0,
              marginLeft: hl ? -6 : 0, marginRight: hl ? -6 : 0, paddingLeft: hl ? 6 : 0, paddingRight: hl ? 6 : 0,
            }}>
              <span style={{ fontSize: 12, color: V3.DIM }}>{r}</span>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <V3Avatar name={n.split(' ').slice(0, 2).map(w => w[0]).join('')} size={22} />
                <span style={{ fontSize: 13, color: V3.INK, fontWeight: hl ? 500 : 400 }}>{n}</span>
              </div>
              <span style={{ textAlign: 'right', fontSize: 12, color: V3.DIM }}>{j}</span>
              <span style={{ textAlign: 'right', fontFamily: V3.font.mono, fontSize: 11, color: V3.DIM }}>{vnd}</span>
              <span style={{ textAlign: 'right', fontSize: 14, color: V3.INK, fontWeight: 500 }}>{pts}</span>
            </div>
          ))}
        </div>

        {/* Top scorers preview */}
        <V3Section label="Meilleurs buteurs" action="Voir tout →">
          {[
            ['Mbappé',     'PSG', 24],
            ['Aubameyang', 'OM',  18],
            ['Lacazette',  'OL',  16],
          ].map(([p, t, g]) => (
            <V3Row key={p} leading={<V3Avatar name={p.split('')[0]} size={28} />} label={p} sub={t} value={`${g} buts`} chevron={false} />
          ))}
        </V3Section>
      </div>

      <V3BottomNav active={0} />
    </V3Screen>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. WALLET / PAIEMENTS
// ─────────────────────────────────────────────────────────────────────────────
function V3Wallet() {
  return (
    <V3Screen>
      <V3Header back={true} title="Portefeuille" />

      <div style={{ height: '100%', overflowY: 'auto', paddingBottom: 100 }}>
        {/* Balance hero — sober */}
        <div style={{ padding: '8px 22px 22px' }}>
          <div style={{ fontSize: 12, color: V3.DIM, fontWeight: 500, marginBottom: 6 }}>Solde disponible</div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
            <V3Hero value="184" />
            <span style={{ fontSize: 16, color: V3.DIM, fontWeight: 400 }}>,20 €</span>
          </div>
          <div style={{ fontSize: 12, color: V3.WIN, marginTop: 4 }}>+44,20 € cette semaine</div>
        </div>

        {/* Actions */}
        <div style={{ padding: '0 22px 6px', display: 'flex', gap: 10 }}>
          <V3Button primary>Retirer</V3Button>
          <V3Button>Ajouter</V3Button>
        </div>

        <V3Section label="Méthodes de paiement" action="Ajouter →">
          <V3Row leading={<MethodBadge label="W" color="#1e88e5" />} label="Wave" sub="•••• 4521 · Par défaut" />
          <V3Row leading={<MethodBadge label="O" color="#ff6900" />} label="Orange Money" sub="+226 70 12 34 56" />
        </V3Section>

        <V3Section label="Transactions" action="Filtrer →">
          {[
            { type: 'Coupon gagné', date: 'Aujourd\'hui · 22:48', amount: '+44,20 €', kind: 'in', sub: 'Coupon du 18 mai · 3/3 picks' },
            { type: 'Mise',         date: 'Aujourd\'hui · 09:31', amount: '-10,00 €',  kind: 'out', sub: 'Coupon du 18 mai' },
            { type: 'Coupon gagné', date: 'Hier · 22:30',         amount: '+31,20 €', kind: 'in',  sub: 'Coupon du 17 mai · 3/3 picks' },
            { type: 'Mise',         date: 'Hier · 09:30',         amount: '-10,00 €', kind: 'out', sub: 'Coupon du 17 mai' },
            { type: 'Premium',       date: '12 mai',               amount: '-7,60 €',  kind: 'out', sub: 'Abonnement mensuel' },
            { type: 'Retrait Wave', date: '10 mai',                amount: '-50,00 €', kind: 'out', sub: 'Vers •••• 4521' },
            { type: 'Coupon perdu', date: '16 mai · 23:10',        amount: '-10,00 €', kind: 'out', sub: 'Coupon du 16 mai' },
          ].map((t, i) => (
            <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '14px 0', borderBottom: `1px solid ${V3.LINE}` }}>
              <div style={{ width: 32, height: 32, borderRadius: 8, background: V3.BG2, color: t.kind === 'in' ? V3.WIN : V3.DIM, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                  {t.kind === 'in'
                    ? <path d="M6.5 11 V2 M3 5.5 L6.5 2 L10 5.5" stroke={V3.WIN} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    : <path d="M6.5 2 V11 M3 7.5 L6.5 11 L10 7.5" stroke={V3.DIM} strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                  }
                </svg>
              </div>
              <div style={{ flex: 1 }}>
                <div style={{ fontSize: 13, color: V3.INK, fontWeight: 500 }}>{t.type}</div>
                <div style={{ fontSize: 11, color: V3.DIM, marginTop: 2 }}>{t.sub} · {t.date}</div>
              </div>
              <span style={{ fontFamily: V3.font.mono, fontSize: 13, color: t.kind === 'in' ? V3.WIN : V3.INK, fontWeight: 500 }}>{t.amount}</span>
            </div>
          ))}
        </V3Section>
      </div>

      <V3BottomNav active={3} />
    </V3Screen>
  );
}

function MethodBadge({ label, color }) {
  return (
    <div style={{ width: 32, height: 32, borderRadius: 8, background: color, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 13, fontWeight: 600 }}>{label}</div>
  );
}

Object.assign(window, { V3Profile, V3Search, V3Team, V3League, V3Wallet });
