import { Linking, StyleSheet, Text, TouchableOpacity, View } from "react-native";
import { useState } from "react";
import { C } from "@/theme/colors";
import MatchDetailScreen from "@/features/match/ui/MatchDetailScreen";
import { SEED_MATCH_DETAIL } from "@/data/matchSeeds";

const WEB_URL = process.env.EXPO_PUBLIC_WEB_URL ?? "https://cota.ci";

const RESULT_COLOR: Record<string, string> = {
  won: C.won, lost: C.lost, void: C.muted,
};
const RESULT_LABEL: Record<string, string> = {
  won: "GAGNÉ", lost: "PERDU", void: "NUL",
};

function Stars({ n }: { n: number }) {
  return (
    <View style={{ flexDirection: "row", gap: 1 }}>
      {[1, 2, 3, 4].map((i) => (
        <Text key={i} style={{ fontSize: 10, color: i <= n ? C.gold : C.dim }}>★</Text>
      ))}
    </View>
  );
}

export default function PredictionCard({
  prediction,
  isPremiumUser,
  inCoupon,
  onToggleCoupon,
}: {
  prediction: any;
  isPremiumUser: boolean;
  inCoupon?: boolean;
  onToggleCoupon?: () => void;
}) {
  const [showDetail, setShowDetail] = useState(false);
  const match = prediction.matches ?? prediction.match ?? {};
  const isLocked = prediction.is_premium && !isPremiumUser;
  const result = prediction.result as string | null;
  const confidence = Math.min(prediction.confidence_level ?? prediction.confidence ?? 1, 4);
  const time = match.match_date
    ? new Date(match.match_date).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
    : match.match_time ?? "";

  const accentColor = result ? (RESULT_COLOR[result] ?? C.border) : C.border;

  // Merge seed detail with real prediction data
  const matchDetail = {
    ...SEED_MATCH_DETAIL,
    home_team: match.home_team ?? SEED_MATCH_DETAIL.home_team,
    away_team: match.away_team ?? SEED_MATCH_DETAIL.away_team,
    league: match.league_name ?? match.league ?? SEED_MATCH_DETAIL.league,
    match_date: match.match_date ?? SEED_MATCH_DETAIL.match_date,
    home_score: match.score ? parseInt(match.score.split("-")[0]) : null,
    away_score: match.score ? parseInt(match.score.split("-")[1]) : null,
    status: match.score ? "finished" : "upcoming",
    prediction: {
      pick: prediction.prediction ?? "—",
      odds: prediction.odds ?? 1.0,
      confidence: prediction.confidence_level ?? 3,
      analysis: prediction.analysis ?? SEED_MATCH_DETAIL.prediction.analysis,
    },
  };

  return (
    <>
    <TouchableOpacity onPress={() => setShowDetail(true)} activeOpacity={0.85}>
    <View style={[s.card, { borderLeftColor: accentColor }]}>
      {/* Premium badge */}
      {prediction.is_premium && (
        <View style={s.proBadge}>
          <Text style={s.proBadgeText}>PRO</Text>
        </View>
      )}

      {/* Time + Teams (FlashScore style) */}
      <View style={s.topRow}>
        <Text style={s.time}>{time || "TBD"}</Text>
        <View style={s.teamsBlock}>
          <Text style={s.team} numberOfLines={1}>{match.home_team ?? "—"}</Text>
          {match.score ? (
            <Text style={s.score}>{match.score}</Text>
          ) : (
            <Text style={s.vs}>vs</Text>
          )}
          <Text style={[s.team, s.teamRight]} numberOfLines={1}>{match.away_team ?? "—"}</Text>
        </View>
      </View>

      {/* Prediction row */}
      {isLocked ? (
        <TouchableOpacity
          style={s.lock}
          onPress={() => Linking.openURL(`${WEB_URL}/subscribe`)}
          activeOpacity={0.8}
        >
          <Text style={s.lockIcon}>🔒</Text>
          <Text style={s.lockText}>Contenu réservé aux abonnés Premium</Text>
          <Text style={s.lockArrow}>›</Text>
        </TouchableOpacity>
      ) : (
        <View style={s.predRow}>
          <View style={s.predBadge}>
            <Text style={s.predText}>
              {prediction.prediction ?? prediction.predicted_outcome ?? "—"}
            </Text>
          </View>
          <Stars n={confidence} />
          {prediction.odds != null && (
            <View style={s.oddsPill}>
              <Text style={s.oddsText}>x{Number(prediction.odds).toFixed(2)}</Text>
            </View>
          )}
          {result && RESULT_LABEL[result] && (
            <View style={[s.resultBadge, { backgroundColor: `${RESULT_COLOR[result]}22` }]}>
              <Text style={[s.resultText, { color: RESULT_COLOR[result] }]}>
                {RESULT_LABEL[result]}
              </Text>
            </View>
          )}

              {/* Bouton ajouter au coupon */}
          {!result && onToggleCoupon && (
            <TouchableOpacity
              style={[s.couponBtn, inCoupon && s.couponBtnActive]}
              onPress={(e) => { e.stopPropagation?.(); onToggleCoupon(); }}
              activeOpacity={0.8}
              hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
            >
              <Text style={[s.couponBtnText, inCoupon && s.couponBtnTextActive]}>
                {inCoupon ? "✓" : "+"}
              </Text>
            </TouchableOpacity>
          )}
        </View>
      )}
    </View>
    </TouchableOpacity>
    <MatchDetailScreen
      match={matchDetail}
      visible={showDetail}
      onClose={() => setShowDetail(false)}
    />
    </>
  );
}

const s = StyleSheet.create({
  card: {
    backgroundColor: C.bg2,
    borderLeftWidth: 3,
    borderLeftColor: C.border,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingVertical: 10,
    paddingHorizontal: 12,
    paddingLeft: 14,
    gap: 8,
    position: "relative",
  },
  proBadge: {
    position: "absolute",
    top: 8,
    right: 8,
    backgroundColor: `${C.primary}22`,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  proBadgeText: { color: C.primaryLight, fontSize: 9, fontWeight: "800", letterSpacing: 0.8 },
  topRow: { flexDirection: "row", alignItems: "center", gap: 10 },
  time: {
    color: C.textMuted,
    fontSize: 11,
    fontWeight: "700",
    minWidth: 38,
    fontVariant: ["tabular-nums"],
  },
  teamsBlock: { flex: 1, flexDirection: "row", alignItems: "center", gap: 8 },
  team: { flex: 1, color: C.textPrimary, fontSize: 13, fontWeight: "700", textAlign: "left" },
  teamRight: { textAlign: "right" },
  vs: { color: C.textMuted, fontSize: 11, fontWeight: "600", minWidth: 20, textAlign: "center" },
  score: {
    color: C.textPrimary,
    fontSize: 13,
    fontWeight: "800",
    minWidth: 44,
    textAlign: "center",
    fontVariant: ["tabular-nums"],
  },
  predRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    paddingLeft: 48,
    paddingRight: 4,
  },
  predBadge: {
    backgroundColor: `${C.primary}1A`,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    borderRadius: 5,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  predText: { color: C.primaryLight, fontSize: 12, fontWeight: "800", letterSpacing: 0.3 },
  oddsPill: {
    backgroundColor: `${C.accent}18`,
    borderWidth: 1,
    borderColor: `${C.accent}44`,
    borderRadius: 5,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  oddsText: { color: C.accent, fontSize: 12, fontWeight: "800" },
  resultBadge: {
    borderRadius: 4,
    paddingHorizontal: 7,
    paddingVertical: 3,
  },
  resultText: { fontSize: 10, fontWeight: "800", letterSpacing: 0.5 },
  lock: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    backgroundColor: `${C.gold}12`,
    borderWidth: 1,
    borderColor: `${C.gold}2A`,
    borderRadius: 6,
    padding: 9,
  },
  lockIcon: { fontSize: 13 },
  lockText: { flex: 1, color: C.gold, fontSize: 12, fontWeight: "600" },
  lockArrow: { color: C.gold, fontSize: 17, fontWeight: "700" },

  // Bouton coupon
  couponBtn: {
    marginLeft: "auto" as any,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: C.bg3,
    borderWidth: 1,
    borderColor: C.border,
    alignItems: "center",
    justifyContent: "center",
  },
  couponBtnActive: {
    backgroundColor: `${C.primary}22`,
    borderColor: C.primary,
  },
  couponBtnText: { color: C.textMuted, fontSize: 16, lineHeight: 20, fontWeight: "700" },
  couponBtnTextActive: { color: C.primary },
});
