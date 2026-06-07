# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# COTA — Contexte Global

Application mobile de prédictions football automatisées avec IA.

## Structure du projet

```
COTA/
├── backend/    ← API Laravel 12 (REST)
├── mobile/     ← Application Flutter (iOS/Android)
└── CDC/        ← Cahier des charges
```

## Vue d'ensemble

**COTA** est une application mobile qui génère des prédictions sportives via un algorithme IA à 9 critères. Les utilisateurs (gratuits et premium) reçoivent des pronostics avec scores de confiance, cotes estimées et coupons combinés quotidiens.

## Stack technique

| Couche   | Technologie                                      |
|----------|--------------------------------------------------|
| Backend  | Laravel 12, MySQL/SQLite, Redis, Sanctum          |
| Mobile   | Flutter, Dart, Riverpod, Dio, GoRouter            |
| APIs     | API-Football, Sportradar, OpenWeatherMap, Paydunya|

## Fonctionnalités principales

- Prédictions quotidiennes avec niveau de confiance (1–4 étoiles)
- Coupon IA combiné (4–5 meilleurs picks du jour)
- Filtrage par compétitions populaires (Tier 1–4)
- Authentification OTP (SMS/email) + Facebook OAuth
- Abonnement Premium (Wave, Orange Money, MTN, Moov via Paydunya)
- Système de parrainage et liens affiliés bookmakers

## Communication backend ↔ mobile

- Base URL configurée dans `mobile/lib/core/api/api_client.dart`
- Client HTTP : Dio avec intercepteurs Bearer token (Sanctum)
- Format : JSON, toutes les dates en ISO 8601

## Statut MVP

- [x] Algorithme de prédiction v3.0 (9 critères)
- [x] Authentification OTP + Facebook
- [x] Endpoints prédictions (today, coupon, history)
- [x] Matchs populaires filtrés par tier de ligue
- [x] Dashboard admin — pages Feedbacks (liste + détail + réponse admin)
- [x] Dashboard admin — pages Utilisateurs, Prédictions, Affiliations, Bookmakers, Compétitions, Paramètres
- [x] Flutter analyze — 0 erreur / 0 warning (18 fichiers nettoyés)
- [x] Paiement Paydunya — architecture provider-agnostique (PaymentGatewayService + AppConfig DB), clés depuis dashboard admin, bug verifyPayment corrigé, seeder AppConfig
- [x] Système de parrainage complet
- [x] Dashboard admin — pages Abonnements (liste, filtres, graphique revenus, accord manuel)
- [x] Dashboard admin — page Parrainages (liste, top parrains, paliers récompenses)
- [x] Dashboard admin — Stats avancées (taux réussite, ROI, graphiques 30j/12m, par étoiles/type/compétition)
- [x] Bookmakers par région — détection IP automatique, groupement par continent, cards alignées (1xBet/Betwinner/Melbet Afrique de l'Ouest, Bet365/Betclic Europe…)


## Règles Claude Code

## Stack
- Backend : Laravel 12, PHP 8.3, MySQL/SQLite, Redis, Pest
- Mobile : Flutter 3.x, Dart, Riverpod, GoRouter, Dio
- Structure : backend/ → API REST | mobile/ → App Flutter

---

## Commandes backend (cd backend/)

```bash
# Développement
php artisan serve                    # Lance le serveur (port 8000)
php artisan queue:work               # Worker pour les Jobs (obligatoire)
php artisan schedule:work            # Scheduler local (remplace crontab)

# Base de données
php artisan migrate
php artisan db:seed

# Tests
php artisan test                     # Tous les tests (28 tests, 59 assertions)
php artisan test --filter NomTest    # Un test unitaire précis
php artisan test tests/Feature/      # Uniquement les tests Feature

# Vérification syntaxe
php -l app/Services/MonService.php

# Jobs manuels
php artisan tinker  # puis App\Jobs\GenerateAllPredictionsJob::dispatch()
```

## Commandes mobile (cd mobile/)

```bash
flutter pub get       # Dépendances
flutter analyze       # Lint — doit passer à 0 erreur avant toute tâche terminée
flutter run           # Lancer sur émulateur/device (confirmation requise)
flutter test          # Tests unitaires Flutter
```

---

## Architecture backend

**Flux d'une requête API mobile :**
`routes/api.php` → `Http/Controllers/Api/` → `app/Services/` → Modèle/Cache Redis

**Services clés :**
- `PredictionAlgorithmService` — algorithme 9 critères, score 0–100 pts
- `PaymentGatewayService` + `Services/Payment/Drivers/` — pattern driver pour Paydunya (clés en base via `AppConfig`, pas dans `.env`)
- `Services/ApiGateway/ApiGatewayService` — agrège API-Football + Sportradar avec fallback, quota tracking et cache Redis
- `FootballApiService` / `TheSportsDbService` / `OddsApiService` — adapters individuels

**Groupes de routes API (`routes/api.php`) :**
- Public throttle 60/min : `/predictions/*`, `/matches/*`, `/teams/*`, `/bookmakers`, `/odds/*`
- Auth throttle 10/min : `/auth/send-otp`, `/auth/verify-otp`, `/auth/facebook`, `/auth/login-pin`
- Authentifié (Sanctum) : `/user/*`, `/subscription/*`, `/referral/*`, `/notifications/*`

**Dashboard Admin :** routes `/admin/*` → `Http/Controllers/Admin/` → vues Blade + Tailwind CDN dans `resources/views/admin/`

**Jobs schedulés critiques :**
- `FetchMatchesJob` — toutes les heures
- `GenerateAllPredictionsJob` — 08h00 et 20h00
- `UpdateLiveScoresJob` — toutes les 2 min
- `UpdatePredictionResultsJob` — toutes les 5 min
- `SendDailyNotificationJob` — 09h00 (FCM v1)

---

## Architecture mobile

**Structure feature-first :** `mobile/lib/features/{nom}/{ui,logic,data}/`

Features : `auth`, `predictions`, `subscription`, `referral`, `profile`, `bookmaker`, `affiliate`, `notifications`

**Couche core :** `mobile/lib/core/`
- `api/api_client.dart` — Dio + intercepteur Bearer token Sanctum, base URL configurée ici
- `routing/` — GoRouter
- `providers/` — Riverpod global providers
- `theme/` — Design system tokens

**Niveaux de confiance prédictions (filtrage premium) :**
- ≥ 85 pts → ⭐⭐⭐⭐ (Premium)
- 70–84 pts → ⭐⭐⭐ (Premium)
- 60–69 pts → ⭐⭐ (libre)
- 50–59 pts → ⭐ (libre)
- < 50 pts → non publié

---

## 🧠 Graphify — Lire le graph avant d'explorer
- Avant toute exploration de fichier, lire graphify-out/GRAPH_REPORT.md
- Naviguer par les "god nodes" du graph, pas en lisant les fichiers bruts
- Utiliser /graphify query "..." pour localiser un concept précis
- Ne jamais lancer un Glob ou Grep sans consulter le graph d'abord
- Si le graph n'existe pas : taper /graphify . pour le générer

---

## ⚡ Économie de tokens
- Ne jamais relire un fichier déjà lu dans la session sauf si modifié
- Ne pas expliquer ce que tu vas faire, agir directement
- Réponses courtes sauf si explication explicitement demandée
- Ne pas reformuler ma question avant de répondre
- Ne modifier que les fichiers strictement nécessaires à la tâche
- Ne pas réécrire un fichier entier pour un petit changement, edits ciblés uniquement
- Pas de refacto non demandé
- Si la tâche touche > 3 fichiers → demander confirmation avant d'explorer

---

## 🔍 Exploration du code
- Avant d'explorer, demander : "quel fichier est concerné ?"
- Lire uniquement les fichiers liés à la tâche en cours
- Si besoin du contexte d'un fichier, lire uniquement la section pertinente
- Ne jamais lancer php artisan ou flutter run sans confirmation explicite

---

## 🔴 Qualité Laravel
- Form Requests obligatoires pour toute validation, jamais dans le Controller
- API Resources obligatoires pour tout retour JSON
- Logique métier dans app/Services/, jamais dans les Controllers
- Typage strict PHP 8.3 sur tous les paramètres et retours
- Une méthode = une responsabilité, max 20 lignes sinon découper
- Avant de terminer : vérifier php -l sur les fichiers modifiés + php artisan test

---

## 🔵 Qualité Flutter
- Architecture feature-first : features/{nom}/ui/ logic/ data/
- Jamais de logique dans les widgets, toujours dans un Provider/Controller
- Nommer les widgets de manière explicite : UserProfileCard, pas Card2
- Extraire tout widget réutilisé plus d'une fois dans /widgets/
- Toujours gérer les 4 états : loading / error / empty / success
- Avant de terminer : flutter analyze doit passer sans erreur

---

## ✅ Quality Gate — avant toute tâche terminée
1. Laravel : php -l → php artisan test → pas de TODO laissé
2. Flutter : flutter analyze → 4 états gérés → pas de logique dans les widgets
3. Jamais modifier un test pour le faire passer, corriger le code
3. apres chaque sprint recu toujours commite ou push a mon nom 

---

## ❌ Erreurs à ne pas reproduire
- ne jamais commit ou push au nom de claude

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:
- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- For cross-module "how does X relate to Y" questions, prefer `graphify query "<question>"`, `graphify path "<A>" "<B>"`, or `graphify explain "<concept>"` over grep — these traverse the graph's EXTRACTED + INFERRED edges instead of scanning files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)
  mainAxisAlignment: start
  mainAxisSize: max
  crossAxisAlignment: center
  textDirection: ltr
  verticalDirection: down
  spacing: 0.0
◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤◢◤
════════════════════════════════════════════════════════════════════════════════════════════════════

Another exception was thrown: A RenderFlex overflowed by 52 pixels on the right.
Another exception was thrown: A RenderFlex overflowed by 52 pixels on the right.
Another exception was thrown: A RenderFlex overflowed by 92 pixels on the right.
I/flutter ( 2698): 
I/flutter ( 2698): ╔╣ Request ║ GET 
I/flutter ( 2698): ║  http://192.168.11.148:8000/api/matches/live
I/flutter ( 2698): ╚══════════════════════════════════════════════════════════════════════════════════════════╝
I/flutter ( 2698): ╔ Headers 
I/flutter ( 2698): ╟ Content-Type: application/json
I/flutter ( 2698): ╟ Accept: application/json
I/flutter ( 2698): ╟ contentType: application/json
I/flutter ( 2698): ╟ responseType: ResponseType.json
I/flutter ( 2698): ╟ followRedirects: true
I/flutter ( 2698): ╟ connectTimeout: 0:00:30.000000
I/flutter ( 2698): ╟ receiveTimeout: 0:00:30.000000
I/flutter ( 2698): ╚══════════════════════════════════════════════════════════════════════════════════════════╝
I/flutter ( 2698): 
I/flutter ( 2698): ╔╣ Response ║ GET ║ Status: 200 OK  ║ Time: 144 ms
I/flutter ( 2698): ║  http://192.168.11.148:8000/api/matches/live
I/flutter ( 2698): ╚══════════════════════════════════════════════════════════════════════════════════════════╝
I/flutter ( 2698): ╔ Body
I/flutter ( 2698): ║
I/flutter ( 2698): ║    {
I/flutter ( 2698): ║         "success": true,
I/flutter ( 2698): ║         "data": [
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1416121",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T11:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Mines",
I/flutter ( 2698): ║                 "away_team": "ZESCO United",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/26200.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/5214.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 1,
I/flutter ( 2698): ║                 "competition": "Super League",
I/flutter ( 2698): ║                 "competition_id": "400",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/400.png",
I/flutter ( 2698): ║                 "country": "Zambia",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "First Half",
I/flutter ( 2698): ║                 "elapsed_time": 14,
I/flutter ( 2698): ║                 "venue": "Godfrey Chitalu Stadium"
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1470811",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T11:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Bandari",
I/flutter ( 2698): ║                 "away_team": "APS Bomet",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/2460.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/19476.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "FKF Premier League",
I/flutter ( 2698): ║                 "competition_id": "276",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/276.png",
I/flutter ( 2698): ║                 "country": "Kenya",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "First Half",
I/flutter ( 2698): ║                 "elapsed_time": 5,
I/flutter ( 2698): ║                 "venue": null
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1523159",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T11:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Shenyang Urban",
I/flutter ( 2698): ║                 "away_team": "Qingdao Jonoon",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/5684.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/1431.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "Super League",
I/flutter ( 2698): ║                 "competition_id": "169",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/169.png",
I/flutter ( 2698): ║                 "country": "China",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "First Half",
I/flutter ( 2698): ║                 "elapsed_time": 17,
I/flutter ( 2698): ║                 "venue": "Olympic Sports Centre Stadium"
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1535365",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T11:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Svay Rieng",
I/flutter ( 2698): ║                 "away_team": "Boeung Ket",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/5396.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/5387.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "Hun Sen Cup",
I/flutter ( 2698): ║                 "competition_id": "1174",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/1174.png",
I/flutter ( 2698): ║                 "country": "Cambodia",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "First Half",
I/flutter ( 2698): ║                 "elapsed_time": 17,
I/flutter ( 2698): ║                 "venue": "Svay Rieng Stadium"
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1536554",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T11:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Avangard Kursk",
I/flutter ( 2698): ║                 "away_team": "Metallurg Lipetsk",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/2009.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/6815.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "Second League - Group 3",
I/flutter ( 2698): ║                 "competition_id": "650",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/650.png",
I/flutter ( 2698): ║                 "country": "Russia",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "First Half",
I/flutter ( 2698): ║                 "elapsed_time": 16,
I/flutter ( 2698): ║                 "venue": "Trudovye Rezervy Stadium"
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1538432",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T10:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Naegohyang W",
I/flutter ( 2698): ║                 "away_team": "Suwon FMC",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/26464.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/14379.png",
I/flutter ( 2698): ║                 "home_score": 1,
I/flutter ( 2698): ║                 "away_score": 1,
I/flutter ( 2698): ║                 "competition": "AFC Women's Champions League",
I/flutter ( 2698): ║                 "competition_id": "1140",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/1140.png",
I/flutter ( 2698): ║                 "country": "World",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "Second Half",
I/flutter ( 2698): ║                 "elapsed_time": 59,
I/flutter ( 2698): ║                 "venue": "Suwon Sports Complex"
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1541068",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T10:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Sheger Ketema",
I/flutter ( 2698): ║                 "away_team": "Mekelle Kenema",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/26880.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/4121.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "Premier League",
I/flutter ( 2698): ║                 "competition_id": "363",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/363.png",
I/flutter ( 2698): ║                 "country": "Ethiopia",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "Second Half",
I/flutter ( 2698): ║                 "elapsed_time": 55,
I/flutter ( 2698): ║                 "venue": null
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1542402",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T10:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Hunters",
I/flutter ( 2698): ║                 "away_team": "Ulaanbaatar",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/24722.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/16854.png",
I/flutter ( 2698): ║                 "home_score": 0,
I/flutter ( 2698): ║                 "away_score": 2,
I/flutter ( 2698): ║                 "competition": "Premier League",
I/flutter ( 2698): ║                 "competition_id": "764",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/764.png",
I/flutter ( 2698): ║                 "country": "Mongolia",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "Second Half",
I/flutter ( 2698): ║                 "elapsed_time": 59,
I/flutter ( 2698): ║                 "venue": null
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1545183",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T10:00:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Benfica U23",
I/flutter ( 2698): ║                 "away_team": "Santa Clara U23",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/15460.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/21719.png",
I/flutter ( 2698): ║                 "home_score": 2,
I/flutter ( 2698): ║                 "away_score": 1,
I/flutter ( 2698): ║                 "competition": "Taça Revelação U23",
I/flutter ( 2698): ║                 "competition_id": "840",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/840.png",
I/flutter ( 2698): ║                 "country": "Portugal",
I/flutter ( 2698): ║                 "status": "live",
I/flutter ( 2698): ║                 "match_status": "Second Half",
I/flutter ( 2698): ║                 "elapsed_time": 51,
I/flutter ( 2698): ║                 "venue": null
I/flutter ( 2698): ║            },
I/flutter ( 2698): ║            {
I/flutter ( 2698): ║                 "id": "1544088",
I/flutter ( 2698): ║                 "start_time": "2026-05-20T10:15:00+00:00",
I/flutter ( 2698): ║                 "home_team": "Tuloy",
I/flutter ( 2698): ║                 "away_team": "Maharlika",
I/flutter ( 2698): ║                 "home_team_logo": "https://media.api-sports.io/football/teams/21580.png",
I/flutter ( 2698): ║                 "away_team_logo": "https://media.api-sports.io/football/teams/16191.png",
I/flutter ( 2698): ║                 "home_score": 1,
I/flutter ( 2698): ║                 "away_score": 0,
I/flutter ( 2698): ║                 "competition": "PFL",
I/flutter ( 2698): ║                 "competition_id": "765",
I/flutter ( 2698): ║                 "competition_logo": "https://media.api-sports.io/football/leagues/765.png",
I/flutter ( 2698): ║                 "country": "Philippines",
I/flutter ( 2698): ║                 "status": "halftime",
I/flutter ( 2698): ║                 "match_status": "Halftime",
I/flutter ( 2698): ║                 "elapsed_time": 45,
I/flutter ( 2698): ║                 "venue": null
I/flutter ( 2698): ║            }
I/flutter ( 2698): ║         ],
I/flutter ( 2698): ║         "meta": {count: 10}
I/flutter ( 2698): ║    }
I/flutter ( 2698): ║
I/flutter ( 2698): ╚══════════════════════════════════════════════════════════════════════════════════════════╝
D/DecorView[]( 2698): onWindowFocusChanged hasWindowFocus false
W/BpBinder( 2698): Slow Binder: BpBinder transact took 213 ms, interface=android.app.IActivityClientController, code=3 oneway=false
W/Looper  ( 2698): PerfMonitor looperActivity : package=com.cotafoot.app/.MainActivity time=6ms latency=592ms running=0ms  procState=-1  historyMsgCount=2 (msgIndex=1 wall=214ms seq=213 late=276ms h=android.app.ActivityThread$H w=159)
I/BufferQueueProducer( 2698): [SurfaceView[com.cotafoot.app/com.cotafoot.app.MainActivity]#1(BLAST Consumer)1](id:a8a00000001,api:1,p:2698,c:2698) disconnect: api 1
I/BufferQueueProducer( 2698): [SurfaceView[com.cotafoot.app/com.cotafoot.app.MainActivity]#1(BLAST Consumer)1](id:a8a00000001,api:0,p:-1,c:2698) disconnect: api -1
W/Looper  ( 2698): PerfMonitor looperActivity : package=com.cotafoot.app/.MainActivity time=49ms latency=784ms running=0ms  procState=-1  historyMsgCount=7
I/Choreographer( 2698): Skipped 52 frames!  The application may be doing too much work on its main thread.
I/BufferQueueProducer( 2698): [ViewRootImpl[MainActivity]#0(BLAST Consumer)0](id:a8a00000000,api:1,p:2698,c:2698) disconnect: api 1
I/BLASTBufferQueue( 2698): [ViewRootImpl[MainActivity]#0] destructor()
I/BufferQueueConsumer( 2698): [ViewRootImpl[MainActivity]#0(BLAST Consumer)0](id:a8a00000000,api:0,p:-1,c:2698) disconnect
I/GED     ( 2698): ged_boost_gpu_freq, level 100, eOrigin 2, final_idx 31, oppidx_max 31, oppidx_min 0
I/GED     ( 2698): ged_boost_gpu_freq, level 100, eOrigin 2, final_idx 31, oppidx_max 31, oppidx_min 0
W/Looper  ( 2698): PerfMonitor doFrame : time=18ms vsyncFrame=0 latency=867ms procState=-1 historyMsgCount=8
I/BLASTBufferQueue( 2698): releaseBufferCallbackThunk bufferId:11587821764620 framenumber:1 blastBufferQueue is dead
Lost connection to device.