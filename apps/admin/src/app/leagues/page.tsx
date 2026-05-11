import { createClient } from "@/lib/supabase/server";
import ToggleLeagueForm from "./ToggleLeagueForm";

export const revalidate = 0;

export default async function LeaguesPage() {
  const supabase = await createClient();
  const { data: leagues } = await supabase
    .from("leagues")
    .select("*")
    .order("tier", { ascending: true });

  return (
    <div className="p-8 flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-black text-white">Compétitions</h1>
        <p className="text-gray-500 text-sm mt-1">{leagues?.length ?? 0} ligues configurées</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase border-b border-gray-800">
              <th className="px-5 py-3 text-left">Ligue</th>
              <th className="px-5 py-3 text-left">Pays</th>
              <th className="px-5 py-3 text-left">Tier</th>
              <th className="px-5 py-3 text-left">API ID</th>
              <th className="px-5 py-3 text-left">Actif</th>
            </tr>
          </thead>
          <tbody>
            {(leagues ?? []).map((l: any) => (
              <tr key={l.id} className="border-t border-gray-800 hover:bg-gray-800/30 transition">
                <td className="px-5 py-3 text-white font-semibold">{l.name}</td>
                <td className="px-5 py-3 text-gray-400 text-xs">{l.country ?? "—"}</td>
                <td className="px-5 py-3">
                  <span className={`px-2 py-1 rounded-lg text-xs font-bold ${
                    l.tier === 1 ? "bg-yellow-500/20 text-yellow-400" :
                    l.tier === 2 ? "bg-blue-500/20 text-blue-400" :
                    l.tier === 3 ? "bg-purple-500/20 text-purple-400" :
                    "bg-gray-500/20 text-gray-400"
                  }`}>
                    Tier {l.tier}
                  </span>
                </td>
                <td className="px-5 py-3 text-gray-500 font-mono text-xs">{l.api_id ?? "—"}</td>
                <td className="px-5 py-3">
                  <ToggleLeagueForm leagueId={l.id} isActive={l.is_active} />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
