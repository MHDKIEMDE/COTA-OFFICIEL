# Inventaire complet des pages Blade — COTA
## Toutes sections : Visiteur + Auth + Admin

---

## PARTIE 1 — PAGES PUBLIQUES / VISITEUR

### Socle (2 layouts + 1 root)
| Fichier | Rôle |
|---------|------|
| `app.blade.php` | Root SPA (point d'entrée Livewire) |
| `layouts/app.blade.php` | Layout principal visiteur — nav, footer |
| `layouts/auth.blade.php` | Layout pages auth — centré, minimaliste |

---

### 🌐 Landing (1 page)
| Fichier | Contenu |
|---------|---------|
| `welcome.blade.php` | Page d'accueil publique — hero, features, CTA téléchargement app |

---

### 🔐 Auth visiteur (3 pages + 3 Livewire)
| Fichier | Contenu |
|---------|---------|
| `auth/login.blade.php` | Connexion par téléphone/email |
| `auth/register.blade.php` | Inscription — nom, téléphone, email |
| `auth/verify-otp.blade.php` | Vérification code OTP 6 chiffres |
| `livewire/auth/login-form.blade.php` | Composant Livewire du formulaire login |
| `livewire/auth/register-form.blade.php` | Composant Livewire du formulaire inscription |
| `livewire/auth/verify-otp-form.blade.php` | Composant Livewire vérification OTP |

---

### 📊 Dashboard visiteur (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/dashboard.blade.php` | Vue principale — prédictions du jour, coupon IA, matchs live, stats personnelles |

---

### 🎯 Prédictions (2 pages)
| Fichier | Contenu |
|---------|---------|
| `pages/predictions/index.blade.php` | Liste prédictions du jour — filtres compétition, étoiles, premium lock |
| `pages/predictions/show.blade.php` | Détail prédiction — analyse complète, scores 9 critères, cote, probabilités |

---

### ⚽ Matchs live (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/live.blade.php` | Scores en direct — liste matchs live avec chrono, score temps réel |

---

### 🏆 Compétitions (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/competitions.blade.php` | Filtrage par ligue/compétition — sélection pour filtrer les prédictions |

---

### ⭐ Favoris (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/favorites.blade.php` | Prédictions et matchs mis en favoris par l'utilisateur |

---

### 📜 Historique (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/history.blade.php` | Historique personnel — prédictions passées, résultats WON/LOST |

---

### 📈 Statistiques personnelles (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/statistics.blade.php` | Stats du compte — win rate, ROI personnel, breakdown par type de pari |

---

### 💳 Abonnement (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/subscription.blade.php` | Plans premium — mensuel/hebdo, paiement Wave/Orange Money/MTN via Paydunya |

---

### 👤 Profil (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/profile.blade.php` | Profil utilisateur — infos, abonnement actif, déconnexion |

---

### 🤝 Parrainage (1 page)
| Fichier | Contenu |
|---------|---------|
| `pages/referral.blade.php` | Lien de parrainage personnel, filleuls, récompenses débloquées |

---

### 📄 Légal (1 page)
| Fichier | Contenu |
|---------|---------|
| `privacy.blade.php` | Politique de confidentialité RGPD |

---

### 🧩 Composants réutilisables (5 composants)
| Fichier | Rôle |
|---------|------|
| `components/prediction-card.blade.php` | Card prédiction — équipes, cote, étoiles, statut |
| `components/confidence-ring.blade.php` | Anneau de confiance SVG (score 0–100) |
| `components/odds-chip.blade.php` | Badge cote bookmaker |
| `components/stat-card.blade.php` | Card statistique avec icône et valeur |
| `components/status-badge.blade.php` | Badge WON / LOST / PENDING / LIVE |

---

### ❌ Pages d'erreur (5 pages)
| Fichier | Contenu |
|---------|---------|
| `errors/403.blade.php` | Accès refusé |
| `errors/404.blade.php` | Page introuvable |
| `errors/419.blade.php` | Session expirée (CSRF) |
| `errors/500.blade.php` | Erreur serveur |
| `errors/layout-error.blade.php` | Erreur de layout générique |

---

---

## PARTIE 2 — PAGES ADMIN

### Socle admin (1 layout)
| Fichier | Rôle |
|---------|------|
| `admin/layouts/app.blade.php` | Sidebar, topbar, tokens CSS, structure globale |

---

### 🔐 Auth admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/auth/login.blade.php` | Connexion admin — email + password |

---

### 📊 Dashboard admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/dashboard.blade.php` | KPIs, graphiques 7j/30j, dernières prédictions, derniers abonnements |

---

### 🎯 Prédictions admin (3 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/predictions/index.blade.php` | Liste toutes prédictions — filtres, pagination, actions |
| `admin/predictions/create.blade.php` | Création manuelle prédiction |
| `admin/predictions/edit.blade.php` | Édition prédiction |

---

### 📈 Statistiques admin (2 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/stats/index.blade.php` | Win rate, ROI, graphiques 30j/12m, breakdown complet |
| `admin/stats/funnel.blade.php` | Funnel conversion Visiteur → Inscrit → Premium |

---

### 🏆 Compétitions admin (3 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/competitions/index.blade.php` | Liste ligues avec tier 1–4 |
| `admin/competitions/create.blade.php` | Ajout compétition |
| `admin/competitions/edit.blade.php` | Édition compétition |

---

### 👥 Utilisateurs admin (3 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/users/index.blade.php` | Liste users — filtres, pagination |
| `admin/users/show.blade.php` | Profil complet user |
| `admin/users/edit.blade.php` | Édition manuelle user |

---

### 💳 Abonnements admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/subscriptions/index.blade.php` | Liste abonnements, graphique revenus, accord manuel |

---

### 🤝 Parrainages admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/referrals/index.blade.php` | Liste parrainages, top parrains, paliers |

---

### 🔗 Affiliations admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/affiliates/index.blade.php` | Liens affiliés, clics, conversions, primes |

---

### 💬 Feedbacks admin (2 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/feedbacks/index.blade.php` | Liste feedbacks — filtres statut |
| `admin/feedbacks/show.blade.php` | Détail + réponse admin |

---

### 🏪 Bookmakers admin (3 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/bookmakers/index.blade.php` | Liste bookmakers par région |
| `admin/bookmakers/create.blade.php` | Ajout bookmaker |
| `admin/bookmakers/edit.blade.php` | Édition bookmaker |

---

### 📝 Articles bookmakers admin (3 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/bookmaker_blogs/index.blade.php` | Liste articles SEO |
| `admin/bookmaker_blogs/create.blade.php` | Rédaction article |
| `admin/bookmaker_blogs/edit.blade.php` | Édition article |

---

### 🔍 Candidats bookmakers admin (2 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/bookmaker_candidates/index.blade.php` | Liste candidatures en attente |
| `admin/bookmaker_candidates/show.blade.php` | Détail + validation/rejet |

---

### 🎟️ Coupon IA admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/coupon/index.blade.php` | Coupon du jour — picks, cote totale, gain potentiel |

---

### 📰 Actualités admin (4 pages)
| Fichier | Contenu |
|---------|---------|
| `admin/news_sources/index.blade.php` | Liste sources configurées |
| `admin/news_sources/create.blade.php` | Ajout source |
| `admin/news_sources/edit.blade.php` | Édition source |
| `admin/news_sources/articles.blade.php` | Articles importés d'une source |

---

### 📡 Monitoring API admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/api_monitor/index.blade.php` | Quota API-Football, Redis, latences, alertes |

---

### ⚙️ Paramètres admin (1 page)
| Fichier | Contenu |
|---------|---------|
| `admin/settings/index.blade.php` | Clés API, Paydunya, seuils algo, maintenance |

---

## Résumé global

| Partie | Section | Pages |
|--------|---------|-------|
| **Visiteur** | Landing | 1 |
| | Auth (pages + Livewire) | 6 |
| | Dashboard visiteur | 1 |
| | Prédictions | 2 |
| | Matchs live | 1 |
| | Compétitions | 1 |
| | Favoris | 1 |
| | Historique | 1 |
| | Statistiques perso | 1 |
| | Abonnement | 1 |
| | Profil | 1 |
| | Parrainage | 1 |
| | Légal | 1 |
| | Composants | 5 |
| | Erreurs | 5 |
| **Admin** | Layout + Auth | 2 |
| | Dashboard | 1 |
| | Prédictions | 3 |
| | Statistiques | 2 |
| | Compétitions | 3 |
| | Utilisateurs | 3 |
| | Abonnements | 1 |
| | Parrainages | 1 |
| | Affiliations | 1 |
| | Feedbacks | 2 |
| | Bookmakers | 3 |
| | Articles | 3 |
| | Candidats | 2 |
| | Coupon IA | 1 |
| | Actualités | 4 |
| | Monitoring | 1 |
| | Paramètres | 1 |
| | **Total Admin** | **34** |
| | **Total Visiteur** | **30** |
| | **TOTAL GÉNÉRAL** | **64** |
