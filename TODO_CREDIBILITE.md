# COTA — TODO Crédibilité
> Créé : 2026-06-02
> Objectif : faire de COTA une app crédible avec un vrai track record affiché

## Contexte réel (base de données ce jour)
- 2 167 prédictions sur 30 jours
- 1 146 gagnées / 937 perdues → **55% de réussite réelle**
- 134 matchs aujourd'hui dont 89 sans prédiction (données insuffisantes)
- Ligues affichées : beaucoup de matchs exotiques (Zambie, Mongolie, Cambodge…)

---

## LÉGENDE
- ✅ Terminé
- ❌ À faire
- 🔴 Bloquant crédibilité
- 🟡 Important
- 🟢 Nice-to-have

---

## 1 — FILTRAGE LIGUES (impact immédiat) 🔴

### 1.1 — Backend : ne générer que sur les ligues tier 1–3
| # | Tâche | Fichier | État |
|---|---|---|---|
| F-01 | Dans `GenerateAllPredictionsJob`, filtrer les fixtures API-Football pour ne garder que les ligues `popular_leagues` tier 1–3 avant de lancer l'algo | `GenerateAllPredictionsJob.php` | ✅ 2026-06-02 |
| F-02 | Ajouter les ligues africaines majeures en tier 3 : CAF Champions League, CHAN, ligues Sénégal/Côte d'Ivoire/Nigeria/Ghana (audience cible) | `config/football-api.php` | ✅ 2026-06-02 |
| F-03 | Log du nombre de matchs filtrés vs total pour monitorer l'impact | `GenerateAllPredictionsJob.php` | ✅ 2026-06-02 |

### 1.2 — Backend : endpoint `/predictions/today` — filtrage côté API
| # | Tâche | Fichier | État |
|---|---|---|---|
| F-04 | Ajouter paramètre `?tier_max=3` sur l'endpoint pour que le mobile puisse filtrer si besoin | `PredictionController.php` | ✅ 2026-06-02 |
| F-05 | Tri des prédictions par tier de ligue (tier 1 en premier) dans la réponse API | `PredictionController.php` | ✅ 2026-06-02 |

---

## 2 — TRACK RECORD RÉEL 🔴

### 2.1 — Backend : calculer le vrai taux de réussite
| # | Tâche | Fichier | État |
|---|---|---|---|
| T-01 | Endpoint `GET /api/stats/accuracy` — retourner le vrai taux sur 7j / 30j calculé depuis la table `predictions` (status = won/lost) | `PredictionController.php` | ✅ existait déjà |
| T-02 | Inclure le taux par étoiles : ★★★★ vs ★★★ vs ★★ — pour montrer que les picks premium sont plus fiables | `PredictionController.php` | ✅ 2026-06-02 |
| T-03 | Endpoint `GET /api/stats/roi` — ROI réel calculé sur mise fictive de 1 000 FCFA par pick | `PredictionController.php` | ✅ inclus dans /stats/accuracy (roi_season) |

### 2.2 — Mobile : afficher le vrai track record
| # | Tâche | Fichier | État |
|---|---|---|---|
| T-04 | Remplacer le "+184 000 FCFA" hardcodé dans `profile_screen.dart` par la valeur réelle via `gainFcfaLabel` (AccuracyStats) | `profile_screen.dart` | ✅ 2026-06-02 |
| T-05 | Remplacer la sparkline fictive par une vraie courbe depuis `sparklineProvider` (periods 30j) | `profile_screen.dart` | ✅ 2026-06-02 |
| T-06 | Sur la home screen, afficher un badge "X% de réussite ce mois" tiré de l'API | `home_screen.dart` | ✅ 2026-06-02 |
| T-07 | Sur l'onboarding slide 2 (9 critères), afficher le vrai taux de réussite 30j au lieu de "—" | `onboarding_screen.dart` | ✅ déjà branché via accuracyProvider |

---

## 3 — COTES RÉELLES 🟡

| # | Tâche | Fichier | État |
|---|---|---|---|
| C-01 | Améliorer la couverture des cotes 1xBet réelles — correspondance approximative par nom d'équipe | `GenerateAllPredictionsJob.php` | ❌ |
| C-02 | Afficher "cote estimée" dim vs "1xBet" accent sur les cards home screen | `home_screen.dart` | ✅ 2026-06-02 |
| C-03 | Masquer cotes < 1.30 (non réalistes) — retourner null dans formatPrediction | `PredictionController.php` | ✅ 2026-06-02 |

---

## 4 — HISTORIQUE UTILISATEUR 🟡

| # | Tâche | Fichier | État |
|---|---|---|---|
| H-01 | Endpoint `GET /api/predictions/history` — retourner le résultat réel (won/lost/pending) | `PredictionController.php` | ✅ déjà en place |
| H-02 | Sur `history_screen.dart`, afficher WIN vert / LOSS rouge / ATTENTE — via `isWon`/`isPending` | `history_screen.dart` | ✅ déjà en place |
| H-03 | Calcul du ROI personnel : si l'utilisateur avait misé 1 000 FCFA sur chaque pick suivi, combien aurait-il gagné/perdu ? | `StatsController.php` | ❌ |

---

## 5 — TRANSPARENCE ALGO 🟢

| # | Tâche | Fichier | État |
|---|---|---|---|
| A-01 | Page "Comment ça marche" dans l'app — expliquer les 9 critères avec les vrais poids (25% forme, 20% H2H…) | nouvelle page ou FAQ | ❌ |
| A-02 | Sur le détail prédiction, afficher "X/9 critères analysés" ou "Données limitées : X/9" | `prediction_detail_screen.dart` | ✅ 2026-06-02 |
| A-03 | Badge "données X/9" sur les cards home quand < 5 critères disponibles | `home_screen.dart` | ✅ 2026-06-02 |

---

## ORDRE D'EXÉCUTION RECOMMANDÉ

```
Semaine 1 — Filtrage ligues (F-01, F-02, F-05) + Track record backend (T-01, T-02)
Semaine 2 — Mobile track record (T-04, T-05, T-06, T-07) + Historique (H-01, H-02)
Semaine 3 — Cotes (C-01, C-02, C-03) + Transparence (A-02, A-03)
Semaine 4 — ROI personnel (H-03, T-03) + Page comment ça marche (A-01)
```

---

## IMPACT ATTENDU

| Action | Impact crédibilité |
|---|---|
| Filtrer ligues tier 1–3 | ★★★★★ — l'utilisateur reconnaît tous les matchs |
| Afficher vrai taux réussite (55%) | ★★★★ — honnête et vérifiable |
| Sparkline réelle | ★★★ — cohérence avec l'historique |
| Cotes réelles 1xBet | ★★★ — jouable directement |
| Badge "données limitées" | ★★ — transparence appréciée |
