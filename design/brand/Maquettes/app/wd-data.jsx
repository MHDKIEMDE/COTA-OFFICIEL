// COTA Web Dashboard — données de l'espace membre.
// Persona + KPIs + série de performance + avis + meilleurs carnets + historique.

const WD_USER = {
  name: 'Marc Dubois',
  city: 'Paris, France',
  plan: 'ABONNÉ PRO',
  since: 'Membre depuis janv. 2025',
  initials: 'MD',
};

// 4 KPI cards (style MaxLand : forme colorée + grand chiffre).
const WD_KPIS = [
  { id: 'carnets', value: '247',     label: 'Prédictions suivies', delta: '+12 ce mois', up: true,  tone: 'lime',  icon: 'coupon' },
  { id: 'roi',     value: '+18.5%',  label: 'ROI moyen',        delta: '+2.1 pt',     up: true,  tone: 'lime',  icon: 'trend' },
  { id: 'taux',    value: '72.1%',   label: 'Taux de réussite', delta: '+1.2 pt',     up: true,  tone: 'cool',  icon: 'target' },
  { id: 'gains',   value: '1 284 €', label: 'Gains cumulés',    delta: '+184 € (7j)', up: true,  tone: 'cool',  icon: 'wallet' },
];

// Série quotidienne de profit/perte (€) sur 28 jours — pour le graphe tweakable.
const WD_SERIES = [
  18, 32, -12, 44, 27, 9, -22, 38, 51, 14, -8, 29, 62, 41,
  -16, 24, 73, 35, 12, -19, 48, 57, 22, 39, -11, 64, 31, 46,
];

// Résumé (barres horizontales façon "Summary" MaxLand).
const WD_SUMMARY = [
  { label: 'Mises engagées', value: 2470, display: '2 470 €', tone: 'ink'  },
  { label: 'Gains bruts',    value: 3754, display: '3 754 €', tone: 'mint' },
  { label: 'Profit net',     value: 1284, display: '+1 284 €', tone: 'lime' },
];
const WD_SUMMARY_MAX = 4000;

// Avis récents (témoignages étoilés — colonne droite).
const WD_REVIEWS = [
  { name: 'Sofiane K.',  city: 'Lyon',      stars: 5, date: '2 juin 2026',  initials: 'SK', tone: 'lime',  text: 'Le carnet du jour est devenu mon rituel. Analyse claire, zéro baratin — +240 € sur le mois.' },
  { name: 'Émilie R.',   city: 'Bordeaux',  stars: 5, date: '31 mai 2026',  initials: 'ER', tone: 'cool',  text: 'Les 9 critères m\u2019aident à comprendre pourquoi, pas juste à parier. Discipline retrouvée.' },
  { name: 'Karim B.',    city: 'Lille',     stars: 4, date: '29 mai 2026',  initials: 'KB', tone: 'lime',  text: 'Interface nickel, indices de confiance fiables. J\u2019aimerais juste plus de Serie A.' },
];

// Meilleurs carnets de la semaine (transposé "agents en vedette").
const WD_TIPSTERS = [
  { id: 'combo-a', name: 'Combo Sécurité',  tag: '3 sélections',  roi: '+22%', win: '78%', odds: '@4.55', tone: 'lime'  },
  { id: 'combo-b', name: 'Value UCL',       tag: '2 sélections',  roi: '+31%', win: '69%', odds: '@3.20', tone: 'cool'  },
  { id: 'combo-c', name: 'Double Ligue 1',  tag: '2 sélections',  roi: '+17%', win: '74%', odds: '@2.74', tone: 'lime'  },
];

// Historique "Mes carnets" (transposé tableau "My Properties").
// statut : win | live | loss
const WD_HISTORY = [
  { matchId: 'psg-om',  type: 'Victoire PSG',        date: '2 juin 2026', odds: 1.65, stake: 20, statut: 'win',  gain: '+33,00 €' },
  { matchId: 'rma-bay', type: 'Note A · Real',       date: '2 juin 2026', odds: 1.55, stake: 25, statut: 'live', gain: '—' },
  { matchId: 'liv-ars', type: 'Indice + · Liv-Ars',  date: '1 juin 2026', odds: 1.78, stake: 15, statut: 'win',  gain: '+26,70 €' },
  { matchId: 'asm-ol',  type: 'Monaco 1.5+ buts',    date: '31 mai 2026', odds: 1.42, stake: 30, statut: 'loss', gain: '−30,00 €' },
  { matchId: 'lil-nic', type: 'Indice − · Lille',    date: '30 mai 2026', odds: 1.92, stake: 10, statut: 'win',  gain: '+19,20 €' },
];

window.WD_USER = WD_USER;
window.WD_KPIS = WD_KPIS;
window.WD_SERIES = WD_SERIES;
window.WD_SUMMARY = WD_SUMMARY;
window.WD_SUMMARY_MAX = WD_SUMMARY_MAX;
window.WD_REVIEWS = WD_REVIEWS;
window.WD_TIPSTERS = WD_TIPSTERS;
window.WD_HISTORY = WD_HISTORY;
