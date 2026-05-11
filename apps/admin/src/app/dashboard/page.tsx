import { createClient } from "@/lib/supabase/server";
import StatCard from "@/components/stats/StatCard";

export const revalidate = 60;

async function getStats() {
  const supabase = await createClient();

  const [users, premium, predictions, wins, losses, subs] = await Promise.all([
    supabase.from("profiles").select("id", { count: "exact", head: true }),
    supabase.from("profiles").select("id", { count: "exact", head: true }).eq("role", "premium"),
    supabase.from("predictions").select("id", { count: "exact", head: true }),
    supabase.from("predictions").select("id", { count: "exact", head: true }).eq("result", "win"),
    supabase.from("predictions").select("id", { count: "exact", head: true }).eq("result", "loss"),
    supabase.from("subscriptions").select("id", { count: "exact", head: true }).eq("status", "active"),
  ]);

  const totalPreds = predictions.count ?? 0;
  const totalWins = wins.count ?? 0;
  const totalLosses = losses.count ?? 0;
  const finished = totalWins + totalLosses;
  const winRate = finished > 0 ? Math.round((totalWins / finished) * 100) : 0;

  return {
    totalUsers: users.count ?? 0,
    premiumUsers: premium.count ?? 0,
    totalPredictions: totalPreds,
    winRate,
    activeSubs: subs.count ?? 0,
  };
}

async function getRecentPredictions() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("predictions")
    .select("*, matches(home_team, away_team, match_date)")
    .order("created_at", { ascending: false })
    .limit(10);
  return data ?? [];
}

const RESULT_BADGE: Record<string, string> = {
  win: "bg-green-500/20 text-green-400",
  loss: "bg-red-500/20 text-red-400",
  void: "bg-gray-500/20 text-gray-400",
};

export default async function DashboardPage() {
  const [stats, recent] = await Promise.all([getStats(), getRecentPredictions()]);

  return (
    <div className="p-8 flex flex-col gap-8">
      <div>
        <h1 className="text-2xl font-black text-white">Dashboard</h1>
        <p className="text-gray-500 text-sm mt-1">Vue d'ensemble COTA</p>
      </div>

      {/* Stats cards */}
      <div className="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <StatCard label="Utilisateurs" value={stats.totalUsers} color="blue" />
        <StatCard label="Premium" value={stats.premiumUsers} sub={`${Math.round((stats.premiumUsers / Math.max(stats.totalUsers, 1)) * 100)}% du total`} color="yellow" />
        <StatCard label="Abonnements actifs" value={stats.activeSubs} color="green" />
        <StatCard label="Prédictions" value={stats.totalPredictions} color="blue" />
        <StatCard label="Win rate" value={`${stats.winRate}%`} sub="Objectif ≥ 55%" color={stats.winRate >= 55 ? "green" : "red"} />
      </div>

      {/* Prédictions récentes */}
      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-800">
          <h2 className="font-bold text-white">Prédictions récentes</h2>
        </div>
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase">
              <th className="px-6 py-3 text-left">Match</th>
              <th className="px-6 py-3 text-left">Prédiction</th>
              <th className="px-6 py-3 text-left">Confiance</th>
              <th className="px-6 py-3 text-left">Score</th>
              <th className="px-6 py-3 text-left">Résultat</th>
            </tr>
          </thead>
          <tbody>
            {recent.map((p: any) => (
              <tr key={p.id} className="border-t border-gray-800 hover:bg-gray-800/40 transition">
                <td className="px-6 py-3 text-gray-300">
                  {p.matches?.home_team} — {p.matches?.away_team}
                </td>
                <td className="px-6 py-3 font-semibold text-white">{p.prediction}</td>
                <td className="px-6 py-3">
                  <span className="text-yellow-400">{"★".repeat(p.confidence)}{"☆".repeat(4 - p.confidence)}</span>
                </td>
                <td className="px-6 py-3 text-gray-400">{p.score}</td>
                <td className="px-6 py-3">
                  {p.result ? (
                    <span className={`px-2 py-1 rounded-lg text-xs font-semibold ${RESULT_BADGE[p.result]}`}>
                      {p.result === "win" ? "Gagné" : p.result === "loss" ? "Perdu" : "Nul"}
                    </span>
                  ) : (
                    <span className="text-gray-600 text-xs">En attente</span>
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
