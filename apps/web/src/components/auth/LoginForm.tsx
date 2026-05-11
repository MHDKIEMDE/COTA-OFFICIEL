"use client";

import { createClient } from "@/lib/supabase/client";
import { useState } from "react";
import { useRouter } from "next/navigation";

type Step = "email" | "otp";

export default function LoginForm() {
  const [step, setStep] = useState<Step>("email");
  const [email, setEmail] = useState("");
  const [otp, setOtp] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();
  const supabase = createClient();

  async function sendOtp() {
    setLoading(true);
    setError(null);
    const { error } = await supabase.auth.signInWithOtp({ email });
    if (error) {
      setError(error.message);
    } else {
      setStep("otp");
    }
    setLoading(false);
  }

  async function verifyOtp() {
    setLoading(true);
    setError(null);
    const { error } = await supabase.auth.verifyOtp({
      email,
      token: otp,
      type: "email",
    });
    if (error) {
      setError(error.message);
    } else {
      router.push("/dashboard");
      router.refresh();
    }
    setLoading(false);
  }

  async function loginWithGoogle() {
    await supabase.auth.signInWithOAuth({
      provider: "google",
      options: { redirectTo: `${window.location.origin}/auth/callback` },
    });
  }

  if (step === "otp") {
    return (
      <div className="flex flex-col gap-4 w-full max-w-sm">
        <p className="text-sm text-gray-500 text-center">
          Code envoyé à <strong>{email}</strong>
        </p>
        <input
          type="text"
          placeholder="Code à 6 chiffres"
          value={otp}
          onChange={(e) => setOtp(e.target.value)}
          maxLength={6}
          className="border rounded-lg px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:ring-2 focus:ring-green-500"
        />
        {error && <p className="text-red-500 text-sm text-center">{error}</p>}
        <button
          onClick={verifyOtp}
          disabled={loading || otp.length < 6}
          className="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg py-3 font-semibold transition"
        >
          {loading ? "Vérification..." : "Connexion"}
        </button>
        <button
          onClick={() => setStep("email")}
          className="text-sm text-gray-400 hover:text-gray-600"
        >
          Changer d'email
        </button>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4 w-full max-w-sm">
      <input
        type="email"
        placeholder="Ton email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        className="border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
      />
      {error && <p className="text-red-500 text-sm text-center">{error}</p>}
      <button
        onClick={sendOtp}
        disabled={loading || !email}
        className="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg py-3 font-semibold transition"
      >
        {loading ? "Envoi..." : "Recevoir le code"}
      </button>
      <div className="flex items-center gap-3">
        <div className="flex-1 h-px bg-gray-200" />
        <span className="text-gray-400 text-sm">ou</span>
        <div className="flex-1 h-px bg-gray-200" />
      </div>
      <button
        onClick={loginWithGoogle}
        className="border rounded-lg py-3 font-semibold flex items-center justify-center gap-2 hover:bg-gray-50 transition"
      >
        <svg width="18" height="18" viewBox="0 0 48 48">
          <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
          <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
          <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
          <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
        </svg>
        Continuer avec Google
      </button>
    </div>
  );
}
