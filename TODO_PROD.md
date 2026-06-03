# COTA - Checklist Preproduction

> Derniere mise a jour : 2026-05-25
> Source de verite actuelle : backend Laravel 12 + mobile Flutter. Les anciennes references Supabase/Vercel/Expo sont archivees.

## Etat verifie le 2026-05-25

| Sujet | Etat | Note |
|---|---:|---|
| Tests backend | OK | `php artisan test --stop-on-failure` : 27 tests passes, 1 skip |
| Analyse Flutter | OK | `flutter analyze` : aucune issue |
| Scheduler Laravel | OK code | `php artisan schedule:list` liste les jobs attendus |
| Queue worker | OK infra | Docker compose contient un service `queue`; `start-services.sh` lance Horizon en local |
| HTTPS | Partiel | `backend/docker/nginx-vhost.conf` est pret pour Let's Encrypt, mais le certificat/domaine doivent etre verifies sur serveur |
| FCM backend | A corriger | `FIREBASE_PROJECT_ID` est renseigne en local, mais le fichier `FIREBASE_CREDENTIALS_PATH` est absent sur cette machine |
| FCM mobile | Partiel | `firebase_options.dart` existe; aucun `google-services.json` ni `GoogleService-Info.plist` n'est present dans le repo |
| `.env` local | Dev uniquement | `APP_ENV=local`, `APP_URL=http://localhost`, queue/cache en local |
| `.env.production.example` | Corrige | `SESSION_DRIVER=redis`, placeholder API-Football, vars Firebase ajoutees |

## Bloquant avant production

- [ ] Copier le service account Firebase sur le serveur et faire pointer `FIREBASE_CREDENTIALS_PATH` vers ce fichier.
- [ ] Verifier l'enregistrement FCM sur un vrai appareil connecte a l'API production.
- [ ] Generer/installer le certificat HTTPS Let's Encrypt du domaine public.
- [ ] Mettre `APP_URL` et `APP_FRONTEND_URL` sur les domaines finaux.
- [ ] Limiter `config/cors.php` aux domaines COTA avant ouverture publique.
- [ ] Lancer les migrations prod : `php artisan migrate --force`.
- [ ] Lancer un worker durable : Horizon, Supervisor/systemd, ou le service Docker `queue`.
- [ ] Lancer le scheduler durable : crontab Laravel ou le service Docker `scheduler`.
- [ ] Builder Flutter avec l'URL API prod :
  ```bash
  flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api
  ```
- [ ] Tester Paydunya bout en bout : creation facture, redirection, webhook `/api/webhooks/payment`, activation Premium.
- [ ] Remplacer les liens d'affiliation placeholder par les vrais liens trackes.

## Important

- [ ] Confirmer le domaine final : `api.cota.app`, `cota.monghetto.com`, ou autre. La doc et les env examples doivent rester alignes sur ce choix.
- [ ] Configurer logs et rotation `storage/logs`.
- [x] Sentry Laravel — intégré (`bootstrap/app.php`, `config/sentry.php`, `LOG_STACK=daily`).
- [x] Crashlytics Flutter — intégré (`pubspec.yaml` + `main.dart`).
- [x] App Links Android (`AndroidManifest.xml` autoVerify) + iOS (`Info.plist` associated-domains) — en place.
- [ ] Sentry : renseigner `SENTRY_LARAVEL_DSN` réel sur le serveur prod.
- [ ] Crashlytics : copier `google-services.json` (Android) et `GoogleService-Info.plist` (iOS) dans l'app avant le build release.
- [ ] App Links : remplacer `REMPLACER_PAR_SHA256_KEYSTORE_RELEASE` dans `public/.well-known/assetlinks.json` (SHA-256 du keystore release). Remplacer `REMPLACER_PAR_TEAM_ID` dans `apple-app-site-association`.
- [ ] Tester l'app en 3G/lenteur reseau.
- [ ] Tester les notifications programmees locales a 8h et 13h.

## Deja en place

- [x] Backend Laravel 12.
- [x] API mobile REST.
- [x] Auth OTP/PIN/Facebook.
- [x] Endpoints `GET/PUT /api/notifications/settings`.
- [x] Notifications in-app et FCM HTTP v1 cote backend.
- [x] Jobs de predictions, live, resultats, routines, quota API et cache.
- [x] Dashboard admin Blade.
- [x] Paiement via `PaymentGatewayService` avec driver Paydunya.
- [x] Webhooks paiement unifies : `/api/webhooks/payment` et alias `/api/webhooks/paydunya`.
- [x] App mobile Flutter avec Riverpod, Dio, GoRouter, Firebase.
- [x] Decouverte backend locale via `NetworkConfigService`.

## Commandes de controle

Backend :

```bash
cd backend
php artisan test --stop-on-failure
php artisan schedule:list
php artisan queue:failed
```

Mobile :

```bash
cd mobile
flutter analyze
flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api
```
