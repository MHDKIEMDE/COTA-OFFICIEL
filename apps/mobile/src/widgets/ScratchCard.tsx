import {
  Animated,
  PanResponder,
  StyleSheet,
  Text,
  View,
} from "react-native";
import { useRef, useState } from "react";
import { C } from "@/theme/colors";

// Scratch card maison — sans package externe
// Simule le scratch avec un PanResponder qui fait disparaître le voile progressivement

export default function ScratchCard({
  children,
  onRevealed,
  threshold = 45,
}: {
  children: React.ReactNode;
  onRevealed?: () => void;
  threshold?: number;
}) {
  const [scratched, setScratched] = useState(0); // % gratté
  const [revealed, setRevealed] = useState(false);
  const overlayOpacity = useRef(new Animated.Value(1)).current;

  const panResponder = useRef(
    PanResponder.create({
      onStartShouldSetPanResponder: () => true,
      onMoveShouldSetPanResponder: () => true,
      onPanResponderMove: () => {
        if (revealed) return;
        setScratched((prev) => {
          const next = Math.min(prev + 3, 100);
          if (next >= threshold && prev < threshold) {
            Animated.timing(overlayOpacity, {
              toValue: 0,
              duration: 400,
              useNativeDriver: true,
            }).start(() => {
              setRevealed(true);
              onRevealed?.();
            });
          }
          return next;
        });
      },
    })
  ).current;

  return (
    <View style={s.root}>
      {/* Contenu révélé */}
      {children}

      {/* Voile à gratter */}
      {!revealed && (
        <Animated.View
          style={[s.overlay, { opacity: overlayOpacity }]}
          {...panResponder.panHandlers}
        >
          <View style={s.overlayContent}>
            <Text style={s.scratchEmoji}>🎰</Text>
            <Text style={s.scratchTitle}>Combiné du Jour</Text>
            <Text style={s.scratchSub}>Frottez pour révéler</Text>

            {/* Barre de progression */}
            <View style={s.progressTrack}>
              <View style={[s.progressFill, { width: `${scratched}%` as any }]} />
            </View>
            <Text style={s.progressLabel}>{scratched}% gratté</Text>
          </View>
        </Animated.View>
      )}
    </View>
  );
}

const s = StyleSheet.create({
  root: { position: "relative", overflow: "hidden" },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: C.bg3,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: `${C.primary}44`,
    alignItems: "center",
    justifyContent: "center",
    zIndex: 10,
  },
  overlayContent: { alignItems: "center", gap: 12 },
  scratchEmoji: { fontSize: 52 },
  scratchTitle: {
    color: C.textPrimary,
    fontSize: 20,
    fontWeight: "900",
    letterSpacing: 1,
  },
  scratchSub: { color: C.textMuted, fontSize: 13 },
  progressTrack: {
    width: 160,
    height: 6,
    backgroundColor: C.bg2,
    borderRadius: 3,
    overflow: "hidden",
    marginTop: 4,
  },
  progressFill: {
    height: "100%",
    backgroundColor: C.primary,
    borderRadius: 3,
  },
  progressLabel: { color: C.textMuted, fontSize: 11 },
});
