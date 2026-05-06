import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from "react-native";
import ConfidenceStars from "@/widgets/ConfidenceStars";
import { useCoupon } from "../logic/useCoupon";

export default function CouponScreen() {
  const { coupon, status } = useCoupon();

  if (status === "loading") {
    return (
      <View style={styles.center}>
        <ActivityIndicator color="#4ade80" size="large" />
      </View>
    );
  }

  if (status === "error") {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>Erreur de chargement</Text>
      </View>
    );
  }

  if (status === "empty") {
    return (
      <View style={styles.center}>
        <Text style={styles.emptyText}>Pas de coupon aujourd'hui</Text>
        <Text style={styles.subText}>Généré chaque matin à 7h00</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ paddingBottom: 32 }}>
      <Text style={styles.title}>Coupon combiné</Text>

      <View style={styles.card}>
        {/* Header */}
        <View style={styles.cardHeader}>
          <View>
            <Text style={styles.cardTitle}>Coupon IA du jour</Text>
            <Text style={styles.cardSubtitle}>Sélection automatique</Text>
          </View>
          <View style={styles.oddsBlock}>
            <ConfidenceStars level={coupon.confidence} />
            <Text style={styles.totalOdds}>x{coupon.total_odds?.toFixed(2)}</Text>
          </View>
        </View>

        {/* Picks */}
        {(coupon.picks ?? []).map((pick: any, i: number) => (
          <View key={i} style={styles.pick}>
            <View>
              <Text style={styles.pickMatch}>
                {pick.home_team} — {pick.away_team}
              </Text>
              <Text style={styles.pickPrediction}>{pick.prediction}</Text>
            </View>
            <Text style={styles.pickOdds}>{pick.odds?.toFixed(2)}</Text>
          </View>
        ))}

        <Text style={styles.disclaimer}>
          Pariez de manière responsable. Cotes indicatives.
        </Text>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "#030712", paddingHorizontal: 16, paddingTop: 16 },
  center: { flex: 1, backgroundColor: "#030712", alignItems: "center", justifyContent: "center", gap: 8 },
  title: { color: "#fff", fontSize: 24, fontWeight: "900", marginBottom: 16 },
  card: {
    backgroundColor: "#0d1a10",
    borderWidth: 1,
    borderColor: "#166534",
    borderRadius: 20,
    padding: 20,
    gap: 14,
  },
  cardHeader: { flexDirection: "row", justifyContent: "space-between", alignItems: "flex-start" },
  cardTitle: { color: "#fff", fontWeight: "800", fontSize: 17 },
  cardSubtitle: { color: "#4ade80", fontSize: 12, marginTop: 2 },
  oddsBlock: { alignItems: "flex-end", gap: 4 },
  totalOdds: { color: "#4ade80", fontWeight: "900", fontSize: 26 },
  pick: {
    backgroundColor: "#111827",
    borderRadius: 12,
    padding: 12,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  pickMatch: { color: "#6b7280", fontSize: 11 },
  pickPrediction: { color: "#fff", fontWeight: "700", fontSize: 14, marginTop: 2 },
  pickOdds: { color: "#4ade80", fontWeight: "700", fontSize: 14 },
  errorText: { color: "#f87171", fontWeight: "600", fontSize: 16 },
  emptyText: { color: "#6b7280", fontWeight: "600", fontSize: 16 },
  subText: { color: "#374151", fontSize: 13 },
  disclaimer: { color: "#374151", fontSize: 11, textAlign: "center" },
});
