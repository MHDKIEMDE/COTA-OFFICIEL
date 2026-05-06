import { StyleSheet, Text, TouchableOpacity, View } from "react-native";

export default function PremiumLockCard() {
  return (
    <View style={styles.container}>
      <Text style={styles.icon}>🔒</Text>
      <Text style={styles.label}>Contenu Premium</Text>
      <TouchableOpacity style={styles.btn}>
        <Text style={styles.btnText}>Débloquer</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: "#1c1a0e",
    borderWidth: 1,
    borderColor: "#78350f",
    borderRadius: 12,
    padding: 16,
    alignItems: "center",
    gap: 8,
  },
  icon: { fontSize: 24 },
  label: { color: "#fbbf24", fontWeight: "600", fontSize: 13 },
  btn: {
    backgroundColor: "#f59e0b",
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 8,
  },
  btnText: { color: "#000", fontWeight: "700", fontSize: 13 },
});
