# COTA — Script de maquette complet pour Claude Design

> Colle ce script dans Claude Design (claude.ai/design ou l'outil de maquettage interactif).  
> Il génère **toutes les pages** de l'app mobile et du site web, sans dépendance externe.

---

## INSTRUCTIONS POUR CLAUDE DESIGN

Crée une maquette interactive complète pour **COTA**, une app mobile de pronostics football par IA.  
Le canvas doit couvrir **7 sections** dans l'ordre ci-dessous.  
Utilise les tokens de design exacts fournis — pas de couleurs inventées.

---

## TOKENS & TYPOGRAPHIE

```
Couleurs :
  --cota-bg       : #0b0d10   (fond principal)
  --cota-bg-2     : #15181d   (surface élevée)
  --cota-bg-3     : #1a1e25   (surface haute)
  --cota-line     : #1d2026   (séparateur)
  --cota-line-2   : #2a2e36   (séparateur secondaire)
  --cota-ink      : #f4efe2   (texte principal)
  --cota-ink-2    : #c7c4b8   (texte secondaire)
  --cota-dim      : #8b8a85   (texte tertiaire)
  --cota-dim-2    : #5a5d63   (texte quaternaire)
  --cota-accent   : #e8ff36   (jaune signal — live, CTA, validé)
  --cota-win      : #3ddc91   (vert gagné)
  --cota-loss     : #ff5b3a   (rouge perdu)

Fonts Google :
  Archivo Black 900     → titres, wordmark, grands chiffres
  Space Grotesk 500/600/700 → UI général
  JetBrains Mono 500/600/700 → cotes, labels, mono

Règle d'or : le jaune accent est RARE. Réservé aux moments décisifs : live, validé, CTA principal, score ≥ 85%.
```

---

## SECTION 00 — IDENTITÉ DE MARQUE

**Artboard : Identité (720 × 460)**

Carte d'identité avec :
- Logo COTA : icône carrée fond #0b0d10 avec cadre jaune #e8ff36, grande lettre C en Archivo Black blanc, barre underscore jaune sous le C
- Wordmark "COTA" en Archivo Black 36px avec soulignement jaune (4px, décalé de -4px)
- Sous-titre monospace : "APP DE PRONOSTICS FOOT · IA" en #8b8a85 11px tracking 0.18em
- Grille 2 colonnes :
  - Bloc TYPE : "Archivo Black" 22px / "Space Grotesk 500/600/700" 13px / "JetBrains Mono · @1.65" 12px accent
  - Bloc TOKENS : 6 swatches de couleur avec codes hexa en 7px mono
- Échelle icône (16/32/60/96/180px) sur fond sombre

**Artboards icônes (6 variantes × 720 × 560 chacun)**

Pour chaque variante, montrer : échelle de 16→220px + row contextuelle (app settings + notification)

| Variante | Description |
|----------|-------------|
| 01 · SIGNAL | C massif, cadre jaune fin, 3 ticks de confiance en haut, barre terminale |
| 02 · ODDS RAIL | Bracket corners jaunes, cote "2.55" JetBrains en bas, C dominant |
| 03 · COUPON | Découpe ticket avec encoches, tirets jaunes en bas, badge jaune coin |
| 04 · LIVE ORBIT | Anneau partiel jaune autour du C, point jaune coin haut-droit |
| 05 · CONFIANCE | 4 barres montantes en bas (3 blanches + 1 jaune), C en fond |
| 06 · CUTOUT | C en négatif découpé dans une plaque jaune (marketing uniquement) |

**Artboard comparatif (1120 × 420)**  
Les 6 variantes côte à côte en 80px + ligne de lisibilité en 16/24/32px

**Artboards springboard iOS (340 × 560 × 6)**  
Chaque icône insérée dans une grille iOS réaliste (fond dégradé slate, apps voisines, dock)

---

## SECTION 01 — ONBOARDING (3 écrans mobile 402 × 874)

Tous dans un frame iPhone avec Dynamic Island, bords arrondis 54px, anneau de caméra.

### Écran 01 · Hero cinématique
- Fond plein écran : dégradé diagonal #0a3b73 (PSG bleu) → #2faee0 (OM bleu clair)
- Overlay gradient vertical : transparent → #0b0d10 (bottom 60%)
- Grands monogrammes "PSG" + "OM" en Archivo Black 200px, opacity 0.13, débordants
- Bande tickertape à y=78px : fond blur translucide, texte mono 10px dim défilant : "PSG–OM @1.65   ·   LIV–ARS +2.5 @1.78   ·   RMA–BAY BTTS @1.55   ·   COUPON DU JOUR @4.55   ·   87% CONFIANCE"
- En haut : pill "L1 · J34 · CE SOIR 21H" + bouton "PASSER →"
- En bas (padding 50px bottom) :
  - Icône 56px + COTA wordmark 42px avec soulignement
  - Titre "Le foot, lu par une [IA sur fond jaune]." Archivo 30px
  - Description 14px : "9 critères, 1 score de confiance, 1 coupon par jour."
  - Strip 3 stats : "9 / critères IA" · "1 / coupon / jour" · "+18% / ROI saison" — fond glassmorphism
  - Bouton CTA pleine largeur : fond #e8ff36, texte #0b0d10, "COMMENCER GRATUITEMENT →"
  - Dots de pagination : point actif jaune 24px + 2 points gris 8px

### Écran 02 · 9 critères
- Header : "02 — MÉTHODE" mono accent + titre Archivo 36px "9 critères. Chaque match."
- Description 14px en #c7c4b8
- Grille 3×3 de cards (fond #0b0d10, border #2a2e36) :
  - 01 Forme actuelle / 5 derniers
  - 02 Confrontations / h2h 10 ans
  - 03 Dom / Ext / taux W
  - 04 Blessures / titulaires
  - 05 Météo / pluie · vent · T°
  - 06 Cotes marché / consensus
  - 07 Cartons / arbitre
  - 08 Possession / style
  - 09 Buts attendus / xG
- Formule visuelle : "9 critères → analyse IA → **87** %"
- CTA "SUIVANT →" + dots pagination (2e point actif)

### Écran 03 · Rituel du coupon
- Header : "03 — RITUEL" + "Ton coupon, chaque matin **9h30**." (9h30 en accent jaune)
- Carte de notification iOS flottante (glassmorphism, ombre forte) :
  - Icône app 40px + "COTA" 13px bold + "maintenant" dim
  - "3 picks combinés · confiance **87%**" (87 en mono jaune)
  - "Cote @4.55 · PSG · LIV · RMA"
  - Fausse notif en arrière-plan (scalée à 0.94, opacity 0.5)
- 3 bénéfices avec checkmarks ronds jaunes :
  - "Coupon à 9h30 / avant l'ouverture des cotes"
  - "Cote en direct / alerte quand une cote bouge"
  - "Résultat instantané / gagné ou perdu, dès le coup de sifflet"
- Bouton "ACTIVER LES NOTIFS" + lien "PLUS TARD"
- Dots pagination (3e point actif)

---

## SECTION 02 — APP MOBILE (5 écrans 402 × 874)

Tous dans frame iPhone. Bottom nav persistante (4 onglets : Aujourd'hui · Coupon · Historique · Profil).

### Écran 1 · Home — Aujourd'hui
Header app (icône + wordmark + cloche avec badge accent)

Onglets horizontaux mono 11px : **AUJOURD'HUI** (accent + underline) · DEMAIN · SEMAINE · COMPÉTITIONS

**Hero — Coupon du jour**  
Card avec radial-gradient jaune discret coin haut-droit :
- Label "★ COUPON DU JOUR · 9:30" mono accent
- Cote combinée "@4.55" Archivo 30px accent + ring de confiance 64px (87%)
- 3 picks inline : label match | type pari | cote (accent si ≥ 85%)
- CTA "VOIR L'ANALYSE COMPLÈTE →" fond accent pleine largeur

**Section EN LIVE** (scroll horizontal, 2 match hero cards) :
- Card match PSG-OM : dégradé bleu PSG/bleu OM, pill "LIVE · 34'" fond accent texte dark, score "1–0", cote @1.65
- Card match RMA-BAY : dégradé blanc/rouge Bayern, pill "LIVE · 78'", score "2–2"

**Section TOUS LES MATCHES DU JOUR** (scroll horizontal, 3 match row cards 220px) :
- LIV-ARS Premier League — +2.5 buts @1.78 — 76%
- ASM-OL Ligue 1 — Monaco +1.5 @1.42 — 68%
- LIL-NIC Ligue 1 — -2.5 buts @1.92 — 62%

**Section COMPÉTITIONS SUIVIES** (grille de chips) :
- LIGUE 1 · 6 pronos — PREMIER LEAGUE · 5 — UCL · 4 — LIGA · 3 — BUNDESLIGA · 4 — SERIE A · 3
- Chaque chip : fond BG2, barre couleur ligue à gauche, nom + compteur

### Écran 2 · Détail Match — 9 critères
**Hero full-bleed 280px** (dégradé PSG bleu / OM bleu, overlay sombre) :
- Nav : bouton retour 36px glassmorphism + pill "LIGUE 1 · J34" + bouton partage
- Badges équipes 56px avec noms en Archivo 18px
- Centre : heure "21:00" + "VS" dim + "SCORE IA / 2-1" accent

**Onglets collants** : ANALYSE · **9 CRITÈRES** (accent actif) · COTES · H2H

**Card Verdict IA** (radial gradient accent coin) :
- Ring confiance 76px (87%) + label "VERDICT IA"
- "Victoire PSG" Archivo 22px
- Chip cote "@1.65" fond accent + "CONFIANCE FORTE" accent mono

**9 critères listés** (chacun : index 2 chiffres | nom | valeur colorée) :
- 01 Forme (5 derniers) · **4V 1N** [pro/accent]
- 02 Confrontations directes · **6-2-2** [pro/accent]
- 03 Domicile vs Extérieur · **89% V** [pro/accent]
- 04 Blessures clés · **0 vs 2** [pro/accent]
- 05 Météo · Sec, 14° [neutral/white]
- 06 Cotes du marché · 1.65 / 4.20 / 4.50 [neutral]
- 07 Cartons (moy.) · 2.8 [neutral]
- 08 Possession attendue · 64% [pro/accent]
- 09 Buts attendus (xG) · 2.8 – 1.1 [pro/accent]
- Badge résumé : "7 PRO · 2 NEUTRES"

**CTA** : "AJOUTER AU COUPON →" pleine largeur fond accent

### Écran 3 · Coupon du jour
**Header** : "COUPON · MAR 18 MAI" mono accent + "3 picks IA combinés." Archivo 32px

**Hero card** (radial gradient accent) :
- "COTE COMBINÉE" + "@4.55" Archivo 56px accent
- Ring confiance 88px (87%)
- 2 blocs côte à côte : "MISE / 10€" (border line) · "GAIN POSSIBLE / 45.50€" (border accent)

**3 picks détaillés** (chacun = mini hero + info) :
- PSG–OM / Victoire PSG / @1.65 / 87% → chip accent
- LIV–ARS / +2.5 buts / @1.78 / 76%
- RMA–BAY / BTTS Oui / @1.55 / 91% → chip accent

**Multiplicateur** : "1.65 × 1.78 × 1.55 = @4.55"

**2 boutons** : "JOUER CE COUPON →" (fond accent) + "PARTAGER LE COUPON" (border)

### Écran 4 · Notifications
Header : "05 — NOTIFICATIONS" + titre "Activité" Archivo 28px + bouton "TOUT LIRE"

Feed de 7 notifications (badge coloré + titre + sous-titre + timestamp) :
- ★ COUPON (accent) — Coupon du jour disponible — 3 picks · @4.55 · 87% — 09:30 [nouveau]
- ◉ LIVE (accent) — PSG-OM commence dans 30min — Cote PSG 1.72 → 1.65 — il y a 12min [nouveau]
- ✓ GAGNÉ (vert) — Coupon validé : +44.20€ — PSG · LIV · RMA gagnants — Hier 22:48
- ✓ VALIDÉ (vert) — PSG–OM : Victoire PSG 2–1 — Pick @1.65 validé — Hier 21:35
- ◉ LIVE (accent) — PSG–OM : but de Mbappé (62') — 2–1 PSG — Hier 20:50
- ↗ COTE (accent) — Real–Bayern : BTTS passe à @1.55 — Cote favorable — 17 mai 14:00
- ✗ PERDU (rouge) — Coupon perdu : -10€ — OL–Lille : 1–1, +2.5 attendu — 16 mai 23:10

Nouvelles notifs = fond BG2 + point accent 7px coin haut-droit

### Écran 5 · Profil + Stats
Header : "PROFIL" + avatar 60px (fond BG2 border accent, lettre K accent) + "Karim B." + "MEMBRE DEPUIS NOV. 2025"

**3 blocs stats côte à côte** :
- "+18.5%" ROI SAISON (accent)
- "47/59" PICKS GAGNANTS
- "4" STREAK ✓ (accent)

**Graphique performance 30 jours** (sparkline SVG ascendante, trait accent 1.8px, fill gradient accent→transparent) :
- Label "PERFORMANCE — 30 JOURS" + "+184€" accent
- Axe dates : 15 AVR · 1 MAI · 15 MAI

**Répartition par compétition** (barres progress) :
- Ligue 1 : 21/32 (accent)
- Champions League : 11/14
- Premier League : 5/8
- Liga : 3/5

**Réglages** (liste avec chevrons) :
- Notifications / À 9h30
- Compétitions suivies / 6 ligues
- Bookmaker préféré / Aucun
- Mode coupon / Combiné 3 picks

---

## SECTION 03 — ANIMATIONS (3 artboards 600 × 500)

Chaque animation est autonome, en boucle.

### Animation 1 · Splash — Analyse IA
- Fond #0b0d10, centré
- Anneau SVG 200px : cercle fond #1d2026 + arc jaune 80px tournant (360°/s)
- Wordmark COTA 44px centré dans l'anneau
- "ANALYSE EN COURS..." mono 11px dim avec 3 points clignotants décalés
- Coins : 9 critères mono 10px flashant en jaune de manière séquentielle (stagger 0.18s)
- Pied de page : "v1.0.4" · "247 MATCHES SOURCES" · "UTC 09:31"

### Animation 2 · Score de confiance
- Counter 0→87 en ease-out sur 1.8s, en boucle toutes les 4s
- Ring SVG 220px : arc coloré suit la valeur, passe blanc→jaune au lock
- Chiffre Archivo 72px au centre, passe blanc→jaune au lock
- "✓ SCORE DE CONFIANCE" / "CALCUL DU SCORE..." selon état
- Pulse ring expand au moment du lock (scale 0.4→2.2, opacity 0.7→0)
- Pills PSG–OM · VICTOIRE PSG · @1.65 (fond accent au lock)

### Animation 3 · Coupon validé
- 3 picks glissent vers le haut en stagger (easeOutBack, 0.8s entre chacun)
- Multiplicateur "1.65 × 1.78 × 1.55 = @4.55" apparaît après pick 3
- Stamp "✓ COUPON VALIDÉ" explose depuis le centre (fond accent, scale 0.6→1.08→1, ombre jaune)
- Reset invisible → boucle 6s

---

## SECTION 04 — SITE WEB LANDING (1280 × 820)

### Landing page — cota.app

**Nav** (padding 24px 56px, border-bottom) :
- Gauche : icône 32px + wordmark 24px
- Droite : liens mono — MÉTHODE · STATS · PRICING · BLOG + bouton "TÉLÉCHARGER L'APP →" fond accent

**Hero section** (grille 2 colonnes, gap 56px) :
- Colonne gauche :
  - Pill "● 247 MATCHES ANALYSÉS CE WEEK-END" fond accent/10 border accent
  - H1 Archivo 88px "Le foot, lu par une **[IA]**." (IA sur fond accent texte dark)
  - Paragraphe 18px "9 critères, 1 score de confiance..."
  - 2 CTAs : "APP STORE" (fond accent + icône Apple) · "GOOGLE PLAY ↓" (transparent border)
  - Strip stats : "+18.5% ROI" · "72% TAUX RÉUSSITE" · "47k UTILISATEURS"
- Colonne droite (phone mockup 320×580 centré) :
  - Frame iPhone fond BG2, border BG3 8px, shadow forte + glow jaune subtil
  - Miniature écran home : mini hero PSG-OM + verdict IA + 4 critères
  - Floating chip gauche : "COUPON DU JOUR / @4.55" en accent
  - Floating chip droit : "✓ COUPON GAGNÉ / +44.20€" fond accent texte dark
- Radial gradient accent 600px opacity 8% en haut-droit

**Trust bar** (border-top, padding 40px 56px) :
- "ANALYSÉ PAR L'IA POUR" + logos compétitions mono : LIGUE 1 · UCL · PREMIER LEAGUE · LA LIGA · BUNDESLIGA · SERIE A

---

## SECTION 05 — DASHBOARD ADMIN (1280 × 820)

### Dashboard admin

**Sidebar gauche 220px** (fond BG2, border-right) :
- Logo + "COTA / ADMIN"
- Menu items mono 11px (item actif : fond BG3, border-left accent 2px, texte accent) :
  - **VUE D'ENSEMBLE** (actif)
  - MATCHES
  - COUPONS
  - MODÈLE IA
  - UTILISATEURS
  - MONÉTISATION
- Badge statut en bas : "● MODÈLE OK / v1.0.4 · 09:30 UTC"

**Main (margin-left 220px, padding 24px 32px)** :

Top bar :
- "MAR 18 MAI 2026" mono dim + "Vue d'ensemble" Archivo 28px
- Boutons : "EXPORT CSV" (border) · "PUBLIER LE COUPON" (fond accent)

KPIs grille 4 colonnes :
- **247** MATCHES ANALYSÉS (accent)
- **12.4k** COUPONS GÉNÉRÉS · +8% vs hier (vert)
- **72.1%** TAUX SUCCÈS · +1.2pt (vert)
- **€18.2k** GAINS UTILISATEURS · +€2.4k (accent)

Grille 2 colonnes :
- Colonne gauche : Coupon du jour (3 picks PSG-OM/LIV-ARS/RMA-BAY avec confiances et cotes, total @4.55)
- Colonne droite : Modèle IA — 6 critères avec barres de performance (Forme 92%, Confrontations 84%, Dom/Ext 78%, Blessures 71%, Cotes 88%, xG 95%)

---

## SECTION 06 — PAGES WEB COMPLÉMENTAIRES (1280 × 900 chacune)

### Page Méthode
- Hero : "9 critères. Chaque match. Zéro biais." Archivo 72px
- Grille 3×3 des critères avec icônes SVG et descriptions détaillées
- Formule visuelle : flow "données brutes → 9 critères → score IA → prédiction"
- Témoignage fictif avec stats : "72% taux de réussite sur 30 jours glissants"

### Page Pricing
- 2 plans côte à côte :
  - **GRATUIT** (fond BG2, border line) : 2 étoiles max · Pas de coupon · Historique 7j — "COMMENCER"
  - **PREMIUM** (fond BG3, border accent glow) : ⭐⭐⭐⭐ · Coupon IA quotidien · Alertes cotes · Historique complet — "14 JOURS OFFERTS →" (CTA accent)
- Prix : 4 990 FCFA/mois · 49 900 FCFA/an (économie -15%)
- Modes de paiement : Wave · Orange Money · MTN · Moov — logos sous les plans

### Page Bookmakers
- Hero court : "Les meilleures cotes. Par région."
- Carte ou sélecteur région : Afrique de l'Ouest · Europe · Reste du monde
- Grille de cards bookmakers (pour Afrique de l'Ouest) :
  - **1xBet** — cote PSG win 1.65 — badge "RECOMMANDÉ" accent — "OUVRIR LE COMPTE →"
  - **Betwinner** — cote 1.68 — "OBTENIR LE BONUS →"
  - **Melbet** — cote 1.62
- Disclaimer légal mono 10px dim en bas

### Page Historique / Stats publiques
- Titre : "72% de réussite en mai 2026"
- Graphique barres 30 jours (vert = gagné, rouge = perdu, gris = en attente)
- Tableau mensuel : 59 picks · 43 gagnants · 14 perdus · 2 nuls · ROI +18.5%
- Répartition par compétition (barres horizontales)
- Derniers résultats (liste 5 derniers coupons avec résultat)

---

## SECTION 07 — ÉTATS ET MICRO-INTERACTIONS

**Artboard États des picks (800 × 400)** :
- Ligne 1 : état **EN ATTENTE** — card normale, cote blanche
- Ligne 2 : état **GAGNÉ** — flash vert #3ddc91, checkmark, "+16.50€"
- Ligne 3 : état **PERDU** — fond rouge discret #ff5b3a/15, croix, -10€
- Ligne 4 : état **LIVE** — dot pulsant accent, cote en jaune, minute en direct

**Artboard États boutons (600 × 200)** :
- Default → Hover (légère élévation) → Loading (spinner) → Success (✓ vert)

**Artboard Cote live (600 × 300)** :
- Cote qui oscille rapidement → se fige → passe en jaune + "✓ MEILLEURE COTE"
- Transition hausse : blanc → jaune en 200ms
- Transition baisse : blanc → gris en 200ms

---

## NOTES DE TONE

- Pas de gradient flashy, pas d'emoji dans les interfaces
- Le jaune #e8ff36 est précieux — max 1 élément jaune visible par écran (sauf l'accentuation de données)
- Les chiffres de cotes sont TOUJOURS en JetBrains Mono
- Les titres de sections en majuscules avec tracking 0.18em
- Tout texte secondaire en #8b8a85, jamais complètement invisible
- Niveau de confiance ≥ 85% : cote en accent jaune. En dessous : blanc standard
