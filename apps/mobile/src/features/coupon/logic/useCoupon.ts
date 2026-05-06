import { useEffect, useState } from "react";

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000";
type Status = "loading" | "error" | "empty" | "success";

export function useCoupon() {
  const [coupon, setCoupon] = useState<any>(null);
  const [status, setStatus] = useState<Status>("loading");

  useEffect(() => {
    fetch(`${API_URL}/predictions/coupon`)
      .then((r) => {
        if (r.status === 404) { setStatus("empty"); return null; }
        return r.json();
      })
      .then((res) => {
        if (!res) return;
        setCoupon(res.data);
        setStatus("success");
      })
      .catch(() => setStatus("error"));
  }, []);

  return { coupon, status };
}
