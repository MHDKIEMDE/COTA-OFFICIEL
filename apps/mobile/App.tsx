import { supabase } from "@/lib/supabase";
import { Session } from "@supabase/supabase-js";
import { StatusBar } from "expo-status-bar";
import { useEffect, useState } from "react";
import { Text, View } from "react-native";
import LoginScreen from "@/features/auth/ui/LoginScreen";

export default function App() {
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session);
      setLoading(false);
    });

    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      (_event, session) => setSession(session)
    );

    return () => subscription.unsubscribe();
  }, []);

  if (loading) {
    return (
      <View style={{ flex: 1, backgroundColor: "#030712", alignItems: "center", justifyContent: "center" }}>
        <Text style={{ color: "#4ade80", fontSize: 36, fontWeight: "900" }}>COTA</Text>
      </View>
    );
  }

  if (!session) return <LoginScreen />;

  return (
    <View style={{ flex: 1, backgroundColor: "#030712", alignItems: "center", justifyContent: "center" }}>
      <StatusBar style="light" />
      <Text style={{ color: "#fff" }}>Dashboard — {session.user.email}</Text>
    </View>
  );
}
