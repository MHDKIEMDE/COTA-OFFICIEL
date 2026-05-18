# 📋 CAHIER DES CHARGES v4.0 - FOOTAPP

**Application Pronostics Football Automatisée par IA**

> **Version** : 4.0 (Modulaire Lean)
> **Date** : Octobre 2025
> **Porteur** : MHD SERVICE
> **Statut** : ✅ COMPLET - PRÊT POUR DÉVELOPPEMENT SOLO
> **Stack** : Laravel 11 + Flutter 3.24 + Python ML + MySQL
> **Total** : 33 fichiers Markdown - ~600 KB documentation

---

## 🎯 PROFIL PROJET

| Critère | Valeur |
|---------|--------|
| **Objectif principal** | Cash flow rapide (12 mois) |
| **Priorité absolue** | Algorithme à 60%+ de fiabilité |
| **Mode développement** | Solo + IA (Claude/Cursor) |
| **Budget MVP** | < 5 000€ (4 mois) |
| **Marché cible** | Afrique Ouest + Maghreb + France |
| **Modèle** | Freemium + Affiliation + B2B futur |

---

## 📂 STRUCTURE MODULAIRE

### 📌 Documents Fondamentaux

| # | Document | Description |
|---|----------|-------------|
| `00` | **INDEX.md** *(ce fichier)* | Sommaire global + navigation |
| `01` | **VISION-OBJECTIFS.md** | Vision, public cible, KPIs |
| `02` | **BUDGET-ROADMAP.md** | Budget détaillé + planning sprints |
| `03` | **STACK-TECHNIQUE.md** | Architecture technique globale |
| `04` | **RISQUES-ALERTES.md** | Risques identifiés + mitigations |

### 🚀 PHASE 1 - MVP CRITIQUE (Mois 1-4, < 5000€)

> **Objectif** : Lancer une app fonctionnelle qui génère du cash dès le mois 4.

| Sprint | Durée | Module | Priorité |
|--------|-------|--------|----------|
| `SPRINT-01` | 2 sem | **Setup Backend Laravel** | 🔴 CRITIQUE |
| `SPRINT-02` | 1 sem | **Authentification (OTP + Facebook)** | 🔴 CRITIQUE |
| `SPRINT-03` | 2 sem | **Algorithme v3.0 (règles + base ML)** | 🔴 CRITIQUE |
| `SPRINT-04` | 2 sem | **Multi-sources données + xG** | 🟠 IMPORTANT |
| `SPRINT-05` | 1 sem | **Paiements (Paydunya + Stripe)** | 🔴 CRITIQUE |
| `SPRINT-06` | 1 sem | **Affiliation Bookmakers + Auto-fill** | 🔴 CRITIQUE |
| `SPRINT-07` | 2 sem | **Mobile App Core (Flutter)** | 🔴 CRITIQUE |
| `SPRINT-08` | 1 sem | **Mobile Premium + Carte à gratter** | 🟠 IMPORTANT |
| `SPRINT-09` | 1 sem | **Dashboard Admin (Filament)** | 🟠 IMPORTANT |
| `SPRINT-10` | 1 sem | **Tests + Déploiement + Beta** | 🔴 CRITIQUE |

**Total Phase 1** : ~14 semaines (3.5 mois)

### 📈 PHASE 2 - GROWTH (Mois 5-8, auto-financée)

> **Objectif** : Scale les revenus, fidéliser, expansion.

| Module | Durée | Priorité revenus |
|--------|-------|------------------|
| `MODULE-Gamification.md` | 2 sem | 🟢 Rétention +30% |
| `MODULE-Site-Web-SEO.md` | 3 sem | 🟢 Acquisition gratuite |
| `MODULE-Multi-Langues.md` | 1 sem | 🟢 Expansion |
| `MODULE-Tier-VIP.md` | 1 sem | 🔴 Revenus +30% |
| `MODULE-Coaching-Premium.md` | 2 sem | 🟢 High-ticket |
| `MODULE-Notifications-Avancees.md` | 1 sem | 🟢 Engagement |

### 🌐 PHASE 3 - SCALE (Mois 9-12+)

> **Objectif** : Devenir une plateforme, B2B, écosystème.

| Module | Durée | Type |
|--------|-------|------|
| `MODULE-ML-Avance.md` | 4 sem | Tech |
| `MODULE-Marketplace-Pronostiqueurs.md` | 4 sem | Business |
| `MODULE-API-Publique-B2B.md` | 3 sem | Business |
| `MODULE-Live-Betting.md` | 4 sem | Tech |
| `MODULE-Multi-Sports.md` | 3 sem | Tech |
| `MODULE-Fantasy-Football.md` | 6 sem | Tech |

### 📚 ANNEXES TECHNIQUES

| Annexe | Description |
|--------|-------------|
| `ANNEXE-Algorithme-Detail.md` | Specs complètes algo v3.0 |
| `ANNEXE-BDD-Schema.md` | Toutes les migrations SQL |
| `ANNEXE-API-Endpoints.md` | 50+ endpoints documentés |
| `ANNEXE-Securite-Conformite.md` | RGPD, sécurité, jeu responsable |
| `ANNEXE-UI-UX-Design.md` | Charte graphique, écrans, dark mode |
| `ANNEXE-Marketing-Strategy.md` | Plan acquisition complet |

---

## 🚦 ORDRE DE LECTURE RECOMMANDÉ

### Pour démarrer le développement (toi, dev solo)

1. **Lire `01-VISION-OBJECTIFS.md`** (5 min) → comprendre le "pourquoi"
2. **Lire `02-BUDGET-ROADMAP.md`** (10 min) → planning concret
3. **Lire `04-RISQUES-ALERTES.md`** (5 min) → ce qu'il faut éviter
4. **Démarrer `SPRINT-01-Setup-Backend.md`** → action !

### Pour partager avec un investisseur/partenaire

1. `01-VISION-OBJECTIFS.md`
2. `02-BUDGET-ROADMAP.md`
3. `ANNEXE-Marketing-Strategy.md`

### Pour présenter à un dev freelance ponctuel

1. `03-STACK-TECHNIQUE.md`
2. Le sprint concerné
3. Les annexes techniques pertinentes

---

## 🔑 PRINCIPES DIRECTEURS DU PROJET

### 1. **Lean First, Scale Later**
Chaque sprint doit produire quelque chose de **fonctionnel et testable**. Pas de feature sans validation.

### 2. **Cash Flow > Perfection**
On lance vite (mois 4), on encaisse, on améliore avec le revenu généré.

### 3. **IA-Assisted Development**
Tout le code est écrit avec l'aide de Claude/Cursor. Les specs sont écrites pour être **lisibles par une IA**.

### 4. **Modularité Stricte**
Chaque module est **indépendant**. On peut skipper, reporter, ou paralléliser.

### 5. **Données = Or**
L'algorithme à 60%+ dépend de la qualité des données. Investissement #1.

### 6. **Mobile-First Africa**
95% des users sont sur mobile. Optimisation 4G + offline = obligatoire.

---

## ⚠️ ALERTES CRITIQUES (À LIRE)

🔴 **Alerte 1 - Budget < 5000€** : Les options "tout ML + multi-sources + tous championnats" demandées initialement coûtent réellement 15-25k€. **Phasage strict** appliqué dans ce document.

🔴 **Alerte 2 - Solo dev** : 14 semaines de dev en solo = ~6h/jour minimum. Si tu travailles à côté, prévoir 6-8 mois au lieu de 4.

🔴 **Alerte 3 - Audit juridique manquant** : Tu as choisi RGPD (Europe) mais ta cible est Afrique. **Investir 300-500€** en consultation juridique locale dès le mois 2.

🔴 **Alerte 4 - Crypto reportée** : Trop complexe juridiquement pour MVP. Prévue Phase 3.

🔴 **Alerte 5 - Marketplace pronostiqueurs** : Reporté Phase 3 (modération + KYC obligatoires).

📖 **Détails complets** : voir `04-RISQUES-ALERTES.md`

---

## 📞 CONTACTS

- **Email Support** : support@mhdservice.com
- **Email Tech** : dev@mhdservice.com
- **Repository** : (à créer) github.com/mhdservice/footapp

---

## 📜 HISTORIQUE VERSIONS

| Version | Date | Changements |
|---------|------|-------------|
| 1.0-3.0 | Oct 2025 | Versions initiales (cahier monolithique) |
| **4.0** | **Oct 2025** | **Refonte modulaire Lean Solo + IA** |

---

*Document confidentiel - Propriété MHD SERVICE*
