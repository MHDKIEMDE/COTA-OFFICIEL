"use client";

import { createClient } from "@/lib/supabase/client";
import { useRouter } from "next/navigation";
import { useState } from "react";

export default function ToggleLeagueForm({
  leagueId,
  isActive,
}: {
  leagueId: string;
  isActive: boolean;
}) {
  const [active, setActive] = useState(isActive);
  const [loading, setLoading] = useState(false);
  const router = useRouter();
  const supabase = createClient();

  async function toggle() {
    setLoading(true);
    const next = !active;
    await supabase.from("leagues").update({ is_active: next }).eq("id", leagueId);
    setActive(next);
    router.refresh();
    setLoading(false);
  }

  return (
    <button
      onClick={toggle}
      disabled={loading}
      className={`relative w-10 h-6 rounded-full transition-colors ${
        active ? "bg-green-600" : "bg-gray-700"
      }`}
    >
      <span
        className={`absolute top-1 w-4 h-4 bg-white rounded-full transition-transform ${
          active ? "translate-x-5" : "translate-x-1"
        }`}
      />
    </button>
  );
}
