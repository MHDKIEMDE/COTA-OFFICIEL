# COTA — TODO (tâches restantes)

## Design System — Vérifications visuelles sur device

> Code en place. Il ne reste que des validations visuelles après `flutter run -d 21`.

- [ ] **A5** Favicon web — mettre à jour `web/favicon.svg` et `web/favicon-animated.svg`
- [ ] **A6** Vérifier icône sur écran d'accueil du téléphone (après `flutter run -d 21`)
- [ ] **B1** Splash/Loader — ring 200×200 + wordmark + 9 critères staggered + transition 3.5 s
- [ ] **B2** Confidence Reveal — ring + rouleau numérique + lock jaune sur `PredictionDetailScreen`
- [ ] **B3** Coupon Validé — 3 picks staggered + stamp "COUPON VALIDÉ" sur `CouponScreen`
- [ ] **C** Boutons — vérifier login, signup, otp, feedback, subscription, referral
- [ ] **D** Erreur 404 Flutter — naviguer vers une route inexistante sur device
- [ ] **E** Erreurs Laravel — vérifier 404/403/500/419 dans le navigateur
- [ ] **F** Cache — vérifier `php artisan cache:warm` + Redis connecté en prod

---

## Déploiement — Actions manuelles

- [ ] Remplir les vraies clés **Paydunya** dans `.env` (`PAYDUNYA_MASTER_KEY`, `PAYDUNYA_PRIVATE_KEY`, `PAYDUNYA_TOKEN`)
- [ ] Remplir clés **Firebase** (`FIREBASE_PROJECT_ID`, `FIREBASE_CREDENTIALS_PATH`)
- [ ] Configurer **Nginx** : `server_name`, cert Let's Encrypt, port 443 → backend, CORS limité
- [ ] Déployer backend : `composer install --no-dev`, `migrate --force`, `config:cache`, `route:cache`
- [ ] Lancer **queue worker** en prod (Supervisor / systemd → `php artisan horizon`)
- [ ] Ajouter **crontab** : `* * * * * php artisan schedule:run`
- [ ] Build APK/AAB release : `flutter build appbundle --release --dart-define=APP_BASE_URL=https://api.cota.app/api`
- [ ] Configurer domaines : `api.cota.app` / `cota.app`
- [ ] Tester en production : `/api/health`, auth OTP, paiement Paydunya mode test → live, FCM push sur vrai device

---

## Auth — Système international ✅ (code livré)

- [x] Numéro + PIN/mot de passe (toggle numéro/email, sélecteur 17 pays, alphanumérique)
- [x] OTP : send → verify → complete_registration
- [x] Email + username (mode toggle)
- [x] Google OAuth — backend `POST /auth/google` + SDK `google_sign_in` branché
- [x] Facebook OAuth — backend `POST /auth/facebook` (access_token) + SDK `flutter_facebook_auth` branché
- [x] Migration `google_id` sur table users

> **À configurer avant prod :**
> - `GOOGLE_CLIENT_ID_ANDROID` et `GOOGLE_CLIENT_ID_IOS` dans `.env` backend
> - `google-services.json` / `GoogleService-Info.plist` dans le projet Flutter (requis pour `google_sign_in`)
> - App ID Facebook dans `AndroidManifest.xml` / `Info.plist` (requis pour `flutter_facebook_auth`)
> - `php artisan migrate` sur le serveur (colonne `google_id`)

---

## RenderFlex overflows (UI bug signalé)

- [x] Corriger les débordements de 52 px et 92 px sur la droite (`live_screen.dart` — `_LiveCard` header + `_MyPredTile` colonne droite)

---

## Crédibilité ✅

- [x] **C-01** Matching cotes 1xBet amélioré — `OddsApiService::find()` : normalisation UTF-8, `similar_text` pondéré, seuil 65%, debug log (`OddsApiService.php`)
- [x] **H-03** ROI personnel — `GET /api/user/roi` + `personalRoiProvider` + widget `_buildPersonalRoi()` dans `profile_screen.dart`
- [x] **A-01** Page "Comment ça marche" — déjà en place (`how_it_works_screen.dart` + lien profil)
