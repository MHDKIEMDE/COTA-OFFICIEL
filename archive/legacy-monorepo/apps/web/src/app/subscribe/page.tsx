"use client";

import { useSearchParams } from "next/navigation";
import { Suspense, useState } from "react";
import { api, getToken, isAuthenticated } from "@/lib/api/client";

const PLANS = [
  {
    key: "mensuel",
    label: "Mensuel",
    amount: 2500,
    currency: "XOF",
    period: "/ mois",
    highlight: false,
    perks: ["Tous les pronostics du jour", "Coupon IA combiné", "Analyses détaillées"],
  },
  {
    key: "trimestriel",
    label: "Trimestriel",
    amount: 6500,
    currency: "XOF",
    period: "/ 3 mois",
    highlight: true,
    badge: "Populaire",
    saving: "Économisez 13%",
    perks: ["Tous les pronostics du jour", "Coupon IA combiné", "Analyses détaillées", "Alertes matchs"],
  },
  {
    key: "annuel",
    label: "Annuel",
    amount: 20000,
    currency: "XOF",
    period: "/ an",
    highlight: false,
    saving: "Économisez 33%",
    perks: ["Tous les pronostics du jour", "Coupon IA combiné", "Analyses détaillées", "Alertes matchs", "Support prioritaire"],
  },
];

function SubscribeContent() {
  const searchParams = useSearchParams();
  const status = searchParams.get("status");
  const [loading, setLoading] = useState<string | null>(null);

  async function handleSubscribe(planKey: string) {
    if (!isAuthenticated()) {
      window.location.href = "/login?redirect=/subscribe";
      return;
    }
    setLoading(planKey);
    try {
      const res = await api.post<{ checkout_url: string }>("/payments/initiate", { plan: planKey });
      window.location.href = res.checkout_url;
    } catch {
      alert("Erreur lors de l'initiation du paiement. Réessayez.");
      setLoading(null);
    }
  }

  return (
    <main className="min-h-screen bg-[#000000] text-white">
      <div className="max-w-5xl mx-auto px-4 py-12">
        <div className="text-center mb-12">
          <p className="text-[#F9FF00] text-xs font-bold uppercase tracking-widest mb-2">⭐ Premium</p>
          <h1 className="text-4xl font-black mb-3">Passez Premium</h1>
          <p className="text-[#888888] text-lg">
            Accédez à tous nos pronostics et analyses IA sans limite
          </p>
        </div>

        {status === "success" && (
          <div className="mb-8 bg-[#00FF8C]/10 border border-[#00FF8C]/40 rounded-xl p-4 text-center text-[#00FF8C] font-semibold">
            Paiement réussi ! Votre abonnement Premium est activé.
          </div>
        )}
        {status === "cancel" && (
          <div className="mb-8 bg-[#FF3B30]/10 border border-[#FF3B30]/30 rounded-xl p-4 text-center text-[#FF3B30] font-semibold">
            Paiement annulé. Vous pouvez réessayer à tout moment.
          </div>
        )}

        <div className="subscribe-grid grid md:grid-cols-3 gap-6">
          {PLANS.map((plan) => (
            <div
              key={plan.key}
              className={`relative rounded-2xl p-6 flex flex-col gap-5 border ${
                plan.highlight
                  ? "bg-[#F9FF00]/5 border-[#F9FF00]/50 ring-2 ring-[#F9FF00]/20"
                  : "bg-[#111111] border-[#1E1E1E]"
              }`}
            >
              {plan.badge && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#F9FF00] text-black text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide">
                  {plan.badge}
                </span>
              )}

              <div>
                <p className="text-sm font-semibold text-[#888888] uppercase tracking-wider mb-1">
                  {plan.label}
                </p>
                <div className="flex items-end gap-1">
                  <span className="text-4xl font-black">
                    {plan.amount.toLocaleString("fr-FR")}
                  </span>
                  <span className="text-lg text-[#888888] mb-1">{plan.currency}</span>
                </div>
                <span className="text-[#888888] text-sm">{plan.period}</span>
                {plan.saving && (
                  <p className="mt-1 text-[#F9FF00] text-sm font-semibold">{plan.saving}</p>
                )}
              </div>

              <ul className="flex flex-col gap-2 flex-1">
                {plan.perks.map((p) => (
                  <li key={p} className="flex items-center gap-2 text-sm text-[#CCCCCC]">
                    <span className="text-[#F9FF00] font-bold">✓</span> {p}
                  </li>
                ))}
              </ul>

              <button
                onClick={() => handleSubscribe(plan.key)}
                disabled={loading === plan.key}
                className={`w-full py-3 rounded-xl font-bold text-sm transition-all ${
                  plan.highlight
                    ? "bg-[#F9FF00] hover:bg-[#e8ee00] text-black"
                    : "bg-[#1A1A1A] hover:bg-[#222222] border border-[#2A2A2A] text-white"
                } disabled:opacity-60 disabled:cursor-not-allowed`}
              >
                {loading === plan.key ? "Redirection..." : "S'abonner maintenant"}
              </button>
            </div>
          ))}
        </div>

        <div className="mt-10 flex flex-wrap justify-center gap-6 text-sm text-[#888888]">
          <div className="flex items-center gap-2"><span>💳</span> Wave, Orange Money, MTN, Moov</div>
          <div className="flex items-center gap-2"><span>🔒</span> Paiement sécurisé via Paydunya</div>
          <div className="flex items-center gap-2"><span>✓</span> Résiliation à tout moment</div>
        </div>
      </div>
    </main>
  );
}

export default function SubscribePage() {
  return (
    <Suspense>
      <SubscribeContent />
    </Suspense>
  );
}
