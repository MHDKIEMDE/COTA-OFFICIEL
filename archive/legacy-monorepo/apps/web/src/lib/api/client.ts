/**
 * Client HTTP vers le backend Laravel — identique au Dio Flutter.
 * Lit le token Sanctum depuis localStorage (côté client) ou cookie (côté serveur).
 */

const BASE = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

// ── Token (localStorage côté client) ─────────────────────────────────────────

export function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("sanctum_token");
}

export function setToken(token: string): void {
  localStorage.setItem("sanctum_token", token);
  document.cookie = `sanctum_token=${token}; path=/; max-age=${60 * 60 * 24 * 30}; SameSite=Lax`;
}

export function removeToken(): void {
  localStorage.removeItem("sanctum_token");
  document.cookie = "sanctum_token=; path=/; max-age=0";
}

export function isAuthenticated(): boolean {
  return !!getToken();
}

// ── Requête générique ─────────────────────────────────────────────────────────

async function request<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getToken();
  const headers: HeadersInit = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(options.headers ?? {}),
  };

  const res = await fetch(`${BASE}${path}`, { ...options, headers });

  if (res.status === 401) {
    removeToken();
    if (typeof window !== "undefined") window.location.href = "/login";
    throw new Error("Non authentifié");
  }

  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.message ?? `HTTP ${res.status}`);
  }

  return res.json() as Promise<T>;
}

// ── Méthodes ──────────────────────────────────────────────────────────────────

export const api = {
  get:    <T>(path: string)                          => request<T>(path, { method: "GET" }),
  post:   <T>(path: string, body: unknown)           => request<T>(path, { method: "POST",  body: JSON.stringify(body) }),
  put:    <T>(path: string, body: unknown)           => request<T>(path, { method: "PUT",   body: JSON.stringify(body) }),
  delete: <T>(path: string)                          => request<T>(path, { method: "DELETE" }),
};
