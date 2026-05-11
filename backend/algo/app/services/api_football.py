import httpx
import os
from datetime import date
from typing import Any

BASE_URL = "https://v3.football.api-sports.io"

# Ligues populaires Tier 1-4 (Afrique de l'Ouest + Europe top)
LEAGUE_TIERS: dict[int, int] = {
    # Tier 1 — Europe top
    39: 1,   # Premier League
    140: 1,  # La Liga
    135: 1,  # Serie A
    61: 1,   # Ligue 1
    78: 1,   # Bundesliga
    2: 1,    # Champions League
    # Tier 2 — Europe second
    848: 2,  # Europa League
    94: 2,   # Primeira Liga
    88: 2,   # Eredivisie
    # Tier 3 — Afrique
    12: 3,   # CAF Champions League
    29: 3,   # AFCON
    # Tier 4 — Afrique de l'Ouest
    764: 4,  # Senegal Premier League
    763: 4,  # Mali Premier League
}


def _headers() -> dict[str, str]:
    return {
        "x-apisports-key": os.environ["API_FOOTBALL_KEY"],
        "x-rapidapi-host": "v3.football.api-sports.io",
    }


async def get_matches_today() -> list[dict[str, Any]]:
    today = date.today().isoformat()
    async with httpx.AsyncClient() as client:
        res = await client.get(
            f"{BASE_URL}/fixtures",
            headers=_headers(),
            params={"date": today},
            timeout=10,
        )
        res.raise_for_status()
        data = res.json()
        return data.get("response", [])


async def get_team_stats(team_id: int, league_id: int, season: int) -> dict[str, Any]:
    async with httpx.AsyncClient() as client:
        res = await client.get(
            f"{BASE_URL}/teams/statistics",
            headers=_headers(),
            params={"team": team_id, "league": league_id, "season": season},
            timeout=10,
        )
        res.raise_for_status()
        return res.json().get("response", {})


async def get_head_to_head(home_id: int, away_id: int, last: int = 10) -> list[dict[str, Any]]:
    async with httpx.AsyncClient() as client:
        res = await client.get(
            f"{BASE_URL}/fixtures/headtohead",
            headers=_headers(),
            params={"h2h": f"{home_id}-{away_id}", "last": last},
            timeout=10,
        )
        res.raise_for_status()
        return res.json().get("response", [])


async def get_team_form(team_id: int, last: int = 5) -> list[dict[str, Any]]:
    async with httpx.AsyncClient() as client:
        res = await client.get(
            f"{BASE_URL}/fixtures",
            headers=_headers(),
            params={"team": team_id, "last": last, "status": "FT"},
            timeout=10,
        )
        res.raise_for_status()
        return res.json().get("response", [])


def get_league_tier(league_id: int) -> int:
    return LEAGUE_TIERS.get(league_id, 4)
