# Inventaire des pages Blade Admin — COTA

## Structure générale
- **34 fichiers** total (33 pages + 1 layout)
- **12 sections** fonctionnelles
- Toutes héritent de `layouts/app.blade.php`

---

## 🏗️ Socle

### `layouts/app.blade.php`
Sidebar navigation, topbar, tokens CSS (couleurs, typo, composants), structure flex globale, toggle mobile.

---

## 🔐 Auth (1 page)

### `auth/login.blade.php`
Formulaire connexion admin — email + password + CSRF.

---

## 📊 Dashboard (1 page)

### `dashboard.blade.php`
- 4 KPI cards : Utilisateurs, Premium, Win Rate, Revenus/mois
- Graphique inscriptions 7 jours
- Graphique prédictions 7 jours (gagné/perdu/en attente)
- Graphique revenus 30 jours
- Liste 8 dernières prédictions du jour
- Liste 8 derniers abonnements
- Feed activité récente

---

## 🎯 Prédictions (3 pages)

### `predictions/index.blade.php`
Liste toutes les prédictions — filtres date/statut/étoiles, pagination, badges statut (WON/LOST/PENDING), logos équipes.

### `predictions/create.blade.php`
Formulaire création manuelle d'une prédiction.

### `predictions/edit.blade.php`
Formulaire édition d'une prédiction existante.

---

## 📈 Statistiques (2 pages)

### `stats/index.blade.php`
Taux de réussite global, ROI, graphiques 30j/12 mois, breakdown par étoiles / type de pari / compétition.

### `stats/funnel.blade.php`
Funnel de conversion : Visiteur → Inscrit → Premium.

---

## 🏆 Compétitions (3 pages)

### `competitions/index.blade.php`
Liste ligues/compétitions avec tier (1–4), logo, pays, statut actif.

### `competitions/create.blade.php`
Formulaire ajout compétition.

### `competitions/edit.blade.php`
Formulaire édition compétition.

---

## 👥 Utilisateurs (3 pages)

### `users/index.blade.php`
Liste users — filtres premium/actif/date, pagination, badge premium.

### `users/show.blade.php`
Profil complet : abonnements, prédictions consultées, parrainages, activité.

### `users/edit.blade.php`
Édition manuelle : statut premium, date expiration, rôle.

---

## 💳 Abonnements (1 page)

### `subscriptions/index.blade.php`
Liste abonnements — filtres plan/statut/provider, graphique revenus, accord manuel premium.

---

## 🤝 Parrainages (1 page)

### `referrals/index.blade.php`
Liste parrainages, top parrains, paliers récompenses.

---

## 🔗 Affiliations (1 page)

### `affiliates/index.blade.php`
Liens affiliés bookmakers — clics, conversions, primes générées.

---

## 💬 Feedbacks (2 pages)

### `feedbacks/index.blade.php`
Liste feedbacks — filtres statut (ouvert/répondu/fermé), badge compteur.

### `feedbacks/show.blade.php`
Détail feedback + zone réponse admin + changement statut.

---

## 🏪 Bookmakers (3 pages)

### `bookmakers/index.blade.php`
Liste bookmakers actifs par région/continent.

### `bookmakers/create.blade.php`
Ajout bookmaker — nom, logo, lien affilié, région.

### `bookmakers/edit.blade.php`
Édition bookmaker.

---

## 📝 Articles bookmakers (3 pages)

### `bookmaker_blogs/index.blade.php`
Liste articles SEO liés aux bookmakers.

### `bookmaker_blogs/create.blade.php`
Rédaction article — titre, contenu, bookmaker associé.

### `bookmaker_blogs/edit.blade.php`
Édition article.

---

## 🔍 Candidats bookmakers (2 pages)

### `bookmaker_candidates/index.blade.php`
Liste candidatures en attente — badge compteur dans sidebar.

### `bookmaker_candidates/show.blade.php`
Détail candidature + boutons valider/rejeter.

---

## 🎟️ Coupon IA (1 page)

### `coupon/index.blade.php`
Coupon combiné du jour — 4–5 picks, cote totale, gain potentiel 1000 FCFA.

---

## 📰 Actualités (4 pages)

### `news_sources/index.blade.php`
Liste sources d'actualités configurées + statut actif.

### `news_sources/create.blade.php`
Ajout source — URL, nom, fréquence.

### `news_sources/edit.blade.php`
Édition source.

### `news_sources/articles.blade.php`
Articles importés depuis une source — titre, date, lien.

---

## 📡 Monitoring API (1 page)

### `api_monitor/index.blade.php`
Quota API-Football restant, statut Redis, latences endpoints, alertes.

---

## ⚙️ Paramètres (1 page)

### `settings/index.blade.php`
Clés API (Football, Paydunya, Weather), seuils algorithme, mode maintenance, config notifications.

---

## Résumé

| Section           | Pages |
|-------------------|-------|
| Socle layout      | 1     |
| Auth              | 1     |
| Dashboard         | 1     |
| Prédictions       | 3     |
| Statistiques      | 2     |
| Compétitions      | 3     |
| Utilisateurs      | 3     |
| Abonnements       | 1     |
| Parrainages       | 1     |
| Affiliations      | 1     |
| Feedbacks         | 2     |
| Bookmakers        | 3     |
| Articles          | 3     |
| Candidats         | 2     |
| Coupon IA         | 1     |
| Actualités        | 4     |
| Monitoring        | 1     |
| Paramètres        | 1     |
| **Total**         | **34**|
