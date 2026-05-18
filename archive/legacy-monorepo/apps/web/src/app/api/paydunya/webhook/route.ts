import { NextRequest, NextResponse } from "next/server";

const LARAVEL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export async function POST(req: NextRequest) {
  const body = await req.text();

  const res = await fetch(`${LARAVEL}/payments/webhook/paydunya`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Paydunya-Secret": process.env.PAYDUNYA_MASTER_KEY ?? "",
    },
    body,
  });

  const data = await res.json().catch(() => ({ received: true }));
  return NextResponse.json(data, { status: res.status });
}
