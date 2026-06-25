// COTA V3 — web pages (sober editorial, no full-bleed heroes).

// ─────────────────────────────────────────────────────────────────────────────
// 6. CENTRE D'AIDE — desktop
// ─────────────────────────────────────────────────────────────────────────────
function V3HelpCenter() {
  const categories = [
    { title: 'Bien démarrer',       sub: '8 articles', items: ['Créer son compte', 'Recevoir le coupon quotidien', 'Comprendre les 9 critères', 'Personnaliser ses ligues'] },
    { title: 'Coupons & picks',     sub: '12 articles', items: ['Comment fonctionne le score IA ?', 'Pourquoi je n\'ai pas reçu mon coupon ?', 'Modifier l\'heure de réception', 'Suivre l\'historique de mes coupons'] },
    { title: 'Premium',             sub: '6 articles',  items: ['Quels sont les bénéfices Premium ?', 'Comment annuler mon abonnement ?', 'Essai gratuit 14 jours', 'Méthodes de paiement acceptées'] },
    { title: 'Paiements & retraits', sub: '10 articles', items: ['Délais de retrait Wave / Orange Money', 'Frais de transaction', 'Échec de paiement, que faire ?', 'Changer ma méthode par défaut'] },
    { title: 'Compte & sécurité',   sub: '7 articles',  items: ['Changer mon numéro de téléphone', 'Activer la 2FA', 'Supprimer mon compte', 'Récupérer un compte oublié'] },
    { title: 'Bookmakers & affiliés', sub: '5 articles', items: ['Comment fonctionne l\'affiliation ?', 'Pourquoi 1xBet est recommandé ?', 'Bookmakers par région'] },
  ];

  return (
    <div style={{ width: '100%', minHeight: '100%', background: V3.BG, color: V3.INK, fontFamily: V3.font.ui }}>
      {/* Nav — minimal */}
      <header style={{ padding: '24px 64px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: `1px solid ${V3.LINE}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <AppIcon size={28} />
          <span style={{ fontSize: 18, fontFamily: V3.font.hero, letterSpacing: '-0.04em', fontWeight: 900 }}>COTA</span>
          <span style={{ fontSize: 13, color: V3.DIM, marginLeft: 14 }}>· Centre d'aide</span>
        </div>
        <div style={{ display: 'flex', gap: 28, fontSize: 13 }}>
          <a style={{ color: V3.DIM }}>Coupons</a>
          <a style={{ color: V3.DIM }}>Méthode</a>
          <a style={{ color: V3.DIM }}>Aide</a>
          <a style={{ color: V3.DIM }}>Connexion</a>
        </div>
      </header>

      {/* Hero — sober, just headline + search */}
      <section style={{ padding: '80px 64px 60px', maxWidth: 980, margin: '0 auto' }}>
        <div style={{ fontSize: 13, color: V3.DIM, fontWeight: 500, marginBottom: 12 }}>Centre d'aide</div>
        <h1 style={{ fontSize: 48, fontWeight: 400, letterSpacing: '-0.02em', lineHeight: 1.1, margin: 0 }}>
          Comment pouvons-nous<br/>vous aider ?
        </h1>

        {/* Search */}
        <div style={{ marginTop: 36, padding: '16px 18px', background: V3.BG2, border: `1px solid ${V3.LINE2}`, borderRadius: 12, display: 'flex', alignItems: 'center', gap: 12, maxWidth: 600 }}>
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="7" cy="7" r="5" stroke={V3.DIM} strokeWidth="1.5"/>
            <path d="M11 11 L14.5 14.5" stroke={V3.DIM} strokeWidth="1.5" strokeLinecap="round"/>
          </svg>
          <span style={{ flex: 1, fontSize: 14, color: V3.DIM }}>Chercher dans 48 articles…</span>
          <span style={{ fontSize: 11, color: V3.DIM, padding: '4px 8px', border: `1px solid ${V3.LINE2}`, borderRadius: 6 }}>↵</span>
        </div>

        {/* Common questions */}
        <div style={{ marginTop: 36, display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          <span style={{ fontSize: 12, color: V3.DIM, padding: '8px 0' }}>Populaire :</span>
          {['Comment recevoir mon coupon ?', 'Essai gratuit Premium', 'Retrait Wave', 'Modifier mes ligues'].map(t => (
            <span key={t} style={{ padding: '7px 14px', background: V3.BG2, borderRadius: 999, fontSize: 12, color: V3.INK2, border: `1px solid ${V3.LINE}`, cursor: 'pointer' }}>{t}</span>
          ))}
        </div>
      </section>

      {/* Categories */}
      <section style={{ padding: '20px 64px 60px', maxWidth: 980, margin: '0 auto' }}>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 1, background: V3.LINE2, borderRadius: 12, overflow: 'hidden', border: `1px solid ${V3.LINE2}` }}>
          {categories.map((c, i) => (
            <div key={c.title} style={{ background: V3.BG, padding: 24 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 12 }}>
                <div>
                  <div style={{ fontSize: 16, fontWeight: 500, color: V3.INK, letterSpacing: '-0.01em' }}>{c.title}</div>
                  <div style={{ fontSize: 11, color: V3.DIM, marginTop: 2 }}>{c.sub}</div>
                </div>
              </div>
              {c.items.slice(0, 4).map(it => (
                <div key={it} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '10px 0', borderTop: `1px solid ${V3.LINE}` }}>
                  <span style={{ fontSize: 13, color: V3.INK2 }}>{it}</span>
                  <svg width="9" height="9" viewBox="0 0 9 9"><path d="M3 1 L6 4.5 L3 8" stroke={V3.DIM2} strokeWidth="1.3" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
                </div>
              ))}
              <div style={{ marginTop: 14, fontSize: 12, color: V3.INK2, fontWeight: 500, cursor: 'pointer' }}>Voir tous →</div>
            </div>
          ))}
        </div>
      </section>

      {/* Contact */}
      <section style={{ padding: '40px 64px 80px', maxWidth: 980, margin: '0 auto' }}>
        <div style={{ padding: 32, background: V3.BG2, border: `1px solid ${V3.LINE2}`, borderRadius: 14, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 20 }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 500, color: V3.INK, letterSpacing: '-0.01em' }}>Tu n'as pas trouvé ta réponse ?</div>
            <div style={{ fontSize: 13, color: V3.DIM, marginTop: 6 }}>Notre équipe répond sous 4h en moyenne. Du lundi au samedi.</div>
          </div>
          <div style={{ display: 'flex', gap: 10 }}>
            <V3Button>WhatsApp</V3Button>
            <V3Button primary>Contacter</V3Button>
          </div>
        </div>
      </section>

      {/* Footer — minimal */}
      <footer style={{ padding: '40px 64px', borderTop: `1px solid ${V3.LINE}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', fontSize: 12, color: V3.DIM }}>
        <div>© 2026 COTA</div>
        <div style={{ display: 'flex', gap: 22 }}>
          <span>CGU</span>
          <span>Confidentialité</span>
          <span>Jeu responsable</span>
          <span>Statut système</span>
        </div>
      </footer>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// 7. TIPSTER PUBLIC — page partageable (cota.app/k/karim)
// ─────────────────────────────────────────────────────────────────────────────
function V3TipsterPublic() {
  return (
    <div style={{ width: '100%', minHeight: '100%', background: V3.BG, color: V3.INK, fontFamily: V3.font.ui }}>
      {/* Nav */}
      <header style={{ padding: '20px 64px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: `1px solid ${V3.LINE}` }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <AppIcon size={26} />
          <span style={{ fontSize: 17, fontFamily: V3.font.hero, letterSpacing: '-0.04em', fontWeight: 900 }}>COTA</span>
        </div>
        <V3Button size="sm" primary>Télécharger l'app</V3Button>
      </header>

      <div style={{ maxWidth: 1040, margin: '0 auto', padding: '48px 64px 80px', display: 'grid', gridTemplateColumns: '1fr 320px', gap: 56 }}>
        {/* MAIN */}
        <div>
          {/* Identity */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 18, marginBottom: 6 }}>
            <V3Avatar name="Karim B." size={64} />
            <div>
              <div style={{ fontSize: 28, fontWeight: 500, letterSpacing: '-0.01em' }}>Karim Bouchareb</div>
              <div style={{ fontSize: 13, color: V3.DIM, marginTop: 4 }}>cota.app/k/karim · Membre depuis nov. 2025</div>
            </div>
          </div>

          <div style={{ fontSize: 14, color: V3.INK2, marginTop: 14, lineHeight: 1.55, maxWidth: 540 }}>
            Pronostics IA quotidiens, focus Ligue 1 & Champions League. 9 critères, zéro émotion.
          </div>

          {/* Hero stats — sober numbers */}
          <div style={{ marginTop: 36, padding: '24px 0', borderTop: `1px solid ${V3.LINE}`, borderBottom: `1px solid ${V3.LINE}`, display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 0 }}>
            {[
              ['+18,5%', 'ROI 30 jours', V3.WIN],
              ['72%',   'Taux réussite', V3.INK],
              ['47',    'Coupons', V3.INK],
              ['4',     'Streak', V3.WIN],
            ].map(([n, l, c], i) => (
              <div key={l} style={{ textAlign: i === 0 ? 'left' : 'left', paddingLeft: i === 0 ? 0 : 24, borderLeft: i > 0 ? `1px solid ${V3.LINE}` : 'none' }}>
                <div style={{ fontFamily: V3.font.hero, fontSize: 32, color: c, letterSpacing: '-0.03em', lineHeight: 1, fontWeight: 900 }}>{n}</div>
                <div style={{ fontSize: 11, color: V3.DIM, marginTop: 8, fontWeight: 500 }}>{l}</div>
              </div>
            ))}
          </div>

          {/* Performance chart */}
          <div style={{ marginTop: 40 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 14 }}>
              <div>
                <div style={{ fontSize: 16, fontWeight: 500, letterSpacing: '-0.01em' }}>Performance</div>
                <div style={{ fontSize: 12, color: V3.DIM, marginTop: 2 }}>Sur 30 jours · +184 € net</div>
              </div>
              <div style={{ display: 'flex', gap: 4 }}>
                {['7J', '30J', '90J', 'Tout'].map((t, i) => (
                  <V3Chip key={t} label={t} on={i === 1} />
                ))}
              </div>
            </div>
            <Sparkline data={[12, 18, 22, 19, 28, 32, 38, 42, 48, 55, 62, 68, 78, 84, 92, 88, 100, 110, 122, 134, 145, 156, 162, 168, 175, 180, 184]} width={620} height={140} color={V3.WIN} />
          </div>

          {/* Recent coupons */}
          <div style={{ marginTop: 40 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 16 }}>
              <div style={{ fontSize: 16, fontWeight: 500, letterSpacing: '-0.01em' }}>Coupons récents</div>
              <span style={{ fontSize: 12, color: V3.INK2, fontWeight: 500 }}>Tout voir →</span>
            </div>

            {[
              { date: '18 mai', odds: '@4.55', picks: 'PSG · LIV · RMA', result: '+44,20 €', kind: 'won' },
              { date: '17 mai', odds: '@3.12', picks: 'OM · BAY · JUV',  result: '+31,20 €', kind: 'won' },
              { date: '16 mai', odds: '@5.10', picks: 'OL · LIV · MCI',  result: '-10,00 €', kind: 'lost' },
              { date: '15 mai', odds: '@6.80', picks: 'BAY · LIV · MCI · PSG', result: '+68,00 €', kind: 'won' },
              { date: '14 mai', odds: '@4.20', picks: 'ARS · DOR · RMA', result: '+42,00 €', kind: 'won' },
            ].map((c, i) => (
              <div key={i} style={{ display: 'grid', gridTemplateColumns: '70px 70px 1fr 110px 80px', gap: 16, padding: '14px 0', alignItems: 'center', borderTop: `1px solid ${V3.LINE}` }}>
                <span style={{ fontSize: 13, color: V3.DIM }}>{c.date}</span>
                <span style={{ fontFamily: V3.font.mono, fontSize: 13, color: V3.INK, fontWeight: 500 }}>{c.odds}</span>
                <span style={{ fontSize: 13, color: V3.INK2 }}>{c.picks}</span>
                <span style={{ fontFamily: V3.font.mono, fontSize: 13, color: c.kind === 'won' ? V3.WIN : V3.LOSS, fontWeight: 500 }}>{c.result}</span>
                <span style={{ fontSize: 11, color: c.kind === 'won' ? V3.WIN : V3.LOSS, fontWeight: 500 }}>
                  {c.kind === 'won' ? 'Gagné' : 'Perdu'}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* SIDE */}
        <aside>
          <div style={{ padding: 24, background: V3.BG2, border: `1px solid ${V3.LINE2}`, borderRadius: 12, position: 'sticky', top: 90 }}>
            <div style={{ fontSize: 13, color: V3.DIM, fontWeight: 500, marginBottom: 8 }}>Suis Karim sur COTA</div>
            <div style={{ fontSize: 15, color: V3.INK, lineHeight: 1.45, marginBottom: 18 }}>
              Reçois ses coupons IA chaque matin à 9h30, directement dans l'app.
            </div>
            <V3Button primary>Télécharger l'app</V3Button>
            <div style={{ marginTop: 12 }}>
              <V3Button>Voir le coupon du jour</V3Button>
            </div>

            <div style={{ marginTop: 22, paddingTop: 22, borderTop: `1px solid ${V3.LINE}` }}>
              <div style={{ fontSize: 11, color: V3.DIM, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 10 }}>Spécialités</div>
              <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                <V3Chip label="Ligue 1" on />
                <V3Chip label="UCL" on />
                <V3Chip label="BTTS" on />
              </div>
            </div>

            <div style={{ marginTop: 22, paddingTop: 22, borderTop: `1px solid ${V3.LINE}` }}>
              <div style={{ fontSize: 11, color: V3.DIM, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em', marginBottom: 10 }}>Partager</div>
              <div style={{ display: 'flex', gap: 8 }}>
                {['X', 'WA', 'TG', 'IG'].map(s => (
                  <div key={s} style={{ width: 32, height: 32, borderRadius: 8, border: `1px solid ${V3.LINE2}`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, color: V3.INK2, fontWeight: 500 }}>{s}</div>
                ))}
              </div>
            </div>
          </div>
        </aside>
      </div>

      {/* Footer */}
      <footer style={{ padding: '32px 64px', borderTop: `1px solid ${V3.LINE}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', fontSize: 12, color: V3.DIM }}>
        <div>© 2026 COTA · cota.app/k/karim</div>
        <div style={{ display: 'flex', gap: 22 }}>
          <span>Jeu responsable</span>
          <span>18+</span>
          <span>Aide</span>
        </div>
      </footer>
    </div>
  );
}

Object.assign(window, { V3HelpCenter, V3TipsterPublic });
