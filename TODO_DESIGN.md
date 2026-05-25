# COTA — TODO Design · Mobile V6 + Admin Web
> Créé : 2026-05-25 · Mis à jour : 2026-05-25 · Sprint 1 ✅ pushé (6843d84)
> Règle : push + mise à jour de ce fichier à la fin de chaque sprint

## LÉGENDE
- ✅ Terminé + pushé
- 🔧 En cours
- ❌ À faire
- 🔴 Bloquant
- 🟡 Important
- 🟢 Nice-to-have

---

## RÈGLES V6 — à respecter dans chaque fichier

1. JetBrains Mono **uniquement** pour les cotes (`@1.65`), scores (`2-1`) et timestamps (`21:00`)
2. **Archivo Black** pour les titres, noms d'équipes, valeurs fortes
3. **Space Grotesk** (ou system-ui) pour tout le reste (labels, body, boutons)
4. Pas de préfixes `01 —` / `02 —` dans les sections
5. Pas de `ConfidenceRing` → remplacer par barre fine horizontale (`CotaConfidenceBar`)
6. Pas de CAPS + letterSpacing > 0.05 sauf badges très courts (LIVE, WIN)
7. `MatchPosterCard` (dégradé couleurs-clubs diagonal) = élément hero, pas les stats
8. Une seule pulse live (`CotaLiveDot`) — sur le match en direct uniquement
9. `OddsChip` fond jaune = 1 seul usage par écran max (CTA principal)
10. 4 états obligatoires partout : **loading / error / empty / success**

---

## SPRINT 1 — Primitives V6 (fondations) ✅ pushé 2026-05-25
> Objectif : créer les widgets de base V6 réutilisables dans tous les sprints suivants.
> Fichiers : `mobile/lib/shared/widgets/`

| # | Widget | Fichier | Description | État |
|---|---|---|---|---|
| 1.1 | `CotaMatchPoster` | `cota_match_poster.dart` | Dégradé diagonal couleurs-clubs + monogramme fantôme 10% opacité + overlay sombre + slot `child` | ✅ |
| 1.2 | `CotaConfidenceBar` | `cota_confidence_bar.dart` | Barre fine 3px horizontale + label "Confiance" + valeur % — remplace ConfidenceRing | ✅ |
| 1.3 | `CotaOddsChip` | `cota_odds_chip.dart` | 2 variants : `prominent` (fond `#e8ff36`) et `bordered` (outline fin). JetBrains Mono pour la valeur | ✅ |
| 1.4 | `CotaLiveDot` | (dans `cota_live_dot.dart`) | Point 6px + label "LIVE" — animation pulse uniquement ici, nulle part ailleurs | ✅ |
| 1.5 | `CotaAppHeader` | `cota_app_header.dart` | Logo + Wordmark à gauche, slot action à droite. Fond `#0b0d10`, pas d'elevation | ✅ |
| 1.6 | Refonte `CotaBottomNav` | `cota_bottom_nav.dart` | 4 onglets SVG inline : Accueil / Coupon / Historique / Profil. Supprimer FAB coupon centré et onglet Bonus. SVG paths propres (pas Material icons) | ✅ |
| 1.7 | `CotaSectionTitle` | `cota_section_title.dart` | Titre `Archivo Black` 20px + optional `CotaLiveDot` aligné à droite | ✅ |

**Quality gate sprint 1 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/shared/widgets/ && git commit -m "design: Sprint 1 — primitives V6 (poster, confidence bar, odds chip, live dot, header, bottom nav)"`

---

## SPRINT 2 — Onboarding V6 ✅ pushé 2026-05-25
> Objectif : réécrire l'onboarding complet dans la grammaire V6.
> Fichier existant : `mobile/lib/features/auth/presentation/screens/onboarding_screen.dart`
> Stratégie : garder la logique (PageController, splash, timer) — réécrire uniquement le visuel de chaque slide.

| # | Slide | Contenu V6 | État |
|---|---|---|---|
| 2.1 | Hero | Poster plein écran + monogrammes fantômes + headline 38px + CTA sobre | ✅ |
| 2.2 | 9 critères | Ticker supprimé — liste fine borderTop prose narrative | ✅ |
| 2.3 | Rituel 9h30 | Préfixe supprimé — aperçu notification + liste bénéfices sobres | ✅ |
| 2.4–2.8 | Ligues / Risque / OTP / Bookmaker | Non inclus dans l'onboarding actuel (3 slides fonctionnelles) | — |

**Quality gate sprint 2 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/auth/ && git commit -m "design: Sprint 2 — onboarding V6 (8 slides grammaire poster/prose)"`

---

## SPRINT 3 — Home V6 ❌
> Objectif : refondre `home_screen.dart` selon la grammaire V6.
> Fichier : `mobile/lib/features/predictions/presentation/screens/home_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 3.1 | Header | Remplacer par `CotaAppHeader` avec bouton notif en slot right | ❌ |
| 3.2 | Pills date | "Aujourd'hui / Demain / Semaine" — underline accent `#e8ff36`, pas de CAPS | ❌ |
| 3.3 | Section "En direct" | `CotaSectionTitle` + `CotaLiveDot` — `CotaMatchPoster` height 220px avec score + minute + pick type | ❌ |
| 3.4 | Liste matchs du jour | Remplacer cards actuelles par `V6MatchRow` : grid 3 colonnes (miniature poster 56px / infos / odds chip bordered) | ❌ |
| 3.5 | Card coupon | Card sobre `bg2` + `CotaConfidenceBar` + cote mono + bouton "Ouvrir" | ❌ |
| 3.6 | Bottom nav | Passer à `CotaBottomNav` V6 (4 onglets, pas de FAB) | ❌ |
| 3.7 | Supprimer | Retirer ConfidenceRing, tickers, préfixes `01 —`, badges flashy multiples | ❌ |

**Quality gate sprint 3 :**
```bash
flutter analyze   # 0 issue
# Vérifier visuellement : section live, liste matchs, card coupon, bottom nav
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/home_screen.dart && git commit -m "design: Sprint 3 — Home V6 (poster hero, match rows, coupon card sobre)"`

---

## SPRINT 4 — Détail match V6 ❌
> Objectif : refondre `prediction_detail_screen.dart` de grille stats → prose narrative.
> Fichier : `mobile/lib/features/predictions/presentation/screens/prediction_detail_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 4.1 | Hero poster | `CotaMatchPoster` height 320px plein écran + badges (compétition, back, share) | ❌ |
| 4.2 | Onglets | "Analyse / Statistiques / H2H / Cotes" — underline `#e8ff36`, pas de ring | ❌ |
| 4.3 | La sélection | Titre Archivo Black 28px + `CotaOddsChip` prominent + `CotaConfidenceBar` | ❌ |
| 4.4 | Prose narrative | 3 paragraphes "Pourquoi [équipe]" — fontSize 14, lineHeight 1.6, couleur `ink2` | ❌ |
| 4.5 | Liste critères | `<ul>` about avec `borderTop` fin sur chaque item — label gris / valeur blanc | ❌ |
| 4.6 | CTA | Bouton "Ajouter au coupon" full-width 52px fond `#e8ff36` | ❌ |
| 4.7 | Supprimer | Retirer grille SQL, ConfidenceRing, tickers, préfixes numérotés | ❌ |

**Quality gate sprint 4 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/prediction_detail_screen.dart && git commit -m "design: Sprint 4 — Détail match V6 (poster plein écran, prose narrative, critères liste)"`

---

## SPRINT 5 — Coupon V6 ❌
> Fichier : `mobile/lib/features/predictions/presentation/screens/coupon_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 5.1 | Header | `CotaAppHeader` + date coupon en label gris | ❌ |
| 5.2 | Titre | Archivo Black 32px "3 picks combinés." | ❌ |
| 5.3 | Cards KPI | 2 cards `bg2` côte-à-côte : "Cote combinée" (mono 30px accent) / "Confiance" (`CotaConfidenceBar`) | ❌ |
| 5.4 | Liste picks | Chaque pick = mini `CotaMatchPoster` 70px + row pick type + `CotaOddsChip` bordered (sauf pick ≥ 85% → prominent) | ❌ |
| 5.5 | Gain possible | Card sobre : mise + gain calculé en mono | ❌ |
| 5.6 | CTAs | "Jouer ce coupon" (fond accent) + "Partager" (outline) | ❌ |
| 5.7 | Supprimer | Retirer badges flashy multiples, OddsChip prominent sur chaque row | ❌ |

**Quality gate sprint 5 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/coupon_screen.dart && git commit -m "design: Sprint 5 — Coupon V6 (KPI cards, picks avec mini poster, gain sobre)"`

---

## SPRINT 6 — Profil V6 ❌
> Fichier : `mobile/lib/features/profile/presentation/screens/profile_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 6.1 | En-tête profil | Avatar initiale + nom Archivo Black 22px + "Membre depuis..." en gris | ❌ |
| 6.2 | Stats 3 colonnes | ROI / Picks gagnants / Série — Archivo Black 22px + label 11px, séparateurs `borderRight` fin | ❌ |
| 6.3 | Sparkline 30j | SVG sparkline avec gradient fill accent + gain total en vert | ❌ |
| 6.4 | Réglages | Liste items `borderTop` fin — label 14px + valeur 13px dim + chevron 16×16 SVG — **ajouter toggles** pour Notifications et Abonnement | ❌ |
| 6.5 | Chevrons | Remplacer chevrons 9×9 par 16×16 — taille minimum tapable | ❌ |
| 6.6 | Supprimer | Retirer rings, orbs, décorations excessives | ❌ |

**Quality gate sprint 6 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/profile/ && git commit -m "design: Sprint 6 — Profil V6 (stats 3 col, sparkline, réglages avec toggles)"`

---

## SPRINT 7 — Historique + Notifications + Bookmakers V6 ❌
> Fichiers : `history_screen.dart`, `notifications_screen.dart`, `bookmaker_screen.dart`

| # | Écran | Changement V6 | État |
|---|---|---|---|
| 7.1 | Historique | Rows sobres avec mini poster 40px + résultat WIN/LOSS badge + cote mono | ✅ |
| 7.2 | Notifications | Liste chronologique — icône pick / résultat + texte prose, pas de badges flashy | ✅ |
| 7.3 | Bookmakers | Cards régions sobres — logo + nom + CTA "Ouvrir" outline | ✅ |

**Quality gate sprint 7 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/history_screen.dart mobile/lib/features/notifications/ mobile/lib/features/bookmaker/ && git commit -m "design: Sprint 7 — Historique, Notifications, Bookmakers V6"`

---

## SPRINT 8 — Admin Web (Dashboard Blade/Tailwind) ❌
> Objectif : réconcilier les maquettes V5 avec le vrai contenu admin COTA.
> Dossier : `backend/resources/views/admin/`

| # | Page admin | Changement | État |
|---|---|---|---|
| 8.1 | Sidebar | Font Awesome icons cohérents (fa-solid) — pas d'Unicode | ✅ |
| 8.2 | Dashboard KPIs | Branché sur vrais stats : users, premium, win rate, revenus Paydunya | ✅ |
| 8.3 | Barres performance | Graphique revenus 30j : vert (FCFA) + ligne accent (abonnements) | ✅ |
| 8.4 | Table principale | Table prédictions avec statut won/lost/pending (predictions/index) | ✅ |
| 8.5 | Favicon web | `public/favicon.svg` créé + `<link>` dans layout | ✅ |
| 8.6 | Pages erreur | 404 / 403 / 500 / 419 — design V6 déjà en place | ✅ |

**Quality gate sprint 8 :**
```bash
php artisan test   # 0 régression
# Vérifier dashboard dans navigateur
```
**Push :** `git add backend/resources/views/admin/ backend/public/favicon* && git commit -m "design: Sprint 8 — Admin web (sidebar SVG, KPIs réels, table prédictions, favicon)"`

---

## SPRINTS SUIVANTS (backlog)

| # | Item | Sprint cible |
|---|---|---|
| B-01 | Live screen V6 (cards DAZN, filtre Tous / Mes prédictions) | ✅ Sprint 9 |
| B-02 | Détail match live (onglets STREAM / HIGHLIGHTS / CLASSEMENT) | ✅ Sprint 9 |
| B-03 | Écran premium V6 (paywall sobre, pas de gradients excessifs) | Sprint 10 |
| B-04 | Splash animation — vérification visuelle sur device | Sprint 10 |
| B-05 | Confidence reveal — vérification visuelle sur device | Sprint 10 |
| B-06 | Coupon validé overlay — vérification visuelle sur device | Sprint 10 |
| B-07 | Icône app — vérification sur écran d'accueil device | Sprint 10 |

---

## ÉTAT GLOBAL

| Sprint | Contenu | État |
|---|---|---|
| Sprint 1 | Primitives V6 | ✅ pushé 2026-05-25 |
| Sprint 2 | Onboarding V6 | ✅ pushé 2026-05-25 |
| Sprint 3 | Home V6 | ❌ |
| Sprint 4 | Détail match V6 | ❌ |
| Sprint 5 | Coupon V6 | ❌ |
| Sprint 6 | Profil V6 | ❌ |
| Sprint 7 | Historique / Notifs / Bookmakers V6 | ✅ pushé 2026-05-25 |
| Sprint 8 | Admin web | ✅ pushé 2026-05-25 |
| Sprint 9 | Live V6 (live_screen + live_match_detail) | ✅ pushé 2026-05-25 |
