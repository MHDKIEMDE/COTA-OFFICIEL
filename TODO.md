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

## Sprint 7 — Notifications & Polish
- [ ] Intégration OneSignal (push web + iOS + Android)
- [ ] Notification matchs du jour (matin)
- [ ] Notification résultats (soir)
- [ ] PWA (manifest, service worker, installable Android)
- [ ] Optimisations performance + SEO

## Sprint 8 — Déploiement
- [ ] Vercel (web + admin)
- [ ] Railway (FastAPI)
- [ ] Expo EAS Build (Play Store)
- [ ] App Store (optionnel)
