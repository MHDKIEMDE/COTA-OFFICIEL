// COTA V2 — Section A: post-onboarding screens 04 → 07.

const { BG: aBG, BG2: aBG2, BG3: aBG3, LINE: aLINE, LINE2: aLINE2, INK: aINK, INK2: aINK2, DIM: aDIM, DIM2: aDIM2, ACCENT: aACCENT, WIN: aWIN, font: aFONT } = window.COTA;

// Reusable dots indicator. activeIndex 0-indexed.
function Dots({ count = 7, active = 0 }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', gap: 6 }}>
      {Array.from({ length: count }).map((_, i) => (
        <span key={i} style={{
          width: i === active ? 24 : 8, height: 3,
          background: i === active ? aACCENT : aLINE2,
          borderRadius: 2, transition: 'width 0.2s',
        }} />
      ))}
    </div>
  );
}

// ── Screen 04 · Personnalisation Ligues ──────────────────────────────────────
function OnboardLeagues() {
  const T1 = [
    { name: 'Champions League', country: 'Europe',     color: '#001f3f' },
    { name: 'Premier League',   country: 'Angleterre', color: '#37003c' },
    { name: 'La Liga',          country: 'Espagne',    color: '#ee2737' },
    { name: 'Serie A',          country: 'Italie',     color: '#008fd7' },
    { name: 'Bundesliga',       country: 'Allemagne',  color: '#d3010c' },
    { name: 'Ligue 1',          country: 'France',     color: '#003366' },
  ];
  const T2 = [
    { name: 'Europa League',    country: 'Europe',     color: '#ff6900' },
    { name: 'Liga Portugal',    country: 'Portugal',   color: '#006400' },
    { name: 'Eredivisie',       country: 'Pays-Bas',   color: '#ff6f00' },
    { name: 'Saudi Pro League', country: 'Arabie',     color: '#1a7a3e' },
  ];

  const selected = new Set([0, 1, 5]); // PSG/UCL/Ligue 1 etc

  return (
    <div style={{ background: aBG, color: aINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: aFONT.ui, paddingTop: 90 }}>
      <div style={{ padding: '8px 24px 0', flex: 1, overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: aACCENT, marginBottom: 12 }}>04 — PERSONNALISE</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 28, letterSpacing: '-0.03em', lineHeight: 1.05 }}>
          Quelles ligues<br/>te passionnent ?
        </div>
        <div style={{ fontSize: 13, color: aINK2, marginTop: 10, lineHeight: 1.45 }}>
          On filtre tes coupons selon tes ligues. Tu pourras changer ça plus tard.
        </div>

        {/* Tier 1 grid */}
        <div style={{ marginTop: 20, fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.18em' }}>LIGUES MAJEURES</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 8, marginTop: 10 }}>
          {T1.map((l, i) => (
            <LeagueChip key={l.name} name={l.name} country={l.country} color={l.color} selected={selected.has(i)} />
          ))}
        </div>

        {/* Tier 2 */}
        <div style={{ marginTop: 16, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.18em' }}>AUTRES LIGUES</div>
          <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aACCENT, letterSpacing: '0.1em' }}>VOIR TOUT ↓</div>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 8, marginTop: 10 }}>
          {T2.map(l => <LeagueChip key={l.name} name={l.name} country={l.country} color={l.color} />)}
        </div>

        <div style={{ flex: 1 }} />
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aDIM, letterSpacing: '0.12em', textAlign: 'center', padding: '12px 0 4px' }}>TOUT SÉLECTIONNER</div>
      </div>

      {/* CTA */}
      <div style={{ padding: '0 24px 36px' }}>
        <button style={{ width: '100%', height: 52, background: aACCENT, color: aBG, border: 'none', borderRadius: 12, fontFamily: aFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>VALIDER MES LIGUES →</button>
        <div style={{ marginTop: 16 }}><Dots count={7} active={3} /></div>
      </div>
    </div>
  );
}

// ── Screen 05 · Niveau de risque ─────────────────────────────────────────────
function OnboardRisk() {
  const cards = [
    {
      id: 'prudent', label: 'Prudent', stars: '★★★ minimum',
      sub: 'Confiance ≥ 75% uniquement', desc: 'Moins de picks, taux de réussite plus élevé. Idéal pour commencer.',
      icon: (c) => <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M11 2 L19 5 v6 c0 5 -4 8 -8 9 c-4 -1 -8 -4 -8 -9 v-6 Z" stroke={c} strokeWidth="1.6" strokeLinejoin="round"/></svg>,
      selected: false,
    },
    {
      id: 'eq', label: 'Équilibré', stars: '★★ minimum', recommended: true,
      sub: 'Confiance ≥ 65%', desc: 'Le juste milieu entre volume et qualité. Notre réglage par défaut.',
      icon: (c) => <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M3 17 L11 5 L19 17" stroke={c} strokeWidth="1.6" strokeLinejoin="round"/><path d="M5 17 H17" stroke={c} strokeWidth="1.6" strokeLinecap="round"/></svg>,
      selected: true,
    },
    {
      id: 'chasse', label: 'Chasseur de cotes', stars: '★ minimum',
      sub: 'Tous les picks publiés', desc: 'Plus de matchs, cotes plus élevées. Pour les profils audacieux.',
      icon: (c) => <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><circle cx="11" cy="11" r="8" stroke={c} strokeWidth="1.6"/><circle cx="11" cy="11" r="4" stroke={c} strokeWidth="1.6"/><circle cx="11" cy="11" r="1.2" fill={c}/></svg>,
      selected: false,
    },
  ];
  return (
    <div style={{ background: aBG, color: aINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: aFONT.ui, paddingTop: 90 }}>
      <div style={{ padding: '8px 24px 0', flex: 1 }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: aACCENT, marginBottom: 12 }}>05 — TON STYLE</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 28, letterSpacing: '-0.03em', lineHeight: 1.05 }}>
          Comment tu<br/>parierais ?
        </div>
        <div style={{ fontSize: 13, color: aINK2, marginTop: 10, lineHeight: 1.45 }}>
          On adapte les picks au profil que tu choisis.
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 22 }}>
          {cards.map(c => (
            <div key={c.id} style={{
              padding: '14px', borderRadius: 12,
              background: c.selected ? aBG3 : aBG2,
              border: `1px solid ${c.selected ? aACCENT : aLINE}`,
              position: 'relative', display: 'flex', alignItems: 'center', gap: 14,
            }}>
              <div style={{
                width: 44, height: 44, borderRadius: 10,
                background: c.selected ? 'rgba(232,255,54,0.15)' : aBG3,
                color: c.selected ? aACCENT : aDIM,
                display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
              }}>{c.icon(c.selected ? aACCENT : aDIM)}</div>
              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ fontSize: 14, fontWeight: 700, color: aINK }}>{c.label}</span>
                  {c.recommended && <Pill bg={aACCENT} color={aBG}>RECOMMANDÉ</Pill>}
                </div>
                <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aDIM, letterSpacing: '0.08em', marginTop: 3 }}>{c.sub} · {c.stars}</div>
                <div style={{ fontSize: 11, color: aINK2, marginTop: 5, lineHeight: 1.4 }}>{c.desc}</div>
              </div>
              {c.selected && (
                <div style={{ position: 'absolute', top: 12, right: 12, width: 18, height: 18, borderRadius: 9, background: aACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <svg width="10" height="10" viewBox="0 0 10 10"><path d="M1.5 5 L4 7.5 L8.5 2.5" stroke={aBG} strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      <div style={{ padding: '12px 24px 36px' }}>
        <button style={{ width: '100%', height: 52, background: aACCENT, color: aBG, border: 'none', borderRadius: 12, fontFamily: aFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>CONTINUER →</button>
        <button style={{ width: '100%', height: 36, background: 'transparent', color: aDIM, border: 'none', fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.15em', marginTop: 4 }}>PASSER</button>
        <div style={{ marginTop: 8 }}><Dots count={7} active={4} /></div>
      </div>
    </div>
  );
}

// ── Screen 06 · Compte (inscription) ─────────────────────────────────────────
function OnboardAccount() {
  return (
    <div style={{ background: aBG, color: aINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: aFONT.ui, paddingTop: 76 }}>
      <div style={{ padding: '6px 24px 0', flex: 1, overflow: 'auto' }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: aACCENT, marginBottom: 10 }}>06 — COMPTE</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 26, letterSpacing: '-0.03em', lineHeight: 1.05 }}>
          Crée ton compte<br/>gratuit.
        </div>
        <div style={{ fontSize: 13, color: aINK2, marginTop: 8, lineHeight: 1.45 }}>
          Sauvegarde tes stats, reçois ton coupon chaque matin.
        </div>

        {/* Toggle tabs */}
        <div style={{ display: 'flex', gap: 24, marginTop: 22, borderBottom: `1px solid ${aLINE}` }}>
          <div style={{ paddingBottom: 8, fontFamily: aFONT.mono, fontSize: 11, color: aACCENT, letterSpacing: '0.12em', borderBottom: `2px solid ${aACCENT}` }}>CRÉER UN COMPTE</div>
          <div style={{ paddingBottom: 8, fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.12em' }}>SE CONNECTER</div>
        </div>

        {/* Phone */}
        <div style={{ marginTop: 16 }}>
          <label style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.12em' }}>NUMÉRO DE TÉLÉPHONE</label>
          <div style={{ display: 'flex', gap: 6, marginTop: 6 }}>
            <div style={{ padding: '12px 10px', background: aBG2, border: `1px solid ${aLINE2}`, borderRadius: 10, display: 'flex', alignItems: 'center', gap: 6 }}>
              <span style={{ fontSize: 14 }}>🇧🇫</span>
              <span style={{ fontFamily: aFONT.mono, fontSize: 12, color: aINK }}>+226</span>
              <svg width="8" height="8" viewBox="0 0 8 8"><path d="M1 3 L4 6 L7 3" stroke={aDIM} strokeWidth="1.4" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
            </div>
            <div style={{ flex: 1, padding: '12px 14px', background: aBG2, border: `1px solid ${aLINE2}`, borderRadius: 10, fontFamily: aFONT.mono, fontSize: 13, color: aINK, letterSpacing: '0.08em' }}>70 12 34 56</div>
          </div>
        </div>

        <div style={{ marginTop: 12 }}>
          <label style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.12em' }}>EMAIL <span style={{ color: aDIM2 }}>· OPTIONNEL</span></label>
          <div style={{ marginTop: 6, padding: '12px 14px', background: aBG2, border: `1px solid ${aLINE2}`, borderRadius: 10, fontSize: 13, color: aDIM2 }}>karim@example.com</div>
        </div>

        <div style={{ marginTop: 12 }}>
          <label style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.12em' }}>PRÉNOM</label>
          <div style={{ marginTop: 6, padding: '12px 14px', background: aBG2, border: `1px solid ${aLINE2}`, borderRadius: 10, fontSize: 13, color: aINK }}>Karim</div>
        </div>

        {/* SMS toggle */}
        <div style={{ marginTop: 14, padding: '12px 14px', background: aBG2, border: `1px solid ${aLINE}`, borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <div style={{ fontSize: 13, color: aINK, fontWeight: 600 }}>Recevoir le coupon par SMS</div>
            <div style={{ fontFamily: aFONT.mono, fontSize: 10, color: aDIM, marginTop: 2, letterSpacing: '0.05em' }}>+ notification push</div>
          </div>
          <Toggle on={true} />
        </div>

        {/* OR */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '18px 0' }}>
          <div style={{ flex: 1, height: 1, background: aLINE }} />
          <span style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.15em' }}>OU</span>
          <div style={{ flex: 1, height: 1, background: aLINE }} />
        </div>

        <button style={{ width: '100%', height: 46, background: '#1877f2', color: '#fff', border: 'none', borderRadius: 10, fontFamily: aFONT.title, fontSize: 13, letterSpacing: '0.05em', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }}>
          <span style={{ width: 22, height: 22, background: '#fff', color: '#1877f2', borderRadius: 4, display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 900 }}>f</span>
          CONTINUER AVEC FACEBOOK
        </button>
      </div>

      <div style={{ padding: '12px 24px 36px', background: aBG }}>
        <button style={{ width: '100%', height: 52, background: aACCENT, color: aBG, border: 'none', borderRadius: 12, fontFamily: aFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>ENVOYER LE CODE OTP →</button>
        <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.1em', textAlign: 'center', marginTop: 10, lineHeight: 1.4 }}>
          En continuant tu acceptes nos<br/>
          <u>CGU</u> et <u>Politique de confidentialité</u>
        </div>
        <div style={{ marginTop: 12 }}><Dots count={7} active={5} /></div>
      </div>
    </div>
  );
}

// ── Screen 07 · OTP ──────────────────────────────────────────────────────────
function OnboardOTP() {
  const code = ['7', '3', '8', '', '', ''];
  return (
    <div style={{ background: aBG, color: aINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: aFONT.ui, paddingTop: 90 }}>
      <div style={{ padding: '8px 24px 0', flex: 1 }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: aACCENT, marginBottom: 12 }}>VÉRIFICATION</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 26, letterSpacing: '-0.03em', lineHeight: 1.1 }}>
          Code envoyé au<br/>
          <span style={{ fontFamily: aFONT.mono, fontSize: 18, color: aACCENT, letterSpacing: '0.05em' }}>+226 70 12 34 56</span>
        </div>
        <div style={{ fontSize: 13, color: aINK2, marginTop: 12, lineHeight: 1.45 }}>
          Entre le code à 6 chiffres reçu par SMS.
        </div>

        {/* OTP fields */}
        <div style={{ display: 'flex', gap: 8, marginTop: 32, justifyContent: 'center' }}>
          {code.map((c, i) => {
            const active = i === code.findIndex(x => !x);
            const filled = !!c;
            return (
              <div key={i} style={{
                width: 46, height: 56, borderRadius: 10,
                background: aBG2,
                border: `1.5px solid ${active ? aACCENT : filled ? aLINE2 : aLINE}`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontFamily: aFONT.mono, fontSize: 24, fontWeight: 700, color: aINK,
                position: 'relative',
              }}>
                {c}
                {active && <span style={{
                  position: 'absolute', bottom: 14, left: '50%', transform: 'translateX(-50%)',
                  width: 2, height: 22, background: aACCENT,
                }} />}
              </div>
            );
          })}
        </div>

        <div style={{ textAlign: 'center', marginTop: 24, fontFamily: aFONT.mono, fontSize: 11, color: aDIM, letterSpacing: '0.1em' }}>
          Renvoyer le code dans <span style={{ color: aINK }}>0:48</span>
        </div>

        <div style={{ textAlign: 'center', marginTop: 12, fontFamily: aFONT.mono, fontSize: 10, color: aACCENT, letterSpacing: '0.12em' }}>
          CHANGER DE NUMÉRO
        </div>
      </div>

      <div style={{ padding: '0 24px 36px' }}>
        <button style={{ width: '100%', height: 52, background: aLINE2, color: aDIM, border: 'none', borderRadius: 12, fontFamily: aFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>VÉRIFIER →</button>

        <div style={{ display: 'flex', alignItems: 'center', gap: 8, justifyContent: 'center', marginTop: 16 }}>
          <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><rect x="2" y="5" width="7" height="5" rx="0.5" stroke={aDIM} strokeWidth="0.9"/><path d="M3.5 5 v-1.5 a2 2 0 0 1 4 0 v1.5" stroke={aDIM} strokeWidth="0.9" fill="none"/></svg>
          <span style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.12em' }}>CODE VALABLE 10 MIN · JAMAIS PARTAGÉ</span>
        </div>

        <div style={{ marginTop: 14 }}><Dots count={7} active={6} /></div>
      </div>
    </div>
  );
}

// ── Screen 08 · Bookmaker selection ──────────────────────────────────────────
// Real bookmakers rendered as styled logo cards, grouped by region.
function OnboardBookmaker() {
  const selected = new Set(['1xbet']);

  // Each bookmaker = stylised logo block (color + wordmark) — real brand colors.
  const Logo = ({ kind }) => {
    const styles = {
      ui: { fontFamily: 'system-ui, -apple-system, sans-serif', letterSpacing: '-0.02em' },
    };
    switch (kind) {
      case '1xbet':
        return (
          <div style={{ width: '100%', height: 56, background: '#0f6ec4', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 2 }}>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 22, fontWeight: 900, fontStyle: 'italic' }}>1x</span>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 18, fontWeight: 700 }}>BET</span>
          </div>
        );
      case 'betwinner':
        return (
          <div style={{ width: '100%', height: 56, background: '#0e0e0e', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4 }}>
            <span style={{ ...styles.ui, color: '#f4c83f', fontSize: 16, fontWeight: 900, fontStyle: 'italic' }}>BET</span>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 16, fontWeight: 900, fontStyle: 'italic' }}>WINNER</span>
          </div>
        );
      case 'melbet':
        return (
          <div style={{ width: '100%', height: 56, background: '#005028', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 18, fontWeight: 900, fontStyle: 'italic' }}>mel</span>
            <span style={{ ...styles.ui, color: '#f4c83f', fontSize: 18, fontWeight: 900, fontStyle: 'italic' }}>bet</span>
          </div>
        );
      case 'premier':
        return (
          <div style={{ width: '100%', height: 56, background: '#003c1e', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4 }}>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 14, fontWeight: 900 }}>PREMIER</span>
            <span style={{ ...styles.ui, color: '#ffce00', fontSize: 14, fontWeight: 900 }}>BET</span>
          </div>
        );
      case '22bet':
        return (
          <div style={{ width: '100%', height: 56, background: '#000', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 3 }}>
            <span style={{ ...styles.ui, color: '#ff6900', fontSize: 22, fontWeight: 900 }}>22</span>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 18, fontWeight: 700 }}>bet</span>
          </div>
        );
      case 'bet365':
        return (
          <div style={{ width: '100%', height: 56, background: '#02844a', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#ffd200', fontSize: 18, fontWeight: 900, fontStyle: 'italic' }}>bet</span>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 18, fontWeight: 900, fontStyle: 'italic' }}>365</span>
          </div>
        );
      case 'bwin':
        return (
          <div style={{ width: '100%', height: 56, background: '#161616', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#d4a73c', fontSize: 22, fontWeight: 800 }}>bwin</span>
          </div>
        );
      case 'winamax':
        return (
          <div style={{ width: '100%', height: 56, background: '#c41e1e', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#fff', fontSize: 18, fontWeight: 900 }}>winamax</span>
          </div>
        );
      case 'betclic':
        return (
          <div style={{ width: '100%', height: 56, background: '#fff', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#d1132e', fontSize: 18, fontWeight: 900 }}>Betclic</span>
          </div>
        );
      case 'unibet':
        return (
          <div style={{ width: '100%', height: 56, background: '#147149', borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <span style={{ ...styles.ui, color: '#ffe000', fontSize: 18, fontWeight: 900, fontStyle: 'italic' }}>unibet</span>
          </div>
        );
      default:
        return <div style={{ width: '100%', height: 56, background: aBG3 }} />;
    }
  };

  const Card = ({ id, name, bonus, recommended }) => {
    const isOn = selected.has(id);
    return (
      <div style={{
        padding: 10,
        background: isOn ? aBG3 : aBG2,
        border: `1px solid ${isOn ? aACCENT : aLINE}`,
        borderRadius: 12, position: 'relative',
      }}>
        <Logo kind={id} />
        <div style={{ marginTop: 8, fontSize: 11, color: aDIM, lineHeight: 1.35 }}>{bonus}</div>
        {recommended && (
          <div style={{ position: 'absolute', top: -8, left: 10, padding: '2px 7px', background: aACCENT, color: aBG, borderRadius: 4, fontFamily: aFONT.mono, fontSize: 8, fontWeight: 700, letterSpacing: '0.12em' }}>RECO</div>
        )}
        {isOn && (
          <div style={{ position: 'absolute', top: 8, right: 8, width: 18, height: 18, borderRadius: 9, background: aACCENT, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <svg width="10" height="10" viewBox="0 0 10 10"><path d="M1.5 5 L4 7.5 L8.5 2.5" stroke={aBG} strokeWidth="2" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
          </div>
        )}
      </div>
    );
  };

  return (
    <div style={{ background: aBG, color: aINK, height: '100%', display: 'flex', flexDirection: 'column', fontFamily: aFONT.ui, paddingTop: 78 }}>
      <div style={{ padding: '8px 24px 0', flex: 1, overflow: 'auto' }}>
        <div style={{ fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.18em', color: aACCENT, marginBottom: 12 }}>08 — BOOKMAKER</div>
        <div style={{ fontFamily: aFONT.title, fontSize: 26, letterSpacing: '-0.03em', lineHeight: 1.05 }}>
          Quel bookmaker<br/>tu utilises ?
        </div>
        <div style={{ fontSize: 13, color: aINK2, marginTop: 10, lineHeight: 1.45 }}>
          On t'enverra le coupon avec les liens directs vers ton bookmaker. Tu peux en sélectionner plusieurs.
        </div>

        {/* Region pills */}
        <div style={{ display: 'flex', gap: 6, marginTop: 18, overflowX: 'auto' }}>
          {['AFRIQUE DE L\'OUEST', 'EUROPE', 'MONDE'].map((r, i) => (
            <span key={r} style={{
              padding: '6px 11px', borderRadius: 999, flexShrink: 0,
              background: i === 0 ? aACCENT : aBG2,
              color: i === 0 ? aBG : aDIM,
              border: i === 0 ? 'none' : `1px solid ${aLINE2}`,
              fontFamily: aFONT.mono, fontSize: 9, letterSpacing: '0.1em', fontWeight: 700,
            }}>{r}</span>
          ))}
        </div>

        {/* Afrique Ouest */}
        <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.18em', marginTop: 18, marginBottom: 10 }}>POPULAIRES EN AFRIQUE DE L'OUEST</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 10 }}>
          <Card id="1xbet"     name="1xBet"     bonus="Bonus 100% jusqu'à 100€"   recommended />
          <Card id="betwinner" name="Betwinner" bonus="Bonus 130% jusqu'à 130€" />
          <Card id="melbet"    name="Melbet"    bonus="Bonus 100% jusqu'à 100€" />
          <Card id="premier"   name="Premier Bet" bonus="Bonus 50% jusqu'à 50€" />
          <Card id="22bet"     name="22Bet"     bonus="Bonus 100% jusqu'à 122€" />
        </div>

        <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM, letterSpacing: '0.18em', marginTop: 18, marginBottom: 10 }}>EUROPE</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 10 }}>
          <Card id="bet365"  name="bet365"  bonus="Cotes parmi les meilleures" />
          <Card id="bwin"    name="bwin"    bonus="Bonus 100€ premier dépôt" />
          <Card id="winamax" name="Winamax" bonus="Cashback 100€ remboursé" />
          <Card id="betclic" name="Betclic" bonus="Bonus 100€ pour les nouveaux" />
          <Card id="unibet"  name="Unibet"  bonus="50€ remboursés en cash" />
        </div>

        <div style={{ fontFamily: aFONT.mono, fontSize: 9, color: aDIM2, letterSpacing: '0.1em', textAlign: 'center', padding: '20px 0 4px', lineHeight: 1.5 }}>
          LIENS AFFILIÉS · JEU RESPONSABLE · 18+
        </div>
      </div>

      <div style={{ padding: '12px 24px 36px', background: aBG }}>
        <button style={{ width: '100%', height: 52, background: aACCENT, color: aBG, border: 'none', borderRadius: 12, fontFamily: aFONT.title, fontSize: 14, letterSpacing: '0.05em' }}>CONTINUER →</button>
        <button style={{ width: '100%', height: 36, background: 'transparent', color: aDIM, border: 'none', fontFamily: aFONT.mono, fontSize: 10, letterSpacing: '0.15em', marginTop: 4 }}>JE N'AI PAS DE BOOKMAKER</button>
        <div style={{ marginTop: 8 }}><Dots count={8} active={7} /></div>
      </div>
    </div>
  );
}

Object.assign(window, { OnboardLeagues, OnboardRisk, OnboardAccount, OnboardOTP, OnboardBookmaker });
