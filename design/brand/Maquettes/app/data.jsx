// Realistic match data — Ligue 1 / Champions League 2025-26 (current season).
// Two-letter codes + plausible team-color pairs to keep the visual rhythm.

const TEAMS = {
  PSG:  { code: 'PSG', name: 'Paris SG',     color: '#0a3b73', text: '#fff', short: 'PSG' },
  OM:   { code: 'OM',  name: 'Olympique de Marseille', color: '#2faee0', text: '#fff', short: 'OM' },
  OL:   { code: 'OL',  name: 'Lyon',         color: '#0c2f5b', text: '#fff', short: 'OL' },
  ASM:  { code: 'AS',  name: 'Monaco',       color: '#e2001a', text: '#fff', short: 'ASM' },
  LOSC: { code: 'LIL', name: 'Lille',        color: '#d52020', text: '#fff', short: 'LIL' },
  NICE: { code: 'NIC', name: 'Nice',         color: '#cc1c2b', text: '#fff', short: 'OGCN' },
  RMA:  { code: 'RMA', name: 'Real Madrid',  color: '#fffdf7', text: '#0b0d10', short: 'RMA' },
  BAY:  { code: 'BAY', name: 'Bayern',       color: '#dc052d', text: '#fff', short: 'FCB' },
  LIV:  { code: 'LIV', name: 'Liverpool',    color: '#c8102e', text: '#fff', short: 'LIV' },
  ARS:  { code: 'ARS', name: 'Arsenal',      color: '#ef0107', text: '#fff', short: 'ARS' },
  MUN:  { code: 'MUN', name: 'Man United',   color: '#da291c', text: '#fff', short: 'MUN' },
  MCI:  { code: 'MCI', name: 'Man City',     color: '#6cabdd', text: '#fff', short: 'MCI' },
};

const MATCHES = [
  {
    id: 'psg-om',
    home: 'PSG', away: 'OM',
    competition: 'Ligue 1', round: 'J34',
    kickoff: '21:00', date: 'Mar 18 mai',
    pick: { type: 'Victoire PSG', odds: 1.65 },
    score: '2-1',
    confidence: 87,
    odds: { home: 1.65, draw: 4.20, away: 4.50 },
    criteria: [
      { name: 'Forme (5 derniers)',  value: '4V 1N',     signal: 'pro', detail: 'PSG : invaincu · OM : 2D' },
      { name: 'Confrontations directes',  value: '6-2-2',    signal: 'pro', detail: 'PSG sur les 10 derniers' },
      { name: 'Domicile vs Extérieur',  value: '89% V',     signal: 'pro', detail: 'PSG à domicile cette saison' },
      { name: 'Blessures clés',     value: '0 vs 2',    signal: 'pro', detail: 'OM : Aubameyang, Veretout out' },
      { name: 'Météo',              value: 'Sec, 14°',  signal: 'neutral' },
      { name: 'Indices du marché',    value: '1.65 / 4.20 / 4.50', signal: 'neutral' },
      { name: 'Cartons (moy.)',     value: '2.8',       signal: 'neutral', detail: 'sur les 5 derniers PSG' },
      { name: 'Possession attendue',value: '64%',       signal: 'pro' },
      { name: 'Buts attendus (xG)', value: '2.8 - 1.1', signal: 'pro' },
    ],
  },
  {
    id: 'liv-ars',
    home: 'LIV', away: 'ARS',
    competition: 'Premier League', round: 'GW37',
    kickoff: '18:30', date: 'Mar 18 mai',
    pick: { type: 'Indice +', odds: 1.78 },
    score: '2-2',
    confidence: 76,
    odds: { home: 2.30, draw: 3.40, away: 2.95 },
  },
  {
    id: 'rma-bay',
    home: 'RMA', away: 'BAY',
    competition: 'Champions League', round: '1/2 finale',
    kickoff: '21:00', date: 'Mer 19 mai',
    pick: { type: 'Note A', odds: 1.55 },
    score: '2-1',
    confidence: 91,
    odds: { home: 1.95, draw: 3.80, away: 3.60 },
  },
  {
    id: 'asm-ol',
    home: 'ASM', away: 'OL',
    competition: 'Ligue 1', round: 'J34',
    kickoff: '17:00', date: 'Mer 19 mai',
    pick: { type: 'Monaco 1.5+ buts', odds: 1.42 },
    score: '2-0',
    confidence: 68,
    odds: { home: 1.70, draw: 4.00, away: 4.80 },
  },
  {
    id: 'lil-nic',
    home: 'LOSC', away: 'NICE',
    competition: 'Ligue 1', round: 'J34',
    kickoff: '15:00', date: 'Mer 19 mai',
    pick: { type: 'Indice −', odds: 1.92 },
    score: '1-0',
    confidence: 62,
    odds: { home: 2.10, draw: 3.20, away: 3.50 },
  },
];

// The 3 sélections that make today's combined coupon.
const COUPON = {
  date: 'Mar 18 mai',
  sélections: [
    { matchId: 'psg-om', label: 'PSG - OM',  type: 'Victoire PSG',  odds: 1.65, confidence: 87 },
    { matchId: 'liv-ars', label: 'LIV - ARS', type: 'indice +',     odds: 1.78, confidence: 76 },
    { matchId: 'rma-bay', label: 'RMA - BAY', type: 'Note A',       odds: 1.55, confidence: 91 },
  ],
  total: 4.55, // 1.65 × 1.78 × 1.55 ≈ 4.55
  stake: 10,
  confidence: 87,
};

window.TEAMS = TEAMS;
window.MATCHES = MATCHES;
window.COUPON = COUPON;
