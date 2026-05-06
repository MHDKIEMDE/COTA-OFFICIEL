import { Linking, StyleSheet, Text, TouchableOpacity, View } from "react-native";
import { C } from "@/theme/colors";

const WEB_URL = process.env.EXPO_PUBLIC_WEB_URL ?? "https://cota.ci";
const CONFIDENCE_STARS = ["", "★", "★★", "★★★", "★★★★"];
const RESULT_COLORS: Record<string, string> = { won: C.won, lost: C.lost, void: C.textMuted };
const RESULT_LABELS: Record<string, string> = { won: "GAGNÉ", lost: "PERDU", void: "NUL" };

export default function PredictionCard({
  prediction,
  isPremiumUser,
}: {
  prediction: any;
  isPremiumUser: boolean;
}) {
  const match = prediction.matches ?? prediction.match ?? {};
  const isLocked = prediction.is_premium && !isPremiumUser;
  const result = prediction.result;
  const confidence = prediction.confidence_level ?? prediction.confidence ?? 1;
  const time = match.match_date
    ? new Date(match.match_date).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
    : match.match_time ?? "";

  return (
    <View style={[
      styles.card,
      result ? { borderLeftWidth: 3, borderLeftColor: RESULT_COLORS[result] ?? C.border } : null,
    ]}>
      {/* Ligue + heure */}
      <View style={styles.row}>
        <View style={styles.dot} />
        <Text style={styles.league} numberOfLines={1}>
          {match.league_name ?? match.league ?? match.competition ?? "Compétition"}
        </Text>
        <Text style={styles.time}>{time}</Text>
      </View>

      {/* Équipes vs score */}
      <View style={styles.matchRow}>
        <Text style={styles.team} numberOfLines={2}>
          {match.home_team ?? match.homeTeam ?? "—"}
        </Text>
        <Text style={styles.vs}>{match.score ?? "VS"}</Text>
        <Text style={[styles.team, { textAlign: "center" }]} numberOfLines={2}>
          {match.away_team ?? match.awayTeam ?? "—"}
        </Text>
      </View>

      {/* Pronostic ou verrou */}
      {isLocked ? (
        <TouchableOpacity style={styles.lockRow} onPress={() => Linking.openURL(`${WEB_URL}/subscribe`)}>
          <Text style={styles.lockIcon}>🔒</Text>
          <Text style={styles.lockText}>Contenu Premium — Débloquer</Text>
          <Text style={styles.lockArrow}>›</Text>
        </TouchableOpacity>
      ) : (
        <View style={styles.predRow}>
          <View style={styles.predBadge}>
            <Text style={styles.predText} numberOfLines={1}>
              {prediction.prediction ?? prediction.predicted_outcome ?? "—"}
            </Text>
          </View>
          <Text style={styles.stars}>{CONFIDENCE_STARS[Math.min(confidence, 4)] ?? "★"}</Text>
          {prediction.odds != null && (
            <Text style={styles.odds}>x{Number(prediction.odds).toFixed(2)}</Text>
          )}
        </View>
      )}

      {/* Badge résultat */}
      {result && RESULT_LABELS[result] && (
        <View style={[styles.resultBadge, { backgroundColor: `${RESULT_COLORS[result]}22` }]}>
          <Text style={[styles.resultText, { color: RESULT_COLORS[result] }]}>
            {RESULT_LABELS[result]}
          </Text>
        </View>
      )}

      {/* Badge premium */}
      {prediction.is_premium && (
        <View style={styles.premiumBadge}>
          <Text style={styles.premiumText}>PRO</Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: C.card,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: C.border,
    padding: 12,
    marginBottom: 6,
    gap: 10,
  },
  row: { flexDirection: "row", alignItems: "center", gap: 6 },
  dot: { width: 6, height: 6, borderRadius: 3, backgroundColor: C.primary },
  league: { flex: 1, color: C.primary, fontSize: 11, fontWeight: "600" },
  time: { color: C.textMuted, fontSize: 11 },
  matchRow: { flexDirection: "row", alignItems: "center", gap: 8 },
  team: { flex: 1, color: C.textPrimary, fontSize: 13, fontWeight: "700", textAlign: "center" },
  vs: { color: C.textMuted, fontSize: 13, fontWeight: "600", minWidth: 32, textAlign: "center" },
  predRow: { flexDirection: "row", alignItems: "center", gap: 8 },
  predBadge: {
    flex: 1,
    backgroundColor: `${C.primary}22`,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  predText: { color: C.primaryLight, fontSize: 12, fontWeight: "700" },
  stars: { color: C.gold, fontSize: 12 },
  odds: { color: C.success, fontSize: 13, fontWeight: "800" },
  lockRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    backgroundColor: `${C.gold}18`,
    borderRadius: 6,
    padding: 8,
  },
  lockIcon: { fontSize: 14 },
  lockText: { flex: 1, color: C.gold, fontSize: 12, fontWeight: "600" },
  lockArrow: { color: C.gold, fontSize: 16, fontWeight: "700" },
  resultBadge: {
    position: "absolute",
    bottom: 10,
    right: 10,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  resultText: { fontSize: 10, fontWeight: "800", letterSpacing: 0.5 },
  premiumBadge: {
    position: "absolute",
    top: 10,
    right: 10,
    backgroundColor: `${C.primary}33`,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  premiumText: { color: C.primaryLight, fontSize: 9, fontWeight: "800", letterSpacing: 0.8 },
});
