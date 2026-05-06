import os
import httpx
from fastapi import APIRouter
from pydantic import BaseModel
from supabase import create_client

router = APIRouter(prefix="/notifications", tags=["notifications"])

ONESIGNAL_APP_ID = os.getenv("ONESIGNAL_APP_ID", "")
ONESIGNAL_REST_KEY = os.getenv("ONESIGNAL_REST_KEY", "")
EXPO_PUSH_URL = "https://exp.host/--/api/v2/push/send"


def get_supabase():
    return create_client(
        os.getenv("SUPABASE_URL", ""),
        os.getenv("SUPABASE_SERVICE_ROLE_KEY", ""),
    )


class RegisterTokenRequest(BaseModel):
    user_id: str
    expo_token: str


@router.post("/register")
async def register_token(body: RegisterTokenRequest):
    supabase = get_supabase()
    supabase.table("push_tokens").upsert(
        {"user_id": body.user_id, "expo_token": body.expo_token},
        on_conflict="user_id",
    ).execute()
    return {"registered": True}


async def send_onesignal_notification(title: str, message: str, url: str = "/"):
    """Send push via OneSignal to all subscribed web users."""
    if not ONESIGNAL_APP_ID or not ONESIGNAL_REST_KEY:
        return

    payload = {
        "app_id": ONESIGNAL_APP_ID,
        "included_segments": ["All"],
        "headings": {"fr": title},
        "contents": {"fr": message},
        "url": url,
    }

    async with httpx.AsyncClient() as client:
        await client.post(
            "https://onesignal.com/api/v1/notifications",
            json=payload,
            headers={
                "Authorization": f"Basic {ONESIGNAL_REST_KEY}",
                "Content-Type": "application/json",
            },
        )


async def send_expo_notifications(title: str, body: str, tokens: list[str]):
    """Send push via Expo to all registered mobile tokens."""
    if not tokens:
        return

    messages = [
        {"to": token, "title": title, "body": body, "sound": "default"}
        for token in tokens
    ]

    async with httpx.AsyncClient() as client:
        await client.post(
            EXPO_PUSH_URL,
            json=messages,
            headers={"Content-Type": "application/json"},
        )


async def notify_predictions_today():
    """Cron 7h00 — Notifie les utilisateurs des prédictions du jour."""
    supabase = get_supabase()

    from datetime import date
    today = date.today().isoformat()
    count_res = supabase.table("predictions").select("id", count="exact").eq("match_date", today).execute()
    count = count_res.count or 0

    if count == 0:
        return

    title = "Pronostics COTA disponibles !"
    message = f"{count} pronostics analysés pour aujourd'hui — consultez votre tableau de bord."

    await send_onesignal_notification(title, message, "/predictions")

    tokens_res = supabase.table("push_tokens").select("expo_token").execute()
    tokens = [r["expo_token"] for r in (tokens_res.data or [])]
    await send_expo_notifications(title, message, tokens)


async def notify_results_evening():
    """Cron 21h00 — Notifie les utilisateurs des résultats du soir."""
    supabase = get_supabase()

    from datetime import date
    today = date.today().isoformat()
    results_res = (
        supabase.table("predictions")
        .select("result")
        .eq("match_date", today)
        .not_.is_("result", "null")
        .execute()
    )
    results = results_res.data or []
    if not results:
        return

    won = sum(1 for r in results if r["result"] == "won")
    total = len(results)
    rate = round(won / total * 100) if total else 0

    title = "Résultats COTA du soir"
    message = f"{won}/{total} pronostics gagnants aujourd'hui ({rate}% de réussite) !"

    await send_onesignal_notification(title, message, "/predictions")

    tokens_res = supabase.table("push_tokens").select("expo_token").execute()
    tokens = [r["expo_token"] for r in (tokens_res.data or [])]
    await send_expo_notifications(title, message, tokens)
