import type { ApiResponse, DailyCoupon, Prediction, User } from "@cota/types";

const BASE_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:3001";

async function fetcher<T>(path: string, token?: string): Promise<T> {
  const headers: HeadersInit = { "Content-Type": "application/json" };
  if (token) headers["Authorization"] = `Bearer ${token}`;
  const res = await fetch(`${BASE_URL}${path}`, { headers });
  if (!res.ok) throw new Error(await res.text());
  return res.json() as Promise<T>;
}

export const cotaApi = {
  predictions: {
    today: (token?: string) =>
      fetcher<ApiResponse<Prediction[]>>("/predictions/today", token),
    coupon: (token?: string) =>
      fetcher<ApiResponse<DailyCoupon>>("/predictions/coupon", token),
    history: (token?: string) =>
      fetcher<ApiResponse<Prediction[]>>("/predictions/history", token),
  },
  auth: {
    me: (token: string) => fetcher<ApiResponse<User>>("/auth/me", token),
  },
};
