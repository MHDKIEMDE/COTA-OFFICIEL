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
    <main className="min-h-screen bg-[#000000] text-white">
      <div className="max-w-2xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <p className="text-[#F9FF00] text-xs font-bold uppercase tracking-widest mb-2">
            🎯 Coupon combiné
          </p>
          <h1 className="text-3xl font-black text-white">Coupon IA</h1>
          <p className="text-[#888888] mt-1 text-sm">Sélection automatique du jour</p>
        </div>

        {/* Loading */}
        {status === "loading" && (
          <div className="bg-[#111111] border border-[#1E1E1E] rounded-2xl h-64 animate-pulse" />
        )}

        {/* Erreur */}
        {status === "error" && (
          <div className="bg-[#111111] border border-[#FF3B30]/30 rounded-2xl p-8 text-center">
            <p className="text-[#FF3B30] text-2xl mb-3">⚠️</p>
            <p className="font-semibold text-white">Erreur de chargement</p>
            <p className="text-[#888888] text-sm mt-1">Vérifie ta connexion et réessaie</p>
          </div>
        )}

        {/* Vide */}
        {status === "empty" && (
          <div className="bg-[#111111] border border-[#1E1E1E] rounded-2xl p-8 text-center">
            <p className="text-4xl mb-4">🎯</p>
            <p className="text-lg font-bold text-white">Pas de coupon aujourd'hui</p>
            <p className="text-sm text-[#888888] mt-1">
              L'algorithme génère le coupon chaque matin à 8h00
            </p>
          </div>
        )}

        {/* Coupon */}
        {status === "success" && coupon && <CouponCard coupon={coupon} />}
      </div>
    </main>
  );
}
