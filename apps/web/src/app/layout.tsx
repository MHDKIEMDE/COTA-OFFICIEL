import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import OneSignalInit from "@/components/OneSignalInit";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
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
    description:
      "Pronostics football alimentés par l'IA pour l'Afrique de l'Ouest.",
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
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="fr"
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col">
        <OneSignalInit />
        {children}
      </body>
    </html>
  );
}
