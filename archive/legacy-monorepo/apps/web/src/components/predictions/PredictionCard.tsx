import ConfidenceStars from "@/components/shared/ConfidenceStars";
import PremiumLock from "@/components/shared/PremiumLock";

interface Match {
  home_team: string;
  away_team: string;
  home_logo_url?: string;
  away_logo_url?: string;
  match_date: string;
  league_name?: string;
}

interface Prediction {
  id: string;
  prediction: string;
  confidence: 1 | 2 | 3 | 4;
  score: number;
  odds: number;
  is_premium: boolean;
  result?: "win" | "loss" | "void" | null;
  matches: Match;
}

const RESULT_BADGE: Record<string, string> = {
  win:  "bg-[#34C759]/15 text-[#34C759] border-[#34C759]/30",
  loss: "bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30",
  void: "bg-[#888888]/15 text-[#888888] border-[#888888]/30",
};

const RESULT_LABEL: Record<string, string> = {
  win:  "Gagné ✓",
  loss: "Perdu ✗",
  void: "Nul",
};

export default function PredictionCard({
  prediction,
  isPremiumUser,
}: {
  prediction: Prediction;
  isPremiumUser: boolean;
}) {
  const match = prediction.matches;
  const isLocked = prediction.is_premium && !isPremiumUser;
  const matchTime = new Date(match.match_date).toLocaleTimeString("fr-FR", {
    hour: "2-digit",
    minute: "2-digit",
  });

  return (
    <div className="bg-[#111111] border border-[#1E1E1E] rounded-2xl p-4 flex flex-col gap-4 card-hover">
      {/* Ligue + heure */}
      <div className="flex items-center justify-between text-xs text-[#888888]">
        <span>{match.league_name ?? "Ligue"}</span>
        <span>{matchTime}</span>
      </div>

      {/* Équipes */}
      <div className="flex items-center justify-between gap-2">
        <div className="flex flex-col items-center gap-1 flex-1">
          {match.home_logo_url && (
            <img src={match.home_logo_url} alt={match.home_team} className="w-8 h-8 object-contain" />
          )}
          <span className="text-sm font-semibold text-center text-white leading-tight">
            {match.home_team}
          </span>
        </div>
        <span className="text-[#444444] font-bold text-lg">VS</span>
        <div className="flex flex-col items-center gap-1 flex-1">
          {match.away_logo_url && (
            <img src={match.away_logo_url} alt={match.away_team} className="w-8 h-8 object-contain" />
          )}
          <span className="text-sm font-semibold text-center text-white leading-tight">
            {match.away_team}
          </span>
        </div>
      </div>

      {/* Prédiction ou verrou */}
      {isLocked ? (
        <PremiumLock />
      ) : (
        <div className="flex items-center justify-between bg-[#1E1E1E] rounded-xl px-4 py-3">
          <div className="flex flex-col gap-1">
            <span className="text-xs text-[#888888]">Prédiction</span>
            <span className="font-bold text-white text-lg">{prediction.prediction}</span>
          </div>
          <div className="flex flex-col items-end gap-1">
            <ConfidenceStars level={prediction.confidence} />
            <span className="text-xs text-[#888888]">Cote ~{prediction.odds.toFixed(2)}</span>
          </div>
        </div>
      )}

      {/* Résultat */}
      {prediction.result && (
        <div className={`text-xs font-semibold px-3 py-1.5 rounded-lg border w-fit ${RESULT_BADGE[prediction.result]}`}>
          {RESULT_LABEL[prediction.result]}
        </div>
      )}
    </div>
  );
}
