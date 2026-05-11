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
      style={{ background: "#080C14" }}
    >
      <body>
        <OneSignalInit />
        {children}
      </body>
    </html>
  );
}
