import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { useState, useEffect } from "react";
import PredictionCard from "./PredictionCard";
import { C } from "@/theme/colors";

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8000";

type TabKey = "all" | "free" | "premium";

function formatDateKey(date: Date) {
  return date.toISOString().split("T")[0];
}

function formatDateLabel(date: Date) {
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(today.getDate() - 1);

  if (formatDateKey(date) === formatDateKey(today)) return "Aujourd'hui";
  if (formatDateKey(date) === formatDateKey(yesterday)) return "Hier";

  return date.toLocaleDateString("fr-FR", { weekday: "short", day: "numeric", month: "short" });
}

export default function PredictionsScreen({ isPremiumUser = false }: { isPremiumUser?: boolean }) {
  const [predictions, setPredictions] = useState<any[]>([]);
  const [status, setStatus] = useState<"loading" | "error" | "empty" | "success">("loading");
  const [tab, setTab] = useState<TabKey>("all");
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [refreshing, setRefreshing] = useState(false);

  async function load(date: Date) {
    setStatus("loading");
    try {
      const dateStr = formatDateKey(date);
      const url = dateStr === formatDateKey(new Date())
        ? `${API_URL}/predictions/today`
        : `${API_URL}/predictions/today?date=${dateStr}`;
      const r = await fetch(url);
      const json = await r.json();
      const data = json.data ?? [];
      setPredictions(data);
      setStatus(data.length === 0 ? "empty" : "success");
    } catch {
      setStatus("error");
    }
  }

  useEffect(() => { load(selectedDate); }, [selectedDate]);

  async function onRefresh() {
    setRefreshing(true);
    await load(selectedDate);
    setRefreshing(false);
  }

  function goDate(delta: number) {
    const d = new Date(selectedDate);
    d.setDate(d.getDate() + delta);
    if (d > new Date()) return; // pas dans le futur
    setSelectedDate(d);
  }

  // Filtrage par tab
  const filtered = predictions.filter((p) => {
    if (tab === "free") return !p.is_premium;
    if (tab === "premium") return p.is_premium;
    return true;
  });

  // Groupement par ligue
  const grouped: Record<string, any[]> = {};
  for (const p of filtered) {
    const league = p.matches?.league_name ?? p.matches?.league ?? "Autre";
    if (!grouped[league]) grouped[league] = [];
    grouped[league].push(p);
  }

  // Flat list items: sections + cards
  const items: any[] = [];
  for (const [league, preds] of Object.entries(grouped)) {
    items.push({ type: "section", league });
    for (const p of preds) items.push({ type: "card", prediction: p });
  }

  const TABS: { key: TabKey; label: string }[] = [
    { key: "all", label: "Tous" },
    { key: "free", label: "Gratuits" },
    { key: "premium", label: "Premium" },
  ];

  return (
    <View style={styles.container}>
      {/* Navigation par date */}
      <View style={styles.dateBar}>
        <TouchableOpacity onPress={() => goDate(-1)} style={styles.dateArrow}>
          <Text style={styles.dateArrowText}>‹</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.datePill}>
          <Text style={styles.dateText}>{formatDateLabel(selectedDate)}</Text>
          {formatDateKey(selectedDate) === formatDateKey(new Date()) && (
            <View style={styles.todayBadge}>
              <Text style={styles.todayBadgeText}>LIVE</Text>
            </View>
          )}
        </TouchableOpacity>
        <TouchableOpacity
          onPress={() => goDate(1)}
          style={[styles.dateArrow, formatDateKey(selectedDate) === formatDateKey(new Date()) && styles.dateArrowDisabled]}
          disabled={formatDateKey(selectedDate) === formatDateKey(new Date())}
        >
          <Text style={[styles.dateArrowText, formatDateKey(selectedDate) === formatDateKey(new Date()) && { color: C.textDisabled }]}>›</Text>
        </TouchableOpacity>
      </View>

      {/* Tabs */}
      <View style={styles.tabBar}>
        {TABS.map((t) => (
          <TouchableOpacity
            key={t.key}
            style={[styles.tab, tab === t.key && styles.tabActive]}
            onPress={() => setTab(t.key)}
          >
            <Text style={[styles.tabText, tab === t.key && styles.tabTextActive]}>
              {t.label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Contenu */}
      {status === "loading" && !refreshing && (
        <View style={styles.center}>
          <ActivityIndicator color={C.primary} size="large" />
        </View>
      )}

      {status === "error" && (
        <View style={styles.center}>
          <Text style={styles.errorIcon}>⚠️</Text>
          <Text style={styles.errorText}>Erreur de chargement</Text>
          <TouchableOpacity onPress={() => load(selectedDate)} style={styles.retryBtn}>
            <Text style={styles.retryText}>Réessayer</Text>
          </TouchableOpacity>
        </View>
      )}

      {(status === "empty" || (status === "success" && items.length === 0)) && (
        <View style={styles.center}>
          <Text style={styles.emptyIcon}>⚽</Text>
          <Text style={styles.emptyText}>Aucun pronostic disponible</Text>
          <Text style={styles.emptySubText}>Revenez plus tard</Text>
        </View>
      )}

      {status === "success" && items.length > 0 && (
        <FlatList
          data={items}
          keyExtractor={(item, i) =>
            item.type === "section" ? `s-${item.league}` : `p-${item.prediction.id ?? i}`
          }
          renderItem={({ item }) =>
            item.type === "section" ? (
              <View style={styles.sectionHeader}>
                <Text style={styles.sectionText}>{item.league}</Text>
              </View>
            ) : (
              <PredictionCard
                prediction={item.prediction}
                isPremiumUser={isPremiumUser}
              />
            )
          }
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={C.primary} />
          }
          contentContainerStyle={styles.list}
          showsVerticalScrollIndicator={false}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: C.bg },
  dateBar: {
    flexDirection: "row",
    alignItems: "center",
    backgroundColor: C.bgSecondary,
    borderBottomWidth: 0.5,
    borderBottomColor: C.border,
    paddingHorizontal: 12,
    paddingVertical: 8,
    gap: 8,
  },
  dateArrow: { padding: 8 },
  dateArrowDisabled: { opacity: 0.3 },
  dateArrowText: { color: C.primary, fontSize: 22, fontWeight: "600" },
  datePill: {
    flex: 1,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    backgroundColor: C.bgTertiary,
    borderRadius: 6,
    paddingVertical: 8,
    gap: 8,
  },
  dateText: { color: C.textPrimary, fontSize: 13, fontWeight: "700" },
  todayBadge: {
    backgroundColor: `${C.live}33`,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  todayBadgeText: { color: C.live, fontSize: 9, fontWeight: "800" },
  tabBar: {
    flexDirection: "row",
    backgroundColor: C.bgSecondary,
    borderBottomWidth: 0.5,
    borderBottomColor: C.border,
    paddingHorizontal: 12,
    gap: 4,
    paddingBottom: 0,
  },
  tab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: "center",
    borderBottomWidth: 2,
    borderBottomColor: "transparent",
  },
  tabActive: { borderBottomColor: C.primary },
  tabText: { color: C.textMuted, fontSize: 13, fontWeight: "600" },
  tabTextActive: { color: C.primary },
  list: { paddingHorizontal: 12, paddingTop: 8, paddingBottom: 32 },
  sectionHeader: {
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 8,
    paddingTop: 16,
    gap: 8,
  },
  sectionText: { color: C.textSecondary, fontSize: 12, fontWeight: "700", textTransform: "uppercase", letterSpacing: 0.5 },
  center: { flex: 1, alignItems: "center", justifyContent: "center", gap: 10 },
  errorIcon: { fontSize: 36 },
  errorText: { color: C.error, fontSize: 15, fontWeight: "600" },
  retryBtn: {
    marginTop: 4,
    backgroundColor: C.primary,
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 8,
  },
  retryText: { color: "#fff", fontWeight: "700", fontSize: 13 },
  emptyIcon: { fontSize: 40 },
  emptyText: { color: C.textSecondary, fontSize: 15, fontWeight: "600" },
  emptySubText: { color: C.textDisabled, fontSize: 12 },
});
