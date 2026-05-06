// Auto-fill bookmakers : ouvre le bookmaker via Linking avec les picks du coupon
// (WebView non installé, on redirige vers le site mobile du bookmaker)

import {
  Animated,
  Dimensions,
  Linking,
  Modal,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useEffect, useRef } from "react";
import { C } from "@/theme/colors";

const { height: H } = Dimensions.get("window");

const BOOKMAKERS = [
  {
    name: "Betwinner",
    icon: "🎯",
    color: "#f97316",
    url: "https://betwinner.com",
    bonus: "100% jusqu'à 130€",
  },
  {
    name: "1xBet",
    icon: "⚡",
    color: "#3b82f6",
    url: "https://1xbet.com",
    bonus: "100% jusqu'à 100€",
  },
  {
    name: "Melbet",
    icon: "🔥",
    color: "#ef4444",
    url: "https://melbet.com",
    bonus: "100% jusqu'à 130€",
  },
  {
    name: "Betway",
    icon: "🏆",
    color: "#10b981",
    url: "https://betway.com",
    bonus: "Offre de bienvenue",
  },
];

export default function AutoFillSheet({
  visible,
  picks,
  onClose,
}: {
  visible: boolean;
  picks: any[];
  onClose: () => void;
}) {
  const slideY = useRef(new Animated.Value(H)).current;

  useEffect(() => {
    if (visible) {
      Animated.spring(slideY, {
        toValue: 0,
        useNativeDriver: true,
        tension: 65,
        friction: 11,
      }).start();
    } else {
      Animated.timing(slideY, {
        toValue: H,
        duration: 240,
        useNativeDriver: true,
      }).start();
    }
  }, [visible]);

  function openBookmaker(bm: (typeof BOOKMAKERS)[0]) {
    // Construit l'URL avec les picks en query param (deep link bookmaker)
    const picksParam = picks.map((p) => p.prediction).join(",");
    const url = `${bm.url}?utm_source=cota&picks=${encodeURIComponent(picksParam)}`;
    Linking.openURL(url);
    onClose();
  }

  if (picks.length === 0) return null;

  return (
    <Modal visible={visible} transparent animationType="none" onRequestClose={onClose}>
      <TouchableOpacity style={s.backdrop} activeOpacity={1} onPress={onClose} />
      <Animated.View style={[s.sheet, { transform: [{ translateY: slideY }] }]}>
        <View style={s.handle} />

        <Text style={s.title}>Parier maintenant ⚡</Text>
        <Text style={s.sub}>
          {picks.length} sélections · cote x{picks.reduce((a, p) => a * p.odds, 1).toFixed(2)}
        </Text>

        {/* Picks résumé */}
        <View style={s.picksRow}>
          {picks.slice(0, 3).map((p, i) => (
            <View key={i} style={s.pickChip}>
              <Text style={s.pickChipText} numberOfLines={1}>{p.prediction}</Text>
            </View>
          ))}
          {picks.length > 3 && (
            <View style={s.pickChip}>
              <Text style={s.pickChipText}>+{picks.length - 3}</Text>
            </View>
          )}
        </View>

        <Text style={s.sectionLabel}>CHOISIR UN BOOKMAKER</Text>

        {BOOKMAKERS.map((bm) => (
          <TouchableOpacity
            key={bm.name}
            style={s.bmRow}
            onPress={() => openBookmaker(bm)}
            activeOpacity={0.85}
          >
            <Text style={s.bmIcon}>{bm.icon}</Text>
            <View style={s.bmBody}>
              <Text style={s.bmName}>{bm.name}</Text>
              <Text style={s.bmBonus}>{bm.bonus}</Text>
            </View>
            <View style={[s.bmBtn, { backgroundColor: bm.color }]}>
              <Text style={s.bmBtnText}>Ouvrir →</Text>
            </View>
          </TouchableOpacity>
        ))}

        {/* Jeu responsable */}
        <View style={s.responsible}>
          <Text style={s.responsibleText}>
            ⚠️ 18+ · Pariez de manière responsable · joueurs-info-service.fr · 09 74 75 13 13
          </Text>
        </View>
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
    borderTopWidth: 1,
    borderTopColor: C.border,
    paddingBottom: 32,
    overflow: "hidden",
  },
  handle: {
    width: 36,
    height: 4,
    borderRadius: 2,
    backgroundColor: C.dim,
    alignSelf: "center",
    marginTop: 10,
    marginBottom: 12,
  },
  title: {
    color: C.textPrimary,
    fontSize: 20,
    fontWeight: "900",
    paddingHorizontal: 16,
  },
  sub: { color: C.textMuted, fontSize: 13, paddingHorizontal: 16, marginTop: 4 },
  picksRow: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 6,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  pickChip: {
    backgroundColor: `${C.primary}18`,
    borderWidth: 1,
    borderColor: `${C.primary}33`,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  pickChipText: { color: C.primaryLight, fontSize: 12, fontWeight: "700" },
  sectionLabel: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1.5,
    paddingHorizontal: 16,
    paddingBottom: 8,
    borderTopWidth: 1,
    borderTopColor: C.divider,
    paddingTop: 12,
  },
  bmRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    paddingHorizontal: 16,
    paddingVertical: 13,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  bmIcon: { fontSize: 24 },
  bmBody: { flex: 1 },
  bmName: { color: C.textPrimary, fontSize: 15, fontWeight: "700" },
  bmBonus: { color: C.accent, fontSize: 11, marginTop: 2 },
  bmBtn: {
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  bmBtnText: { color: "#fff", fontSize: 12, fontWeight: "800" },
  responsible: {
    marginHorizontal: 16,
    marginTop: 12,
    backgroundColor: `${C.gold}10`,
    borderRadius: 8,
    padding: 10,
  },
  responsibleText: { color: C.gold, fontSize: 10, lineHeight: 16, textAlign: "center" },
});
