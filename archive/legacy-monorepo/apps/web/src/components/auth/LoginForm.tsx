"use client";

import { api, setToken } from "@/lib/api/client";
import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import {
  parsePhoneNumberFromString,
  getCountries,
  getCountryCallingCode,
  getExampleNumber,
  type CountryCode,
} from "libphonenumber-js";
import examples from "libphonenumber-js/mobile/examples";

// ── Types ─────────────────────────────────────────────────────────────────────

type Tab  = "phone" | "email" | "otp";
type Step = "input" | "otp";

// ── Constantes (aucune valeur hardcodée ici) ──────────────────────────────────

const SOCIAL_PROVIDERS = [
  {
    id: "google",
    label: "Google",
    url: process.env.NEXT_PUBLIC_GOOGLE_AUTH_URL,
    icon: (
      <svg viewBox="0 0 24 24" width="18" height="18">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
      </svg>
    ),
  },
  {
    id: "facebook",
    label: "Facebook",
    url: process.env.NEXT_PUBLIC_FACEBOOK_AUTH_URL,
    icon: (
      <svg viewBox="0 0 24 24" width="18" height="18">
        <path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
      </svg>
    ),
  },
] as const;

// ── Helpers ───────────────────────────────────────────────────────────────────

function useDetectedCountry(): CountryCode {
  const [country, setCountry] = useState<CountryCode>("CI");
  useEffect(() => {
    fetch("https://ip-api.com/json/?fields=countryCode")
      .then((r) => r.json())
      .then((d) => { if (d.countryCode) setCountry(d.countryCode as CountryCode); })
      .catch(() => {});
  }, []);
  return country;
}

function getPhonePlaceholder(country: CountryCode): string {
  const example = getExampleNumber(country, examples);
  if (!example) return "Numéro de téléphone";
  // Format national sans l'indicatif, ex: "07 07 07 07 07"
  return example.formatNational();
}

function formatPhoneDisplay(raw: string, country: CountryCode): string {
  const parsed = parsePhoneNumberFromString(raw, country);
  return parsed ? parsed.formatInternational() : raw;
}

function isPhoneValid(raw: string, country: CountryCode): boolean {
  const parsed = parsePhoneNumberFromString(raw, country);
  return !!parsed?.isValid();
}

// ── Composants atomiques ──────────────────────────────────────────────────────

function InputField({
  type, value, onChange, placeholder, autoComplete,
}: {
  type: string; value: string; onChange: (v: string) => void;
  placeholder: string; autoComplete?: string;
}) {
  return (
    <input
      type={type}
      placeholder={placeholder}
      value={value}
      autoComplete={autoComplete}
      onChange={(e) => onChange(e.target.value)}
      className="w-full bg-[#0A0A0A] border border-[#1E1E1E] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F9FF00] transition placeholder:text-[#444444]"
    />
  );
}

function SubmitButton({ loading, disabled, label, loadingLabel }: {
  loading: boolean; disabled: boolean; label: string; loadingLabel: string;
}) {
  return (
    <button
      type="submit"
      disabled={disabled || loading}
      className="w-full bg-[#F9FF00] hover:bg-[#e8ee00] disabled:opacity-40 text-black rounded-xl py-3 text-sm font-bold transition"
    >
      {loading ? loadingLabel : label}
    </button>
  );
}

function ErrorMsg({ msg }: { msg: string | null }) {
  if (!msg) return null;
  return <p className="text-[#FF3B30] text-xs text-center">{msg}</p>;
}

function Divider() {
  return (
    <div className="flex items-center gap-3 my-1">
      <div className="flex-1 h-px bg-[#1E1E1E]" />
      <span className="text-[#444444] text-xs font-medium">Ou continuer avec</span>
      <div className="flex-1 h-px bg-[#1E1E1E]" />
    </div>
  );
}

function SocialButtons() {
  return (
    <div className="flex flex-col gap-2">
      {SOCIAL_PROVIDERS.map((p) => (
        <a
          key={p.id}
          href={p.url ?? "#"}
          className="flex items-center justify-center gap-3 w-full bg-[#0A0A0A] border border-[#1E1E1E] rounded-xl py-3 text-sm font-semibold text-white hover:border-[#333333] hover:bg-[#111111] transition"
        >
          {p.icon}
          <span>Continuer avec {p.label}</span>
        </a>
      ))}
    </div>
  );
}

// ── Tab: Téléphone + Mot de passe ─────────────────────────────────────────────

function PhonePasswordForm() {
  const detectedCountry = useDetectedCountry();
  const [country, setCountry]     = useState<CountryCode>("CI");
  const [phone, setPhone]         = useState("");
  const [password, setPassword]   = useState("");
  const [isLogin, setIsLogin]     = useState(true);
  const [loading, setLoading]     = useState(false);
  const [error, setError]         = useState<string | null>(null);
  const router = useRouter();

  useEffect(() => { setCountry(detectedCountry); }, [detectedCountry]);

  const callingCode = `+${getCountryCallingCode(country)}`;
  const allCountries = getCountries();
  const phoneOk = isPhoneValid(phone, country);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!phoneOk || !password) return;
    setLoading(true); setError(null);
    const fullPhone = parsePhoneNumberFromString(phone, country)!.number;
    try {
      const endpoint = isLogin ? "/auth/phone/login" : "/auth/phone/register";
      const res = await api.post<{ token: string }>(endpoint, { phone: fullPhone, password });
      setToken(res.token);
      router.push("/dashboard");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Erreur");
    }
    setLoading(false);
  }

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-3">
      {/* Sélecteur pays + numéro */}
      <div className="flex gap-2">
        <div className="relative">
          <select
            value={country}
            onChange={(e) => setCountry(e.target.value as CountryCode)}
            className="appearance-none bg-[#0A0A0A] border border-[#1E1E1E] rounded-xl px-3 py-3 text-white text-sm focus:outline-none focus:border-[#F9FF00] transition pr-6 cursor-pointer"
            style={{ minWidth: 90 }}
          >
            {allCountries.map((c) => (
              <option key={c} value={c}>
                {c} +{getCountryCallingCode(c)}
              </option>
            ))}
          </select>
          <span className="absolute right-2 top-1/2 -translate-y-1/2 text-[#444444] text-xs pointer-events-none">▾</span>
        </div>
        <input
          type="tel"
          placeholder={getPhonePlaceholder(country)}
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          className="flex-1 bg-[#0A0A0A] border border-[#1E1E1E] rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#F9FF00] transition placeholder:text-[#444444]"
        />
      </div>
      {phone && !phoneOk && (
        <p className="text-[#888888] text-xs">
          Format attendu : {formatPhoneDisplay(phone, country) || `${callingCode}XXXXXXXX`}
        </p>
      )}
      <InputField
        type="password"
        placeholder="Mot de passe"
        value={password}
        onChange={setPassword}
        autoComplete={isLogin ? "current-password" : "new-password"}
      />
      <ErrorMsg msg={error} />
      <SubmitButton
        loading={loading}
        disabled={!phoneOk || !password}
        label={isLogin ? "Se connecter" : "Créer le compte"}
        loadingLabel={isLogin ? "Connexion..." : "Création..."}
      />
      <button
        type="button"
        onClick={() => { setIsLogin(!isLogin); setError(null); }}
        className="text-xs text-[#888888] hover:text-white transition text-center"
      >
        {isLogin ? "Pas encore de compte ? S'inscrire" : "Déjà un compte ? Se connecter"}
      </button>
    </form>
  );
}

// ── Tab: Email + Mot de passe ─────────────────────────────────────────────────

function EmailPasswordForm() {
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword]     = useState("");
  const [isLogin, setIsLogin]       = useState(true);
  const [loading, setLoading]       = useState(false);
  const [error, setError]           = useState<string | null>(null);
  const router = useRouter();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!identifier || !password) return;
    setLoading(true); setError(null);
    try {
      const endpoint = isLogin ? "/auth/email/login" : "/auth/email/register";
      const res = await api.post<{ token: string }>(endpoint, { identifier, password });
      setToken(res.token);
      router.push("/dashboard");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Erreur");
    }
    setLoading(false);
  }

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-3">
      <InputField
        type="text"
        placeholder="Email ou identifiant"
        value={identifier}
        onChange={setIdentifier}
        autoComplete="username email"
      />
      <InputField
        type="password"
        placeholder="Mot de passe"
        value={password}
        onChange={setPassword}
        autoComplete={isLogin ? "current-password" : "new-password"}
      />
      <ErrorMsg msg={error} />
      <SubmitButton
        loading={loading}
        disabled={!identifier || !password}
        label={isLogin ? "Se connecter" : "Créer le compte"}
        loadingLabel={isLogin ? "Connexion..." : "Création..."}
      />
      <button
        type="button"
        onClick={() => { setIsLogin(!isLogin); setError(null); }}
        className="text-xs text-[#888888] hover:text-white transition text-center"
      >
        {isLogin ? "Pas encore de compte ? S'inscrire" : "Déjà un compte ? Se connecter"}
      </button>
    </form>
  );
}

// ── Tab: OTP ──────────────────────────────────────────────────────────────────

function OtpForm() {
  const [step, setStep]       = useState<Step>("input");
  const [contact, setContact] = useState("");
  const [otp, setOtp]         = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState<string | null>(null);
  const [cooldown, setCooldown] = useState(0);
  const router = useRouter();

  useEffect(() => {
    if (cooldown <= 0) return;
    const t = setTimeout(() => setCooldown((c) => c - 1), 1000);
    return () => clearTimeout(t);
  }, [cooldown]);

  async function sendOtp(e: React.FormEvent) {
    e.preventDefault();
    if (!contact) return;
    setLoading(true); setError(null);
    try {
      await api.post("/auth/send-otp", { contact });
      setStep("otp");
      setCooldown(60);
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Erreur d'envoi");
    }
    setLoading(false);
  }

  async function verifyOtp(e: React.FormEvent) {
    e.preventDefault();
    if (otp.length < 6) return;
    setLoading(true); setError(null);
    try {
      const res = await api.post<{ token: string }>("/auth/verify-otp", { contact, otp });
      setToken(res.token);
      router.push("/dashboard");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Code invalide");
    }
    setLoading(false);
  }

  if (step === "otp") {
    return (
      <form onSubmit={verifyOtp} className="flex flex-col gap-3">
        <p className="text-sm text-[#888888] text-center">
          Code envoyé à <strong className="text-white">{contact}</strong>
        </p>
        <input
          type="text"
          inputMode="numeric"
          placeholder="• • • • • •"
          value={otp}
          onChange={(e) => setOtp(e.target.value.replace(/\D/g, "").slice(0, 6))}
          maxLength={6}
          className="bg-[#0A0A0A] border border-[#1E1E1E] rounded-xl px-4 py-3 text-center text-2xl tracking-[0.5em] text-white focus:outline-none focus:border-[#F9FF00] transition"
        />
        <ErrorMsg msg={error} />
        <SubmitButton loading={loading} disabled={otp.length < 6} label="Vérifier" loadingLabel="Vérification..." />
        <div className="flex justify-between text-xs text-[#888888]">
          <button type="button" onClick={() => { setStep("input"); setOtp(""); setError(null); }} className="hover:text-white transition">
            Changer de contact
          </button>
          {cooldown > 0 ? (
            <span>Renvoyer dans {cooldown}s</span>
          ) : (
            <button type="button" onClick={(e) => sendOtp(e as unknown as React.FormEvent)} className="hover:text-white transition">
              Renvoyer le code
            </button>
          )}
        </div>
      </form>
    );
  }

  return (
    <form onSubmit={sendOtp} className="flex flex-col gap-3">
      <InputField
        type="text"
        placeholder="Email ou numéro de téléphone"
        value={contact}
        onChange={setContact}
        autoComplete="email tel"
      />
      <ErrorMsg msg={error} />
      <SubmitButton loading={loading} disabled={!contact} label="Recevoir le code" loadingLabel="Envoi..." />
    </form>
  );
}

// ── Composant principal ───────────────────────────────────────────────────────

const TABS: { id: Tab; label: string }[] = [
  { id: "phone", label: "Téléphone" },
  { id: "email", label: "Email" },
  { id: "otp",   label: "OTP" },
];

export default function LoginForm() {
  const [activeTab, setActiveTab] = useState<Tab>("phone");

  return (
    <div className="flex flex-col gap-5">
      {/* Onglets */}
      <div className="flex bg-[#0A0A0A] rounded-xl p-1 gap-1">
        {TABS.map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => setActiveTab(t.id)}
            className={`flex-1 py-2 rounded-lg text-xs font-semibold transition ${
              activeTab === t.id
                ? "bg-[#F9FF00] text-black"
                : "text-[#888888] hover:text-white"
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* Contenu de l'onglet actif */}
      {activeTab === "phone" && <PhonePasswordForm />}
      {activeTab === "email" && <EmailPasswordForm />}
      {activeTab === "otp"   && <OtpForm />}

      {/* Séparateur + Boutons sociaux */}
      <Divider />
      <SocialButtons />
    </div>
  );
}
