// COTA V5 — admin pages, MaxLand-inspired.

// ── D1 · Overview (recap dashboard) ──────────────────────────────────────────
function V5Overview() {
  return (
    <V5Frame
      active="dashboard"
      title="Bienvenue sur votre profil"
      actions={(<>
        <V5Button variant="ghost">↓ Export CSV</V5Button>
        <V5Button variant="primary">+ Publier le coupon</V5Button>
      </>)}
    >
      {/* KPI row */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 28 }}>
        <V5KPICard value="471"     label="Coupons publiés"   color={V5.WIN}    bg={V5.WIN_BG}    icon="≡" />
        <V5KPICard value="56"      label="En attente"         color={V5.BLUE}   bg="#e7eefe"      icon="◐" />
        <V5KPICard value="37"      label="Picks favoris"      color={V5.ORANGE} bg={V5.ORANGE_BG} icon="♥" />
        <V5KPICard value="27"      label="Avis utilisateurs"  color={V5.ROSE}   bg={V5.ROSE_BG}   icon="★" />
      </div>

      {/* Chart + Summary */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr', gap: 18, marginBottom: 28 }}>
        {/* Chart */}
        <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 24, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 18 }}>
            <h3 style={{ fontFamily: V5.font.serif, fontSize: 18, color: V5.INK, margin: 0, fontWeight: 600 }}>Picks · 24 derniers jours</h3>
            <span style={{ fontSize: 11, color: V5.DIM, fontFamily: V5.font.mono, letterSpacing: '0.08em' }}>MAI 2026</span>
          </div>
          <V5BarChart />
        </div>

        {/* Summary bars */}
        <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 24, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
          <h3 style={{ fontFamily: V5.font.serif, fontSize: 18, color: V5.INK, margin: '0 0 22px', fontWeight: 600 }}>Récap performance</h3>
          {[
            ['Revenus',  '€18 240', 90, V5.ORANGE],
            ['Profits',  '€12 480', 68, V5.ORANGE],
            ['Dépenses', '€ 5 760', 32, V5.ORANGE],
          ].map(([l, v, p, c]) => (
            <div key={l} style={{ marginBottom: 22 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                <span style={{ fontSize: 13, color: V5.INK2, fontWeight: 500 }}>{l}</span>
                <span style={{ fontFamily: V5.font.mono, fontSize: 13, color: V5.INK, fontWeight: 600 }}>{v}</span>
              </div>
              <div style={{ height: 6, background: V5.LINE, borderRadius: 3, overflow: 'hidden' }}>
                <div style={{ height: '100%', width: `${p}%`, background: c, borderRadius: 3 }} />
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Coupons table */}
      <div style={{ marginBottom: 28 }}>
        <V5Table
          columns={[
            { label: 'Image',    w: '90px' },
            { label: 'Détails',  w: '2fr' },
            { label: 'Cote',     w: '110px' },
            { label: 'Statut',   w: '130px' },
            { label: 'Action',   w: '140px' },
          ]}
          rows={[
            [
              <CouponThumb kind="psg" />,
              <CouponDetails title="PSG – Olympique de Marseille" date="Posté le 18 mai 2026" stars={5} reviews={24} />,
              <span style={{ fontFamily: V5.font.mono, fontSize: 16, fontWeight: 700 }}>@4.55</span>,
              <V5Pill label="Approuvé" color={V5.WIN}  bg={V5.WIN_BG} />,
              <RowEdit />,
            ],
            [
              <CouponThumb kind="liv" />,
              <CouponDetails title="Liverpool – Arsenal" date="Posté le 18 mai 2026" stars={4} reviews={58} />,
              <span style={{ fontFamily: V5.font.mono, fontSize: 16, fontWeight: 700 }}>@3.12</span>,
              <V5Pill label="En attente" color={V5.ROSE} bg={V5.ROSE_BG} />,
              <RowEdit />,
            ],
            [
              <CouponThumb kind="rma" />,
              <CouponDetails title="Real Madrid – Bayern" date="Posté le 18 mai 2026" stars={5} reviews={146} />,
              <span style={{ fontFamily: V5.font.mono, fontSize: 16, fontWeight: 700 }}>@5.10</span>,
              <V5Pill label="Approuvé" color={V5.WIN}  bg={V5.WIN_BG} />,
              <RowEdit />,
            ],
            [
              <CouponThumb kind="asm" />,
              <CouponDetails title="Monaco – Lyon" date="Posté le 17 mai 2026" stars={5} reviews={32} />,
              <span style={{ fontFamily: V5.font.mono, fontSize: 16, fontWeight: 700 }}>@2.40</span>,
              <V5Pill label="Vente" color={V5.ORANGE}  bg={V5.ORANGE_BG} />,
              <RowEdit />,
            ],
          ]}
        />
      </div>

      {/* Recent reviews */}
      <div>
        <h2 style={{ fontFamily: V5.font.serif, fontSize: 22, color: V5.INK, margin: '0 0 18px', fontWeight: 600 }}>Avis récents</h2>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 16 }}>
          {[
            { name: 'Elon Gated', date: '24 février 2026', stars: 5, body: 'Coupon validé du premier coup, +44 € en une soirée. Le score de confiance à 87% s\'est confirmé exactement.' },
            { name: 'Aminata D.',  date: '23 février 2026', stars: 5, body: 'L\'analyse des 9 critères est nette. Pas besoin de chercher ailleurs, tout est là à 9h30 chaque matin.' },
          ].map(r => (
            <div key={r.name} style={{ background: V5.CARD_BG, borderRadius: 10, padding: 22, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 12 }}>
                <V5Avatar name={r.name} size={42} />
                <div>
                  <div style={{ fontFamily: V5.font.serif, fontSize: 17, fontWeight: 600 }}>{r.name}</div>
                  <V5Stars value={r.stars} />
                </div>
                <div style={{ marginLeft: 'auto', fontSize: 11, color: V5.DIM, fontFamily: V5.font.mono }}>{r.date}</div>
              </div>
              <p style={{ fontSize: 14, color: V5.INK2, lineHeight: 1.55, margin: 0 }}>{r.body}</p>
            </div>
          ))}
        </div>
      </div>
    </V5Frame>
  );
}

function V5BarChart() {
  const data = [88, 102, 96, 105, 64, 58, 78, 84, 95, 110, 98, 72, 86, 102, 96, 95, 88, 76, 110, 105, 92, 88, 84, 72];
  const max = 115;
  return (
    <svg width="100%" height="220" viewBox="0 0 600 220" preserveAspectRatio="none">
      {[28.7, 43.1, 57.5, 71.8, 86.2, 100.6, 115].map((v, i) => (
        <line key={v} x1="20" x2="600" y1={200 - (v / max) * 180} y2={200 - (v / max) * 180} stroke={V5.LINE} strokeDasharray="3 4" />
      ))}
      {data.map((v, i) => {
        const x = 26 + i * 23;
        const h = (v / max) * 180;
        return (
          <g key={i}>
            <rect x={x} y={200 - h} width={15} height={h} rx="2" fill="#7ec5a8" />
            <text x={x + 7.5} y={216} textAnchor="middle" fontSize="9" fill={V5.DIM} fontFamily={V5.font.mono}>{String(i + 1).padStart(2, '0')}</text>
          </g>
        );
      })}
      {/* Y-axis labels */}
      {[28.7, 43.1, 57.5, 71.8, 86.2, 100.6, 115].map((v, i) => (
        <text key={v} x="0" y={200 - (v / max) * 180 + 3} fontSize="9" fill={V5.DIM} fontFamily={V5.font.mono}>{v}</text>
      ))}
    </svg>
  );
}

function CouponThumb({ kind }) {
  const colors = { psg: '#0a3b73', liv: '#c8102e', rma: '#fffdf7', asm: '#e2001a' };
  const labels = { psg: 'PSG', liv: 'LIV', rma: 'RMA', asm: 'ASM' };
  return (
    <div style={{ width: 74, height: 56, borderRadius: 6, overflow: 'hidden', background: colors[kind], display: 'flex', alignItems: 'center', justifyContent: 'center', color: kind === 'rma' ? V5.INK : '#fff', fontFamily: V5.font.serif, fontSize: 15, fontWeight: 700 }}>
      {labels[kind]}
    </div>
  );
}

function CouponDetails({ title, date, stars, reviews }) {
  return (
    <div>
      <div style={{ fontFamily: V5.font.serif, fontSize: 16, color: V5.INK, fontWeight: 600 }}>{title}</div>
      <div style={{ fontSize: 12, color: V5.DIM, marginTop: 4 }}>{date}</div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginTop: 6 }}>
        <V5Stars value={stars} size={12} />
        <span style={{ fontSize: 11, color: V5.DIM }}>({reviews} avis)</span>
      </div>
    </div>
  );
}

function RowEdit() {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: 13, color: V5.INK2, cursor: 'pointer' }}>
        <svg width="11" height="11" viewBox="0 0 12 12"><path d="M1 11 V8.5 L8 1.5 L10.5 4 L3.5 11 Z M7 2.5 L9.5 5" stroke={V5.INK2} strokeWidth="0.9" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
        Modifier
      </span>
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: 13, color: V5.INK2, cursor: 'pointer' }}>
        <svg width="11" height="11" viewBox="0 0 12 12"><path d="M2 3 L10 3 M4 3 V11 L8 11 V3 M5 5 V9 M7 5 V9 M4 3 V1.5 H8 V3" stroke={V5.INK2} strokeWidth="0.9" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
        Supprimer
      </span>
    </div>
  );
}

// ── Properties · "Mes coupons" (style MaxLand list) ─────────────────────────
function V5Properties() {
  const rows = [
    { kind: 'psg', title: 'PSG – Olympique de Marseille', date: '18 mai 2026', price: '@4.55', purpose: 'won', stars: 5, reviews: 24 },
    { kind: 'liv', title: 'Liverpool – Arsenal',          date: '18 mai 2026', price: '@3.12', purpose: 'pending', stars: 4, reviews: 58 },
    { kind: 'rma', title: 'Real Madrid – Bayern',         date: '17 mai 2026', price: '@5.10', purpose: 'won', stars: 5, reviews: 146 },
    { kind: 'asm', title: 'Monaco – Lyon',                date: '17 mai 2026', price: '@2.40', purpose: 'sale', stars: 5, reviews: 32 },
    { kind: 'psg', title: 'PSG – Lille',                  date: '16 mai 2026', price: '@1.92', purpose: 'won', stars: 5, reviews: 89 },
    { kind: 'liv', title: 'Liverpool – Man City',          date: '15 mai 2026', price: '@6.80', purpose: 'sale', stars: 5, reviews: 24 },
  ];
  const purposeBadge = {
    won:     <V5Pill label="Gagné"     color={V5.WIN}    bg={V5.WIN_BG} />,
    pending: <V5Pill label="En attente" color={V5.ROSE}   bg={V5.ROSE_BG} />,
    sale:    <V5Pill label="Vente"     color={V5.ORANGE} bg={V5.ORANGE_BG} />,
    rent:    <V5Pill label="Location"  color={V5.WIN}    bg={V5.WIN_BG} />,
  };

  return (
    <V5Frame
      active="coupons"
      title="Mes coupons"
      actions={<V5Button variant="primary">+ Ajouter un coupon</V5Button>}
    >
      <V5Table
        columns={[
          { label: 'Image',   w: '110px' },
          { label: 'Détails', w: '2fr' },
          { label: 'Cote',    w: '110px' },
          { label: 'Statut',  w: '140px' },
          { label: 'Action',  w: '140px' },
        ]}
        rows={rows.map(r => [
          <CouponThumb kind={r.kind} />,
          <CouponDetails title={r.title} date={`Posté le ${r.date}`} stars={r.stars} reviews={r.reviews} />,
          <span style={{ fontFamily: V5.font.mono, fontSize: 16, fontWeight: 700 }}>{r.price}</span>,
          purposeBadge[r.purpose],
          <RowEdit />,
        ])}
      />
    </V5Frame>
  );
}

// ── Pricing plan (3 cards style MaxLand) ─────────────────────────────────────
function V5Pricing() {
  const plans = [
    { name: 'Basic',        price: '259', desc: 'Pour découvrir les coupons IA, 30 jours.',
      features: ['Tous appareils', '20 coupons par mois', '30 jours d\'accès', 'Coupons premium', 'Support 24/7'] },
    { name: 'Professional', price: '699', desc: 'Pour les passionnés de pronostics, 60 jours.',
      features: ['Tous appareils', '30 coupons par mois', '60 jours d\'accès', 'Coupons premium', 'Support 24/7'], featured: true },
    { name: 'Business',     price: '999', desc: 'Volume max pour les tipsters expérimentés.',
      features: ['Tous appareils', '40 coupons par mois', '90 jours d\'accès', 'Coupons premium', 'Support 24/7'] },
  ];
  return (
    <V5Frame
      active="subscriptions"
      title="Plans tarifaires"
      actions={null}
    >
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 22 }}>
        {plans.map(p => (
          <div key={p.name} style={{
            background: V5.CARD_BG, borderRadius: 12,
            padding: 32,
            boxShadow: p.featured ? '0 12px 32px rgba(37,99,235,0.10)' : '0 1px 2px rgba(0,0,0,0.04)',
            border: p.featured ? `2px solid ${V5.BLUE}` : `1px solid ${V5.LINE}`,
            position: 'relative',
          }}>
            {p.featured && (
              <div style={{ position: 'absolute', top: -12, right: 22, padding: '4px 12px', background: V5.BLUE, color: '#fff', borderRadius: 4, fontSize: 10, fontWeight: 700, letterSpacing: '0.08em' }}>POPULAIRE</div>
            )}
            <h3 style={{ fontFamily: V5.font.serif, fontSize: 22, color: V5.INK, fontWeight: 600, margin: 0 }}>{p.name}</h3>
            <p style={{ fontSize: 13, color: V5.DIM, marginTop: 8, lineHeight: 1.5 }}>{p.desc}</p>

            <div style={{ marginTop: 22, paddingBottom: 22, borderBottom: `1px solid ${V5.LINE}` }}>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 4 }}>
                <span style={{ fontFamily: V5.font.serif, fontSize: 48, color: V5.INK, fontWeight: 700, letterSpacing: '-0.02em' }}>${p.price}</span>
                <span style={{ fontSize: 13, color: V5.DIM }}>.00</span>
              </div>
            </div>

            <div style={{ marginTop: 22, marginBottom: 28 }}>
              {p.features.map(f => (
                <div key={f} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 0' }}>
                  <span style={{ width: 18, height: 18, borderRadius: 9, background: V5.WIN, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                    <svg width="10" height="10" viewBox="0 0 10 10"><path d="M2 5 L4 7 L8 3" stroke="#fff" strokeWidth="1.6" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
                  </span>
                  <span style={{ fontSize: 13, color: V5.INK2 }}>{f}</span>
                </div>
              ))}
            </div>

            <V5Button variant={p.featured ? 'primary' : 'ghost'} full>Choisir ce plan</V5Button>
          </div>
        ))}
      </div>
    </V5Frame>
  );
}

// ── Profile page (MaxLand-style personal info + update form) ─────────────────
function V5Profile() {
  return (
    <V5Frame active="users" title="Informations personnelles" actions={null}>
      <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 28, marginBottom: 22, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '320px 1fr', gap: 36 }}>
          {/* Photo */}
          <div style={{ aspectRatio: '1 / 1', background: 'linear-gradient(135deg,#e8e0c8,#cfc6ab)', borderRadius: 8, display: 'flex', alignItems: 'flex-end', justifyContent: 'center', position: 'relative' }}>
            <div style={{ width: '70%', aspectRatio: '1 / 1.2', background: V5.INK, borderRadius: '50% 50% 0 0', position: 'absolute', bottom: 0 }} />
            <div style={{ width: '34%', aspectRatio: '1 / 1', background: '#e8c4a0', borderRadius: '50%', position: 'absolute', bottom: '30%' }} />
          </div>
          {/* Info */}
          <div>
            <h2 style={{ fontFamily: V5.font.serif, fontSize: 28, color: V5.INK, fontWeight: 600, margin: 0 }}>Karim Bouchareb</h2>
            <div style={{ marginTop: 20 }}>
              {[
                ['Email',    'karim@cota.app'],
                ['Téléphone', '+226 70 12 34 56'],
                ['Ville',     'Ouagadougou'],
                ['Pays',      'Burkina Faso'],
                ['Adresse',   '441, 4ème avenue, Ouagadougou'],
              ].map(([k, v]) => (
                <div key={k} style={{ display: 'grid', gridTemplateColumns: '110px 1fr', padding: '8px 0', alignItems: 'baseline' }}>
                  <span style={{ fontSize: 13, color: V5.INK, fontWeight: 600 }}>{k} :</span>
                  <span style={{ fontSize: 13, color: V5.INK2 }}>{v}</span>
                </div>
              ))}
            </div>
            {/* Socials */}
            <div style={{ display: 'flex', gap: 8, marginTop: 18 }}>
              {['f', 'in', 't', 'Bē'].map(s => (
                <div key={s} style={{ width: 36, height: 36, borderRadius: 4, background: V5.BLUE, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 13, fontWeight: 700 }}>{s}</div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Update form */}
      <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 28, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
        <h3 style={{ fontFamily: V5.font.serif, fontSize: 22, color: V5.INK, fontWeight: 600, margin: '0 0 22px' }}>Mettre à jour les informations</h3>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 22 }}>
          {[
            ['Nom',       'Karim Bouchareb'],
            ['Email',     'karim@cota.app'],
            ['Téléphone', '+226 70 12 34 56'],
            ['Site web',  'cota.app/k/karim'],
            ['Adresse',   '441, 4ème avenue, Ouagadougou'],
            ['Photo de profil', '', 'file'],
          ].map(([k, v, type]) => (
            <div key={k}>
              <label style={{ fontSize: 13, color: V5.INK, fontWeight: 600, display: 'block', marginBottom: 8 }}>{k}</label>
              {type === 'file' ? (
                <div style={{ padding: '10px 14px', background: '#fff', border: `1px solid ${V5.LINE2}`, borderRadius: 6, fontSize: 13, color: V5.DIM, display: 'flex', gap: 10 }}>
                  <span style={{ padding: '4px 12px', background: V5.LINE, borderRadius: 4, color: V5.INK }}>Choisir un fichier</span>
                  <span style={{ alignSelf: 'center' }}>Aucun fichier choisi</span>
                </div>
              ) : (
                <input defaultValue={v} placeholder={k} style={{ width: '100%', boxSizing: 'border-box', padding: '12px 14px', border: `1px solid ${V5.LINE2}`, borderRadius: 6, fontSize: 13, color: V5.INK, fontFamily: V5.font.ui, background: '#fff' }} />
              )}
            </div>
          ))}
        </div>
        <div style={{ marginTop: 22 }}>
          <label style={{ fontSize: 13, color: V5.INK, fontWeight: 600, display: 'block', marginBottom: 8 }}>À propos</label>
          <textarea defaultValue="Pronostiqueur passionné de Ligue 1 et UCL. Coupons IA quotidiens, 9 critères analysés." style={{ width: '100%', boxSizing: 'border-box', padding: '12px 14px', border: `1px solid ${V5.LINE2}`, borderRadius: 6, fontSize: 13, color: V5.INK, fontFamily: V5.font.ui, background: '#fff', resize: 'vertical', minHeight: 80 }} />
        </div>
        <div style={{ marginTop: 22 }}>
          <V5Button variant="primary">Enregistrer les modifications</V5Button>
        </div>
      </div>
    </V5Frame>
  );
}

// ── Add Property (form style MaxLand) ────────────────────────────────────────
function V5AddCoupon() {
  const Field = ({ label, placeholder, select }) => (
    <div>
      <label style={{ fontSize: 13, color: V5.INK, fontWeight: 600, display: 'block', marginBottom: 8 }}>{label}</label>
      <div style={{ padding: '12px 14px', background: '#fff', border: `1px solid ${V5.LINE2}`, borderRadius: 6, fontSize: 13, color: V5.DIM, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        {placeholder}
        {select && <svg width="11" height="11" viewBox="0 0 11 11"><path d="M2 4 L5.5 7.5 L9 4" stroke={V5.DIM} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>}
      </div>
    </div>
  );

  return (
    <V5Frame active="predictions" title="Ajouter un coupon" actions={<V5Button variant="primary">Tous les coupons</V5Button>}>
      {/* Basic info */}
      <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 28, marginBottom: 22, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
        <h3 style={{ fontFamily: V5.font.serif, fontSize: 22, color: V5.INK, fontWeight: 600, margin: '0 0 22px' }}>Informations de base</h3>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 22 }}>
          <Field label="Titre"          placeholder="PSG – OM"         />
          <Field label="Slug"           placeholder="psg-om-18-mai"    />
          <Field label="Compétition"    placeholder="Sélectionner"     select />
          <Field label="Ligue"          placeholder="Sélectionner"     select />
          <Field label="Date du match"  placeholder="18 mai 2026"       />
          <Field label="Source IA"      placeholder="cota-model-v1"    />
          <Field label="Adresse stade"   placeholder="Parc des Princes" />
          <Field label="Téléphone"      placeholder="Téléphone"        />
          <Field label="Email contact"   placeholder="Email"            />
          <Field label="Type de pari"    placeholder="Sélectionner"     select />
          <Field label="Confiance"      placeholder="Pourcentage"      />
          <Field label="Cote"           placeholder="@1.65"            />
        </div>
      </div>

      {/* Other info */}
      <div style={{ background: V5.CARD_BG, borderRadius: 10, padding: 28, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' }}>
        <h3 style={{ fontFamily: V5.font.serif, fontSize: 22, color: V5.INK, fontWeight: 600, margin: '0 0 22px' }}>Autres informations</h3>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 22 }}>
          <Field label="Buts attendus (xG)" placeholder="2.8" />
          <Field label="Possession attendue" placeholder="64%" />
          <Field label="Cartons attendus"    placeholder="2.8" />
        </div>
        <div style={{ marginTop: 22, display: 'flex', gap: 10 }}>
          <V5Button variant="primary">Publier le coupon</V5Button>
          <V5Button variant="ghost">Annuler</V5Button>
        </div>
      </div>
    </V5Frame>
  );
}

Object.assign(window, { V5Overview, V5Properties, V5Pricing, V5Profile, V5AddCoupon });
