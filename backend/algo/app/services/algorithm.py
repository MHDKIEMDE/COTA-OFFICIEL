"""
Algorithme COTA v3 — 9 critères, score 0-100
"""
from typing import Any, Literal


def _safe_div(a: float, b: float, default: float = 0.0) -> float:
    return a / b if b else default


def score_recent_form(fixtures: list[dict[str, Any]], team_id: int) -> float:
    """Critère 1 — Forme récente (28%). 5 derniers matchs."""
    if not fixtures:
        return 0.0
    points = 0.0
    for f in fixtures[-5:]:
        teams = f["teams"]
        goals = f["goals"]
        is_home = teams["home"]["id"] == team_id
        scored = goals["home"] if is_home else goals["away"]
        conceded = goals["away"] if is_home else goals["home"]
        if scored is None or conceded is None:
            continue
        if scored > conceded:
            points += 3
        elif scored == conceded:
            points += 1
    return min(points / 15 * 100, 100)


def score_head_to_head(h2h: list[dict[str, Any]], home_id: int) -> float:
    """Critère 2 — Head-to-head (23%). 10 derniers H2H."""
    if not h2h:
        return 50.0
    wins = 0
    for f in h2h[-10:]:
        teams = f["teams"]
        goals = f["goals"]
        home_goals = goals.get("home") or 0
        away_goals = goals.get("away") or 0
        is_home = teams["home"]["id"] == home_id
        scored = home_goals if is_home else away_goals
        conceded = away_goals if is_home else home_goals
        if scored > conceded:
            wins += 1
    return _safe_div(wins, len(h2h)) * 100


def score_home_advantage(stats: dict[str, Any]) -> float:
    """Critère 3 — Avantage domicile (15%)."""
    fixtures = stats.get("fixtures", {})
    home_played = fixtures.get("played", {}).get("home", 0)
    home_wins = fixtures.get("wins", {}).get("home", 0)
    if not home_played:
        return 50.0
    return _safe_div(home_wins, home_played) * 100


def score_goals_scored(stats: dict[str, Any]) -> float:
    """Critère 4 — Buts marqués (10%)."""
    goals = stats.get("goals", {}).get("for", {})
    avg = goals.get("average", {}).get("total")
    if avg is None:
        return 50.0
    avg = float(avg)
    # 2.5 buts/match = score parfait
    return min(avg / 2.5 * 100, 100)


def score_goals_conceded(stats: dict[str, Any]) -> float:
    """Critère 5 — Buts encaissés (10%). Moins = mieux."""
    goals = stats.get("goals", {}).get("against", {})
    avg = goals.get("average", {}).get("total")
    if avg is None:
        return 50.0
    avg = float(avg)
    return max(0, 100 - (avg / 2.5 * 100))


def score_league_position(standing: dict[str, Any], total_teams: int = 20) -> float:
    """Critère 6 — Position au classement (7%)."""
    rank = standing.get("rank", total_teams)
    return max(0, (total_teams - rank) / (total_teams - 1) * 100)


def score_injuries(squad_available: float) -> float:
    """Critère 7 — Absences/blessures (4%). squad_available = % joueurs dispo."""
    return max(0, min(squad_available * 100, 100))


def score_time_factor(hour: int) -> float:
    """Critère 8 — Facteur horaire (2%). Matchs en soirée = plus de données."""
    if 18 <= hour <= 22:
        return 80.0
    elif 14 <= hour < 18:
        return 60.0
    return 40.0


def score_league_tier(tier: int) -> float:
    """Critère 9 — Qualité de la ligue (1%). Tier 1 = plus fiable."""
    return {1: 100, 2: 75, 3: 50, 4: 25}.get(tier, 25)


# Poids des 9 critères (total = 100%)
WEIGHTS = {
    "form": 0.28,
    "h2h": 0.23,
    "home": 0.15,
    "goals_for": 0.10,
    "goals_against": 0.10,
    "position": 0.07,
    "injuries": 0.04,
    "time": 0.02,
    "league": 0.01,
}


def compute_score(
    form: float,
    h2h: float,
    home: float,
    goals_for: float,
    goals_against: float,
    position: float,
    injuries: float,
    time: float,
    league: float,
) -> float:
    total = (
        form * WEIGHTS["form"]
        + h2h * WEIGHTS["h2h"]
        + home * WEIGHTS["home"]
        + goals_for * WEIGHTS["goals_for"]
        + goals_against * WEIGHTS["goals_against"]
        + position * WEIGHTS["position"]
        + injuries * WEIGHTS["injuries"]
        + time * WEIGHTS["time"]
        + league * WEIGHTS["league"]
    )
    return round(total, 2)


def score_to_confidence(score: float) -> Literal[1, 2, 3, 4]:
    if score >= 75:
        return 4
    elif score >= 60:
        return 3
    elif score >= 45:
        return 2
    return 1


def score_to_odds(score: float) -> float:
    """Cote estimée inversement proportionnelle au score."""
    probability = score / 100
    if probability < 0.05:
        probability = 0.05
    return round(1 / probability, 2)


def should_publish(score: float) -> bool:
    """Publie uniquement les prédictions avec score >= 45."""
    return score >= 45
