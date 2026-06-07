# Guide de deploiement COTA

Ce guide decrit l'architecture actuelle du dossier `COTA` : backend Laravel 12 + application Flutter. Les anciennes instructions Vercel/Railway/Supabase/Expo concernaient le monorepo archive et ne sont plus la source de verite.

## 1. Backend Laravel

### Prerequis serveur

- PHP 8.2+ avec les extensions Laravel usuelles.
- Composer.
- Redis.
- Nginx avec HTTPS.
- Base SQLite ou MySQL selon l'environnement cible.
- Acces au dossier de deploiement, par exemple `/var/www/cota-backend`.

### Installation

```bash
cd /var/www/cota-backend
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Variables importantes

Configurer au minimum :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.cota.app
APP_FRONTEND_URL=https://www.cota.app

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1

FOOTBALL_API_KEY=...
OPENWEATHERMAP_KEY=...

FIREBASE_PROJECT_ID=...
FIREBASE_CREDENTIALS_PATH=/var/www/html/storage/app/firebase-service-account.json

PAYDUNYA_MODE=live
PAYDUNYA_MASTER_KEY=...
PAYDUNYA_PRIVATE_KEY=...
PAYDUNYA_TOKEN=...
PAYDUNYA_WEBHOOK_URL=https://api.cota.app/api/webhooks/payment
```

Les cles de paiement peuvent aussi etre gerees depuis le dashboard admin via `payment.active_provider` et `payment.providers`.

## 2. HTTPS et Nginx

Le dossier `backend/docker/` contient deux fichiers utiles :

- `nginx.conf` pour le conteneur interne expose sur le port `8082`.
- `nginx-vhost.conf` pour le reverse proxy HTTPS public avec Let's Encrypt.

Points a verifier en production :

- `server_name` correspond au domaine reel.
- Le certificat Let's Encrypt existe dans `/etc/letsencrypt/live/<domaine>/`.
- Le port public `443` redirige vers le backend.
- `APP_URL` utilise le meme domaine HTTPS.
- CORS est limite aux domaines COTA avant ouverture publique.

## 3. Queue worker

Deux modes sont possibles.

### Docker Compose

`backend/docker/docker-compose.yml` definit deja un service `queue` :

```bash
cd backend/docker
docker compose up -d queue
```

### Serveur classique

Utiliser Horizon si Redis est active :

```bash
php artisan horizon
```

ou un worker Laravel simple :

```bash
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
```

En production, lancer ce processus via systemd ou Supervisor, avec redemarrage automatique.

## 4. Scheduler

Les taches planifiees sont dans `backend/routes/console.php`. Verifier avec :

```bash
php artisan schedule:list
```

### Docker Compose

`backend/docker/docker-compose.yml` definit deja un service `scheduler` qui lance `schedule:run` toutes les 60 secondes.

```bash
cd backend/docker
docker compose up -d scheduler
```

### Serveur classique

Ajouter la crontab Laravel :

```cron
* * * * * cd /var/www/cota-backend && php artisan schedule:run >> /dev/null 2>&1
```

## 5. Firebase Cloud Messaging

### Backend

Le backend utilise FCM HTTP v1. Le fichier service account JSON doit exister sur le serveur et le chemin doit correspondre a `FIREBASE_CREDENTIALS_PATH`.

```bash
test -f "$FIREBASE_CREDENTIALS_PATH"
```

### Mobile Flutter

Le projet initialise Firebase via `mobile/lib/firebase_options.dart`. Verifier aussi :

- Le package name Android : `com.cotafoot.app`.
- Le bundle id iOS correspond a celui configure dans Firebase.
- Les notifications push/APNs sont activees cote Apple avant publication iOS.

## 6. Mobile Flutter

Installation :

```bash
cd mobile
flutter pub get
flutter analyze
```

Build Android :

```bash
flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api
```

Build iOS :

```bash
flutter build ipa --release --dart-define=APP_BASE_URL=https://api.cota.app/api
```

En developpement, `NetworkConfigService` peut decouvrir automatiquement le backend local. En production, toujours passer `APP_BASE_URL` au build.

## 7. Verification finale

Avant publication :

```bash
cd backend
php artisan test
php artisan schedule:list
php artisan config:clear && php artisan config:cache

cd ../mobile
flutter analyze
```

Tester ensuite :

- `GET https://api.cota.app/api/health`
- Auth OTP.
- Achat abonnement Paydunya en mode test puis live.
- Enregistrement token FCM depuis un vrai appareil.
- Reception notification push.
- Execution d'un job queue.
- Execution scheduler apres une minute.
