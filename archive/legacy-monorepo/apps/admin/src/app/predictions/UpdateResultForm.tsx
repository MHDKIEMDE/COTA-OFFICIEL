"use client";

import { createClient } from "@/lib/supabase/client";
import { useRouter } from "next/navigation";
import { useState } from "react";

export default function UpdateResultForm({ predictionId }: { predictionId: string }) {
  const [loading, setLoading] = useState(false);
  const router = useRouter();
  const supabase = createClient();

  async function setResult(result: "win" | "loss" | "void") {
    setLoading(true);
    await supabase.from("predictions").update({ result }).eq("id", predictionId);
    router.refresh();
    setLoading(false);
  }

  return (
    <div className="flex gap-1">
      <button
        onClick={() => setResult("win")}
        disabled={loading}
        className="px-2 py-1 bg-green-500/20 hover:bg-green-500/40 text-green-400 text-xs rounded-lg transition"
      >✓</button>
      <button
        onClick={() => setResult("loss")}
        disabled={loading}
        className="px-2 py-1 bg-red-500/20 hover:bg-red-500/40 text-red-400 text-xs rounded-lg transition"
      >✗</button>
      <button
        onClick={() => setResult("void")}
        disabled={loading}
        className="px-2 py-1 bg-gray-500/20 hover:bg-gray-500/40 text-gray-400 text-xs rounded-lg transition"
      >—</button>
    </div>
  );
}
