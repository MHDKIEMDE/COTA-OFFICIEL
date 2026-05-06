import { createClient } from "@/lib/supabase/server";
import Link from "next/link";
import { redirect } from "next/navigation";

export default async function DashboardPage() {
  const supabase = await createClient();
  const { data: { user } } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("*")
    .eq("id", user.id)
    .single();

  const isPremium = profile?.role === "premium" || profile?.role === "admin";

  const MENU = [
    { href: "/predictions", label: "Pronostics du jour", emoji: "⚽", desc: "Tous les picks d'aujourd'hui" },
    { href: "/coupon", label: "Coupon combiné", emoji: "🎯", desc: "Les meilleurs picks du jour" },
    { href: "/history", label: "Historique", emoji: "📊", desc: "Résultats passés" },
  ];

  return (
    <main className="min-h-screen bg-gray-950 text-white">
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-10">
          <div className="flex items-center gap-3 mb-1">
            <h1 className="text-3xl font-black text-green-400">COTA</h1>
            {isPremium && (
              <span className="bg-yellow-500/20 text-yellow-400 text-xs font-bold px-2 py-0.5 rounded-full border border-yellow-500/30">
                PREMIUM
              </span>
            )}
          </div>
          <p className="text-gray-400">
            Bonjour {profile?.full_name ?? user.email?.split("@")[0]} 👋
          </p>
        </div>

        {/* Menu */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
          {MENU.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="bg-gray-900 hover:bg-gray-800 border border-gray-800 hover:border-gray-700 rounded-2xl p-5 flex flex-col gap-2 transition"
            >
              <span className="text-3xl">{item.emoji}</span>
              <span className="font-bold text-white">{item.label}</span>
              <span className="text-xs text-gray-500">{item.desc}</span>
            </Link>
          ))}
        </div>

        {/* CTA Premium */}
        {!isPremium && (
          <div className="bg-gradient-to-r from-yellow-900/30 to-orange-900/20 border border-yellow-500/30 rounded-2xl p-6 flex items-center justify-between gap-4">
            <div>
              <p className="font-bold text-white">Passe à Premium</p>
              <p className="text-sm text-gray-400 mt-0.5">
                Accès à tous les picks + coupon complet
              </p>
            </div>
            <Link
              href="/premium"
              className="bg-yellow-500 hover:bg-yellow-400 text-black font-bold px-5 py-2.5 rounded-xl transition whitespace-nowrap"
            >
              Voir les offres
            </Link>
          </div>
        )}
      </div>
    </main>
  );
}
