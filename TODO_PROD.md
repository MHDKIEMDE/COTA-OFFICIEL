# COTA — Checklist Mise en Production

> Dernière mise à jour : 2026-05-12

---

## 🔴 BLOQUANT — À finir avant prod

### Backend (Laravel)
- [ ] **Paiement Paydunya** — implémenter Wave / Orange Money / MTN / Moov (`SubscriptionController`, webhooks Paydunya)
- [ ] **`POST /affiliate/claim`** — endpoint activation Premium après inscription bookmaker
- [ ] **`GET /notifications/settings`** + **`PUT /notifications/settings`** — endpoints paramètres notifications
- [ ] **Migrations production** — lancer `php artisan migrate` sur le vrai serveur
- [ ] **Base URL API** — changer `localhost:8000` → URL prod dans `mobile/lib/core/api/api_client.dart`

### Mobile (Flutter)
- [ ] **`history_screen.dart`** — remplacer données simulées par `historyPredictionsProvider` réel
- [ ] **`statistics_screen.dart`** — remplacer données simulées par `statisticsProvider` réel
- [ ] **Liens affiliés bookmakers** — remplacer URLs placeholder par vrais liens avec tracking ID
  - 1xBet : `https://...`
  - BetWinner : `https://...`
  - Melbet : `https://...`
  - LineBet : `https://...`

---

## 🟡 IMPORTANT — Qualité prod

### Serveur / DevOps
- [x] Configurer `.env` production ✅ 2026-05-12
  - `APP_KEY` (générer avec `php artisan key:generate`)
  - `APP_ENV=production` + `APP_DEBUG=false`
  - `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - `REDIS_HOST`, `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`
  - `FOOTBALL_API_KEY` (API-Football)
  - `OPENWEATHERMAP_KEY`
  - `TERMII_API_KEY` + `TERMII_SENDER_ID` (SMS OTP)
  - `PAYDUNYA_*` (clés paiement)
- [ ] Activer **HTTPS / SSL** (Let's Encrypt ou certificat serveur)
- [ ] Limiter CORS → ton domaine dans `config/cors.php`
- [ ] Configurer **queue worker** comme service systemd (pour jobs async)
  ```
  php artisan queue:work --daemon
  ```
- [ ] Configurer **scheduler** comme cron (prédictions auto, scores live)
  ```
  * * * * * php /var/www/cota/artisan schedule:run
  ```
- [ ] Configurer logs (`storage/logs/`) + rotation logrotate

### Mobile
- [ ] Changer base URL dans `mobile/lib/core/api/api_client.dart`
  ```dart
  static const String baseUrl = 'https://TON-DOMAINE.com/api';
  ```
- [ ] Configurer **FCM** (Firebase Cloud Messaging) pour push notifications
  - Ajouter `google-services.json` dans `android/app/`
  - Ajouter `GoogleService-Info.plist` dans `ios/Runner/`
- [ ] Tester sur connexion lente (3G) — états loading/error/empty/success
- [ ] Vérifier que `flutter analyze` passe sans erreur
- [ ] Build release :
  ```bash
  flutter build apk --release          # Android
  flutter build appbundle --release    # Play Store
  flutter build ipa --release          # iOS
  ```

---

## 🟢 OPTIONNEL — Après lancement

### Features incomplètes
- [ ] **Système de parrainage complet** — les écrans existent, backend à terminer
- [ ] **Dashboard admin** — gestion prédictions, utilisateurs, stats
- [ ] **Match detail** — données réelles API (événements, compositions) remplacent le mock
- [ ] **Team / Player / Competition** — connecter aux endpoints API-Football réels

### Qualité & Monitoring
- [ ] Intégrer **Sentry** ou **Firebase Crashlytics** — suivi erreurs prod
- [ ] Intégrer **Firebase Analytics** ou **Mixpanel** — comportement utilisateurs
- [ ] Mettre en place **tests Pest** (Laravel) pour les endpoints critiques
- [ ] Rate limiting API (`throttle`) sur les routes publiques

### Publication stores
- [ ] **Google Play Store**
  - Compte développeur (25 USD one-time)
  - Screenshots, description FR, icône 512×512
  - Privacy Policy URL obligatoire
  - `flutter build appbundle --release`
- [ ] **Apple App Store**
  - Compte Apple Developer (99 USD/an)
  - Certificats signing (Xcode)
  - Review Apple (délai 1–3 jours)
  - `flutter build ipa --release`

---

## 🔵 FUTURES FEATURES (post-lancement)

### Carte des agents de recharge _(v2 — hors MVP)_
> Agents bookmakers (1xBet, BetWinner, Melbet, LineBet) — auto-inscription via l'app — statut on/off manuel — gratuit pour tous.
> **À ne pas implémenter avant le lancement.**

- [ ] Carte interactive géolocalisée agents bookmakers (dépôt / retrait)
- [ ] Auto-inscription agent + validation admin
- [ ] Statut disponible/indisponible en temps réel (bouton on/off)
- [ ] Backend : modèle `Agent`, `POST /agents/register`, `GET /agents/nearby`, `PUT /agents/status`

### Expérience matchs & pronostics
- [ ] **Vue match enrichie** — pour chaque match affiché :
  - Forme récente des deux équipes (5 derniers matchs)
  - Confrontations directes (head-to-head)
  - Buteurs / joueurs clés à surveiller
  - Météo au stade (déjà intégré via OpenWeatherMap)
  - Cote en direct bookmakers (si disponible via API)
- [ ] **Filtres avancés** sur la liste des matchs
  - Par ligue / pays
  - Par niveau de confiance (1–4 étoiles)
  - Par heure de coup d'envoi
- [ ] **Notifications personnalisées** — alerter l'utilisateur avant ses matchs favoris

### Psychologie & rétention utilisateur
> Cible : deux profils (parieurs perso + suiveurs COTA). Style : mix gamification légère + stats sérieuses.
> Premium justifié par : **picks haute confiance (4 étoiles) exclusifs Premium**.

- [ ] **Streak quotidien** — récompenser les connexions consécutives (badge, indicateur visuel)
- [ ] **Score de performance** — suivi des pronostics perso vs algorithme COTA (taux de réussite, ROI estimé)
- [ ] **Push notifications contextuelles** — "Ton coupon du jour est disponible", "Match dans 1h"
- [ ] **Classement / leaderboard** — comparaison anonyme entre utilisateurs (taux de réussite)
- [ ] **Résumé hebdomadaire** — recap performances de la semaine envoyé le lundi matin
- [ ] **Picks 4 étoiles verrouillés** — visibles en aperçu pour les gratuits, accessibles Premium uniquement

---

## ✅ DÉJÀ FAIT (MVP)

- [x] Algorithme de prédiction v3.0 (9 critères)
- [x] Authentification OTP (SMS/email) + Facebook OAuth
- [x] Endpoints prédictions : today, coupon, history
- [x] Matchs populaires filtrés par tier de ligue
- [x] Navigation COTA (Accueil / Pronostics / Live / Historique / Profil)
- [x] Pull-to-refresh sur tous les écrans
- [x] Page promotion bookmakers (affiliate)
- [x] Écrans détail : match (5 onglets), équipe, joueur, compétition
- [x] Palette COTA noir/jaune complète
- [x] Paramètres notifications (frontend + backend)
- [x] Préférences utilisateur (questions onboarding → API)
- [x] Système abonnement Premium (écrans)
- [x] Page FAQ, Parrainage, Confidentialité

---

## PRIORITÉ IMMÉDIATE (top 3)

1. 🔴 Implémenter **Paydunya** (sans paiement = pas de revenus)
2. 🔴 Changer **URL API** `localhost` → prod
3. 🔴 Configurer **`.env` production** sur le serveur
