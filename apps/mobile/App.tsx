import { supabase } from "@/lib/supabase";
import { Session } from "@supabase/supabase-js";
import { StatusBar } from "expo-status-bar";
import { useEffect, useRef, useState } from "react";
import {
  Animated,
  Dimensions,
  Easing,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import PredictionsScreen from "@/features/predictions/ui/PredictionsScreen";
import CouponScreen from "@/features/coupon/ui/CouponScreen";
import SubscribeScreen from "@/features/subscribe/ui/SubscribeScreen";
import LoginScreen from "@/features/auth/ui/LoginScreen";
import { C } from "@/theme/colors";

const { width } = Dimensions.get("window");

type Tab = "predictions" | "coupon" | "subscribe";

const TABS: { key: Tab; label: string; icon: string }[] = [
  { key: "predictions", label: "Pronostics", icon: "⚽" },
  { key: "coupon", label: "Combiné", icon: "🎯" },
  { key: "subscribe", label: "Premium", icon: "⭐" },
];

// ─── Onboarding slides ───────────────────────────────────────
const SLIDES = [
  {
    emoji: "🤖",
    title: "IA & Algorithme",
    subtitle: "9 critères analysés par match\npour des pronostics solides",
    accent: C.primary,
  },
  {
    emoji: "⚽",
    title: "Pronostics du jour",
    subtitle: "Tous les matchs, groupés par ligue\navec niveaux de confiance",
    accent: C.success,
  },
  {
    emoji: "🎯",
    title: "Coupon combiné",
    subtitle: "La meilleure sélection du jour\ngénérée chaque matin à 7h",
    accent: C.gold,
  },
];

function OnboardingScreen({ onDone }: { onDone: () => void }) {
  const [current, setCurrent] = useState(0);
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(40)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, { toValue: 1, duration: 500, useNativeDriver: true }),
      Animated.timing(slideAnim, { toValue: 0, duration: 500, easing: Easing.out(Easing.cubic), useNativeDriver: true }),
    ]).start();
  }, [current]);

  function next() {
    Animated.parallel([
      Animated.timing(fadeAnim, { toValue: 0, duration: 200, useNativeDriver: true }),
      Animated.timing(slideAnim, { toValue: -30, duration: 200, useNativeDriver: true }),
    ]).start(() => {
      slideAnim.setValue(40);
      if (current < SLIDES.length - 1) {
        setCurrent(current + 1);
      } else {
        onDone();
      }
    });
  }

  const slide = SLIDES[current];

  return (
    <SafeAreaView style={ob.root}>
      <StatusBar style="light" />
      {/* Logo */}
      <View style={ob.header}>
        <Text style={ob.logo}>COTA</Text>
      </View>

      {/* Slide animé */}
      <Animated.View style={[ob.slide, { opacity: fadeAnim, transform: [{ translateY: slideAnim }] }]}>
        <View style={[ob.emojiCircle, { backgroundColor: `${slide.accent}22` }]}>
          <Text style={ob.emoji}>{slide.emoji}</Text>
        </View>
        <Text style={[ob.slideTitle, { color: slide.accent }]}>{slide.title}</Text>
        <Text style={ob.slideSubtitle}>{slide.subtitle}</Text>
      </Animated.View>

      {/* Indicators */}
      <View style={ob.dots}>
        {SLIDES.map((_, i) => (
          <View key={i} style={[ob.dot, i === current && { backgroundColor: slide.accent, width: 20 }]} />
        ))}
      </View>

      {/* CTA */}
      <View style={ob.footer}>
        <TouchableOpacity
          style={[ob.btn, { backgroundColor: slide.accent }]}
          onPress={next}
          activeOpacity={0.85}
        >
          <Text style={ob.btnText}>
            {current < SLIDES.length - 1 ? "Suivant" : "Commencer"}
          </Text>
        </TouchableOpacity>
        {current < SLIDES.length - 1 && (
          <TouchableOpacity onPress={onDone} style={ob.skip}>
            <Text style={ob.skipText}>Passer</Text>
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
}

// ─── App principale ───────────────────────────────────────────
export default function App() {
  const [session, setSession] = useState<Session | null>(null);
  const [tab, setTab] = useState<Tab>("predictions");
  const [showLogin, setShowLogin] = useState(false);
  const [showOnboarding, setShowOnboarding] = useState(true);

  // Indicateur de tab animé
  const tabIndicatorX = useRef(new Animated.Value(0)).current;
  const tabWidth = width / TABS.length;

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => setSession(session));
    const { data: { subscription } } = supabase.auth.onAuthStateChange((_e, s) => {
      setSession(s);
      if (s) setShowLogin(false);
    });
    return () => subscription.unsubscribe();
  }, []);

  useEffect(() => {
    const idx = TABS.findIndex((t) => t.key === tab);
    Animated.spring(tabIndicatorX, {
      toValue: idx * tabWidth,
      useNativeDriver: true,
      tension: 80,
      friction: 10,
    }).start();
  }, [tab]);

  const isPremium =
    (session?.user as any)?.user_metadata?.role === "premium" ||
    (session?.user as any)?.user_metadata?.role === "admin";

  if (showOnboarding) {
    return <OnboardingScreen onDone={() => setShowOnboarding(false)} />;
  }

  if (showLogin) {
    return <LoginScreen />;
  }

  return (
    <SafeAreaView style={styles.root}>
      <StatusBar style="light" />

      {/* Header DAZN-style */}
      <View style={styles.header}>
        <Text style={styles.logo}>COTA</Text>
        <View style={styles.headerRight}>
          {isPremium && (
            <View style={styles.premiumBadge}>
              <Text style={styles.premiumBadgeText}>PRO</Text>
            </View>
          )}
          {session ? (
            <TouchableOpacity onPress={() => supabase.auth.signOut()} style={styles.authBtn}>
              <Text style={styles.authBtnText}>Déconnexion</Text>
            </TouchableOpacity>
          ) : (
            <TouchableOpacity onPress={() => setShowLogin(true)} style={styles.authBtn}>
              <Text style={styles.authBtnText}>Connexion</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Content */}
      <View style={styles.content}>
        {tab === "predictions" && <PredictionsScreen isPremiumUser={isPremium} />}
        {tab === "coupon" && <CouponScreen />}
        {tab === "subscribe" && <SubscribeScreen />}
      </View>

      {/* Bottom tab bar avec indicateur animé */}
      <View style={styles.tabBar}>
        <Animated.View
          style={[
            styles.tabIndicator,
            { width: tabWidth - 24, transform: [{ translateX: Animated.add(tabIndicatorX, new Animated.Value(12)) }] },
          ]}
        />
        {TABS.map((t) => {
          const isActive = tab === t.key;
          return (
            <TouchableOpacity
              key={t.key}
              style={styles.tabItem}
              onPress={() => setTab(t.key)}
              activeOpacity={0.7}
            >
              <Text style={styles.tabIcon}>{t.icon}</Text>
              <Text style={[styles.tabLabel, isActive && styles.tabLabelActive]}>
                {t.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </SafeAreaView>
  );
}

// ─── Styles Onboarding ───────────────────────────────────────
const ob = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg, justifyContent: "space-between" },
  header: { alignItems: "center", paddingTop: 20 },
  logo: { color: C.primary, fontSize: 28, fontWeight: "900", letterSpacing: 3 },
  slide: { flex: 1, alignItems: "center", justifyContent: "center", paddingHorizontal: 32, gap: 20 },
  emojiCircle: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: "center",
    justifyContent: "center",
  },
  emoji: { fontSize: 44 },
  slideTitle: { fontSize: 26, fontWeight: "900", textAlign: "center" },
  slideSubtitle: { color: C.textSecondary, fontSize: 16, textAlign: "center", lineHeight: 24 },
  dots: { flexDirection: "row", justifyContent: "center", gap: 8, paddingBottom: 16 },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: C.bgElevated },
  footer: { paddingHorizontal: 24, paddingBottom: 32, gap: 12 },
  btn: {
    borderRadius: 14,
    paddingVertical: 16,
    alignItems: "center",
  },
  btnText: { color: "#fff", fontWeight: "800", fontSize: 16 },
  skip: { alignItems: "center", paddingVertical: 8 },
  skipText: { color: C.textMuted, fontSize: 14 },
});

// ─── Styles App ───────────────────────────────────────────────
const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  header: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: C.bgSecondary,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 0.5,
    borderBottomColor: C.border,
  },
  logo: { color: C.primary, fontSize: 20, fontWeight: "900", letterSpacing: 2 },
  headerRight: { flexDirection: "row", alignItems: "center", gap: 8 },
  premiumBadge: {
    backgroundColor: C.primary,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  premiumBadgeText: { color: "#fff", fontSize: 10, fontWeight: "800", letterSpacing: 1 },
  authBtn: {
    backgroundColor: C.bgTertiary,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
    borderWidth: 0.5,
    borderColor: C.border,
  },
  authBtnText: { color: C.textSecondary, fontSize: 12, fontWeight: "600" },
  content: { flex: 1 },
  tabBar: {
    flexDirection: "row",
    backgroundColor: C.bgSecondary,
    borderTopWidth: 0.5,
    borderTopColor: C.border,
    paddingBottom: 6,
    paddingTop: 4,
    position: "relative",
  },
  tabIndicator: {
    position: "absolute",
    top: 0,
    height: 2,
    backgroundColor: C.primary,
    borderRadius: 1,
  },
  tabItem: {
    flex: 1,
    alignItems: "center",
    paddingVertical: 6,
    gap: 3,
  },
  tabIcon: { fontSize: 18 },
  tabLabel: { color: C.textMuted, fontSize: 11, fontWeight: "600" },
  tabLabelActive: { color: C.primary },
});
