import { createClient } from "@/lib/supabase/server";

export const revalidate = 30;

export default async function SubscriptionsPage() {
  const supabase = await createClient();
  const { data: subs } = await supabase
    .from("subscriptions")
    .select("*, profiles(email, phone)")
    .order("created_at", { ascending: false })
    .limit(100);

  const PLAN_LABEL: Record<string, string> = {
    monthly: "Mensuel",
    quarterly: "Trimestriel",
    yearly: "Annuel",
  };

  const STATUS_STYLE: Record<string, string> = {
    active: "bg-green-500/20 text-green-400",
    expired: "bg-gray-500/20 text-gray-400",
    cancelled: "bg-red-500/20 text-red-400",
  };

  const totalRevenue = (subs ?? [])
    .filter((s: any) => s.status === "active")
    .reduce((acc: number, s: any) => acc + Number(s.amount), 0);

  return (
    <div className="p-8 flex flex-col gap-6">
      <div className="flex items-end justify-between">
        <div>
          <h1 className="text-2xl font-black text-white">Abonnements</h1>
          <p className="text-gray-500 text-sm mt-1">{subs?.length ?? 0} abonnements</p>
        </div>
        <div className="text-right">
          <p className="text-xs text-gray-500">Revenus actifs (MRR estimé)</p>
          <p className="text-2xl font-black text-green-400">
            {totalRevenue.toLocaleString("fr-FR")} XOF
          </p>
        </div>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-gray-500 text-xs uppercase border-b border-gray-800">
              <th className="px-5 py-3 text-left">Utilisateur</th>
              <th className="px-5 py-3 text-left">Plan</th>
              <th className="px-5 py-3 text-left">Montant</th>
              <th className="px-5 py-3 text-left">Paiement</th>
              <th className="px-5 py-3 text-left">Expiration</th>
              <th className="px-5 py-3 text-left">Statut</th>
            </tr>
          </thead>
          <tbody>
            {(subs ?? []).map((s: any) => (
              <tr key={s.id} className="border-t border-gray-800 hover:bg-gray-800/30 transition">
                <td className="px-5 py-3 text-gray-200 text-xs">
                  {s.profiles?.email ?? s.profiles?.phone ?? "—"}
                </td>
                <td className="px-5 py-3 text-white font-semibold">
                  {PLAN_LABEL[s.plan] ?? s.plan}
                </td>
                <td className="px-5 py-3 text-gray-300">
                  {Number(s.amount).toLocaleString("fr-FR")} {s.currency}
                </td>
                <td className="px-5 py-3 text-gray-400 capitalize text-xs">
                  {s.payment_method?.replace("_", " ") ?? "—"}
                </td>
                <td className="px-5 py-3 text-gray-400 text-xs">
                  {new Date(s.end_date).toLocaleDateString("fr-FR")}
                </td>
                <td className="px-5 py-3">
                  <span className={`px-2 py-1 rounded-lg text-xs font-semibold ${STATUS_STYLE[s.status]}`}>
                    {s.status}
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
