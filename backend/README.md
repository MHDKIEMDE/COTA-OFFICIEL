# COTA - Pronostics Football Automatisés

Backend Laravel 12 pour l'application mobile de pronostics football. Intègre l'API-Football pour récupérer les données de matchs et génère des prédictions via un algorithme à 9 critères pondérés.

## Stack Technique

- **Laravel 12** - Framework PHP
- **MySQL / SQLite** - Base de données
- **Redis (Predis)** - Cache & Queues
- **Laravel Sanctum** - Authentification API
- **API-Football (v3)** - Source de données football
- **OpenWeatherMap** - Données météo (critère météo de l'algorithme)
- **Paydunya** - Paiements Mobile Money (Wave, Orange Money, MTN)

---

## Démarrage Rapide

```bash
# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Lancer le serveur
php artisan serve
```

### Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+ ou SQLite
- Redis

---

## Variables d'Environnement

```env
# App
APP_NAME=COTA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de données
DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_DATABASE=cota_db

# API-Football (api-sports.io)
FOOTBALL_API_KEY=your_api_key_here
FOOTBALL_API_PLAN=free          # free (100 req/jour) ou premium (3000 req/jour)
FOOTBALL_API_TIMEZONE=Africa/Ouagadougou

# OpenWeatherMap (critère météo)
OPENWEATHERMAP_KEY=your_key_here

# Redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Paiements Paydunya
PAYDUNYA_MASTER_KEY=your_key
PAYDUNYA_PRIVATE_KEY=your_key
PAYDUNYA_TOKEN=your_token
PAYDUNYA_MODE=test              # test ou live

# Firebase (notifications push)
FIREBASE_SERVER_KEY=your_key
FIREBASE_CREDENTIALS_PATH=storage/app/firebase.json

# SMS OTP
SMS_PROVIDER=log                # log, termii
TERMII_API_KEY=your_key
```

---

## Architecture

### Structure des Services

```
app/Services/
├── FootballApiService.php          # Client API-Football avec rate limiting + cache
├── PredictionAlgorithmService.php  # Algorithme 9 critères v3.0
├── PaydunyaService.php             # Paiements Mobile Money
├── OddsApiService.php              # Cotes bookmakers
└── Sms/
    ├── SmsProviderInterface.php
    ├── TermiiSmsProvider.php
    └── LogSmsProvider.php
```

### FootballApiService

Gère tous les appels API-Football avec :
- **Rate Limiting** : Plan gratuit (100 req/jour, 30 req/min), Premium (3000 req/jour)
- **Cache intelligent** : TTL adaptés (fixtures: 5min, classements: 24h)
- **Gestion d'erreurs** : Fallbacks et logs complets

Méthodes principales :
```php
getUpcomingMatches(int $daysAhead)   // Matchs à venir
getLiveMatches()                      // Scores en direct
getHeadToHead(int $home, int $away)  // Confrontations directes
getTeamStatistics(int $teamId, int $leagueId, int $season)
getStandings(int $leagueId, int $season)
getUsageStats()                       // Quota utilisé/restant
```

---

## Algorithme de Prédiction v3.0

9 critères pondérés (100 points total) dans `app/Services/PredictionAlgorithmService.php` :

| Critère | Poids | Description |
|---------|-------|-------------|
| Forme récente | 25 | 10 derniers matchs, bonus série |
| Confrontations H2H | 20 | 8 derniers face-à-face avec pondération temporelle |
| Performance dom/ext | 15 | Stats séparées domicile/extérieur |
| Classement | 12 | Position et écart au classement |
| Statistiques buts | 10 | Buts marqués/encaissés, BTTS, clean sheets |
| Horaire match | 8 | Bonus prime time |
| Météo | 5 | Température, précipitations, vent |
| Tirs cadrés | 3 | Précision et efficacité offensive |
| Forme physique | 2 | Fatigue et blessures |

**Niveaux de confiance** :
- 85-100 pts : 4 étoiles — Très haute (Premium)
- 70-84 pts : 3 étoiles — Haute (Premium)
- 60-69 pts : 2 étoiles — Moyenne
- 50-59 pts : 1 étoile — Faible
- < 50 pts : Non publié

**Types de paris générés** : 1X2, BTTS, Plus/Moins 2.5 buts, Handicap, Double Chance

---

## API REST

### Endpoints Publics

```
GET  /api/health                    # Health check
POST /api/auth/send-otp             # Envoi code OTP
POST /api/auth/verify-otp           # Vérification OTP + token
POST /api/auth/facebook             # Login Facebook OAuth
GET  /api/predictions/today         # Pronostics du jour (mode invité)
GET  /api/predictions/{id}          # Détail pronostic
GET  /api/subscriptions/plans       # Plans premium disponibles
```

### Endpoints Protégés (auth:sanctum)

```
GET  /api/auth/me                   # Profil utilisateur
POST /api/auth/logout               # Déconnexion
GET  /api/predictions/history       # Historique pronostics
GET  /api/predictions/statistics    # Statistiques de performance
POST /api/subscriptions/purchase    # Acheter un abonnement premium
GET  /api/referrals/stats           # Stats programme parrainage
```

### Flux d'authentification

1. Utilisateur envoie son numéro/email → `POST /api/auth/send-otp`
2. Backend envoie un OTP par SMS (Termii) ou email
3. Utilisateur vérifie le code → `POST /api/auth/verify-otp`
4. Backend retourne un token Sanctum
5. Token utilisé dans `Authorization: Bearer {token}` pour les routes protégées

---

## Jobs Automatisés

```php
// Synchroniser les matchs depuis API-Football (toutes les heures)
Schedule::job(new FetchMatchesJob())->hourly();

// Générer les prédictions (2x par jour)
Schedule::job(new GenerateAllPredictionsJob())->dailyAt('08:00');
Schedule::job(new GenerateAllPredictionsJob())->dailyAt('20:00');

// Mettre à jour les scores en direct (toutes les 5 minutes)
Schedule::job(new UpdateLiveScoresJob())->everyFiveMinutes()->between('10:00', '23:00');

// Marquer les prédictions gagnées/perdues (toutes les heures)
Schedule::job(new UpdatePredictionResultsJob())->hourly();
```

Activer le scheduler en développement :
```bash


```

Crontab en production :
```
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

---

## Commandes Artisan

```bash
# Tester la connexion API-Football et les quotas
php artisan football:diagnose

# Récupérer les matchs du jour
php artisan football:fetch-today

# Tester l'algorithme de prédiction sur des matchs réels
php artisan prediction:test
php artisan prediction:test --limit=5 --details

# Générer les prédictions pour les prochaines 24h
php artisan prediction:generate

# Gestion du cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
```

---

## Schéma de Base de Données

8 tables principales :

| Table | Description |
|-------|-------------|
| `users` | Auth (OTP/Facebook), statut premium, code parrainage |
| `matches` | Données matchs API-Football (équipes, scores, statut, lieu) |
| `predictions` | Prédictions générées avec score et analyse |
| `subscriptions` | Abonnements premium via Paydunya |
| `affiliations_bonus` | Liens bookmakers (Betwinner, 1xBet) + 7 jours premium offerts |
| `referrals` | Programme de parrainage |
| `feedbacks` | Rapports de bugs et suggestions utilisateurs |
| `statistics` | Métriques quotidiennes de performance des prédictions |

---

## Déploiement Docker

```bash
# Build et démarrage
docker compose up -d --build

# Vérifier les logs
docker compose logs -f app

# Rebuild complet (si changement Composer)
docker compose build --no-cache app && docker compose up -d
```

Le fichier `docker/nginx-vhost.conf` configure le reverse proxy hôte avec :
- Redirection HTTP → HTTPS
- SSL via Let's Encrypt (Certbot)
- Rate limiting : 30 req/min (API), 5 req/min (auth)
- Blocage des fichiers sensibles (`.env`, `.git`, `.sql`)

---

## Tests

```bash
# Tous les tests
php artisan test

# Test spécifique
php artisan test --filter=AuthTest

# Avec couverture
php artisan test --coverage
```

---

## Notes Importantes

- **Ne jamais committer `.env`** — contient les clés API et secrets
- **Quota API-Football** — Plan gratuit : 100 req/jour. Utiliser le cache, éviter les boucles de test
- **Redis requis** — pour les queues et le cache. En développement : `QUEUE_CONNECTION=database`
- **Toutes les dates utilisent Carbon** — timezone configurée dans `config/football-api.php`
- **Jobs nécessitent Redis** — ou `QUEUE_CONNECTION=database` en dev

---

## Licence

Proprietary — © 2026 MHD SERVICE
