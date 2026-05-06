// Données de test pour l'écran détail match (style FlashScore)

export const SEED_MATCH_DETAIL = {
  id: "m1",
  status: "upcoming", // upcoming | live | finished
  minute: null,
  home_team: "Arsenal",
  away_team: "Chelsea",
  home_score: null,
  away_score: null,
  home_logo: "🔴",
  away_logo: "🔵",
  league: "Premier League",
  league_flag: "🏴󠁧󠁢󠁥󠁮󠁧󠁿",
  match_date: new Date().toISOString(),
  venue: "Emirates Stadium, Londres",
  referee: "Michael Oliver",

  // Cotes bookmakers
  odds: [
    { bookmaker: "Betwinner", logo: "BW", home: 2.10, draw: 3.40, away: 3.50, link: "https://betwinner.com" },
    { bookmaker: "1xBet",     logo: "1X", home: 2.15, draw: 3.35, away: 3.45, link: "https://1xbet.com" },
    { bookmaker: "Melbet",    logo: "MB", home: 2.05, draw: 3.40, away: 3.55, link: "https://melbet.com" },
    { bookmaker: "Betway",    logo: "BW", home: 2.00, draw: 3.30, away: 3.60, link: "https://betway.com" },
    { bookmaker: "Parimatch", logo: "PM", home: 2.12, draw: 3.38, away: 3.48, link: "https://parimatch.com" },
  ],

  // Notre pronostic IA
  prediction: {
    pick: "1X",
    odds: 1.72,
    confidence: 4,
    analysis: "Arsenal reste invaincu à domicile cette saison (8V 2N). Chelsea encaisse en moyenne 1.8 but/match en déplacement. L'algorithme privilégie une victoire ou un nul Arsenal.",
  },

  // Stats du match (si en cours / terminé)
  stats: [
    { label: "Possession", home: "58%", away: "42%", home_val: 58 },
    { label: "Tirs", home: "14", away: "8", home_val: 64 },
    { label: "Tirs cadrés", home: "6", away: "3", home_val: 67 },
    { label: "Passes", home: "487", away: "342", home_val: 59 },
    { label: "Fautes", home: "9", away: "14", home_val: 39 },
    { label: "Corners", home: "7", away: "3", home_val: 70 },
    { label: "Hors-jeu", home: "2", away: "4", home_val: 33 },
  ],

  // H2H (5 derniers matchs)
  h2h: [
    { date: "2024-04-23", home: "Arsenal", away: "Chelsea", score: "5 - 0", winner: "home" },
    { date: "2023-10-21", home: "Chelsea", away: "Arsenal", score: "2 - 2", winner: "draw" },
    { date: "2023-04-04", home: "Arsenal", away: "Chelsea", score: "3 - 1", winner: "home" },
    { date: "2022-11-06", home: "Chelsea", away: "Arsenal", score: "0 - 1", winner: "away" },
    { date: "2022-04-20", home: "Arsenal", away: "Chelsea", score: "4 - 2", winner: "home" },
  ],

  // Forme récente
  home_form: ["W", "W", "D", "W", "L"],
  away_form: ["L", "W", "W", "D", "W"],

  // Compositions
  home_lineup: {
    formation: "4-3-3",
    players: [
      { number: 1,  name: "Raya",      position: "GK",  rating: 7.2, value: "€25M" },
      { number: 2,  name: "Ben White", position: "RB",  rating: 7.5, value: "€50M" },
      { number: 12, name: "Timber",    position: "LB",  rating: 7.3, value: "€40M" },
      { number: 4,  name: "White",     position: "CB",  rating: 7.4, value: "€55M" },
      { number: 6,  name: "Gabriel",   position: "CB",  rating: 7.6, value: "€60M" },
      { number: 29, name: "Havertz",   position: "CM",  rating: 7.1, value: "€65M" },
      { number: 8,  name: "Ødegaard", position: "CAM", rating: 8.2, value: "€120M" },
      { number: 35, name: "Zinchenko", position: "CM",  rating: 7.0, value: "€30M" },
      { number: 11, name: "Martinelli",position: "LW",  rating: 7.8, value: "€80M" },
      { number: 9,  name: "Jesus",     position: "ST",  rating: 7.3, value: "€45M" },
      { number: 7,  name: "Saka",      position: "RW",  rating: 8.5, value: "€150M" },
    ],
  },
  away_lineup: {
    formation: "4-2-3-1",
    players: [
      { number: 13, name: "Sánchez",  position: "GK",  rating: 6.8, value: "€12M" },
      { number: 28, name: "Gusto",    position: "RB",  rating: 7.1, value: "€30M" },
      { number: 3,  name: "Cucurella",position: "LB",  rating: 7.0, value: "€20M" },
      { number: 26, name: "Disasi",   position: "CB",  rating: 7.2, value: "€38M" },
      { number: 5,  name: "Chalobah", position: "CB",  rating: 6.9, value: "€15M" },
      { number: 8,  name: "Caicedo",  position: "CDM", rating: 7.8, value: "€115M" },
      { number: 4,  name: "Gallagher",position: "CDM", rating: 7.2, value: "€40M" },
      { number: 22, name: "Mudryk",   position: "LW",  rating: 7.0, value: "€70M" },
      { number: 10, name: "Pulisic",  position: "CAM", rating: 7.3, value: "€35M" },
      { number: 17, name: "Sterling", position: "RW",  rating: 7.1, value: "€25M" },
      { number: 9,  name: "Jackson",  position: "ST",  rating: 7.4, value: "€45M" },
    ],
  },

  // Absents / blessés
  home_absent: [
    { name: "Saliba", reason: "Blessure musculaire", return: "2 semaines" },
    { name: "Tomiyasu", reason: "Genou", return: "1 mois" },
  ],
  away_absent: [
    { name: "Reece James", reason: "Blessure genou", return: "Inconnu" },
    { name: "Chilwell", reason: "Ligament", return: "3 mois" },
  ],
};

export const SEED_MATCH_LIVE = {
  ...SEED_MATCH_DETAIL,
  id: "m2",
  status: "live",
  minute: 67,
  home_team: "PSG",
  away_team: "Lyon",
  home_score: 2,
  away_score: 1,
  home_logo: "🔴",
  away_logo: "🔵",
  league: "Ligue 1",
  league_flag: "🇫🇷",
  events: [
    { minute: 23, type: "goal", team: "home", player: "Mbappé", assist: "Dembélé" },
    { minute: 45, type: "goal", team: "away", player: "Lacazette", assist: "Cherki" },
    { minute: 61, type: "goal", team: "home", player: "Dembélé", assist: "Mbappé" },
    { minute: 38, type: "yellow", team: "away", player: "Tolisso", assist: null },
    { minute: 55, type: "yellow", team: "home", player: "Marquinhos", assist: null },
  ],
};
