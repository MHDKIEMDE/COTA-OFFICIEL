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
import ProfileScreen from "@/features/profile/ui/ProfileScreen";
import LoginScreen from "@/features/auth/ui/LoginScreen";
import MyCouponSheet from "@/features/mycoupon/ui/MyCouponSheet";
import { useMyCoupon } from "@/features/mycoupon/logic/useMyCoupon";
import AutoFillSheet from "@/widgets/AutoFillSheet";
import { C } from "@/theme/colors";
import { TICKER_SEEDS } from "@/data/seeds";

const { width } = Dimensions.get("window");
type Tab = "predictions" | "coupon" | "mycoupon" | "profile";

// ─── Ticker ──────────────────────────────────────────────────
const STATUS_COLOR: Record<string, string> = {
  won: C.won, lost: C.lost, live: C.live, pending: C.textMuted,
};

function TickerBar() {
  const scrollX = useRef(new Animated.Value(0)).current;
  const totalWidth = TICKER_SEEDS.reduce(
    (a, t) => a + `${t.match}  ${t.pred}  x${t.odds}   ·   `.length * 7.5,
    0
  );

  useEffect(() => {
    function loop() {
      scrollX.setValue(0);
      Animated.timing(scrollX, {
        toValue: -totalWidth,
        duration: totalWidth * 28,
        easing: Easing.linear,
        useNativeDriver: true,
      }).start(({ finished }) => { if (finished) loop(); });
    }
    loop();
  }, []);

  return (
    <View style={tk.bar}>
      <View style={tk.liveTag}>
        <View style={tk.liveDot} />
        <Text style={tk.liveText}>LIVE</Text>
      </View>
      <View style={tk.overflow}>
        <Animated.View style={[tk.inner, { transform: [{ translateX: scrollX }] }]}>
          {[...TICKER_SEEDS, ...TICKER_SEEDS].map((t, i) => (
            <View key={i} style={tk.item}>
              <Text style={tk.match}>{t.match}</Text>
              <Text style={[tk.pred, { color: STATUS_COLOR[t.status] ?? C.textMuted }]}>{t.pred}</Text>
              <Text style={tk.odds}>x{t.odds}</Text>
              <Text style={tk.sep}>·</Text>
            </View>
          ))}
        </Animated.View>
      </View>
    </View>
  );
}

// ─── Onboarding ───────────────────────────────────────────────
const SLIDES = [
  {
    num: "01", tag: "INTELLIGENCE",
    title: "IA & Algorithme",
    sub: "9 critères analysés\npar match pour des\npronostics solides",
    color: C.primary,
  },
  {
    num: "02", tag: "FOOTBALL",
    title: "Pronostics\nDu Jour",
    sub: "Tous les matchs groupés\npar ligue avec niveaux\nde confiance",
    color: C.accent,
  },
  {
    num: "03", tag: "MON COUPON",
    title: "Crée ton\nCoupon",
    sub: "Sélectionne tes pronos\npréférés et construis\nton propre combiné",
    color: C.gold,
  },
  {
    num: "04", tag: "PREMIUM",
    title: "Combiné IA\ndu Jour",
    sub: "La meilleure sélection\ngénérée chaque matin\nà 7h00 par l'IA",
    color: "#A855F7",
  },
];

function OnboardingScreen({ onDone }: { onDone: () => void }) {
  const [idx, setIdx] = useState(0);
  const fade = useRef(new Animated.Value(1)).current;
  const slideY = useRef(new Animated.Value(0)).current;

  function transition(next: () => void) {
    Animated.parallel([
      Animated.timing(fade, { toValue: 0, duration: 200, useNativeDriver: true }),
      Animated.timing(slideY, { toValue: -20, duration: 200, useNativeDriver: true }),
    ]).start(() => {
      next();
      slideY.setValue(28);
      Animated.parallel([
        Animated.timing(fade, { toValue: 1, duration: 300, easing: Easing.out(Easing.cubic), useNativeDriver: true }),
        Animated.timing(slideY, { toValue: 0, duration: 300, easing: Easing.out(Easing.cubic), useNativeDriver: true }),
      ]).start();
    });
  }

  function next() {
    if (idx < SLIDES.length - 1) transition(() => setIdx((i) => i + 1));
    else onDone();
  }

  const slide = SLIDES[idx];

  return (
    <SafeAreaView style={[ob.root, { backgroundColor: C.bg }]}>
      <StatusBar style="light" />
      <View style={ob.topBar}>
        <Text style={ob.logo}>COTA</Text>
        <TouchableOpacity onPress={onDone}><Text style={ob.skip}>Passer</Text></TouchableOpacity>
      </View>

      {/* Giant number bg */}
      <Animated.Text style={[ob.bigNum, { color: `${slide.color}10` }]}>{slide.num}</Animated.Text>

      {/* Content */}
      <Animated.View style={[ob.content, { opacity: fade, transform: [{ translateY: slideY }] }]}>
        <View style={[ob.tag, { borderColor: `${slide.color}44`, backgroundColor: `${slide.color}18` }]}>
          <Text style={[ob.tagText, { color: slide.color }]}>{slide.tag}</Text>
        </View>
        <Text style={ob.title}>{slide.title}</Text>
        <Text style={ob.sub}>{slide.sub}</Text>
      </Animated.View>

      <View style={ob.bottom}>
        <View style={ob.dots}>
          {SLIDES.map((ss, i) => (
            <View key={i} style={[ob.dot, { backgroundColor: i === idx ? ss.color : C.dim, width: i === idx ? 22 : 7 }]} />
          ))}
        </View>
        <TouchableOpacity style={[ob.btn, { backgroundColor: slide.color }]} onPress={next} activeOpacity={0.85}>
          <Text style={ob.btnText}>{idx < SLIDES.length - 1 ? "Suivant →" : "Commencer"}</Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

// ─── App ─────────────────────────────────────────────────────
const TABS: { key: Tab; label: string; icon: string }[] = [
  { key: "predictions", label: "Pronostics", icon: "⚽" },
  { key: "coupon", label: "Combiné IA", icon: "🤖" },
  { key: "mycoupon", label: "Mon Coupon", icon: "🎯" },
  { key: "profile", label: "Profil", icon: "👤" },
];

export default function App() {
  const [session, setSession] = useState<Session | null>(null);
  const [tab, setTab] = useState<Tab>("predictions");
  const [showLogin, setShowLogin] = useState(false);
  const [showOnboarding, setShowOnboarding] = useState(true);
  const [showMyCoupon, setShowMyCoupon] = useState(false);
  const [showAutoFill, setShowAutoFill] = useState(false);

  const myCoupon = useMyCoupon();
  const tabX = useRef(new Animated.Value(0)).current;
  const tabW = width / TABS.length;

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => setSession(session));
    const { data: { subscription } } = supabase.auth.onAuthStateChange((_e, s) => {
      setSession(s);
      if (s) setShowLogin(false);
    });
    return () => subscription.unsubscribe();
  }, []);

  useEffect(() => {
    const i = TABS.findIndex((t) => t.key === tab);
    Animated.spring(tabX, { toValue: i * tabW, useNativeDriver: true, tension: 90, friction: 11 }).start();
  }, [tab]);

  const isPremium =
    (session?.user as any)?.user_metadata?.role === "premium" ||
    (session?.user as any)?.user_metadata?.role === "admin";

  if (showOnboarding) return <OnboardingScreen onDone={() => setShowOnboarding(false)} />;
  if (showLogin) return <LoginScreen />;

  return (
    <SafeAreaView style={app.root}>
      <StatusBar style="light" />

      {/* Header */}
      <View style={app.header}>
        <View style={app.headerLeft}>
          <Text style={app.logo}>COTA</Text>
          <View style={app.liveChip}>
            <View style={app.liveDot} />
            <Text style={app.liveChipText}>PRONOSTICS</Text>
          </View>
        </View>
        <View style={app.headerRight}>
          {isPremium && (
            <View style={app.proBadge}><Text style={app.proBadgeText}>PRO</Text></View>
          )}
          {session ? (
            <TouchableOpacity onPress={() => supabase.auth.signOut()} style={app.authBtn}>
              <Text style={app.authBtnText}>Déconn.</Text>
            </TouchableOpacity>
          ) : (
            <TouchableOpacity onPress={() => setShowLogin(true)} style={[app.authBtn, app.authBtnOutline]}>
              <Text style={[app.authBtnText, { color: C.primary }]}>Connexion</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Ticker */}
      <TickerBar />

      {/* Content */}
      <View style={app.content}>
        {tab === "predictions" && (
          <PredictionsScreen
            isPremiumUser={isPremium}
            couponPicks={myCoupon.picks}
            onToggleCoupon={myCoupon.toggle}
            onOpenCoupon={() => setShowMyCoupon(true)}
          />
        )}
        {tab === "coupon" && <CouponScreen isPremiumUser={isPremium} />}
        {tab === "mycoupon" && (
          <MyCouponSheetEmbed
            picks={myCoupon.picks}
            totalOdds={myCoupon.totalOdds}
            onRemove={myCoupon.remove}
            onClear={myCoupon.clear}
            onAutoFill={() => setShowAutoFill(true)}
          />
        )}
        {tab === "profile" && (
          <ProfileScreen session={session} onLogin={() => setShowLogin(true)} />
        )}
      </View>

      {/* Bottom nav */}
      <View style={app.nav}>
        <Animated.View
          style={[
            app.navIndicator,
            { width: tabW - 28, transform: [{ translateX: Animated.add(tabX, new Animated.Value(14)) }] },
          ]}
        />
        {TABS.map((t) => {
          const active = tab === t.key;
          const isMyCoupon = t.key === "mycoupon";
          return (
            <TouchableOpacity
              key={t.key}
              style={app.navItem}
              onPress={() => setTab(t.key)}
              activeOpacity={0.7}
            >
              <View style={app.navIconWrap}>
                <Text style={[app.navIcon, active && { opacity: 1 }]}>{t.icon}</Text>
                {isMyCoupon && myCoupon.picks.length > 0 && (
                  <View style={app.navBadge}>
                    <Text style={app.navBadgeText}>{myCoupon.picks.length}</Text>
                  </View>
                )}
              </View>
              <Text style={[app.navLabel, active && app.navLabelActive]}>{t.label}</Text>
            </TouchableOpacity>
          );
        })}
      </View>

      {/* Auto-fill bookmakers */}
      <AutoFillSheet
        visible={showAutoFill}
        picks={myCoupon.picks}
        onClose={() => setShowAutoFill(false)}
      />

      {/* My Coupon sheet (from FAB in PredictionsScreen) */}
      <MyCouponSheet
        visible={showMyCoupon}
        picks={myCoupon.picks}
        totalOdds={myCoupon.totalOdds}
        onRemove={myCoupon.remove}
        onClear={myCoupon.clear}
        onClose={() => setShowMyCoupon(false)}
      />
    </SafeAreaView>
  );
}

// ─── Mon Coupon tab (full screen version) ────────────────────
function MyCouponSheetEmbed({
  picks, totalOdds, onRemove, onClear, onAutoFill,
}: { picks: any[]; totalOdds: number; onRemove: (id: string) => void; onClear: () => void; onAutoFill?: () => void }) {
  const LEAGUE_COLORS = [C.primary, C.accent, C.gold, "#A855F7", "#EC4899", "#06B6D4"];

  if (picks.length === 0) {
    return (
      <View style={em.center}>
        <Text style={em.emptyIcon}>🎯</Text>
        <Text style={em.emptyTitle}>Ton coupon est vide</Text>
        <Text style={em.emptySub}>
          Va sur l'onglet Pronostics{"\n"}et clique sur + pour ajouter des picks
        </Text>
        <View style={em.hint}>
          <Text style={em.hintText}>⚽ Pronostics → Clique + sur un match</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={{ flex: 1, backgroundColor: C.bg }}>
      {/* Header */}
      <View style={em.header}>
        <Text style={em.headerTitle}>Mon Coupon</Text>
        <View style={em.headerBadge}>
          <Text style={em.headerBadgeText}>{picks.length} sélections</Text>
        </View>
      </View>

      {/* Cote */}
      <View style={em.heroCard}>
        <View style={em.heroLeft}>
          <Text style={em.heroLabel}>Cote combinée</Text>
          <Text style={em.heroOdds}>x{totalOdds.toFixed(2)}</Text>
        </View>
        <View style={em.heroRight}>
          <Text style={em.heroGain}>{(totalOdds * 500).toFixed(0)} XOF</Text>
          <Text style={em.heroGainLabel}>gain (mise 500)</Text>
          <TouchableOpacity onPress={onClear} style={em.clearBtn}>
            <Text style={em.clearBtnText}>Vider</Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Picks */}
      {picks.map((pk, i) => {
        const color = LEAGUE_COLORS[i % LEAGUE_COLORS.length];
        return (
          <View key={pk.id} style={[em.pickRow, { borderLeftColor: color }]}>
            <View style={em.pickBody}>
              <Text style={[em.pickLeague, { color }]} numberOfLines={1}>{pk.league}</Text>
              <Text style={em.pickMatch} numberOfLines={1}>{pk.home_team} — {pk.away_team}</Text>
              <View style={em.pickBottom}>
                <View style={em.predBadge}>
                  <Text style={em.predText}>{pk.prediction}</Text>
                </View>
                <Text style={em.pickOdds}>x{pk.odds.toFixed(2)}</Text>
              </View>
            </View>
            <TouchableOpacity
              onPress={() => onRemove(pk.id)}
              style={em.removeBtn}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Text style={em.removeBtnText}>×</Text>
            </TouchableOpacity>
          </View>
        );
      })}

      {/* CTA Parier */}
      {picks.length > 0 && (
        <TouchableOpacity style={em.betBtn} onPress={onAutoFill} activeOpacity={0.85}>
          <Text style={em.betBtnText}>⚡ Parier maintenant</Text>
        </TouchableOpacity>
      )}

      {/* Jeu responsable */}
      <View style={em.responsible}>
        <Text style={em.responsibleText}>
          🛡️ 18+ · Jeu responsable · joueurs-info-service.fr · 09 74 75 13 13
        </Text>
      </View>
    </View>
  );
}

// ─── Styles ───────────────────────────────────────────────────
const ob = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  topBar: { flexDirection: "row", justifyContent: "space-between", alignItems: "center", paddingHorizontal: 24, paddingTop: 12, paddingBottom: 8 },
  logo: { color: C.primary, fontSize: 22, fontWeight: "900", letterSpacing: 3 },
  skip: { color: C.textMuted, fontSize: 13 },
  bigNum: { position: "absolute", top: "12%", right: -20, fontSize: 220, fontWeight: "900", letterSpacing: -8 },
  content: { flex: 1, paddingHorizontal: 28, paddingTop: "20%", gap: 20 },
  tag: { alignSelf: "flex-start", borderWidth: 1, borderRadius: 4, paddingHorizontal: 10, paddingVertical: 4 },
  tagText: { fontSize: 10, fontWeight: "800", letterSpacing: 1.5 },
  title: { color: C.textPrimary, fontSize: 42, fontWeight: "900", lineHeight: 46, letterSpacing: -0.5 },
  sub: { color: C.textSecondary, fontSize: 15, lineHeight: 24 },
  bottom: { paddingHorizontal: 24, paddingBottom: 40, gap: 20 },
  dots: { flexDirection: "row", gap: 6, alignItems: "center" },
  dot: { height: 7, borderRadius: 4 },
  btn: { borderRadius: 12, paddingVertical: 17, alignItems: "center" },
  btnText: { color: "#fff", fontWeight: "800", fontSize: 16 },
});

const app = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  header: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: C.bg2,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  headerLeft: { flexDirection: "row", alignItems: "center", gap: 10 },
  logo: { color: C.primary, fontSize: 20, fontWeight: "900", letterSpacing: 3 },
  liveChip: {
    flexDirection: "row",
    alignItems: "center",
    gap: 5,
    backgroundColor: `${C.live}18`,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
    borderWidth: 1,
    borderColor: `${C.live}33`,
  },
  liveDot: { width: 5, height: 5, borderRadius: 3, backgroundColor: C.live },
  liveChipText: { color: C.live, fontSize: 9, fontWeight: "800", letterSpacing: 1 },
  headerRight: { flexDirection: "row", alignItems: "center", gap: 8 },
  proBadge: { backgroundColor: C.primary, paddingHorizontal: 8, paddingVertical: 3, borderRadius: 4 },
  proBadgeText: { color: "#fff", fontSize: 10, fontWeight: "800", letterSpacing: 0.8 },
  authBtn: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 7, backgroundColor: C.bg3, borderWidth: 1, borderColor: C.border },
  authBtnOutline: { borderColor: `${C.primary}66`, backgroundColor: `${C.primary}12` },
  authBtnText: { color: C.textSecondary, fontSize: 12, fontWeight: "700" },
  content: { flex: 1, backgroundColor: C.bg },
  nav: {
    flexDirection: "row",
    backgroundColor: C.bg2,
    borderTopWidth: 1,
    borderTopColor: C.divider,
    paddingBottom: 4,
    paddingTop: 2,
    position: "relative",
  },
  navIndicator: { position: "absolute", top: 0, height: 2, backgroundColor: C.primary, borderRadius: 1 },
  navItem: { flex: 1, alignItems: "center", paddingVertical: 7, gap: 2 },
  navIconWrap: { position: "relative" },
  navIcon: { fontSize: 18, opacity: 0.55 },
  navBadge: {
    position: "absolute",
    top: -4,
    right: -8,
    backgroundColor: C.primary,
    width: 16,
    height: 16,
    borderRadius: 8,
    alignItems: "center",
    justifyContent: "center",
  },
  navBadgeText: { color: "#fff", fontSize: 9, fontWeight: "900" },
  navLabel: { color: C.textMuted, fontSize: 10, fontWeight: "600" },
  navLabelActive: { color: C.primary },
});

const tk = StyleSheet.create({
  bar: { flexDirection: "row", alignItems: "center", backgroundColor: C.bg3, borderBottomWidth: 1, borderBottomColor: C.divider, height: 32, overflow: "hidden" },
  liveTag: { flexDirection: "row", alignItems: "center", gap: 4, paddingHorizontal: 10, borderRightWidth: 1, borderRightColor: C.border, height: "100%" },
  liveDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: C.live },
  liveText: { color: C.live, fontSize: 9, fontWeight: "800", letterSpacing: 1 },
  overflow: { flex: 1, overflow: "hidden" },
  inner: { flexDirection: "row", alignItems: "center" },
  item: { flexDirection: "row", alignItems: "center", gap: 6, paddingHorizontal: 10 },
  match: { color: C.textSecondary, fontSize: 11 },
  pred: { fontSize: 11, fontWeight: "700" },
  odds: { color: C.gold, fontSize: 11, fontWeight: "700" },
  sep: { color: C.dim, fontSize: 14 },
});

const em = StyleSheet.create({
  center: { flex: 1, alignItems: "center", justifyContent: "center", gap: 12, padding: 32 },
  emptyIcon: { fontSize: 52 },
  emptyTitle: { color: C.textPrimary, fontSize: 20, fontWeight: "800" },
  emptySub: { color: C.textMuted, fontSize: 14, textAlign: "center", lineHeight: 22 },
  hint: { backgroundColor: C.bg3, borderRadius: 10, borderWidth: 1, borderColor: C.border, paddingHorizontal: 16, paddingVertical: 10, marginTop: 8 },
  hintText: { color: C.primary, fontSize: 13, fontWeight: "600" },
  header: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  headerTitle: { color: C.textPrimary, fontSize: 17, fontWeight: "800" },
  headerBadge: { backgroundColor: `${C.primary}22`, borderRadius: 20, paddingHorizontal: 10, paddingVertical: 3 },
  headerBadgeText: { color: C.primary, fontSize: 11, fontWeight: "700" },
  heroCard: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    padding: 16,
  },
  heroLeft: { gap: 2 },
  heroLabel: { color: C.textMuted, fontSize: 11 },
  heroOdds: { color: C.accent, fontSize: 36, fontWeight: "900" },
  heroRight: { alignItems: "flex-end", gap: 4 },
  heroGain: { color: C.accent, fontSize: 18, fontWeight: "800" },
  heroGainLabel: { color: C.textMuted, fontSize: 10 },
  clearBtn: { backgroundColor: `${C.live}18`, borderRadius: 6, paddingHorizontal: 10, paddingVertical: 4, marginTop: 4 },
  clearBtnText: { color: C.live, fontSize: 12, fontWeight: "700" },
  pickRow: {
    flexDirection: "row",
    alignItems: "center",
    borderLeftWidth: 3,
    backgroundColor: C.bg2,
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
  predBadge: { backgroundColor: `${C.primary}1A`, borderWidth: 1, borderColor: `${C.primary}44`, borderRadius: 4, paddingHorizontal: 8, paddingVertical: 3 },
  predText: { color: C.primaryLight, fontSize: 11, fontWeight: "800" },
  pickOdds: { color: C.accent, fontSize: 12, fontWeight: "800" },
  removeBtn: { width: 28, height: 28, borderRadius: 14, backgroundColor: `${C.live}18`, alignItems: "center", justifyContent: "center" },
  removeBtnText: { color: C.live, fontSize: 18, lineHeight: 22, fontWeight: "300" },
  betBtn: {
    backgroundColor: C.primary,
    marginHorizontal: 16,
    marginTop: 12,
    borderRadius: 12,
    paddingVertical: 15,
    alignItems: "center",
    shadowColor: C.primary,
    shadowOpacity: 0.35,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 4 },
    elevation: 6,
  },
  betBtnText: { color: "#fff", fontWeight: "900", fontSize: 16 },
  responsible: {
    marginHorizontal: 16,
    marginTop: 10,
    backgroundColor: `${C.gold}10`,
    borderRadius: 8,
    padding: 10,
    marginBottom: 8,
  },
  responsibleText: { color: C.gold, fontSize: 10, lineHeight: 16, textAlign: "center" },
});
