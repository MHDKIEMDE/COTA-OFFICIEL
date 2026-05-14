# COTA — Contexte Global

Application mobile de prédictions football automatisées avec IA.

## Structure du projet

```
COTA/
├── backend/    ← API Laravel 12 (REST)
├── mobile/     ← Application Flutter (iOS/Android)
└── CDC/        ← Cahier des charges
```

## Vue d'ensemble

**COTA** est une application mobile qui génère des prédictions sportives via un algorithme IA à 9 critères. Les utilisateurs (gratuits et premium) reçoivent des pronostics avec scores de confiance, cotes estimées et coupons combinés quotidiens.

## Stack technique

| Couche   | Technologie                                      |
|----------|--------------------------------------------------|
| Backend  | Laravel 12, MySQL/SQLite, Redis, Sanctum          |
| Mobile   | Flutter, Dart, Riverpod, Dio, GoRouter            |
| APIs     | API-Football, Sportradar, OpenWeatherMap, Paydunya|

## Fonctionnalités principales

- Prédictions quotidiennes avec niveau de confiance (1–4 étoiles)
- Coupon IA combiné (4–5 meilleurs picks du jour)
- Filtrage par compétitions populaires (Tier 1–4)
- Authentification OTP (SMS/email) + Facebook OAuth
- Abonnement Premium (Wave, Orange Money, MTN, Moov via Paydunya)
- Système de parrainage et liens affiliés bookmakers

## Communication backend ↔ mobile

- Base URL configurée dans `mobile/lib/core/api/api_client.dart`
- Client HTTP : Dio avec intercepteurs Bearer token (Sanctum)
- Format : JSON, toutes les dates en ISO 8601

## Statut MVP

- [x] Algorithme de prédiction v3.0 (9 critères)
- [x] Authentification OTP + Facebook
- [x] Endpoints prédictions (today, coupon, history)
- [x] Matchs populaires filtrés par tier de ligue
- [x] Dashboard admin — pages Feedbacks (liste + détail + réponse admin)
- [x] Dashboard admin — pages Utilisateurs, Prédictions, Affiliations, Bookmakers, Compétitions, Paramètres
- [x] Flutter analyze — 0 erreur / 0 warning (18 fichiers nettoyés)
- [x] Paiement Paydunya — architecture provider-agnostique (PaymentGatewayService + AppConfig DB), clés depuis dashboard admin, bug verifyPayment corrigé, seeder AppConfig
- [x] Système de parrainage complet
- [x] Dashboard admin — pages Abonnements (liste, filtres, graphique revenus, accord manuel)
- [x] Dashboard admin — page Parrainages (liste, top parrains, paliers récompenses)
- [x] Dashboard admin — Stats avancées (taux réussite, ROI, graphiques 30j/12m, par étoiles/type/compétition)
- [x] Bookmakers par région — détection IP automatique, groupement par continent, cards alignées (1xBet/Betwinner/Melbet Afrique de l'Ouest, Bet365/Betclic Europe…)


# CLAUDE.md — Laravel + Flutter Project

## Stack
- Backend : Laravel 11, PHP 8.3, MySQL, Pest
- Mobile : Flutter 3.x, Dart, Riverpod
- Structure : backend/ → API | mobile/ → App Flutter

---

## 🧠 Graphify — Lire le graph avant d'explorer
- Avant toute exploration de fichier, lire graphify-out/GRAPH_REPORT.md
- Naviguer par les "god nodes" du graph, pas en lisant les fichiers bruts
- Utiliser /graphify query "..." pour localiser un concept précis
- Ne jamais lancer un Glob ou Grep sans consulter le graph d'abord
- Si le graph n'existe pas : taper /graphify . pour le générer

---

## ⚡ Économie de tokens
- Ne jamais relire un fichier déjà lu dans la session sauf si modifié
- Ne pas expliquer ce que tu vas faire, agir directement
- Réponses courtes sauf si explication explicitement demandée
- Ne pas reformuler ma question avant de répondre
- Ne modifier que les fichiers strictement nécessaires à la tâche
- Ne pas réécrire un fichier entier pour un petit changement, edits ciblés uniquement
- Pas de refacto non demandé
- Si la tâche touche > 3 fichiers → demander confirmation avant d'explorer

---

## 🔍 Exploration du code
- Avant d'explorer, demander : "quel fichier est concerné ?"
- Lire uniquement les fichiers liés à la tâche en cours
- Si besoin du contexte d'un fichier, lire uniquement la section pertinente
- Ne jamais lancer php artisan ou flutter run sans confirmation explicite

---

## 🔴 Qualité Laravel
- Form Requests obligatoires pour toute validation, jamais dans le Controller
- API Resources obligatoires pour tout retour JSON
- Logique métier dans app/Services/, jamais dans les Controllers
- Typage strict PHP 8.3 sur tous les paramètres et retours
- Une méthode = une responsabilité, max 20 lignes sinon découper
- Avant de terminer : vérifier php -l sur les fichiers modifiés + php artisan test

---

## 🔵 Qualité Flutter
- Architecture feature-first : features/{nom}/ui/ logic/ data/
- Jamais de logique dans les widgets, toujours dans un Provider/Controller
- Nommer les widgets de manière explicite : UserProfileCard, pas Card2
- Extraire tout widget réutilisé plus d'une fois dans /widgets/
- Toujours gérer les 4 états : loading / error / empty / success
- Avant de terminer : flutter analyze doit passer sans erreur

---

## ✅ Quality Gate — avant toute tâche terminée
1. Laravel : php -l → php artisan test → pas de TODO laissé
2. Flutter : flutter analyze → 4 états gérés → pas de logique dans les widgets
3. Jamais modifier un test pour le faire passer, corriger le code
3. apres chaque sprint recu toujours commite ou push a mon nom 

---

## ❌ Erreurs à ne pas reproduire
- ne jamais commit ou push au nom de claude

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:
- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- For cross-module "how does X relate to Y" questions, prefer `graphify query "<question>"`, `graphify path "<A>" "<B>"`, or `graphify explain "<concept>"` over grep — these traverse the graph's EXTRACTED + INFERRED edges instead of scanning files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)
