import { ActivityIndicator, RefreshControl, ScrollView, StyleSheet, Text, View } from "react-native";
import { useState } from "react";
import { useCoupon } from "../logic/useCoupon";
import { C } from "@/theme/colors";

const CONFIDENCE_STARS = ["", "★", "★★", "★★★", "★★★★"];

export default function CouponScreen() {
  const { coupon, status, reload } = useCoupon();
  const [refreshing, setRefreshing] = useState(false);

  async function onRefresh() {
    setRefreshing(true);
    await reload?.();
    setRefreshing(false);
  }

  if (status === "loading") {
    return (
      <View style={styles.center}>
        <ActivityIndicator color={C.primary} size="large" />
      </View>
    );
  }

  if (status === "error") {
    return (
      <View style={styles.center}>
        <Text style={styles.errorIcon}>⚠️</Text>
        <Text style={styles.errorText}>Erreur de chargement</Text>
      </View>
    );
  }

  if (status === "empty" || !coupon) {
    return (
      <View style={styles.center}>
        <Text style={styles.emptyIcon}>🎯</Text>
        <Text style={styles.emptyText}>Pas de coupon aujourd'hui</Text>
        <Text style={styles.emptySubText}>Généré chaque matin à 7h00</Text>
      </View>
    );
  }

  const confidence = coupon.confidence ?? 1;

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={C.primary} />
      }
    >
      {/* Header card */}
      <View style={styles.headerCard}>
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.headerLabel}>COMBINÉ IA DU JOUR</Text>
            <Text style={styles.headerSubLabel}>Sélection algorithmique</Text>
          </View>
          <View style={styles.oddsBlock}>
            <Text style={styles.stars}>{CONFIDENCE_STARS[Math.min(confidence, 4)]}</Text>
            <Text style={styles.totalOdds}>
              x{coupon.total_odds?.toFixed(2) ?? "—"}
            </Text>
          </View>
        </View>

        {/* Barre de confiance */}
        <View style={styles.confidenceBar}>
          <View style={styles.confidenceTrack}>
            <View style={[styles.confidenceFill, { width: `${(confidence / 4) * 100}%` as any }]} />
          </View>
          <Text style={styles.confidenceLabel}>Confiance {confidence}/4</Text>
        </View>
      </View>

      {/* Picks */}
      <Text style={styles.sectionTitle}>SÉLECTIONS</Text>
      {(coupon.picks ?? []).map((pick: any, i: number) => (
        <View key={i} style={styles.pickCard}>
          <View style={styles.pickHeader}>
            <View style={styles.leagueDot} />
            <Text style={styles.pickLeague} numberOfLines={1}>
              {pick.league ?? pick.competition ?? "Compétition"}
            </Text>
          </View>
          <View style={styles.pickMatch}>
            <Text style={styles.pickTeam} numberOfLines={1}>{pick.home_team}</Text>
            <Text style={styles.pickVs}>vs</Text>
            <Text style={[styles.pickTeam, { textAlign: "right" }]} numberOfLines={1}>{pick.away_team}</Text>
          </View>
          <View style={styles.pickFooter}>
            <View style={styles.pickPredBadge}>
              <Text style={styles.pickPredText}>{pick.prediction}</Text>
            </View>
            {pick.odds != null && (
              <Text style={styles.pickOdds}>x{Number(pick.odds).toFixed(2)}</Text>
            )}
          </View>
        </View>
      ))}

      <Text style={styles.disclaimer}>
        Pariez de manière responsable. Cotes indicatives.
      </Text>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: C.bg },
  content: { padding: 12, paddingBottom: 40 },
  center: { flex: 1, backgroundColor: C.bg, alignItems: "center", justifyContent: "center", gap: 10 },
  errorIcon: { fontSize: 36 },
  errorText: { color: C.error, fontSize: 15, fontWeight: "600" },
  emptyIcon: { fontSize: 40 },
  emptyText: { color: C.textSecondary, fontSize: 15, fontWeight: "600" },
  emptySubText: { color: C.textDisabled, fontSize: 12 },

  headerCard: {
    backgroundColor: C.bgSecondary,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: C.primary,
    padding: 16,
    marginBottom: 16,
    gap: 12,
  },
  headerTop: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "flex-start",
  },
  headerLabel: {
    color: C.textPrimary,
    fontSize: 14,
    fontWeight: "800",
    letterSpacing: 0.5,
  },
  headerSubLabel: {
    color: C.primary,
    fontSize: 11,
    marginTop: 2,
  },
  oddsBlock: { alignItems: "flex-end", gap: 2 },
  stars: { color: C.gold, fontSize: 14 },
  totalOdds: { color: C.success, fontWeight: "900", fontSize: 28 },
  confidenceBar: { gap: 4 },
  confidenceTrack: {
    height: 4,
    backgroundColor: C.bgElevated,
    borderRadius: 2,
    overflow: "hidden",
  },
  confidenceFill: {
    height: "100%",
    backgroundColor: C.primary,
    borderRadius: 2,
  },
  confidenceLabel: { color: C.textMuted, fontSize: 11 },

  sectionTitle: {
    color: C.textMuted,
    fontSize: 11,
    fontWeight: "700",
    letterSpacing: 1,
    marginBottom: 8,
    marginTop: 4,
  },

  pickCard: {
    backgroundColor: C.bgSecondary,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: C.border,
    padding: 12,
    marginBottom: 8,
    gap: 8,
  },
  pickHeader: { flexDirection: "row", alignItems: "center", gap: 6 },
  leagueDot: { width: 5, height: 5, borderRadius: 3, backgroundColor: C.primary },
  pickLeague: { color: C.primary, fontSize: 11, fontWeight: "600", flex: 1 },
  pickMatch: { flexDirection: "row", alignItems: "center", gap: 6 },
  pickTeam: { flex: 1, color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  pickVs: { color: C.textMuted, fontSize: 11 },
  pickFooter: { flexDirection: "row", alignItems: "center", gap: 8 },
  pickPredBadge: {
    flex: 1,
    backgroundColor: `${C.primary}22`,
    borderRadius: 5,
    paddingHorizontal: 8,
    paddingVertical: 4,
  },
  pickPredText: { color: C.primaryLight, fontSize: 12, fontWeight: "700" },
  pickOdds: { color: C.success, fontWeight: "800", fontSize: 14 },
  disclaimer: { color: C.textDisabled, fontSize: 11, textAlign: "center", marginTop: 12 },
});
