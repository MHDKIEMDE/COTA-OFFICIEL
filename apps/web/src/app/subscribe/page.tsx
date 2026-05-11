"use client";

import { useSearchParams } from "next/navigation";
import { Suspense, useState } from "react";
import { createClient } from "@/lib/supabase/client";

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
    setLoading(planKey);
    const supabase = createClient();
    const { data: { user } } = await supabase.auth.getUser();

    if (!user) {
      window.location.href = "/login?redirect=/subscribe";
      return;
    }

    const { data: profile } = await supabase
      .from("profiles")
      .select("full_name")
      .eq("id", user.id)
      .single();

    const resp = await fetch(
      `${process.env.NEXT_PUBLIC_API_URL}/payments/initiate`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          plan: planKey,
          user_id: user.id,
          user_email: user.email,
          user_name: profile?.full_name ?? user.email,
        }),
      }
    );

    if (!resp.ok) {
      setLoading(null);
      alert("Erreur lors de l'initiation du paiement. Réessayez.");
      return;
    }

    const data = await resp.json();
    window.location.href = data.checkout_url;
  }

  return (
    <main className="min-h-screen bg-gray-950 text-white">
      <div className="max-w-5xl mx-auto px-4 py-12">
        <div className="text-center mb-12">
          <h1 className="text-4xl font-black mb-3">Passez Premium</h1>
          <p className="text-gray-400 text-lg">
            Accédez à tous nos pronostics et analyses IA sans limite
          </p>
        </div>

        {status === "success" && (
          <div className="mb-8 bg-green-900/40 border border-green-700 rounded-xl p-4 text-center text-green-400 font-semibold">
            Paiement réussi ! Votre abonnement Premium est activé.
          </div>
        )}
        {status === "cancel" && (
          <div className="mb-8 bg-red-900/40 border border-red-700 rounded-xl p-4 text-center text-red-400 font-semibold">
            Paiement annulé. Vous pouvez réessayer à tout moment.
          </div>
        )}

        <div className="grid md:grid-cols-3 gap-6">
          {PLANS.map((plan) => (
            <div
              key={plan.key}
              className={`relative rounded-2xl p-6 flex flex-col gap-5 border ${
                plan.highlight
                  ? "bg-amber-950/40 border-amber-500 ring-2 ring-amber-500/40"
                  : "bg-gray-900 border-gray-800"
              }`}
            >
              {plan.badge && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-black text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide">
                  {plan.badge}
                </span>
              )}

              <div>
                <p className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-1">
                  {plan.label}
                </p>
                <div className="flex items-end gap-1">
                  <span className="text-4xl font-black">
                    {plan.amount.toLocaleString("fr-FR")}
                  </span>
                  <span className="text-lg text-gray-400 mb-1">{plan.currency}</span>
                </div>
                <span className="text-gray-500 text-sm">{plan.period}</span>
                {plan.saving && (
                  <p className="mt-1 text-amber-400 text-sm font-semibold">{plan.saving}</p>
                )}
              </div>

              <ul className="flex flex-col gap-2 flex-1">
                {plan.perks.map((p) => (
                  <li key={p} className="flex items-center gap-2 text-sm text-gray-300">
                    <span className="text-amber-400 font-bold">✓</span> {p}
                  </li>
                ))}
              </ul>

              <button
                onClick={() => handleSubscribe(plan.key)}
                disabled={loading === plan.key}
                className={`w-full py-3 rounded-xl font-bold text-sm transition-all ${
                  plan.highlight
                    ? "bg-amber-500 hover:bg-amber-400 text-black"
                    : "bg-gray-700 hover:bg-gray-600 text-white"
                } disabled:opacity-60 disabled:cursor-not-allowed`}
              >
                {loading === plan.key ? "Redirection..." : "S'abonner maintenant"}
              </button>
            </div>
          ))}
        </div>

        <div className="mt-10 flex flex-wrap justify-center gap-6 text-sm text-gray-500">
          <div className="flex items-center gap-2">
            <span>💳</span> Wave, Orange Money, MTN, Moov
          </div>
          <div className="flex items-center gap-2">
            <span>🔒</span> Paiement sécurisé via Paydunya
          </div>
          <div className="flex items-center gap-2">
            <span>✓</span> Résiliation à tout moment
          </div>
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
