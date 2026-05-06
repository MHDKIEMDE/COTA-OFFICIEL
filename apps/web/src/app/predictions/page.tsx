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
    weekday: "long",
    day: "numeric",
    month: "long",
  });

  return (
    <main className="min-h-screen bg-gray-950 text-white">
      <div className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-black text-white">
            Pronostics du jour
          </h1>
          <p className="text-gray-400 mt-1 capitalize">{today}</p>
        </div>
        <PredictionsList isPremiumUser={isPremiumUser} />
      </div>
    </main>
  );
}
