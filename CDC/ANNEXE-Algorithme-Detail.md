# 📐 ANNEXE - Algorithme Détaillé v3.0

> **Référence technique complète** | Formules mathématiques, pondérations, exemples chiffrés.
> **Document à conserver** : c'est le coeur de la propriété intellectuelle de FootApp.

---

## 🎯 OBJECTIF

Spécification mathématique précise de l'algorithme de scoring pour atteindre un win rate de 55-60%+ sur les pronostics football.

---

## 🧮 FORMULE GÉNÉRALE

### Score de confiance (0-100)

```
Score_Total = Σ(Score_Critère_i × Poids_i)

Où :
- Score_Critère_i ∈ [-Poids_i, +Poids_i]
- Poids_i = pondération du critère i
- Σ(Poids_i) = 100
```

### Score absolu (pour publication)

```
Score_Final = |Score_Total|
Direction = SIGN(Score_Total)
   - Si Direction > 0 → Favori = Équipe Domicile
   - Si Direction < 0 → Favori = Équipe Extérieur
   - Si |Direction| < 10 → Match équilibré → Recommandation Over/Under ou BTTS
```

---

## 📊 LES 6 CRITÈRES (Phase 1)

| # | Critère | Poids | Min | Max | Source |
|---|---------|-------|-----|-----|--------|
| 1 | 🔥 Forme récente | 28% | -28 | +28 | API-Football last 5 |
| 2 | ⚔️ Head-to-Head | 23% | -23 | +23 | API-Football H2H |
| 3 | 🏟️ Domicile/Extérieur | 18% | -18 | +18 | API-Football team stats |
| 4 | 📍 Classement | 13% | -13 | +13 | API-Football standings |
| 5 | ⚽ Stats buts | 10% | -10 | +10 | API-Football team stats |
| 6 | 🌙 Facteur horaire | 8% | -8 | +8 | Match date/time |
| | **TOTAL** | **100%** | **-100** | **+100** | |

---

## 1️⃣ CRITÈRE - FORME RÉCENTE (28 pts)

### Définition
Performance d'une équipe sur ses **5 derniers matchs toutes compétitions confondues**.

### Système de points par résultat

| Résultat | Points |
|----------|--------|
| Victoire (V) | 5 |
| Match nul (N) | 2.5 |
| Défaite (D) | 0 |

### Formule de base

```
Points_Forme_A = Σ(résultats_équipe_A) sur 5 derniers matchs
Points_Forme_B = Σ(résultats_équipe_B) sur 5 derniers matchs

Diff_Forme = Points_Forme_A - Points_Forme_B

Score_Critère_1 = (Diff_Forme / 25) × 28
```

> Diff max théorique = 25 - 0 = 25 (équipe A 5V, équipe B 5D)
> Score max = 25/25 × 28 = 28 points

### Bonus/Malus

| Condition | Modificateur |
|-----------|--------------|
| Équipe A : 5 victoires consécutives (V5) | **+2 points** |
| Équipe B : 5 défaites consécutives (D5) | **-2 points** |
| Équipe A : 5 défaites (D5) | **-2 points** |
| Équipe B : 5 victoires (V5) | **+2 points** |

### Exemple chiffré

**Match : Real Madrid vs Cadiz**

```
Real Madrid (5 derniers) : V-V-V-N-V → 5+5+5+2.5+5 = 22.5 pts
Cadiz (5 derniers)        : D-N-D-D-D → 0+2.5+0+0+0 = 2.5 pts

Diff_Forme = 22.5 - 2.5 = 20
Score_Base = (20/25) × 28 = +22.4 points
Bonus : aucun (pas de V5 ni D5 strict)

Score_Critère_1 = +22.4 points (favorise Real Madrid)
```

### Edge cases

- Match annulé/reporté dans les 5 derniers : ignorer, prendre le 6ème
- Moins de 5 matchs disponibles (début saison, équipe nouvelle) : prendre tous les matchs disponibles, normaliser

---

## 2️⃣ CRITÈRE - HEAD-TO-HEAD (23 pts)

### Définition
Performance lors des **5 dernières confrontations directes** entre les 2 équipes.

### Formule

```
Victoires_A_H2H = nombre de victoires de A sur 5 derniers H2H
Victoires_B_H2H = nombre de victoires de B sur 5 derniers H2H

Diff_H2H = Victoires_A - Victoires_B

Score_Critère_2 = (Diff_H2H / 5) × 23
```

> Diff max = 5 (5 victoires A vs 0 B)
> Score max = 5/5 × 23 = 23 points

### Cas spécial : moins de 3 H2H

Si **moins de 3 confrontations historiques** disponibles (équipes qui s'affrontent rarement) :

- Désactiver le critère H2H (poids = 0%)
- **Re-pondérer les autres critères** :

| Critère | Poids original | Poids ajusté |
|---------|----------------|--------------|
| Forme récente | 28% | **35%** |
| H2H | 23% | **0%** |
| Domicile/Extérieur | 18% | **23%** |
| Classement | 13% | **18%** |
| Stats buts | 10% | **14%** |
| Horaire | 8% | **10%** |

### Exemple chiffré

**Match : PSG vs Marseille (Le Classique)**

```
5 derniers H2H : PSG 4 victoires, Marseille 1 victoire (0 nuls)

Diff_H2H = 4 - 1 = 3
Score_Critère_2 = (3/5) × 23 = +13.8 points
```

---

## 3️⃣ CRITÈRE - DOMICILE/EXTÉRIEUR (18 pts)

### Définition
Comparer la performance de l'équipe **A à domicile** vs l'équipe **B à l'extérieur** sur leurs 10 derniers matchs respectifs (filtré domicile pour A, extérieur pour B).

### Formule

```
Win_Rate_A_Home = (Victoires_A_à_domicile) / 10
Win_Rate_B_Away = (Victoires_B_à_extérieur) / 10

Diff_Performance = Win_Rate_A_Home - Win_Rate_B_Away

Score_Critère_3 = Diff_Performance × 18
```

### Bonus/Malus

| Condition | Modificateur |
|-----------|--------------|
| Équipe A invaincue à domicile (10 derniers) | **+3** |
| Équipe B aucune victoire à l'extérieur (10 derniers) | **-3** |
| Équipe A aucune victoire à domicile | **-3** |
| Équipe B invaincue à l'extérieur | **+3** |

### Clamp final

```
Score_Critère_3 = max(-18, min(18, Score_Critère_3))
```

### Exemple chiffré

**Match : Manchester City (dom) vs Luton Town (ext)**

```
City à domicile (10 derniers) : 9 V, 1 N, 0 D → 90% win rate
Luton à l'extérieur (10 derniers) : 1 V, 2 N, 7 D → 10% win rate

Diff = 0.90 - 0.10 = 0.80
Score_Base = 0.80 × 18 = 14.4 points

Bonus : City invaincu à domicile → +3 points
Bonus : Luton aucune victoire ext (1 V seulement) → 0 (pas strict)

Score_Critère_3 = 14.4 + 3 = +17.4 points (favorise City)
Clamp à max +18 → 17.4 OK
```

---

## 4️⃣ CRITÈRE - CLASSEMENT (13 pts)

### Définition
Différence de position au classement actuel du championnat.

### Formule progressive (pas linéaire)

```
Position_A = position de l'équipe A dans le classement
Position_B = position de l'équipe B
Gap = Position_B - Position_A
   - Gap > 0 → A est mieux classée
   - Gap < 0 → B est mieux classée

Score_Critère_4 :
   |Gap| ≥ 10 places → ±13 points (max)
   |Gap| ∈ [5, 9]   → ±8 points
   |Gap| ∈ [3, 4]   → ±5 points
   |Gap| ∈ [1, 2]   → ±2 points
   Gap = 0          → 0 point
```

### Cas spéciaux

| Cas | Action |
|-----|--------|
| **Compétitions internationales** (Coupe Monde, Euro, Champions League) | Désactiver critère → poids 0%, redistribuer (Forme 35%) |
| **Début de saison** (< 5 journées jouées) | Réduire poids à 5% → redistribuer 8% sur Forme |
| **Équipes différentes championnats** (Coupe d'Europe, amicaux) | Désactiver critère |

### Exemple chiffré

**Match : Real Madrid (1er Liga) vs Cadiz (18ème Liga)**

```
Position_A = 1
Position_B = 18
Gap = 18 - 1 = 17 → ≥ 10 places

Score_Critère_4 = +13 points (favorise Real Madrid)
```

---

## 5️⃣ CRITÈRE - STATS BUTS (10 pts)

### Décomposition en 3 sous-critères

| Sous-critère | Poids | Calcul |
|--------------|-------|--------|
| Moyenne buts marqués | 4 pts | Différentiel sur saison |
| BTTS (Both Teams To Score) | 3 pts | Bonus si 2 équipes >60% BTTS |
| Clean Sheets | 3 pts | Différentiel taux clean sheets |

### A. Moyenne buts marqués (4 pts)

```
Avg_Goals_A = buts marqués totaux / matchs joués (saison)
Avg_Goals_B = idem pour B

Diff_Goals = Avg_Goals_A - Avg_Goals_B

Score_Goals = (Diff_Goals / 4) × 4
   → Clampé entre -4 et +4
```

### B. BTTS (3 pts)

```
BTTS_Rate_A = (matchs où équipe + adversaire ont marqué) / total matchs
BTTS_Rate_B = idem

Si BTTS_Rate_A > 0.60 ET BTTS_Rate_B > 0.60:
   Score_BTTS = +3
Sinon:
   Score_BTTS = 0
```

> Ce sous-critère ne favorise pas une équipe, mais influence la recommandation de pari (Over 2.5, BTTS Yes)

### C. Clean Sheets (3 pts)

```
CS_Rate_A = (matchs sans encaisser) / total matchs
CS_Rate_B = idem

Diff_CS = CS_Rate_A - CS_Rate_B

Score_CS = Diff_CS × 3
```

### Score total critère 5

```
Score_Critère_5 = Score_Goals + Score_BTTS + Score_CS
   → Clampé entre -10 et +10
```

### Exemple chiffré

**Match : Manchester City vs Luton**

```
City : avg 3.2 buts, BTTS 65%, CS 60%
Luton : avg 0.9 but, BTTS 80%, CS 10%

Score_Goals = ((3.2 - 0.9) / 4) × 4 = +2.3
Score_BTTS = (les 2 > 60%) → 0 (Luton 80% mais City 65%, donc condition OK !) 
                          → Re-vérifier : 65% > 60% ET 80% > 60% → +3
Score_CS = (0.60 - 0.10) × 3 = +1.5

Score_Critère_5 = 2.3 + 3.0 + 1.5 = +6.8 points
```

---

## 6️⃣ CRITÈRE - FACTEUR HORAIRE (8 pts) ⭐ INNOVATION

### Définition
Impact de l'**heure du match** sur les performances physiques et mentales.

### Score de base : 4.0 (neutre)

### Modificateurs selon plage horaire

| Plage horaire | Catégorie | Modificateur |
|---------------|-----------|--------------|
| 08h00 - 11h59 | Matinée | **-1 pt** |
| 12h00 - 15h59 | Après-midi | **0 pt** |
| 16h00 - 19h59 | Prime time ⭐ | **+2 pts** |
| 20h00 - 22h59 | Soirée | **0 pt** |
| 23h00 - 01h59 | Nuit | **-3 pts** |
| 02h00 - 07h59 | Nuit profonde ⚠️ | **-5 pts** |

### Bonus additionnels

| Condition | Modificateur |
|-----------|--------------|
| Équipe joue régulièrement à cette heure (>5 matchs sur 10 dans même plage) | **+2** |
| Décalage horaire > 3h (matchs internationaux à l'étranger) | **-2** |

### Formule complète

```
Score = 4.0 (base)
Score += Modificateur_Plage
Score += Modificateur_Habitude (si applicable)
Score += Modificateur_Décalage (si applicable)

Score_Critère_6 = max(0, min(8, Score))
```

> Note : ce critère est **toujours positif ou neutre** (clamp à 0). Il ne pénalise pas une équipe, il représente une incertitude générale (donc max 8, jamais négatif).

### Exemple chiffré

**Match : PSG vs OM - Samedi 21h00 (heure parisienne)**

```
Plage : 20h-23h → Soirée → 0 pt
Habitude : PSG joue souvent à 21h (Ligue 1 prime) → +2
Décalage : non → 0

Score = 4.0 + 0 + 2 = 6.0

Score_Critère_6 = +6.0 points
```

---

## 🔍 EXEMPLE COMPLET (Phase 1)

### Match : Manchester City vs Luton Town - Samedi 16h00 (UK)

#### Données

| Donnée | City | Luton |
|--------|------|-------|
| Forme (5 derniers) | V-V-V-V-V (25 pts) | D-D-N-D-D (2.5 pts) |
| H2H 5 derniers (City vs Luton) | City 4V, Luton 1V | |
| Domicile City | 9V, 1N, 0D (90%) | |
| Extérieur Luton | 1V, 2N, 7D (10%) | |
| Position championnat | 1er | 18ème |
| Avg buts | 3.2 | 0.9 |
| BTTS rate | 65% | 80% |
| Clean sheets | 60% | 10% |
| Heure | 16h00 (Prime time) | |

#### Calcul

| Critère | Score |
|---------|-------|
| 1. Forme récente : (25-2.5)/25 × 28 + 2 (V5 City) = | **+27.2** |
| 2. H2H : (4-1)/5 × 23 = | **+13.8** |
| 3. Dom/Ext : (0.90-0.10) × 18 + 3 (invaincu) = | **+17.4** |
| 4. Classement : Gap=17 > 10 → | **+13.0** |
| 5. Stats buts : 2.3 + 3.0 + 1.5 = | **+6.8** |
| 6. Horaire : 4 + 2 (prime time) + 2 (habitude) = | **+8.0** |
| **TOTAL** | **+86.2** |

#### Interprétation

```
Score_Total = +86.2 → Favorise City fortement
Direction = +1 (Domicile)
Score_Final (absolu) = 86.2

Niveau de confiance :
   86.2 ≥ 85 → TRÈS ÉLEVÉE ⭐⭐⭐⭐
   
Recommandation pari :
   Direction très favorable + Stats buts élevées City
   → Pari principal : "City gagne" (cote ~1.20)
   → Pari alternatif : "Over 2.5 buts" (cote ~1.50)
   → Pari combiné : "City gagne ET +2.5 buts" (cote ~1.80)

Pronostic publié : ⭐⭐⭐⭐ TRÈS ÉLEVÉE
```

---

## 🎯 SEUILS DE PUBLICATION

| Score Final | Niveau | Action | Étoiles |
|-------------|--------|--------|---------|
| **85-100** | TRÈS ÉLEVÉE | Publication prioritaire + Combiné Premium éligible | ⭐⭐⭐⭐ |
| **70-84** | ÉLEVÉE | Publication standard + Combiné éligible | ⭐⭐⭐ |
| **60-69** | MOYENNE | Publication simple uniquement | ⭐⭐ |
| **50-59** | FAIBLE | Publication conditionnelle (cote > 1.80) | ⭐ |
| **0-49** | TRÈS FAIBLE | ❌ Non publié | - |

### Règles de publication

#### Pronostics simples
- Score ≥ 50 obligatoire
- Cote dans [1.40, 3.00]
- **Maximum 15 pronostics/jour** (qualité > quantité)

#### Combiné quotidien (Premium)
- Tous les matchs : score ≥ 65
- 4-5 matchs sélectionnés (max 2 par compétition)
- Cote totale : [8.00, 20.00]
- Publication : 08h00 UTC

#### Combiné Bienvenue (gratuit, 1×)
- Tous les matchs : score ≥ 70
- 3-4 matchs haute confiance
- Cote totale : [6.00, 12.00] (sécurisé)
- Validité : 24h après inscription

---

## 🚀 ÉVOLUTIONS PHASE 2 (avec ML)

### Critères additionnels (Sprint 04)

| # | Critère | Poids ajusté |
|---|---------|--------------|
| 7 | xG (Expected Goals) | **10%** |
| 8 | Blessures (joueurs clés) | **6%** |

### Re-pondération Phase 2

| Critère | Phase 1 | Phase 2 (avec ML) |
|---------|---------|-------------------|
| Forme récente | 28% | **25%** |
| H2H | 23% | **20%** |
| Dom/Ext | 18% | **15%** |
| Classement | 13% | **10%** |
| Stats buts | 10% | **8%** |
| Horaire | 8% | **6%** |
| **xG** (NEW) | - | **10%** |
| **Blessures** (NEW) | - | **6%** |
| **TOTAL** | **100%** | **100%** |

### Hybridation avec ML

```
Score_Hybrid = (Score_Statique × 0.6) + (Score_ML × 0.4)

Où :
- Score_Statique = somme des critères pondérés (algo Phase 1)
- Score_ML = sortie modèle scikit-learn (probabilités converties)
```

---

## 🧪 BACKTESTING

### Métriques à mesurer

```
Pour chaque pronostic :
   - Confidence Score
   - Bet Type & Choice
   - Actual Result
   - Won (boolean)

Statistiques :
   - Win Rate Global
   - Win Rate par niveau (TRÈS ÉLEVÉE, ÉLEVÉE, etc.)
   - Win Rate par bet type (1X2, Over/Under, BTTS, etc.)
   - ROI (Return on Investment)
   - Max Drawdown
   - Sharpe Ratio (optionnel)
```

### Cibles minimales

| Niveau | Win Rate cible Phase 1 | Cible Phase 3 |
|--------|------------------------|---------------|
| TRÈS ÉLEVÉE | ≥ 70% | ≥ 75% |
| ÉLEVÉE | ≥ 60% | ≥ 65% |
| MOYENNE | ≥ 55% | ≥ 60% |
| FAIBLE | ≥ 50% | ≥ 55% |
| **GLOBAL** | **≥ 55%** | **≥ 60%** |

### ROI cible

ROI = (Total gains - Total mises) / Total mises × 100%

```
Mise constante 100€/pronostic
Cote moyenne : 1.85
Win rate : 58%

Sur 100 pronos :
   - 58 gagnés × 100 × (1.85-1) = 4 930€ profit brut
   - 42 perdus × 100 = -4 200€ pertes
   - ROI net : (4 930 - 4 200) / 10 000 = +7.3% sur la période
```

---

## 🔧 IMPLÉMENTATION CODE (référence)

> 📖 Code détaillé dans `SPRINT-03-Algorithme-v3.md`

```php
class PredictionScoringService
{
    private array $criteria = [
        RecentFormCriterion::class,    // 28%
        HeadToHeadCriterion::class,    // 23%
        HomeAwayCriterion::class,      // 18%
        LeaguePositionCriterion::class,// 13%
        GoalStatsCriterion::class,     // 10%
        TimeFactorCriterion::class,    // 8%
    ];
    
    public function calculateScore(FootballMatch $match): array
    {
        $totalScore = 0;
        $details = [];
        
        foreach ($this->criteria as $criterionClass) {
            $criterion = app($criterionClass);
            $result = $criterion->calculate($match);
            
            $totalScore += $result['score'];
            $details[$criterion->getName()] = $result;
        }
        
        return [
            'total_score' => $totalScore,
            'absolute_score' => abs($totalScore),
            'direction' => $totalScore > 0 ? 'home' : 'away',
            'confidence_level' => $this->getConfidenceLevel(abs($totalScore)),
            'criteria_details' => $details,
        ];
    }
}
```

---

## 📚 RÉFÉRENCES & RECHERCHES

### Études consultées

1. "Predicting Football Match Outcomes with eXtreme Gradient Boosting" (Hubáček et al., 2019)
2. "An expected goals (xG) calibration analysis" (StatsBomb, 2020)
3. "Home Advantage in Football" (Goumas, 2014)

### Inspiration

- **FiveThirtyEight Soccer Predictions** (modèle SPI)
- **Football-Data.co.uk** (données historiques)
- **Pinnacle Sports** (cotes référentielles)

---

## ⚠️ AVERTISSEMENT

Cet algorithme produit des **probabilités statistiques**, pas des certitudes.

**Aucun algorithme ne peut prédire à 100%** un événement sportif. Le hasard, les blessures de dernière minute, l'arbitrage, et la psychologie restent imprévisibles.

**Win rate cible 55-60%** = excellent. Pas plus, pas moins.

---

*Document confidentiel - Propriété intellectuelle MHD SERVICE*
*Dernière mise à jour : Octobre 2025*
