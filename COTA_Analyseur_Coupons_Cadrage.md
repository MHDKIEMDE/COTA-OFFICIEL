# COTA — Analyseur Intelligent de Coupons

**Note de cadrage — logique d'analyse & mémoire d'apprentissage**
*MHD SERVICE · Fonctionnalité Premium · Document de travail*

---

## 1. Concept & objectif

L'utilisateur construit son coupon chez son bookmaker, importe une capture d'écran dans l'app, et reçoit un verdict : niveau de risque, événements dangereux, et une version sécurisée proposée par COTA. L'objectif n'est pas seulement de noter le coupon, mais d'aider l'utilisateur à l'améliorer avant de valider son pari.

## 2. Principe d'analyse (logique inspirée de Herald)

On ne demande pas à une IA « ce coupon est-il bon ? ». On fait passer le coupon devant plusieurs vérificateurs indépendants, chacun avec une seule question et une règle fixe. Chacun répond toujours pareil pour la même entrée. On rassemble les réponses, on les classe par gravité, on calcule un score, et on rend un verdict signé.

> **Point clé :** le calcul est déterministe (des règles, pas une IA qui devine). L'IA n'intervient qu'à la fin pour reformuler le verdict en français et signaler le contexte — jamais pour décider le risque. C'est exactement le principe « l'IA est un serveur » déjà acté pour COTA.

## 3. Les trois couches (à ne jamais mélanger)

| Couche | Rôle | Nature | Difficulté |
|---|---|---|---|
| **1. Lecture** | Capture → données (matchs, marchés, cotes) | IA vision | **FRAGILE** — dépend du bookmaker |
| **2. Analyse** | Données → verdict | Déterministe (règles + scores COTA) | Robuste — faisable maintenant |
| **3. Explication** | Verdict → phrases | IA (serveur) | Habillage uniquement |

La lecture est la seule partie vraiment difficile : elle casse comme les sélecteurs CSS de l'auto-fill quand un layout change. Prévoir un **fallback manuel** (l'utilisateur corrige les sélections détectées). L'analyse, elle, est du calcul pur sur des briques déjà existantes (algorithme, scores de confiance, codes de marché).

## 4. Les modules du « conseil »

Chaque module regarde une seule dimension, tourne en parallèle, et renvoie des *findings* notés par sévérité (critique / élevé / moyen / info).

| Module | Ce qu'il vérifie | Règle |
|---|---|---|
| **Risque & probabilité** | Niveau de risque global | `p = 1/cote` par pick ; proba combinée = produit ; classement Faible→Très élevé ; flag cote ≥ 2,50 |
| **Corrélation & diversité** | Sélections non indépendantes | 2 marchés sur un même match, Over 2.5 + BTTS, trop de matchs d'une même compétition |
| **Cohérence statistique** | Picks faibles | Confronte chaque pick au score de confiance COTA ; sous le seuil = statistiquement faible |
| **Contradictions logiques** | Incompatibilités | Table fermée : Under 1.5 + BTTS Oui, victoire domicile + X2, etc. |
| **Value / cote** | Mauvaise value | Cote bookmaker vs cote « juste » COTA : trop basse pour le risque réel |
| **Optimisation** | Suggestions | Quoi retirer / remplacer ; recalcule le risque du coupon optimisé |

## 5. Le cœur faisable dès maintenant : croisement avec les prédictions COTA

Plutôt que de corriger pick par pick, on découpe le coupon match par match et, pour chaque match, on pose une seule question : la sélection de l'utilisateur va-t-elle dans le même sens que la prédiction COTA ?

- **Vert** — l'utilisateur joue dans le sens de COTA : on confirme.
- **Orange** — choix neutre / non couvert, mais pas opposé : on signale.
- **Rouge** — l'utilisateur joue contre la prédiction COTA : maillon dangereux.

Le verdict devient limpide : « 3 de tes 5 matchs vont dans mon sens, 2 vont contre. Voici un coupon où je remplace ces 2-là par ma lecture. » La version sécurisée = le coupon réaligné sur les prédictions COTA. On retrouve la logique deux-coupons (produit haute confiance vs produit assumé risqué), appliquée cette fois au coupon de l'utilisateur. **Aucune donnée nouvelle nécessaire : codable immédiatement.**

## 6. Mémoire & apprentissage (logique TikTok)

« Apprendre de mes erreurs » ne veut pas dire entraîner un modèle. C'est de la statistique sur l'historique vérifié. La force de TikTok n'est pas l'intelligence de l'algo, c'est la **granularité du tracking** : chaque action est captée et stockée immédiatement, puis agrégée par simple comptage. On transpose ce réflexe.

Pour chaque coupon analysé, écrire tout de suite un événement riche : ce que le coupon contenait, ce que le verdict COTA a dit, ce que l'utilisateur en a fait. Puis, une fois les matchs joués, un job remplit le **résultat réel — vérifié par COTA via API-Football**, pas sur la foi d'une capture.

**Ce que ça permet de mesurer (de l'or, qu'aucun concurrent n'a) :** quand COTA dit « risque élevé », le coupon perd-il vraiment plus souvent ? Si oui, le verdict est crédible et prouvable. Sinon, on corrige les règles. C'est la boucle d'amélioration.

### Le piège à éviter : l'empoisonnement des données

- La plupart des coupons des utilisateurs sont perdants (nature du pari). Sans filtre, on n'apprend pas « comment gagner » mais « comment les gens perdent ».
- Récompenser les coupons gagnants postés invite la triche (captures truquées, 50 coupons joués, 1 seul posté). Ne JAMAIS nourrir le moteur avec des résultats non vérifiés.
- **Règle d'or :** la mémoire qui décide ne contient que des coupons dont COTA a vérifié le résultat lui-même. Le feed communautaire (récompenses, coupons postés) reste une mécanique d'engagement **SÉPARÉE**, qui ne nourrit jamais le moteur.

### Volume & démarrage à froid

- 100 coupons : trop peu pour conclure. 1000 : tendances grossières (effet du nombre de sélections, de la cote totale). Dizaines de milliers : schémas fins par marché / compétition.
- Fixer un seuil (≈ 10-15 coupons sur un marché) avant d'afficher un taux personnel. Avant le seuil, rester sur l'analyse déterministe générique (qui marche dès le 1er jour).
- **Première source à brancher :** les coupons que COTA génère lui-même chaque jour (pronostics, combinés) avec leur résultat — 100 % sous contrôle, produits quotidiennement, matière la plus pure.

## 7. Proposer une sécurité : les paramètres à ajouter

Un pourcentage seul ne permet aucune action. Pour que le moteur propose « à la place de ça, prends ça », il lui faut :

- **Contribution au risque par sélection** — savoir quel pick plombe le coupon pour pouvoir le pointer.
- **Alternatives sur le même match** — la carte complète des marchés disponibles et leur score COTA (ex. passer de « Victoire » à « Double Chance »). Nécessite la couverture odds de tous les marchés — c'est le vrai coût technique, et la décision §25 sur la couverture odds devient bloquante ici.
- **Curseur risque / rendement** — vers quel profil sécuriser (Prudent ≥75 % / Équilibré ≥65 % / Audacieux ≥60 %). Sécuriser = un cran plus sûr, pas l'extrême.

> **Sens unique :** « sécuriser » va toujours vers MOINS de risque (baisser la cote contre de la fiabilité). Jamais « ajoute un match pour monter la cote ».

### Plus tard : raisonnement par analogie de match

« Ce France-X ressemble à tel match (même météo, un cadre absent) qui avait fini comme ça ». Faisable, mais demande de décrire chaque match par une liste de caractéristiques (météo, absents, repos, enjeu, lieu, forme) ET de les archiver avec le résultat. Action à lancer dès maintenant même sans l'utiliser : commencer à stocker ces caractéristiques, sinon impossible de raisonner plus tard sur un passé non gardé. Demande un gros volume avant d'être fiable. L'IA sert ici à formuler le rapprochement, pas à calculer la similarité.

## 8. Architecture technique (additif à l'existant)

- Endpoint **`POST /api/v1/coupons/analyze`** qui orchestre les 4 étapes, en job asynchrone (Supervisor / Redis déjà en place). Flutter upload → reçoit un `analysis_id` → push FCM quand prêt. On ne bloque pas l'écran.
- Table **`coupon_analyses`** : `user_id, image_path, detected_json, verdict_json, status, result, created_at`. Sert l'historique Premium ET l'apprentissage du profil de parieur (P1).
- **Job de résultat** : repasse remplir le résultat réel quand les matchs sont finis (comme `UpdateMatchResultsJob`).
- **Filament** : vue « Analyses de coupons » + vue « Échecs de lecture » (captures non lues) — le signal qualité, comme le dashboard « Failed Auto-Fills ».

## 9. Garde-fous

- **Jeu responsable** — un coupon noté « risque faible » n'est jamais une promesse de gain. Le ton reste celui d'un garde-fou (« je te propose »), pas d'un encourageur. On commente un pari d'argent réel.
- **RGPD** — le profil de parieur et le tracking comportemental doivent être couverts par le consentement analytics, inclus dans l'export et supprimés à la suppression de compte.
- **Fallback manuel** — écran de correction des sélections quand la lecture OCR échoue. Indispensable pour un Premium.
- **Coût IA** — chaque analyse = 1 appel vision + 1 appel texte. Garder un quota même côté Premium au début, le temps de mesurer le coût réel par analyse.

## 10. Feuille de route

| Horizon | Action |
|---|---|
| **Maintenant** | Mécanique de croisement (découpage + vert/orange/rouge + coupon réaligné). Logger les coupons générés par COTA avec leur résultat. |
| **En parallèle, en silence** | Archiver les caractéristiques de chaque match (météo, absents, repos, enjeu) + résultat. Récolter sans encore s'en servir. |
| **Plus tard (volume suffisant)** | Activer la mémoire « j'ai vu N coupons comme le tien ». Puis l'analogie de match. Puis la lecture de capture grand public. |

## 11. Décisions ouvertes

- Provider LLM avec capacité vision (lecture de capture en français, cotes serrées) — conditionne la fiabilité de toute la chaîne. Lié à la décision §25 du CDC.
- Couverture odds de tous les marchés en production — bloquant pour la fonction « proposer une sécurité ».
- Apprentissage au niveau du coupon entier (forme du combiné) d'abord, ou au niveau de chaque sélection (plus puissant mais plus gourmand en volume).
- Profil de parieur invisible (influence les verdicts en coulisse) ou écran « Mon profil de parieur » visible (plus engageant, ton à soigner).

---

*Document confidentiel — Propriété intellectuelle MHD SERVICE*
