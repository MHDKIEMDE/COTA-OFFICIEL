import { Linking, ScrollView, StyleSheet, Text, TouchableOpacity, View } from "react-native";

const PLANS = [
  {
    key: "mensuel",
    label: "Mensuel",
    amount: "2 500 XOF",
    period: "/ mois",
    highlight: false,
    perks: ["Tous les pronostics du jour", "Coupon IA combiné", "Analyses détaillées"],
  },
  {
    key: "trimestriel",
    label: "Trimestriel",
    amount: "6 500 XOF",
    period: "/ 3 mois",
    highlight: true,
    badge: "Populaire",
    saving: "Économisez 13%",
    perks: ["Tous les pronostics du jour", "Coupon IA combiné", "Alertes matchs"],
  },
  {
    key: "annuel",
    label: "Annuel",
    amount: "20 000 XOF",
    period: "/ an",
    highlight: false,
    saving: "Économisez 33%",
    perks: ["Tous les pronostics", "Coupon IA", "Alertes matchs", "Support prioritaire"],
  },
];

const WEB_URL = process.env.EXPO_PUBLIC_WEB_URL ?? "https://cota.ci";

export default function SubscribeScreen({ onClose }: { onClose?: () => void }) {
  function handleSubscribe(planKey: string) {
    Linking.openURL(`${WEB_URL}/subscribe?plan=${planKey}`);
  }

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      <View style={styles.header}>
        <Text style={styles.title}>Passez Premium</Text>
        <Text style={styles.subtitle}>Accédez à tous les pronostics sans limite</Text>
      </View>

      {PLANS.map((plan) => (
        <View
          key={plan.key}
          style={[styles.card, plan.highlight && styles.cardHighlight]}
        >
          {plan.badge && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>{plan.badge}</Text>
            </View>
          )}

          <Text style={[styles.planLabel, plan.highlight && styles.planLabelHighlight]}>
            {plan.label}
          </Text>
          <View style={styles.priceRow}>
            <Text style={styles.amount}>{plan.amount}</Text>
            <Text style={styles.period}>{plan.period}</Text>
          </View>
          {plan.saving && <Text style={styles.saving}>{plan.saving}</Text>}

          <View style={styles.perks}>
            {plan.perks.map((p) => (
              <Text key={p} style={styles.perk}>✓ {p}</Text>
            ))}
          </View>

          <TouchableOpacity
            style={[styles.btn, plan.highlight && styles.btnHighlight]}
            onPress={() => handleSubscribe(plan.key)}
          >
            <Text style={[styles.btnText, plan.highlight && styles.btnTextHighlight]}>
              S'abonner
            </Text>
          </TouchableOpacity>
        </View>
      ))}

      <Text style={styles.footer}>
        Paiement sécurisé · Wave · Orange Money · MTN · Moov
      </Text>

      {onClose && (
        <TouchableOpacity onPress={onClose} style={styles.close}>
          <Text style={styles.closeText}>Fermer</Text>
        </TouchableOpacity>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "#030712" },
  content: { padding: 20, paddingBottom: 40 },
  header: { marginBottom: 24 },
  title: { color: "#fff", fontSize: 26, fontWeight: "900" },
  subtitle: { color: "#6b7280", fontSize: 14, marginTop: 4 },
  card: {
    backgroundColor: "#111827",
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#1f2937",
    padding: 20,
    marginBottom: 16,
    gap: 10,
  },
  cardHighlight: {
    backgroundColor: "#1c1000",
    borderColor: "#f59e0b",
  },
  badge: {
    alignSelf: "flex-start",
    backgroundColor: "#f59e0b",
    borderRadius: 20,
    paddingHorizontal: 10,
    paddingVertical: 3,
  },
  badgeText: { color: "#000", fontSize: 11, fontWeight: "900" },
  planLabel: { color: "#9ca3af", fontSize: 12, fontWeight: "700", textTransform: "uppercase" },
  planLabelHighlight: { color: "#fbbf24" },
  priceRow: { flexDirection: "row", alignItems: "flex-end", gap: 4 },
  amount: { color: "#fff", fontSize: 28, fontWeight: "900" },
  period: { color: "#6b7280", fontSize: 14, marginBottom: 2 },
  saving: { color: "#fbbf24", fontSize: 13, fontWeight: "600" },
  perks: { gap: 4 },
  perk: { color: "#d1d5db", fontSize: 13 },
  btn: {
    backgroundColor: "#374151",
    borderRadius: 12,
    paddingVertical: 12,
    alignItems: "center",
    marginTop: 4,
  },
  btnHighlight: { backgroundColor: "#f59e0b" },
  btnText: { color: "#fff", fontWeight: "700", fontSize: 14 },
  btnTextHighlight: { color: "#000" },
  footer: { color: "#4b5563", fontSize: 12, textAlign: "center", marginTop: 8 },
  close: { alignItems: "center", marginTop: 16 },
  closeText: { color: "#6b7280", fontSize: 14 },
});
