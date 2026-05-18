import { Text, View, StyleSheet } from "react-native";

export default function ConfidenceStars({ level }: { level: 1 | 2 | 3 | 4 }) {
  return (
    <View style={styles.row}>
      {[1, 2, 3, 4].map((i) => (
        <Text key={i} style={[styles.star, i <= level ? styles.active : styles.inactive]}>
          ★
        </Text>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: "row", gap: 2 },
  star: { fontSize: 14 },
  active: { color: "#facc15" },
  inactive: { color: "#374151" },
});
