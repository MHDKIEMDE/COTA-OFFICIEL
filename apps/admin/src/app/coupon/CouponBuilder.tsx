"use client";

import { useState } from "react";
import { SaveCouponBtn } from "./CouponActions";

type Pick = {
  home_team: string;
  away_team: string;
  league: string;
  prediction: string;
  odds: number;
  prediction_id?: string;
};

export default function CouponBuilder({ predictions }: { predictions: any[] }) {
  const [picks, setPicks] = useState<Pick[]>([]);
  const [confidence, setConfidence] = useState(3);

  function togglePick(p: any) {
    const match = p.matches ?? {};
    const id = p.id;
    const exists = picks.find((pk) => pk.prediction_id === id);
    if (exists) {
      setPicks(picks.filter((pk) => pk.prediction_id !== id));
    } else {
      setPicks([
        ...picks,
        {
          prediction_id: id,
          home_team: match.home_team ?? "—",
          away_team: match.away_team ?? "—",
          league: match.leagues?.name ?? match.league_name ?? "—",
          prediction: p.prediction ?? "—",
          odds: p.odds ?? 1,
        },
      ]);
    }
  }

  const totalOdds = picks.reduce((acc, pk) => acc * (pk.odds || 1), 1);
  const isSelected = (id: string) => picks.some((pk) => pk.prediction_id === id);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      {/* Left — select predictions */}
      <div className="flex flex-col gap-3">
        <h2 className="text-sm font-bold text-gray-400 uppercase tracking-widest">
          Sélectionner les picks ({predictions.length} disponibles)
        </h2>
        <div className="flex flex-col gap-1 max-h-[560px] overflow-y-auto pr-1">
          {predictions.length === 0 && (
            <p className="text-gray-600 text-sm py-8 text-center">Aucun pronostic disponible aujourd'hui</p>
          )}
          {predictions.map((p: any) => {
            const match = p.matches ?? {};
            const selected = isSelected(p.id);
            return (
              <button
                key={p.id}
                onClick={() => togglePick(p)}
                className={`text-left px-4 py-3 rounded-xl border transition flex items-start gap-3 ${
                  selected
                    ? "bg-blue-600/20 border-blue-500/50 text-white"
                    : "bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800"
                }`}
              >
                <div
                  className={`mt-0.5 w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center text-xs font-bold ${
                    selected ? "bg-blue-600 border-blue-600 text-white" : "border-gray-600"
                  }`}
                >
                  {selected ? "✓" : ""}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="text-xs text-blue-400 font-semibold mb-0.5">
                    {match.leagues?.name ?? "—"}
                  </div>
                  <div className="font-bold text-sm truncate">
                    {match.home_team} — {match.away_team}
                  </div>
                  <div className="flex items-center gap-2 mt-1">
                    <span className="bg-blue-600/20 text-blue-300 text-xs font-bold px-2 py-0.5 rounded">
                      {p.prediction}
                    </span>
                    <span className="text-green-400 text-xs font-bold">x{p.odds?.toFixed(2)}</span>
                    {p.is_premium && (
                      <span className="text-yellow-400 text-xs">PRO</span>
                    )}
                  </div>
                </div>
              </button>
            );
          })}
        </div>
      </div>

      {/* Right — coupon preview */}
      <div className="flex flex-col gap-4">
        <h2 className="text-sm font-bold text-gray-400 uppercase tracking-widest">
          Coupon ({picks.length} sélections)
        </h2>

        {/* Picks list */}
        <div className="flex flex-col gap-1 min-h-[200px]">
          {picks.length === 0 && (
            <div className="flex-1 flex items-center justify-center border border-dashed border-gray-800 rounded-xl py-12">
              <p className="text-gray-600 text-sm">Clique sur des pronostics pour les ajouter</p>
            </div>
          )}
          {picks.map((pk, i) => (
            <div key={i} className="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 flex items-center gap-3">
              <div className="w-5 h-5 rounded-full bg-blue-600/30 text-blue-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                {i + 1}
              </div>
              <div className="flex-1 min-w-0">
                <div className="text-xs text-gray-500 mb-0.5">{pk.league}</div>
                <div className="text-sm font-bold truncate">{pk.home_team} — {pk.away_team}</div>
                <div className="flex items-center gap-2 mt-1">
                  <span className="bg-blue-600/20 text-blue-300 text-xs font-bold px-2 py-0.5 rounded">{pk.prediction}</span>
                  <span className="text-green-400 text-xs font-bold">x{pk.odds.toFixed(2)}</span>
                </div>
              </div>
              <button
                onClick={() => setPicks(picks.filter((_, j) => j !== i))}
                className="text-gray-600 hover:text-red-400 transition text-lg leading-none"
              >
                ×
              </button>
            </div>
          ))}
        </div>

        {/* Totals */}
        {picks.length > 0 && (
          <div className="bg-gray-900 border border-gray-800 rounded-xl p-4 flex flex-col gap-3">
            <div className="flex items-center justify-between">
              <span className="text-gray-400 text-sm">Cote totale</span>
              <span className="text-green-400 text-2xl font-black">x{totalOdds.toFixed(2)}</span>
            </div>

            <div className="flex items-center justify-between">
              <span className="text-gray-400 text-sm">Confiance</span>
              <div className="flex gap-1">
                {[1, 2, 3, 4].map((n) => (
                  <button
                    key={n}
                    onClick={() => setConfidence(n)}
                    className={`w-8 h-8 rounded-lg text-sm font-bold transition border ${
                      confidence >= n
                        ? "bg-yellow-500/20 border-yellow-500/50 text-yellow-400"
                        : "bg-gray-800 border-gray-700 text-gray-600"
                    }`}
                  >
                    ★
                  </button>
                ))}
              </div>
            </div>

            <div className="flex items-center justify-between text-xs text-gray-600">
              <span>Gain potentiel (mise 500 XOF)</span>
              <span>{(totalOdds * 500).toFixed(0)} XOF</span>
            </div>
          </div>
        )}

        <SaveCouponBtn picks={picks} totalOdds={totalOdds} confidence={confidence} />
      </div>
    </div>
  );
}
