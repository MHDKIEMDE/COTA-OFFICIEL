from pydantic import BaseModel
from typing import Literal, Optional
from datetime import datetime


class MatchData(BaseModel):
    api_id: int
    home_team: str
    away_team: str
    league_name: str
    league_tier: int
    match_date: datetime
    home_logo: Optional[str] = None
    away_logo: Optional[str] = None


class PredictionResult(BaseModel):
    match_id: str
    home_team: str
    away_team: str
    league: str
    league_tier: int
    match_date: datetime
    prediction: str
    confidence: Literal[1, 2, 3, 4]
    score: float
    odds: float
    is_premium: bool
    analysis: Optional[str] = None


class DailyCoupon(BaseModel):
    date: str
    predictions: list[PredictionResult]
    total_odds: float
    confidence: Literal[1, 2, 3, 4]
