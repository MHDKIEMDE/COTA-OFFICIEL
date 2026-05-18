const BASE = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

type Bookmaker = {
  id: number;
  name: string;
  logo_url: string | null;
  color: string | null;
  description: string | null;
  affiliate_link: string | null;
  continent: string | null;
  bonus_label: string | null;
};

export const metadata = {
  title: "Bookmakers Recommandés — COTA",
  description: "Nos partenaires bookmakers fiables avec bonus exclusifs pour l'Afrique de l'Ouest.",
};

async function getBookmakers(): Promise<Bookmaker[]> {
  try {
    const res = await fetch(`${BASE}/bookmakers/by-region`, { next: { revalidate: 3600 } });
    if (!res.ok) return [];
    const json = await res.json();
    return json.data ?? [];
  } catch {
    return [];
  }
}

export default async function BookmakersPage() {
  const bookmakers = await getBookmakers();

  return (
    <main className="min-h-screen bg-[#000000] text-white">
      <div className="max-w-4xl mx-auto px-4 py-12">
        <div className="mb-10">
          <p className="text-[#F9FF00] text-xs font-bold uppercase tracking-widest mb-2">🏆 Partenaires</p>
          <h1 className="text-3xl font-black mb-2">Bookmakers Recommandés</h1>
          <p className="text-[#888888]">
            Sites de paris fiables, avec des bonus exclusifs et des paiements Mobile Money.
          </p>
        </div>

        {bookmakers.length === 0 ? (
          <div className="bg-[#111111] border border-[#1E1E1E] rounded-2xl p-8 text-center">
            <p className="text-[#888888]">Aucun bookmaker disponible pour le moment.</p>
          </div>
        ) : (
          <div className="grid sm:grid-cols-2 gap-6">
            {bookmakers.map((bm) => (
              <div
                key={bm.id}
                className="relative rounded-2xl p-6 border bg-[#111111] border-[#1E1E1E] flex flex-col gap-4"
                style={bm.color ? { borderColor: `${bm.color}40` } : undefined}
              >
                <div className="flex items-center gap-3">
                  {bm.logo_url ? (
                    <img src={bm.logo_url} alt={bm.name} className="w-10 h-10 rounded-lg object-contain bg-[#1A1A1A]" />
                  ) : (
                    <div
                      className="w-10 h-10 rounded-lg flex items-center justify-center text-black font-black text-sm"
                      style={{ background: bm.color ?? "#F9FF00" }}
                    >
                      {bm.name.slice(0, 2).toUpperCase()}
                    </div>
                  )}
                  <div>
                    <h2 className="text-lg font-black">{bm.name}</h2>
                    {bm.bonus_label && (
                      <p className="text-[#F9FF00] text-sm font-semibold">{bm.bonus_label}</p>
                    )}
                  </div>
                </div>

                {bm.description && (
                  <p className="text-[#888888] text-sm flex-1">{bm.description}</p>
                )}

                <a
                  href={bm.affiliate_link ?? "#"}
                  target="_blank"
                  rel="noopener noreferrer sponsored"
                  className="text-center py-2.5 rounded-xl text-sm font-bold transition-all bg-[#F9FF00] hover:bg-[#e8ee00] text-black"
                >
                  Obtenir le bonus →
                </a>
              </div>
            ))}
          </div>
        )}

        <p className="mt-10 text-xs text-[#444444] text-center">
          Liens sponsorisés. COTA perçoit une commission sans surcoût pour vous.
          Pariez de manière responsable. Jeu interdit aux mineurs.
        </p>
      </div>
    </main>
  );
}
