<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Prediction extends Model
{
    protected $fillable = [
        'match_id',
        'home_team',
        'away_team',
        'home_team_id',
        'away_team_id',
        'competition',
        'competition_id',
        'country',
        'match_date',
        'match_time',
        'bet_type',
        'prediction',
        'odds',
        'confidence_stars',
        'score_form',
        'score_h2h',
        'score_home_away',
        'score_league',
        'score_goals',
        'score_time',
        'score_weather',
        'score_shots',
        'score_physical',
        'total_score',
        'home_score',
        'away_score',
        'status',
        'is_published',
        'is_premium',
        'analysis_details',
        'published_at',
        'is_combined_daily',
        'combined_date',
        'combined_position',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'published_at' => 'datetime',
        'combined_date' => 'date',
        'is_published' => 'boolean',
        'is_premium' => 'boolean',
        'is_combined_daily' => 'boolean',
        'odds' => 'decimal:2',
        'total_score' => 'decimal:2',
        'score_form' => 'decimal:2',
        'score_h2h' => 'decimal:2',
        'score_home_away' => 'decimal:2',
        'score_league' => 'decimal:2',
        'score_goals' => 'decimal:2',
        'score_time' => 'decimal:2',
        'score_weather' => 'decimal:2',
        'score_shots' => 'decimal:2',
        'score_physical' => 'decimal:2',
    ];

    /**
     * Relation avec le match
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id', 'match_id');
    }

    /**
     * Scope: Pronostics publiés
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Pronostics premium
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope: Pronostics gratuits
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope: Pronostics d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('match_date', Carbon::today());
    }

    /**
     * Scope: Pronostics du combiné quotidien
     */
    public function scopeCombinedDaily($query, ?string $date = null)
    {
        $query = $query->where('is_combined_daily', true);

        if ($date) {
            $query->whereDate('combined_date', $date);
        }

        return $query->orderBy('combined_position');
    }

    /**
     * Scope: Recherche par équipe ou compétition
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('home_team', 'like', "%{$term}%")
              ->orWhere('away_team', 'like', "%{$term}%")
              ->orWhere('competition', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: Pronostics terminés
     */
    public function scopeFinished($query)
    {
        return $query->whereIn('status', ['won', 'lost', 'cancelled']);
    }

    /**
     * Scope: Pronostics en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Pronostics gagnés
     */
    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    /**
     * Scope: Pronostics perdus
     */
    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    /**
     * Accessor: Détails de l'analyse (JSON)
     */
    public function getAnalysisAttribute(): ?array
    {
        if (!$this->analysis_details) {
            return null;
        }

        if (is_string($this->analysis_details)) {
            return json_decode($this->analysis_details, true);
        }

        return $this->analysis_details;
    }

    /**
     * Accessor: Scores detailles (9 criteres)
     */
    public function getScoresAttribute(): array
    {
        return [
            'form' => $this->score_form,
            'h2h' => $this->score_h2h,
            'home_away' => $this->score_home_away,
            'league' => $this->score_league,
            'goals' => $this->score_goals,
            'time' => $this->score_time,
            'weather' => $this->score_weather,
            'shots' => $this->score_shots,
            'physical' => $this->score_physical,
            'total' => $this->total_score,
        ];
    }

    /**
     * Vérifier si le pronostic est correct
     */
    public function checkResult(): ?string
    {
        if ($this->home_score === null || $this->away_score === null) {
            return null;
        }

        $actualResult = $this->getActualResult();

        $isCorrect = false;

        switch ($this->bet_type) {
            case '1X2':
                $isCorrect = $this->prediction === $actualResult;
                break;

            case 'BTTS':
                $btts = $this->home_score > 0 && $this->away_score > 0;
                $isCorrect = ($this->prediction === 'Yes' && $btts) || ($this->prediction === 'No' && !$btts);
                break;

            case 'Over/Under':
                $totalGoals = $this->home_score + $this->away_score;
                if (strpos($this->prediction, 'Over') !== false) {
                    $threshold = (float) str_replace('Over ', '', $this->prediction);
                    $isCorrect = $totalGoals > $threshold;
                } else {
                    $threshold = (float) str_replace('Under ', '', $this->prediction);
                    $isCorrect = $totalGoals < $threshold;
                }
                break;

            case 'Double Chance':
                $results = explode('/', $this->prediction);
                $isCorrect = in_array($actualResult, $results);
                break;
        }

        return $isCorrect ? 'won' : 'lost';
    }

    /**
     * Obtenir le résultat réel du match
     */
    private function getActualResult(): string
    {
        if ($this->home_score > $this->away_score) {
            return '1';
        } elseif ($this->home_score < $this->away_score) {
            return '2';
        } else {
            return 'X';
        }
    }
}
