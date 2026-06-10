# COTA — TODO unique (préproduction & lancement)

> Fichier unique consolidant : ancien `TODO.md`, `TODO_PROD.md`, et les marqueurs `// TODO` du code.
> Dernière mise à jour : 2026-06-10.
> Cible CDC : voir `CDC/COTA_CDC_MASTER.md`.

---

## ✅ Déjà fait (vérifié dans le code)

### Backend & infra
- [x] Backend Laravel 12 + API REST mobile.
- [x] Auth OTP / PIN / Facebook + Google OAuth (`POST /auth/google`, `POST /auth/facebook`, migration `google_id`).
- [x] Auth international : numéro + PIN/mot de passe, toggle numéro/email, sélecteur 17 pays, email + username.
- [x] Jobs : prédictions, live, résultats, routines, quota API, cache.
- [x] Dashboard admin Blade.
- [x] Paiement `PaymentGatewayService` + driver Paydunya.
- [x] Webhooks paiement unifiés : `/api/webhooks/payment` + alias `/api/webhooks/paydunya`.
- [x] Endpoints `GET/PUT /api/notifications/settings` ; notifications in-app + FCM HTTP v1.
- [x] Tests backend : `php artisan test` 27 passés / 1 skip ; `flutter analyze` 0 issue.
- [x] Scheduler (`schedule:list`) + queue worker (Docker `queue` / Horizon local).
- [x] Sentry Laravel (`bootstrap/app.php`, `config/sentry.php`, `LOG_STACK=daily`).
- [x] Crashlytics Flutter (`pubspec.yaml` + `main.dart`).
- [x] App Links Android (`AndroidManifest.xml` autoVerify) + iOS (`Info.plist` associated-domains).
- [x] `.env.production.example` corrigé (`SESSION_DRIVER=redis`, vars Firebase).

### Mobile
- [x] App Flutter (Riverpod, Dio, GoRouter, Firebase).
- [x] Découverte backend locale via `NetworkConfigService`.
- [x] Corrigé les RenderFlex overflows 52 px / 92 px (`live_screen.dart`).

### Crédibilité
- [x] **C-01** Matching cotes 1xBet (`OddsApiService::find()` : UTF-8, `similar_text` pondéré, seuil 65 %).
- [x] **H-03** ROI personnel (`GET /api/user/roi` + `personalRoiProvider` + `_buildPersonalRoi()`).
- [x] **A-01** Page « Comment ça marche » (`how_it_works_screen.dart` + lien profil).

---

## 🔴 Bloquant avant production

- [ ] **Verrous Premium** — réactiver les bypass dev (cf. §Marqueurs code ci-dessous), en priorité `_bypassPremium = false`.
- [ ] Service account **Firebase** sur le serveur → `FIREBASE_CREDENTIALS_PATH` pointant vers le fichier.
- [ ] Vérifier l'enregistrement **FCM** sur un vrai appareil connecté à l'API prod.
- [ ] Certificat **HTTPS** Let's Encrypt du domaine public.
- [ ] `APP_URL` + `APP_FRONTEND_URL` sur les domaines finaux.
- [ ] Limiter `config/cors.php` aux domaines COTA.
- [ ] Migrations prod : `php artisan migrate --force` (inclut colonne `google_id`).
- [ ] Worker durable (Horizon / Supervisor / systemd / Docker `queue`).
- [ ] Scheduler durable : crontab `* * * * * php artisan schedule:run` (ou Docker `scheduler`).
- [ ] Build Flutter prod : `flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api`.
- [ ] Tester **Paydunya** bout en bout : facture → redirection → webhook → activation Premium.
- [ ] Remplacer les **liens d'affiliation placeholder** par les vrais liens trackés.

---

## 🟠 Clés & configuration à renseigner

- [ ] Clés **Paydunya** dans `.env` (`PAYDUNYA_MASTER_KEY`, `PAYDUNYA_PRIVATE_KEY`, `PAYDUNYA_TOKEN`).
- [ ] Vars **Firebase** (`FIREBASE_PROJECT_ID`, `FIREBASE_CREDENTIALS_PATH`).
- [ ] `GOOGLE_CLIENT_ID_ANDROID` / `GOOGLE_CLIENT_ID_IOS` dans `.env` backend.
- [ ] `google-services.json` (Android) + `GoogleService-Info.plist` (iOS) dans l'app (requis `google_sign_in` + Crashlytics).
- [ ] App ID **Facebook** dans `AndroidManifest.xml` / `Info.plist` (requis `flutter_facebook_auth`).
- [ ] `SENTRY_LARAVEL_DSN` réel sur le serveur prod.
- [ ] App Links : remplacer `REMPLACER_PAR_SHA256_KEYSTORE_RELEASE` dans `public/.well-known/assetlinks.json` + `REMPLACER_PAR_TEAM_ID` dans `apple-app-site-association`.
- [ ] **Nginx** : `server_name`, cert Let's Encrypt, 443 → backend, CORS limité.
- [ ] Confirmer le domaine final (`api.cota.app`, `cota.monghetto.com`, ou autre) et aligner doc + env examples.
- [ ] Configurer logs et rotation `storage/logs`.

---

## 🟡 Marqueurs `// TODO` dans le code

| Fichier | Ligne | Action |
|---------|------:|--------|
| `mobile/lib/shared/providers/user_access_providers.dart` | 17 | `_bypassPremium = false` avant lancement |
| `mobile/lib/features/predictions/presentation/widgets/prediction_card.dart` | 66 | Réactiver l'overlay premium lock si `isLocked` |
| `backend/app/Livewire/Auth/RegisterForm.php` | 93 | Envoyer l'OTP via SMS / Email |
| `backend/app/Livewire/Auth/VerifyOtpForm.php` | 138 | Envoyer l'OTP via SMS / Email |

---

## 🟢 Tests & validations finales

- [ ] Tester l'app en 3G / réseau lent.
- [ ] Tester les notifications programmées locales à 8h et 13h.
- [ ] Vérifier en prod : `/api/health`, auth OTP, Paydunya test → live, FCM push sur vrai device.

### Design System — validations visuelles sur device (`flutter run -d 21`)
- [ ] **A5** Favicon web (`web/favicon.svg`, `web/favicon-animated.svg`).
- [ ] **A6** Icône sur écran d'accueil du téléphone.
- [ ] **B1** Splash/Loader — ring 200×200 + wordmark + 9 critères staggered + transition 3.5 s.
- [ ] **B2** Confidence Reveal — ring + rouleau numérique + lock jaune (`PredictionDetailScreen`).
- [ ] **B3** Coupon Validé — 3 picks staggered + stamp « COUPON VALIDÉ » (`CouponScreen`).
- [ ] **C** Boutons — login, signup, otp, feedback, subscription, referral.
- [ ] **D** Erreur 404 Flutter (route inexistante sur device).
- [ ] **E** Erreurs Laravel — 404/403/500/419 dans le navigateur.
- [ ] **F** Cache — `php artisan cache:warm` + Redis connecté en prod.

---

## Commandes de contrôle

```bash
# Backend
cd backend
php artisan test --stop-on-failure
php artisan schedule:list
php artisan queue:failed

# Mobile
cd mobile
flutter analyze
flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api
```
