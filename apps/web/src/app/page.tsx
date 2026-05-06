"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";

// ── Animated counter ──────────────────────────────────────────
function Counter({ target, suffix = "" }: { target: number; suffix?: string }) {
  const [val, setVal] = useState(0);
  const ref = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        observer.disconnect();
        let start = 0;
        const step = target / 60;
        const tick = () => {
          start = Math.min(start + step, target);
          setVal(Math.floor(start));
          if (start < target) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      },
      { threshold: 0.5 }
    );
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, [target]);

  return (
    <span ref={ref} className="tabular-nums">
      {val.toLocaleString("fr-FR")}
      {suffix}
    </span>
  );
}

// ── Live ticker data ──────────────────────────────────────────
const TICKER_ITEMS = [
  { match: "PSG — Marseille", pred: "1", odds: "1.85", status: "live" },
  { match: "Man City — Arsenal", pred: "1X", odds: "2.10", status: "pending" },
  { match: "Bayern — Dortmund", pred: "BTTS", odds: "1.65", status: "won" },
  { match: "Real Madrid — Atlético", pred: "1", odds: "1.90", status: "pending" },
  { match: "Sénégal — Maroc", pred: "+2.5", odds: "2.30", status: "won" },
  { match: "ASEC — Africa Sports", pred: "1", odds: "1.75", status: "pending" },
  { match: "Ajax — PSV", pred: "BTTS", odds: "1.70", status: "won" },
  { match: "Inter — Napoli", pred: "1X", odds: "1.55", status: "pending" },
];

// ── Feature cards ─────────────────────────────────────────────
const FEATURES = [
  {
    icon: "🤖",
    title: "Algorithme IA v3",
    desc: "9 critères analysés : forme récente, H2H, blessures, cotes de marché, domicile/extérieur et 4 autres.",
    accent: "var(--primary)",
    delay: "reveal-2",
  },
  {
    icon: "🎯",
    title: "Coupon combiné",
    desc: "Sélection automatique chaque matin à 7h00. Le meilleur combo cote/probabilité du jour.",
    accent: "var(--gold)",
    delay: "reveal-3",
  },
  {
    icon: "📊",
    title: "74% de réussite",
    desc: "Win rate calculé sur les 3 derniers mois. Toutes les prédictions archivées et vérifiables.",
    accent: "var(--accent)",
    delay: "reveal-4",
  },
  {
    icon: "📲",
    title: "Mobile Money",
    desc: "Abonnement via Wave, Orange Money, MTN ou Moov. Activation instantanée.",
    accent: "var(--gold)",
    delay: "reveal-5",
  },
];

// ── Bookmakers ────────────────────────────────────────────────
const BOOKMAKERS = [
  { name: "1xBet", bonus: "+130 000 XOF", tag: "Top" },
  { name: "MelBet", bonus: "+75 000 XOF", tag: null },
  { name: "Betway", bonus: "+50 000 XOF", tag: null },
  { name: "Bet9ja", bonus: "200%", tag: null },
];

export default function HomePage() {
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const handler = () => setScrolled(window.scrollY > 40);
    window.addEventListener("scroll", handler);
    return () => window.removeEventListener("scroll", handler);
  }, []);

  const statusStyle = (s: string) =>
    s === "won"
      ? { color: "var(--accent)", borderColor: "var(--accent)" }
      : s === "live"
      ? { color: "var(--live)", borderColor: "var(--live)" }
      : { color: "var(--muted)", borderColor: "var(--dim)" };

  return (
    <div className="noise" style={{ minHeight: "100vh", position: "relative", zIndex: 1 }}>

      {/* ── Nav ──────────────────────────────────────────────── */}
      <header
        style={{
          position: "fixed",
          top: 0,
          left: 0,
          right: 0,
          zIndex: 100,
          background: scrolled ? "rgba(8,12,20,0.92)" : "transparent",
          backdropFilter: scrolled ? "blur(16px)" : "none",
          borderBottom: scrolled ? "1px solid var(--border)" : "1px solid transparent",
          transition: "all 0.3s ease",
          padding: "0 24px",
        }}
      >
        <div style={{ maxWidth: 1140, margin: "0 auto", display: "flex", alignItems: "center", justifyContent: "space-between", height: 64 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <div className="pulse" style={{ width: 8, height: 8, borderRadius: 4, background: "var(--live)" }} />
            <span style={{ fontFamily: "'Bebas Neue', 'Impact', sans-serif", fontSize: 24, letterSpacing: 3, color: "var(--primary)" }}>
              COTA
            </span>
          </div>
          <nav style={{ display: "flex", alignItems: "center", gap: 8 }}>
            {[
              { href: "/predictions", label: "Pronostics" },
              { href: "/coupon", label: "Coupon" },
              { href: "/bookmakers", label: "Bookmakers" },
            ].map((l) => (
              <Link
                key={l.href}
                href={l.href}
                style={{
                  color: "var(--muted)",
                  textDecoration: "none",
                  fontSize: 13,
                  fontWeight: 500,
                  padding: "6px 14px",
                  borderRadius: 8,
                  transition: "color 0.2s",
                }}
                onMouseEnter={(e) => ((e.target as HTMLElement).style.color = "var(--text)")}
                onMouseLeave={(e) => ((e.target as HTMLElement).style.color = "var(--muted)")}
              >
                {l.label}
              </Link>
            ))}
            <Link
              href="/subscribe"
              className="btn-glow"
              style={{
                background: "var(--primary)",
                color: "#fff",
                textDecoration: "none",
                fontSize: 13,
                fontWeight: 700,
                padding: "8px 18px",
                borderRadius: 10,
                marginLeft: 8,
              }}
            >
              Premium
            </Link>
          </nav>
        </div>
      </header>

      {/* ── Live Ticker ──────────────────────────────────────── */}
      <div
        style={{
          position: "fixed",
          top: 64,
          left: 0,
          right: 0,
          zIndex: 99,
          background: "var(--bg2)",
          borderBottom: "1px solid var(--border)",
          overflow: "hidden",
          height: 34,
        }}
      >
        <div
          className="ticker-track"
          style={{ display: "flex", alignItems: "center", height: "100%", whiteSpace: "nowrap", willChange: "transform" }}
        >
          {[...TICKER_ITEMS, ...TICKER_ITEMS].map((item, i) => (
            <span
              key={i}
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                padding: "0 28px",
                fontSize: 11,
                fontFamily: "'DM Mono', monospace",
                borderRight: "1px solid var(--border)",
              }}
            >
              <span
                className={item.status === "live" ? "pulse" : ""}
                style={{
                  display: "inline-block",
                  width: 6,
                  height: 6,
                  borderRadius: 3,
                  background: item.status === "won" ? "var(--accent)" : item.status === "live" ? "var(--live)" : "var(--dim)",
                  flexShrink: 0,
                }}
              />
              <span style={{ color: "var(--text2)" }}>{item.match}</span>
              <span
                style={{
                  ...statusStyle(item.status),
                  border: "1px solid",
                  padding: "1px 6px",
                  borderRadius: 3,
                  fontSize: 10,
                  fontWeight: 700,
                  letterSpacing: 0.5,
                }}
              >
                {item.pred}
              </span>
              <span style={{ color: "var(--muted)" }}>×{item.odds}</span>
            </span>
          ))}
        </div>
      </div>

      {/* ── Hero ─────────────────────────────────────────────── */}
      <section
        style={{
          minHeight: "100vh",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          justifyContent: "center",
          paddingTop: 120,
          paddingBottom: 80,
          paddingLeft: 24,
          paddingRight: 24,
          textAlign: "center",
          position: "relative",
          overflow: "hidden",
        }}
      >
        {/* Radial glow BG */}
        <div
          style={{
            position: "absolute",
            top: "20%",
            left: "50%",
            transform: "translateX(-50%)",
            width: 700,
            height: 700,
            background: "radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 65%)",
            pointerEvents: "none",
          }}
        />
        {/* Grid lines */}
        <div
          style={{
            position: "absolute",
            inset: 0,
            backgroundImage: "linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px)",
            backgroundSize: "60px 60px",
            opacity: 0.15,
            pointerEvents: "none",
          }}
        />

        <div style={{ position: "relative", maxWidth: 860, zIndex: 1 }}>
          {/* Live badge */}
          <div
            className="reveal count-in"
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: 7,
              background: "rgba(239,68,68,0.1)",
              border: "1px solid rgba(239,68,68,0.3)",
              color: "var(--live)",
              fontSize: 11,
              fontWeight: 700,
              letterSpacing: 2,
              padding: "5px 14px",
              borderRadius: 20,
              marginBottom: 28,
              textTransform: "uppercase",
            }}
          >
            <span className="pulse" style={{ width: 6, height: 6, borderRadius: 3, background: "var(--live)", flexShrink: 0, display: "inline-block" }} />
            Pronostics en temps réel
          </div>

          {/* Title */}
          <h1
            className="reveal reveal-1"
            style={{
              fontFamily: "'Bebas Neue', 'Impact', sans-serif",
              fontSize: "clamp(56px, 10vw, 112px)",
              lineHeight: 0.92,
              letterSpacing: 2,
              marginBottom: 24,
              color: "var(--text)",
            }}
          >
            Pronostics
            <br />
            <span style={{ color: "var(--primary)", WebkitTextStroke: "0px" }}>Football</span>
            <br />
            <span style={{ color: "var(--muted)", fontSize: "0.55em", letterSpacing: 6, fontFamily: "'DM Sans', sans-serif", fontWeight: 300 }}>
              Alimentés par l'IA
            </span>
          </h1>

          {/* Subline */}
          <p
            className="reveal reveal-2"
            style={{
              color: "var(--text2)",
              fontSize: 17,
              lineHeight: 1.7,
              maxWidth: 540,
              margin: "0 auto 40px",
            }}
          >
            Notre algorithme analyse <strong style={{ color: "var(--text)" }}>9 critères par match</strong> et génère chaque matin
            le coupon combiné le plus solide pour l'Afrique de l'Ouest.
          </p>

          {/* CTA */}
          <div
            className="reveal reveal-3"
            style={{ display: "flex", gap: 12, justifyContent: "center", flexWrap: "wrap" }}
          >
            <Link
              href="/predictions"
              className="btn-glow"
              style={{
                background: "var(--primary)",
                color: "#fff",
                textDecoration: "none",
                fontWeight: 700,
                fontSize: 15,
                padding: "14px 32px",
                borderRadius: 12,
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
              }}
            >
              ⚽ Voir les pronostics
            </Link>
            <Link
              href="/subscribe"
              style={{
                background: "transparent",
                color: "var(--text)",
                textDecoration: "none",
                fontWeight: 600,
                fontSize: 15,
                padding: "14px 32px",
                borderRadius: 12,
                border: "1px solid var(--border)",
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                transition: "border-color 0.2s, background 0.2s",
              }}
              onMouseEnter={(e) => {
                (e.currentTarget as HTMLElement).style.borderColor = "var(--primary)";
                (e.currentTarget as HTMLElement).style.background = "var(--primary-glow)";
              }}
              onMouseLeave={(e) => {
                (e.currentTarget as HTMLElement).style.borderColor = "var(--border)";
                (e.currentTarget as HTMLElement).style.background = "transparent";
              }}
            >
              ⭐ Passer Premium
            </Link>
          </div>
        </div>

        {/* Stats band */}
        <div
          className="reveal reveal-4"
          style={{
            display: "flex",
            gap: 0,
            marginTop: 72,
            background: "var(--bg3)",
            border: "1px solid var(--border)",
            borderRadius: 16,
            overflow: "hidden",
            maxWidth: 680,
            width: "100%",
          }}
        >
          {[
            { value: 74, suffix: "%", label: "Win rate" },
            { value: 1240, suffix: "+", label: "Pronostics" },
            { value: 12, suffix: " ligues", label: "Couverts" },
          ].map((s, i) => (
            <div
              key={i}
              style={{
                flex: 1,
                padding: "24px 16px",
                textAlign: "center",
                borderRight: i < 2 ? "1px solid var(--border)" : "none",
              }}
            >
              <div
                style={{
                  fontFamily: "'Bebas Neue', 'Impact', sans-serif",
                  fontSize: 42,
                  letterSpacing: 1,
                  color: i === 0 ? "var(--accent)" : i === 1 ? "var(--primary)" : "var(--gold)",
                  lineHeight: 1,
                }}
              >
                <Counter target={s.value} suffix={s.suffix} />
              </div>
              <div style={{ color: "var(--muted)", fontSize: 12, fontWeight: 500, marginTop: 4, textTransform: "uppercase", letterSpacing: 1 }}>
                {s.label}
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* ── Features ─────────────────────────────────────────── */}
      <section style={{ padding: "80px 24px", maxWidth: 1140, margin: "0 auto" }}>
        <div style={{ textAlign: "center", marginBottom: 56 }}>
          <p style={{ color: "var(--primary)", fontSize: 12, fontWeight: 700, letterSpacing: 3, textTransform: "uppercase", marginBottom: 12 }}>
            Pourquoi COTA
          </p>
          <h2
            style={{
              fontFamily: "'Bebas Neue', 'Impact', sans-serif",
              fontSize: "clamp(36px, 5vw, 56px)",
              letterSpacing: 1,
              color: "var(--text)",
            }}
          >
            La science au service du sport
          </h2>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))", gap: 16 }}>
          {FEATURES.map((f, i) => (
            <div
              key={i}
              className={`card-hover reveal ${f.delay}`}
              style={{
                background: "var(--bg3)",
                border: "1px solid var(--border)",
                borderRadius: 16,
                padding: 28,
                display: "flex",
                flexDirection: "column",
                gap: 14,
              }}
            >
              <div
                style={{
                  width: 48,
                  height: 48,
                  borderRadius: 12,
                  background: `${f.accent}18`,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  fontSize: 22,
                  border: `1px solid ${f.accent}33`,
                }}
              >
                {f.icon}
              </div>
              <h3 style={{ color: "var(--text)", fontSize: 16, fontWeight: 700 }}>{f.title}</h3>
              <p style={{ color: "var(--muted)", fontSize: 13, lineHeight: 1.7 }}>{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* ── Prediction preview ───────────────────────────────── */}
      <section style={{ padding: "60px 24px", maxWidth: 1140, margin: "0 auto" }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 28, flexWrap: "wrap", gap: 12 }}>
          <h2 style={{ fontFamily: "'Bebas Neue', 'Impact', sans-serif", fontSize: 36, letterSpacing: 1 }}>
            Exemple de pronostics
          </h2>
          <Link
            href="/predictions"
            style={{
              color: "var(--primary)",
              textDecoration: "none",
              fontSize: 13,
              fontWeight: 600,
              display: "flex",
              alignItems: "center",
              gap: 4,
            }}
          >
            Voir tous →
          </Link>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(300px, 1fr))", gap: 12 }}>
          {[
            { league: "Premier League", home: "Arsenal", away: "Chelsea", pred: "1X", odds: "1.72", conf: 4, premium: false },
            { league: "Ligue 1", home: "PSG", away: "Lyon", pred: "1", odds: "1.55", conf: 3, premium: false },
            { league: "Liga", home: "Real Madrid", away: "Barcelona", pred: "BTTS", odds: "1.85", conf: 3, premium: true },
            { league: "AFCON Q.", home: "Sénégal", away: "Guinée", pred: "+2.5", odds: "2.10", conf: 2, premium: true },
          ].map((p, i) => (
            <div
              key={i}
              className="card-hover"
              style={{
                background: "var(--bg3)",
                border: "1px solid var(--border)",
                borderRadius: 10,
                padding: 16,
                position: "relative",
                overflow: "hidden",
              }}
            >
              {p.premium && (
                <div
                  style={{
                    position: "absolute",
                    inset: 0,
                    backdropFilter: "blur(6px)",
                    background: "rgba(8,12,20,0.7)",
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    justifyContent: "center",
                    gap: 8,
                    zIndex: 2,
                  }}
                >
                  <span style={{ fontSize: 22 }}>🔒</span>
                  <span style={{ color: "var(--gold)", fontWeight: 700, fontSize: 13 }}>Premium</span>
                  <Link
                    href="/subscribe"
                    style={{
                      background: "var(--primary)",
                      color: "#fff",
                      textDecoration: "none",
                      fontWeight: 700,
                      fontSize: 12,
                      padding: "6px 16px",
                      borderRadius: 8,
                    }}
                  >
                    Débloquer
                  </Link>
                </div>
              )}
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 10 }}>
                <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                  <div style={{ width: 5, height: 5, borderRadius: "50%", background: "var(--primary)" }} />
                  <span style={{ color: "var(--primary)", fontSize: 11, fontWeight: 600 }}>{p.league}</span>
                </div>
              </div>
              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 12 }}>
                <span style={{ color: "var(--text)", fontWeight: 700, fontSize: 14, flex: 1 }}>{p.home}</span>
                <span style={{ color: "var(--dim)", fontSize: 12, padding: "0 10px" }}>VS</span>
                <span style={{ color: "var(--text)", fontWeight: 700, fontSize: 14, flex: 1, textAlign: "right" }}>{p.away}</span>
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <div
                  style={{
                    flex: 1,
                    background: "rgba(59,130,246,0.12)",
                    border: "1px solid rgba(59,130,246,0.2)",
                    borderRadius: 6,
                    padding: "5px 10px",
                    color: "var(--primaryLight, #60A5FA)",
                    fontWeight: 700,
                    fontSize: 13,
                    fontFamily: "'DM Mono', monospace",
                  }}
                >
                  {p.pred}
                </div>
                <span style={{ color: "var(--gold)", fontSize: 11 }}>{"★".repeat(p.conf)}</span>
                <span style={{ color: "var(--accent)", fontWeight: 800, fontSize: 15, fontFamily: "'DM Mono', monospace" }}>
                  ×{p.odds}
                </span>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* ── Plans ────────────────────────────────────────────── */}
      <section style={{ padding: "80px 24px", maxWidth: 900, margin: "0 auto", textAlign: "center" }}>
        <p style={{ color: "var(--gold)", fontSize: 12, fontWeight: 700, letterSpacing: 3, textTransform: "uppercase", marginBottom: 12 }}>
          Abonnements
        </p>
        <h2 style={{ fontFamily: "'Bebas Neue', 'Impact', sans-serif", fontSize: "clamp(36px, 5vw, 52px)", letterSpacing: 1, marginBottom: 48 }}>
          Un seul paiement, tous les pronostics
        </h2>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: 16, textAlign: "left" }}>
          {[
            { key: "mensuel", label: "Mensuel", price: "2 500", per: "/ mois", highlight: false },
            { key: "trimestriel", label: "Trimestriel", price: "6 500", per: "/ 3 mois", highlight: true, badge: "Populaire", save: "-13%" },
            { key: "annuel", label: "Annuel", price: "20 000", per: "/ an", highlight: false, save: "-33%" },
          ].map((plan) => (
            <div
              key={plan.key}
              className="card-hover"
              style={{
                background: plan.highlight ? "rgba(59,130,246,0.06)" : "var(--bg3)",
                border: `1px solid ${plan.highlight ? "var(--primary)" : "var(--border)"}`,
                borderRadius: 16,
                padding: 24,
                position: "relative",
              }}
            >
              {plan.badge && (
                <div
                  style={{
                    position: "absolute",
                    top: -11,
                    left: "50%",
                    transform: "translateX(-50%)",
                    background: "var(--primary)",
                    color: "#fff",
                    fontSize: 10,
                    fontWeight: 800,
                    letterSpacing: 1,
                    padding: "3px 12px",
                    borderRadius: 10,
                    textTransform: "uppercase",
                    whiteSpace: "nowrap",
                  }}
                >
                  {plan.badge}
                </div>
              )}
              <p style={{ color: "var(--muted)", fontSize: 11, fontWeight: 700, textTransform: "uppercase", letterSpacing: 1, marginBottom: 10 }}>
                {plan.label}
              </p>
              <div style={{ display: "flex", alignItems: "flex-end", gap: 4, marginBottom: 4 }}>
                <span style={{ fontFamily: "'Bebas Neue', sans-serif", fontSize: 40, color: "var(--text)", letterSpacing: 1 }}>
                  {plan.price}
                </span>
                <span style={{ color: "var(--muted)", fontSize: 13, marginBottom: 6 }}>XOF</span>
              </div>
              <p style={{ color: "var(--dim)", fontSize: 12, marginBottom: plan.save ? 4 : 16 }}>{plan.per}</p>
              {plan.save && (
                <p style={{ color: "var(--accent)", fontSize: 12, fontWeight: 700, marginBottom: 16 }}>{plan.save}</p>
              )}
              <Link
                href={`/subscribe?plan=${plan.key}`}
                className="btn-glow"
                style={{
                  display: "block",
                  textAlign: "center",
                  background: plan.highlight ? "var(--primary)" : "var(--surface)",
                  color: "#fff",
                  textDecoration: "none",
                  fontWeight: 700,
                  fontSize: 13,
                  padding: "11px 0",
                  borderRadius: 10,
                  border: plan.highlight ? "none" : "1px solid var(--border)",
                }}
              >
                S'abonner
              </Link>
            </div>
          ))}
        </div>
        <div style={{ display: "flex", gap: 24, justifyContent: "center", marginTop: 28, flexWrap: "wrap" }}>
          {["💳 Wave", "🟠 Orange Money", "🟡 MTN", "🔵 Moov"].map((m) => (
            <span key={m} style={{ color: "var(--muted)", fontSize: 13 }}>{m}</span>
          ))}
        </div>
      </section>

      {/* ── Bookmakers ───────────────────────────────────────── */}
      <section style={{ padding: "40px 24px 80px", maxWidth: 1140, margin: "0 auto" }}>
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 20, flexWrap: "wrap", gap: 12 }}>
          <h3 style={{ fontFamily: "'Bebas Neue', sans-serif", fontSize: 30, letterSpacing: 1 }}>
            Bookmakers partenaires
          </h3>
          <Link href="/bookmakers" style={{ color: "var(--primary)", textDecoration: "none", fontSize: 13, fontWeight: 600 }}>
            Voir tous →
          </Link>
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(200px, 1fr))", gap: 12 }}>
          {BOOKMAKERS.map((bm) => (
            <Link
              key={bm.name}
              href="/bookmakers"
              className="card-hover"
              style={{
                background: "var(--bg3)",
                border: "1px solid var(--border)",
                borderRadius: 12,
                padding: "16px 20px",
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                textDecoration: "none",
                position: "relative",
              }}
            >
              {bm.tag && (
                <span
                  style={{
                    position: "absolute",
                    top: -8,
                    right: 12,
                    background: "var(--gold)",
                    color: "#000",
                    fontSize: 9,
                    fontWeight: 900,
                    padding: "2px 8px",
                    borderRadius: 8,
                    letterSpacing: 1,
                    textTransform: "uppercase",
                  }}
                >
                  {bm.tag}
                </span>
              )}
              <span style={{ color: "var(--text)", fontWeight: 800, fontSize: 15 }}>{bm.name}</span>
              <span style={{ color: "var(--accent)", fontWeight: 700, fontSize: 12 }}>{bm.bonus}</span>
            </Link>
          ))}
        </div>
      </section>

      {/* ── Footer ───────────────────────────────────────────── */}
      <footer
        style={{
          borderTop: "1px solid var(--border)",
          padding: "32px 24px",
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          flexWrap: "wrap",
          gap: 16,
          maxWidth: 1140,
          margin: "0 auto",
        }}
      >
        <span style={{ fontFamily: "'Bebas Neue', sans-serif", fontSize: 20, letterSpacing: 3, color: "var(--primary)" }}>COTA</span>
        <p style={{ color: "var(--dim)", fontSize: 11, textAlign: "center" }}>
          © 2025 COTA · Pariez de manière responsable · Jeu interdit aux mineurs
        </p>
        <div style={{ display: "flex", gap: 20 }}>
          {["/predictions", "/subscribe", "/bookmakers"].map((href, i) => (
            <Link
              key={href}
              href={href}
              style={{ color: "var(--dim)", textDecoration: "none", fontSize: 12, transition: "color 0.2s" }}
              onMouseEnter={(e) => ((e.target as HTMLElement).style.color = "var(--text)")}
              onMouseLeave={(e) => ((e.target as HTMLElement).style.color = "var(--dim)")}
            >
              {["Pronostics", "Premium", "Bookmakers"][i]}
            </Link>
          ))}
        </div>
      </footer>
    </div>
  );
}
