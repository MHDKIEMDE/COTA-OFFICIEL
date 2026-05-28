# COTA — Cahier des charges V2

> **Source de vérité unique.** Remplace : CDC V1 (2026-05-28), Amendements V1.1, Spécifications de détail V1.
> Date : 2026-05-28.
> Statut : version consolidée pour exécution avant lancement (9 juin 2026).
> Propriété : MHD SERVICE.

---

## SOMMAIRE

1. Vision produit
2. Personas & marché cible
3. Règles de communication (non négociables)
4. Stack technique
5. Architecture algorithme — modèle restaurant
6. Algorithme — critères & scoring
7. Algorithme — cascade multi-marchés
8. Algorithme — hybridation algo + source externe
9. Couche d'analyse IA
10. Gestion des cotes
11. Fonctionnalités V1 (périmètre)
12. Endpoints API
13. Interface mobile
14. Spécifications de détail par module
15. Dashboard admin
16. Jobs schedulés
17. Monétisation
18. Distribution & acquisition
19. Notifications
20. Sécurité, conformité, jeu responsable
21. Roadmap & phases (priorités avant 9 juin)
22. KPIs & analytics
23. Definitions of done
24. Évolutions V2+
25. Décisions en attente

---

## 1. Vision produit

### 1.1 Objectif

COTA est une application mobile de pronostics football assistée par algorithme, conçue pour un public mobile-first en Afrique de l'Ouest francophone, au Maghreb et dans la diaspora française.

- Aider l'utilisateur à mieux sélectionner ses paris grâce à une analyse structurée.
- Générer du revenu via Premium, affiliation bookmakers (RevShare) et parrainage.
- Rester crédible : ne pas publier un pronostic si la confiance est insuffisante.

### 1.2 Positionnement

- Plus sérieux qu'un canal Telegram de tipsters.
- Plus accessible qu'un outil de trading sportif complexe.
- Plus transparent qu'une simple liste de « coups sûrs ».

### 1.3 Promesse

Analyser mieux que l'intuition seule. Publier seulement quand la confiance est suffisante. Protéger l'utilisateur avec transparence, limites et jeu responsable.

### 1.4 Lancement

- **Date V1** : 9 juin 2026, juste avant la Coupe du Monde FIFA 2026.
- **Objectif principal au lancement** : inscriptions bookmakers (RevShare), pas abonnements Premium.

---

## 2. Personas & marché cible

### 2.1 Persona principal — Mamadou Diop, 28 ans

- **Géographie** : Sénégal, Côte d'Ivoire, Burkina Faso, Mali (urbain).
- **Profil** : homme 22–38 ans, salaire 100 000–300 000 FCFA/mois, Bac+.
- **Comportement** : mobile-first (90 % Android), WhatsApp, Facebook, Telegram, parie depuis 1–3 ans.
- **Douleurs** : perd souvent à l'intuition, pas le temps d'analyser, défiance envers les tipsters Telegram, budget limité.
- **Aspirations** : améliorer son taux, apprendre à analyser, outil pro à prix accessible.
- **Canaux** : Facebook groupes paris/foot, WhatsApp, Telegram, YouTube.

### 2.2 Marchés géographiques

| Priorité | Marché | Note |
|----------|--------|------|
| 1 | Sénégal, Côte d'Ivoire, Burkina, Mali | Cœur de cible |
| 2 | Maghreb | Marché secondaire |
| 3 | France (diaspora) | Opportunité affinité |

---

## 3. Règles de communication — non négociables

Ces règles s'appliquent à l'UI, au code, aux notifications, à l'analyse IA, au marketing.

- **Jamais** : « gain garanti », « 100 % », « pari sûr », « coup sûr ».
- La **confiance est un indicateur probabiliste**, pas une certitude.
- **Win rate cible 55–60 %** affiché honnêtement. Ne pas gonfler, ne pas mentir, ne pas masquer les pertes.
- **Avertissement jeu responsable** visible dans : écran abonnement, coupon, prédictions, FAQ, page de téléchargement.
- Si l'utilisateur exprime une perte de contrôle vis-à-vis du jeu → ne jamais l'inciter à parier davantage.
- Ne jamais inventer une statistique, un joueur, une donnée manquante. Si l'info manque, le dire.
- Identité visuelle COTA fixée : thème sombre, accents jaune néon, logo COTA souligné en jaune, bouton Coupon central en bottom nav. **Pas de refonte** : optimisations subtiles uniquement.

---

## 4. Stack technique

### 4.1 Backend

| Composant | Choix |
|-----------|-------|
| Framework | Laravel 12 |
| Langage | PHP 8.3 |
| Auth API | Sanctum |
| Cache | Redis |
| Queues | Redis + Horizon |
| Base de données | MySQL (prod) / SQLite (dev simple) |
| Admin | Blade + Tailwind CDN |

### 4.2 Mobile

| Composant | Choix |
|-----------|-------|
| Framework | Flutter 3.x |
| Langage | Dart |
| État | Riverpod |
| HTTP | Dio |
| Routing | GoRouter |
| Notifications | Firebase Core + FCM + Flutter Local Notifications |

### 4.3 Services externes

| Service | Rôle |
|---------|------|
| API-Football | Données matchs, stats, classements (primaire) |
| Sportradar | Fallback données football |
| Bet365 + 1xBet (live odds) | Source cotes temps réel (1xBet ≈ Betwinner ≈ Melbet) |
| Source externe pronostic | Signal d'hybridation (à confirmer §25) |
| LLM (Anthropic / OpenAI) | Analyse IA serveur (à confirmer §25) |
| OpenWeatherMap | Critère météo |
| Paydunya | Paiement Mobile Money (Wave, Orange Money, MTN, Moov) |
| FCM HTTP v1 | Notifications push |
| Termii (ou équiv.) | OTP fallback SMS |

### 4.4 Infrastructure

- Nginx.
- HTTPS obligatoire en production.
- Queue worker durable (pm2 ou supervisor).
- Scheduler Laravel durable.
- Docker Compose disponible (app, nginx, redis, queue, scheduler).

---

## 5. Architecture algorithme — modèle restaurant

Architecture mentale qui guide toutes les décisions techniques sur la production des pronostics.

### 5.1 Trois rôles séparés

| Rôle | Composant | Responsabilité |
|------|-----------|----------------|
| **Chef** | Algorithme + sources de données | Calcule les pronostics chiffrés. En cuisine, invisible. Ne décide pas de la publication. |
| **Passe-plat** | Règle de publication | Décision déterministe : confiance ≥ seuil **ET** cote dans la bande du marché. Pas d'IA. |
| **Serveur** | Couche d'analyse IA | Met en mots les pronostics validés. Ne touche jamais à la recette. Ne promet rien. |

### 5.2 Conséquences

- Le calcul reste mesurable, back-testable, déterministe.
- L'IA n'introduit pas de non-déterminisme dans le win rate.
- La présentation et la qualité sont découplées : on peut améliorer l'une sans casser l'autre.

---

## 6. Algorithme — critères & scoring

### 6.1 Formule générale

```
Score_Algo = Σ(Score_Critère_i × Poids_i)
Σ(Poids_i) = 100

Direction = SIGN(Score_Algo)
  + → favori domicile
  - → favori extérieur
```

### 6.2 Les 9 critères (V1)

| # | Critère | Poids |
|---|---------|------:|
| 1 | Forme récente (5 derniers matchs) | 25 % |
| 2 | Head-to-head (5 dernières confrontations) | 20 % |
| 3 | Performance domicile/extérieur | 15 % |
| 4 | Position au classement | 12 % |
| 5 | Statistiques de buts | 10 % |
| 6 | Horaire du match | 8 % |
| 7 | Météo | 5 % |
| 8 | Tirs cadrés | 3 % |
| 9 | Forme physique | 2 % |
| | **TOTAL** | **100 %** |

### 6.3 Détail des principaux critères

**Critère 1 — Forme récente (25 pts)**

```
V = 5 pts | N = 2.5 pts | D = 0 pt
Score = (Diff_Forme / 25) × 25
Bonus : +2 si V5 consécutifs | -2 si D5 consécutifs
```

**Critère 2 — Head-to-Head (20 pts)**

```
Score = (Diff_Victoires_H2H / 5) × 20
Si < 3 H2H disponibles : critère désactivé, poids redistribué vers Forme.
```

**Critère 3 — Domicile/Extérieur (15 pts)**

```
Score = (Win_Rate_Dom_A - Win_Rate_Ext_B) × 15
Bonus : ±3 si invaincu à dom / aucune victoire ext
Clamp : [-15, +15]
```

**Critère 4 — Classement (12 pts)**

```
|Gap| ≥ 10 places → ±12 pts
|Gap| 5–9 → ±8 pts
|Gap| 3–4 → ±5 pts
|Gap| 1–2 → ±2 pts
Désactivé : compétitions internationales / début saison (< 5 journées).
```

**Critère 5 — Stats buts (10 pts)**

```
Sous-critères :
  Moyenne buts (4 pts)
  BTTS (3 pts)
  Clean Sheets (3 pts)
Clamp : [-10, +10]
```

**Critère 6 — Horaire (8 pts)**

```
Matinée 08–11h : -1
Après-midi 12–15h : 0
Prime time 16–19h : +2
Soirée 20–22h : 0
Nuit 23–01h : -3
Nuit profonde 02–07h : -5
Habitude horaire : +2 | Décalage > 3h : -2
Clamp : [0, 8]
```

**Critères 7–9 — Météo, Tirs cadrés, Forme physique**

Poids faibles (5–2 %). Impact marginal sur le score final. Enrichissent l'explication affichée.

### 6.4 Seuils de publication

| Score | Niveau | Accès | Étoiles |
|-------|--------|-------|:-------:|
| 85–100 | Très haute confiance | Premium | ⭐⭐⭐⭐ |
| 70–84 | Haute confiance | Premium | ⭐⭐⭐ |
| 60–69 | Confiance moyenne | Gratuit | ⭐⭐ |
| 50–59 | Confiance faible | Gratuit (zone audacieuse) | ⭐ |
| < 50 | Non publié | — | — |

### 6.5 Backtesting — règles

| Niveau | Win rate cible |
|--------|---------------|
| Très haute confiance | ≥ 70 % |
| Haute confiance | ≥ 60 % |
| Moyenne | ≥ 55 % |
| Faible | ≥ 50 % |
| Global | ≥ 55 % |

**Backtest obligatoire par type de pari séparément** avant de figer les seuils et les bandes de cote (§10.2). Voir piège §7.4.

---

## 7. Algorithme — cascade multi-marchés

### 7.1 Principe

L'algo ne cherche pas seulement « qui gagne » (1X2, le marché le plus difficile). Pour chaque match, il évalue **plusieurs marchés en parallèle** et publie celui où le signal est le plus fort et valide.

Reformulation : **« Qu'est-ce que je sais de plus sûr sur ce match ? »**

### 7.2 Deux moteurs de scoring spécialisés

Un seul score 0–100 ne convient pas à tous les marchés.

| Moteur | Marchés servis | Critères dominants |
|--------|----------------|--------------------|
| **Moteur Force** | 1X2, Double Chance, Handicap | Forme (1), H2H (2), Dom/Ext (3), Classement (4), Horaire (6) |
| **Moteur Buts** | Over/Under, BTTS | Stats buts (5) élevées au rôle principal, Tirs cadrés (8) en appoint |
| **Haute variance** | Score exact | Dernier recours, signal exceptionnel uniquement |

Le Moteur Buts réutilise les données du critère 5 (§6.3) mais en fait son entrée principale (≈ 70 % du poids), au lieu de 10 % dans le scoring orienté 1X2.

### 7.3 Sélecteur

```
1. Évaluer tous les marchés disponibles.
2. Filtrer : confiance ≥ seuil (§6.4) ET cote dans la bande du marché (§10.2).
3. Publier le MEILLEUR par équilibre confiance × valeur (cote).
   Pas la plus haute confiance seule (un Double Chance @ 1.15 ne vaut rien).
```

### 7.4 Précautions obligatoires

- **Données fiables uniquement** : n'activer un marché que si les données existent via API-Football / Sportradar. Ne pas proposer « tirs cadrés » si la donnée n'est pas fiable.
- **Piège des comparaisons multiples** : plus on teste de marchés, plus le risque de faux signal par hasard augmente. Mitigation : (1) chaque marché calculé sur des données réellement pertinentes pour lui ; (2) **backtest du win rate par type de pari séparément** avant de faire confiance à son seuil.

### 7.5 Bénéfice produit

Là où l'écran serait vide faute de pronostic 1X2, on a désormais souvent un pari honnête (Over 2.5, Double Chance…). Plus de matchs publiables → plus de clics liens bookmakers → plus de RevShare. Sans jamais mentir sur la qualité.

---

## 8. Algorithme — hybridation algo + source externe

### 8.1 Objectif

Stabiliser la qualité au lancement, pendant que l'algo interne n'est pas encore calibré par backtesting.

### 8.2 Formule

```
Score_Publié = Score_Algo × (1 - w_ext) + Score_Externe_normalisé × w_ext
```

- `w_ext` = poids du signal externe, **configurable dans AppConfig**.
- **Au lancement** : `w_ext` ≈ 0.30 à 0.40 (l'algo n'est pas encore prouvé).
- **Dans le temps** : `w_ext` réduit progressivement à mesure que le backtest (§6.5) démontre la fiabilité de l'algo. C'est le plan « revenir en force » de l'algo interne.

### 8.3 Normalisation

Le signal externe doit être ramené sur la même échelle 0–100 que l'algo **avant** tout mélange. Une probabilité externe (0–1) ou une cote doit être convertie en score comparable. Sinon le mélange n'a pas de sens mathématique.

### 8.4 Garde-fou désaccord

```
SI direction(Algo) ≠ direction(Externe) sur le résultat principal :
    → ne pas publier ce pari
    → OU rétrograder la confiance d'un palier
```

Élimine les paris où les deux sources se contredisent, statistiquement les plus risqués.

### 8.5 Traçabilité obligatoire

Stocker pour **chaque** prédiction : `score_algo`, `score_externe`, `score_publié`, `w_ext` utilisé, `résultat_réel`. Sans ce log, impossible de mesurer quand réduire `w_ext`.

---

## 9. Couche d'analyse IA — « le serveur »

### 9.1 Rôle strict

L'IA **n'effectue aucun calcul et ne prend aucune décision**. Elle met en mots les chiffres déjà produits par l'algo et le signal externe.

### 9.2 Contraintes de génération

Consigne verrouillée envoyée au LLM :

- Rédiger 3–4 phrases en français à partir **uniquement** des données fournies.
- Interdiction d'inventer une statistique, un joueur, une information absente.
- Interdiction des termes « garanti », « 100 % », « gain sûr ».
- Terminer par un rappel de jeu responsable.
- Filtrage de sortie : bloquer les mots interdits avant publication.

### 9.3 Implémentation

- Génération **une seule fois** par prédiction dans le pipeline (`GenerateAllPredictionsJob`, §16). Résultat stocké dans le champ `analysis`, mis en cache Redis.
- **Pas d'appel LLM au runtime utilisateur** (coût + latence).
- **Fallback obligatoire** : si l'API LLM échoue, générer par template (« Pronostic basé sur une forme récente de X et un avantage à domicile de Y % »). L'analyse IA est un bonus, pas une dépendance critique.

### 9.4 Hors périmètre V1

Recherche de contexte qualitatif par IA (blessures, compositions, enjeu) → V2.

---

## 10. Gestion des cotes

### 10.1 Source

- Cotes **temps réel** via **Bet365 + 1xBet**.
- **1xBet ≈ Betwinner ≈ Melbet** (marques sœurs du même groupe, moteur de cotes quasi identique) : les cotes 1xBet couvrent approximativement les trois affiliés avec une seule source.

### 10.2 Bandes de cotes par marché

Remplace tout plafond unique. Utilisé par le sélecteur (§7.3).

| Marché | Bande de cote valide |
|--------|----------------------|
| 1X2 / Double Chance | 1.40 – 3.50 |
| Handicap (asiatique) | 1.50 – 2.50 |
| Over/Under buts | 1.50 – 2.30 |
| BTTS | 1.50 – 2.30 |
| Score exact | 5.00 – 12.00 |

Valeurs de départ à affiner par backtest §6.5.

### 10.3 Affichage

- **La confiance est l'élément central** ; la cote est secondaire et **indicative**.
- Mention claire : « cote indicative, susceptible de varier — vérifiez chez le bookmaker ».
- Évite tout litige sur un décalage entre cote affichée et cote réelle au moment du pari.

### 10.4 Value betting — V2

Confiance 70 % ⇒ cote « juste » ≈ 1.43 ; si le bookmaker offre 1.80, c'est une *value bet*. Classement par valeur, pas seulement confiance. Reporté V2 (suppose cotes stabilisées).

---

## 11. Fonctionnalités V1 — périmètre

### 11.1 Inclus V1

| Module | Statut |
|--------|:------:|
| Authentification OTP / PIN / Facebook | ✅ |
| Profil utilisateur | ✅ |
| Pronostics du jour (cascade multi-marchés) | ✅ |
| Coupon IA — 3 variantes (prudent, équilibré, audacieux) | ✅ |
| Analyse IA serveur (rédaction) | ✅ |
| Hybridation algo + source externe | ✅ |
| Live scores | ✅ |
| Historique & statistiques | ✅ |
| Favoris | ✅ |
| Compétitions | ✅ |
| Détails match / équipe / joueur / prédiction | ✅ |
| Abonnement Premium + Paydunya | ✅ |
| Dashboard admin | ✅ |
| Bookmakers & affiliation (RevShare) | ✅ |
| Boucle 7j Premium ↔ inscription bookmaker | ✅ |
| Notifications push & in-app | ✅ |
| Parrainage de base | ✅ |
| Politique confidentialité + RGPD | ✅ |
| Smart Empty State | ✅ |

### 11.2 Hors V1 — V2+

- Marketplace caissiers/agents Mobile Money.
- Coaching premium humain.
- Marketplace pronostiqueurs tiers.
- API publique B2B.
- Multi-sports.
- Fantasy football.
- Live betting avancé.
- Crypto paiement.
- Pré-remplissage automatique des coupons (auto-fill WebView).
- Carte à gratter (gamification).
- Popup sélection bookmaker au premier lancement.
- Bonus de partage d'app.
- Recherche de contexte qualitatif par IA.
- Value betting.

---

## 12. Endpoints API

### 12.1 Publics

```
GET  /api/health
POST /api/auth/send-otp
POST /api/auth/verify-otp
POST /api/auth/facebook
POST /api/auth/check-phone
POST /api/auth/login-pin

GET  /api/predictions/today
GET  /api/predictions/coupon            (3 variantes : prudent, équilibré, audacieux)
GET  /api/predictions/competitions
GET  /api/predictions/search
GET  /api/predictions/{id}

GET  /api/matches/featured
GET  /api/matches/live
GET  /api/matches/today
GET  /api/matches/date/{date}
GET  /api/matches/{id}
GET  /api/matches/{id}/events
GET  /api/matches/{id}/stats
GET  /api/matches/{id}/lineups
GET  /api/matches/{id}/h2h

GET  /api/subscriptions/plans
GET  /api/bookmakers
GET  /api/bookmakers/by-region
GET  /api/bookmakers/blogs
GET  /api/config/app
```

### 12.2 Authentifiés (Sanctum)

```
GET    /api/auth/me
POST   /api/auth/logout
POST   /api/auth/complete-registration
POST   /api/auth/set-pin                (création PIN après OTP)
POST   /api/auth/reset-pin              (réinit PIN après re-OTP)

GET    /api/predictions/history
GET    /api/predictions/statistics
POST   /api/predictions/feedback
GET    /api/predictions/combined-daily
POST   /api/predictions/generate

GET    /api/subscriptions/me
POST   /api/subscriptions/purchase
GET    /api/subscriptions/verify/{token}

GET    /api/referrals/stats
GET    /api/referrals/my-code
GET    /api/referrals/list
POST   /api/referrals/apply

GET    /api/user/profile
PUT    /api/user/profile
PUT    /api/user/preferences
PUT    /api/user/locale
GET    /api/user/data-access
POST   /api/user/data-export
DELETE /api/user/data-delete

GET    /api/notifications
GET    /api/notifications/unread-count
POST   /api/notifications/register
DELETE /api/notifications/unregister
GET    /api/notifications/settings
PUT    /api/notifications/settings
GET    /api/notifications/routine-preferences
PUT    /api/notifications/routine-preferences

POST   /api/bookmakers/{id}/click       (tracking affiliation)
```

### 12.3 Webhooks

```
POST     /api/webhooks/payment
POST     /api/webhooks/paydunya
GET|POST /api/webhooks/affiliate        (confirmation inscription bookmaker → 7j Premium)
```

### 12.4 Throttling

| Groupe | Limite |
|--------|--------|
| Public (prédictions, matchs, bookmakers) | 60 req/min |
| Auth (send-otp, verify-otp, facebook, login-pin) | 10 req/min |
| Authentifié | Standard Sanctum |

---

## 13. Interface mobile

### 13.1 Principes

- Mobile-first.
- Dark theme COTA + accents jaune néon (identité fixée).
- Bouton Coupon central en bottom nav.
- Interface dense mais lisible.
- 4 états obligatoires côté mobile : loading / error / empty / success.
- Les blocages Premium expliquent la valeur sans frustrer brutalement.

### 13.2 Écrans V1

| Écran | Description |
|-------|-------------|
| Onboarding | 3 slides présentation |
| Login | Téléphone → OTP → PIN |
| Home | Pronostics du jour, cards confiance |
| Live | Matchs en cours, scores temps réel |
| Favoris | Matchs et équipes suivis |
| Compétitions | Filtrage par championnat |
| Coupon | 3 variantes du jour (prudent / équilibré / audacieux) |
| Historique | Résultats passés avec filtres et win rate |
| Statistiques | Stats COTA globales + stats perso |
| Détails prédiction | Score algo + critères + analyse IA |
| Détails match | Stats, lineups, events, H2H |
| Détails équipe | Stats saison, effectif |
| Détails joueur | Stats individuelles |
| Bookmakers | Cards bookmakers par région |
| Abonnement | Plans, paiement Paydunya |
| Profil | Données utilisateur, préférences |
| Notifications | Historique in-app, paramètres |
| FAQ | Questions fréquentes |
| Confidentialité | Politique de données |

### 13.3 Smart Empty State

Quand aucun pronostic n'est disponible :

1. Message d'explication (aucun match / seuil non atteint / API indisponible).
2. Derniers résultats avec win rate (preuve de qualité).
3. Compte à rebours prochain pronostic.
4. Lien vers bonus bookmakers (monétise le creux).
5. CTA activer notifications.

KPIs cibles :

| KPI | Cible |
|-----|-------|
| Scroll complet | > 60 % |
| CTR « Activer notifs » | > 25 % |
| CTR « Bonus bookmakers » | > 15 % |
| Retour app après timer | > 40 % |

### 13.4 Architecture feature-first Flutter

```
mobile/lib/features/{nom}/{ui,logic,data}/
```

Features : auth, predictions, subscription, referral, profile, bookmaker, affiliate, notifications.

---

## 14. Spécifications de détail par module

### 14.1 Onboarding

- 3 slides, **une seule fois** (flag local `onboarding_seen = true`).
- Bouton « Passer » visible en haut à droite.
- Indicateur de progression (3 points).
- Dernier slide : bouton « Commencer » → Login.
- Pas de collecte de données pendant l'onboarding.

| Slide | Titre | Message |
|-------|-------|---------|
| 1 | Analyse, pas intuition | « COTA analyse forme, historique, stats et plus pour t'aider à mieux choisir tes paris. » |
| 2 | On publie seulement quand on est sûr | « Pas de coup forcé. Si la confiance est trop basse, on ne publie pas. Notre win rate réel est affiché. » |
| 3 | À toi de jouer, avec prudence | « Outil d'aide à la décision. Aucun gain garanti. Parie de façon responsable. » |

### 14.2 Authentification PIN

**Rôle** : connexion rapide de confort. L'ancre de sécurité reste l'OTP.

**Flux** :

```
Première connexion :
  Téléphone → OTP → vérifié → proposition création PIN (4 chiffres)
  → confirmation PIN → PIN actif

Connexions suivantes (même appareil) :
  Téléphone (pré-rempli) + PIN → token Sanctum

PIN oublié :
  « PIN oublié ? » → re-OTP → définir nouveau PIN

Nouvel appareil / token expiré :
  OTP obligatoire avant tout usage du PIN
```

**Sécurité** :

- PIN = 4 chiffres, hashé serveur (bcrypt/argon). Jamais en clair, jamais stocké uniquement côté mobile.
- Max **5 tentatives** PIN erronées → verrouillage → OTP obligatoire pour débloquer.
- Re-OTP forcé : nouvel appareil, token expiré, réinitialisation PIN.
- Biométrie optionnelle par-dessus le PIN, côté appareil ; le PIN reste le fallback.

### 14.3 Détails match — contrat de données mobile

> ⚠️ Disponibilité dépendante d'API-Football / Sportradar. Championnats locaux ou inférieurs peuvent manquer de données. Le mobile doit gérer l'absence par section (état `empty`).

**Composition (`/lineups`)** :

```
{
  formation: "4-3-3",
  coach: { id, name },
  startingXI: [ { player_id, name, number, position, grid_x, grid_y } ],
  substitutes: [ { player_id, name, number, position } ]
}
```

`grid_x/grid_y` absents → affichage en liste, pas de terrain.

**Événements (`/events`)** : `goal`, `own_goal`, `penalty_goal`, `penalty_missed`, `yellow_card`, `red_card`, `second_yellow`, `substitution`, `var`.

```
{ minute, extra_minute, team_side: "home"|"away", type, player, detail, assist? }
```

**Statistiques (`/stats`)** par équipe : possession %, tirs, tirs cadrés, corners, fautes, hors-jeu, cartons, passes %, attaques dangereuses. Champ `null` → masquer la ligne (ne pas afficher 0).

**Confrontations (`/h2h`)** :

```
[ { date, competition, home, away, score_home, score_away } ]   // 5 à 10 dernières
```

### 14.4 Coupon IA — 3 variantes

Trois coupons distincts par jour, **étiquetés par niveau de risque**, jamais mélangés. Chaque variante a son **propre win rate mesuré et affiché séparément**.

| Variante | Confiance min/pick | Cote totale cible | Marchés privilégiés | Accès |
|----------|:------------------:|:-----------------:|---------------------|-------|
| **Prudent** | ≥ 75 | 3.00 – 6.00 | Double Chance, Under, favoris nets | Gratuit (preuve) |
| **Équilibré** | ≥ 65 | 8.00 – 15.00 | Mix 1X2, Over/Under, BTTS | Premium |
| **Audacieux** | ≥ 60 | 15.00 – 40.00 | Handicap, score exact, haute variance | Premium |

Règles communes :

- 4–5 picks par variante.
- Max 2 picks par compétition.
- Exclure picks contradictoires sur un même match.
- Publication 08h00 UTC.
- **L'audacieux affiche** : « Variante à risque élevé — cotes hautes, taux de réussite plus faible. »
- Si le volume du jour est faible : ne jamais forcer un pick sous le seuil. Au minimum, publier le prudent ; les autres sont absents si non valides.

### 14.5 Bookmakers — détection région & fallback

**Logique** :

```
1. Géolocalisation IP côté serveur sur /api/bookmakers/by-region.
2. IP → pays → région → liste bookmakers + liens affiliés + codes promo.
3. Région détectée mise en cache (session / profil).
```

**Fallback (IP inconnue, VPN, échec)** :

```
SI pays indéterminé :
   → utiliser la locale de l'appareil
   → sinon région par défaut = Afrique de l'Ouest (cœur de cible §2.2)
   → proposer à l'utilisateur de choisir/corriger sa région
```

**Override manuel** : l'utilisateur peut choisir sa région/bookmaker ; le choix **persiste dans le profil** et prime sur la détection IP.

Chaque clic bookmaker passe par `POST /api/bookmakers/{id}/click` (tracking affiliation, §17).

### 14.6 Statistiques utilisateur

> ⚠️ COTA ne voit pas les paris réels ni les mises. Toute « stat perso » se base sur les pronostics **suivis** dans l'app, pas sur de l'argent misé. Le ROI affiché est **hypothétique et indicatif** — libellé clairement.

**Deux blocs distincts** :

| Bloc | Contenu |
|------|---------|
| **Stats COTA (globales)** | Win rate algo 7j/30j, ROI global indicatif, nombre de pronostics publiés |
| **Mes stats (perso)** | Win rate des pronostics suivis, compétition la plus suivie, série de jours d'activité |

**Métriques perso** :

- Win rate perso = % de réussite sur les pronostics marqués « suivis ».
- ROI perso **hypothétique** : cote indicative × mise théorique fixe. Mention obligatoire : « estimation, mise théorique ».
- Graphe : évolution du win rate suivi sur 30 jours.

### 14.7 Historique — filtres & tri

| Filtre | Valeurs |
|--------|---------|
| Période | 7j / 30j / personnalisé |
| Compétition | Liste des compétitions |
| Confiance (étoiles) | ⭐ à ⭐⭐⭐⭐ |
| Résultat | Gagné / Perdu / Annulé |
| Type de marché | 1X2, Double Chance, Over/Under, BTTS, Handicap, Score exact |
| Accès | Gratuit / Premium |

- Tri par défaut : plus récent.
- Item : match, marché, prédiction, cote indicative, confiance, résultat, date.
- En tête de liste filtrée : **win rate de l'ensemble filtré**.

### 14.8 FAQ — contenu

Accessible hors connexion (contenu statique embarqué). Réponses honnêtes, conformes §3.

**Comment fonctionne l'algorithme ?**
COTA analyse plusieurs critères par match (forme, confrontations, domicile/extérieur, classement, stats de buts, horaire et plus) pour calculer un score de confiance de 0 à 100. Plus le score est haut, plus le signal est fort.

**Que veulent dire les étoiles ?**
Elles traduisent le niveau de confiance : plus d'étoiles = confiance plus élevée. C'est une probabilité, pas une certitude.

**Pourquoi parfois aucun pronostic n'est disponible ?**
Parce qu'aucun match du jour n'atteint notre seuil de confiance. On préfère ne rien publier plutôt que de proposer un pari faible.

**Les pronostics sont-ils garantis gagnants ?**
Non. Aucun pronostic n'est garanti. COTA est un outil d'aide à la décision ; le pari comporte toujours un risque.

**C'est quoi BTTS, Over/Under, Double Chance, Handicap ?**
BTTS : les deux équipes marquent. Over/Under : plus ou moins de X buts. Double Chance : deux issues couvertes sur trois. Handicap : avantage ou désavantage fictif donné à une équipe.

**Comment obtenir du Premium gratuitement ?**
Deux moyens : parrainer des amis (paliers de récompenses) ou t'inscrire chez un bookmaker partenaire via nos liens (7 jours Premium offerts).

**Comment fonctionne le parrainage ?**
Partage ton code ; à chaque palier de filleuls inscrits, tu gagnes des jours Premium (jusqu'au Premium à vie à 20 filleuls).

**Comment payer mon abonnement ?**
Via Paydunya : Wave, Orange Money, MTN ou Moov. Activation automatique après confirmation du paiement.

**Que se passe-t-il à l'expiration ?**
Tu repasses en accès gratuit. Tu peux renouveler à tout moment. Pas de prélèvement automatique.

**Comment contacter le support ?**
Via le formulaire de feedback dans l'app. Réponse sous quelques jours ouvrés.

**Le jeu me pose problème — où trouver de l'aide ?**
Le pari doit rester un loisir. Si tu ressens une perte de contrôle, fais une pause et parle-en à une personne de confiance ou à un service d'aide dédié.

---

## 15. Dashboard admin

### 15.1 Stack

- Routes `/admin/*` → `Http/Controllers/Admin/`.
- Vues Blade + Tailwind CDN.
- Accessible aux super admins uniquement.

### 15.2 Pages V1

| Page | Fonctionnalité |
|------|----------------|
| Utilisateurs | Liste, détail, actions manuelles |
| Prédictions | Liste, régénération, résultats, override manuel |
| Abonnements | Liste, filtres, graphique revenus, accord manuel |
| Parrainages | Liste, top parrains, paliers récompenses |
| Bookmakers | CRUD bookmakers, liens affiliation, codes promo |
| Compétitions | CRUD compétitions, tiers |
| Affiliations | Suivi clics, conversions, **RevShare par bookmaker et par joueur** |
| Feedbacks | Liste, détail, réponse admin |
| Stats avancées | Taux réussite, ROI, graphiques 30j/12m, **par type de pari** |
| Paramètres | Clés API, Paydunya, AppConfig (`w_ext` configurable, seuils, bandes de cotes) |

---

## 16. Jobs schedulés

| Job | Fréquence | Rôle |
|-----|-----------|------|
| FetchMatchesJob | Horaire | Récupère matchs à venir |
| RefreshOddsJob | Horaire + avant coup d'envoi | Cotes live Bet365 / 1xBet, met à jour cotes indicatives |
| GenerateAllPredictionsJob | 08h00 et 20h00 | Cascade multi-marchés + hybridation + génération analyse IA |
| GenerateCouponJob | 08h00 | Génère les 3 variantes (prudent / équilibré / audacieux) |
| UpdateLiveScoresJob | Toutes les 2 min | Scores en direct |
| UpdatePredictionResultsJob | Toutes les 5 min | Résultats prédictions |
| SendDailyNotificationJob | 09h00 | Notification du jour (FCM v1) |
| **CheckBookmakerRegistrationsJob** | Horaire | Vérifie inscriptions référées → octroie 7j Premium |

---

## 17. Monétisation

### 17.1 Abonnement Premium

| Plan | Prix indicatif | Durée |
|------|----------------|-------|
| Mensuel | 8 000 FCFA | 30 jours |
| Trimestriel | 20 000 FCFA | 90 jours |
| Annuel / VIP | à arbitrer (voir §25) | 360 jours |

### 17.2 Paiement V1

- Provider : Paydunya.
- Méthodes : Wave, Orange Money, MTN, Moov.
- Clés gérées depuis dashboard admin (AppConfig en base, pas dans .env).
- Webhook `/api/webhooks/paydunya` active Premium automatiquement.
- Fallback accord manuel depuis admin.

### 17.3 Affiliation bookmakers — RevShare

**Modèle** : **RevShare 25–40 %** sur l'activité des joueurs référés (récurrent), **pas CPA fixe**.

Conséquences :

- **Métrique n°1** = joueurs référés actifs et retenus, pas inscriptions brutes.
- Un inscrit qui ne parie jamais = 0 FCFA. Un joueur régulier = revenu mois après mois.
- COTA est exactement conçu pour ça (pronostics quotidiens, notifs « bon coup », facilité de pari).
- Démarrage plus lent que le CPA mais composition dans le temps.

**Boucle d'acquisition** :

```
Utilisateur clique bonus 7j Premium
  → redirection trackée vers bookmaker
  → inscription
  → CheckBookmakerRegistrationsJob (horaire) vérifie
  → 7j Premium activés automatiquement
```

Aligne intérêt utilisateur (Premium gratuit) et revenu COTA (RevShare).

**Protection compte affilié** : trafic propre + jeu responsable (§20) protègent contre audits, gels, bannissements par le bookmaker.

### 17.4 Parrainage

| Palier | Récompense |
|--------|-----------|
| 1 filleul | 3 jours Premium |
| 5 filleuls | 7 jours Premium |
| 10 filleuls | 30 jours Premium |
| 20 filleuls | Premium à vie |

Cashback 10 % sur achats filleuls → Phase 2.

### 17.5 Free vs Premium — règle

| | Gratuit | Premium |
|---|---------|---------|
| Pronostics simples/jour | 1–3 fiables | 10–15 selon volume |
| Coupon prudent | ✅ | ✅ |
| Coupon équilibré | ❌ | ✅ |
| Coupon audacieux | ❌ | ✅ |
| Win rate visible | ✅ | ✅ |
| Notifs « bon coup » | ❌ | ✅ |
| Live | ❌ | ✅ |
| Historique | 7j | 30j |
| Stats détaillées | ❌ | ✅ |
| Publicité | ✅ | ❌ |

**Ne PAS mettre en premium** ce qui fait parier (liens bookmakers, codes promo). Cela réduirait l'affiliation.

### 17.6 Projections (à reconstruire en RevShare, §25)

| Phase | MRR cible (indicatif) |
|-------|-----------------------|
| Mois 4 (fin Phase 1) | 500 € |
| Mois 8 | 3 000 € |
| Mois 12 | 8 000 € |
| Mois 18 | 15 000–25 000 € |

⚠️ Recalcul à faire avec base RevShare réelle dès que la base de calcul (% NGR vs % volume) est confirmée par Betwinner (§25).

---

## 18. Distribution & acquisition

### 18.1 Canaux

- **Canal principal** : web (PWA) + APK direct téléchargeable depuis la page web.
- **Acquisition virale** : bouche-à-oreille (parrainage), groupes WhatsApp / Facebook foot, forums paris, influenceurs.
- **PAS de Play Store au lancement** : risque de bannissement à cause des liens d'affiliation vers bookmakers non licenciés localement.
- Play Store envisageable plus tard en **version édulcorée** (sans liens bookmakers directs visibles).

### 18.2 Page de téléchargement

Trois objectifs :

1. Prouver la crédibilité : win rate réel affiché, derniers gains.
2. Donner envie : combiné de bienvenue visible, valeur claire.
3. Lever le frein d'installation hors store : mini-guide « installer l'APK en 3 étapes » pour passer l'avertissement de sécurité Android.

### 18.3 Influenceurs & forums

- **Code ou lien tracké unique** par influenceur (sinon attribution impossible).
- Choisir des influenceurs au ton compatible avec l'ADN de transparence (éviter ceux qui promettent « 100 % »).
- Forums : apporter de la valeur d'abord (analyse, pronostic gratuit). Pas de spam.

### 18.4 Programme de lancement

| Étape | Actions |
|-------|---------|
| J-30 | Landing page waitlist, recrutement 50 beta testeurs |
| J-15 | Beta privée, feedback intensif |
| J-7 | Teasing public, countdown |
| J0 (9 juin) | Lancement officiel, parrainage actif, campagne influenceurs |
| J+30 | Bilan, doubler ce qui marche |

### 18.5 Funnel acquisition (indicatif)

```
AWARENESS (10 000) → Réseaux sociaux, SEO, bouche-à-oreille
    ↓ 30 %
TÉLÉCHARGEMENT (3 000)
    ↓ 70 %
ACTIVATION (2 100) → Compte créé (OTP / Facebook)
    ↓ 60 %
ENGAGEMENT (1 260) → 3+ sessions
    ↓ 25 %
MONÉTISATION (315) → Abonnement OU inscription bookmaker
    ↓ 50 %
RÉTENTION (157) → Renouvellement / activité mois 2
    ↓ 30 %
ADVOCACY (47) → Parrainage 1+ ami
```

---

## 19. Notifications

### 19.1 Canaux

- Push principal : FCM HTTP v1.
- In-app : notifications persistées en base.
- SMS fallback (Termii ou équivalent) : OTP uniquement en V1.

### 19.2 Types

| Type | Déclencheur |
|------|-------------|
| Pronostics du jour | 09h00 quotidien |
| Coupon IA disponible | 08h00 quotidien |
| Résultat prédiction | Fin de match |
| Abonnement expiré | J-3 + J0 |
| Parrainage validé | Filleul inscrit |
| Inscription bookmaker confirmée | 7j Premium activés |
| Match favori dans 1h | 1h avant coup d'envoi |

### 19.3 Préférences

L'utilisateur peut activer/désactiver chaque type via `PUT /api/notifications/settings`.

---

## 20. Sécurité, conformité, jeu responsable

### 20.1 Sécurité technique

- HTTPS obligatoire en production.
- CORS limité aux domaines COTA.
- Rate limiting OTP : 10 req/min.
- Secrets hors Git (`.env` non commis).
- Rotation tokens Sanctum.

### 20.2 RGPD & données utilisateur

- Export données : `POST /api/user/data-export`.
- Suppression données : `DELETE /api/user/data-delete`.
- Politique de confidentialité publique.
- Logs sans données personnelles sensibles.

### 20.3 Jeu responsable

- Avertissement affiché dans : écran abonnement, coupon, prédictions, FAQ, page de téléchargement, analyse IA.
- Texte type : « COTA est un outil d'aide à la décision. Pariez de façon responsable. »
- Interdit : présenter COTA comme bookmaker, promettre des gains, manipuler les résultats.
- Si un utilisateur exprime une perte de contrôle, ne jamais l'inciter à parier davantage.

---

## 21. Roadmap & phases

### Phase A — Préproduction (jusqu'à J-15)

**Objectif** : rendre l'app testable en conditions proches production.

Tâches :

- Configurer `.env` production.
- Installer HTTPS.
- Restreindre CORS aux domaines COTA.
- Lancer migrations production.
- Queue worker durable + scheduler durable.
- Service account Firebase côté backend.
- Builder mobile avec `APP_BASE_URL` production.
- Tester paiement Paydunya bout en bout.
- Tester notification FCM sur vrai appareil.
- Tester OTP sur vrai numéro.
- **Tester tracking d'attribution affilié bout en bout** (priorité critique §21.3).

### Phase B — Lancement V1 (J0 = 9 juin 2026)

**Objectif** : ouvrir au public via APK direct + web.

Tâches :

- Publier APK sur la page de téléchargement web.
- Finaliser politique de confidentialité.
- Activer monitoring erreurs.
- Vérifier logs et rotation.
- Tester performance réseau lent.
- Remplacer tous les liens affiliation placeholder.

### Phase C — Growth V1.1 (post-Mondial)

**Objectif** : rétention et conversion.

Tâches :

- Améliorer Smart Empty State.
- Notifications personnalisées.
- Stats utilisateur enrichies.
- Suivre conversion free → Premium.
- Optimiser pages bookmakers.
- Campagnes d'acquisition organique.

### 21.3 Priorités critiques avant le 9 juin

Par ordre de criticité :

1. **Tracking d'attribution bout en bout**. En RevShare, si le lien n'attribue pas correctement inscription **ET** activité, le revenu est nul. Test réel : cliquer son propre lien → s'inscrire → parier → vérifier remontée dans le dashboard affilié.
2. **Boucle 7j Premium ↔ inscription bookmaker** (`CheckBookmakerRegistrationsJob`) opérationnelle.
3. **Page de téléchargement web + guide d'installation APK**.
4. **Cascade multi-marchés minimale** : au moins 1X2 + Double Chance + Over/Under + BTTS sur données fiables.
5. **Cotes indicatives + bandes par marché**.
6. **Coupon : au moins la variante prudente publiée chaque jour**.

---

## 22. KPIs & analytics

### 22.1 Outils

- PostHog (self-hosted) : analytics produit.
- Google Analytics 4 : site web.
- Branch.io : deep links + attribution.

### 22.2 Events clés

```
Acquisition :
  app_installed { source }
  signup_completed { method, country }

Activation :
  first_prediction_viewed { confidence }
  first_coupon_opened { variant }

Engagement :
  prediction_clicked { id }
  bookmaker_link_clicked { bookmaker_id }

Conversion :
  subscription_started { plan, amount }
  subscription_renewed { plan }
  bookmaker_registration_confirmed { bookmaker_id }
  premium_granted_via_bookmaker { bookmaker_id }

Rétention :
  app_opened { day_count }

Advocacy :
  referral_shared { channel }
  referral_completed { referrer_id }
```

### 22.3 KPIs hebdomadaires

| KPI | Cible |
|-----|-------|
| Nouveaux signups | Croissance semaine/semaine |
| DAU / MAU | ≥ 40 % |
| Conversion free → paid | ≥ 15 % |
| Conversion free → inscription bookmaker | ≥ 20 % |
| Churn mensuel | < 30 % |
| Win rate algo global | ≥ 55 % |
| Win rate algo par type de pari | suivre §6.5 |

---

## 23. Definitions of done

### 23.1 Feature terminée si

- Backend fonctionnel OU mobile gère proprement l'absence de backend.
- Erreurs API traitées.
- `php artisan test --stop-on-failure` passe.
- `flutter analyze` passe à 0 erreur.
- 4 états gérés côté mobile : loading / error / empty / success.
- Doc endpoint / comportement à jour.
- Comportement cohérent : invité, gratuit, Premium.
- Logs utiles pour diagnostic.
- Aucun TODO dans le code.

### 23.2 Module authentification (OTP + PIN)

- Nouvel utilisateur reçoit OTP.
- OTP valide crée/connecte et retourne token Sanctum.
- OTP invalide ou expiré refusé.
- `GET /api/auth/me` retourne profil avec token valide.
- Création PIN proposée après premier OTP réussi.
- `POST /api/auth/login-pin` valide retourne token.
- 5 PIN erronés verrouillent et forcent OTP.
- « PIN oublié » réinitialise via OTP.
- Nouvel appareil exige OTP avant PIN.
- Endpoints protégés refusent utilisateur non authentifié.

### 23.3 Module prédictions (cascade)

- `GET /api/predictions/today` retourne liste exploitable.
- Données ont les champs nécessaires aux cards.
- Prédictions Premium débloquées pour utilisateur Premium.
- Smart Empty State s'affiche correctement si vide.
- Au moins 4 marchés actifs sur données fiables.
- Aucun pronostic publié sous le seuil de confiance.
- Cote affichée dans la bande de son marché.

### 23.4 Module coupon (3 variantes)

- `GET /api/predictions/coupon` retourne les variantes disponibles.
- Au minimum la variante prudente est publiée si volume suffisant.
- Picks ont cote, confiance, match, prédiction, marché et heure.
- Picks contradictoires exclus.
- Win rate de chaque variante calculé séparément.

### 23.5 Module paiement

- `GET /api/subscriptions/plans` retourne les plans.
- `POST /api/subscriptions/purchase` crée une facture Paydunya.
- Webhook active Premium sans action manuelle.
- Utilisateur devient Premium après paiement confirmé.
- Statut visible via `/api/subscriptions/me`.

### 23.6 Module affiliation & RevShare

- Chaque clic bookmaker passe par `POST /api/bookmakers/{id}/click`.
- Lien affilié transporte l'identifiant tracking.
- Test bout en bout : clic → inscription → activité → remontée correcte dans dashboard affilié bookmaker.
- `CheckBookmakerRegistrationsJob` vérifie et active 7j Premium automatiquement.

### 23.7 Module notifications

- App enregistre token via `POST /api/notifications/register`.
- `GET /api/notifications/settings` retourne préférences.
- `PUT /api/notifications/settings` met à jour préférences.
- Job routine peut envoyer notification.
- Notification envoyée apparaît dans historique in-app.

---

## 24. Évolutions V2+

### 24.1 Algorithme V2

- xG (Expected Goals) — 10 %.
- Blessures joueurs clés — 6 %.
- Hybridation modèle ML scikit-learn.
- Backtesting automatique continu.

### 24.2 Produit V2

- Marketplace caissiers/agents Mobile Money.
- Coaching premium humain.
- Marketplace pronostiqueurs tiers.
- API publique B2B.
- Multi-sports (basket, tennis).
- Fantasy football intégré.
- Pré-remplissage automatique des coupons (auto-fill WebView).
- Carte à gratter (gamification).
- Popup sélection bookmaker au premier lancement.
- Bonus de partage d'app.
- Recherche de contexte qualitatif par IA (blessures, compositions, enjeu).
- Value betting (classement par valeur).

### 24.3 Infrastructure V2

- CDN pour assets médias.
- Monitoring Sentry ou équivalent.
- CI/CD GitHub Actions.

### 24.4 Monétisation V2

- Crypto paiement (USDT / stablecoins pour diaspora).
- Cashback parrainage 10 % sur achats filleuls.
- Partenariat officiel bookmakers (co-marketing).
- API B2B sous licence.

---

## 25. Décisions en attente

Points à trancher avant l'exécution complète des modules concernés.

| # | Point | Bloque |
|---|-------|--------|
| D1 | Source externe de pronostic : identité du fournisseur + format (probabilités / cotes / pronostic sec) | §8 |
| D2 | Base de calcul RevShare (% NGR vs % volume) + % exact + possibilité de modèle hybride (petit CPA + RevShare) | §17.3, §17.6 |
| D3 | Fournisseur LLM (Anthropic / OpenAI / autre) + budget mensuel | §9 |
| D4 | Prix du plan Annuel / VIP (incohérence à clarifier) | §17.1 |
| D5 | Backtest par type de pari avant figeage des seuils et bandes de cote | §6.5, §10.2 |
| D6 | Confirmer couverture cotes (Bet365 / 1xBet) en production réelle | §10.1 |
| D7 | Stratégie Play Store post-lancement (version édulcorée, calendrier) | §18.1 |

---

*COTA — Cahier des charges V2 | 2026-05-28 | MHD SERVICE*
*Source de vérité unique. Remplace toutes les versions antérieures et documents complémentaires.*
