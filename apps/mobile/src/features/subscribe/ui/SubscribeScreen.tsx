import { Linking, ScrollView, StyleSheet, Text, TouchableOpacity, View } from "react-native";
import { C } from "@/theme/colors";

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
    <ScrollView
      style={s.root}
      contentContainerStyle={s.content}
      showsVerticalScrollIndicator={false}
    >
      {/* Header */}
      <View style={s.header}>
        <View style={s.headerTag}>
          <Text style={s.headerTagText}>PREMIUM</Text>
        </View>
        <Text style={s.title}>Passez Premium</Text>
        <Text style={s.subtitle}>
          Accédez à tous les pronostics sans limite · Coupon IA chaque matin
        </Text>
      </View>

      {/* Plans */}
      {PLANS.map((plan) => (
        <View
          key={plan.key}
          style={[s.card, plan.highlight && s.cardHighlight]}
        >
          {plan.badge && (
            <View style={s.badge}>
              <Text style={s.badgeText}>{plan.badge}</Text>
            </View>
          )}

          <View style={s.planTop}>
            <Text style={[s.planLabel, plan.highlight && { color: C.gold }]}>
              {plan.label}
            </Text>
            {plan.saving && (
              <Text style={s.saving}>{plan.saving}</Text>
            )}
          </View>

          <View style={s.priceRow}>
            <Text style={[s.amount, plan.highlight && { color: C.gold }]}>
              {plan.amount}
            </Text>
            <Text style={s.period}>{plan.period}</Text>
          </View>

          <View style={s.separator} />

          <View style={s.perks}>
            {plan.perks.map((p) => (
              <View key={p} style={s.perkRow}>
                <Text style={[s.perkCheck, plan.highlight && { color: C.gold }]}>✓</Text>
                <Text style={s.perkText}>{p}</Text>
              </View>
            ))}
          </View>

          <TouchableOpacity
            style={[s.btn, plan.highlight && s.btnHighlight]}
            onPress={() => handleSubscribe(plan.key)}
            activeOpacity={0.85}
          >
            <Text style={[s.btnText, plan.highlight && s.btnTextHighlight]}>
              S'abonner
            </Text>
          </TouchableOpacity>
        </View>
      ))}

      {/* Payment methods */}
      <View style={s.paymentRow}>
        {["Wave", "Orange Money", "MTN", "Moov"].map((m) => (
          <View key={m} style={s.paymentChip}>
            <Text style={s.paymentText}>{m}</Text>
          </View>
        ))}
      </View>

      <Text style={s.footer}>
        Paiement sécurisé · Paydunya · Cote d'Ivoire
      </Text>

      {onClose && (
        <TouchableOpacity onPress={onClose} style={s.closeBtn}>
          <Text style={s.closeBtnText}>Fermer</Text>
        </TouchableOpacity>
      )}
    </ScrollView>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  content: { padding: 16, paddingBottom: 48 },

  header: { marginBottom: 20, gap: 8 },
  headerTag: {
    alignSelf: "flex-start",
    backgroundColor: `${C.gold}18`,
    borderWidth: 1,
    borderColor: `${C.gold}44`,
    borderRadius: 4,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  headerTagText: { color: C.gold, fontSize: 10, fontWeight: "800", letterSpacing: 1.5 },
  title: { color: C.textPrimary, fontSize: 28, fontWeight: "900" },
  subtitle: { color: C.textMuted, fontSize: 13, lineHeight: 20 },

  card: {
    backgroundColor: C.bg2,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: C.border,
    padding: 16,
    marginBottom: 12,
    gap: 10,
  },
  cardHighlight: {
    backgroundColor: "#110D00",
    borderColor: `${C.gold}66`,
    shadowColor: C.gold,
    shadowOpacity: 0.12,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 4 },
    elevation: 4,
  },
  badge: {
    alignSelf: "flex-start",
    backgroundColor: C.gold,
    borderRadius: 4,
    paddingHorizontal: 10,
    paddingVertical: 3,
  },
  badgeText: { color: "#000", fontSize: 10, fontWeight: "900", letterSpacing: 0.5 },
  planTop: { flexDirection: "row", justifyContent: "space-between", alignItems: "center" },
  planLabel: { color: C.textMuted, fontSize: 11, fontWeight: "800", letterSpacing: 0.8 },
  saving: { color: C.accent, fontSize: 11, fontWeight: "700" },
  priceRow: { flexDirection: "row", alignItems: "flex-end", gap: 6 },
  amount: { color: C.textPrimary, fontSize: 30, fontWeight: "900" },
  period: { color: C.textMuted, fontSize: 13, marginBottom: 3 },
  separator: { height: 1, backgroundColor: C.divider },
  perks: { gap: 7 },
  perkRow: { flexDirection: "row", alignItems: "flex-start", gap: 8 },
  perkCheck: { color: C.accent, fontSize: 13, fontWeight: "800", lineHeight: 20 },
  perkText: { color: C.textSecondary, fontSize: 13, flex: 1, lineHeight: 20 },
  btn: {
    backgroundColor: C.bg3,
    borderRadius: 10,
    paddingVertical: 13,
    alignItems: "center",
    borderWidth: 1,
    borderColor: C.border,
    marginTop: 2,
  },
  btnHighlight: {
    backgroundColor: C.gold,
    borderColor: C.gold,
  },
  btnText: { color: C.textSecondary, fontWeight: "700", fontSize: 14 },
  btnTextHighlight: { color: "#000", fontWeight: "800" },

  paymentRow: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 8,
    justifyContent: "center",
    marginTop: 8,
    marginBottom: 4,
  },
  paymentChip: {
    backgroundColor: C.bg3,
    borderWidth: 1,
    borderColor: C.border,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  paymentText: { color: C.textMuted, fontSize: 11, fontWeight: "600" },
  footer: { color: C.dim, fontSize: 11, textAlign: "center", marginTop: 8 },
  closeBtn: { alignItems: "center", marginTop: 20 },
  closeBtnText: { color: C.textMuted, fontSize: 14 },
});
