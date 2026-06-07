import os
import httpx
from fastapi import APIRouter, HTTPException, Request
from pydantic import BaseModel
from supabase import create_client

router = APIRouter(prefix="/payments", tags=["payments"])

PAYDUNYA_BASE = "https://app.paydunya.com/api/v1"

HEADERS = {
    "PAYDUNYA-MASTER-KEY": os.getenv("PAYDUNYA_MASTER_KEY", ""),
    "PAYDUNYA-PRIVATE-KEY": os.getenv("PAYDUNYA_PRIVATE_KEY", ""),
    "PAYDUNYA-TOKEN": os.getenv("PAYDUNYA_TOKEN", ""),
    "PAYDUNYA-PUBLIC-KEY": os.getenv("PAYDUNYA_PUBLIC_KEY", ""),
    "Content-Type": "application/json",
}

PLANS = {
    "mensuel": {"amount": 2500, "label": "Abonnement mensuel COTA Premium", "months": 1},
    "trimestriel": {"amount": 6500, "label": "Abonnement trimestriel COTA Premium", "months": 3},
    "annuel": {"amount": 20000, "label": "Abonnement annuel COTA Premium", "months": 12},
}


class InitiatePaymentRequest(BaseModel):
    plan: str
    user_id: str
    user_email: str
    user_name: str


def get_supabase():
    return create_client(
        os.getenv("SUPABASE_URL", ""),
        os.getenv("SUPABASE_SERVICE_ROLE_KEY", ""),
    )


@router.post("/initiate")
async def initiate_payment(body: InitiatePaymentRequest):
    plan = PLANS.get(body.plan)
    if not plan:
        raise HTTPException(status_code=400, detail="Plan invalide")

    web_url = os.getenv("WEB_URL", "https://cota.app")
    backend_url = os.getenv("BACKEND_URL", "https://api.cota.app")

    payload = {
        "invoice": {
            "total_amount": plan["amount"],
            "description": plan["label"],
        },
        "store": {
            "name": "COTA Premium",
        },
        "actions": {
            "cancel_url": f"{web_url}/subscribe?status=cancel",
            "return_url": f"{web_url}/subscribe?status=success",
            "callback_url": f"{backend_url}/payments/webhook",
        },
        "custom_data": {
            "user_id": body.user_id,
            "plan": body.plan,
            "months": plan["months"],
        },
        "customer": {
            "name": body.user_name,
            "email": body.user_email,
        },
    }

    async with httpx.AsyncClient() as client:
        resp = await client.post(
            f"{PAYDUNYA_BASE}/checkout-invoice/create",
            json=payload,
            headers=HEADERS,
        )

    if resp.status_code != 200:
        raise HTTPException(status_code=502, detail="Erreur Paydunya")

    data = resp.json()
    if data.get("response_code") != "00":
        raise HTTPException(status_code=400, detail=data.get("response_text", "Erreur"))

    return {
        "checkout_url": data["response_text"],
        "invoice_token": data.get("token"),
    }


@router.post("/webhook")
async def paydunya_webhook(request: Request):
    data = await request.json()

    if data.get("status") != "completed":
        return {"received": True}

    custom = data.get("custom_data", {})
    user_id = custom.get("user_id")
    months = int(custom.get("months", 1))

    if not user_id:
        return {"received": True}

    supabase = get_supabase()

    from datetime import datetime, timezone, timedelta
    now = datetime.now(timezone.utc)
    expires_at = now + timedelta(days=30 * months)

    supabase.table("subscriptions").upsert({
        "user_id": user_id,
        "plan": custom.get("plan"),
        "status": "active",
        "started_at": now.isoformat(),
        "expires_at": expires_at.isoformat(),
        "paydunya_token": data.get("invoice", {}).get("token"),
    }, on_conflict="user_id").execute()

    supabase.table("profiles").update({"role": "premium"}).eq("id", user_id).execute()

    return {"received": True}


@router.get("/plans")
def get_plans():
    return {
        key: {
            "amount": v["amount"],
            "label": v["label"],
            "months": v["months"],
        }
        for key, v in PLANS.items()
    }
