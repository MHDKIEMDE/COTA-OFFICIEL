# COTA — TODO

## Sprint 1 — Fondations ✅
- [x] Monorepo Turborepo
- [x] Next.js 15 (web + admin)
- [x] Expo React Native (mobile)
- [x] Package types partagé
- [x] Package api-client partagé
- [x] Git + push GitHub COTA-OFFICIEL

## Sprint 2 — Authentification Supabase ✅
- [x] Créer projet Supabase + configurer variables d'env
- [x] Schéma DB : profiles, subscriptions, predictions, coupons, leagues, matches
- [x] Auth Supabase (OTP email + Google OAuth)
- [x] Gestion des rôles : free / premium / admin (RLS)
- [x] Écrans auth web (login OTP + Google)
- [x] Écrans auth mobile (login OTP)
- [ ] ⚠️ Exécuter schema.sql dans Supabase Dashboard (SQL Editor)

## Sprint 3 — Algorithme & Données ✅
- [x] Setup FastAPI (Python) dans backend/algo/
- [x] Intégration API-Football (matchs du jour, stats, H2H)
- [x] Algorithme de prédiction v3 (9 critères, score 0-100)
- [x] Endpoint /predictions/today
- [x] Endpoint /predictions/coupon
- [x] Endpoint /predictions/history
- [x] Job quotidien (cron) de génération des prédictions

## Sprint 4 — App principale (web + mobile) ✅
- [x] Page pronostics du jour (liste + détail)
- [x] Coupon IA combiné du jour
- [x] Filtrage par compétitions (Tier 1–4)
- [x] Niveaux de confiance (1–4 étoiles)
- [x] Contenu verrouillé premium
- [x] 4 états gérés : loading / error / empty / success

## Sprint 5 — Admin back-office ✅
- [x] Dashboard : stats clés (users, revenus, win rate)
- [x] Gestion matchs + prédictions (CRUD)
- [x] Gestion utilisateurs + abonnements
- [x] Gestion compétitions et tiers
- [x] Historique des résultats

## Sprint 6 — Monétisation ✅
- [x] Intégration Paydunya (Wave, Orange Money, MTN, Moov)
- [x] Système freemium (verrouillage contenu → /subscribe)
- [x] Plans d'abonnement (mensuel 2500 / trimestriel 6500 / annuel 20000 XOF)
- [x] Liens affiliation bookmakers (/bookmakers)
- [ ] ⚠️ Remplir les vraies clés Paydunya dans .env (PAYDUNYA_MASTER_KEY, etc.)
- [ ] ⚠️ Remplir SUPABASE_SERVICE_ROLE_KEY dans apps/web/.env.local
- [ ] ⚠️ Remplir les liens affiliation réels dans .env.local

## Sprint 7 — Notifications & Polish ✅
- [x] Intégration OneSignal web (SDK CDN + OneSignalSDKWorker.js)
- [x] Intégration expo-notifications mobile (hook useNotifications + register token)
- [x] Cron 7h15 — notification pronostics du jour (OneSignal + Expo push)
- [x] Cron 21h00 — notification résultats du soir (win rate)
- [x] PWA — manifest.ts + icônes déjà présents + service worker OneSignal
- [x] SEO — metadata globale layout.tsx + landing page COTA complète
- [ ] ⚠️ Remplir NEXT_PUBLIC_ONESIGNAL_APP_ID dans apps/web/.env.local
- [ ] ⚠️ Remplir ONESIGNAL_APP_ID + ONESIGNAL_REST_KEY dans backend/algo/.env
- [ ] ⚠️ Créer table push_tokens dans Supabase (user_id, expo_token)

## Sprint 8 — Déploiement ✅
- [x] vercel.json + next.config.ts web + admin (vars env)
- [x] Dockerfile + railway.toml FastAPI (healthcheck /health)
- [x] eas.json : profils development / preview / production
- [x] DEPLOY.md : guide complet Vercel + Railway + EAS + Supabase
- [ ] ⚠️ MANUEL : `vercel --prod` dans apps/web et apps/admin
- [ ] ⚠️ MANUEL : `railway up` dans backend/algo
- [ ] ⚠️ MANUEL : `eas build --profile preview --platform android`
- [ ] ⚠️ MANUEL : exécuter schema.sql + créer table push_tokens dans Supabase
- [ ] ⚠️ MANUEL : configurer domaines cota.ci / admin.cota.ci / api.cota.ci
