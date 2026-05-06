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

## Sprint 3 — Algorithme & Données
- [ ] Setup FastAPI (Python) dans backend/algo/
- [ ] Intégration API-Football (matchs du jour, stats, H2H)
- [ ] Algorithme de prédiction v3 (9 critères, score 0-100)
- [ ] Endpoint /predictions/today
- [ ] Endpoint /predictions/coupon
- [ ] Endpoint /predictions/history
- [ ] Job quotidien (cron) de génération des prédictions

## Sprint 4 — App principale (web + mobile)
- [ ] Page pronostics du jour (liste + détail)
- [ ] Coupon IA combiné du jour
- [ ] Filtrage par compétitions (Tier 1–4)
- [ ] Niveaux de confiance (1–4 étoiles)
- [ ] Contenu verrouillé premium
- [ ] 4 états gérés : loading / error / empty / success

## Sprint 5 — Admin back-office
- [ ] Dashboard : stats clés (users, revenus, win rate)
- [ ] Gestion matchs + prédictions (CRUD)
- [ ] Gestion utilisateurs + abonnements
- [ ] Gestion compétitions et tiers
- [ ] Historique des résultats

## Sprint 6 — Monétisation
- [ ] Intégration Paydunya (Wave, Orange Money, MTN, Moov)
- [ ] Système freemium (verrouillage contenu)
- [ ] Plans d'abonnement (mensuel / trimestriel / annuel)
- [ ] Liens affiliation bookmakers

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
