import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useState } from "react";
import { useCoupon } from "../logic/useCoupon";
import { C } from "@/theme/colors";
import ScratchCard from "@/widgets/ScratchCard";

function ConfidenceBar({ value, max = 4 }: { value: number; max?: number }) {
  const pct = Math.max(0, Math.min(value / max, 1));
  const color = pct >= 0.75 ? C.accent : pct >= 0.5 ? C.gold : C.live;
  return (
    <View style={cb.root}>
      <View style={cb.track}>
        <View style={[cb.fill, { width: `${pct * 100}%` as any, backgroundColor: color }]} />
      </View>
      <Text style={[cb.label, { color }]}>
        Confiance {value}/{max}
      </Text>
    </View>
  );
}

function Stars({ n, max = 4 }: { n: number; max?: number }) {
  return (
    <View style={{ flexDirection: "row", gap: 2 }}>
      {Array.from({ length: max }).map((_, i) => (
        <Text key={i} style={{ fontSize: 13, color: i < n ? C.gold : C.dim }}>★</Text>
      ))}
    </View>
  );
}

// League colour map (same as PredictionsScreen)
const LEAGUE_COLORS = [C.primary, C.accent, C.gold, "#A855F7", "#EC4899", "#06B6D4"];

export default function CouponScreen({ isPremiumUser = false }: { isPremiumUser?: boolean }) {
  const { coupon, status, reload } = useCoupon();
  const [refreshing, setRefreshing] = useState(false);
  const [scratched, setScratched] = useState(false);

  async function onRefresh() {
    setRefreshing(true);
    await reload?.();
    setRefreshing(false);
  }

  if (status === "loading" && !refreshing) {
    return (
      <View style={s.center}>
        <ActivityIndicator color={C.primary} size="large" />
        <Text style={s.loadingText}>Génération du coupon…</Text>
      </View>
    );
  }

  if (status === "empty" || !coupon) {
    return (
      <View style={s.center}>
        <Text style={s.emptyIcon}>🎯</Text>
        <Text style={s.emptyTitle}>Pas de coupon aujourd'hui</Text>
        <Text style={s.emptyText}>Généré chaque matin à 7h00</Text>
      </View>
    );
  }

  const confidence = coupon.confidence ?? coupon.confidence_level ?? 1;
  const picks: any[] = coupon.picks ?? [];

  return (
    <ScrollView
      style={s.root}
      contentContainerStyle={s.content}
      refreshControl={
        <RefreshControl
          refreshing={refreshing}
          onRefresh={onRefresh}
          tintColor={C.primary}
          colors={[C.primary]}
        />
      }
      showsVerticalScrollIndicator={false}
    >
      {/* ── Scratch card pour utilisateurs gratuits */}
      {!isPremiumUser && !scratched && (
        <View style={{ marginBottom: 16 }}>
          <ScratchCard onRevealed={() => setScratched(true)}>
            <View style={s.heroCard}>
              <View style={s.heroTop}>
                <View style={s.heroDot} />
                <Text style={s.heroTag}>COMBINÉ IA DU JOUR — APERÇU</Text>
              </View>
              <View style={s.heroBody}>
                <View style={s.heroLeft}>
                  <Text style={s.heroSub}>Cote totale</Text>
                  <Text style={[s.heroOdds, { filter: "blur(6px)" as any }]}>x??</Text>
                </View>
                <View style={s.heroRight}>
                  <Text style={s.heroPickCount}>{(coupon?.picks ?? []).length} SÉLECTIONS</Text>
                </View>
              </View>
            </View>
          </ScratchCard>
          <Text style={s.scratchHint}>🎰 Grattez pour révéler le combiné du jour</Text>
        </View>
      )}

      {/* ── Hero card (DAZN broadcast style) */}
      {(isPremiumUser || scratched) && (
      <View style={s.heroCard}>
        {/* Header row */}
        <View style={s.heroTop}>
          <View style={s.heroDot} />
          <Text style={s.heroTag}>COMBINÉ IA DU JOUR</Text>
        </View>

        {/* Cote + stars */}
        <View style={s.heroBody}>
          <View style={s.heroLeft}>
            <Text style={s.heroSub}>Cote totale</Text>
            <Text style={s.heroOdds}>
              x{coupon.total_odds?.toFixed(2) ?? "—"}
            </Text>
          </View>
          <View style={s.heroRight}>
            <Text style={s.heroPickCount}>{picks.length} SÉLECTIONS</Text>
            <Stars n={confidence} />
          </View>
        </View>

        {/* Confidence bar */}
        <ConfidenceBar value={confidence} />

        {/* Generated label */}
        <Text style={s.heroGenerated}>Algorithme COTA · Mis à jour ce matin à 7h00</Text>
      </View>

      )}

      {/* ── Section header + Picks */}
      {(isPremiumUser || scratched) && (
        <>
          <View style={s.sectionRow}>
            <Text style={s.sectionTitle}>SÉLECTIONS</Text>
            <View style={s.sectionLine} />
          </View>

          {picks.map((pick: any, i: number) => {
            const color = LEAGUE_COLORS[i % LEAGUE_COLORS.length];
            return (
              <View key={i} style={[s.pickCard, { borderLeftColor: color }]}>
                <View style={s.pickLeagueRow}>
                  <View style={[s.pickLeagueDot, { backgroundColor: color }]} />
                  <Text style={[s.pickLeague, { color }]} numberOfLines={1}>
                    {pick.league ?? pick.competition ?? "Compétition"}
                  </Text>
                  <Text style={s.pickNum}>#{i + 1}</Text>
                </View>
                <View style={s.pickTeams}>
                  <Text style={s.pickTeam} numberOfLines={1}>{pick.home_team ?? "—"}</Text>
                  <Text style={s.pickVs}>vs</Text>
                  <Text style={[s.pickTeam, s.pickTeamRight]} numberOfLines={1}>{pick.away_team ?? "—"}</Text>
                </View>
                <View style={s.pickFooter}>
                  <View style={s.pickBadge}>
                    <Text style={s.pickBadgeText}>{pick.prediction ?? "—"}</Text>
                  </View>
                  {pick.odds != null && (
                    <View style={s.pickOddsPill}>
                      <Text style={s.pickOddsText}>x{Number(pick.odds).toFixed(2)}</Text>
                    </View>
                  )}
                </View>
              </View>
            );
          })}
        </>
      )}

      {/* ── Total footer */}
      {(isPremiumUser || scratched) && (
      <View style={s.totalCard}>
        <View style={s.totalRow}>
          <Text style={s.totalLabel}>Mise suggérée</Text>
          <Text style={s.totalValue}>500 XOF</Text>
        </View>
        <View style={s.totalDivider} />
        <View style={s.totalRow}>
          <Text style={s.totalLabel}>Gain potentiel</Text>
          <Text style={[s.totalValue, { color: C.accent }]}>
            {((coupon.total_odds ?? 1) * 500).toFixed(0)} XOF
          </Text>
        </View>
      </View>

      )}

      {/* ── Jeu responsable */}
      <View style={s.responsibleCard}>
        <Text style={s.responsibleIcon}>🛡️</Text>
        <Text style={s.responsibleText}>
          Jeu responsable · 18+ uniquement · Pariez uniquement ce que vous pouvez vous permettre de perdre.{"\n"}
          Aide : joueurs-info-service.fr · 09 74 75 13 13
        </Text>
      </View>

      <Text style={s.disclaimer}>
        Pariez de manière responsable · Cotes à titre indicatif uniquement
      </Text>
    </ScrollView>
  );
}

// ─── ConfidenceBar styles ─────────────────────────────────────
const cb = StyleSheet.create({
  root: { gap: 5 },
  track: {
    height: 5,
    backgroundColor: C.bg3,
    borderRadius: 3,
    overflow: "hidden",
  },
  fill: { height: "100%", borderRadius: 3 },
  label: { fontSize: 11, fontWeight: "700" },
});

// ─── CouponScreen styles ──────────────────────────────────────
const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  content: { padding: 12, paddingBottom: 48 },

  center: {
    flex: 1,
    backgroundColor: C.bg,
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
  },
  loadingText: { color: C.textMuted, fontSize: 13 },
  emptyIcon: { fontSize: 44 },
  emptyTitle: { color: C.textSecondary, fontSize: 16, fontWeight: "700" },
  emptyText: { color: C.textMuted, fontSize: 13 },

  // Hero card
  heroCard: {
    backgroundColor: C.bg2,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    padding: 16,
    marginBottom: 16,
    gap: 14,
    shadowColor: C.primary,
    shadowOpacity: 0.15,
    shadowRadius: 20,
    shadowOffset: { width: 0, height: 4 },
    elevation: 6,
  },
  heroTop: { flexDirection: "row", alignItems: "center", gap: 8 },
  heroDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: C.primary,
  },
  heroTag: {
    color: C.primary,
    fontSize: 11,
    fontWeight: "800",
    letterSpacing: 1.2,
  },
  heroBody: {
    flexDirection: "row",
    alignItems: "flex-end",
    justifyContent: "space-between",
  },
  heroLeft: { gap: 2 },
  heroSub: { color: C.textMuted, fontSize: 11, fontWeight: "600" },
  heroOdds: { color: C.accent, fontSize: 40, fontWeight: "900", lineHeight: 44 },
  heroRight: { alignItems: "flex-end", gap: 6 },
  heroPickCount: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "700",
    letterSpacing: 0.8,
  },
  heroGenerated: {
    color: C.textMuted,
    fontSize: 10,
    fontStyle: "italic",
  },

  // Section header
  sectionRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    marginBottom: 8,
    marginTop: 4,
  },
  sectionTitle: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1.5,
  },
  sectionLine: { flex: 1, height: 1, backgroundColor: C.divider },

  // Pick card
  pickCard: {
    backgroundColor: C.bg2,
    borderLeftWidth: 3,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingVertical: 10,
    paddingHorizontal: 12,
    paddingLeft: 14,
    marginBottom: 2,
    gap: 7,
  },
  pickLeagueRow: { flexDirection: "row", alignItems: "center", gap: 7 },
  pickLeagueDot: { width: 5, height: 5, borderRadius: 3 },
  pickLeague: { flex: 1, fontSize: 10, fontWeight: "700", letterSpacing: 0.4 },
  pickNum: { color: C.dim, fontSize: 10, fontWeight: "600" },
  pickTeams: { flexDirection: "row", alignItems: "center", gap: 8, paddingLeft: 12 },
  pickTeam: { flex: 1, color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  pickTeamRight: { textAlign: "right" },
  pickVs: { color: C.textMuted, fontSize: 11 },
  pickFooter: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    paddingLeft: 12,
  },
  pickBadge: {
    backgroundColor: `${C.primary}1A`,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    borderRadius: 5,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  pickBadgeText: { color: C.primaryLight, fontSize: 12, fontWeight: "800" },
  pickOddsPill: {
    marginLeft: "auto" as any,
    backgroundColor: `${C.accent}18`,
    borderWidth: 1,
    borderColor: `${C.accent}44`,
    borderRadius: 5,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  pickOddsText: { color: C.accent, fontSize: 12, fontWeight: "800" },

  // Total footer
  totalCard: {
    backgroundColor: C.bg2,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: C.border,
    padding: 14,
    marginTop: 12,
    gap: 0,
  },
  totalRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    paddingVertical: 8,
  },
  totalDivider: { height: 1, backgroundColor: C.divider },
  totalLabel: { color: C.textMuted, fontSize: 13, fontWeight: "600" },
  totalValue: { color: C.textPrimary, fontSize: 15, fontWeight: "800" },
  disclaimer: { color: C.dim, fontSize: 10, textAlign: "center", marginTop: 8, lineHeight: 16 },
  scratchHint: { color: C.textMuted, fontSize: 12, textAlign: "center", marginTop: 8 },
  responsibleCard: {
    flexDirection: "row",
    alignItems: "flex-start",
    gap: 8,
    backgroundColor: `${C.gold}10`,
    borderWidth: 1,
    borderColor: `${C.gold}22`,
    borderRadius: 10,
    padding: 12,
    marginTop: 12,
  },
  responsibleIcon: { fontSize: 16 },
  responsibleText: { flex: 1, color: C.gold, fontSize: 11, lineHeight: 18 },
});
