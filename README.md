# COTA — Application de Pronostics Football IA

Application mobile de prédictions football automatisées avec algorithme IA à 9 critères.

## Stack technique

| Couche    | Technologie                                         |
|-----------|-----------------------------------------------------|
| Backend   | Laravel 12, PHP 8.3, MySQL/SQLite, Redis, Sanctum   |
| Mobile    | Flutter 3.x, Dart, Riverpod, Dio, GoRouter          |
| API Sport | API-Football, Sportradar                            |
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
│   │       └── PredictionAlgorithmService.php
│   ├── database/
│   └── resources/views/admin/   ← Dashboard Blade + Tailwind CDN
├── mobile/     ← Application Flutter
│   └── lib/
│       ├── core/
│       │   ├── api/             ← ApiClient (Dio)
│       │   ├── routing/         ← GoRouter
│       │   └── services/
│       └── features/
│           ├── auth/
│           ├── predictions/
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

# Serveur de développement
php artisan serve

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

# Analyser le code
flutter analyze

# Lancer l'application
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

## Jobs schedulés

| Job                          | Fréquence          | Rôle                                  |
|------------------------------|--------------------|---------------------------------------|
| `FetchMatchesJob`            | Toutes les heures  | Synchronise les matchs depuis l'API   |
| `UpdateLiveScoresJob`        | Toutes les 2 min   | Met à jour les scores en direct       |
| `UpdatePredictionResultsJob` | Toutes les 5 min   | Marque les prédictions won/lost       |
| `GenerateAllPredictionsJob`  | 08h00 et 20h00     | Génère les prédictions du jour        |
| `SendDailyNotificationJob`   | 09h00              | Notifie les utilisateurs              |
| `SendPremiumExpiryReminderJob` | 10h00            | Rappels expiration Premium (J-7/3/1)  |

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
# 28 tests — 59 assertions
```

## Fonctionnalités MVP

- [x] Algorithme prédiction v3.0 (9 critères)
- [x] Auth OTP (SMS/email) + Facebook OAuth
- [x] Endpoints API : today, coupon, history, plans, purchase, verify
- [x] Paiement Paydunya — clés configurées depuis dashboard admin
- [x] Système de parrainage complet
- [x] Notifications push FCM v1 + fallback SMS
- [x] Dashboard admin — 10 pages complètes
- [x] Flutter analyze — 0 erreur / 0 warning
- [x] Tests — 28/28 passent

## Auteur

**massakambp12** — [COTA-OFFICIEL](https://github.com/MHDKIEMDE/COTA-OFFICIEL)
