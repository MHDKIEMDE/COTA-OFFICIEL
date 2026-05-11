# Guide de déploiement COTA

## 1. Vercel — Web + Admin

### Prérequis
- Compte Vercel : https://vercel.com
- CLI : `npm i -g vercel`

### Web
```bash
cd apps/web
vercel --prod
```
Variables d'env à ajouter dans Vercel Dashboard → Settings → Environment Variables :
```
NEXT_PUBLIC_SUPABASE_URL
NEXT_PUBLIC_SUPABASE_ANON_KEY
NEXT_PUBLIC_API_URL          # https://api.cota.ci
NEXT_PUBLIC_ONESIGNAL_APP_ID
SUPABASE_SERVICE_ROLE_KEY
NEXT_PUBLIC_AFFILIATE_1XBET
NEXT_PUBLIC_AFFILIATE_BETWAY
NEXT_PUBLIC_AFFILIATE_BET9JA
NEXT_PUBLIC_AFFILIATE_MELBET
```

### Admin
```bash
cd apps/admin
vercel --prod
```
Variables :
```
NEXT_PUBLIC_SUPABASE_URL
NEXT_PUBLIC_SUPABASE_ANON_KEY
SUPABASE_SERVICE_ROLE_KEY
```

---

## 2. Railway — FastAPI

### Prérequis
- Compte Railway : https://railway.app
- CLI : `npm i -g @railway/cli`

### Déploiement
```bash
cd backend/algo
railway login
railway init        # Crée un nouveau projet
railway up          # Déploie via Dockerfile
```

Variables d'env à ajouter dans Railway Dashboard :
```
SUPABASE_URL
SUPABASE_SERVICE_ROLE_KEY
API_FOOTBALL_KEY
PAYDUNYA_MASTER_KEY
PAYDUNYA_PRIVATE_KEY
PAYDUNYA_TOKEN
PAYDUNYA_PUBLIC_KEY
WEB_URL             # https://cota.ci
BACKEND_URL         # https://api.cota.ci (ton domaine Railway)
ONESIGNAL_APP_ID
ONESIGNAL_REST_KEY
```

Railway génère une URL comme `https://cota-algo.up.railway.app` → utilise-la comme `NEXT_PUBLIC_API_URL` dans Vercel.

---

## 3. Expo EAS Build — Android

### Prérequis
- Compte Expo : https://expo.dev
- CLI EAS : `npm i -g eas-cli`
- Connexion : `eas login`

### APK interne (test)
```bash
cd apps/mobile
eas build --profile preview --platform android
```
→ Génère un `.apk` téléchargeable directement.

### Production (Play Store)
```bash
eas build --profile production --platform android
eas submit --platform android
```
→ Upload l'`.aab` sur le Play Store (compte Google Play requis).

### Mettre à jour l'EAS project ID
Dans `app.json`, remplace :
```json
"projectId": "your-eas-project-id"
```
par l'ID généré après `eas init`.

---

## 4. Supabase — Actions manuelles

1. Aller sur https://app.supabase.com → ton projet → SQL Editor
2. Coller et exécuter le contenu de `backend/api/schema.sql`
3. Créer la table `push_tokens` :
```sql
CREATE TABLE push_tokens (
  user_id UUID PRIMARY KEY REFERENCES profiles(id) ON DELETE CASCADE,
  expo_token TEXT NOT NULL,
  created_at TIMESTAMPTZ DEFAULT NOW()
);
```

---

## 5. Domaine personnalisé

- **cota.ci** → Vercel (web)
- **admin.cota.ci** → Vercel (admin)
- **api.cota.ci** → Railway (FastAPI)

Configurer dans les dashboards respectifs après déploiement.
