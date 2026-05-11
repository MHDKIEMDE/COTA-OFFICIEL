import { createClient } from "@/lib/supabase/server";
import { GenerateCouponBtn } from "./CouponActions";
import CouponBuilder from "./CouponBuilder";

export const revalidate = 0;

export default async function CouponAdminPage() {
  const supabase = await createClient();

  // Today's predictions (no result yet = pending)
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const { data: predictions } = await supabase
    .from("predictions")
    .select("*, matches(home_team, away_team, match_date, league_name, leagues(name))")
    .gte("created_at", today.toISOString())
    .order("created_at", { ascending: false });

  // Current coupon
  const { data: coupon } = await supabase
    .from("coupons")
    .select("*")
    .order("created_at", { ascending: false })
    .limit(1)
    .maybeSingle();

  const picks = coupon?.picks ?? [];

  return (
    <div className="p-8 flex flex-col gap-8">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-black text-white">Coupon du jour</h1>
          <p className="text-gray-500 text-sm mt-1">
            Composez manuellement ou laissez l'IA générer le combiné
          </p>
        </div>
        <GenerateCouponBtn />
      </div>

      {/* Current coupon status */}
      {coupon && (
        <div className="bg-gray-900 border border-blue-500/30 rounded-2xl p-5 flex items-center gap-6">
          <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
          <div>
            <p className="text-xs text-gray-500 uppercase tracking-widest mb-0.5">Coupon actif</p>
            <p className="text-white font-bold">{picks.length} sélections · cote x{coupon.total_odds?.toFixed(2) ?? "—"}</p>
          </div>
          <div className="ml-auto flex gap-6">
            {picks.slice(0, 3).map((pk: any, i: number) => (
              <div key={i} className="text-center">
                <p className="text-xs text-gray-500 truncate max-w-[90px]">{pk.home_team} — {pk.away_team}</p>
                <p className="text-blue-400 text-xs font-bold mt-0.5">{pk.prediction}</p>
              </div>
            ))}
            {picks.length > 3 && (
              <div className="text-center">
                <p className="text-gray-500 text-xs">+{picks.length - 3} autres</p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Builder */}
      <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <CouponBuilder predictions={predictions ?? []} />
      </div>
    </div>
  );
}
