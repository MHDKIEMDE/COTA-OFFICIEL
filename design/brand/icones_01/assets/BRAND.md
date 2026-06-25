# COTA — Brand handoff

App de pronostics sportifs. "COTA" vient de **cote**.

## Tokens

```css
--cota-bg:       #0b0d10;   /* fond app sombre */
--cota-bg-2:     #15181d;   /* surface élevée / grille */
--cota-ink:      #f4efe2;   /* texte principal */
--cota-ink-dim:  #8b8a85;   /* texte secondaire */
--cota-accent:   #e8ff36;   /* signal jaune — live, validé, CTA */
--cota-win:      #3ddc91;   /* état gagnant */
--cota-loss:     #ff5b3a;   /* état perdu */
```

## Typographie

- **Wordmark / titres** : Archivo Black (900), tracking -0.04em
- **UI** : Space Grotesk (500/600/700)
- **Cotes / chiffres / mono** : JetBrains Mono (500/600/700), tracking 0.05em

Charger via Google Fonts :
```
Archivo:wght@800;900
Space+Grotesk:wght@500;600;700
JetBrains+Mono:wght@500;600;700
```

## Logo — fichiers

- `cota-logo.svg` — wordmark horizontal (header, marketing). Utilise `currentColor` : applique la couleur via CSS.
- `cota-icon.svg` — badge carré (favicon, app icon, avatar).

### Wordmark — règles

- Header app : hauteur 32 px.
- Zone de protection : hauteur d'une lettre tout autour.
- Couleur :
  - Sur fond sombre → `color: #f4efe2` (le soulignement reste jaune).
  - Sur fond clair → `color: #0b0d10`.
- En dessous de 24 px, **retirer le soulignement** (`<line>` + `<rect>` finaux du SVG).
- Jamais sur photo chargée — toujours sur aplat.

### Icône — règles

- Favicon, app icon iOS/Android, avatar, badges in-app.
- Carré, fond intégré (#0b0d10), surface intérieure #141820, cadre jaune.
- Direction master : C massif + 3 ticks de confiance + barre terminale jaune.
- Les ticks représentent la lecture IA/confiance ; éviter d'ajouter étoiles ou ballons dans l'icône app.
- La Todo design est intégrée dans `COTA Icon.html` et disparaît automatiquement quand tous les points sont cochés.

## Animations (à implémenter en code)

Toutes les animations bouclent. Référence visuelle complète : `COTA Logo.html` (ouvrir dans un navigateur).

### 1. Splash / loader (~3.5s loop)

Cercle 180 px, ring jaune 60° tournant à 240°/s autour du wordmark COTA centré.

```
- ring background : stroke #1d2026, 3px
- ring actif      : stroke #e8ff36, 3px, dasharray "60 400", linecap round, rotate
- wordmark        : Archivo 900, 38px, fill #f4efe2
```

### 2. Hero (~6s, à jouer une fois puis figer)

Timeline :
- 0.0–0.2s : tickertape de cotes glisse en haut (boucle à 90 px/s)
- 0.2–0.8s : lettres C, O, T, A tombent staggered (0.14s entre chacune), easeOutBack, depuis y +80px
- 0.5–1.2s : ring jaune fin apparaît autour du O (rotation continue 60°/s)
- 1.0–1.4s : chip "PRONOSTIC · @x.xx" apparaît
- 1.4–2.8s : la cote roule (oscille entre 1.20 et 4.10) puis ralentit et se fige sur **2.55**
- 2.78s    : pulse — ring 240px expand + fade depuis le centre, accent jaune
- 2.85–3.5s : soulignement jaune se trace de gauche à droite, finit avec un carré 12px
- 2.95s    : "✓ VALIDÉ" apparaît en vert (#3ddc91)
- 5.4–6.0s : fade out pour boucle

### 3. Cote en direct (~4s loop)

Pour chaque révélation de cote dans l'app :
- 0.4–2.0s : la valeur oscille rapidement (sin × range)
- 2.0s     : se fige sur la cote réelle, passe en jaune accent + label "✓ MEILLEURE COTE"

## États & feedbacks

- **Pari placé** : pulse jaune accent (réutiliser le pulse du hero).
- **Pari gagné** : flash #3ddc91 + ✓.
- **Pari perdu** : flash #ff5b3a + ✗ (pas de pulse, fade discret).
- **Cote qui change en live** : transition couleur 200ms vers jaune si hausse, vers gris si baisse.

## Tone

Confiant, précis, sportif. Pas de gradient flashy, pas d'emoji. La couleur jaune est rare et précieuse — réservée aux moments décisifs (live, validé, gagnant).
