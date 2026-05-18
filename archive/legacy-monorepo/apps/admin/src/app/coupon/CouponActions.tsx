"use client";

import { useState } from "react";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000";

export function GenerateCouponBtn() {
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState("");

  async function generate() {
    setLoading(true);
    setMsg("");
    try {
      const r = await fetch(`${API_URL}/predictions/coupon/generate`, { method: "POST" });
      if (r.ok) setMsg("✅ Coupon généré avec succès");
      else setMsg("❌ Erreur lors de la génération");
    } catch {
      setMsg("❌ API inaccessible");
    }
    setLoading(false);
  }

  return (
    <div className="flex items-center gap-4">
      <button
        onClick={generate}
        disabled={loading}
        className="bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition flex items-center gap-2"
      >
        {loading ? (
          <span className="animate-spin">⟳</span>
        ) : (
          <span>⚡</span>
        )}
        Générer le coupon IA
      </button>
      {msg && <span className="text-sm text-gray-400">{msg}</span>}
    </div>
  );
}

export function SaveCouponBtn({
  picks,
  totalOdds,
  confidence,
}: {
  picks: any[];
  totalOdds: number;
  confidence: number;
}) {
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState("");

  async function save() {
    if (picks.length === 0) { setMsg("⚠️ Sélectionne au moins un pick"); return; }
    setLoading(true);
    setMsg("");
    try {
      const r = await fetch(`${API_URL}/predictions/coupon`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ picks, total_odds: totalOdds, confidence }),
      });
      if (r.ok) setMsg("✅ Coupon sauvegardé");
      else setMsg("❌ Erreur lors de la sauvegarde");
    } catch {
      setMsg("❌ API inaccessible");
    }
    setLoading(false);
  }

  return (
    <div className="flex items-center gap-4">
      <button
        onClick={save}
        disabled={loading}
        className="bg-green-600 hover:bg-green-500 disabled:opacity-50 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition"
      >
        {loading ? "Sauvegarde…" : "💾 Sauvegarder le coupon"}
      </button>
      {msg && <span className="text-sm text-gray-400">{msg}</span>}
    </div>
  );
}
