import { Image, StyleSheet, Text, View } from "react-native";
import ConfidenceStars from "@/widgets/ConfidenceStars";
import PremiumLockCard from "@/widgets/PremiumLockCard";

const RESULT_COLOR: Record<string, string> = {
  win: "#22c55e",
  loss: "#ef4444",
  void: "#6b7280",
};

export default function PredictionCard({
  prediction,
  isPremiumUser,
}: {
  prediction: any;
  isPremiumUser: boolean;
}) {
  const match = prediction.matches ?? {};
  const isLocked = prediction.is_premium && !isPremiumUser;
  const time = match.match_date
    ? new Date(match.match_date).toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
    : "";

  return (
    <View style={styles.card}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.league}>{match.league_name ?? "Ligue"}</Text>
        <Text style={styles.time}>{time}</Text>
      </View>

      {/* Équipes */}
      <View style={styles.teams}>
        <View style={styles.team}>
          {match.home_logo_url && (
            <Image source={{ uri: match.home_logo_url }} style={styles.logo} />
          )}
          <Text style={styles.teamName} numberOfLines={2}>{match.home_team}</Text>
        </View>
        <Text style={styles.vs}>VS</Text>
        <View style={styles.team}>
          {match.away_logo_url && (
            <Image source={{ uri: match.away_logo_url }} style={styles.logo} />
          )}
          <Text style={styles.teamName} numberOfLines={2}>{match.away_team}</Text>
        </View>
      </View>

      {/* Prédiction ou verrou */}
      {isLocked ? (
        <PremiumLockCard />
      ) : (
        <View style={styles.predBox}>
          <View>
            <Text style={styles.predLabel}>Prédiction</Text>
            <Text style={styles.predValue}>{prediction.prediction}</Text>
          </View>
          <View style={styles.predRight}>
            <ConfidenceStars level={prediction.confidence} />
            <Text style={styles.odds}>Cote ~{prediction.odds?.toFixed(2)}</Text>
          </View>
        </View>
      )}

      {/* Résultat */}
      {prediction.result && (
        <Text style={[styles.result, { color: RESULT_COLOR[prediction.result] }]}>
          {prediction.result === "win" ? "✓ Gagné" : prediction.result === "loss" ? "✗ Perdu" : "Nul"}
        </Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: "#111827",
    borderWidth: 1,
    borderColor: "#1f2937",
    borderRadius: 16,
    padding: 16,
    gap: 12,
    marginBottom: 12,
  },
  header: { flexDirection: "row", justifyContent: "space-between" },
  league: { color: "#6b7280", fontSize: 12 },
  time: { color: "#6b7280", fontSize: 12 },
  teams: { flexDirection: "row", alignItems: "center", justifyContent: "space-between" },
  team: { flex: 1, alignItems: "center", gap: 4 },
  logo: { width: 32, height: 32, resizeMode: "contain" },
  teamName: { color: "#fff", fontWeight: "600", fontSize: 12, textAlign: "center" },
  vs: { color: "#374151", fontWeight: "700", fontSize: 16, marginHorizontal: 8 },
  predBox: {
    backgroundColor: "#1f2937",
    borderRadius: 12,
    padding: 12,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  predLabel: { color: "#6b7280", fontSize: 11 },
  predValue: { color: "#fff", fontWeight: "700", fontSize: 18, marginTop: 2 },
  predRight: { alignItems: "flex-end", gap: 4 },
  odds: { color: "#9ca3af", fontSize: 11 },
  result: { fontSize: 12, fontWeight: "700" },
});
