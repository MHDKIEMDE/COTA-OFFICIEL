import { createClient } from "@/lib/supabase/server";
import UpdateResultForm from "./UpdateResultForm";

export const revalidate = 0;

export default async function PredictionsAdminPage() {
  const supabase = await createClient();
  const { data: predictions } = await supabase
    .from("predictions")
    .select("*, matches(home_team, away_team, match_date, leagues(name))")
    .order("created_at", { ascending: false })
    .limit(50);

  const RESULT_STYLE: Record<string, string> = {
    win: "text-green-400",
    loss: "text-red-400",
    void: "text-gray-400",
  };

  return (
    <div className="p-8 flex flex-col gap-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-black text-white">Prédictions</h1>
          <p className="text-gray-500 text-sm mt-1">{predictions?.length ?? 0} prédictions</p>
        </div>
        <a
          href={`${process.env.NEXT_PUBLIC_API_URL}/predictions/generate`}
          target="_blank"
          className="bg-green-600 hover:bg-green-500 text-white text-sm font-bold px-4 py-2 rounded-xl transition"
        >
          ⚡ Générer aujourd'hui
        </a>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase border-b border-gray-800">
              <th className="px-5 py-3 text-left">Match</th>
              <th className="px-5 py-3 text-left">Ligue</th>
              <th className="px-5 py-3 text-left">Pick</th>
              <th className="px-5 py-3 text-left">Score</th>
              <th className="px-5 py-3 text-left">Cote</th>
              <th className="px-5 py-3 text-left">Premium</th>
              <th className="px-5 py-3 text-left">Résultat</th>
            </tr>
          </thead>
          <tbody>
            {(predictions ?? []).map((p: any) => (
              <tr key={p.id} className="border-t border-gray-800 hover:bg-gray-800/30 transition">
                <td className="px-5 py-3 text-gray-200 text-xs">
                  <div>{p.matches?.home_team} — {p.matches?.away_team}</div>
                  <div className="text-gray-600">
                    {p.matches?.match_date
                      ? new Date(p.matches.match_date).toLocaleDateString("fr-FR")
                      : ""}
                  </div>
                </td>
                <td className="px-5 py-3 text-gray-400 text-xs">{p.matches?.leagues?.name ?? "—"}</td>
                <td className="px-5 py-3 font-bold text-white">{p.prediction}</td>
                <td className="px-5 py-3 text-gray-400">{p.score}</td>
                <td className="px-5 py-3 text-gray-400">{p.odds?.toFixed(2)}</td>
                <td className="px-5 py-3">
                  {p.is_premium
                    ? <span className="text-yellow-400 text-xs font-semibold">Premium</span>
                    : <span className="text-gray-600 text-xs">Gratuit</span>}
                </td>
                <td className="px-5 py-3">
                  {p.result ? (
                    <span className={`font-semibold text-xs ${RESULT_STYLE[p.result]}`}>
                      {p.result === "win" ? "✓ Gagné" : p.result === "loss" ? "✗ Perdu" : "Nul"}
                    </span>
                  ) : (
                    <UpdateResultForm predictionId={p.id} />
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
