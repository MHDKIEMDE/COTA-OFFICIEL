"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const NAV = [
  { href: "/dashboard", label: "Dashboard", icon: "📊" },
  { href: "/predictions", label: "Prédictions", icon: "⚽" },
  { href: "/coupon", label: "Coupon du jour", icon: "🎯" },
  { href: "/matches", label: "Matchs", icon: "🏟️" },
  { href: "/users", label: "Utilisateurs", icon: "👥" },
  { href: "/subscriptions", label: "Abonnements", icon: "💳" },
  { href: "/leagues", label: "Compétitions", icon: "🏆" },
  { href: "/bookmakers", label: "Bookmakers", icon: "🎰" },
];

export default function AdminSidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-56 min-h-screen bg-gray-900 border-r border-gray-800 flex flex-col">
      <div className="px-6 py-5 border-b border-gray-800">
        <span className="text-xl font-black text-green-400">COTA</span>
        <span className="ml-2 text-xs text-gray-500 font-medium">Admin</span>
      </div>
      <nav className="flex-1 p-3 flex flex-col gap-1">
        {NAV.map((item) => {
          const active = pathname.startsWith(item.href);
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                active
                  ? "bg-green-600/20 text-green-400"
                  : "text-gray-400 hover:bg-gray-800 hover:text-white"
              }`}
            >
              <span>{item.icon}</span>
              {item.label}
            </Link>
          );
        })}
      </nav>
      <div className="p-4 border-t border-gray-800">
        <p className="text-xs text-gray-600">COTA Admin v2.0</p>
      </div>
    </aside>
  );
}
