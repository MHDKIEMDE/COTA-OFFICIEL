import os
from datetime import date, datetime
from supabase import create_client, Client
from .api_football import (
    get_matches_today,
    get_team_form,
    get_head_to_head,
    get_team_stats,
    get_league_tier,
)
from .algorithm import (
    compute_score,
    score_recent_form,
    score_head_to_head,
    score_home_advantage,
    score_goals_scored,
    score_goals_conceded,
    score_league_position,
    score_injuries,
    score_time_factor,
    score_league_tier,
    score_to_confidence,
    score_to_odds,
    should_publish,
)


def get_supabase() -> Client:
    return create_client(
        os.environ["SUPABASE_URL"],
        os.environ["SUPABASE_SERVICE_ROLE_KEY"],
    )


async def generate_daily_predictions() -> list[dict]:
    fixtures = await get_matches_today()
    results = []
    supabase = get_supabase()

    for fixture in fixtures:
        try:
            league_id = fixture["league"]["id"]
            season = fixture["league"]["season"]
            home_team = fixture["teams"]["home"]
            away_team = fixture["teams"]["away"]
            match_dt = datetime.fromisoformat(fixture["fixture"]["date"])
            tier = get_league_tier(league_id)

            # Récupérer données
            form_data = await get_team_form(home_team["id"])
            h2h_data = await get_head_to_head(home_team["id"], away_team["id"])
            home_stats = await get_team_stats(home_team["id"], league_id, season)

            # Calculer critères
            form = score_recent_form(form_data, home_team["id"])
            h2h = score_head_to_head(h2h_data, home_team["id"])
            home_adv = score_home_advantage(home_stats)
            goals_for = score_goals_scored(home_stats)
            goals_against = score_goals_conceded(home_stats)
            position = score_league_position({})  # TODO: standing API
            injuries = score_injuries(0.9)         # TODO: injuries API
            time_f = score_time_factor(match_dt.hour)
            league_f = score_league_tier(tier)

            total_score = compute_score(
                form, h2h, home_adv, goals_for, goals_against,
                position, injuries, time_f, league_f
            )

            if not should_publish(total_score):
                continue

            confidence = score_to_confidence(total_score)
            odds = score_to_odds(total_score)
            is_premium = confidence <= 2

            # Upsert match
            match_row = supabase.table("matches").upsert({
                "api_id": fixture["fixture"]["id"],
                "home_team": home_team["name"],
                "away_team": away_team["name"],
                "home_logo_url": home_team.get("logo"),
                "away_logo_url": away_team.get("logo"),
                "match_date": match_dt.isoformat(),
                "status": "scheduled",
            }, on_conflict="api_id").execute()

            match_id = match_row.data[0]["id"]

            # Upsert prediction
            pred_row = supabase.table("predictions").upsert({
                "match_id": match_id,
                "prediction": "1",  # Victoire domicile
                "confidence": confidence,
                "score": total_score,
                "odds": odds,
                "is_premium": is_premium,
            }).execute()

            results.append(pred_row.data[0])

        except Exception as e:
            print(f"Erreur fixture {fixture.get('fixture', {}).get('id')}: {e}")
            continue

    # Créer le coupon du jour si assez de picks
    if len(results) >= 4:
        top_picks = sorted(results, key=lambda x: x["score"], reverse=True)[:5]
        total_odds = 1.0
        for p in top_picks:
            total_odds *= p["odds"]
        avg_confidence = round(sum(p["confidence"] for p in top_picks) / len(top_picks))

        supabase.table("daily_coupons").upsert({
            "date": date.today().isoformat(),
            "prediction_ids": [p["id"] for p in top_picks],
            "total_odds": round(total_odds, 2),
            "confidence": min(max(avg_confidence, 1), 4),
        }, on_conflict="date").execute()

    return results
