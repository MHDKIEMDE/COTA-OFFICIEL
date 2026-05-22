# COTA — Sprint TODO (état réel après session du 2026-05-22)

---

## LÉGENDE
- ✅ Terminé
- 🔧 Partiellement fait, correction nécessaire
- ❌ À créer de zéro
- 🔴 Bloquant / priorité haute
- 🟡 Important mais non bloquant
- 🟢 Nice-to-have

---

## SPRINT 1 ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 1.2 | Vitesse carrousel ÷2 | `home_bookmaker_carousel.dart` | ✅ |
| 2.3 | Checkbox coupon invité → modal signup | `home_screen.dart` | ✅ |
| 2.4 | Invité voit prédictions gratuites (★★ et ★) | `access_control.dart` | ✅ |
| 3.2 | Filtrer matchs commencés côté backend | `PredictionController@today` | ✅ |
| 5.2 | `minPicks` adaptatif (toujours un coupon) | `PredictionController@coupon` | ✅ |

---

## SPRINT 2 ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 3.3 | Seuil adaptatif 40/52 selon third_party | `PredictionController@today` | ✅ |
| 5.4 | Exclure picks `agreement == 'contradicts'` du coupon | `PredictionController@coupon` | ✅ |
| 10.2 | Cotes 1xBet réelles dans GenerateAllPredictionsJob | `GenerateAllPredictionsJob` | ✅ |

---

## SPRINT 3 ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 4.2 | Filtre live "Tous / Mes prédictions" | `live_screen.dart` | ✅ |
| 5.3 | 3 variantes coupon (Prudent / Équilibré / Audacieux) | `PredictionController@coupon` | ✅ |
| 9.3 | Onglet STREAM dans détail match live | `live_match_detail_screen.dart` | ✅ |
| 9.4 | Onglet HIGHLIGHTS dans détail prédiction | `prediction_detail_screen.dart` | ✅ |

---

## SPRINT 4 ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 8.2 | Page monitoring APIs admin (`/admin/api-monitor`) | `ApiMonitorController` | ✅ |
| 2.5 | Modal signup sur clic prédiction premium (invité) | `home_screen.dart` | ✅ |
| 2.6 | Modal accès réservé sur items profil (invité) | `profile_screen.dart` | ✅ |

---

## SPRINT 5 ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 7.3 | **Section "Mes coupons"** sur profil : créer coupon perso depuis les prédictions du jour, suivre résultat | `my_coupons_screen.dart` + `UserCouponController` + migration `user_coupons` | ✅ |
| 6.3 | **Auto-découverte bookmakers** : commande qui scrape les bookmakers dispo + propose à admin | `Console\Commands\DiscoverBookmakers` | ✅ |
| 6.4 | **Notification admin** à chaque nouveau bookmaker détecté (email Mail::raw) | `DiscoverBookmakers.php` + schedule hebdo | ✅ |
| 8.x | **Lien "Monitoring APIs"** dans la sidebar admin | `layouts/app.blade.php` | ✅ |

---

## BACKLOG — Nice-to-have 🟢

| # | Tâche | Fichier | État |
|---|---|---|---|
| 3.4 | Badge "Confirmé" / "Contredit" sur card home (via `third_party.agreement`) | `home_screen.dart` | ❌ |
| 3.6 | Probabilités tierces dans détail prédiction | `prediction_detail_screen.dart` | ❌ |
| 4.4 | Classement buteurs dans détail match live | `live_match_detail_screen.dart` | 🔧 |
| 4.5 | Classement compétition dans détail match | `live_match_detail_screen.dart` | ❌ |
| 4.7 | Design cards live style DAZN | `live_screen.dart` | 🔧 |
| 5.5 | Historique coupons du jour (Premium) | `coupon_screen.dart` | ❌ |
| 5.7 | Badge "Confirmé par IA tierce" sur picks coupon | `coupon_screen.dart` | ❌ |
| 6.5 | Articles blog bookmaker auto via Claude Haiku | `BookmakerBlogController` | 🔧 |
| 6.6 | Cotes live 1xBet sur page détail bookmaker | `bookmaker_detail_screen.dart` | ❌ |
| 8.3 | Utilisateurs actifs temps réel dans admin | `Admin\StatsController` | 🔧 |
| 8.5 | Toggle activer/désactiver API source depuis admin | `AppConfig` en DB | ❌ |
| 9.5 | Feed highlights catégorisé | `HighlightsFeedWidget` | ❌ |
| 10.3 | Badge source de cote ("1xBet" vs "estimé") sur card | `home_screen.dart` | ❌ |

---

## CE QUI EST EN PLACE (base solide)

| Fonctionnalité | Fichier | Note |
|---|---|---|
| Algorithme prédiction v3.1 (10 critères) | `PredictionAlgorithmService` | +7 pts tierce partie |
| `RapidApiService` centralisé | `RapidApiService.php` | 6 endpoints agrégés |
| Prédictions tierces intégrées + `agreement` | `PredictionController@formatPrediction` | Seuil adaptatif 40/52 |
| Coupon : 3 variantes + filtre contradicts | `PredictionController@coupon` | Prudent/Équilibré/Audacieux |
| Cotes 1xBet réelles | `GenerateAllPredictionsJob` | Fallback algo si absent |
| `MediaController` (highlights + streams) | `MediaController.php` | 4 endpoints |
| Filtre live "Tous / Mes prédictions" | `live_screen.dart` | Chips animés |
| Onglet STREAM + HIGHLIGHTS | `live_match_detail_screen` / `prediction_detail_screen` | url_launcher |
| Page monitoring APIs admin | `ApiMonitorController` | Chart.js 7j, jauges quota |
| Modals invité (premium, coupon, profil) | `home_screen` / `profile_screen` | Aucune redirection forcée |
| Logos bookmakers réels | DB `bookmakers.logo_url` | Google Favicon API |
| Authentification OTP + Facebook | `AuthController` | Sanctum tokens |
| Abonnement Paydunya | `PaymentGatewayService` | Wave, OM, MTN, Moov |
| Dashboard admin complet | `resources/views/admin/` | 10 pages |
| Système parrainage | `ReferralController` | Paliers + récompenses |
