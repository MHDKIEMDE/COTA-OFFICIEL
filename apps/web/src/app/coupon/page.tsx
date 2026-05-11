"use client";

import CouponCard from "@/components/predictions/CouponCard";
import { useEffect, useState } from "react";

export default function CouponPage() {
  const [coupon, setCoupon] = useState<any>(null);
  const [status, setStatus] = useState<"loading" | "error" | "empty" | "success">("loading");

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/predictions/coupon`)
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

  return (
    <main className="min-h-screen bg-gray-950 text-white">
      <div className="max-w-2xl mx-auto px-4 py-8">
        <h1 className="text-3xl font-black mb-8">Coupon combiné</h1>

        {status === "loading" && (
          <div className="bg-gray-900 rounded-2xl h-64 animate-pulse" />
        )}

        {status === "error" && (
          <div className="text-center py-12 text-red-400">
            <p className="font-semibold">Erreur de chargement</p>
          </div>
        )}

        {status === "empty" && (
          <div className="text-center py-12 text-gray-500">
            <p className="text-lg font-semibold">Pas de coupon aujourd'hui</p>
            <p className="text-sm mt-1">L'algorithme génère le coupon chaque matin à 7h00</p>
          </div>
        )}

        {status === "success" && coupon && (
          <CouponCard coupon={coupon} />
        )}
      </div>
    </main>
  );
}
