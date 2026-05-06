import { useEffect, useState } from "react";

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000";
type Status = "loading" | "error" | "empty" | "success";

export function useCoupon() {
  const [coupon, setCoupon] = useState<any>(null);
  const [status, setStatus] = useState<Status>("loading");

  async function load() {
    setStatus("loading");
    try {
      const r = await fetch(`${API_URL}/predictions/coupon`);
      if (r.status === 404) { setStatus("empty"); return; }
      const res = await r.json();
      setCoupon(res.data);
      setStatus("success");
    } catch {
      setStatus("error");
    }
  }

  useEffect(() => { load(); }, []);

  return { coupon, status, reload: load };
}
