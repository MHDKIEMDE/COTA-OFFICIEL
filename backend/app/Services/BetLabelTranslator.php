<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Traduit un outcome de prédiction (produit par PredictionAlgorithmService)
 * en libellé localisé, selon la locale active de l'application.
 *
 * L'algorithme stocke des chaînes sémantiques stables (ex. "Over 2.5",
 * "1", "X2", "{Equipe} Over 1.5"). Ce service les associe à une clé du
 * fichier lang/{locale}/predictions.php et réinjecte équipe / ligne.
 *
 * La conception est volontairement défensive : tout outcome non reconnu
 * est renvoyé tel quel (jamais d'exception), pour rester rétrocompatible
 * avec d'éventuels nouveaux marchés ajoutés à l'algorithme.
 */
class BetLabelTranslator
{
    /**
     * Traduire un outcome dans la locale courante.
     *
     * @param string      $outcome  Outcome brut stocké (ex. "Over 2.5", "1", "PSG Over 1.5")
     * @param string|null $homeName Nom de l'équipe domicile (pour retirer le préfixe)
     * @param string|null $awayName Nom de l'équipe extérieur
     */
    public function translate(string $outcome, ?string $homeName = null, ?string $awayName = null): string
    {
        $outcome = trim($outcome);

        // 1X2 et double chance : codes exacts
        if (in_array($outcome, ['1', 'X', '2', '1X', 'X2', '12'], true)) {
            return $this->line($outcome);
        }

        // BTTS (l'algo produit déjà "Oui"/"Non")
        $lower = mb_strtolower($outcome);
        if (in_array($lower, ['oui', 'yes'], true)) {
            return $this->line('btts_yes');
        }
        if (in_array($lower, ['non', 'no'], true)) {
            return $this->line('btts_no');
        }

        // Préfixe équipe : "{Equipe} Over 1.5", "{Equipe} Over 3.5 shots"...
        $team = null;
        foreach (array_filter([$homeName, $awayName]) as $name) {
            if ($name !== '' && str_starts_with($outcome, $name . ' ')) {
                $team    = $name;
                $outcome = trim(substr($outcome, strlen($name)));
                break;
            }
        }

        // Tirs au total : "Under 8.5 total shots"
        if (preg_match('/^Under\s+([\d.]+)\s+total shots$/i', $outcome, $m)) {
            return $this->line('shots_total_under', ['line' => $m[1]]);
        }

        // Tirs par équipe : "Over 3.5 shots"
        if ($team !== null && preg_match('/^Over\s+([\d.]+)\s+shots$/i', $outcome, $m)) {
            return $this->line('team_shots_over', ['team' => $team, 'line' => $m[1]]);
        }

        // Corners : "Over 9.5 corners" / "Under 8.5 corners"
        if (preg_match('/^(Over|Under)\s+([\d.]+)\s+corners$/i', $outcome, $m)) {
            $key = strtolower($m[1]) === 'over' ? 'corners_over' : 'corners_under';
            return $this->line($key, ['line' => $m[2]]);
        }

        // Cartons : "Over 4.5 cards" / "Under 3.5 cards"
        if (preg_match('/^(Over|Under)\s+([\d.]+)\s+cards$/i', $outcome, $m)) {
            $key = strtolower($m[1]) === 'over' ? 'cards_over' : 'cards_under';
            return $this->line($key, ['line' => $m[2]]);
        }

        // Buts par équipe : "{Equipe} Over 1.5" / "Under 1.5" (équipe déjà retirée)
        if ($team !== null && preg_match('/^(Over|Under)\s+([\d.]+)$/i', $outcome, $m)) {
            $key = strtolower($m[1]) === 'over' ? 'team_over' : 'team_under';
            return $this->line($key, ['team' => $team, 'line' => $m[2]]);
        }

        // Over/Under buts du match : "Over 2.5" / "Under 2.5"
        if (preg_match('/^(Over|Under)\s+([\d.]+)$/i', $outcome, $m)) {
            $key = strtolower($m[1]) === 'over' ? 'over' : 'under';
            return $this->line($key, ['line' => $m[2]]);
        }

        // Inconnu : renvoyer tel quel (réinjecter l'équipe si elle avait été retirée)
        return $team !== null ? trim($team . ' ' . $outcome) : $outcome;
    }

    /**
     * Récupère une ligne de traduction ; renvoie une valeur de repli lisible
     * si la clé n'existe pas (jamais la clé brute "predictions.xxx").
     */
    private function line(string $key, array $replace = []): string
    {
        $full = "predictions.$key";
        $label = __($full, $replace);

        return $label === $full ? ($replace['team'] ?? $key) : $label;
    }
}
