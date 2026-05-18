"use client";

import { useEffect, useState } from "react";
import { api, isAuthenticated, removeToken } from "@/lib/api/client";
import { useRouter } from "next/navigation";
import Link from "next/link";

type User = {
  id: number;
  name: string;
  email: string;
  role: string;
};

const MENU = [
  { href: "/predictions", label: "Pronostics du jour", emoji: "⚽", desc: "Tous les picks d'aujourd'hui" },
  { href: "/coupon",      label: "Coupon combiné",     emoji: "🎯", desc: "Les meilleurs picks du jour" },
  { href: "/bookmakers",  label: "Bookmakers",         emoji: "🏆", desc: "Nos partenaires recommandés" },
];

export default function DashboardPage() {
  const [user, setUser]       = useState<User | null>(null);
  const [status, setStatus]   = useState<"loading" | "ready">("loading");
  const router = useRouter();

  useEffect(() => {
    if (!isAuthenticated()) { router.replace("/login"); return; }

    api.get<{ data: User }>("/auth/me")
      .then((res) => { setUser(res.data); setStatus("ready"); })
      .catch(() => { removeToken(); router.replace("/login"); });
  }, [router]);

  const isPremium = user?.role === "premium" || user?.role === "admin";

  if (status === "loading") {
    return (
      <main className="min-h-screen bg-[#000000] flex items-center justify-center">
        <div className="w-8 h-8 rounded-full border-2 border-[#F9FF00] border-t-transparent animate-spin" />
      </main>
    );
  }

  return (
    <main className="min-h-screen bg-[#000000] text-white">
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-10">
          <div className="flex items-center gap-3 mb-1">
            <h1 className="text-3xl font-black text-white">COTA</h1>
            {isPremium && (
              <span className="bg-[#F9FF00]/20 text-[#F9FF00] text-xs font-bold px-2 py-0.5 rounded-full border border-[#F9FF00]/30">
                PREMIUM
              </span>
            )}
          </div>
          <p className="text-[#888888]">
            Bonjour {user?.name ?? user?.email?.split("@")[0]} 👋
          </p>
        </div>

        {/* Menu */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
          {MENU.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="bg-[#111111] hover:bg-[#1A1A1A] border border-[#1E1E1E] hover:border-[#F9FF00]/30 rounded-2xl p-5 flex flex-col gap-2 transition"
            >
              <span className="text-3xl">{item.emoji}</span>
              <span className="font-bold text-white">{item.label}</span>
              <span className="text-xs text-[#888888]">{item.desc}</span>
            </Link>
          ))}
        </div>

        {/* CTA Premium */}
        {!isPremium && (
          <div className="bg-[#F9FF00]/5 border border-[#F9FF00]/20 rounded-2xl p-6 flex items-center justify-between gap-4">
            <div>
              <p className="font-bold text-white">Passe à Premium</p>
              <p className="text-sm text-[#888888] mt-0.5">
                Accès à tous les picks + coupon complet
              </p>
            </div>
            <Link
              href="/subscribe"
              className="bg-[#F9FF00] hover:bg-[#e8ee00] text-black font-bold px-5 py-2.5 rounded-xl transition whitespace-nowrap"
            >
              Voir les offres
            </Link>
          </div>
        )}

        {/* Déconnexion */}
        <button
          onClick={() => { removeToken(); router.replace("/login"); }}
          className="mt-8 text-sm text-[#888888] hover:text-white transition"
        >
          Se déconnecter
        </button>
      </div>
    </main>
  );
}
