from fastapi import APIRouter, HTTPException
from datetime import date
from ..services.prediction_service import generate_daily_predictions, get_supabase

router = APIRouter(prefix="/predictions", tags=["predictions"])


@router.get("/today")
async def get_today():
    supabase = get_supabase()
    today = date.today().isoformat()
    res = (
        supabase.table("predictions")
        .select("*, matches(*)")
        .gte("matches.match_date", f"{today}T00:00:00")
        .lte("matches.match_date", f"{today}T23:59:59")
        .execute()
    )
    return {"data": res.data}


@router.get("/coupon")
async def get_coupon():
    supabase = get_supabase()
    today = date.today().isoformat()
    res = (
        supabase.table("daily_coupons")
        .select("*")
        .eq("date", today)
        .single()
        .execute()
    )
    if not res.data:
        raise HTTPException(status_code=404, detail="Pas de coupon aujourd'hui")
    return {"data": res.data}


@router.get("/history")
async def get_history(page: int = 1, limit: int = 20):
    supabase = get_supabase()
    offset = (page - 1) * limit
    res = (
        supabase.table("predictions")
        .select("*, matches(*)")
        .not_.is_("result", "null")
        .order("created_at", desc=True)
        .range(offset, offset + limit - 1)
        .execute()
    )
    return {"data": res.data}


@router.post("/generate")
async def trigger_generation():
    """Endpoint admin — génère les prédictions du jour."""
    results = await generate_daily_predictions()
    return {"message": f"{len(results)} prédictions générées", "data": results}
