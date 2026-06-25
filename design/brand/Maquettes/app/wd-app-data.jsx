// COTA Web — données des pages de l'espace visiteur.
// S'appuie sur window.MATCHES / window.TEAMS. Tout exposé sur window.

// ── Prédictions (liste) — on enrichit MATCHES avec value + statut ──────────────
const WD_PREDICTIONS = [
  { matchId: 'rma-bay', pickType: 'Note A · Real ML',     value: 'ÉLEVÉE', kickoff: 'Auj. 21:00', conf: 91, odds: 1.55, statut: 'upcoming' },
  { matchId: 'psg-om',  pickType: 'Victoire PSG',          value: 'ÉLEVÉE', kickoff: 'Auj. 21:00', conf: 87, odds: 1.65, statut: 'upcoming' },
  { matchId: 'liv-ars', pickType: 'Plus de 2.5 buts',      value: 'MOYENNE', kickoff: 'Live · 58\u2032', conf: 76, odds: 1.78, statut: 'live', live: { score: '1-1', minute: "58'" } },
  { matchId: 'asm-ol',  pickType: 'Monaco +1.5 but',       value: 'MOYENNE', kickoff: 'Dem. 17:00', conf: 68, odds: 1.42, statut: 'upcoming' },
  { matchId: 'lil-nic', pickType: 'Moins de 2.5 buts',     value: 'FAIBLE', kickoff: 'Dem. 15:00', conf: 62, odds: 1.92, statut: 'upcoming' },
];

// ── Live (matchs en cours) ─────────────────────────────────────────────────────
const WD_LIVE = [
  { matchId: 'liv-ars', minute: "58'", score: '1-1', period: '2e MT', pickType: 'Plus de 2.5 buts', conf: 76, status: 'En bonne voie', statusTone: 'win',  momentum: 62 },
  { matchId: 'asm-ol',  minute: "23'", score: '1-0', period: '1re MT', pickType: 'Monaco +1.5 but',   conf: 68, status: 'En cours',     statusTone: 'cool', momentum: 54 },
  { matchId: 'lil-nic', minute: "71'", score: '0-1', period: '2e MT', pickType: 'Moins de 2.5 buts',  conf: 62, status: 'Sous pression', statusTone: 'loss', momentum: 38 },
];

// ── Compétitions ────────────────────────────────────────────────────────────────
const WD_COMPETS = [
  { id: 'l1',  name: 'Ligue 1',         pays: 'France',      mono: 'L1',  matches: 9, hot: true,  win: '74%', color: '#0a2c5e' },
  { id: 'ucl', name: 'Champions League', pays: 'Europe',     mono: 'UCL', matches: 6, hot: true,  win: '71%', color: '#11103a' },
  { id: 'pl',  name: 'Premier League',  pays: 'Angleterre',  mono: 'PL',  matches: 10, hot: false, win: '69%', color: '#36003c' },
  { id: 'lga', name: 'La Liga',         pays: 'Espagne',     mono: 'LGA', matches: 8, hot: false, win: '67%', color: '#1a1a2e' },
  { id: 'sea', name: 'Serie A',         pays: 'Italie',      mono: 'SA',  matches: 7, hot: false, win: '65%', color: '#0b3d2e' },
  { id: 'bun', name: 'Bundesliga',      pays: 'Allemagne',   mono: 'BUN', matches: 6, hot: false, win: '70%', color: '#3a1a1a' },
];

// ── Favoris ──────────────────────────────────────────────────────────────────
const WD_FAV_TEAMS = ['PSG', 'RMA', 'LIV', 'ASM'];
const WD_FAV_PREDS = ['rma-bay', 'psg-om'];

// ── Statistiques (perf mensuelle + par compétition + par type) ─────────────────
const WD_STATS_MONTHLY = [
  { m: 'Jan', roi: 8 },  { m: 'Fév', roi: 12 }, { m: 'Mar', roi: -4 },
  { m: 'Avr', roi: 16 }, { m: 'Mai', roi: 22 }, { m: 'Juin', roi: 18 },
];
const WD_STATS_BY_COMP = [
  { name: 'Ligue 1',          win: 76, n: 84 },
  { name: 'Champions League', win: 71, n: 38 },
  { name: 'Premier League',   win: 68, n: 56 },
  { name: 'La Liga',          win: 64, n: 41 },
];
const WD_STATS_BY_TYPE = [
  { name: 'Résultat (1N2)',   win: 74, n: 96 },
  { name: 'Plus/Moins buts',  win: 70, n: 71 },
  { name: 'Double chance',    win: 81, n: 34 },
  { name: 'Buteurs',          win: 58, n: 46 },
];
const WD_STATS_KPIS = [
  { id: 'n',   value: '247',    label: 'Prédictions jouées', icon: 'coupon', tone: 'lime' },
  { id: 'win', value: '72.1%',  label: 'Taux de réussite',   icon: 'target', tone: 'lime' },
  { id: 'roi', value: '+18.5%', label: 'ROI cumulé',         icon: 'trend',  tone: 'cool' },
  { id: 'str', value: '6',      label: 'Série en cours',     icon: 'flame',  tone: 'cool' },
];

// ── Abonnement (plans) ──────────────────────────────────────────────────────────
const WD_PLANS = [
  { id: 'free', name: 'Découverte', price: '0', period: '', tagline: 'Pour tester COTA',
    features: ['1 prédiction par jour', 'Score de confiance', 'Historique 7 jours'],
    cta: 'Plan actuel', current: false, highlight: false },
  { id: 'pro', name: 'Pro', price: '19', period: '/ mois', tagline: 'Le choix des parieurs réguliers',
    features: ['Toutes les prédictions', 'Analyse des 9 critères', 'Combinés du jour', 'Matchs en live', 'Historique illimité'],
    cta: 'Plan actuel', current: true, highlight: true },
  { id: 'elite', name: 'Elite', price: '49', period: '/ mois', tagline: 'Performance maximale',
    features: ['Tout le plan Pro', 'Prédictions value premium', 'Alertes temps réel', 'Stats avancées & exports', 'Accompagnement dédié'],
    cta: 'Passer Elite', current: false, highlight: false },
];

// ── Parrainage ───────────────────────────────────────────────────────────────
const WD_REFERRAL = {
  code: 'MARC-COTA26',
  earned: '90 €',
  pending: '30 €',
  invited: 6,
  converted: 3,
  friends: [
    { initials: 'TL', name: 'Thomas L.', status: 'Abonné Pro', reward: '+30 €', tone: 'win' },
    { initials: 'NF', name: 'Nadia F.',  status: 'Abonné Pro', reward: '+30 €', tone: 'win' },
    { initials: 'YB', name: 'Yanis B.',  status: 'Inscrit',    reward: 'En attente', tone: 'cool' },
    { initials: 'CL', name: 'Chloé L.',  status: 'Invité',     reward: '—', tone: 'dim' },
  ],
};

// ── Profil ───────────────────────────────────────────────────────────────────
const WD_PROFILE = {
  email: 'marc.dubois@email.com',
  phone: '+33 6 12 34 56 78',
  joined: 'Janvier 2025',
  prefs: [
    { label: 'Notifications push', on: true },
    { label: 'Résumé quotidien par e-mail', on: true },
    { label: 'Alertes matchs live', on: false },
    { label: 'Jeu responsable — limite de mise', on: true },
  ],
  competitions: ['Ligue 1', 'Champions League', 'Premier League'],
};

Object.assign(window, {
  WD_PREDICTIONS, WD_LIVE, WD_COMPETS, WD_FAV_TEAMS, WD_FAV_PREDS,
  WD_STATS_MONTHLY, WD_STATS_BY_COMP, WD_STATS_BY_TYPE, WD_STATS_KPIS,
  WD_PLANS, WD_REFERRAL, WD_PROFILE,
});
