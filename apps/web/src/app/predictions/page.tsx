import { createClient } from "@/lib/supabase/server";
import PredictionsList from "@/components/predictions/PredictionsList";

export default async function PredictionsPage() {
  const supabase = await createClient();
  const { data: { user } } = await supabase.auth.getUser();

  let isPremiumUser = false;
  if (user) {
    const { data: profile } = await supabase
      .from("profiles")
      .select("role")
      .eq("id", user.id)
      .single();
    isPremiumUser = profile?.role === "premium" || profile?.role === "admin";
  }

  const today = new Date().toLocaleDateString("fr-FR", {
    weekday: "long", day: "numeric", month: "long",
  });

  return (
    <main className="min-h-screen bg-[#000000] text-white">
      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <p className="text-[#F9FF00] text-xs font-bold uppercase tracking-widest mb-2">
            ⚽ Pronostics du jour
          </p>
          <h1 className="text-3xl font-black text-white">Picks IA</h1>
          <p className="text-[#888888] mt-1 capitalize text-sm">{today}</p>
        </div>

        <PredictionsList isPremiumUser={isPremiumUser} />
      </div>
    </main>
  );
}
