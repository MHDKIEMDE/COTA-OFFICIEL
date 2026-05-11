import { createClient } from "@supabase/supabase-js";
import { NextRequest, NextResponse } from "next/server";

const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL!,
  process.env.SUPABASE_SERVICE_ROLE_KEY!
);

export async function POST(req: NextRequest) {
  const data = await req.json();

  if (data?.status !== "completed") {
    return NextResponse.json({ received: true });
  }

  const custom = data.custom_data ?? {};
  const userId = custom.user_id;
  const months = Number(custom.months ?? 1);
  const plan = custom.plan;

  if (!userId) return NextResponse.json({ received: true });

  const now = new Date();
  const expiresAt = new Date(now.getTime() + months * 30 * 24 * 60 * 60 * 1000);

  await supabase.from("subscriptions").upsert(
    {
      user_id: userId,
      plan,
      status: "active",
      started_at: now.toISOString(),
      expires_at: expiresAt.toISOString(),
      paydunya_token: data.invoice?.token ?? null,
    },
    { onConflict: "user_id" }
  );

  await supabase.from("profiles").update({ role: "premium" }).eq("id", userId);

  return NextResponse.json({ received: true });
}
