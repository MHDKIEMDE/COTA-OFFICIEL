import { supabase } from "@/lib/supabase";
import { useState } from "react";
import {
  ActivityIndicator,
  Alert,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from "react-native";

type Step = "email" | "otp";

export default function LoginScreen() {
  const [step, setStep] = useState<Step>("email");
  const [email, setEmail] = useState("");
  const [otp, setOtp] = useState("");
  const [loading, setLoading] = useState(false);

  async function sendOtp() {
    setLoading(true);
    const { error } = await supabase.auth.signInWithOtp({ email });
    if (error) Alert.alert("Erreur", error.message);
    else setStep("otp");
    setLoading(false);
  }

  async function verifyOtp() {
    setLoading(true);
    const { error } = await supabase.auth.verifyOtp({
      email,
      token: otp,
      type: "email",
    });
    if (error) Alert.alert("Erreur", error.message);
    setLoading(false);
  }

  if (step === "otp") {
    return (
      <View style={styles.container}>
        <Text style={styles.title}>COTA</Text>
        <Text style={styles.subtitle}>Code envoyé à {email}</Text>
        <TextInput
          style={[styles.input, styles.otpInput]}
          placeholder="000000"
          placeholderTextColor="#555"
          value={otp}
          onChangeText={setOtp}
          keyboardType="number-pad"
          maxLength={6}
        />
        <TouchableOpacity
          style={[styles.btn, styles.btnPrimary, loading && styles.btnDisabled]}
          onPress={verifyOtp}
          disabled={loading || otp.length < 6}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.btnText}>Connexion</Text>
          )}
        </TouchableOpacity>
        <TouchableOpacity onPress={() => setStep("email")}>
          <Text style={styles.link}>Changer d'email</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>COTA</Text>
      <Text style={styles.subtitle}>Pronostics football IA</Text>
      <TextInput
        style={styles.input}
        placeholder="Ton email"
        placeholderTextColor="#555"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
      />
      <TouchableOpacity
        style={[styles.btn, styles.btnPrimary, loading && styles.btnDisabled]}
        onPress={sendOtp}
        disabled={loading || !email}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.btnText}>Recevoir le code</Text>
        )}
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#030712",
    alignItems: "center",
    justifyContent: "center",
    padding: 24,
    gap: 16,
  },
  title: {
    fontSize: 48,
    fontWeight: "900",
    color: "#4ade80",
    letterSpacing: -1,
  },
  subtitle: {
    fontSize: 14,
    color: "#6b7280",
    marginBottom: 8,
  },
  input: {
    width: "100%",
    backgroundColor: "#111827",
    borderWidth: 1,
    borderColor: "#1f2937",
    borderRadius: 12,
    padding: 16,
    color: "#fff",
    fontSize: 16,
  },
  otpInput: {
    textAlign: "center",
    fontSize: 28,
    letterSpacing: 12,
  },
  btn: {
    width: "100%",
    borderRadius: 12,
    padding: 16,
    alignItems: "center",
  },
  btnPrimary: {
    backgroundColor: "#16a34a",
  },
  btnDisabled: {
    opacity: 0.5,
  },
  btnText: {
    color: "#fff",
    fontWeight: "700",
    fontSize: 16,
  },
  link: {
    color: "#6b7280",
    fontSize: 14,
  },
});
