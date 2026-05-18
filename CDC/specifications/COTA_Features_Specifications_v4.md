# 📋 SPÉCIFICATIONS TECHNIQUES — FEATURES AVANCÉES COTA

## Application COTA — Pronostics Football & Écosystème Parieurs

**Version** : 4.0
**Date** : Mai 2026
**Statut** : ✅ SPÉCIFICATIONS VALIDÉES — PRÊT POUR DÉVELOPPEMENT

**Features incluses** :
- #COTA-FEAT-027 — Smart Empty State (MVP)
- #COTA-FEAT-028 — Smart Notifications System (MVP → Phases)
- #COTA-FEAT-029 — Marketplace Caissiers (Post-MVP, V2.0)
- #COTA-FEAT-030 — Stratégie Multi-API (Architecture critique)

---

## 📚 SOMMAIRE GÉNÉRAL

**PARTIE A — SMART EMPTY STATE** (Sections 1-11)
**PARTIE B — SMART NOTIFICATIONS SYSTEM** (Sections 12-23)
**PARTIE C — MARKETPLACE CAISSIERS** (Sections 24-38)
**PARTIE D — STRATÉGIE MULTI-API** (Sections 39-50)

---
---

# 🟢 PARTIE A — SMART EMPTY STATE

---

## 📌 1. CONTEXTE & PROBLÉMATIQUE

### 1.1 Le problème

L'application COTA propose des pronostics sportifs générés par un algorithme analysant **6 critères pondérés** (forme récente, H2H, dom/ext, classement, stats buts, facteur horaire). Pour garantir la qualité, l'algorithme n'affiche **que** les pronostics avec un score de confiance ≥ 60 points.

**Conséquence** : Certains jours, la page d'accueil peut se retrouver vide pour 3 raisons :

1. **Aucun match disponible** ce jour-là (intersaison, jour creux)
2. **Matchs disponibles mais aucun ≥ seuil de confiance** (matchs trop incertains)
3. **API indisponible** ou erreur technique

### 1.2 L'enjeu

Une page vide donne l'impression que :
- ❌ L'application est inactive ou bugée
- ❌ Le service ne fonctionne pas
- ❌ L'utilisateur n'a aucune raison de revenir

### 1.3 La solution proposée

Système **"Smart Empty State"** qui transforme une absence de pronostics en :
- ✅ **Preuve de qualité** (transparence pro)
- ✅ **Preuve sociale** (win rate + derniers gagnés)
- ✅ **Engagement futur** (compte à rebours)
- ✅ **Monétisation subtile** (lien vers bonus bookmakers)

---

## 🎯 2. OBJECTIFS

### 2.1 KPIs à suivre

| KPI | Cible |
|-----|-------|
| Taux de scroll complet sur empty state | > 60% |
| CTR sur "Activer notifications" | > 25% |
| CTR sur "Découvrez les bonus" | > 15% |
| Retour app après expiration du timer | > 40% |
| Réduction du taux de désinstallation | -15% |
| Augmentation DAU/MAU | +20% |

---

## 🔄 3. LOGIQUE EN CASCADE

### NIVEAU 1 — Pronostics disponibles ✅
```
SI matchs analysés AVEC score ≥ seuil
   → Affichage normal de la liste des pronostics
```

### NIVEAU 2 — Matchs analysés mais aucun ne passe le seuil ⚠️
**Message** : *"Notre algorithme a analysé X matchs. Aucun n'atteint le seuil de confiance minimum."*

### NIVEAU 3 — Aucun match disponible ❌
**Message** : *"Aucun match programmé aujourd'hui. Profitez de cette pause pour découvrir nos performances."*

---

## 🎨 4. SPÉCIFICATION DE L'INTERFACE

### 4.1 Architecture verticale (6 sections)

```
┌──────────────────────────────────────┐
│  HEADER (inchangé)                   │
├──────────────────────────────────────┤
│  SÉLECTEUR DATE                      │
├──────────────────────────────────────┤
│  FILTRE COMPÉTITIONS                 │
├──────────────────────────────────────┤
│  PILULES FILTRES                     │
├══════════════════════════════════════┤
│  ▼ ZONE SMART EMPTY STATE ▼          │
├──────────────────────────────────────┤
│  1. CARTE "AUCUN PRONOSTIC"          │
├──────────────────────────────────────┤
│  2. BANDEAU WIN RATE                 │
├──────────────────────────────────────┤
│  3. SECTION "DERNIERS GAGNÉS"        │
├──────────────────────────────────────┤
│  4. COMPTE À REBOURS                 │
├──────────────────────────────────────┤
│  5. CTA BONUS DISCRET                │
├══════════════════════════════════════┤
│  BOTTOM NAV                          │
└──────────────────────────────────────┘
```

### 4.2 Détail des blocs

#### 🛡️ BLOC 1 — Carte "Aucun pronostic"

**Style** : Carte avec dégradé jaune subtil + bordure jaune fine

**Contenu** :
- Icône `ti-shield-check` (cercle bordé jaune, 28px, `#FFEB3B`)
- Titre : "Aucun pronostic aujourd'hui"
- Message : "Notre algorithme a analysé **23 matchs**. Aucun n'atteint le seuil de confiance minimum."
- Encart info : "Nous préférons ne rien publier plutôt que vous proposer un pari risqué."

#### 📊 BLOC 2 — Bandeau Win Rate

| Colonne | Label | Valeur | Couleur |
|---------|-------|--------|---------|
| 1 | "WIN RATE" | "68%" | `#FFEB3B` |
| 2 | "ROI" | "+12%" | `#4ade80` |
| 3 | "7 JOURS" | "47/69" | `#FFFFFF` |

#### 🏆 BLOC 3 — Section "Derniers gagnés"

3 cards des pronostics gagnés (status = "won"), triés par date décroissante.

**Règle d'or** : JAMAIS afficher de pronostic perdu seul.

#### ⏰ BLOC 4 — Compte à rebours

Format `HH : MM : SS` avec chiffres jaunes en boxes (effet "casino").
Heure cible : **08h00 UTC** du jour suivant.

#### 🎁 BLOC 5 — CTA Bonus

Bouton "En attendant, découvrez les bonus exclusifs" → navigation vers onglet Bonus.

---

## 🛠️ 5. SPÉCIFICATIONS TECHNIQUES

### 5.1 Endpoint API

```http
GET /api/v1/predictions/today
Authorization: Bearer {user_token}
```

### 5.2 Response — Niveau 2

```json
{
  "status": "no_predictions_above_threshold",
  "matches_analyzed": 23,
  "min_confidence_threshold": 60,
  "next_predictions_at": "2026-05-18T08:00:00Z",
  "stats_7d": {
    "win_rate": 68,
    "roi": 12,
    "wins": 47,
    "total": 69
  },
  "last_winning_predictions": [
    {
      "id": 9876,
      "match_title": "Man City - Luton Town",
      "competition": "Premier League",
      "bet_choice": "1",
      "odds": 1.45,
      "match_date": "2026-05-16T16:00:00Z",
      "status": "won"
    }
  ]
}
```

### 5.3 Job Scheduler

```php
class CacheEmptyStateDataJob implements ShouldQueue
{
    public function handle()
    {
        $data = [
            'stats_7d' => Statistics::weekly()->latest()->first(),
            'last_winning_predictions' => Prediction::won()
                ->latest('match_date')->limit(3)->get(),
        ];
        Cache::put('empty_state_data', $data, now()->addMinutes(15));
    }
}
```

**Fréquence** : Toutes les 15 minutes

---

## ✅ 6-11. CRITÈRES D'ACCEPTATION & PLANNING

### Critères clés
- [ ] Affichage correct sur Niveau 2 et Niveau 3
- [ ] 5 blocs visibles sans scroll
- [ ] Stats et matchs dynamiques depuis API
- [ ] Compte à rebours temps réel
- [ ] Cache Redis 15 minutes
- [ ] Affichage < 800ms

### Planning : **43h / 2 semaines**

---
---

# 🔔 PARTIE B — SMART NOTIFICATIONS SYSTEM

---

## 📌 12. CONTEXTE & VISION

Système intelligent à **4 piliers** combinant routine (80%) et événementiel (20%) pour créer des habitudes ET du FOMO.

---

## 🎯 13. OBJECTIFS STRATÉGIQUES

| Objectif | Cible |
|----------|-------|
| Augmentation DAU | +35% |
| Taux de réouverture après notif | > 28% |
| Conversion gratuit → premium via "bon coup" | > 8% |
| Réduction désinstallation | -25% |
| Taux désactivation notifications | < 12% |

---

## 🏗️ 14. ARCHITECTURE DES 4 PILIERS

```
┌─────────────────────────────────────┐
│       SMART NOTIFICATIONS ENGINE     │
└──────────────────┬──────────────────┘
                   │
       ┌───────────┼───────────┬────────────┐
       ▼           ▼           ▼            ▼
   ┌───────┐  ┌─────────┐  ┌────────┐  ┌─────────┐
   │ROUTINE│  │ÉVÉNEMENT│  │CONTEXTE│  │RE-ENGAGE│
   │  🔵   │  │   🟠    │  │   🟢   │  │   🟣    │
   └───┬───┘  └────┬────┘  └───┬────┘  └────┬────┘
       │           │            │            │
       └───────────┴────────────┴────────────┘
                   │
                   ▼
       ┌─────────────────────────┐
       │  ANTI-SPAM CONTROLLER   │
       │  Max 3/jour gratuit     │
       │  Max 5/jour premium     │
       └────────────┬────────────┘
                    ▼
            ┌──────────────┐
            │ FIREBASE FCM │
            └──────────────┘
```

---

## 🔵 15.1 PILIER 1 — ROUTINE (Rendez-vous fixes)

3 notifications fixes avec **randomization Fibonacci ±8 minutes** :

#### A. Routine matin (08h00 ±5min) — ☕ ROUTINE
*"Bonjour ! 8 nouveaux pronostics · Win rate hier : 3/3 ✓"*

#### B. Routine midi (13h00 ±3min) — 🎟️ COUPON
*"Coupon du midi prêt à gratter · 5 matchs · Cote x82.47"*

#### C. Routine soir (22h00 ±2min) — 📊 BILAN
*"Bilan du jour : 3/3 gagnés ✅ · ROI +12%"*

---

## 🟠 15.2 PILIER 2 — ÉVÉNEMENTIELLES (Bons coups)

#### Conditions de déclenchement
```
Score confiance ≥ 85
ET cote ≥ 1.80 ET cote ≤ 4.00
ET match dans les 24h
ET compétition majeure
```

#### A. 🔥 BON COUP
*"Bon coup détecté · Confiance 92% · Real Madrid - Atletico · 21h · Cote 1.95"*

#### B. 🎰 COMBINÉ XXL
Pour combinés à cote totale ≥ x10.

#### C. 💎 OPPORTUNITÉ
Pour cotes qui montent significativement.

---

## 🟢 15.3 PILIER 3 — CONTEXTUELLES (Live)

**Premium uniquement.**

#### A. ⏰ MATCH IMMINENT (-1h)
*"Real - Atletico commence dans 1h. Ton pronostic Over 2.5 tient toujours."*

#### B. ⚽ MI-TEMPS
*"Tu es à 3/5 sur ton combiné. Cote potentielle : x82."*

#### C. ✅ GAGNÉ / 😔 PERDU
Bienveillant et motivant dans les 2 cas.

---

## 🟣 15.4 PILIER 4 — RE-ENGAGEMENT (Fibonacci)

**Séquence** : J+1 → J+2 → J+3 → J+5 → J+8 → J+13 → STOP

| Jour | Message |
|------|---------|
| J+1 | "On t'a manqué hier" |
| J+2 | "Win rate cette semaine : 71%" |
| J+3 | "🎁 7 jours premium offerts" |
| J+5 | "🔥 Gros bon coup demain matin" |
| J+8 | "🚀 Code exclusif : 30% sur 1 mois" |
| J+13 | STOP automatique |

---

## 🚫 17. SYSTÈME ANTI-SPAM

| Type utilisateur | Max/jour | Mode silence |
|------------------|----------|--------------|
| Gratuit | 3 | 23h - 07h |
| Premium | 5 | 23h - 07h |
| VIP | 7 | Configurable |

**Priorité** : Contextuel > Événementiel > Routine > Re-engagement
**Fusion intelligente** : Plusieurs notifs en 10min → fusion en 1 seule.

---

## 📅 20. PLAN D'IMPLÉMENTATION PAR PHASES

| Phase | Durée | Charge | Inclus |
|-------|-------|--------|--------|
| **Phase 1 — MVP Lancement** | 2 sem | 35h | Pilier 1 + anti-spam basique |
| **Phase 2 — Coupe du Monde** | 2 sem | 40h | Pilier 2 (bons coups) |
| **Phase 3 — Post-Coupe** | 2 sem | 30h | Pilier 3 (contextuelles) |
| **Phase 4 — Récupération** | 2 sem | 25h | Pilier 4 (Fibonacci) |
| **TOTAL** | **8 sem** | **130h** | Système complet |

---
---

# 🛒 PARTIE C — MARKETPLACE CAISSIERS

---

## 📌 24. CONTEXTE & VISION

### 24.1 Le problème adressé

Les parieurs sportifs en Afrique de l'Ouest font face à **2 problèmes majeurs** :

1. **Trouver de bons pronostics** ✅ (résolu par COTA actuel)
2. **Déposer/retirer leur argent facilement** ❌ (à résoudre)

### 24.2 La solution proposée

**Marketplace de mise en relation** entre joueurs et agents caissiers, intégrée dans COTA.

### 24.3 Principe fondamental

> **COTA est UNIQUEMENT un connecteur. COTA ne touche JAMAIS à l'argent.**

✅ COTA met en relation
✅ COTA valide les agents
✅ COTA gère le système de notation
❌ COTA ne fait pas de transactions
❌ COTA n'est ni partie ni garante

---

## 👤 27. PROFIL AGENT CAISSIER

### 27.1 Informations à fournir lors de l'inscription

**Personnelles** :
- Nom complet, photo, téléphone (OTP)
- Adresse + GPS
- Pièce d'identité (CNI/passeport)

**Professionnelles** :
- Types d'opérations (dépôt/retrait/les deux)
- Bookmakers supportés
- Moyens de paiement (Wave, Orange Money, MTN, Moov, etc.)
- Liquidité disponible
- Horaires de disponibilité

### 27.2 Niveaux de réputation gamifiés

| Niveau | Critères | Avantages |
|--------|----------|-----------|
| 🥉 **Bronze** | 0-49 transactions | Visibilité standard |
| 🥈 **Argent** | 50-199 + note ≥ 4.0 | Badge + visibilité +20% |
| 🥇 **Or** | 200-499 + note ≥ 4.3 | Badge + filtre "Top agents" |
| 💎 **Diamant** | 500+ + note ≥ 4.6 | Apparition prioritaire |

---

## 🛡️ 28. SYSTÈME DE VALIDATION ADMIN

### 28.1 Processus simplifié

1. Agent crée son profil → Statut `PENDING`
2. **Admin reçoit notification dashboard**
3. Admin **enquête** :
   - Vérification téléphone
   - Recherche réseaux sociaux
   - Appel optionnel
   - Adresse via Google Maps
4. Décision : ✅ APPROUVER / ❌ REJETER / 🔄 DEMANDER COMPLÉMENT

**Pas de KYC lourd** — Validation manuelle légère et rapide.

---

## ⭐ 29. SYSTÈME DE NOTATION

### 29.1 Principe fondamental

**Tout joueur DOIT noter l'agent après transaction.**

**Récompense pour le joueur qui note** :
- 🎁 **Choix** : 1 coupon premium OU 1 journée VIP

### 29.2 Formulaire de notation

- ⭐ Note globale (1-5)
- Critères détaillés (Rapidité, Fiabilité, Courtoisie, Frais)
- Commentaire optionnel
- Type transaction (Dépôt/Retrait)
- Montant approximatif (tranches)

### 29.3 Anti-fraude
- 1 récompense max par jour
- Note après vraie mise en relation (vérifiée chat)
- Si note ≤ 2 étoiles → bouton "Signaler un problème" → ticket de litige

---

## 🗺️ 30. EXPÉRIENCE UTILISATEUR (Joueur)

### Parcours type

1. Joueur ouvre onglet "Marketplace"
2. Géolocalisation → carte avec agents proches
3. Filtres : type, distance, montant, bookmaker, note
4. Profil agent → boutons [📞 Appeler] [💬 Chat] [🗺️ Maps]
5. Contact via chat in-app
6. Transaction HORS plateforme (mobile money direct)
7. Notation post-transaction → récompense

---

## 🛠️ 32. SPÉCIFICATIONS TECHNIQUES

### Tables BDD (5 nouvelles tables)

```sql
agents (id, user_id, full_name, photo, phone, address, gps,
        bookmakers, payment_methods, liquidity, status, level, ...)

agent_ratings (id, agent_id, user_id, rating, comment,
               transaction_type, reward_claimed, ...)

marketplace_conversations (id, user_id, agent_id, status, ...)

marketplace_messages (id, conversation_id, sender_type, content, ...)

marketplace_disputes (id, user_id, agent_id, description,
                      evidence, status, admin_decision, ...)
```

---

## 📅 34. PLAN D'IMPLÉMENTATION

| Phase | Durée | Charge | Description |
|-------|-------|--------|-------------|
| Phase 0 | 1 sem | - | Préparation + juridique |
| Phase 1 | 4 sem | 170h | MVP fonctionnel |
| Phase 2 | 3 sem | 90h | Chat + notation |
| Phase 3 | 2 sem | 60h | Litiges |
| **TOTAL** | **10 sem** | **320h** | Marketplace complète |

### Calendrier suggéré

- **Juin 2026** : Lancement COTA (focus pronostics)
- **Août-Décembre 2026** : Développement Marketplace
- **Décembre 2026** : Lancement COTA v2.0 (Marketplace)

---

## ⚠️ 37. POINTS LÉGAUX

### Risques principaux
- Considéré comme intermédiaire financier → CGU strictes
- Utilisation pour blanchiment → signalement + plafonds
- Arnaques d'agents → validation + notation + bannissement

### Disclaimer permanent in-app
```
⚖️ AVERTISSEMENT MARKETPLACE
Cette section est un service de mise en relation.
❌ COTA n'effectue PAS de transactions
❌ COTA n'est PAS responsable des fonds échangés
✅ COTA fournit système de validation et notation
```

---
---

# 🔌 PARTIE D — STRATÉGIE MULTI-API

---

## 📌 39. CONTEXTE & VISION

### 39.1 Le problème actuel

Le cahier des charges initial prévoit **une seule API** (API-Football). Cette approche mono-source présente plusieurs risques critiques :

- ❌ **Single point of failure** : Si l'API tombe, COTA est inutilisable
- ❌ **Dépendance prix** : Augmentation tarifaire imposée
- ❌ **Limites de données** : Limitation à ce qu'un seul fournisseur propose
- ❌ **Pas de vérification croisée** : Impossible de confirmer une info
- ❌ **Pas de fallback** : Aucun plan B en cas de panne

### 39.2 La vision multi-API

Construire un **écosystème d'APIs spécialisées**, chacune excellente dans son domaine :

- ✅ **Redondance** : Si une API tombe, fallback automatique
- ✅ **Richesse données** : Chaque API excelle dans son domaine
- ✅ **Vérification croisée** : Confirme les infos importantes
- ✅ **Différenciation produit** : Faire ce que les concurrents ne font pas
- ✅ **Algorithme enrichi** : Plus de données = pronostics plus précis

### 39.3 Stratégie de coût : 100% gratuit au démarrage

**Principe fondamental** : Démarrer 100% gratuit, upgrade uniquement quand les revenus le justifient.

---

## 🎯 40. OBJECTIFS

| Objectif | Cible |
|----------|-------|
| Coût mensuel APIs en phase MVP (Mois 0-3) | **0€** |
| Coût mensuel APIs en phase Scale (Mois 6+) | < 80$ |
| Disponibilité données (uptime) | > 99% |
| Latence moyenne API Gateway | < 200ms |
| Taux de cache hit | > 80% |
| Couverture compétitions | > 1200 |

---

## 🏗️ 41. ARCHITECTURE GÉNÉRALE

### 41.1 Vue d'ensemble

```
┌──────────────────────────────────────┐
│         COTA App (Flutter)           │
└──────────────────┬───────────────────┘
                   │
                   ▼
┌──────────────────────────────────────┐
│   API Gateway COTA (Laravel)         │
│   • Cache Redis                      │
│   • Fallback automatique             │
│   • Rate limiting                    │
│   • Monitoring quotas                │
└──────────────────┬───────────────────┘
                   │
       ┌───────────┴───────────────────┐
       │                               │
       ▼                               ▼
┌──────────────────┐         ┌──────────────────┐
│  API PRINCIPALE  │  ←──→   │  API SECOURS     │
│  (par catégorie) │ Fallback│  (par catégorie) │
└──────────────────┘         └──────────────────┘
```

### 41.2 Les 6 catégories de données

| Catégorie | Usage |
|-----------|-------|
| 🔵 **Matchs + Stats** | Calendrier, scores live, stats équipes |
| 🟣 **Cotes bookmakers** | Comparaison cotes, value bets |
| 🔵 **Joueurs** | Blessures, suspensions, forme |
| 🟢 **Météo** | Conditions match (impact algorithme) |
| 🩷 **News** | Actualités, mercato, blessures |
| 🟠 **Streams** | ⚠️ Liens TV légaux uniquement |

---

## 🆓 42. STRATÉGIE 100% GRATUITE (MVP)

### 42.1 Stack APIs gratuites recommandées

| Priorité | Catégorie | API Principale | Limite Free |
|---|---|---|---|
| **#1** | Matchs/Stats | **API-Football** | 100 req/jour |
| **#1bis** | Backup matchs | **football-data.org** | 10 req/min |
| **#2** | Cotes | **The Odds API** | 500 req/mois |
| **#3** | Joueurs | API-Football (inclus) | - |
| **#4** | Météo | **OpenWeatherMap** | 1000 req/jour |
| **#5** | News | **GNews API** | 100 req/jour |
| **#6** | Streams | ❌ Skip MVP | - |
| **#7** | Logos | **TheSportsDB** | Illimité |

**💰 Coût total mensuel MVP : 0€**

### 42.2 Détail de chaque API gratuite

#### 🥇 API-Football (Free Plan)

**URL** : https://www.api-football.com/
**Limite** : 100 requêtes/jour
**Couverture** : 1200+ compétitions (incluant Coupe du Monde 2026)

**Endpoints critiques** :
```
GET /fixtures            (matchs du jour)
GET /fixtures/predictions (prédictions intégrées)
GET /fixtures/headtohead (H2H pour algo)
GET /standings           (classements)
GET /odds                (cotes)
GET /injuries            (blessures)
```

**Stratégie pour tenir avec 100 req/jour** :
- 1 req matin → tous les matchs (cache 24h)
- 15 req → stats détaillées par match (cache 12h)
- 5 req → blessures équipes
- 2 req → cotes
- ~25-30 req/jour utilisées → marge de 70%

#### 🥈 football-data.org (Backup gratuit)

**URL** : https://www.football-data.org/
**Limite** : Rate limit 10 req/min
**Couverture** : Top 5 européens + Champions League

**Avantages** :
- Documentation excellente
- API REST très propre
- Uptime fiable
- Pas de limite quotidienne

**Stratégie hybride** :
- API-Football → compétitions africaines/secondaires/mondial
- football-data.org → Top 5 européens
- **Couverture totale gratuite !**

#### 🥉 The Odds API (Free Plan)

**URL** : https://the-odds-api.com/
**Limite** : 500 requêtes/mois (~16/jour)
**Couverture** : 50+ bookmakers incluant Betwinner, 1xBet, Melbet

**Innovation possible** : Détecter automatiquement les **arbitrages de cotes**
> *"Real Madrid à 2.10 chez Betwinner mais 1.85 chez 1xBet → opportunité !"*

#### 🌧️ OpenWeatherMap (Free Plan)

**URL** : https://openweathermap.org/api
**Limite** : 1000 requêtes/jour (largement suffisant)
**Couverture** : Mondial

**Usage stratégique** :
```php
// Pour chaque match programmé
$weather = OpenWeatherMap::getForecast(
    lat: $stadium->latitude,
    lng: $stadium->longitude,
    time: $match->kickoff_time
);

// Ajustement algorithme
if ($weather->rain > 5) {
    $score += $rainBonusForUnderTeams;
}
if ($weather->wind > 30) {
    $score -= $windPenaltyForLongShots;
}
```

**Innovation** : Ajouter un **7ème critère "Météo"** à l'algorithme (3-5% pondération).

#### 📰 GNews API (Free Plan)

**URL** : https://gnews.io/
**Limite** : 100 requêtes/jour
**Couverture** : News mondiale, filtres avancés

**Usage** :
- Détecter mots-clés : "blessure", "conflit", "changement coach"
- Alimenter les notifications événementielles (Pilier 2)
- Sentiment analysis sur les équipes

#### 🎨 TheSportsDB (Gratuit illimité)

**URL** : https://www.thesportsdb.com/
**Limite** : Illimité (clé gratuite via Patreon $0)
**Usage** :
- Logos équipes pour l'UI
- Photos joueurs
- Images stades
- Bannières compétitions

**Enrichit visuellement l'app sans coût.**

### 42.3 Calcul de consommation quotidienne optimisée

| Action | Fréquence | API consommée |
|--------|-----------|---------------|
| Fetch matchs du jour | 1x à 08h00 | 1 req API-Football |
| Stats par match (15 max) | 1x à 08h00 | 15 req API-Football |
| Refresh scores live | 5x/match | ~10 req API-Football |
| Fetch blessures | 1x à 07h00 | 5 req API-Football |
| Refresh cotes | 2x/jour | 2 req Odds API |
| Météo matchs | 1x à 08h00 | 15 req OpenWeather |
| News football | 4x/jour | 4 req GNews |
| **TOTAL JOUR** | - | **~52 req API-Football** |

**Marge de sécurité** : 50% sous le quota gratuit ✅

---

## 💰 43. STRATÉGIE PAYANTE (Phase Scale)

### 43.1 Évolution progressive

#### Mois 0-3 : **100% GRATUIT** (Phase MVP)
```
✅ API-Football Free (100 req/jour)
✅ football-data.org (illimité avec rate limit)
✅ The Odds API Free (500 req/mois)
✅ OpenWeatherMap Free (1000 req/jour)
✅ GNews Free (100 req/jour)
✅ TheSportsDB Free (illimité)

💰 Coût : 0€/mois
```

#### Mois 4-6 : **UPGRADE CIBLÉ** (si revenus arrivent)
```
→ API-Football Pro (19$/mois) - dès qu'on dépasse 80 req/jour
→ Reste sur autres APIs gratuites

💰 Coût : ~19$/mois
```

#### Mois 7-12 : **PHASE SCALE**
```
→ API-Football Ultra (29$/mois)
→ The Odds API Standard (30$/mois)
→ Sportmonks Hobbyist (12€/mois) - pour xG natif

💰 Coût : ~75$/mois
```

#### Mois 12+ : **PHASE MATURE**
```
→ Sportradar enterprise si revenus >5000$/mois
→ APIs premium pour vérification croisée

💰 Coût : 150-300$/mois (mais ROI prouvé)
```

### 43.2 Critère d'upgrade

**Règle d'or** : Ne paie une API que quand :
1. Tu atteins 80%+ du quota gratuit régulièrement
2. Tes revenus mensuels couvrent au moins 10x le coût de l'API
3. Une feature payante dépend directement de cette API

---

## 🛠️ 44. ARCHITECTURE TECHNIQUE LARAVEL

### 44.1 Structure des dossiers

```
app/
├── Services/
│   └── ApiGateway/
│       ├── ApiGatewayService.php           # Routeur principal
│       ├── ApiQuotaTracker.php             # Suivi quotas
│       ├── ApiFallbackHandler.php          # Gestion fallbacks
│       │
│       ├── Providers/
│       │   ├── ApiFootballProvider.php
│       │   ├── FootballDataOrgProvider.php
│       │   ├── TheOddsApiProvider.php
│       │   ├── OpenWeatherMapProvider.php
│       │   ├── GNewsProvider.php
│       │   └── TheSportsDbProvider.php
│       │
│       └── Adapters/
│           ├── MatchDataAdapter.php        # Normalisation matchs
│           ├── OddsDataAdapter.php         # Normalisation cotes
│           ├── PlayerDataAdapter.php       # Normalisation joueurs
│           └── WeatherDataAdapter.php      # Normalisation météo
│
├── Models/
│   ├── ApiCall.php                         # Log appels API
│   ├── ApiQuotaUsage.php                   # Suivi consommation
│   └── ApiFallbackEvent.php                # Log fallbacks
│
└── Jobs/
    ├── MonitorApiQuotasJob.php             # Hourly
    ├── AlertApiLimitJob.php                # Si 80% quota
    └── DailyApiReportJob.php               # Daily 23h
```

### 44.2 Pattern API Gateway

```php
// app/Services/ApiGateway/ApiGatewayService.php

class ApiGatewayService
{
    public function __construct(
        protected ApiFootballProvider $apiFootball,
        protected FootballDataOrgProvider $footballDataOrg,
        protected ApiQuotaTracker $quotaTracker,
        protected ApiFallbackHandler $fallbackHandler
    ) {}

    public function getMatches(string $date): Collection
    {
        $cacheKey = "matches:{$date}";

        // 1. Check cache d'abord
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // 2. Vérifier quota disponible
        if (!$this->quotaTracker->canCall('api_football')) {
            return $this->getFallbackMatches($date, $cacheKey);
        }

        // 3. Try primary API
        try {
            $rawData = $this->apiFootball->fetchMatches($date);
            $matches = MatchDataAdapter::fromApiFootball($rawData);

            $this->quotaTracker->record('api_football', 1);
            Cache::put($cacheKey, $matches, $this->getCacheDuration($date));

            return $matches;
        } catch (ApiException $e) {
            return $this->getFallbackMatches($date, $cacheKey);
        }
    }

    private function getFallbackMatches(string $date, string $cacheKey): Collection
    {
        // Fallback vers football-data.org
        try {
            $rawData = $this->footballDataOrg->fetchMatches($date);
            $matches = MatchDataAdapter::fromFootballDataOrg($rawData);

            $this->fallbackHandler->log('api_football', 'football_data_org');
            Cache::put($cacheKey, $matches, $this->getCacheDuration($date));

            return $matches;
        } catch (ApiException $e2) {
            // Dernier recours : cache "stale"
            return Cache::get("{$cacheKey}:stale", collect());
        }
    }

    private function getCacheDuration(string $date): int
    {
        $matchDate = Carbon::parse($date);

        if ($matchDate->isPast()) return 86400 * 7;   // 7 jours pour passé
        if ($matchDate->isFuture()) return 3600 * 6;  // 6h pour futur
        return 600; // 10min pour aujourd'hui
    }
}
```

### 44.3 Pattern Adapter (normalisation)

```php
// app/Services/ApiGateway/Adapters/MatchDataAdapter.php

class MatchDataAdapter
{
    public static function fromApiFootball(array $rawData): Collection
    {
        return collect($rawData['response'])->map(fn($match) => [
            'external_id' => $match['fixture']['id'],
            'source' => 'api_football',
            'home_team' => $match['teams']['home']['name'],
            'away_team' => $match['teams']['away']['name'],
            'home_logo' => $match['teams']['home']['logo'],
            'away_logo' => $match['teams']['away']['logo'],
            'competition' => $match['league']['name'],
            'kickoff_time' => Carbon::parse($match['fixture']['date']),
            'status' => self::normalizeStatus($match['fixture']['status']['short']),
            'venue' => $match['fixture']['venue']['name'] ?? null,
            'venue_lat' => $match['fixture']['venue']['lat'] ?? null,
            'venue_lng' => $match['fixture']['venue']['lng'] ?? null,
        ]);
    }

    public static function fromFootballDataOrg(array $rawData): Collection
    {
        return collect($rawData['matches'])->map(fn($match) => [
            'external_id' => 'fdo_' . $match['id'],
            'source' => 'football_data_org',
            'home_team' => $match['homeTeam']['name'],
            'away_team' => $match['awayTeam']['name'],
            'home_logo' => $match['homeTeam']['crest'] ?? null,
            'away_logo' => $match['awayTeam']['crest'] ?? null,
            'competition' => $match['competition']['name'],
            'kickoff_time' => Carbon::parse($match['utcDate']),
            'status' => self::normalizeStatus($match['status']),
            'venue' => null,
            'venue_lat' => null,
            'venue_lng' => null,
        ]);
    }

    private static function normalizeStatus(string $status): string
    {
        return match($status) {
            'NS', 'SCHEDULED', 'TIMED' => 'scheduled',
            '1H', '2H', 'HT', 'LIVE', 'IN_PLAY' => 'live',
            'FT', 'AET', 'PEN', 'FINISHED' => 'finished',
            'PST', 'CANC', 'POSTPONED', 'CANCELED' => 'postponed',
            default => 'unknown'
        };
    }
}
```

### 44.4 Système de cache Redis

**Stratégie de cache à 3 niveaux** :

```php
// app/Services/ApiGateway/CacheStrategy.php

class CacheStrategy
{
    // Niveau 1 : Cache court (matchs live)
    const LIVE_TTL = 60; // 1 minute

    // Niveau 2 : Cache moyen (matchs du jour)
    const TODAY_TTL = 600; // 10 minutes

    // Niveau 3 : Cache long (matchs futurs)
    const FUTURE_TTL = 21600; // 6 heures

    // Niveau 4 : Cache très long (matchs passés)
    const HISTORICAL_TTL = 604800; // 7 jours

    // Niveau 5 : Cache "stale" (en cas de fallback total)
    const STALE_TTL = 2592000; // 30 jours
}
```

---

## 📊 45. MONITORING & ALERTES

### 45.1 Table de tracking BDD

```sql
CREATE TABLE api_calls (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(50) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) DEFAULT 'GET',
    status_code INT,
    response_time_ms INT,
    error_message TEXT NULL,
    was_fallback BOOLEAN DEFAULT FALSE,
    cache_hit BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_provider_date (provider, created_at),
    INDEX idx_fallbacks (was_fallback, created_at)
);

CREATE TABLE api_quota_usage (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    requests_count INT DEFAULT 0,
    quota_limit INT NOT NULL,
    last_request_at TIMESTAMP NULL,
    UNIQUE KEY unique_provider_date (provider, date)
);
```

### 45.2 Dashboard admin (Filament)

**Section "API Monitoring"** :

```
┌─────────────────────────────────────────────┐
│         API Monitoring Dashboard            │
├─────────────────────────────────────────────┤
│                                             │
│  API-Football        ████████░░  82/100     │
│  ⚠️ Approche limite (82%)                  │
│                                             │
│  football-data.org   ███░░░░░░░  Healthy   │
│                                             │
│  The Odds API        █████░░░░░  234/500   │
│  ✅ OK (47% utilisé)                       │
│                                             │
│  OpenWeatherMap      ██░░░░░░░░  43/1000   │
│  ✅ Très large marge                       │
│                                             │
│  GNews               █░░░░░░░░░  12/100    │
│  ✅ OK                                     │
│                                             │
├─────────────────────────────────────────────┤
│  Fallbacks aujourd'hui : 3                  │
│  Cache hit rate : 87%                       │
│  Latence moyenne : 145ms                    │
│  Erreurs : 0                                │
└─────────────────────────────────────────────┘
```

### 45.3 Alertes automatiques

**Notifications admin** quand :
- ⚠️ **80% quota atteint** sur une API
- 🚨 **95% quota atteint** (alerte urgente)
- ❌ **API down** (>3 erreurs consécutives)
- 🔄 **Fallback déclenché** (info)
- 📉 **Latence > 500ms** sur une API

**Job de monitoring** :

```php
class MonitorApiQuotasJob implements ShouldQueue
{
    public function handle()
    {
        $providers = ['api_football', 'football_data_org',
                      'the_odds_api', 'open_weather_map', 'gnews'];

        foreach ($providers as $provider) {
            $usage = ApiQuotaUsage::where('provider', $provider)
                ->where('date', today())
                ->first();

            if (!$usage) continue;

            $percentage = ($usage->requests_count / $usage->quota_limit) * 100;

            if ($percentage >= 95) {
                Notification::send(
                    User::admins()->get(),
                    new ApiQuotaCriticalAlert($provider, $percentage)
                );
            } elseif ($percentage >= 80) {
                Notification::send(
                    User::admins()->get(),
                    new ApiQuotaWarningAlert($provider, $percentage)
                );
            }
        }
    }
}
```

**Fréquence** : Toutes les heures

---

## 📅 46. PLAN D'IMPLÉMENTATION

### 46.1 Phases de développement

#### **Phase 0 — Inscriptions APIs (1h)**

- [ ] Créer compte API-Football (Free)
- [ ] Créer compte football-data.org (Free)
- [ ] Créer compte The Odds API (Free)
- [ ] Créer compte OpenWeatherMap (Free)
- [ ] Créer compte GNews (Free)
- [ ] Créer compte TheSportsDB (Free)
- [ ] Sauvegarder toutes les clés API dans `.env`

#### **Phase 1 — Architecture de base (1 semaine - 30h)**

- [ ] Créer structure `app/Services/ApiGateway/`
- [ ] Implémenter `ApiGatewayService` (routeur principal)
- [ ] Implémenter `ApiFootballProvider` (provider principal)
- [ ] Implémenter pattern Adapter pour normalisation
- [ ] Setup Redis cache
- [ ] Tests unitaires

#### **Phase 2 — Fallback & Multi-API (1 semaine - 25h)**

- [ ] Implémenter `FootballDataOrgProvider` (backup)
- [ ] Implémenter `ApiFallbackHandler`
- [ ] Logique de fallback automatique
- [ ] Stratégie de cache à 3 niveaux
- [ ] Tests d'intégration

#### **Phase 3 — APIs spécialisées (1 semaine - 25h)**

- [ ] Implémenter `TheOddsApiProvider` (cotes)
- [ ] Implémenter `OpenWeatherMapProvider` (météo)
- [ ] Implémenter `GNewsProvider` (news)
- [ ] Implémenter `TheSportsDbProvider` (logos)
- [ ] Adaptateurs spécifiques

#### **Phase 4 — Monitoring & Dashboard (1 semaine - 20h)**

- [ ] Tables BDD (api_calls, api_quota_usage)
- [ ] Implémenter `ApiQuotaTracker`
- [ ] Dashboard Filament API Monitoring
- [ ] Jobs de monitoring (hourly)
- [ ] Système d'alertes (email + push admin)

### 46.2 Estimation totale

| Phase | Durée | Charge |
|-------|-------|--------|
| Phase 0 | 1h | Inscriptions |
| Phase 1 | 1 sem | 30h |
| Phase 2 | 1 sem | 25h |
| Phase 3 | 1 sem | 25h |
| Phase 4 | 1 sem | 20h |
| **TOTAL** | **4 sem** | **101h** |

---

## ✅ 47. CRITÈRES D'ACCEPTATION

### 47.1 Architecture

- [ ] `ApiGatewayService` opérationnel avec routage automatique
- [ ] Pattern Adapter normalise les données de tous les providers
- [ ] Fallback automatique fonctionnel (API-Football → football-data.org)
- [ ] Cache Redis 3 niveaux opérationnel
- [ ] Aucune dépendance directe à une seule API dans le code métier

### 47.2 Monitoring

- [ ] Tracking des appels API en BDD
- [ ] Dashboard admin affichant l'usage en temps réel
- [ ] Alertes automatiques à 80% et 95% quota
- [ ] Logs des fallbacks consultables
- [ ] Rapport quotidien envoyé par email admin

### 47.3 Performance

- [ ] Taux de cache hit > 80%
- [ ] Latence moyenne < 200ms
- [ ] Quotas gratuits jamais dépassés (avec marge 20%)
- [ ] Aucune perte de données utilisateur si API tombe
- [ ] App reste fonctionnelle en mode dégradé

---

## 📊 48. KPIs & MÉTRIQUES

### 48.1 Métriques techniques

| Métrique | Cible | Mesure |
|----------|-------|--------|
| Cache hit rate | > 80% | Quotidien |
| Latence API Gateway | < 200ms | Temps réel |
| Uptime API Gateway | > 99.9% | Mensuel |
| Fallbacks par jour | < 5 | Quotidien |
| Erreurs API par jour | < 10 | Quotidien |

### 48.2 Métriques business

| Métrique | Cible Mois 3 | Cible Mois 12 |
|----------|--------------|---------------|
| Coût mensuel APIs | 0€ | < 100$ |
| Couverture compétitions | 1200+ | 2000+ |
| Données disponibles par match | 50+ critères | 70+ critères |
| Précision algorithme | 60% | 65% |

---

## 🚀 49. AMÉLIORATIONS FUTURES (V2)

### 49.1 Phase 2 (Mois 6+)

- [ ] **Sportmonks intégration** : Pour xG natif (données avancées)
- [ ] **Twitter/X API** : Sentiment analysis pré-match
- [ ] **Weather historique** : Pour entraîner l'algo sur la météo
- [ ] **Player tracking** : Position GPS des joueurs (StatsBomb)

### 49.2 Phase 3 (Mois 12+)

- [ ] **Sportradar Enterprise** : Données officielles ultra-précises
- [ ] **Machine Learning pipeline** : Auto-tuning de l'algo selon les sources
- [ ] **API publique COTA** : Revente de pronostics aux développeurs tiers
- [ ] **Multi-sport** : Extension basket, tennis, F1

---

## 📝 50. CHECKLIST DE LANCEMENT

### 50.1 Avant le lancement Coupe du Monde 2026

- [ ] Comptes API gratuits créés et clés sauvegardées
- [ ] Architecture ApiGateway implémentée
- [ ] Cache Redis configuré et optimisé
- [ ] Fallback automatique testé
- [ ] Dashboard admin opérationnel
- [ ] Alertes configurées
- [ ] Tests de charge effectués (simulation 1000 users)
- [ ] Documentation technique à jour
- [ ] Plan de bascule vers APIs payantes documenté

### 50.2 Monitoring quotidien post-lancement

- [ ] Vérification dashboard chaque matin
- [ ] Revue des fallbacks de la veille
- [ ] Analyse latence et erreurs
- [ ] Optimisation requêtes si nécessaire
- [ ] Mise à jour du cache si besoin

---

## 💡 51. CONSEILS STRATÉGIQUES

### 51.1 Les 3 règles d'or

1. **Ne paie aucune API avant d'avoir 100 utilisateurs payants**
   Tu peux tenir 3-6 mois 100% gratuit avec un bon cache.

2. **L'investissement le plus rentable au début : Cache Redis**
   2-3 heures de dev = des mois d'économies d'APIs.

3. **Structure ton code multi-API DÈS le départ**
   Même si tu utilises 1 seule API, le pattern `ApiGatewayService` doit exister.

### 51.2 Pièges à éviter

- ❌ Ne pas appeler les APIs directement depuis le code métier
- ❌ Ne pas ignorer le cache (= explosion des quotas)
- ❌ Ne pas oublier le fallback (= app down si API tombe)
- ❌ Ne pas dépendre des streams (= risques légaux)
- ❌ Ne pas payer trop tôt (= argent gaspillé)

---
---

## 🚀 ROADMAP GÉNÉRALE COTA

| Période | Feature | Statut |
|---------|---------|--------|
| **Mai 2026** | Architecture Multi-API + Empty State | 🟡 Spec validée |
| **Mai-Juin 2026** | Smart Notifications Phase 1 | 🟡 Spec validée |
| **Juin 2026** | **LANCEMENT COTA + Coupe du Monde 2026** | 🟢 En préparation |
| **Juin-Juillet 2026** | Smart Notifications Phase 2 (bons coups) | 🟡 Spec validée |
| **Août-Sept 2026** | Smart Notifications Phase 3-4 + Upgrade APIs | 🟡 Spec validée |
| **Août-Décembre 2026** | Marketplace Caissiers (4 phases) | 🟡 Spec validée |
| **Décembre 2026** | **LANCEMENT COTA v2.0 (Marketplace)** | 🔴 Cible |
| **2027** | Expansion multi-pays + Multi-sport | 🔴 Vision |

---

## 💰 BUDGET CONSOLIDÉ

### Budget initial (Phase MVP - 4 mois)

| Poste | Coût mensuel | Total 4 mois |
|-------|--------------|--------------|
| APIs (gratuites) | **0€** | **0€** |
| VPS Hostinger | 15€ | 60€ |
| SMS OTP | 10€ | 40€ |
| Domaine | 1€ | 12€ |
| Google Play | - | 25€ |
| Apple Developer | - | 99€/an |
| **TOTAL** | **26€/mois** | **~225€** |

### Budget Phase Scale (Mois 6+)

| Poste | Coût mensuel |
|-------|--------------|
| APIs (upgrade ciblé) | ~50$ |
| VPS Upgrade | 30€ |
| SMS OTP volume | 50€ |
| Email SendGrid | 10€ |
| Monitoring Sentry Pro | 26€ |
| **TOTAL** | **~165€/mois** |

---

## 📞 CONTACTS

**Porteur de projet** : MHD SERVICE
**Email Support** : support@mhdservice.com
**Email Technique** : dev@mhdservice.com

---

## ✅ VALIDATION FINALE GÉNÉRALE

| Élément | Statut |
|---------|--------|
| **PARTIE A — Smart Empty State** | ✅ COMPLÈTE |
| Spécification UX/UI | ✅ COMPLÈTE |
| API & BDD | ✅ DÉFINIE |
| Estimation 43h / 2 sem | ✅ APPROUVÉE |
| **PARTIE B — Smart Notifications** | ✅ COMPLÈTE |
| 4 piliers définis | ✅ DOCUMENTÉ |
| Anti-spam | ✅ CONÇU |
| Estimation 130h / 8 sem | ✅ APPROUVÉE |
| **PARTIE C — Marketplace Caissiers** | ✅ COMPLÈTE |
| Validation admin simple | ✅ DÉFINIE |
| Système notation + récompense | ✅ SPÉCIFIÉ |
| Estimation 320h / 10 sem | ✅ APPROUVÉE |
| **PARTIE D — Stratégie Multi-API** | ✅ COMPLÈTE |
| Stack 100% gratuit MVP | ✅ DÉFINI |
| Architecture ApiGateway | ✅ SPÉCIFIÉE |
| Monitoring & alertes | ✅ DOCUMENTÉ |
| Plan upgrade progressif | ✅ ORGANISÉ |
| Estimation 101h / 4 sem | ✅ APPROUVÉE |

**🎉 LES 4 FEATURES SONT PRÊTES POUR DÉVELOPPEMENT**

**Charge totale développement** : **594h** (~14 semaines de dev intensif)

**Signature** : MHD SERVICE
**Date validation** : Mai 2026
**Version** : 4.0 Final

---

**Document confidentiel** — Propriété intellectuelle MHD SERVICE — Reproduction interdite
