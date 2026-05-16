"use client";

import { api, setToken } from "@/lib/api/client";
import { useState } from "react";
import { useRouter } from "next/navigation";

type Step = "email" | "otp";

export default function LoginForm() {
  const [step, setStep]       = useState<Step>("email");
  const [email, setEmail]     = useState("");
  const [otp, setOtp]         = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState<string | null>(null);
  const router = useRouter();

  async function sendOtp() {
    setLoading(true); setError(null);
    try {
      await api.post("/auth/send-otp", { contact: email, type: "email" });
      setStep("otp");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Erreur lors de l'envoi");
    }
    setLoading(false);
  }

  async function verifyOtp() {
    setLoading(true); setError(null);
    try {
      const res = await api.post<{ token: string }>("/auth/verify-otp", { contact: email, otp });
      setToken(res.token);
      router.push("/dashboard");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Code invalide");
    }
    setLoading(false);
  }

  if (step === "otp") {
    return (
      <div className="flex flex-col gap-4 w-full">
        <p className="text-sm text-[#888888] text-center">
          Code envoyé à <strong className="text-white">{email}</strong>
        </p>
        <input
          type="text"
          placeholder="Code à 6 chiffres"
          value={otp}
          onChange={(e) => setOtp(e.target.value)}
          maxLength={6}
          className="bg-[#111111] border border-[#1E1E1E] rounded-xl px-4 py-3 text-center text-2xl tracking-widest text-white focus:outline-none focus:border-[#F9FF00] transition"
        />
        {error && <p className="text-[#FF3B30] text-sm text-center">{error}</p>}
        <button
          onClick={verifyOtp}
          disabled={loading || otp.length < 6}
          className="bg-[#F9FF00] hover:bg-[#e8ee00] disabled:opacity-40 text-black rounded-xl py-3 font-bold transition"
        >
          {loading ? "Vérification..." : "Se connecter"}
        </button>
        <button onClick={() => setStep("email")} className="text-sm text-[#888888] hover:text-white transition">
          Changer d'email
        </button>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4 w-full">
      <input
        type="email"
        placeholder="Ton email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        className="bg-[#111111] border border-[#1E1E1E] rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[#F9FF00] transition placeholder:text-[#444444]"
      />
      {error && <p className="text-[#FF3B30] text-sm text-center">{error}</p>}
      <button
        onClick={sendOtp}
        disabled={loading || !email}
        className="bg-[#F9FF00] hover:bg-[#e8ee00] disabled:opacity-40 text-black rounded-xl py-3 font-bold transition"
      >
        {loading ? "Envoi..." : "Recevoir le code"}
      </button>
    </div>
  );
}
