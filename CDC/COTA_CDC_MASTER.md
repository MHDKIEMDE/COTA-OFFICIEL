# COTA — Cahier des charges MASTER

> **Source de vérité unique.** Consolide : CDC V2 (2026-05-28), CDC V1, Annexes Algorithme / Marketing / Spécifications v4, Smart Empty State v1.0, Évolutions MVP v4.0.
> Couvre l'**ensemble du projet** : backend, application mobile, **site web (PWA)**, **bot Telegram**, paiements, affiliation, parrainage.
> Dernière mise à jour : 2026-06-10.
> Propriété intellectuelle MHD SERVICE.

---

## SOMMAIRE

1. Vision produit
2. Personas & marché cible
3. Règles de communication (non négociables)
4. Périmètre & plateformes
5. Stack technique
6. Architecture algorithme — modèle restaurant
7. Algorithme — critères & scoring
8. Algorithme — cascade multi-marchés
9. Algorithme — hybridation algo + source externe
10. Couche d'analyse IA
11. Gestion des cotes
12. Fonctionnalités V1 (périmètre)
13. Endpoints API
14. Application mobile
15. Site web (PWA) & page de téléchargement
16. Bot Telegram & Mini App
17. Dashboard admin
18. Jobs schedulés
19. Monétisation
20. Affiliation & tracking d'attribution
21. Notifications
22. Sécurité, conformité, jeu responsable
23. Distribution & acquisition
24. Roadmap & phases
25. KPIs & analytics
26. Definitions of done
27. Évolutions V2+
28. Décisions en attente

---

## 1. Vision produit

### 1.1 Objectif

COTA est une plateforme de pronostics football assistée par algorithme, **mobile-first** mais déclinée en **app mobile, site web et bot Telegram**, pour l'Afrique de l'Ouest francophone, le Maghreb et la diaspora française.

- Aider l'utilisateur à mieux sélectionner ses paris grâce à une analyse structurée.
- Générer du revenu via Premium, **affiliation bookmakers (RevShare)** et parrainage.
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

S'appliquent à l'UI mobile, au web, au bot Telegram, aux notifications, à l'analyse IA et au marketing.

- **Jamais** : « gain garanti », « 100 % », « pari sûr », « coup sûr ».
- La **confiance est un indicateur probabiliste**, pas une certitude.
- **Win rate cible 55–60 %** affiché honnêtement. Ne pas gonfler, ne pas mentir, ne pas masquer les pertes.
- **Avertissement jeu responsable** visible dans : écran abonnement, coupon, prédictions, FAQ, page de téléchargement, messages Telegram.
- Si l'utilisateur exprime une perte de contrôle → ne jamais l'inciter à parier davantage.
- Ne jamais inventer une statistique, un joueur, une donnée manquante. Si l'info manque, le dire.
- Identité visuelle fixée : thème sombre, accents jaune néon `#FFEB3B`, logo COTA souligné en jaune, bouton Coupon central en bottom nav. **Pas de refonte** : optimisations subtiles uniquement.

---

## 4. Périmètre & plateformes

COTA est un produit **multi-surfaces** alimenté par un backend unique :

| Surface | Rôle | Statut |
|---------|------|:------:|
| **Backend API Laravel** | Cerveau : algo, cotes, paiements, affiliation, notifications, source de toutes les données | ✅ |
| **Application mobile Flutter** | Surface principale (APK direct, Android prioritaire) | ✅ |
| **Site web (PWA + Blade)** | Pages publiques, page de téléchargement APK, parcours web complet | ✅ |
| **Bot Telegram** | Acquisition + diffusion quotidienne + Mini App (WebView du site) | ✅ |
| **Dashboard admin (Blade)** | Pilotage interne : users, prédictions, affiliation, paramètres | ✅ |

Principe : **une seule logique métier dans le backend**, consommée par toutes les surfaces. Le mobile et le web partagent les mêmes endpoints ; Telegram s'appuie sur le backend pour les commandes et le broadcast, et ouvre la Mini App (WebView du site).

---

## 5. Stack technique

### 5.1 Backend

| Composant | Choix |
|-----------|-------|
| Framework | Laravel 12 |
| Langage | PHP 8.3 |
| Auth API | Sanctum |
| Cache | Redis |
| Queues | Redis + Horizon |
| Base de données | MySQL (prod) / SQLite (dev simple) |
| Admin & Web | Blade + Tailwind CDN |

### 5.2 Mobile

| Composant | Choix |
|-----------|-------|
| Framework | Flutter 3.x |
| Langage | Dart |
| État | Riverpod |
| HTTP | Dio |
| Routing | GoRouter |
| Notifications | Firebase Core + FCM + Flutter Local Notifications |

### 5.3 Services externes

| Service | Rôle |
|---------|------|
| API-Football | Données matchs, stats, classements (primaire) |
| Sportradar | Fallback données football |
| Bet365 + 1xBet (live odds) | Source cotes temps réel (1xBet ≈ Betwinner ≈ Melbet) |
| Source externe pronostic | Signal d'hybridation (à confirmer §28) |
| LLM (Anthropic / OpenAI) | Analyse IA serveur (à confirmer §28) |
| OpenWeatherMap | Critère météo |
| Paydunya | Paiement Mobile Money (Wave, Orange Money, MTN, Moov) |
| FCM HTTP v1 | Notifications push |
| Termii (ou équiv.) | OTP fallback SMS |
| Telegram Bot API | Bot, webhook, broadcast, Mini App |
| 1xPartners | Postback S2S `{reg}` / `{ftd}` pour attribution affiliée |

### 5.4 Infrastructure

- Nginx, HTTPS obligatoire en production.
- Queue worker durable (Supervisor / pm2 / service Docker).
- Scheduler Laravel durable.
- Docker Compose disponible (app, nginx, redis, queue, scheduler).
- Coût cible MVP : 0 € ; Scale : < 80 $/mois. Disponibilité données > 99 %, cache hit Redis > 80 %.

---

## 6. Architecture algorithme — modèle restaurant

| Rôle | Composant | Responsabilité |
|------|-----------|----------------|
| **Chef** | Algorithme + sources de données | Calcule les pronostics chiffrés. Invisible. Ne décide pas de la publication. |
| **Passe-plat** | Règle de publication | Décision déterministe : confiance ≥ seuil **ET** cote dans la bande du marché. Pas d'IA. |
| **Serveur** | Couche d'analyse IA | Met en mots les pronostics validés. Ne touche jamais à la recette. Ne promet rien. |

Conséquences : calcul mesurable et back-testable ; l'IA n'introduit pas de non-déterminisme ; présentation et qualité découplées.

---

## 7. Algorithme — critères & scoring

### 7.1 Formule générale

```
Score_Algo = Σ(Score_Critère_i × Poids_i)     Σ(Poids_i) = 100
Direction = SIGN(Score_Algo)   + favori domicile | - favori extérieur
```

### 7.2 Les 9 critères (V1, v3.0 production)

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

### 7.3 Détail des principaux critères

**C1 — Forme récente (25 pts)** : `V=5 N=2.5 D=0` ; `Score=(Diff_Forme/25)×25` ; bonus +2 si V5 consécutifs, -2 si D5.
**C2 — Head-to-Head (20 pts)** : `Score=(Diff_Victoires_H2H/5)×20` ; si < 3 H2H → critère désactivé, poids redistribué vers Forme.
**C3 — Domicile/Extérieur (15 pts)** : `Score=(Win_Rate_Dom_A − Win_Rate_Ext_B)×15` ; bonus ±3 ; clamp [-15,+15].
**C4 — Classement (12 pts)** : gap ≥10→±12 ; 5–9→±8 ; 3–4→±5 ; 1–2→±2 ; désactivé en compétitions internationales / début saison (<5 journées).
**C5 — Stats buts (10 pts)** : Moyenne buts (4) + BTTS (3) + Clean Sheets (3) ; clamp [-10,+10].
**C6 — Horaire (8 pts)** : matinée −1, prime time 16–19h +2, nuit 23–01h −3, nuit profonde 02–07h −5 ; habitude +2, décalage >3h −2 ; clamp [0,8].
**C7–C9 — Météo / Tirs cadrés / Forme physique** : poids faibles, enrichissent l'explication sans dominer le score.

### 7.4 Seuils de publication

| Score | Niveau | Accès | Étoiles |
|-------|--------|-------|:-------:|
| 85–100 | Très haute confiance | Premium | ⭐⭐⭐⭐ |
| 70–84 | Haute confiance | Premium | ⭐⭐⭐ |
| 60–69 | Confiance moyenne | Gratuit | ⭐⭐ |
| 50–59 | Confiance faible | Gratuit | ⭐ |
| < 50 | Non publié | — | — |

### 7.5 Backtesting

| Niveau | Win rate cible |
|--------|---------------|
| Très haute | ≥ 70 % |
| Haute | ≥ 60 % |
| Moyenne | ≥ 55 % |
| Faible | ≥ 50 % |
| Global | ≥ 55 % |

ROI cible : > +7 % sur 100 pronostics à cote moyenne 1.85. **Backtest obligatoire par type de pari séparément** avant de figer seuils et bandes de cote.

---

## 8. Algorithme — cascade multi-marchés

L'algo n'évalue pas seulement « qui gagne » : pour chaque match il teste **plusieurs marchés en parallèle** et publie celui où le signal est le plus fort et valide. Reformulation : *« Qu'est-ce que je sais de plus sûr sur ce match ? »*

| Moteur | Marchés servis | Critères dominants |
|--------|----------------|--------------------|
| **Force** | 1X2, Double Chance, Handicap | Forme, H2H, Dom/Ext, Classement, Horaire |
| **Buts** | Over/Under, BTTS | Stats buts (≈70 % du poids), tirs cadrés en appoint |
| **Haute variance** | Score exact | Dernier recours, signal exceptionnel uniquement |

**Sélecteur** : évaluer tous les marchés → filtrer (confiance ≥ seuil ET cote dans la bande) → publier le meilleur par équilibre **confiance × valeur**.

**Précautions** : n'activer un marché que si les données sont fiables ; backtest par type de pari (piège des comparaisons multiples). **Bénéfice produit** : plus de matchs publiables → plus de clics bookmakers → plus de RevShare, sans mentir sur la qualité.

### Types de marchés disponibles

`1X2`, `Double Chance`, `Handicap`, `Over/Under`, `BTTS`, `Team Goals`, `Corners`, `Cards`, `Shots`, `Score Exact`. Badges sur les cards : 1X2, O/U, BTTS, DC, HC, BUTS, CRN, CART, TIRS.

---

## 9. Algorithme — hybridation algo + source externe

Stabilise la qualité au lancement, tant que l'algo interne n'est pas calibré.

```
Score_Publié = Score_Algo × (1 − w_ext) + Score_Externe_normalisé × w_ext
```

- `w_ext` configurable dans **AppConfig**. Lancement : ≈ 0.30–0.40. Réduit progressivement à mesure que le backtest prouve l'algo.
- Le signal externe est **normalisé sur l'échelle 0–100** avant tout mélange.
- **Garde-fou désaccord** : si `direction(Algo) ≠ direction(Externe)` → ne pas publier OU rétrograder d'un palier.
- **Traçabilité obligatoire** : stocker `score_algo`, `score_externe`, `score_publié`, `w_ext`, `résultat_réel` par prédiction.

---

## 10. Couche d'analyse IA — « le serveur »

- L'IA **ne calcule rien et ne décide rien** : elle met en mots les chiffres produits.
- Consigne verrouillée : 3–4 phrases en français à partir **uniquement** des données fournies ; interdiction d'inventer ; interdiction des termes « garanti / 100 % / gain sûr » ; rappel jeu responsable ; filtrage de sortie.
- Génération **une seule fois** par prédiction dans le pipeline (`GenerateAllPredictionsJob`), stockée dans `analysis`, en cache Redis. **Pas d'appel LLM au runtime utilisateur.**
- **Fallback obligatoire** : si l'API LLM échoue, générer par template. L'analyse IA est un bonus, pas une dépendance critique.

---

## 11. Gestion des cotes

- Cotes **temps réel** via **Bet365 + 1xBet** (1xBet ≈ Betwinner ≈ Melbet : une seule source couvre les trois affiliés).

| Marché | Bande de cote valide |
|--------|----------------------|
| 1X2 / Double Chance | 1.40 – 3.50 |
| Handicap (asiatique) | 1.50 – 2.50 |
| Over/Under buts | 1.50 – 2.30 |
| BTTS | 1.50 – 2.30 |
| Score exact | 5.00 – 12.00 |

- **La confiance est centrale ; la cote est secondaire et indicative.** Mention : « cote indicative, susceptible de varier — vérifiez chez le bookmaker ».
- Value betting → V2.

---

## 12. Fonctionnalités V1 — périmètre

### 12.1 Inclus V1

| Module | Statut |
|--------|:------:|
| Authentification OTP / PIN / Facebook | ✅ |
| Liaison de compte Telegram (OTP unifié) | ✅ |
| Profil utilisateur | ✅ |
| Pronostics du jour (cascade multi-marchés) | ✅ |
| Coupon IA — 3 variantes (prudent / équilibré / audacieux) | ✅ |
| Analyse IA serveur (rédaction) | ✅ |
| Hybridation algo + source externe | ✅ |
| Live scores | ✅ |
| Historique & statistiques | ✅ |
| Favoris | ✅ |
| Compétitions | ✅ |
| Détails match / équipe / joueur / prédiction | ✅ |
| Abonnement Premium + Paydunya | ✅ |
| Site web public + page de téléchargement APK | ✅ |
| Bot Telegram (commandes + broadcast quotidien) | ✅ |
| Telegram Mini App (WebView du site) | ✅ |
| Dashboard admin | ✅ |
| Bookmakers & affiliation (RevShare) | ✅ |
| Boucle 7j Premium ↔ inscription bookmaker | ✅ |
| Attribution affiliée S2S (postback `{reg}`/`{ftd}`) + vérif manuelle | ✅ |
| Notifications push & in-app | ✅ |
| Parrainage de base | ✅ |
| Politique confidentialité + RGPD (consentement tracking) | ✅ |
| Smart Empty State | ✅ |

### 12.2 Hors V1 — V2+

Marketplace caissiers/agents Mobile Money · coaching premium humain · marketplace pronostiqueurs · API publique B2B · multi-sports · fantasy football · live betting avancé · crypto paiement · auto-fill WebView des coupons · carte à gratter · popup sélection bookmaker au premier lancement · bonus de partage d'app · contexte qualitatif IA · value betting.

---

## 13. Endpoints API

### 13.1 Publics

```
GET  /api/health
POST /api/auth/send-otp
POST /api/auth/verify-otp
POST /api/auth/facebook
POST /api/auth/check-phone
POST /api/auth/login-pin                 (numéro + PIN)
POST /api/auth/login-password            (email + mot de passe)

GET  /api/predictions/today
GET  /api/predictions/coupon            (3 variantes : prudent, équilibré, audacieux)
GET  /api/predictions/competitions
GET  /api/predictions/search
GET  /api/predictions/{id}

GET  /api/matches/featured | live | today | date/{date}
GET  /api/matches/{id} | {id}/events | {id}/stats | {id}/lineups | {id}/h2h

GET  /api/subscriptions/plans
GET  /api/bookmakers | bookmakers/by-region | bookmakers/blogs
GET  /api/config/app
```

### 13.2 Authentifiés (Sanctum)

```
GET    /api/auth/me
POST   /api/auth/logout | complete-registration | set-pin | reset-pin | set-password
GET    /api/auth/telegram-link           (générer lien de liaison Telegram)
POST   /api/auth/unlink-telegram

GET    /api/predictions/history | statistics | combined-daily
POST   /api/predictions/feedback | generate

GET    /api/subscriptions/me
POST   /api/subscriptions/purchase
GET    /api/subscriptions/verify/{token}

GET    /api/referrals/stats | my-code | list
POST   /api/referrals/apply

GET    /api/user/profile | data-access
PUT    /api/user/profile | preferences | locale
POST   /api/user/data-export
DELETE /api/user/data-delete

GET    /api/favorites | favorites/check
POST   /api/favorites
DELETE /api/favorites | favorites/{id}

GET    /api/notifications | unread-count | settings | routine-preferences
POST   /api/notifications/register
PUT    /api/notifications/settings | routine-preferences
DELETE /api/notifications/unregister

POST   /api/bookmakers/{id}/click       (tracking affiliation)
```

### 13.3 Webhooks

```
POST     /api/telegram/webhook          (réception updates Telegram)
POST     /api/webhooks/payment
POST     /api/webhooks/paydunya         (activation Premium auto)
GET|POST /api/webhooks/affiliate        (postback S2S reg/ftd → 7j Premium)
```

### 13.4 Throttling

| Groupe | Limite |
|--------|--------|
| Public (prédictions, matchs, bookmakers) | 60 req/min |
| Auth (send-otp, verify-otp, facebook, login-pin) | 10 req/min |
| Authentifié | Standard Sanctum |

---

## 14. Application mobile

### 14.1 Principes

Mobile-first · dark theme + accents jaune néon · bouton Coupon central en bottom nav · interface dense mais lisible · **4 états obligatoires : loading / error / empty / success** · blocages Premium qui expliquent la valeur sans frustrer.

Architecture feature-first : `mobile/lib/features/{nom}/{ui,logic,data}/` — features : auth, predictions, subscription, referral, profile, bookmaker, affiliate, notifications.

### 14.2 Écrans V1

Onboarding (3 slides) · Login (Tél → OTP → PIN) · Home (pronostics du jour) · Live · Favoris · Compétitions · Coupon (3 variantes) · Historique · Statistiques · Détails prédiction / match / équipe / joueur · Bookmakers · Abonnement · Profil · Notifications · FAQ · Confidentialité.

### 14.3 Spécifications de détail

**Onboarding** : 3 slides, une seule fois (flag `onboarding_seen`), bouton « Passer », pas de collecte. Slides : (1) « Analyse, pas intuition » ; (2) « On publie seulement quand on est sûr » ; (3) « À toi de jouer, avec prudence ».

**Auth PIN** : PIN 4 chiffres = confort, ancre de sécurité = OTP. Hashé serveur (jamais en clair). Max **5 tentatives** → verrouillage + OTP. Re-OTP forcé sur nouvel appareil / token expiré / reset PIN. Biométrie optionnelle par-dessus le PIN.

**Détails match (contrat de données)** : `/lineups` (formation, coach, startingXI, substitutes ; sans grid → liste), `/events` (goal, cartons, substitution, var…), `/stats` (possession, tirs, corners… ; `null` → masquer la ligne), `/h2h` (5–10 dernières). Le mobile gère l'absence par section (état `empty`).

**Coupon 3 variantes** :

| Variante | Conf. min/pick | Cote totale | Marchés | Accès |
|----------|:--:|:--:|---|---|
| Prudent | ≥ 75 | 3.00–6.00 | Double Chance, Under, favoris nets | Gratuit (preuve) |
| Équilibré | ≥ 65 | 8.00–15.00 | Mix 1X2, O/U, BTTS | Premium |
| Audacieux | ≥ 60 | 15.00–40.00 | Handicap, score exact | Premium |

4–5 picks · max 2/compétition · picks contradictoires exclus · publication 08h00 UTC · win rate mesuré séparément par variante · l'audacieux affiche « variante à risque élevé » · si volume faible, ne jamais forcer un pick sous le seuil (au minimum publier le prudent).

**Bookmakers — détection région** : géoloc IP serveur → pays → région → liste + liens affiliés + codes promo, mise en cache. Fallback : locale appareil → défaut Afrique de l'Ouest → proposer correction. **Override manuel persistant** dans le profil, prioritaire sur l'IP. Chaque clic via `POST /api/bookmakers/{id}/click`.

**Statistiques utilisateur** : COTA ne voit ni paris réels ni mises ; tout ROI est **hypothétique et indicatif**. Deux blocs : *Stats COTA globales* (win rate 7j/30j, ROI global, nb pronostics) et *Mes stats* (win rate des pronostics suivis, compétition la plus suivie, série d'activité).

**Historique** : filtres période / compétition / étoiles / résultat / type de marché / accès ; tri par défaut récent ; win rate de l'ensemble filtré en tête.

**FAQ** : contenu statique embarqué (hors connexion), réponses honnêtes conformes §3.

### 14.4 Smart Empty State (feature #COTA-FEAT-027)

Affiché quand aucun pronostic ne passe le seuil. Logique en cascade 3 niveaux servie par `GET /api/predictions/today` :

| Niveau | Condition | `status` |
|--------|-----------|----------|
| 1 | Pronostics ≥ seuil disponibles | `predictions_available` |
| 2 | Matchs analysés > 0, aucun ≥ seuil | `no_predictions_above_threshold` |
| 3 | Aucun match | `no_matches_today` |

**5 blocs empilés** : (1) Carte « Aucun pronostic » + nb matchs analysés dynamique + « Nous préférons ne rien publier plutôt que vous proposer un pari risqué » ; (2) Bandeau Win Rate (win rate / ROI / ratio 7j) ; (3) « Derniers gagnés » (3 derniers `won`, **jamais un perdu seul**) ; (4) Compte à rebours vers 08h00 UTC + bouton « Activer notifications » ; (5) CTA bonus bookmakers.

Données mises en cache 15 min (`CacheEmptyStateDataJob` / Redis). **KPIs** : scroll complet > 60 %, CTR notifs > 25 %, CTR bonus > 15 %, retour après timer > 40 %. **Règles** : jamais de pub sur cet écran ; disclaimer jeu responsable maintenu ; jamais affiché aux non-majeurs.

---

## 15. Site web (PWA) & page de téléchargement

### 15.1 Rôle

Le web (Blade + Tailwind, routes `routes/web.php`) est le **canal de distribution principal au lancement** (pas de Play Store en V1, cf. §23). Il sert aussi de **cible de la Telegram Mini App**.

### 15.2 Pages web publiques

| Route | Page |
|-------|------|
| `/` | Accueil (pronostics du jour) |
| `/live` | Matchs en direct |
| `/predictions` · `/predictions/{prediction}` | Liste + détail prédiction (accès public) |
| `/competitions` | Compétitions |
| `/favorites` | Favoris |
| `/history` · `/statistics` | Historique + statistiques (accès public à tous les visiteurs) |
| `/subscription` | Abonnement Premium |
| `/referral` | Parrainage |
| `/profile` | Profil (authentifié) |
| `/login` · `/register` · `/verify-otp` | Auth web |
| `/auth/{provider}/redirect` · `/callback` | OAuth social (Facebook…) |
| `/privacy` | Politique de confidentialité |
| `/r/{slug}` | Redirection lien influenceur tracké |
| `/.well-known/assetlinks.json` · `apple-app-site-association` | App Links / Universal Links |

### 15.3 Page de téléchargement

Trois objectifs : (1) **prouver la crédibilité** (win rate réel, derniers gains) ; (2) **donner envie** (combiné de bienvenue visible) ; (3) **lever le frein hors store** (mini-guide « installer l'APK en 3 étapes » pour passer l'avertissement Android).

---

## 16. Bot Telegram & Mini App

### 16.1 Architecture

- Bot connecté via **webhook** : `POST /api/telegram/webhook` (route protégée par token de vérification).
- Commande artisan `telegram:set-webhook` pour (dé)configurer le webhook.
- `TelegramService` : `sendMessage`, `sendPhoto`, `setWebhook`, `getWebhookInfo`, `formatPick`, `formatCoupon`.
- `SendTelegramBroadcastJob` : diffusion quotidienne (pronostics / coupon du jour) aux utilisateurs liés ayant opté.

### 16.2 Commandes de base

| Commande | Action |
|----------|--------|
| `/start` | Accueil + présentation + bouton Mini App |
| `/profile` | Profil et statut Premium |
| `/subscribe` | Lien vers l'abonnement |
| `/referral` | Code et lien de parrainage |

### 16.3 Liaison de compte

- Table `users` étendue : `telegram_id`, `preferred_bookmaker_id`.
- Liaison par **OTP unifié** : `GET /api/auth/telegram-link` génère un lien ; `POST /api/auth/unlink-telegram` délie.
- Un même compte COTA est utilisable depuis mobile, web et Telegram.

### 16.4 Mini App

- **Telegram Mini App** = WebView du site web COTA dans Telegram : expérience complète sans quitter l'app.
- Respecte les mêmes règles de communication et jeu responsable (§3, §22).

---

## 17. Dashboard admin

Routes `/admin/*` → `Http/Controllers/Admin/` → Blade + Tailwind CDN, super admins uniquement.

| Page | Fonctionnalité |
|------|----------------|
| Dashboard | Stats clés (users, revenus, win rate), actifs temps réel |
| Utilisateurs | Liste, détail, export, actions : `add-premium`, `lifetime-premium`, `revoke-premium` |
| Prédictions | Liste, régénération, résultats, `bulk-status`, override manuel |
| Abonnements | Liste, filtres, graphique revenus, accord manuel |
| Parrainages | Liste, top parrains, paliers récompenses |
| Bookmakers | CRUD, liens affiliation, codes promo |
| Bookmaker candidates | Fetch / approve / reject de bookmakers candidats |
| Bookmaker blogs | CRUD + génération blog IA + toggle featured |
| Compétitions | CRUD compétitions, tiers |
| Affiliations | Suivi clics & conversions, **RevShare par bookmaker et par joueur**, `verify` / `reject` / `bulk-verify` |
| Influenceurs | Liens trackés `/r/{slug}`, attribution |
| Feedbacks | Liste, détail, réponse admin |
| Stats avancées | Taux réussite, ROI, graphiques 30j/12m, par étoiles / type de pari / compétition |
| Paramètres | Clés API, Paydunya, AppConfig (`w_ext`, seuils, bandes de cotes), activation des sources |
| Monitoring APIs | Chart.js 7j, jauges quota, statuts providers |

---

## 18. Jobs schedulés

| Job | Fréquence | Rôle |
|-----|-----------|------|
| FetchMatchesJob | Horaire | Récupère les matchs à venir |
| RefreshOddsJob | Horaire + avant coup d'envoi | Cotes live Bet365 / 1xBet |
| GenerateAllPredictionsJob | 08h00 & 20h00 | Cascade multi-marchés + hybridation + analyse IA |
| GenerateCouponJob | 08h00 | Génère les 3 variantes |
| UpdateLiveScoresJob | 2 min | Scores en direct |
| UpdatePredictionResultsJob | 5 min | Résultats prédictions |
| SendDailyNotificationJob | 09h00 | Notification du jour (FCM v1) |
| SendTelegramBroadcastJob | Quotidien | Diffusion Telegram (pronostics / coupon) |
| SendPremiumExpiryReminderJob | Quotidien | Rappels J-7 / J-3 / J-1 |
| CheckBookmakerRegistrationsJob | 15 min | Vérifie inscriptions référées → octroie 7j Premium |
| CacheEmptyStateDataJob | 15 min | Cache des données Smart Empty State |

---

## 19. Monétisation

### 19.1 Abonnement Premium

| Plan | Prix indicatif | Durée |
|------|----------------|-------|
| Hebdomadaire | 2 500 FCFA | 7 jours |
| Mensuel | 8 000 FCFA | 30 jours |
| Trimestriel | 20 000 FCFA | 90 jours |
| Annuel / VIP | à arbitrer (§28) | 360 jours |

### 19.2 Paiement V1

Provider Paydunya · méthodes Wave, Orange Money, MTN, Moov · clés gérées depuis l'admin (AppConfig en base, pas dans `.env`) · webhook `/api/webhooks/paydunya` active Premium automatiquement · fallback accord manuel depuis l'admin.

### 19.3 Parrainage

| Palier | Récompense |
|--------|-----------|
| 1 filleul | 3 jours Premium |
| 5 filleuls | 7 jours Premium |
| 10 filleuls | 30 jours Premium |
| 20 filleuls | Premium à vie |

Cashback 10 % sur achats des filleuls → Phase 2.

### 19.4 Free vs Premium

| | Gratuit | Premium |
|---|---|---|
| Pronostics simples/jour | 1–3 fiables | 10–15 selon volume |
| Coupon prudent | ✅ | ✅ |
| Coupon équilibré / audacieux | ❌ | ✅ |
| Win rate visible | ✅ | ✅ |
| Notifs « bon coup » · Live | ❌ | ✅ |
| Historique | 7j | 30j |
| Stats détaillées | ❌ | ✅ |
| Publicité | ✅ | ❌ |

**Ne PAS mettre en premium** ce qui fait parier (liens bookmakers, codes promo) : cela réduirait l'affiliation.

### 19.5 Projections (à reconstruire en base RevShare réelle, §28)

| Phase | MRR cible indicatif |
|-------|---------------------|
| Mois 4 | 500 € |
| Mois 8 | 3 000 € |
| Mois 12 | 8 000 € |
| Mois 18 | 15 000–25 000 € |

---

## 20. Affiliation & tracking d'attribution

### 20.1 Modèle RevShare

**RevShare 25–40 %** sur l'activité des joueurs référés (récurrent), **pas CPA fixe**. Métrique n°1 = joueurs référés **actifs et retenus**, pas inscriptions brutes. COTA est conçu pour ça : pronostics quotidiens, notifs « bon coup », facilité de pari.

### 20.2 Boucle d'acquisition

```
Utilisateur clique bonus 7j Premium → redirection trackée vers bookmaker
  → inscription → attribution (postback S2S ou job) → 7j Premium activés
```

### 20.3 Attribution — deux voies (v4.0)

- **Automatique (Postback S2S)** : postbacks `{reg}` et `{ftd}` avec `subid = user_id COTA`, configurés avec le manager **1xPartners**. Octroi du bonus 7 jours immédiat sur réception. `CheckBookmakerRegistrationsJob` (15 min) rattrape les conversions manquées.
- **Manuelle (Player Report)** : pour inscriptions anciennes / non trackées. L'utilisateur saisit son ID joueur bookmaker → vérification semi-automatique dans le CSV / manuelle par un admin dans le Player Report → octroi du bonus après validation.

### 20.4 Sélection bookmaker

Déclenchée **au moment du pari / de l'obtention du bonus** (pas au premier lancement). Un routeur détermine la voie de vérification (auto/manuelle), génère le lien tracké du bon partenaire, et indique quel Player Report consulter en fallback.

### 20.5 Garde-fou

Chaque clic passe par `POST /api/bookmakers/{id}/click`. Trafic propre + jeu responsable protègent le compte affilié contre audits, gels et bannissements bookmaker.

---

## 21. Notifications

**Canaux** : push FCM HTTP v1 · in-app persistées · SMS (Termii) pour OTP uniquement en V1 · Telegram broadcast.

**Types** : pronostics du jour (09h00) · coupon IA (08h00) · résultat prédiction (fin de match) · abonnement expiré (J-7/J-3/J-1) · parrainage validé · inscription bookmaker confirmée (7j Premium) · match favori dans 1h (Premium).

**Anti-spam** : Gratuit max 3/jour, Premium max 5/jour, silence 23h–07h. Priorité : Contextuel > Événementiel > Routine > Re-engagement. Préférences modifiables via `PUT /api/notifications/settings`.

---

## 22. Sécurité, conformité, jeu responsable

- **Technique** : HTTPS obligatoire ; CORS limité aux domaines COTA ; rate limiting OTP 10/min ; secrets hors Git ; rotation tokens Sanctum ; webhook Telegram protégé par token.
- **RGPD** : export (`POST /api/user/data-export`), suppression (`DELETE /api/user/data-delete`), politique publique, logs sans données sensibles, **popup de consentement avant activation du tracking** (requis dès le premier lancement).
- **Jeu responsable** : avertissement dans abonnement, coupon, prédictions, FAQ, page de téléchargement, analyse IA, messages Telegram. Interdit : présenter COTA comme bookmaker, promettre des gains, manipuler les résultats. Jamais affiché aux non-majeurs. Ne jamais inciter un utilisateur en perte de contrôle à parier davantage.

---

## 23. Distribution & acquisition

- **Canal principal** : web (PWA) + **APK direct** depuis la page web. **Pas de Play Store au lancement** (risque de bannissement à cause des liens d'affiliation). Play Store envisagé plus tard en version édulcorée.
- **Acquisition virale** : bouche-à-oreille (parrainage), groupes WhatsApp/Facebook foot, forums, **Telegram**, influenceurs (lien/code tracké unique `/r/{slug}` par influenceur).
- **Programme de lancement** : J-30 landing waitlist + 50 beta ; J-15 beta privée ; J-7 teasing/countdown ; **J0 = 9 juin 2026** lancement + parrainage + campagne influenceurs ; J+30 bilan.
- **Funnel** : Awareness → Téléchargement (30 %) → Activation (70 %) → Engagement (60 %) → Monétisation : abonnement OU inscription bookmaker (25 %) → Rétention (50 %) → Advocacy (30 %).

---

## 24. Roadmap & phases

**Phase A — Préproduction (jusqu'à J-15)** : `.env` prod, HTTPS, CORS restreint, migrations prod, queue + scheduler durables, Firebase service account, build mobile `APP_BASE_URL` prod, tests bout-en-bout (Paydunya, FCM, OTP), **tracking d'attribution affilié bout en bout**, webhook Telegram opérationnel.

**Phase B — Lancement V1 (9 juin 2026)** : publier APK + page de téléchargement, finaliser confidentialité, monitoring erreurs, logs/rotation, perf réseau lent, remplacer les liens affiliation placeholder.

**Phase C — Growth V1.1 (post-Mondial)** : Smart Empty State, notifications personnalisées, stats enrichies, conversion free → Premium, pages bookmakers, acquisition organique, analytics (PostHog, GA4, Branch.io).

### Priorités critiques avant le 9 juin

1. **Tracking d'attribution bout en bout** (RevShare nul si mal attribué) — tester clic → inscription → activité → remontée dashboard.
2. Boucle 7j Premium ↔ inscription bookmaker (`CheckBookmakerRegistrationsJob`) + postback S2S.
3. Page de téléchargement web + guide d'installation APK.
4. Cascade multi-marchés minimale (1X2 + Double Chance + Over/Under + BTTS).
5. Cotes indicatives + bandes par marché.
6. Coupon : variante prudente publiée chaque jour.
7. Bot Telegram opérationnel (commandes + broadcast).

---

## 25. KPIs & analytics

**Outils** : PostHog (produit), GA4 (web), Branch.io (deep links + attribution).

**Events clés** : `app_installed{source}`, `signup_completed{method,country}`, `first_prediction_viewed`, `first_coupon_opened{variant}`, `prediction_clicked`, `bookmaker_link_clicked`, `subscription_started/renewed`, `bookmaker_registration_confirmed`, `premium_granted_via_bookmaker`, `app_opened{day_count}`, `referral_shared/completed`.

| KPI hebdomadaire | Cible |
|------------------|-------|
| DAU / MAU | ≥ 40 % |
| Conversion free → paid | ≥ 15 % |
| Conversion free → inscription bookmaker | ≥ 20 % |
| Churn mensuel | < 30 % |
| Win rate algo global | ≥ 55 % |
| Win rate algo par type de pari | suivre §7.5 |

---

## 26. Definitions of done

**Feature terminée si** : backend fonctionnel OU mobile gère l'absence de backend ; erreurs API traitées ; `php artisan test --stop-on-failure` passe ; `flutter analyze` 0 erreur ; 4 états gérés ; comportement cohérent invité/gratuit/Premium ; logs utiles ; aucun TODO.

**Auth (OTP+PIN+Telegram)** : OTP envoyé/validé/refusé correctement ; token Sanctum retourné ; `/api/auth/me` OK ; PIN proposé après OTP ; 5 PIN erronés → verrouillage ; reset via OTP ; nouvel appareil exige OTP ; liaison Telegram fonctionne ; endpoints protégés refusent non-authentifié.

**Prédictions (cascade)** : `today` exploitable ; champs cards complets ; Premium débloqué ; Smart Empty State si vide ; ≥ 4 marchés actifs sur données fiables ; aucun pronostic sous seuil ; cote dans sa bande.

**Coupon** : variantes disponibles ; prudent publié si volume suffisant ; picks complets (cote, confiance, match, prédiction, marché, heure) ; contradictoires exclus ; win rate par variante.

**Paiement** : plans retournés ; `purchase` crée facture Paydunya ; webhook active Premium ; statut visible via `me`.

**Affiliation & RevShare** : clic via `POST /api/bookmakers/{id}/click` ; lien transporte le tracking (`subid`) ; test bout-en-bout clic → inscription → activité → remontée ; postback S2S + `CheckBookmakerRegistrationsJob` activent 7j Premium.

**Notifications** : token enregistré ; settings GET/PUT ; job routine envoie ; notification dans l'historique in-app.

---

## 27. Évolutions V2+

- **Algo** : xG (10 %), blessures (6 %), hybridation ML scikit-learn, backtesting continu.
- **Produit** : marketplace caissiers, coaching humain, marketplace pronostiqueurs, API B2B, multi-sports, fantasy, auto-fill coupons, carte à gratter, contexte qualitatif IA, value betting.
- **Infra** : CDN assets, Sentry, CI/CD GitHub Actions.
- **Monétisation** : crypto (USDT diaspora), cashback parrainage 10 %, partenariat officiel bookmakers (commission 5–8 %), API B2B sous licence.

---

## 28. Décisions en attente

| # | Point | Bloque |
|---|-------|--------|
| D1 | Source externe de pronostic : fournisseur + format | §9 |
| D2 | Base de calcul RevShare (% NGR vs % volume) + % exact + hybride CPA+RevShare | §19.5, §20 |
| D3 | Fournisseur LLM (Anthropic / OpenAI) + budget | §10 |
| D4 | Prix du plan Annuel / VIP | §19.1 |
| D5 | Backtest par type de pari avant figeage des seuils & bandes | §7.5, §11 |
| D6 | Confirmer couverture cotes (Bet365 / 1xBet) en production | §11 |
| D7 | Stratégie Play Store post-lancement (version édulcorée, calendrier) | §23 |
| D8 | Format exact des postbacks `{reg}` / `{ftd}` avec 1xPartners | §20.3 |

---

*COTA — Cahier des charges MASTER | 2026-06-10 | MHD SERVICE*
*Source de vérité unique. Couvre backend, mobile, web, Telegram, admin. Remplace toutes les versions antérieures et documents complémentaires.*
