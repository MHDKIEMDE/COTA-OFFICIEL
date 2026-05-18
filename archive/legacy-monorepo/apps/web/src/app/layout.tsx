import type { Metadata } from "next";
import { Bebas_Neue, DM_Sans, DM_Mono } from "next/font/google";
import "./globals.css";
import OneSignalInit from "@/components/OneSignalInit";

const bebasNeue = Bebas_Neue({
  weight: "400",
  variable: "--font-bebas",
  subsets: ["latin"],
  display: "swap",
});

const dmSans = DM_Sans({
  variable: "--font-dm-sans",
  subsets: ["latin"],
  display: "swap",
});

const dmMono = DM_Mono({
  weight: ["300", "400", "500"],
  variable: "--font-dm-mono",
  subsets: ["latin"],
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: "COTA — Pronostics Football IA",
    template: "%s | COTA",
  },
  description:
    "Pronostics football alimentés par l'IA — matchs du jour, coupon combiné, analyses détaillées pour l'Afrique de l'Ouest.",
  keywords: ["pronostics football", "paris sportifs", "IA", "Afrique", "COTA"],
  authors: [{ name: "COTA" }],
  creator: "COTA",
  openGraph: {
    type: "website",
    locale: "fr_CI",
    siteName: "COTA",
    title: "COTA — Pronostics Football IA",
    description: "Pronostics football alimentés par l'IA pour l'Afrique de l'Ouest.",
  },
  twitter: {
    card: "summary_large_image",
    title: "COTA — Pronostics Football IA",
    description: "Pronostics football IA pour l'Afrique de l'Ouest.",
  },
  manifest: "/manifest.webmanifest",
  appleWebApp: {
    capable: true,
    statusBarStyle: "black-translucent",
    title: "COTA",
  },
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html
      lang="fr"
      className={`${bebasNeue.variable} ${dmSans.variable} ${dmMono.variable}`}
      style={{ background: "#000000" }}
    >
      <body style={{ background: "#000000" }}>
        <OneSignalInit />

        {/* ── Nav — alignée avec l'identité Flutter ── */}
        <nav style={{ background: "#111111", borderBottom: "1px solid #1E1E1E", position: "sticky", top: 0, zIndex: 50 }}>
          <div style={{ maxWidth: 1200, margin: "0 auto", padding: "0 16px", height: 56, display: "flex", alignItems: "center", justifyContent: "space-between" }}>
            <a href="/" style={{ display: "flex", alignItems: "center", gap: 8, textDecoration: "none" }}>
              <div style={{ background: "rgba(249,255,0,0.10)", border: "1px solid rgba(249,255,0,0.25)", borderRadius: 8, width: 32, height: 32, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 16 }}>⚽</div>
              <span style={{ fontWeight: 900, fontSize: 18, color: "#FFFFFF", letterSpacing: "-0.3px" }}>COTA</span>
            </a>
            <div style={{ display: "flex", alignItems: "center", gap: 4 }}>
              {[
                { href: "/predictions", label: "Picks"       },
                { href: "/coupon",      label: "Coupon"      },
                { href: "/bookmakers",  label: "Bookmakers"  },
              ].map((l) => (
                <a key={l.href} href={l.href} style={{ padding: "6px 12px", borderRadius: 8, fontSize: 13, fontWeight: 600, color: "#888888", textDecoration: "none" }}
                  onMouseEnter={(e) => { const el = e.currentTarget; el.style.color = "#F9FF00"; el.style.background = "rgba(249,255,0,0.08)"; }}
                  onMouseLeave={(e) => { const el = e.currentTarget; el.style.color = "#888888"; el.style.background = "transparent"; }}
                >{l.label}</a>
              ))}
              <a href="/subscribe" style={{ marginLeft: 8, background: "#F9FF00", color: "#000000", fontWeight: 800, fontSize: 12, padding: "7px 14px", borderRadius: 8, textDecoration: "none" }}>Premium</a>
            </div>
          </div>
        </nav>

        {children}
      </body>
    </html>
  );
}
