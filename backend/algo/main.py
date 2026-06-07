from dotenv import load_dotenv
load_dotenv()

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from apscheduler.schedulers.asyncio import AsyncIOScheduler
from app.routes.predictions import router as predictions_router
from app.routes.payments import router as payments_router
from app.routes.notifications import router as notifications_router
from app.routes.notifications import notify_predictions_today, notify_results_evening
from app.services.prediction_service import generate_daily_predictions

app = FastAPI(title="COTA Algo API", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(predictions_router)
app.include_router(payments_router)
app.include_router(notifications_router)

# Cron : génération automatique chaque jour à 7h00
scheduler = AsyncIOScheduler()

@app.on_event("startup")
async def startup():
    scheduler.add_job(
        generate_daily_predictions,
        "cron",
        hour=7,
        minute=0,
        id="daily_predictions",
    )
    scheduler.add_job(
        notify_predictions_today,
        "cron",
        hour=7,
        minute=15,
        id="notify_predictions",
    )
    scheduler.add_job(
        notify_results_evening,
        "cron",
        hour=21,
        minute=0,
        id="notify_results",
    )
    scheduler.start()

@app.on_event("shutdown")
async def shutdown():
    scheduler.shutdown()

@app.get("/health")
def health():
    return {"status": "ok", "service": "cota-algo"}
