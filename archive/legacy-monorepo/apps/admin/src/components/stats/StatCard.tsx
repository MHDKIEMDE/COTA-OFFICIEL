export default function StatCard({
  label,
  value,
  sub,
  color = "green",
}: {
  label: string;
  value: string | number;
  sub?: string;
  color?: "green" | "yellow" | "blue" | "red";
}) {
  const colors = {
    green: "text-green-400 bg-green-400/10 border-green-400/20",
    yellow: "text-yellow-400 bg-yellow-400/10 border-yellow-400/20",
    blue: "text-blue-400 bg-blue-400/10 border-blue-400/20",
    red: "text-red-400 bg-red-400/10 border-red-400/20",
  };

  return (
    <div className={`rounded-2xl border p-5 flex flex-col gap-1 ${colors[color]}`}>
      <span className="text-xs font-medium opacity-70 uppercase tracking-wide">{label}</span>
      <span className="text-3xl font-black">{value}</span>
      {sub && <span className="text-xs opacity-60">{sub}</span>}
    </div>
  );
}
