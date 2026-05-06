import {
  ActivityIndicator,
  FlatList,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { usePredictions } from "../logic/usePredictions";
import PredictionCard from "./PredictionCard";

const TIERS = [
  { value: null, label: "Tous" },
  { value: 1, label: "Tier 1" },
  { value: 2, label: "Tier 2" },
  { value: 3, label: "Tier 3" },
  { value: 4, label: "Tier 4" },
];

export default function PredictionsScreen({ isPremiumUser = false }: { isPremiumUser?: boolean }) {
  const { predictions, tier, setTier, status } = usePredictions();

  return (
    <View style={styles.container}>
      {/* Titre */}
      <Text style={styles.title}>Pronostics du jour</Text>

      {/* Filtres tiers */}
      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.filters}>
        {TIERS.map((t) => (
          <TouchableOpacity
            key={String(t.value)}
            onPress={() => setTier(t.value)}
            style={[styles.chip, tier === t.value && styles.chipActive]}
          >
            <Text style={[styles.chipText, tier === t.value && styles.chipTextActive]}>
              {t.label}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* États */}
      {status === "loading" && (
        <View style={styles.center}>
          <ActivityIndicator color="#4ade80" size="large" />
        </View>
      )}

      {status === "error" && (
        <View style={styles.center}>
          <Text style={styles.errorText}>Erreur de chargement</Text>
          <Text style={styles.subText}>Vérifie ta connexion</Text>
        </View>
      )}

      {(status === "empty" || (status === "success" && predictions.length === 0)) && (
        <View style={styles.center}>
          <Text style={styles.emptyText}>Aucune prédiction disponible</Text>
          <Text style={styles.subText}>Reviens plus tard</Text>
        </View>
      )}

      {status === "success" && predictions.length > 0 && (
        <FlatList
          data={predictions}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => (
            <PredictionCard prediction={item} isPremiumUser={isPremiumUser} />
          )}
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingBottom: 32 }}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "#030712", paddingHorizontal: 16, paddingTop: 16 },
  title: { color: "#fff", fontSize: 24, fontWeight: "900", marginBottom: 12 },
  filters: { flexGrow: 0, marginBottom: 16 },
  chip: {
    backgroundColor: "#111827",
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 999,
    marginRight: 8,
  },
  chipActive: { backgroundColor: "#16a34a" },
  chipText: { color: "#9ca3af", fontSize: 13, fontWeight: "600" },
  chipTextActive: { color: "#fff" },
  center: { flex: 1, alignItems: "center", justifyContent: "center", gap: 8 },
  errorText: { color: "#f87171", fontWeight: "600", fontSize: 16 },
  emptyText: { color: "#6b7280", fontWeight: "600", fontSize: 16 },
  subText: { color: "#374151", fontSize: 13 },
});
