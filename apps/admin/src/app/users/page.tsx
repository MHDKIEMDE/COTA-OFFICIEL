import { createClient } from "@/lib/supabase/server";

export const revalidate = 30;

export default async function UsersPage() {
  const supabase = await createClient();
  const { data: users } = await supabase
    .from("profiles")
    .select("*, subscriptions(plan, status, end_date)")
    .order("created_at", { ascending: false })
    .limit(100);

  const ROLE_BADGE: Record<string, string> = {
    admin: "bg-red-500/20 text-red-400",
    premium: "bg-yellow-500/20 text-yellow-400",
    free: "bg-gray-500/20 text-gray-400",
  };

  return (
    <div className="p-8 flex flex-col gap-6">
      <div>
        <h1 className="text-2xl font-black text-white">Utilisateurs</h1>
        <p className="text-gray-500 text-sm mt-1">{users?.length ?? 0} comptes enregistrés</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase border-b border-gray-800">
              <th className="px-6 py-3 text-left">Email</th>
              <th className="px-6 py-3 text-left">Rôle</th>
              <th className="px-6 py-3 text-left">Abonnement</th>
              <th className="px-6 py-3 text-left">Parrainage</th>
              <th className="px-6 py-3 text-left">Inscription</th>
            </tr>
          </thead>
          <tbody>
            {(users ?? []).map((u: any) => {
              const activeSub = u.subscriptions?.find((s: any) => s.status === "active");
              return (
                <tr key={u.id} className="border-t border-gray-800 hover:bg-gray-800/40 transition">
                  <td className="px-6 py-3 text-gray-200">{u.email ?? u.phone ?? "—"}</td>
                  <td className="px-6 py-3">
                    <span className={`px-2 py-1 rounded-lg text-xs font-semibold ${ROLE_BADGE[u.role]}`}>
                      {u.role}
                    </span>
                  </td>
                  <td className="px-6 py-3 text-gray-400 text-xs">
                    {activeSub
                      ? `${activeSub.plan} — expire ${new Date(activeSub.end_date).toLocaleDateString("fr-FR")}`
                      : "—"}
                  </td>
                  <td className="px-6 py-3 text-gray-500 font-mono text-xs">{u.referral_code}</td>
                  <td className="px-6 py-3 text-gray-500 text-xs">
                    {new Date(u.created_at).toLocaleDateString("fr-FR")}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}
