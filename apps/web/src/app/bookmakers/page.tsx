import Link from "next/link";

const BOOKMAKERS = [
  {
    name: "1xBet",
    logo: "1️⃣",
    description: "Bonus de bienvenue jusqu'à 130 000 XOF. Large choix de marchés africains.",
    bonus: "Jusqu'à 130 000 XOF",
    url: process.env.NEXT_PUBLIC_AFFILIATE_1XBET ?? "#",
    highlight: true,
  },
  {
    name: "Bet9ja",
    logo: "9️⃣",
    description: "Leader en Afrique de l'Ouest. Cotes compétitives sur les championnats locaux.",
    bonus: "200% sur 1er dépôt",
    url: process.env.NEXT_PUBLIC_AFFILIATE_BET9JA ?? "#",
    highlight: false,
  },
  {
    name: "Betway",
    logo: "🎯",
    description: "Interface intuitive, paiements rapides via Mobile Money.",
    bonus: "50% jusqu'à 50 000 XOF",
    url: process.env.NEXT_PUBLIC_AFFILIATE_BETWAY ?? "#",
    highlight: false,
  },
  {
    name: "MelBet",
    logo: "🏆",
    description: "Très bonnes cotes, multiples options de retrait dont Wave et Orange Money.",
    bonus: "100% jusqu'à 75 000 XOF",
    url: process.env.NEXT_PUBLIC_AFFILIATE_MELBET ?? "#",
    highlight: false,
  },
];

export const metadata = {
  title: "Bookmakers Recommandés — COTA",
  description: "Nos partenaires bookmakers fiables avec bonus exclusifs pour l'Afrique de l'Ouest.",
};

export default function BookmakersPage() {
  return (
    <main className="min-h-screen bg-gray-950 text-white">
      <div className="max-w-4xl mx-auto px-4 py-12">
        <div className="mb-10">
          <h1 className="text-3xl font-black mb-2">Bookmakers Recommandés</h1>
          <p className="text-gray-400">
            Sites de paris fiables, avec des bonus exclusifs et des paiements Mobile Money.
          </p>
        </div>

        <div className="grid sm:grid-cols-2 gap-6">
          {BOOKMAKERS.map((bm) => (
            <div
              key={bm.name}
              className={`relative rounded-2xl p-6 border flex flex-col gap-4 ${
                bm.highlight
                  ? "bg-amber-950/30 border-amber-600"
                  : "bg-gray-900 border-gray-800"
              }`}
            >
              {bm.highlight && (
                <span className="absolute top-4 right-4 text-xs bg-amber-500 text-black font-black px-2 py-0.5 rounded-full uppercase">
                  Top choix
                </span>
              )}

              <div className="flex items-center gap-3">
                <span className="text-3xl">{bm.logo}</span>
                <div>
                  <h2 className="text-lg font-black">{bm.name}</h2>
                  <p className="text-amber-400 text-sm font-semibold">{bm.bonus}</p>
                </div>
              </div>

              <p className="text-gray-400 text-sm flex-1">{bm.description}</p>

              <Link
                href={bm.url}
                target="_blank"
                rel="noopener noreferrer sponsored"
                className={`text-center py-2.5 rounded-xl text-sm font-bold transition-all ${
                  bm.highlight
                    ? "bg-amber-500 hover:bg-amber-400 text-black"
                    : "bg-gray-700 hover:bg-gray-600 text-white"
                }`}
              >
                Obtenir le bonus →
              </Link>
            </div>
          ))}
        </div>

        <p className="mt-10 text-xs text-gray-600 text-center">
          Liens sponsorisés. COTA perçoit une commission sans surcoût pour vous.
          Pariez de manière responsable. Jeu interdit aux mineurs.
        </p>
      </div>
    </main>
  );
}
