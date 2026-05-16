import ConfidenceStars from "@/components/shared/ConfidenceStars";

interface CouponPick {
  home_team: string;
  away_team: string;
  prediction: string;
  odds: number;
  confidence: 1 | 2 | 3 | 4;
}

interface Coupon {
  date: string;
  total_odds: number;
  confidence: 1 | 2 | 3 | 4;
  picks: CouponPick[];
}

export default function CouponCard({ coupon }: { coupon: Coupon }) {
  return (
    <div className="bg-[#111111] border border-[#F9FF00]/25 rounded-2xl p-6 flex flex-col gap-5"
         style={{ borderLeft: "3px solid #F9FF00" }}>
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-black text-white">Coupon IA du jour</h2>
          <p className="text-[#F9FF00] text-sm mt-0.5 font-semibold">Sélection automatique</p>
        </div>
        <div className="flex flex-col items-end gap-1">
          <ConfidenceStars level={coupon.confidence} />
          <span className="text-2xl font-black text-[#F9FF00]">
            x{coupon.total_odds.toFixed(2)}
          </span>
        </div>
      </div>

      {/* Picks */}
      <div className="flex flex-col gap-2">
        {coupon.picks.map((pick, i) => (
          <div
            key={i}
            className="flex items-center justify-between bg-[#1E1E1E] rounded-xl px-4 py-3"
          >
            <div className="flex flex-col">
              <span className="text-xs text-[#888888]">
                {pick.home_team} — {pick.away_team}
              </span>
              <span className="text-sm font-bold text-white mt-0.5">
                {pick.prediction}
              </span>
            </div>
            <span className="text-[#F9FF00] font-semibold text-sm">
              {pick.odds.toFixed(2)}
            </span>
          </div>
        ))}
      </div>

      {/* Footer */}
      <p className="text-xs text-[#444444] text-center">
        Pariez de manière responsable. Cote totale combinée indicative.
      </p>
    </div>
  );
}
