import type { Metadata } from "next";
import { Geist } from "next/font/google";
import "./globals.css";
import AdminSidebar from "@/components/layout/AdminSidebar";

const geist = Geist({ variable: "--font-geist", subsets: ["latin"] });

export const metadata: Metadata = {
  title: "COTA Admin",
  description: "Back-office COTA",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="fr" className={`${geist.variable} h-full`}>
      <body className="min-h-full flex bg-gray-950 text-white antialiased">
        <AdminSidebar />
        <main className="flex-1 overflow-auto">{children}</main>
      </body>
    </html>
  );
}
