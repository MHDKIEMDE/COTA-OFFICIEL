import { useState, useCallback } from "react";

export type CouponPick = {
  id: string;
  home_team: string;
  away_team: string;
  league: string;
  prediction: string;
  odds: number;
  is_premium: boolean;
};

export function useMyCoupon() {
  const [picks, setPicks] = useState<CouponPick[]>([]);

  const toggle = useCallback((pick: CouponPick) => {
    setPicks((prev) => {
      const exists = prev.find((p) => p.id === pick.id);
      if (exists) return prev.filter((p) => p.id !== pick.id);
      return [...prev, pick];
    });
  }, []);

  const remove = useCallback((id: string) => {
    setPicks((prev) => prev.filter((p) => p.id !== id));
  }, []);

  const clear = useCallback(() => setPicks([]), []);

  const has = useCallback((id: string) => picks.some((p) => p.id === id), [picks]);

  const totalOdds = picks.reduce((acc, p) => acc * (p.odds || 1), 1);

  return { picks, toggle, remove, clear, has, totalOdds };
}
