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
| 3.1 | Header | Shell injecte `CotaAppHeader` — pas de doublon | ✅ |
| 3.2 | Pills date | CAPS "DEMAIN →" → "Voir demain →" SpaceGrotesk w700 | ✅ |
| 3.3 | Section "En direct" | `_LiveBadge` sobre — badge count EN DIRECT | ✅ |
| 3.4 | Liste matchs du jour | `_PredictionTile` sobre : heure + équipes + stars + pick badge + cote bordered | ✅ |
| 3.5 | Card coupon | Banner `bg2` + cote mono accent + bouton "Voir" fond accent | ✅ |
| 3.6 | Bottom nav | Shell injecte `CotaBottomNav` V6 | ✅ |
| 3.7 | Supprimer | Emoji 📡 → `CotaEmptyState` avec `Icons.wifi_off_rounded` | ✅ |

**Quality gate sprint 3 :**
```bash
flutter analyze   # 0 issue
# Vérifier visuellement : section live, liste matchs, card coupon, bottom nav
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/home_screen.dart && git commit -m "design: Sprint 3 — Home V6 (poster hero, match rows, coupon card sobre)"`

---

## SPRINT 4 — Détail match V6 ✅ pushé 2026-05-30
> Objectif : refondre `prediction_detail_screen.dart` de grille stats → prose narrative.
> Fichier : `mobile/lib/features/predictions/presentation/screens/prediction_detail_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 4.1 | Hero poster | `CotaMatchPoster` height 320px plein écran + badges (compétition, LIVE, score/heure, forme) | ✅ |
| 4.2 | Onglets | 7 onglets underline accent — RÉSUMÉ / STATS / H2H / COMPOS / CLASSEMENT / HIGHLIGHTS / ACTUALITÉS | ✅ |
| 4.3 | La sélection | Archivo Black 28px + `CotaOddsChip` prominent + `CotaConfidenceBarAnimated` + étoiles | ✅ |
| 4.4 | Critères liste | `borderTop` fin sur chaque item — label gris SpaceGrotesk / valeur JetBrainsMono colorée | ✅ |
| 4.5 | CTA | Bouton "Ajouter au coupon" full-width 52px fond accent (toggle si déjà ajouté) | ✅ |
| 4.6 | Supprimer | `_GlobalScoreBadge` (ring), `_AnimatedCriterionRow` (barres), `ConfidenceRevealWidget` | ✅ |

**Quality gate sprint 4 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/prediction_detail_screen.dart && git commit -m "design: Sprint 4 — Détail match V6 (poster plein écran, prose narrative, critères liste)"`

---

## SPRINT 5 — Coupon V6 ✅ pushé 2026-05-25
> Fichier : `mobile/lib/features/predictions/presentation/screens/coupon_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 5.1 | Header | SliverAppBar "Coupon IA" + bouton "Générer" accent | ✅ |
| 5.2 | Titre | Variante selector Prudent/Équilibré/Audacieux | ✅ |
| 5.3 | Cards KPI | `_CouponOddsReveal` ring animé cote + picks + mise/gain | ✅ |
| 5.4 | Liste picks | `_PickCard` : numéro + match + stars + `CotaOddsChip` bordered/prominent | ✅ |
| 5.5 | Gain possible | Card mise FCFA + gain JetBrainsMono accent | ✅ |
| 5.6 | CTAs | "Valider →" Mon coupon + "Générer" header | ✅ |
| 5.7 | Supprimer | Badges flashy retirés, OddsChip prominent uniquement sur ≥4 étoiles | ✅ |

**Quality gate sprint 5 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/predictions/presentation/screens/coupon_screen.dart && git commit -m "design: Sprint 5 — Coupon V6 (KPI cards, picks avec mini poster, gain sobre)"`

---

## SPRINT 6 — Profil V6 ✅ pushé 2026-06-02
> Fichier : `mobile/lib/features/profile/presentation/screens/profile_screen.dart`

| # | Section | Changement V6 | État |
|---|---|---|---|
| 6.1 | En-tête profil | Avatar initiale + nom Archivo Black 22px + "Membre depuis..." en gris | ✅ |
| 6.2 | Stats 3 colonnes | ROI / Picks gagnants / Streak — Archivo Black 18px + label 10px, borderRadius cards | ✅ |
| 6.3 | Sparkline 30j | SparklinePainter gradient fill accent + gain total en vert | ✅ |
| 6.4 | Réglages | Toggles Switch pour Notifications et Abonnement, chevrons 16×16 pour le reste | ✅ |
| 6.5 | Chevrons | Chevrons `CustomPaint` 16×16 (min tapable) | ✅ |
| 6.6 | Supprimer | Rings, orbs, décorations excessives retirés | ✅ |

**Quality gate sprint 6 :**
```bash
flutter analyze   # 0 issue
```
**Push :** `git add mobile/lib/features/profile/ && git commit -m "design: Sprint 6 — Profil V6 (stats 3 col, sparkline, réglages avec toggles)"`

---

## SPRINT 7 — Historique + Notifications + Bookmakers V6 ✅ pushé 2026-05-25
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

## SPRINT 8 — Admin Web (Dashboard Blade/Tailwind) ✅ pushé 2026-05-25
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

---

## SPRINT 11 — Admin Web UX (navigation + pages manquantes) ✅ Terminé
> Objectif : corriger la navigation, ajouter les pages absentes, améliorer le dashboard.
> Dossier : `backend/resources/views/admin/`

| # | Item | Changement | État |
|---|---|---|---|
| 11.1 | Sidebar restructurée | 4 groupes (Pronostics / Utilisateurs / Bookmakers / Système), route cassée `admin.bookmakers.index` corrigée | ✅ pushé 2026-05-25 |
| 11.2 | Blogs bookmakers | Lien ajouté dans sidebar — `admin.admin.bookmaker-blogs.index` | ✅ pushé 2026-05-25 |
| 11.3 | Candidats bookmakers | Lien + badge compteur `pending` ajouté dans sidebar | ✅ pushé 2026-05-25 |
| 11.4 | Dashboard — activité temps réel | Dernières prédictions + derniers abonnements en bas de page | ✅ |
| 11.5 | Page Coupon du jour admin | Voir / valider le coupon IA généré + historique coupons | ✅ |
| 11.6 | Page Candidats bookmakers | Approuver / rejeter les bookmakers auto-découverts | ✅ |

**Quality gate sprint 11 :**
```bash
php artisan test   # 27 passent, 1 skip — 2026-05-30
```

---

## AMÉLIORATION UX Profil mobile — 2026-05-25
> Améliorations ciblées sur `profile_screen.dart` suite à analyse cible jeune.

| # | Item | Changement | État |
|---|---|---|---|
| P-01 | Menu réduit | 16 items → 5 visibles + "Plus d'options" collapsable animé | ✅ pushé 2026-05-25 |
| P-02 | Fausse data supprimée | Bloc "Répartition par compétition" retiré | ✅ pushé 2026-05-25 |
| P-03 | Devise corrigée | `+184€` → `+184 000 FCFA` | ✅ pushé 2026-05-25 |
| P-04 | Streak mis en avant | Fond accent + label "En feu !" si streak ≥ 3 | ✅ pushé 2026-05-25 |
| P-05 | Copy premium FOMO | "Tu rates des picks aujourd'hui. 3 picks Premium cachés ce soir." + prix ancré FCFA | ✅ pushé 2026-05-25 |

---

## SPRINTS SUIVANTS (backlog)

| # | Item | Sprint cible |
|---|---|---|
| B-01 | Live screen V6 (cards DAZN, filtre Tous / Mes prédictions) | ✅ Sprint 9 |
| B-02 | Détail match live (onglets STREAM / HIGHLIGHTS / CLASSEMENT) | ✅ Sprint 9 |
| B-03 | Écran premium V6 (paywall sobre, pas de gradients excessifs) | ✅ Sprint 10 |
| B-04 | Splash animation — fontFamily SpaceGrotesk ajouté, label sobre | ✅ Sprint 10 |
| B-05 | Confidence reveal — ring supprimé → CotaConfidenceBarAnimated | ✅ Sprint 10 |
| B-06 | Coupon validé overlay — conforme V6 (badges courts tolérés) | ✅ Sprint 10 |
| B-07 | Icône app — vérification visuelle sur device (pas de code) | — |
| B-08 | Admin — Dashboard activité temps réel | Sprint 11 |
| B-09 | Admin — Page Coupon du jour | Sprint 11 |
| B-10 | Admin — Page Candidats bookmakers | Sprint 11 |

---

## ÉTAT GLOBAL

| Sprint | Contenu | État |
|---|---|---|
| Sprint 1 | Primitives V6 | ✅ pushé 2026-05-25 |
| Sprint 2 | Onboarding V6 | ✅ pushé 2026-05-25 |
| Sprint 3 | Home V6 | ✅ pushé 2026-05-26 |
| Sprint 4 | Détail match V6 | ✅ pushé 2026-05-25 |
| Sprint 5 | Coupon V6 | ✅ pushé 2026-05-25 |
| Sprint 6 | Profil V6 + améliorations UX jeune | ✅ pushé 2026-05-25 |
| Sprint 7 | Historique / Notifs / Bookmakers V6 | ✅ pushé 2026-05-25 |
| Sprint 8 | Admin web (fondations) | ✅ pushé 2026-05-25 |
| Sprint 9 | Live V6 (live_screen + live_match_detail) | ✅ pushé 2026-05-25 |
| Sprint 10 | Premium paywall, splash, confidence reveal, coupon overlay | ✅ pushé 2026-05-25 |
| Sprint 11 | Admin web UX (navigation + pages manquantes) | ✅ pushé 2026-05-30 |
