# COTA — Application de Pronostics Football IA

Application mobile de prédictions football automatisées avec algorithme IA à 9 critères, value betting, détection d'anomalies de cotes et analyse coup sûr.

## Stack technique

| Couche    | Technologie                                         |
|-----------|-----------------------------------------------------|
| Backend   | Laravel 12, PHP 8.3, MySQL/SQLite, Redis, Sanctum   |
| Mobile    | Flutter 3.x, Dart, Riverpod, Dio, GoRouter          |
| API Sport | API-Football, Sportradar, RapidAPI (fallback)       |
| Paiement  | Paydunya (Wave, Orange Money, MTN, Moov)            |
| Notifs    | Firebase Cloud Messaging (FCM v1)                   |
| Météo     | OpenWeatherMap                                      |

## Structure du projet

```
COTA/
├── backend/    ← API Laravel 12 (REST)
│   ├── app/
│   │   ├── Console/Commands/    ← Commandes artisan
│   │   ├── Http/Controllers/
│   │   │   ├── Admin/           ← Dashboard admin (Blade)
│   │   │   └── Api/             ← API mobile (JSON)
│   │   ├── Jobs/                ← Jobs schedulés (fetch, prédictions, notifs)
│   │   ├── Models/
│   │   └── Services/
│   │       ├── Payment/         ← PaymentGatewayService + drivers
│   │       ├── ValueBettingService.php     ← EV+ et Kelly Criterion
│   │       ├── OddsAnomalyDetectorService.php ← Détection anomalies cotes
│   │       └── SureBetAnalysisService.php  ← Analyse coup sûr 95/99%
│   ├── database/
│   └── resources/views/admin/   ← Dashboard Blade + Tailwind CDN
├── mobile/     ← Application Flutter
│   └── lib/
│       ├── core/
│       │   ├── api/             ← ApiClient (Dio)
│       │   ├── routing/         ← GoRouter
│       │   └── services/        ← CacheService (cache 24h), OddsService
│       └── features/
│           ├── auth/
│           ├── predictions/     ← PredictionCard, CouponScreen, LiveScreen
│           ├── bookmaker/       ← Carousel auto-scroll + liens détail
│           ├── subscription/
│           ├── referral/
│           └── profile/
├── CDC/        ← Cahier des charges et spécifications
├── design/     ← Assets de marque, icônes, logos et maquettes
└── archive/    ← Anciennes bases techniques conservées pour référence
```

## Démarrage backend

```bash
cd backend

# Dépendances
composer install

# Environnement
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Serveur de développement (accessible depuis le réseau local)
php artisan serve --host=0.0.0.0 --port=8000

# Worker queue (dans un autre terminal)
php artisan queue:work
```

### Variables d'environnement essentielles

```env
# Base de données
DB_CONNECTION=sqlite        # ou mysql en production

# API Football
FOOTBALL_API_KEY=your_key

# Firebase (notifications push)
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_CREDENTIALS_PATH=/path/to/firebase-service-account.json

# Scheduler (crontab en production)
# * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

> Les clés Paydunya se configurent depuis le **Dashboard Admin → Paramètres → Paiement** (stockées en base, pas dans `.env`).

## Démarrage mobile

```bash
cd mobile

# Dépendances
flutter pub get

# Analyser le code (doit passer à 0 erreur)
flutter analyze

# Lancer sur device Android (avec URL backend explicite)
flutter run -d <device_id> --dart-define=APP_BASE_URL=http://<ip_locale>:8000/api

# Lancer sur émulateur
flutter run
```

## Algorithme de prédiction (v3.0)

L'algorithme évalue chaque match sur 9 critères pondérés (total 100 points) :

| Critère              | Poids |
|----------------------|-------|
| Forme récente        | 25%   |
| Head-to-Head         | 20%   |
| Performances dom/ext | 15%   |
| Position au classement | 12% |
| Statistiques de buts | 10%   |
| Horaire du match     | 8%    |
| Météo                | 5%    |
| Tirs cadrés          | 3%    |
| Forme physique       | 2%    |

**Niveaux de confiance :**
- ⭐⭐⭐⭐ 85–100 pts — Premium uniquement
- ⭐⭐⭐ 70–84 pts — Premium uniquement
- ⭐⭐ 60–69 pts — Accès libre
- ⭐ 50–59 pts — Accès libre
- < 50 pts — Non publié

## Value Betting & Kelly Criterion

Chaque prédiction est évaluée sur sa valeur espérée :

- **EV+ (Expected Value)** : `value_score = (confiance/100) × cote - 1`. Si > 0.05 → pari à valeur positive, badge vert `+X%` affiché sur la card.
- **Kelly Criterion** : mise conseillée = Kelly fractionnel à 25%, plafonné à 10% de la bankroll. Affiché en FCFA sur la page détail.

## Détection d'anomalies de cotes

`OddsAnomalyDetectorService` tourne toutes les 15 minutes :
- Compare les cotes 1xBet live vs les cotes de référence de l'algorithme
- Seuil d'anomalie : écart > 35%
- TTL anomalie : 20 minutes (les bookmakers corrigent vite)
- Notification admin instantanée pour chaque anomalie détectée
- Endpoint : `GET /api/odds-anomalies/live`

## Analyse Coup Sûr (95% / 99%)

`SureBetAnalysisService` s'exécute quotidiennement à 09h00 UTC sur les ligues Tier 1-2 :

Pipeline en 5 étapes pour les favoris avec cote entre 1.05 et 2.20 :
1. Validation de la fourchette de cote
2. Vérification des blessures (API-Football, ≤ 3 absents)
3. Conditions météo (OpenWeatherMap, pas d'extrême)
4. Score de forme récente ≥ 12.0/25
5. Score Head-to-Head ≥ 10.0/20

- **5/5 checks + cote ≥ 1.80** → classé `99%` (COUP SÛR)
- **5/5 checks** → classé `95%`
- **4/5 sans blessure** → classé `95%`
- Endpoint : `GET /api/predictions/sure-bets`

## PredictionCard — Design

La carte de prédiction met visuellement en valeur l'équipe prédite :

| `bet_type`     | `prediction` | Rendu                                      |
|----------------|--------------|--------------------------------------------|
| `1X2`          | `1`          | Équipe **domicile** en blanc gras          |
| `1X2`          | `2`          | Équipe **extérieure** en blanc gras        |
| `1X2`          | `X`          | Les deux en gris (nul)                     |
| `Double Chance`| `1X`         | Équipe **domicile** favorisée              |
| `Double Chance`| `X2`         | Équipe **extérieure** favorisée            |
| `Double Chance`| `12`         | Les deux actives                           |
| `BTTS`         | `Oui`        | Les deux équipes actives                   |
| `Over/Under`   | `Over 2.5`   | Neutre (pas d'équipe spécifique)           |

## Cache local (24h)

Toutes les données sont mises en cache 24h via `CacheService` (SharedPreferences) :

| Données              | Clé cache                    | TTL   |
|----------------------|------------------------------|-------|
| Prédictions du jour  | `predictions_YYYY-MM-DD`     | 24h   |
| Coupon quotidien     | `coupon_daily`               | 24h   |
| Historique           | `predictions_history_*`      | 24h   |
| Statistiques         | `predictions_statistics`     | 24h   |
| Bookmakers par région| `bookmakers_by_region`       | 24h   |

Stratégie stale-while-revalidate : l'app affiche le cache instantanément, rafraîchit en arrière-plan.

## Jobs schedulés

| Job                             | Fréquence        | Rôle                                     |
|---------------------------------|------------------|------------------------------------------|
| `FetchMatchesJob`               | Toutes les heures| Synchronise les matchs depuis l'API      |
| `UpdateLiveScoresJob`           | Toutes les 2 min | Met à jour les scores en direct          |
| `UpdatePredictionResultsJob`    | Toutes les 5 min | Marque les prédictions won/lost          |
| `GenerateAllPredictionsJob`     | 08h00 et 20h00   | Génère les prédictions du jour           |
| `DetectOddsAnomalyJob`          | Toutes les 15 min| Scanne les anomalies de cotes            |
| `RunSureBetAnalysisJob`         | 09h00 UTC        | Classifie les coups sûrs 95/99%          |
| `SendBestValueBetNotificationJob`| 08h00 UTC       | Notifie le meilleur pari EV+ du jour     |
| `SendDailyNotificationJob`      | 09h00            | Notifie les utilisateurs                 |
| `SendPremiumExpiryReminderJob`  | 10h00            | Rappels expiration Premium (J-7/3/1)     |

## Dashboard Admin

Accessible sur `/admin` (compte super_admin requis).

Pages disponibles :
- **Dashboard** — KPIs, graphiques temps réel
- **Pronostics** — CRUD, statuts, publication
- **Utilisateurs** — Gestion premium, export
- **Abonnements** — Historique paiements, accord manuel
- **Affiliations** — Liens bookmakers
- **Bookmakers** — Gestion partenaires
- **Compétitions** — Tendances, activation/désactivation
- **Parrainages** — Top parrains, paliers récompenses
- **Feedbacks** — Réponses admin, statuts
- **Statistiques** — ROI, taux réussite, graphiques 30j/12m
- **Paramètres** — Clés API paiement, configuration app

## Tests

```bash
cd backend
php artisan test
```

## Fonctionnalités complètes

- [x] Algorithme prédiction v3.0 (9 critères)
- [x] Auth OTP (SMS/email) + Facebook OAuth
- [x] Endpoints API : today, coupon, history, plans, purchase, verify
- [x] Paiement Paydunya — clés configurées depuis dashboard admin
- [x] Système de parrainage complet
- [x] Notifications push FCM v1 + fallback SMS
- [x] Dashboard admin — 11 pages complètes
- [x] Value Betting + Kelly Criterion (EV+, mise conseillée)
- [x] Détection anomalies de cotes bookmakers (alerte temps réel)
- [x] Analyse Coup Sûr 95%/99% (pipeline 5 critères)
- [x] Carousel bookmakers avec liens affiliés → page détail
- [x] Cache local 24h (stale-while-revalidate)
- [x] Highlight équipe prédite sur PredictionCard (1X2, Double Chance, BTTS)
- [x] Masquage cotes estimées ("Cote indispo." si pas de cote réelle)
- [x] Fix crash 401 — providers protégés par vérification auth
- [x] Flutter analyze — 0 erreur / 0 warning

## Auteur

**massakambp12** — [COTA-OFFICIEL](https://github.com/MHDKIEMDE/COTA-OFFICIEL)
