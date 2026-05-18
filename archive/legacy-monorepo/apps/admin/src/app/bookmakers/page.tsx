"use client";

import { useEffect, useState } from "react";

const API = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

type Bookmaker = {
  id?: number;
  name: string;
  slug: string;
  primary_color: string;
  affiliate_link: string;
  download_link: string;
  description: string;
  is_active: boolean;
  sort_order: number;
};

const EMPTY: Bookmaker = {
  name: "",
  slug: "",
  primary_color: "#F9FF00",
  affiliate_link: "",
  download_link: "",
  description: "",
  is_active: true,
  sort_order: 0,
};

function slugify(str: string) {
  return str.toLowerCase().replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "");
}

export default function BookmakersAdminPage() {
  const [bookmakers, setBookmakers] = useState<Bookmaker[]>([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState<Bookmaker>(EMPTY);
  const [editing, setEditing] = useState<number | null>(null);
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState<{ type: "ok" | "err"; text: string } | null>(null);

  const load = async () => {
    setLoading(true);
    try {
      const r = await fetch(`${API}/bookmakers`);
      const d = await r.json();
      setBookmakers(d.data ?? []);
    } catch {
      setMsg({ type: "err", text: "Impossible de charger les bookmakers" });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);

  const handleField = (k: keyof Bookmaker, v: string | boolean | number) => {
    setForm((f) => ({
      ...f,
      [k]: v,
      ...(k === "name" && !editing ? { slug: slugify(v as string) } : {}),
    }));
  };

  const startEdit = (bm: Bookmaker) => {
    setEditing(bm.id ?? null);
    setForm({ ...bm });
    setMsg(null);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const cancelEdit = () => {
    setEditing(null);
    setForm(EMPTY);
    setMsg(null);
  };

  const save = async () => {
    if (!form.name || !form.affiliate_link) {
      setMsg({ type: "err", text: "Nom et lien inscription requis" });
      return;
    }
    setSaving(true);
    setMsg(null);
    try {
      const url = editing
        ? `${API}/admin/bookmakers/${editing}`
        : `${API}/admin/bookmakers`;
      const method = editing ? "PUT" : "POST";
      const r = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      if (!r.ok) throw new Error(await r.text());
      setMsg({ type: "ok", text: editing ? "Bookmaker mis à jour ✓" : "Bookmaker ajouté ✓" });
      cancelEdit();
      await load();
    } catch (e: any) {
      setMsg({ type: "err", text: e.message ?? "Erreur serveur" });
    } finally {
      setSaving(false);
    }
  };

  const toggle = async (bm: Bookmaker) => {
    try {
      await fetch(`${API}/admin/bookmakers/${bm.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...bm, is_active: !bm.is_active }),
      });
      await load();
    } catch {
      setMsg({ type: "err", text: "Erreur lors de la mise à jour" });
    }
  };

  const remove = async (id: number) => {
    if (!confirm("Supprimer ce bookmaker ?")) return;
    await fetch(`${API}/admin/bookmakers/${id}`, { method: "DELETE" });
    await load();
  };

  return (
    <div className="p-8 flex flex-col gap-8 max-w-5xl">

      {/* ── En-tête ── */}
      <div>
        <h1 className="text-2xl font-black text-white">🎰 Bookmakers</h1>
        <p className="text-gray-500 text-sm mt-1">
          Gérez les liens d'inscription et de téléchargement. Modifiez à tout moment sans redéploiement.
        </p>
      </div>

      {/* ── Message feedback ── */}
      {msg && (
        <div className={`px-4 py-3 rounded-xl text-sm font-medium ${msg.type === "ok" ? "bg-green-900/40 text-green-400 border border-green-800" : "bg-red-900/40 text-red-400 border border-red-800"}`}>
          {msg.text}
        </div>
      )}

      {/* ── Formulaire ajout / édition ── */}
      <div className="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h2 className="text-base font-bold text-white mb-5">
          {editing ? "✏️ Modifier le bookmaker" : "➕ Ajouter un bookmaker"}
        </h2>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Nom *</label>
            <input
              value={form.name}
              onChange={(e) => handleField("name", e.target.value)}
              placeholder="ex: Bet365"
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Slug</label>
            <input
              value={form.slug}
              onChange={(e) => handleField("slug", e.target.value)}
              placeholder="ex: bet365"
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-gray-400 placeholder-gray-600 focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="md:col-span-2 flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">
              🔗 Lien inscription (avec code affilié) *
            </label>
            <input
              value={form.affiliate_link}
              onChange={(e) => handleField("affiliate_link", e.target.value)}
              placeholder="https://bet365.com/ref=TONCODE"
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="md:col-span-2 flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">
              📱 Lien téléchargement app (APK ou store)
            </label>
            <input
              value={form.download_link}
              onChange={(e) => handleField("download_link", e.target.value)}
              placeholder="https://bet365.com/app ou lien Play Store"
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Description courte</label>
            <input
              value={form.description}
              onChange={(e) => handleField("description", e.target.value)}
              placeholder="ex: Bonus 130 000 XOF à l'inscription"
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Couleur (hex)</label>
            <div className="flex gap-2 items-center">
              <input
                type="color"
                value={form.primary_color}
                onChange={(e) => handleField("primary_color", e.target.value)}
                className="w-10 h-10 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer"
              />
              <input
                value={form.primary_color}
                onChange={(e) => handleField("primary_color", e.target.value)}
                className="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-yellow-500"
              />
            </div>
          </div>

          <div className="flex flex-col gap-1">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Ordre d'affichage</label>
            <input
              type="number"
              value={form.sort_order}
              onChange={(e) => handleField("sort_order", parseInt(e.target.value) || 0)}
              className="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-yellow-500"
            />
          </div>

          <div className="flex items-center gap-3">
            <label className="text-xs text-gray-500 font-medium uppercase tracking-wider">Actif dans l'app</label>
            <button
              onClick={() => handleField("is_active", !form.is_active)}
              className={`w-11 h-6 rounded-full transition relative ${form.is_active ? "bg-yellow-500" : "bg-gray-700"}`}
            >
              <span className={`absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all ${form.is_active ? "left-5" : "left-0.5"}`} />
            </button>
          </div>
        </div>

        <div className="flex gap-3 mt-6">
          <button
            onClick={save}
            disabled={saving}
            className="bg-yellow-500 hover:bg-yellow-400 text-black font-bold text-sm px-6 py-2.5 rounded-xl transition disabled:opacity-50"
          >
            {saving ? "Enregistrement..." : editing ? "Mettre à jour" : "Ajouter"}
          </button>
          {editing && (
            <button
              onClick={cancelEdit}
              className="border border-gray-700 text-gray-400 hover:text-white text-sm px-5 py-2.5 rounded-xl transition"
            >
              Annuler
            </button>
          )}
        </div>
      </div>

      {/* ── Liste des bookmakers ── */}
      <div className="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
          <h2 className="text-sm font-bold text-white">Bookmakers configurés ({bookmakers.length})</h2>
          <p className="text-xs text-gray-600">Les modifications sont instantanées dans l'app</p>
        </div>

        {loading ? (
          <div className="p-10 text-center text-gray-600 text-sm">Chargement...</div>
        ) : bookmakers.length === 0 ? (
          <div className="p-10 text-center text-gray-600 text-sm">
            Aucun bookmaker — ajoutez le premier ci-dessus.
          </div>
        ) : (
          <div className="divide-y divide-gray-800">
            {bookmakers.map((bm) => (
              <div key={bm.id} className="flex items-center gap-4 px-6 py-4 hover:bg-gray-800/30 transition">
                {/* Couleur indicator */}
                <div
                  className="w-2 h-10 rounded-full flex-shrink-0"
                  style={{ background: bm.primary_color }}
                />

                {/* Infos */}
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <span className="text-sm font-bold text-white">{bm.name}</span>
                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${bm.is_active ? "bg-green-900/50 text-green-400" : "bg-gray-800 text-gray-500"}`}>
                      {bm.is_active ? "Actif" : "Inactif"}
                    </span>
                  </div>
                  {bm.description && (
                    <p className="text-xs text-gray-500 mb-1">{bm.description}</p>
                  )}
                  <div className="flex gap-4 text-xs">
                    {bm.affiliate_link ? (
                      <a href={bm.affiliate_link} target="_blank" className="text-yellow-500 hover:underline truncate max-w-xs">
                        🔗 Inscription
                      </a>
                    ) : (
                      <span className="text-gray-600">🔗 Pas de lien inscription</span>
                    )}
                    {bm.download_link ? (
                      <a href={bm.download_link} target="_blank" className="text-blue-400 hover:underline truncate max-w-xs">
                        📱 Téléchargement
                      </a>
                    ) : (
                      <span className="text-gray-600">📱 Pas de lien app</span>
                    )}
                  </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2 flex-shrink-0">
                  <button
                    onClick={() => toggle(bm)}
                    className={`text-xs px-3 py-1.5 rounded-lg font-medium transition ${bm.is_active ? "bg-gray-800 text-gray-400 hover:text-white" : "bg-green-900/40 text-green-400 hover:bg-green-900/60"}`}
                  >
                    {bm.is_active ? "Désactiver" : "Activer"}
                  </button>
                  <button
                    onClick={() => startEdit(bm)}
                    className="text-xs px-3 py-1.5 rounded-lg bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500/20 font-medium transition"
                  >
                    Modifier
                  </button>
                  <button
                    onClick={() => remove(bm.id!)}
                    className="text-xs px-3 py-1.5 rounded-lg bg-red-900/30 text-red-400 hover:bg-red-900/50 font-medium transition"
                  >
                    Supprimer
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
