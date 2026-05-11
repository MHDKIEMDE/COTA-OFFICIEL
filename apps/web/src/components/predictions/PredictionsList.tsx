"use client";

import { useEffect, useState } from "react";
import PredictionCard from "./PredictionCard";

type Tier = "all" | "1" | "2" | "3" | "4";

export default function PredictionsList({
  isPremiumUser,
}: {
  isPremiumUser: boolean;
}) {
  const [predictions, setPredictions] = useState<any[]>([]);
  const [filtered, setFiltered] = useState<any[]>([]);
  const [tier, setTier] = useState<Tier>("all");
  const [status, setStatus] = useState<"loading" | "error" | "empty" | "success">("loading");

  useEffect(() => {
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/predictions/today`)
      .then((r) => r.json())
      .then((res) => {
        const data = res.data ?? [];
        setPredictions(data);
        setFiltered(data);
        setStatus(data.length === 0 ? "empty" : "success");
      })
      .catch(() => setStatus("error"));
  }, []);

  useEffect(() => {
    if (tier === "all") {
      setFiltered(predictions);
    } else {
      setFiltered(
        predictions.filter((p) => p.matches?.league_tier === Number(tier))
      );
    }
  }, [tier, predictions]);

  const TIERS: { value: Tier; label: string }[] = [
    { value: "all", label: "Tous" },
    { value: "1", label: "Tier 1" },
    { value: "2", label: "Tier 2" },
    { value: "3", label: "Tier 3" },
    { value: "4", label: "Tier 4" },
  ];

  return (
    <div className="flex flex-col gap-6">
      {/* Filtre tiers */}
      <div className="flex gap-2 overflow-x-auto pb-1">
        {TIERS.map((t) => (
          <button
            key={t.value}
            onClick={() => setTier(t.value)}
            className={`px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition ${
              tier === t.value
                ? "bg-green-600 text-white"
                : "bg-gray-800 text-gray-400 hover:bg-gray-700"
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* États */}
      {status === "loading" && (
        <div className="flex flex-col gap-3">
          {[1, 2, 3].map((i) => (
            <div key={i} className="bg-gray-900 rounded-2xl h-40 animate-pulse" />
          ))}
        </div>
      )}

      {status === "error" && (
        <div className="text-center py-12 text-red-400">
          <p className="text-lg font-semibold">Erreur de chargement</p>
          <p className="text-sm text-gray-500 mt-1">Vérifie ta connexion et réessaie</p>
        </div>
      )}

      {status === "empty" && (
        <div className="text-center py-12 text-gray-500">
          <p className="text-lg font-semibold">Aucune prédiction aujourd'hui</p>
          <p className="text-sm mt-1">Reviens plus tard</p>
        </div>
      )}

      {status === "success" && filtered.length === 0 && (
        <div className="text-center py-12 text-gray-500">
          <p className="text-sm">Aucun match pour ce tier aujourd'hui</p>
        </div>
      )}

      {status === "success" && filtered.length > 0 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {filtered.map((p) => (
            <PredictionCard key={p.id} prediction={p} isPremiumUser={isPremiumUser} />
          ))}
        </div>
      )}
    </div>
  );
}
