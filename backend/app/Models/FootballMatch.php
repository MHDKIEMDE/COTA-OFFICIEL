<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FootballMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'match_id',
        'home_team_id',
        'away_team_id',
        'competition_id',
        'home_team',
        'home_team_logo',
        'away_team',
        'away_team_logo',
        'competition',
        'country',
        'competition_logo',
        'match_date',
        'match_time',
        'timezone',
        'home_score',
        'away_score',
        'home_score_halftime',
        'away_score_halftime',
        'status',
        'status_long',
        'elapsed_time',
        'home_team_form',
        'away_team_form',
        'h2h_history',
        'standings_info',
        'match_statistics',
        'venue_name',
        'venue_city',
        'referee',
        'last_api_fetch',
    ];

    // Clé primaire custom
    protected $primaryKey = 'id';

    // Désactiver auto-incrémentation si match_id est externe
    public $incrementing = true;

    protected $casts = [
        'match_date' => 'datetime',
        'last_api_fetch' => 'datetime',
        'home_team_form' => 'array',
        'away_team_form' => 'array',
        'h2h_history' => 'array',
        'standings_info' => 'array',
        'match_statistics' => 'array',
    ];

    /**
     * Relation avec les pronostics
     */
    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'match_id', 'match_id');
    }

    /**
     * Scope: Matchs à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('match_date', '>', now())
                     ->where('status', 'scheduled')
                     ->orderBy('match_date', 'asc');
    }

    /**
     * Scope: Matchs en direct
     */
    public function scopeLive($query)
    {
        return $query->whereIn('status', ['live', 'halftime'])
                     ->orderBy('elapsed_time', 'desc');
    }

    /**
     * Scope: Matchs terminés
     */
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished')
                     ->orderBy('match_date', 'desc');
    }

    /**
     * Scope: Matchs d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('match_date', Carbon::today());
    }

    /**
     * Scope: Par compétition
     */
    public function scopeByCompetition($query, int $competitionId)
    {
        return $query->where('competition_id', $competitionId);
    }

    /**
     * Vérifier si le match a besoin d'une mise à jour depuis l'API
     */
    public function needsApiUpdate(): bool
    {
        // Si jamais récupéré
        if (!$this->last_api_fetch) {
            return true;
        }

        // Si match en direct, mettre à jour toutes les minutes
        if (in_array($this->status, ['live', 'halftime'])) {
            return $this->last_api_fetch->diffInMinutes(now()) >= 1;
        }

        // Si match programmé dans les prochaines 24h, mettre à jour toutes les heures
        if ($this->status === 'scheduled' && $this->match_date->diffInHours(now()) <= 24) {
            return $this->last_api_fetch->diffInHours(now()) >= 1;
        }

        // Si match terminé, pas besoin de mise à jour
        if ($this->status === 'finished') {
            return false;
        }

        // Par défaut, mettre à jour toutes les 6 heures
        return $this->last_api_fetch->diffInHours(now()) >= 6;
    }

    /**
     * Obtenir le résultat du match
     */
    public function getResult(): ?string
    {
        if ($this->status !== 'finished' || $this->home_score === null) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return '1'; // Victoire domicile
        } elseif ($this->home_score < $this->away_score) {
            return '2'; // Victoire extérieur
        } else {
            return 'X'; // Match nul
        }
    }

    /**
     * Vérifier si les deux équipes ont marqué
     */
    public function isBtts(): ?bool
    {
        if ($this->status !== 'finished' || $this->home_score === null) {
            return null;
        }

        return $this->home_score > 0 && $this->away_score > 0;
    }

    /**
     * Calculer le total de buts
     */
    public function getTotalGoals(): ?int
    {
        if ($this->home_score === null || $this->away_score === null) {
            return null;
        }

        return $this->home_score + $this->away_score;
    }

    /**
     * Vérifier si over 2.5
     */
    public function isOver25(): ?bool
    {
        $total = $this->getTotalGoals();
        return $total !== null ? $total > 2 : null;
    }

    /**
     * Vérifier si under 2.5
     */
    public function isUnder25(): ?bool
    {
        $total = $this->getTotalGoals();
        return $total !== null ? $total < 3 : null;
    }
}
