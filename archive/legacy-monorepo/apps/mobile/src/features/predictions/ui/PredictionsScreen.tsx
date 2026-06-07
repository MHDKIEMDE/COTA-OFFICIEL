import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useState, useEffect } from "react";
import PredictionCard from "./PredictionCard";
import { C } from "@/theme/colors";
import { SEED_PREDICTIONS } from "@/data/seeds";
import { CouponPick } from "@/features/mycoupon/logic/useMyCoupon";

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000";
type TabKey = "all" | "free" | "premium";

function fmt(d: Date) { return d.toISOString().split("T")[0]; }
function dateLabel(d: Date) {
  const today = new Date();
  const yest = new Date(today); yest.setDate(today.getDate() - 1);
  if (fmt(d) === fmt(today)) return "Aujourd'hui";
  if (fmt(d) === fmt(yest)) return "Hier";
  return d.toLocaleDateString("fr-FR", { weekday: "short", day: "numeric", month: "short" });
}

const LEAGUE_COLORS = [C.primary, C.accent, C.gold, "#A855F7", "#EC4899", "#06B6D4"];

export default function PredictionsScreen({
  isPremiumUser = false,
  couponPicks,
  onToggleCoupon,
  onOpenCoupon,
}: {
  isPremiumUser?: boolean;
  couponPicks: CouponPick[];
  onToggleCoupon: (pick: CouponPick) => void;
  onOpenCoupon: () => void;
}) {
  const [predictions, setPredictions] = useState<any[]>([]);
  const [status, setStatus] = useState<"loading" | "error" | "empty" | "success">("loading");
  const [tab, setTab] = useState<TabKey>("all");
  const [date, setDate] = useState(new Date());
  const [refreshing, setRefreshing] = useState(false);

  async function load(d: Date) {
    setStatus("loading");
    try {
      const ds = fmt(d);
      const url = ds === fmt(new Date())
        ? `${API_URL}/predictions/today`
        : `${API_URL}/predictions/today?date=${ds}`;
      const r = await fetch(url);
      const json = await r.json();
      const data = json.data ?? [];
      setPredictions(data.length === 0 ? SEED_PREDICTIONS : data);
      setStatus("success");
    } catch {
      setPredictions(SEED_PREDICTIONS);
      setStatus("success");
    }
  }

  useEffect(() => { load(date); }, [date]);

  async function onRefresh() {
    setRefreshing(true);
    await load(date);
    setRefreshing(false);
  }

  function goDate(delta: number) {
    const d = new Date(date);
    d.setDate(d.getDate() + delta);
    if (d > new Date()) return;
    setDate(d);
  }

  const filtered = predictions.filter((p) => {
    if (tab === "free") return !p.is_premium;
    if (tab === "premium") return p.is_premium;
    return true;
  });

  const grouped: Record<string, any[]> = {};
  for (const p of filtered) {
    const league = p.matches?.league_name ?? p.matches?.league ?? "Autre";
    if (!grouped[league]) grouped[league] = [];
    grouped[league].push(p);
  }

  const items: any[] = [];
  Object.keys(grouped).forEach((league, li) => {
    items.push({ type: "section", league, color: LEAGUE_COLORS[li % LEAGUE_COLORS.length] });
    grouped[league].forEach((p) => items.push({ type: "card", prediction: p }));
  });

  const TABS = [
    { key: "all" as TabKey, label: "Tous", count: predictions.length },
    { key: "free" as TabKey, label: "Gratuits", count: predictions.filter((p) => !p.is_premium).length },
    { key: "premium" as TabKey, label: "Premium", count: predictions.filter((p) => p.is_premium).length },
  ];

  const isToday = fmt(date) === fmt(new Date());
  const couponCount = couponPicks.length;

  return (
    <View style={s.root}>
      {/* Date nav */}
      <View style={s.dateNav}>
        <TouchableOpacity onPress={() => goDate(-1)} style={s.dateArrow}>
          <Text style={s.dateArrowText}>‹</Text>
        </TouchableOpacity>
        <View style={s.datePill}>
          <Text style={s.dateLabel}>{dateLabel(date)}</Text>
          {isToday && (
            <View style={s.todayBadge}>
              <View style={s.todayDot} />
              <Text style={s.todayText}>LIVE</Text>
            </View>
          )}
        </View>
        <TouchableOpacity
          onPress={() => goDate(1)}
          style={[s.dateArrow, isToday && s.dateArrowOff]}
          disabled={isToday}
        >
          <Text style={[s.dateArrowText, isToday && { color: C.dim }]}>›</Text>
        </TouchableOpacity>
      </View>

      {/* Filter tabs */}
      <View style={s.tabs}>
        {TABS.map((t) => (
          <TouchableOpacity
            key={t.key}
            style={[s.tab, tab === t.key && s.tabActive]}
            onPress={() => setTab(t.key)}
          >
            <Text style={[s.tabText, tab === t.key && s.tabTextActive]}>{t.label}</Text>
            <View style={[s.tabCount, tab === t.key && s.tabCountActive]}>
              <Text style={[s.tabCountText, tab === t.key && s.tabCountTextActive]}>{t.count}</Text>
            </View>
          </TouchableOpacity>
        ))}
      </View>

      {/* Loading */}
      {status === "loading" && !refreshing && (
        <View style={s.center}>
          <ActivityIndicator color={C.primary} size="large" />
          <Text style={s.loadingText}>Chargement des pronostics…</Text>
        </View>
      )}

      {/* List */}
      {(status === "success" || refreshing) && (
        <FlatList
          data={items}
          keyExtractor={(item, i) =>
            item.type === "section" ? `s-${item.league}-${i}` : `c-${item.prediction?.id ?? i}`
          }
          renderItem={({ item }) => {
            if (item.type === "section") {
              return (
                <View style={[s.leagueHeader, { borderLeftColor: item.color }]}>
                  <View style={[s.leagueDot, { backgroundColor: item.color }]} />
                  <Text style={[s.leagueName, { color: item.color }]}>{item.league}</Text>
                  <View style={s.leagueLine} />
                </View>
              );
            }
            const p = item.prediction;
            const match = p.matches ?? p.match ?? {};
            const pickData: CouponPick = {
              id: p.id,
              home_team: match.home_team ?? "—",
              away_team: match.away_team ?? "—",
              league: match.league_name ?? match.league ?? "—",
              prediction: p.prediction ?? "—",
              odds: p.odds ?? 1,
              is_premium: p.is_premium ?? false,
            };
            const inCoupon = couponPicks.some((cp) => cp.id === p.id);
            return (
              <PredictionCard
                prediction={p}
                isPremiumUser={isPremiumUser}
                inCoupon={inCoupon}
                onToggleCoupon={!p.result ? () => onToggleCoupon(pickData) : undefined}
              />
            );
          }}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={C.primary} colors={[C.primary]} />
          }
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingBottom: couponCount > 0 ? 90 : 32 }}
        />
      )}

      {status === "empty" && (
        <View style={s.center}>
          <Text style={s.emptyIcon}>⚽</Text>
          <Text style={s.emptyTitle}>Aucun pronostic</Text>
          <Text style={s.emptyText}>Revenez ce soir</Text>
        </View>
      )}

      {/* FAB — Mon coupon flottant */}
      {couponCount > 0 && (
        <TouchableOpacity style={s.fab} onPress={onOpenCoupon} activeOpacity={0.88}>
          <View style={s.fabBadge}>
            <Text style={s.fabBadgeText}>{couponCount}</Text>
          </View>
          <Text style={s.fabText}>Mon Coupon</Text>
          <Text style={s.fabOdds}>
            x{couponPicks.reduce((a, p) => a * p.odds, 1).toFixed(2)}
          </Text>
          <Text style={s.fabArrow}>›</Text>
        </TouchableOpacity>
      )}
    </View>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  dateNav: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingVertical: 6,
    paddingHorizontal: 8,
    gap: 6,
  },
  dateArrow: { padding: 6, paddingHorizontal: 10 },
  dateArrowOff: { opacity: 0.25 },
  dateArrowText: { color: C.primary, fontSize: 24, fontWeight: "300", lineHeight: 28 },
  datePill: {
    flex: 1,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 8,
    backgroundColor: C.bg3,
    borderRadius: 6,
    paddingVertical: 7,
  },
  dateLabel: { color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  todayBadge: {
    flexDirection: "row",
    alignItems: "center",
    gap: 4,
    backgroundColor: `${C.live}22`,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  todayDot: { width: 5, height: 5, borderRadius: 3, backgroundColor: C.live },
  todayText: { color: C.live, fontSize: 9, fontWeight: "800", letterSpacing: 1 },
  tabs: {
    flexDirection: "row",
    backgroundColor: C.bg2,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    paddingHorizontal: 8,
  },
  tab: {
    flex: 1,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 10,
    gap: 6,
    borderBottomWidth: 2,
    borderBottomColor: "transparent",
  },
  tabActive: { borderBottomColor: C.primary },
  tabText: { color: C.textMuted, fontSize: 12, fontWeight: "600" },
  tabTextActive: { color: C.primary },
  tabCount: {
    backgroundColor: C.bg3,
    borderRadius: 10,
    minWidth: 20,
    height: 18,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 5,
  },
  tabCountActive: { backgroundColor: `${C.primary}22` },
  tabCountText: { color: C.textMuted, fontSize: 10, fontWeight: "700" },
  tabCountTextActive: { color: C.primary },
  leagueHeader: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: C.bg3,
    borderLeftWidth: 3,
    paddingHorizontal: 12,
    paddingVertical: 8,
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
  },
  leagueDot: { width: 6, height: 6, borderRadius: 3 },
  leagueName: { fontSize: 11, fontWeight: "800", letterSpacing: 0.5, textTransform: "uppercase" },
  leagueLine: { flex: 1 },
  center: { flex: 1, alignItems: "center", justifyContent: "center", gap: 10 },
  loadingText: { color: C.textMuted, fontSize: 13 },
  emptyIcon: { fontSize: 44 },
  emptyTitle: { color: C.textSecondary, fontSize: 16, fontWeight: "700" },
  emptyText: { color: C.textMuted, fontSize: 13 },

  // FAB
  fab: {
    position: "absolute",
    bottom: 16,
    left: 16,
    right: 16,
    backgroundColor: C.primary,
    borderRadius: 14,
    paddingHorizontal: 16,
    paddingVertical: 14,
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    shadowColor: C.primary,
    shadowOpacity: 0.4,
    shadowRadius: 20,
    shadowOffset: { width: 0, height: 6 },
    elevation: 8,
  },
  fabBadge: {
    backgroundColor: "#fff",
    width: 24,
    height: 24,
    borderRadius: 12,
    alignItems: "center",
    justifyContent: "center",
  },
  fabBadgeText: { color: C.primary, fontSize: 12, fontWeight: "900" },
  fabText: { flex: 1, color: "#fff", fontSize: 15, fontWeight: "800" },
  fabOdds: { color: "rgba(255,255,255,0.8)", fontSize: 13, fontWeight: "700" },
  fabArrow: { color: "#fff", fontSize: 20, fontWeight: "300" },
});
