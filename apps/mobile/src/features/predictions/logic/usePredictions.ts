import { useEffect, useState } from "react";
import { SEED_PREDICTIONS } from "@/data/seeds";

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000";

type Status = "loading" | "error" | "empty" | "success";

export function usePredictions() {
  const [predictions, setPredictions] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [tier, setTier] = useState<number | null>(null);
  const [status, setStatus] = useState<Status>("loading");

  useEffect(() => {
    fetch(`${API_URL}/predictions/today`)
      .then((r) => r.json())
      .then((res) => {
        const data = res.data ?? [];
        if (data.length === 0) {
          setPredictions(SEED_PREDICTIONS);
          setFiltered(SEED_PREDICTIONS);
          setStatus("success");
        } else {
          setPredictions(data);
          setFiltered(data);
          setStatus("success");
        }
      })
      .catch(() => {
        setPredictions(SEED_PREDICTIONS);
        setFiltered(SEED_PREDICTIONS);
        setStatus("success");
      });
  }, []);

  useEffect(() => {
    if (tier === null) setFiltered(predictions);
    else setFiltered(predictions.filter((p) => p.matches?.league_tier === tier));
  }, [tier, predictions]);

  return { predictions: filtered, tier, setTier, status };
}
