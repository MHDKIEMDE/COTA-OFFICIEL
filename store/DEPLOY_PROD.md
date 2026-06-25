# COTA — Checklist déploiement production

## 1. Backend Laravel — Serveur VPS

### Variables .env production obligatoires
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.cota.app

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cota_prod
DB_USERNAME=cota_user
DB_PASSWORD=MOT_DE_PASSE_FORT

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Firebase FCM
FIREBASE_PROJECT_ID=cota-xxxxx
FIREBASE_CREDENTIALS_PATH=/var/www/cota/storage/firebase-service-account.json

# APIs Sport
FOOTBALL_API_KEY=xxx
SPORTRADAR_API_KEY=xxx
OPENWEATHERMAP_KEY=xxx

# Paydunya (clés configurées depuis dashboard admin)
PAYDUNYA_MODE=live

# SMS (OTP)
TERMII_API_KEY=xxx
TERMII_SENDER_ID=COTA
```

### Commandes de déploiement
```bash
# 1. Pull du code
git pull origin main

# 2. Dépendances
composer install --no-dev --optimize-autoloader

# 3. Migrations (11 migrations en attente)
php artisan migrate --force

# 4. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Redémarrer queue worker (Supervisor)
sudo supervisorctl restart cota-worker:*
```

### Supervisor — queue worker (/etc/supervisor/conf.d/cota-worker.conf)
```ini
[program:cota-worker]
command=php /var/www/cota/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
directory=/var/www/cota/backend
user=www-data
numprocs=2
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/log/cota/worker.log
```

### Crontab — scheduler Laravel
```cron
* * * * * www-data cd /var/www/cota/backend && php artisan schedule:run >> /dev/null 2>&1
```

### Migrations en attente (à exécuter en prod)
```
2026_05_16_120000 — bet_type string dans predictions
2026_05_16_140000 — index de performance
2026_05_16_160000 — table notifications
2026_05_16_170000 — table bookmaker_blogs
2026_05_16_175245 — match_id string dans matches
2026_05_16_175257 — match_id string dans predictions
2026_05_16_175340 — team/competition ids nullable dans matches
2026_05_16_175556 — table api_source_logs
2026_05_18_100000 — table api_calls
2026_05_18_100001 — table api_quota_usage
2026_05_18_200000 — table notification_preferences
```

---

## 2. Android — Play Store

### Pré-requis
```bash
# Générer le keystore (une seule fois — conserver précieusement)
keytool -genkey -v \
  -keystore android/keystore/cota-release.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias cota

# Créer android/keystore.properties (ne PAS commiter)
storeFile=../keystore/cota-release.jks
storePassword=MOT_DE_PASSE_STORE
keyAlias=cota
keyPassword=MOT_DE_PASSE_CLE
```

### Build App Bundle (Play Store)
```bash
cd mobile
flutter pub get
flutter build appbundle --release
# Sortie : build/app/outputs/bundle/release/app-release.aab
```

### Checklist Play Store Console
- [ ] Icône 512×512 PNG (fond uni, pas de transparence)
- [ ] Feature graphic 1024×500 PNG
- [ ] Captures d'écran phone : min 2, max 8 (ratio 9:16 ou 9:20)
  - Home avec coupon du jour
  - Liste prédictions avec étoiles
  - Détail prédiction + 9 critères
  - Écran Premium / abonnement
  - Empty state avec stats win rate
- [ ] Titre, description courte (80 chars), description longue → voir store/play-store-listing.md
- [ ] Politique de confidentialité (URL publique obligatoire)
- [ ] Classification : 17+ (paris sportifs)
- [ ] versionCode = 2, versionName = "1.0.0"

---

## 3. iOS — App Store

### Pré-requis
- Compte Apple Developer actif ($99/an)
- Xcode ≥ 15 sur Mac
- Certificat Distribution + Provisioning Profile configurés dans Xcode

### Build IPA
```bash
cd mobile
flutter pub get
flutter build ipa --release
# Ou depuis Xcode : Product → Archive → Distribute App
```

### Checklist App Store Connect
- [ ] Bundle ID : com.cotafoot.app
- [ ] Captures d'écran iPhone 6.7" et 6.5" (obligatoires)
- [ ] Icône 1024×1024 PNG (sans transparence, sans coins arrondis)
- [ ] Description, mots-clés → voir store/play-store-listing.md
- [ ] Politique de confidentialité (URL publique)
- [ ] Classification 17+ (jeux de hasard simulés)
- [ ] TestFlight : envoyer aux testeurs internes avant soumission
- [ ] Info.plist — NSUserNotificationUsageDescription ✅ (déjà ajouté)
- [ ] UIBackgroundModes : fetch + remote-notification ✅ (déjà ajouté)

---

## 4. Test golden path avant soumission

```
1. Installer l'APK release sur device physique Android
2. Lancer l'app → voir splash + onboarding
3. Demande permission notifications → accepter
4. S'inscrire via OTP téléphone
5. Voir les prédictions du jour (ou empty state si pas de prédictions)
6. Ouvrir le coupon IA
7. Voir le détail d'une prédiction (9 critères)
8. Souscrire au Premium (Wave test)
9. Vérifier réception notification push
10. Tap sur notification → deep link vers l'écran correct
11. Vérifier historique + statistiques
12. Tester le parrainage (code + lien)
```
