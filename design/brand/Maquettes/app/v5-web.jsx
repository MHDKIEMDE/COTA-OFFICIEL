// COTA V5 — Landing web (sober, MaxLand-inspired editorial sober).

function V5Landing() {
  return (
    <div style={{ width: '100%', minHeight: '100%', background: V5.MAIN_BG, color: V5.INK, fontFamily: V5.font.ui }}>
      {/* Nav */}
      <header style={{ padding: '20px 64px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: V5.CARD_BG, borderBottom: `1px solid ${V5.LINE}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <AppIcon size={32} />
          <span style={{ fontFamily: V5.font.serif, fontSize: 22, fontWeight: 700 }}>COTA</span>
        </div>
        <nav style={{ display: 'flex', gap: 32, fontSize: 14, color: V5.INK2 }}>
          <a style={{ color: V5.INK, fontWeight: 600 }}>Accueil</a>
          <a>Comment ça marche</a>
          <a>Tarifs</a>
          <a>Tipsters</a>
          <a>Aide</a>
        </nav>
        <div style={{ display: 'flex', gap: 10 }}>
          <V5Button variant="ghost">Se connecter</V5Button>
          <V5Button variant="primary">Télécharger</V5Button>
        </div>
      </header>

      {/* Hero */}
      <section style={{ padding: '96px 64px', display: 'grid', gridTemplateColumns: '1.2fr 1fr', gap: 56, alignItems: 'center', maxWidth: 1300, margin: '0 auto' }}>
        <div>
          <div style={{ fontSize: 13, color: V5.BLUE, fontWeight: 600, marginBottom: 14, letterSpacing: '0.05em' }}>★ 247 MATCHS ANALYSÉS CE WEEK-END</div>
          <h1 style={{ fontFamily: V5.font.serif, fontSize: 72, lineHeight: 1.05, letterSpacing: '-0.02em', fontWeight: 700, margin: 0, color: V5.INK }}>
            Le foot, lu par<br/>une <em style={{ color: V5.BLUE, fontStyle: 'italic' }}>intelligence</em>.
          </h1>
          <p style={{ fontSize: 17, color: V5.INK2, marginTop: 22, lineHeight: 1.55, maxWidth: 480 }}>
            9 critères croisés par notre IA, 1 coupon livré à 9h30. Pas d'émotion, juste de la donnée.
          </p>
          <div style={{ display: 'flex', gap: 12, marginTop: 32 }}>
            <V5Button variant="primary">Télécharger pour iOS</V5Button>
            <V5Button variant="ghost">Télécharger pour Android</V5Button>
          </div>
          <div style={{ display: 'flex', gap: 36, marginTop: 56, paddingTop: 32, borderTop: `1px solid ${V5.LINE}` }}>
            {[['+18.5%', 'ROI moyen'], ['72%', 'Taux réussite'], ['47k', 'Utilisateurs']].map(([n, l]) => (
              <div key={l}>
                <div style={{ fontFamily: V5.font.serif, fontSize: 32, color: V5.INK, fontWeight: 700, letterSpacing: '-0.02em' }}>{n}</div>
                <div style={{ fontSize: 12, color: V5.DIM, marginTop: 4 }}>{l}</div>
              </div>
            ))}
          </div>
        </div>
        <div>
          {/* Phone mockup card */}
          <div style={{ width: 360, height: 600, background: V5.INK, borderRadius: 36, padding: 12, boxShadow: '0 36px 72px rgba(0,0,0,0.18)', margin: '0 auto' }}>
            <div style={{ width: '100%', height: '100%', background: V5.CARD_BG, borderRadius: 28, overflow: 'hidden', padding: 24, boxSizing: 'border-box' }}>
              <div style={{ fontSize: 11, color: V5.DIM, fontFamily: V5.font.mono, letterSpacing: '0.15em' }}>MAR 18 MAI · 09:30</div>
              <h3 style={{ fontFamily: V5.font.serif, fontSize: 22, fontWeight: 600, margin: '8px 0 0' }}>Ton coupon du jour</h3>

              <div style={{ marginTop: 18, padding: 16, background: V5.MAIN_BG, borderRadius: 10 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                  <span style={{ fontSize: 11, color: V5.DIM, fontFamily: V5.font.mono, letterSpacing: '0.12em' }}>COTE COMBINÉE</span>
                  <span style={{ fontFamily: V5.font.serif, fontSize: 28, color: V5.INK, fontWeight: 700 }}>@4.55</span>
                </div>
                <div style={{ marginTop: 14, display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {[
                    ['PSG-OM',   'Victoire PSG', '1.65'],
                    ['LIV-ARS',  '+2.5 buts',    '1.78'],
                    ['RMA-BAY',  'BTTS Oui',     '1.55'],
                  ].map(([m, t, o]) => (
                    <div key={m} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', padding: '6px 0', borderTop: `1px solid ${V5.LINE}` }}>
                      <span style={{ fontFamily: V5.font.mono, fontSize: 10, color: V5.DIM }}>{m}</span>
                      <span style={{ fontSize: 12, flex: 1, marginLeft: 8 }}>{t}</span>
                      <span style={{ fontFamily: V5.font.mono, fontSize: 12, fontWeight: 700 }}>@{o}</span>
                    </div>
                  ))}
                </div>
              </div>

              <div style={{ marginTop: 18, padding: '14px 16px', background: V5.WIN_BG, borderRadius: 10, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: 12, color: V5.WIN, fontWeight: 600 }}>✓ Confiance moyenne</span>
                <span style={{ fontFamily: V5.font.serif, fontSize: 20, color: V5.WIN, fontWeight: 700 }}>87%</span>
              </div>

              <V5Button variant="primary" full>Voir l'analyse</V5Button>
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section style={{ padding: '80px 64px', background: V5.CARD_BG, maxWidth: '100%' }}>
        <div style={{ maxWidth: 1100, margin: '0 auto', textAlign: 'center' }}>
          <div style={{ fontSize: 12, color: V5.BLUE, fontWeight: 600, letterSpacing: '0.08em', marginBottom: 12 }}>NOTRE MÉTHODE</div>
          <h2 style={{ fontFamily: V5.font.serif, fontSize: 48, fontWeight: 700, letterSpacing: '-0.02em', margin: 0 }}>9 critères, chaque match.</h2>
          <p style={{ fontSize: 16, color: V5.INK2, marginTop: 18, maxWidth: 640, marginLeft: 'auto', marginRight: 'auto', lineHeight: 1.55 }}>
            Notre IA croise plus de 50 millions de données par rencontre. Voici exactement ce qu'elle analyse pour générer le coupon du jour.
          </p>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 22, maxWidth: 1100, margin: '48px auto 0' }}>
          {[
            ['01', 'Forme actuelle',     'Performance des 5 derniers matchs, série en cours, momentum statistique.'],
            ['02', 'Confrontations',     'Historique direct entre les deux équipes sur 10 ans, contexte stade.'],
            ['03', 'Domicile / Ext.',    'Taux de victoire à domicile, performances en extérieur, voyage.'],
            ['04', 'Blessures clés',     'Indisponibilités des titulaires, qualité de la rotation, retours.'],
            ['05', 'Météo',              'Conditions au coup d\'envoi, vent, températures, pelouse.'],
            ['06', 'Cotes du marché',     'Consensus bookmakers, mouvement des cotes, volume parié.'],
            ['07', 'Cartons & arbitre',  'Style de l\'arbitre, profil des équipes, sanctions saison.'],
            ['08', 'Possession attendue', 'Style de jeu, pressing, contre-attaque, blocs défensifs.'],
            ['09', 'Buts attendus (xG)',  'Modèle statistique sur les occasions créées, finition.'],
          ].map(([n, t, d]) => (
            <div key={n} style={{ background: V5.MAIN_BG, borderRadius: 10, padding: 28 }}>
              <div style={{ fontFamily: V5.font.mono, fontSize: 11, color: V5.BLUE, fontWeight: 700, letterSpacing: '0.15em' }}>{n}</div>
              <h4 style={{ fontFamily: V5.font.serif, fontSize: 19, fontWeight: 600, margin: '10px 0 8px', color: V5.INK }}>{t}</h4>
              <p style={{ fontSize: 13, color: V5.INK2, lineHeight: 1.55, margin: 0 }}>{d}</p>
            </div>
          ))}
        </div>
      </section>

      {/* CTA download */}
      <section style={{ padding: '72px 64px', background: V5.INK, color: V5.MAIN_BG, textAlign: 'center' }}>
        <h2 style={{ fontFamily: V5.font.serif, fontSize: 44, fontWeight: 700, letterSpacing: '-0.02em', margin: 0, color: V5.MAIN_BG }}>
          Ton coupon arrive à 9h30. Chaque jour.
        </h2>
        <p style={{ fontSize: 16, color: 'rgba(244,239,226,0.65)', marginTop: 14, maxWidth: 540, margin: '14px auto 0', lineHeight: 1.55 }}>
          App gratuite. Essai Premium de 14 jours. Annule quand tu veux.
        </p>
        <div style={{ display: 'flex', gap: 12, marginTop: 28, justifyContent: 'center' }}>
          <button style={{ background: V5.MAIN_BG, color: V5.INK, border: 'none', padding: '14px 28px', borderRadius: 6, fontSize: 14, fontWeight: 600, fontFamily: V5.font.ui }}>↓ App Store</button>
          <button style={{ background: V5.ACCENT, color: V5.INK, border: 'none', padding: '14px 28px', borderRadius: 6, fontSize: 14, fontWeight: 600, fontFamily: V5.font.ui }}>↓ Google Play</button>
        </div>
      </section>

      {/* Footer */}
      <footer style={{ padding: '48px 64px 32px', background: V5.CARD_BG, color: V5.INK2 }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr 1fr 1fr', gap: 32, maxWidth: 1200, margin: '0 auto' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <AppIcon size={32} />
              <span style={{ fontFamily: V5.font.serif, fontSize: 22, fontWeight: 700, color: V5.INK }}>COTA</span>
            </div>
            <p style={{ fontSize: 13, marginTop: 14, lineHeight: 1.55, maxWidth: 260 }}>
              Coupons IA quotidiens pour passionnés de football. Données vérifiées, 9 critères croisés.
            </p>
          </div>
          {[
            ['Produit',   ['Méthode', 'Tarifs', 'Bookmakers', 'Statistiques']],
            ['Communauté', ['Tipsters', 'Discord', 'Twitter', 'Blog']],
            ['Légal',      ['CGU', 'Confidentialité', 'Jeu responsable 18+', 'Contact']],
          ].map(([h, items]) => (
            <div key={h}>
              <div style={{ fontSize: 12, color: V5.INK, fontWeight: 700, letterSpacing: '0.06em', marginBottom: 14 }}>{h.toUpperCase()}</div>
              {items.map(it => (
                <div key={it} style={{ fontSize: 13, marginBottom: 9 }}>{it}</div>
              ))}
            </div>
          ))}
        </div>
        <div style={{ marginTop: 36, paddingTop: 22, borderTop: `1px solid ${V5.LINE}`, display: 'flex', justifyContent: 'space-between', fontSize: 12, color: V5.DIM }}>
          <span>© 2026 COTA. Tous droits réservés.</span>
          <span>Jeu responsable · 18+</span>
        </div>
      </footer>
    </div>
  );
}

Object.assign(window, { V5Landing });
