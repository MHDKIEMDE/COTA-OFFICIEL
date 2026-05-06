import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "COTA — Pronostics Football IA pour l'Afrique de l'Ouest",
  description:
    "Analyse IA de 9 critères par match, coupon combiné quotidien, cotes et alertes en temps réel. Abonnement Mobile Money.",
};

export default function HomePage() {
  return (
    <main className="min-h-screen bg-gray-950 text-white flex flex-col">
      {/* Nav */}
      <header className="border-b border-gray-900">
        <div className="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
          <span className="text-2xl font-black text-green-400">COTA</span>
          <nav className="flex items-center gap-4 text-sm">
            <Link href="/predictions" className="text-gray-400 hover:text-white transition">
              Pronostics
            </Link>
            <Link href="/coupon" className="text-gray-400 hover:text-white transition">
              Coupon IA
            </Link>
            <Link href="/bookmakers" className="text-gray-400 hover:text-white transition">
              Bookmakers
            </Link>
            <Link
              href="/subscribe"
              className="bg-amber-500 hover:bg-amber-400 text-black font-bold px-4 py-2 rounded-xl transition"
            >
              Premium
            </Link>
          </nav>
        </div>
      </header>

      {/* Hero */}
      <section className="flex-1 flex flex-col items-center justify-center text-center px-4 py-20">
        <div className="inline-block bg-green-900/30 border border-green-700 text-green-400 text-xs font-bold px-3 py-1 rounded-full mb-6 uppercase tracking-widest">
          Algorithme IA v3 — 9 critères
        </div>
        <h1 className="text-5xl md:text-6xl font-black mb-6 leading-tight">
          Les meilleurs pronostics<br />
          <span className="text-green-400">football d'Afrique</span>
        </h1>
        <p className="text-gray-400 text-lg max-w-xl mb-10">
          Notre IA analyse forme récente, H2H, blessures, cotes et 5 autres critères
          pour générer chaque matin le coupon combiné le plus solide du jour.
        </p>
        <div className="flex flex-wrap gap-4 justify-center">
          <Link
            href="/predictions"
            className="bg-green-600 hover:bg-green-500 text-white font-bold px-8 py-4 rounded-2xl text-lg transition"
          >
            Voir les pronostics
          </Link>
          <Link
            href="/subscribe"
            className="bg-amber-500 hover:bg-amber-400 text-black font-bold px-8 py-4 rounded-2xl text-lg transition"
          >
            Débloquer Premium
          </Link>
        </div>
      </section>

      {/* Features */}
      <section className="border-t border-gray-900 py-16">
        <div className="max-w-5xl mx-auto px-4 grid sm:grid-cols-3 gap-8 text-center">
          {[
            { icon: "🤖", title: "Algorithme IA", desc: "9 critères analysés automatiquement chaque matin avant les matchs." },
            { icon: "🎯", title: "Coupon combiné", desc: "Le meilleur combo du jour sélectionné parmi les matchs les plus sûrs." },
            { icon: "📲", title: "Mobile Money", desc: "Paiement Wave, Orange Money, MTN ou Moov en moins de 30 secondes." },
          ].map((f) => (
            <div key={f.title} className="flex flex-col items-center gap-3">
              <span className="text-4xl">{f.icon}</span>
              <h3 className="font-black text-lg">{f.title}</h3>
              <p className="text-gray-500 text-sm">{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-gray-900 py-6 text-center text-xs text-gray-600">
        © 2025 COTA · Pariez de manière responsable · Jeu interdit aux mineurs
      </footer>
    </main>
  );
}
