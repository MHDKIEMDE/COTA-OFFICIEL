# COTA — ToDo Design System & Brand
> Session dédiée. Travailler étape par étape, valider chaque point avant de passer au suivant.
> Référence brand : `design/brand/icones_01/assets/BRAND.md`
> Tokens : BG `#0b0d10` · BG2 `#15181d` · INK `#f4efe2` · ACCENT `#e8ff36` · WIN `#3ddc91` · LOSS `#ff5b3a`

---

## BLOC A — Icône app (nouveau logo)

- [x] **A1** Remplacer `mobile/asset/brand/cota-icon.svg` par le fichier `design/brand/icones_01/assets/cota-icon.svg`
  - C massif centré, fond `#0b0d10`, surface `#141820`, cadre jaune, barre terminale jaune
- [x] **A2** PNG 1024×1024 généré via `npx svgexport` → `mobile/asset/brand/app_icon.png`
- [x] **A3** `pubspec.yaml` mis à jour — `flutter_launcher_icons: ^0.14.3` + config `remove_alpha_ios: true`
- [x] **A4** `dart run flutter_launcher_icons` — icônes Android (mdpi→xxxhdpi) + iOS régénérées
- [x] **A5** Mettre à jour `web/favicon.svg` et `web/favicon-animated.svg`
- [ ] **A6** Vérifier visuellement l'icône sur l'écran d'accueil du téléphone (à faire sur device)

---

## BLOC B — Animations Flutter (3 animations brand)

### B1 · Splash / Loader
Fichier : `mobile/lib/shared/widgets/cota_splash_animation.dart`

- [x] Ring 200×200 tournant (stroke `#e8ff36`, 1.5s loop, `AnimationController`)
- [x] Fond ring background : stroke `#2a2e36`, 3px
- [x] Wordmark COTA centré (fontWeight w900, 44px, `#f4efe2`)
- [x] 9 critères staggered (delay 0.18s/item, flash fond jaune `rgba(232,255,54,0.10)`)
- [x] Texte `ANALYSE EN COURS` + 3 points pulsants
- [x] Ligne de télémétrie bas : `v1.0.0 · 247 MATCHES SOURCES · UTC hh:mm`
- [x] Intégré sur `OnboardingScreen` — remplace `CotaSplashLoader`, délai 3.5s

### B2 · Confidence Reveal
Fichier : `mobile/lib/shared/widgets/confidence_reveal_widget.dart`

- [x] Ring r=90 stroke=6, remplit de 0 → score en ease-out cubic 1.8s
- [x] Valeur numérique qui roule synchronisée (0 → target)
- [x] Au lock : ring + chiffre passent `#e8ff36` + pulse expand (scale 2.2, opacity 0.7→0)
- [x] Label bascule `ANALYSE DES 9 CRITÈRES` → `CONFIANCE FORTE`
- [x] Intégré sur `PredictionDetailScreen` — remplace `_AnimatedProgressBar` + label

### B3 · Coupon Validé
Fichier : `mobile/lib/shared/widgets/coupon_validated_overlay.dart`

- [x] 3 picks slide-in staggered (+0ms, +1000ms, +1800ms) depuis `translateY`, easeOutBack
- [x] Cote totale apparaît après les picks
- [x] Stamp `COUPON VALIDÉ` overlay jaune : scale(0.6→1) easeOutBack + box-shadow jaune
- [x] Helper `showCouponValidatedOverlay()` pour usage depuis CouponScreen
- [x] Intégré sur `CouponScreen` — `_showValidatedOverlay()` appelé dans `_initCoupon()` au 1er chargement

---

## BLOC C — Cohérence boutons & couleurs (audit complet Flutter)

> Règle unique : 1 seul style par type de bouton, partout dans l'app.
> Taille : hauteur 48px (primaire), 40px (secondaire), 36px (tertiaire/icône)
> Radius : 10px uniforme partout

### C1 · Bouton Primaire (CTA principal)
- [x] Fond `#e8ff36`, texte `#0b0d10`, fontWeight 800, fontSize 14, letterSpacing 0.08em
- [x] Height 48px, radius 10px, width: double.infinity
- [x] Widget partagé `cota_button.dart` créé (5 variants : Primary, Secondary, Destructive, Text, IconBtn)
- [x] Corrigé : `feedback_screen.dart` — ElevatedButton gradient → yellow solid
- [x] Corrigé : `subscription_screen.dart` — height 56→48, radius 12→10
- [x] Corrigé : `auth/login_screen.dart` — height 56→48, radius 4→10
- [x] Corrigé : `auth/signup_screen.dart` — height 56→48, radius 4→10
- [x] Corrigé : `auth/otp_screen.dart` — gradient → yellow solid, height 54→48
- [x] Corrigé : `referral_screen.dart` — "Réessayer" radius 6→10, "Appliquer" radius 8→10

### C2 · Bouton Secondaire (outline)
- [x] Fond transparent, border 1px `#2a2e36`, texte `#f4efe2`, fontWeight 600, fontSize 13
- [x] Height 40px, radius 10px (défini dans `cota_button.dart`)
- [x] Corrigé : `login_screen.dart` — "Créer un compte" height 52→40

### C3 · Bouton Destructeur (logout, supprimer)
- [x] Fond `rgba(255,91,58,0.10)`, border `rgba(255,91,58,0.3)`, texte `#ff5b3a`
- [x] Height 48px, radius 10px (défini dans `cota_button.dart`)
- [x] `profile_screen.dart` logout — déjà correct

### C4 · Bouton Texte / Lien
- [x] Couleur `#e8ff36`, fontWeight 600, pas de fond (défini dans `cota_button.dart`)

### C5 · Icônes boutons (header, back, actions)
- [x] Container 36×36, radius 8, fond `#15181d`, border `#1d2026` (défini dans `cota_button.dart`)
- [x] Icône 18px, couleur `#f4efe2`

### Couleur brand globale
- [x] `#F9FF00` → `#E8FF36` remplacé dans **18 occurrences / 9 fichiers** (global sed)

---

## BLOC D — Page d'erreur / 404 Flutter

- [x] Fichier créé : `mobile/lib/shared/widgets/error_screen.dart`
- [x] Fond `#0b0d10`
- [x] Icône custom `CustomPainter` (signal brisé / ✗), couleur `#ff5b3a`, taille 64px
- [x] Titre précis selon type : `ROUTE INTROUVABLE` / `CONNEXION PERDUE` / `ERREUR SERVEUR`
- [x] Sous-titre lisible (pas l'URL brute)
- [x] Bouton primaire `RETOUR À L'ACCUEIL` → `context.go('/')`
- [x] Code erreur en monospace bas : `#8b8a85`, fontSize 10
- [x] `errorBuilder` dans `app_router.dart` branché sur `ErrorScreen`
- [x] `ApiErrorWidget` créé (inline, pour listes / empty states)
- [x] `bookmaker_screen.dart` — `_ErrorState` branché sur `ApiErrorWidget`

---

## BLOC E — Page d'erreur Laravel (web)

Dossier à créer : `backend/resources/views/errors/`

- [x] **`404.blade.php`** — Page introuvable
- [x] **`403.blade.php`** — Accès refusé
- [x] **`500.blade.php`** — Erreur serveur
- [x] **`419.blade.php`** — Session expirée
- [x] Style commun `layout-error.blade.php` partagé (fond sombre, wordmark COTA, no nav)

---

## BLOC F — Cache Laravel (audit complet)

> Règle : tout endpoint GET appelé fréquemment doit avoir un cache Redis.
> TTL standard : public/statique 1h, user-specific 5min, live 30s

- [x] **`BookmakerController.php`** — `index()` : cache 1h clé `bookmakers:list:{region}`
- [x] **`BookmakerBlogController.php`** — `show()` : cache 24h clé `bookmaker:blog:{id}`
- [x] **`MatchController.php`** — `byDate()` : cache 5min clé `matches:today:{date}`
- [x] **`TeamController.php`** — `show()` : cache 6h clé `team:{id}`
- [x] **`FavoriteController.php`** — `index()` : cache 2min clé `favorites:user:{userId}:{type?}`
- [x] **`ConfigController.php`** — `getAppConfig()` : cache 10min clé `app:config`
- [x] `Cache::forget()` ajoutés dans `FavoriteController` — `store()`, `destroy()`, `destroyByItem()`
- [x] Commande artisan `cache:warm` créée (`BookmakerController`, `BookmakerBlogController`, `ConfigController`)

---

## PRIORITÉ D'EXÉCUTION

```
A (icône) → C (boutons, 1 session) → D (404 Flutter) → E (404 Laravel) → F (cache) → B (animations)
```

Les animations (B) sont en dernier car elles nécessitent les assets finalisés.
