import { supabase } from "@/lib/supabase";
import { useState } from "react";
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from "react-native";
import { C } from "@/theme/colors";

type Step = "email" | "otp";

export default function LoginScreen() {
  const [step, setStep] = useState<Step>("email");
  const [email, setEmail] = useState("");
  const [otp, setOtp] = useState("");
  const [loading, setLoading] = useState(false);

  async function sendOtp() {
    if (!email.trim()) return;
    setLoading(true);
    const { error } = await supabase.auth.signInWithOtp({ email: email.trim() });
    if (error) Alert.alert("Erreur", error.message);
    else setStep("otp");
    setLoading(false);
  }

  async function verifyOtp() {
    setLoading(true);
    const { error } = await supabase.auth.verifyOtp({
      email: email.trim(),
      token: otp,
      type: "email",
    });
    if (error) Alert.alert("Erreur", error.message);
    setLoading(false);
  }

  return (
    <KeyboardAvoidingView
      style={s.root}
      behavior={Platform.OS === "ios" ? "padding" : undefined}
    >
      {/* Logo area */}
      <View style={s.logoArea}>
        <Text style={s.logo}>COTA</Text>
        <Text style={s.tagline}>Pronostics Football · Intelligence Artificielle</Text>
      </View>

      {/* Card */}
      <View style={s.card}>
        {step === "email" ? (
          <>
            <Text style={s.cardTitle}>Connexion</Text>
            <Text style={s.cardSub}>
              Reçois un code par email — sans mot de passe
            </Text>

            <View style={s.inputWrap}>
              <Text style={s.inputLabel}>EMAIL</Text>
              <TextInput
                style={s.input}
                placeholder="ton@email.com"
                placeholderTextColor={C.dim}
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
                autoCorrect={false}
              />
            </View>

            <TouchableOpacity
              style={[s.btn, (!email.trim() || loading) && s.btnDisabled]}
              onPress={sendOtp}
              disabled={!email.trim() || loading}
              activeOpacity={0.85}
            >
              {loading ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <Text style={s.btnText}>Recevoir le code →</Text>
              )}
            </TouchableOpacity>
          </>
        ) : (
          <>
            <Text style={s.cardTitle}>Code reçu ?</Text>
            <Text style={s.cardSub}>
              Code envoyé à{" "}
              <Text style={{ color: C.primary }}>{email}</Text>
            </Text>

            <View style={s.inputWrap}>
              <Text style={s.inputLabel}>CODE 6 CHIFFRES</Text>
              <TextInput
                style={[s.input, s.otpInput]}
                placeholder="● ● ● ● ● ●"
                placeholderTextColor={C.dim}
                value={otp}
                onChangeText={setOtp}
                keyboardType="number-pad"
                maxLength={6}
                autoFocus
              />
            </View>

            <TouchableOpacity
              style={[s.btn, (otp.length < 6 || loading) && s.btnDisabled]}
              onPress={verifyOtp}
              disabled={otp.length < 6 || loading}
              activeOpacity={0.85}
            >
              {loading ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <Text style={s.btnText}>Confirmer →</Text>
              )}
            </TouchableOpacity>

            <TouchableOpacity onPress={() => { setStep("email"); setOtp(""); }} style={s.back}>
              <Text style={s.backText}>← Changer d'adresse</Text>
            </TouchableOpacity>
          </>
        )}
      </View>

      <Text style={s.disclaimer}>
        Connexion sécurisée via Supabase Auth
      </Text>
    </KeyboardAvoidingView>
  );
}

const s = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: C.bg,
    alignItems: "center",
    justifyContent: "center",
    padding: 24,
    gap: 32,
  },
  logoArea: { alignItems: "center", gap: 8 },
  logo: {
    color: C.primary,
    fontSize: 40,
    fontWeight: "900",
    letterSpacing: 6,
  },
  tagline: {
    color: C.textMuted,
    fontSize: 11,
    letterSpacing: 0.5,
    textAlign: "center",
  },
  card: {
    width: "100%",
    backgroundColor: C.bg2,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: C.border,
    padding: 20,
    gap: 16,
  },
  cardTitle: {
    color: C.textPrimary,
    fontSize: 20,
    fontWeight: "800",
  },
  cardSub: {
    color: C.textMuted,
    fontSize: 13,
    lineHeight: 19,
  },
  inputWrap: { gap: 6 },
  inputLabel: {
    color: C.textMuted,
    fontSize: 10,
    fontWeight: "800",
    letterSpacing: 1.2,
  },
  input: {
    backgroundColor: C.bg3,
    borderWidth: 1,
    borderColor: C.border,
    borderRadius: 10,
    padding: 14,
    color: C.textPrimary,
    fontSize: 15,
  },
  otpInput: {
    textAlign: "center",
    fontSize: 24,
    fontWeight: "700",
    letterSpacing: 10,
  },
  btn: {
    backgroundColor: C.primary,
    borderRadius: 10,
    paddingVertical: 15,
    alignItems: "center",
  },
  btnDisabled: { opacity: 0.4 },
  btnText: { color: "#fff", fontWeight: "800", fontSize: 15 },
  back: { alignItems: "center", paddingTop: 4 },
  backText: { color: C.textMuted, fontSize: 13 },
  disclaimer: {
    color: C.dim,
    fontSize: 11,
    textAlign: "center",
  },
});
