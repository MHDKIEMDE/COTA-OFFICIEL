# COTA OFFICIEL

Application de pronostics football IA — web + mobile.

## Stack

- **Web** : Next.js 15 + Tailwind CSS
- **Mobile** : Expo (React Native)
- **Admin** : Next.js 15 + Refine
- **Backend** : Supabase (PostgreSQL + Auth + Realtime)
- **Algo IA** : FastAPI (Python)
- **Push** : OneSignal
- **Paiement** : Paydunya

## Structure

```
apps/
  web/      → Site web + PWA
  mobile/   → App iOS/Android
  admin/    → Back-office admin
packages/
  types/      → Types TypeScript partagés
  api-client/ → Client API partagé
backend/
  algo/     → FastAPI algorithme IA
```

## Démarrage

```bash
pnpm install
pnpm dev
```
