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

## SPRINT 1 — Corrections rapides ✅ TERMINÉ

| # | Tâche | Fichier | État |
|---|---|---|---|
| 1.2 | Vitesse carrousel ÷2 : `* 2000` → `* 4000` | `home_bookmaker_carousel.dart:117` | ✅ |
| 2.3 | Checkbox coupon invité → modal "Créer un compte" (plus de redirection login) | `home_screen.dart` | ✅ |
| 2.4 | `AccessControl` : invité voit les prédictions gratuites (★★ et ★) sans compte | `access_control.dart` | ✅ |
| 3.2 | Filtrer matchs déjà commencés côté backend (`match_time > now`) | `PredictionController@today` | ✅ |
| 5.2 | `minPicks` adaptatif : `max(2, min(4, count))` → toujours un coupon si ≥1 pick | `PredictionController@coupon` | ✅ |

---

## SPRINT 2 — Moteur prédictions 🔴 PRIORITÉ HAUTE

| # | Tâche | Fichier / Endpoint | État |
|---|---|---|---|
| 3.3 | **Prédictions tierces moteur principal** : abaisser seuil à 40 si `third_party.prediction != null`, sinon garder 52 | `PredictionController@today:77` | 🔧 |
| 5.4 | **Croisement COTA × tierces** : exclure du coupon les picks avec `third_party.agreement == 'contradicts'` | `PredictionController@coupon` | ❌ |
| 10.2 | **Cotes réelles 1xBet** : remplacer cotes algo par cotes API dans `GenerateAllPredictionsJob` (fallback algo si absent) | `GenerateAllPredictionsJob` | ❌ |

---

## SPRINT 3 — Fonctionnalités clés 🟡

| # | Tâche | Fichier | État |
|---|---|---|---|
| 4.2 | **Filtre live "Tous / Mes prédictions"** : reproduire pattern filtre home (voir `competitions_carousel`) | `live_screen.dart` | ❌ |
| 5.3 | **3 variantes coupon** : Prudent (cote 2–4), Équilibré (4–10), Audacieux (>10) | `PredictionController@coupon` | ❌ |
| 7.3 | **Section "Mes coupons"** sur profil : créer coupon perso + suivre résultat | `profile_screen.dart` + `UserCouponController` | ❌ |
| 9.3 | **Onglet "Stream"** dans détail match live : bouton "Regarder" via `url_launcher` | `live_match_detail_screen.dart` | ❌ |
| 9.4 | **Section Highlights** dans détail prédiction (matchs terminés) : thumbnails + bouton play | `prediction_detail_screen.dart` | ❌ |

---

## SPRINT 4 — Admin + bookmakers 🟡

| # | Tâche | Fichier | État |
|---|---|---|---|
| 8.2 | **Page consommation APIs** : quota API-Football restant, nb appels/endpoint aujourd'hui | `Admin\ApiMonitorController` | ❌ |
| 6.3 | **Auto-découverte bookmakers** : job qui scrape les bookmakers dispo et les propose à admin | `Console\Commands\DiscoverBookmakers` | ❌ |
| 6.4 | **Notification admin** à chaque nouveau bookmaker détecté | `Admin notification + form` | ❌ |
| 2.5 | Modal "Se connecter / S'inscrire" sur clic prédiction premium (invité) | `home_screen.dart` | ❌ |
| 2.6 | `profile_screen.dart` : remplacer `context.go(login)` par modal si non connecté | `profile_screen.dart:819` | 🔧 |

---

## BACKLOG — Nice-to-have 🟢

| # | Tâche | Fichier | État |
|---|---|---|---|
| 3.4 | Badge "Confirmé" / "Contredit" sur la card (via `third_party.agreement`) | `home_screen.dart` card widget | ❌ |
| 3.6 | Probabilités tierces (home_win_pct / draw_pct / away_win_pct) dans détail prédiction | `prediction_detail_screen.dart` | ❌ |
| 4.4 | Classement buteurs dans détail match live | `live_match_detail_screen.dart` | 🔧 |
| 4.5 | Classement compétition dans détail match (`GET /standings/{competition}`) | `live_match_detail_screen.dart` | ❌ |
| 4.7 | Design cards live style DAZN : score centré, minute rouge pulsant, logos équipes | `live_screen.dart` | 🔧 |
| 5.5 | Historique coupons du jour (Premium) | `coupon_screen.dart` | ❌ |
| 5.7 | Badge "Confirmé par IA tierce" sur les picks coupon | `coupon_screen.dart` | ❌ |
| 6.5 | Articles blog bookmaker auto via Claude Haiku | `BookmakerBlogController` + cron | 🔧 |
| 6.6 | Cotes live 1xBet sur page détail bookmaker | `bookmaker_detail_screen.dart` | ❌ |
| 7.4 | Condenseur de liens sur profil : Historique, Stats, Parrainage, Affiliés, Notifs | `profile_screen.dart` | ❌ |
| 8.3 | Utilisateurs actifs temps réel dans admin | `Admin\StatsController` | 🔧 |
| 8.4 | Graphique consommation quota par API sur 7 jours | Dashboard admin | ❌ |
| 8.5 | Toggle activer/désactiver une API source depuis admin | `AppConfig` en DB | ❌ |
| 9.5 | Feed highlights catégorisé (Top matchs, Par compétition) | `HighlightsFeedWidget` | ❌ |
| 10.3 | Badge source de cote sur la card ("1xBet" vs "estimé") | `home_screen.dart` card widget | ❌ |

---

## CE QUI EST EN PLACE (base solide)

| Fonctionnalité | Fichier | Note |
|---|---|---|
| Algorithme prédiction v3.1 (10 critères) | `PredictionAlgorithmService` | +7 pts tierce partie |
| `RapidApiService` centralisé | `RapidApiService.php` | 6 endpoints agrégés |
| Prédictions tierces intégrées (`third_party` dans API) | `PredictionController@formatPrediction` | Champ `agreement` exposé |
| `MediaController` (highlights + streams) | `MediaController.php` | 4 endpoints prêts |
| Logos bookmakers réels (Google Favicon API) | DB `bookmakers.logo_url` | 12 bookmakers |
| Bookmakers par région IP | `BookmakerController` | Détection automatique |
| Authentification OTP + Facebook | `AuthController` | Sanctum tokens |
| Abonnement Paydunya | `PaymentGatewayService` | Wave, OM, MTN, Moov |
| Dashboard admin complet | `resources/views/admin/` | 9 pages |
| Système parrainage | `ReferralController` | Paliers + récompenses |
