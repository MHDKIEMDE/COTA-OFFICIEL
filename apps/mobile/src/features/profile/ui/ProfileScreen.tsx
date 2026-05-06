import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from "react-native";
import { Session } from "@supabase/supabase-js";
import { supabase } from "@/lib/supabase";
import { C } from "@/theme/colors";

const PLANS = [
  { key: "mensuel", label: "Mensuel", price: "2 500 XOF", period: "/ mois" },
  { key: "trimestriel", label: "Trimestriel", price: "6 500 XOF", period: "/ 3 mois", badge: "Populaire" },
  { key: "annuel", label: "Annuel", price: "20 000 XOF", period: "/ an" },
];

const WEB_URL = process.env.EXPO_PUBLIC_WEB_URL ?? "https://cota.ci";

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <View style={s.section}>
      <Text style={s.sectionTitle}>{title}</Text>
      <View style={s.sectionCard}>{children}</View>
    </View>
  );
}

function Row({
  icon, label, value, onPress, danger,
}: { icon: string; label: string; value?: string; onPress?: () => void; danger?: boolean }) {
  return (
    <TouchableOpacity
      style={s.row}
      onPress={onPress}
      activeOpacity={onPress ? 0.7 : 1}
    >
      <Text style={s.rowIcon}>{icon}</Text>
      <Text style={[s.rowLabel, danger && { color: C.live }]}>{label}</Text>
      {value && <Text style={s.rowValue}>{value}</Text>}
      {onPress && <Text style={s.rowArrow}>›</Text>}
    </TouchableOpacity>
  );
}

export default function ProfileScreen({ session, onLogin }: { session: Session | null; onLogin: () => void }) {
  const email = session?.user?.email ?? null;
  const isPremium =
    (session?.user as any)?.user_metadata?.role === "premium" ||
    (session?.user as any)?.user_metadata?.role === "admin";

  async function signOut() {
    Alert.alert("Déconnexion", "Confirmer ?", [
      { text: "Annuler", style: "cancel" },
      { text: "Déconnecter", style: "destructive", onPress: () => supabase.auth.signOut() },
    ]);
  }

  return (
    <ScrollView style={s.root} contentContainerStyle={s.content} showsVerticalScrollIndicator={false}>

      {/* Hero */}
      <View style={s.hero}>
        <View style={s.avatar}>
          <Text style={s.avatarText}>{email ? email[0].toUpperCase() : "?"}</Text>
        </View>
        <View style={s.heroInfo}>
          <Text style={s.heroEmail} numberOfLines={1}>{email ?? "Non connecté"}</Text>
          {isPremium ? (
            <View style={s.premiumBadge}>
              <Text style={s.premiumBadgeText}>⭐ PREMIUM ACTIF</Text>
            </View>
          ) : (
            <Text style={s.heroTier}>Compte gratuit</Text>
          )}
        </View>
      </View>

      {/* Stats rapides */}
      <View style={s.statsRow}>
        {[
          { label: "Pronostics", value: "—" },
          { label: "Gagnés", value: "—" },
          { label: "Taux", value: "—%" },
        ].map((st) => (
          <View key={st.label} style={s.statBox}>
            <Text style={s.statValue}>{st.value}</Text>
            <Text style={s.statLabel}>{st.label}</Text>
          </View>
        ))}
      </View>

      {/* Connexion / Déconnexion */}
      {!session ? (
        <Section title="COMPTE">
          <Row icon="🔑" label="Se connecter" onPress={onLogin} />
          <Row icon="📱" label="Créer un compte" onPress={onLogin} />
        </Section>
      ) : (
        <Section title="COMPTE">
          <Row icon="📧" label="Email" value={email ?? "—"} />
          <Row icon="🔒" label="Se déconnecter" onPress={signOut} danger />
        </Section>
      )}

      {/* Abonnement */}
      <Section title="ABONNEMENT">
        {isPremium ? (
          <Row icon="⭐" label="Premium actif" value="Renouveler" onPress={() => {}} />
        ) : (
          <>
            <View style={s.upgradeBox}>
              <Text style={s.upgradeTitle}>Passez Premium</Text>
              <Text style={s.upgradeSub}>Accédez à tous les pronostics sans limite</Text>
            </View>
            {PLANS.map((plan, i) => (
              <View key={plan.key}>
                <View style={s.planRow}>
                  <View style={s.planLeft}>
                    <Text style={s.planLabel}>{plan.label}</Text>
                    {plan.badge && (
                      <View style={s.planBadge}>
                        <Text style={s.planBadgeText}>{plan.badge}</Text>
                      </View>
                    )}
                  </View>
                  <Text style={s.planPrice}>
                    {plan.price} <Text style={s.planPeriod}>{plan.period}</Text>
                  </Text>
                </View>
                {i < PLANS.length - 1 && <View style={s.divider} />}
              </View>
            ))}
            <TouchableOpacity style={s.upgradeBtn} activeOpacity={0.85}>
              <Text style={s.upgradeBtnText}>S'abonner maintenant →</Text>
            </TouchableOpacity>
          </>
        )}
      </Section>

      {/* Paiements */}
      <Section title="PAIEMENT">
        <Row icon="📱" label="Wave · Orange Money · MTN · Moov" />
      </Section>

      {/* Aide */}
      <Section title="AIDE">
        <Row icon="❓" label="FAQ" onPress={() => {}} />
        <View style={s.divider} />
        <Row icon="🐛" label="Signaler un problème" onPress={() => {}} />
        <View style={s.divider} />
        <Row icon="ℹ️" label="À propos" value="v2.0" />
      </Section>

      <Text style={s.footer}>COTA — Pronostics Football IA · cota.ci</Text>
    </ScrollView>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: C.bg },
  content: { padding: 16, paddingBottom: 48 },

  hero: {
    flexDirection: "row",
    alignItems: "center",
    gap: 14,
    backgroundColor: C.bg2,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: C.border,
    padding: 16,
    marginBottom: 12,
  },
  avatar: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: `${C.primary}22`,
    borderWidth: 2,
    borderColor: `${C.primary}66`,
    alignItems: "center",
    justifyContent: "center",
  },
  avatarText: { color: C.primary, fontSize: 22, fontWeight: "800" },
  heroInfo: { flex: 1, gap: 5 },
  heroEmail: { color: C.textPrimary, fontSize: 14, fontWeight: "700" },
  heroTier: { color: C.textMuted, fontSize: 12 },
  premiumBadge: {
    alignSelf: "flex-start",
    backgroundColor: `${C.gold}22`,
    borderWidth: 1,
    borderColor: `${C.gold}44`,
    borderRadius: 4,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  premiumBadgeText: { color: C.gold, fontSize: 10, fontWeight: "800", letterSpacing: 0.5 },

  statsRow: {
    flexDirection: "row",
    gap: 8,
    marginBottom: 16,
  },
  statBox: {
    flex: 1,
    backgroundColor: C.bg2,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: C.border,
    padding: 12,
    alignItems: "center",
    gap: 4,
  },
  statValue: { color: C.textPrimary, fontSize: 18, fontWeight: "900" },
  statLabel: { color: C.textMuted, fontSize: 10, fontWeight: "600" },

  section: { marginBottom: 16, gap: 6 },
  sectionTitle: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1.5,
    paddingLeft: 4,
  },
  sectionCard: {
    backgroundColor: C.bg2,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: C.border,
    overflow: "hidden",
  },
  row: {
    flexDirection: "row",
    alignItems: "center",
    paddingHorizontal: 16,
    paddingVertical: 14,
    gap: 12,
  },
  rowIcon: { fontSize: 16, width: 22, textAlign: "center" },
  rowLabel: { flex: 1, color: C.textSecondary, fontSize: 14 },
  rowValue: { color: C.textMuted, fontSize: 13 },
  rowArrow: { color: C.dim, fontSize: 18 },
  divider: { height: 1, backgroundColor: C.divider, marginLeft: 50 },

  upgradeBox: {
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: C.divider,
    gap: 4,
  },
  upgradeTitle: { color: C.gold, fontSize: 15, fontWeight: "800" },
  upgradeSub: { color: C.textMuted, fontSize: 12 },
  planRow: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 16,
    paddingVertical: 13,
  },
  planLeft: { flexDirection: "row", alignItems: "center", gap: 8 },
  planLabel: { color: C.textSecondary, fontSize: 14, fontWeight: "600" },
  planBadge: {
    backgroundColor: `${C.gold}22`,
    borderRadius: 4,
    paddingHorizontal: 6,
    paddingVertical: 2,
  },
  planBadgeText: { color: C.gold, fontSize: 9, fontWeight: "800" },
  planPrice: { color: C.textPrimary, fontSize: 14, fontWeight: "700" },
  planPeriod: { color: C.textMuted, fontWeight: "400", fontSize: 12 },
  upgradeBtn: {
    backgroundColor: C.gold,
    margin: 12,
    borderRadius: 10,
    paddingVertical: 13,
    alignItems: "center",
  },
  upgradeBtnText: { color: "#000", fontWeight: "800", fontSize: 14 },

  footer: { color: C.dim, fontSize: 11, textAlign: "center", marginTop: 8 },
});
