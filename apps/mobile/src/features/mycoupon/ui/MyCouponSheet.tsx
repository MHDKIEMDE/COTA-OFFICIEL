import {
  Animated,
  Dimensions,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useEffect, useRef } from "react";
import { C } from "@/theme/colors";
import { CouponPick } from "../logic/useMyCoupon";

const { height: SCREEN_H } = Dimensions.get("window");
const LEAGUE_COLORS = [C.primary, C.accent, C.gold, "#A855F7", "#EC4899", "#06B6D4"];

function Stars({ n }: { n: number }) {
  return (
    <View style={{ flexDirection: "row", gap: 2 }}>
      {[1, 2, 3, 4].map((i) => (
        <Text key={i} style={{ fontSize: 10, color: i <= n ? C.gold : C.dim }}>★</Text>
      ))}
    </View>
  );
}

export default function MyCouponSheet({
  visible,
  picks,
  totalOdds,
  onRemove,
  onClear,
  onClose,
}: {
  visible: boolean;
  picks: CouponPick[];
  totalOdds: number;
  onRemove: (id: string) => void;
  onClear: () => void;
  onClose: () => void;
}) {
  const slideY = useRef(new Animated.Value(SCREEN_H)).current;

  useEffect(() => {
    if (visible) {
      Animated.spring(slideY, {
        toValue: 0,
        useNativeDriver: true,
        tension: 70,
        friction: 12,
      }).start();
    } else {
      Animated.timing(slideY, {
        toValue: SCREEN_H,
        duration: 250,
        useNativeDriver: true,
      }).start();
    }
  }, [visible]);

  return (
    <Modal visible={visible} transparent animationType="none" onRequestClose={onClose}>
      <TouchableOpacity style={s.backdrop} activeOpacity={1} onPress={onClose} />
      <Animated.View style={[s.sheet, { transform: [{ translateY: slideY }] }]}>
        {/* Handle */}
        <View style={s.handle} />

        {/* Header */}
        <View style={s.header}>
          <View style={s.headerLeft}>
            <Text style={s.title}>Mon Coupon</Text>
            <View style={s.countBadge}>
              <Text style={s.countText}>{picks.length}</Text>
            </View>
          </View>
          <TouchableOpacity onPress={onClose} style={s.closeBtn}>
            <Text style={s.closeBtnText}>✕</Text>
          </TouchableOpacity>
        </View>

        {/* Empty state */}
        {picks.length === 0 && (
          <View style={s.empty}>
            <Text style={s.emptyIcon}>🎯</Text>
            <Text style={s.emptyTitle}>Ton coupon est vide</Text>
            <Text style={s.emptySub}>Clique sur + à côté d'un pronostic pour l'ajouter</Text>
          </View>
        )}

        {/* Picks list */}
        {picks.length > 0 && (
          <>
            <ScrollView style={s.list} showsVerticalScrollIndicator={false}>
              {picks.map((pk, i) => {
                const color = LEAGUE_COLORS[i % LEAGUE_COLORS.length];
                return (
                  <View key={pk.id} style={[s.pickRow, { borderLeftColor: color }]}>
                    <View style={s.pickBody}>
                      <Text style={[s.pickLeague, { color }]} numberOfLines={1}>
                        {pk.league}
                      </Text>
                      <Text style={s.pickMatch} numberOfLines={1}>
                        {pk.home_team} — {pk.away_team}
                      </Text>
                      <View style={s.pickBottom}>
                        <View style={s.predBadge}>
                          <Text style={s.predText}>{pk.prediction}</Text>
                        </View>
                        <Text style={s.pickOdds}>x{pk.odds.toFixed(2)}</Text>
                      </View>
                    </View>
                    <TouchableOpacity
                      onPress={() => onRemove(pk.id)}
                      style={s.removeBtn}
                      hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
                    >
                      <Text style={s.removeBtnText}>×</Text>
                    </TouchableOpacity>
                  </View>
                );
              })}
            </ScrollView>

            {/* Summary */}
            <View style={s.summary}>
              <View style={s.summaryRow}>
                <Text style={s.summaryLabel}>Cote combinée</Text>
                <Text style={s.summaryOdds}>x{totalOdds.toFixed(2)}</Text>
              </View>
              <View style={s.summaryDivider} />
              <View style={s.summaryRow}>
                <Text style={s.summaryLabel}>Gain potentiel (500 XOF)</Text>
                <Text style={[s.summaryLabel, { color: C.accent }]}>
                  {(totalOdds * 500).toFixed(0)} XOF
                </Text>
              </View>
              <View style={s.summaryRow}>
                <Text style={s.summaryLabel}>{picks.length} sélections</Text>
                <TouchableOpacity onPress={onClear}>
                  <Text style={s.clearText}>Vider</Text>
                </TouchableOpacity>
              </View>
            </View>
          </>
        )}
      </Animated.View>
    </Modal>
  );
}

const s = StyleSheet.create({
  backdrop: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: "rgba(0,0,0,0.65)",
  },
  sheet: {
    position: "absolute",
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: C.bg2,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: SCREEN_H * 0.82,
    borderTopWidth: 1,
    borderTopColor: C.border,
    overflow: "hidden",
  },
  handle: {
    width: 36,
    height: 4,
    borderRadius: 2,
    backgroundColor: C.dim,
    alignSelf: "center",
    marginTop: 10,
    marginBottom: 4,
  },
  header: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  headerLeft: { flexDirection: "row", alignItems: "center", gap: 8 },
  title: { color: C.textPrimary, fontSize: 17, fontWeight: "800" },
  countBadge: {
    backgroundColor: C.primary,
    width: 22,
    height: 22,
    borderRadius: 11,
    alignItems: "center",
    justifyContent: "center",
  },
  countText: { color: "#fff", fontSize: 11, fontWeight: "800" },
  closeBtn: { padding: 4 },
  closeBtnText: { color: C.textMuted, fontSize: 18, lineHeight: 22 },

  empty: { alignItems: "center", justifyContent: "center", padding: 48, gap: 10 },
  emptyIcon: { fontSize: 40 },
  emptyTitle: { color: C.textSecondary, fontSize: 16, fontWeight: "700" },
  emptySub: { color: C.textMuted, fontSize: 13, textAlign: "center", lineHeight: 19 },

  list: { maxHeight: SCREEN_H * 0.42 },

  pickRow: {
    flexDirection: "row",
    alignItems: "center",
    borderLeftWidth: 3,
    backgroundColor: C.bg3,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingVertical: 10,
    paddingHorizontal: 12,
    paddingLeft: 14,
    gap: 10,
  },
  pickBody: { flex: 1, gap: 4 },
  pickLeague: { fontSize: 10, fontWeight: "700", letterSpacing: 0.3 },
  pickMatch: { color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  pickBottom: { flexDirection: "row", alignItems: "center", gap: 8 },
  predBadge: {
    backgroundColor: `${C.primary}1A`,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    borderRadius: 4,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  predText: { color: C.primaryLight, fontSize: 11, fontWeight: "800" },
  pickOdds: { color: C.accent, fontSize: 12, fontWeight: "800" },
  removeBtn: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: `${C.live}18`,
    alignItems: "center",
    justifyContent: "center",
  },
  removeBtnText: { color: C.live, fontSize: 18, lineHeight: 22, fontWeight: "300" },

  summary: {
    borderTopWidth: 1,
    borderTopColor: C.border,
    padding: 16,
    gap: 10,
    paddingBottom: 32,
  },
  summaryRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  summaryLabel: { color: C.textMuted, fontSize: 13 },
  summaryOdds: { color: C.accent, fontSize: 26, fontWeight: "900" },
  summaryDivider: { height: 1, backgroundColor: C.divider },
  clearText: { color: C.live, fontSize: 12, fontWeight: "700" },
});
