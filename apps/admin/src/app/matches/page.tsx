import { createClient } from "@/lib/supabase/server";

export const revalidate = 30;

export default async function MatchesPage() {
  const supabase = await createClient();
  const { data: matches } = await supabase
    .from("matches")
    .select("*, leagues(name, tier)")
    .order("match_date", { ascending: false })
    .limit(100);

  const STATUS_BADGE: Record<string, string> = {
    scheduled: "bg-blue-500/20 text-blue-400",
    live: "bg-green-500/20 text-green-400",
    finished: "bg-gray-500/20 text-gray-400",
    cancelled: "bg-red-500/20 text-red-400",
  };

  return (
    <div className="p-8 flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-black text-white">Matchs</h1>
        <p className="text-gray-500 text-sm mt-1">{matches?.length ?? 0} matchs</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase border-b border-gray-800">
              <th className="px-5 py-3 text-left">Match</th>
              <th className="px-5 py-3 text-left">Ligue</th>
              <th className="px-5 py-3 text-left">Date</th>
              <th className="px-5 py-3 text-left">Score</th>
              <th className="px-5 py-3 text-left">Statut</th>
            </tr>
          </thead>
          <tbody>
            {(matches ?? []).map((m: any) => (
              <tr key={m.id} className="border-t border-gray-800 hover:bg-gray-800/30 transition">
                <td className="px-5 py-3 text-gray-200">
                  {m.home_team} — {m.away_team}
                </td>
                <td className="px-5 py-3 text-gray-400 text-xs">
                  {m.leagues?.name ?? "—"}
                  {m.leagues?.tier && (
                    <span className="ml-1 text-gray-600">T{m.leagues.tier}</span>
                  )}
                </td>
                <td className="px-5 py-3 text-gray-400 text-xs">
                  {new Date(m.match_date).toLocaleString("fr-FR", { dateStyle: "short", timeStyle: "short" })}
                </td>
                <td className="px-5 py-3 text-white font-mono">
                  {m.home_score !== null && m.away_score !== null
                    ? `${m.home_score} - ${m.away_score}`
                    : "—"}
                </td>
                <td className="px-5 py-3">
                  <span className={`px-2 py-1 rounded-lg text-xs font-semibold ${STATUS_BADGE[m.status]}`}>
                    {m.status}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
