export type UserRole = "free" | "premium" | "admin";

export interface User {
  id: string;
  email: string;
  phone?: string;
  role: UserRole;
  createdAt: string;
}

export type ConfidenceLevel = 1 | 2 | 3 | 4;

export interface Prediction {
  id: string;
  matchId: string;
  homeTeam: string;
  awayTeam: string;
  league: string;
  leagueTier: 1 | 2 | 3 | 4;
  matchDate: string;
  prediction: string;
  confidence: ConfidenceLevel;
  odds: number;
  score: number;
  isPremium: boolean;
  result?: "win" | "loss" | "void";
}

export interface DailyCoupon {
  id: string;
  date: string;
  predictions: Prediction[];
  totalOdds: number;
  confidence: ConfidenceLevel;
}

export interface Subscription {
  id: string;
  userId: string;
  plan: "monthly" | "quarterly" | "yearly";
  status: "active" | "expired" | "cancelled";
  startDate: string;
  endDate: string;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
  error?: string;
}
