<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0d10">
    <title>COTA — Pronostics Football par IA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --bg:     #0b0d10;
            --bg-2:   #15181d;
            --bg-3:   #1a1e25;
            --ink:    #f4efe2;
            --ink-2:  #c8c4b8;
            --dim:    #8b8a85;
            --dim-2:  #5a5d63;
            --accent: #e8ff36;
            --win:    #3ddc91;
            --loss:   #ff5b3a;
            --line:   #1d2026;
            --line-2: #2a2e36;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Space Grotesk', sans-serif;
            overflow-x: hidden;
        }

        /* ── NAV ─────────────────────────────────────────────── */
        .lp-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 40px;
            background: rgba(11,13,16,.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--line);
        }
        .lp-nav__brand {
            display: flex; align-items: center; gap: 8px;
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: 1.5rem; letter-spacing: -.02em; color: var(--ink);
            text-decoration: none;
        }
        .lp-nav__brand-dot { color: var(--accent); }
        .lp-nav__links { display: flex; align-items: center; gap: 8px; }
        .lp-nav__link {
            padding: 8px 16px; border-radius: 10px; font-size: 0.9375rem;
            font-weight: 600; color: var(--dim); text-decoration: none;
            transition: color .2s;
        }
        .lp-nav__link:hover { color: var(--ink); }
        .lp-nav__cta {
            padding: 10px 20px; border-radius: 10px; font-size: 0.9375rem;
            font-weight: 700; color: #0b0d10; background: var(--accent);
            text-decoration: none; transition: opacity .2s;
        }
        .lp-nav__cta:hover { opacity: .88; }

        /* ── HERO ────────────────────────────────────────────── */
        .lp-hero {
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; text-align: center;
            padding: 100px 24px 80px;
            position: relative; overflow: hidden;
        }
        .lp-hero__glow {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -60%);
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(232,255,54,.08) 0%, transparent 70%);
            pointer-events: none;
        }
        /* Ring animation */
        .lp-hero__ring-wrap {
            position: relative; width: 200px; height: 200px; margin: 0 auto 40px;
        }
        .lp-hero__ring {
            position: absolute; inset: 0;
            animation: spinRing 8s linear infinite;
        }
        .lp-hero__ring-inner {
            position: absolute; inset: 12px;
            animation: spinRing 5s linear infinite reverse;
        }
        @keyframes spinRing {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        .lp-hero__logo-letters {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: 2.25rem; letter-spacing: -.04em; color: var(--ink);
        }
        .lp-hero__logo-letters span { color: var(--accent); }

        .lp-hero__badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 20px;
            background: rgba(232,255,54,.1); border: 1px solid rgba(232,255,54,.2);
            color: var(--accent); font-size: 0.8125rem; font-weight: 600;
            margin-bottom: 24px;
        }
        .lp-hero__badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent); animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: .5; transform: scale(1.4); }
        }

        .lp-hero__title {
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: clamp(2.5rem, 7vw, 5rem); line-height: 1.0;
            letter-spacing: -.03em; color: var(--ink);
            margin-bottom: 20px;
        }
        .lp-hero__title em { color: var(--accent); font-style: normal; }

        .lp-hero__sub {
            max-width: 560px; font-size: 1.125rem; line-height: 1.7;
            color: var(--dim); margin: 0 auto 40px;
        }

        .lp-hero__actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .lp-hero__btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 12px; font-size: 1rem;
            font-weight: 700; color: #0b0d10; background: var(--accent);
            text-decoration: none; transition: opacity .2s;
        }
        .lp-hero__btn-primary:hover { opacity: .88; }
        .lp-hero__btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 12px; font-size: 1rem;
            font-weight: 600; color: var(--ink);
            background: var(--bg-2); border: 1px solid var(--line-2);
            text-decoration: none; transition: border-color .2s;
        }
        .lp-hero__btn-secondary:hover { border-color: var(--accent); }

        .lp-hero__stats {
            display: flex; gap: 40px; margin-top: 60px; justify-content: center;
        }
        .lp-hero__stat-val {
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: 2rem; color: var(--ink);
        }
        .lp-hero__stat-val em { color: var(--accent); font-style: normal; }
        .lp-hero__stat-label { font-size: 0.8125rem; color: var(--dim); margin-top: 2px; }

        /* ── SECTION LABELS ──────────────────────────────────── */
        .lp-section { padding: 80px 40px; }
        .lp-section--alt { background: var(--bg-2); }
        .lp-section__label {
            font-size: 0.6875rem; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase; color: var(--accent);
            margin-bottom: 12px;
        }
        .lp-section__title {
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: clamp(1.75rem, 4vw, 2.75rem); letter-spacing: -.02em;
            color: var(--ink); margin-bottom: 16px;
        }
        .lp-section__sub { color: var(--dim); font-size: 1.0625rem; max-width: 520px; line-height: 1.7; }
        .container { max-width: 1100px; margin: 0 auto; }

        /* ── 9 CRITÈRES ──────────────────────────────────────── */
        .criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px; margin-top: 48px;
        }
        .criteria-card {
            background: var(--bg-2); border: 1px solid var(--line);
            border-radius: 16px; padding: 24px;
            transition: border-color .2s;
        }
        .criteria-card:hover { border-color: rgba(232,255,54,.3); }
        .criteria-card__icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(232,255,54,.1); display: flex;
            align-items: center; justify-content: center;
            font-size: 1.25rem; margin-bottom: 16px;
            color: var(--accent);
        }
        .criteria-card__weight {
            font-family: 'JetBrains Mono', monospace; font-size: 0.75rem;
            color: var(--dim); margin-bottom: 8px;
        }
        .criteria-card__title {
            font-weight: 700; font-size: 1rem;
            color: var(--ink); margin-bottom: 6px;
        }
        .criteria-card__desc { font-size: 0.875rem; color: var(--dim); line-height: 1.5; }

        /* ── CARDS PREVIEW ───────────────────────────────────── */
        .cards-preview {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px; margin-top: 48px;
        }
        .preview-card {
            background: var(--bg-2); border: 1px solid var(--line);
            border-radius: 16px; overflow: hidden;
        }
        .preview-card__header {
            padding: 10px 14px; border-bottom: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
        }
        .preview-card__league { font-size: 0.6875rem; font-weight: 600; color: var(--dim); text-transform: uppercase; letter-spacing: .06em; }
        .preview-card__status {
            font-size: 0.6875rem; font-weight: 700; padding: 3px 8px;
            border-radius: 6px;
        }
        .preview-card__status--win  { background: rgba(61,220,145,.14); color: var(--win); }
        .preview-card__status--pend { background: rgba(245,166,35,.14); color: #f5a623; }
        .preview-card__body { padding: 16px 14px; }
        .preview-card__vs {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .preview-card__team { text-align: center; flex: 1; }
        .preview-card__team-abbr {
            width: 44px; height: 44px; border-radius: 10px;
            background: var(--bg-3); display: flex; align-items: center;
            justify-content: center; margin: 0 auto 6px;
            font-weight: 800; font-size: 0.8125rem; color: var(--ink);
        }
        .preview-card__team-name { font-size: 0.75rem; font-weight: 600; color: var(--ink-2); }
        .preview-card__sep { padding: 0 8px; text-align: center; }
        .preview-card__sep-vs { font-size: 0.6875rem; font-weight: 800; color: var(--dim); letter-spacing: .08em; }
        .preview-card__sep-time { font-size: 0.6875rem; color: var(--dim); margin-top: 2px; }
        .preview-card__pick {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 12px; background: var(--bg-3); border-radius: 10px;
        }
        .preview-card__pick-label { font-size: 0.6875rem; color: var(--dim); }
        .preview-card__pick-val { font-size: 0.9375rem; font-weight: 700; color: var(--ink); }
        .preview-card__odds {
            font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem;
            font-weight: 700; padding: 3px 10px; border-radius: 6px;
            background: var(--bg); border: 1px solid var(--line-2);
            color: var(--ink-2);
        }
        .preview-card__footer {
            padding: 8px 14px 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .star { color: #f5a623; font-size: 0.75rem; }
        .star--empty { color: var(--dim-2); }

        /* ── CTA ────────────────────────────────────────────── */
        .lp-cta {
            text-align: center; padding: 100px 24px;
            position: relative; overflow: hidden;
        }
        .lp-cta__glow {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(232,255,54,.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .lp-cta__title {
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: clamp(2rem, 5vw, 3.5rem); letter-spacing: -.025em;
            color: var(--ink); margin-bottom: 16px;
        }
        .lp-cta__sub { color: var(--dim); font-size: 1.0625rem; margin-bottom: 40px; }
        .lp-cta__stores { display: flex; align-items: center; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .lp-cta__store-btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 24px; border-radius: 12px; font-weight: 700;
            font-size: 0.9375rem; text-decoration: none; transition: opacity .2s;
        }
        .lp-cta__store-btn--primary {
            background: var(--accent); color: #0b0d10;
        }
        .lp-cta__store-btn--secondary {
            background: var(--bg-2); border: 1px solid var(--line-2); color: var(--ink);
        }
        .lp-cta__store-btn:hover { opacity: .88; }

        /* ── FOOTER ─────────────────────────────────────────── */
        .lp-footer {
            padding: 40px; border-top: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }
        .lp-footer__brand {
            font-family: 'Archivo', sans-serif; font-weight: 900;
            font-size: 1.125rem; letter-spacing: -.02em; color: var(--dim);
            text-decoration: none;
        }
        .lp-footer__brand span { color: var(--accent); }
        .lp-footer__copy { font-size: 0.8125rem; color: var(--dim-2); }
        .lp-footer__links { display: flex; gap: 20px; }
        .lp-footer__link { font-size: 0.8125rem; color: var(--dim-2); text-decoration: none; }
        .lp-footer__link:hover { color: var(--dim); }

        /* ── RESPONSIVE ─────────────────────────────────────── */
        @media (max-width: 768px) {
            .lp-nav { padding: 16px 20px; }
            .lp-nav__links { display: none; }
            .lp-section { padding: 60px 20px; }
            .lp-footer { flex-direction: column; text-align: center; padding: 32px 20px; }
            .lp-footer__links { justify-content: center; }
            .lp-hero__stats { gap: 24px; }
        }
    </style>
</head>
<body>

    {{-- NAV --}}
    <nav class="lp-nav">
        <a href="/" class="lp-nav__brand">COT<span class="lp-nav__brand-dot">A</span></a>
        <div class="lp-nav__links">
            <a href="#features" class="lp-nav__link">Fonctionnalités</a>
            <a href="#predictions" class="lp-nav__link">Pronostics</a>
        </div>
        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="lp-nav__cta">
            Accéder à l'app
        </a>
    </nav>

    {{-- HERO --}}
    <section class="lp-hero">
        <div class="lp-hero__glow"></div>

        {{-- Logo animé --}}
        <div class="lp-hero__ring-wrap">
            <svg class="lp-hero__ring" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="90" stroke="rgba(232,255,54,.12)" stroke-width="1"/>
                <circle cx="100" cy="100" r="90" stroke="rgba(232,255,54,.6)" stroke-width="1.5"
                        stroke-dasharray="60 505" stroke-linecap="round"/>
            </svg>
            <svg class="lp-hero__ring-inner" viewBox="0 0 176 176" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="88" cy="88" r="78" stroke="rgba(61,220,145,.15)" stroke-width="1"/>
                <circle cx="88" cy="88" r="78" stroke="rgba(61,220,145,.5)" stroke-width="1.5"
                        stroke-dasharray="30 459" stroke-linecap="round"/>
            </svg>
            <div class="lp-hero__logo-letters">COT<span>A</span></div>
        </div>

        <div class="lp-hero__badge">
            <span class="lp-hero__badge-dot"></span>
            IA — 9 critères d'analyse
        </div>

        <h1 class="lp-hero__title">
            Le pronostic<br>
            <em>intelligent</em><br>
            pour le foot
        </h1>
        <p class="lp-hero__sub">
            COTA analyse chaque match avec 9 critères pondérés — forme, H2H, météo, classement — pour vous donner les meilleures sélections du jour.
        </p>

        <div class="lp-hero__actions">
            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="lp-hero__btn-primary">
                <i class="bi bi-lightning-charge-fill"></i> Démarrer gratuitement
            </a>
            <a href="#features" class="lp-hero__btn-secondary">
                <i class="bi bi-info-circle"></i> En savoir plus
            </a>
        </div>

        <div class="lp-hero__stats">
            <div>
                <div class="lp-hero__stat-val">73<em>%</em></div>
                <div class="lp-hero__stat-label">Taux de réussite</div>
            </div>
            <div>
                <div class="lp-hero__stat-val">9<em>+</em></div>
                <div class="lp-hero__stat-label">Critères d'analyse</div>
            </div>
            <div>
                <div class="lp-hero__stat-val">50<em>+</em></div>
                <div class="lp-hero__stat-label">Ligues couvertes</div>
            </div>
        </div>
    </section>

    {{-- 9 CRITÈRES --}}
    <section class="lp-section" id="features">
        <div class="container">
            <div class="lp-section__label">Algorithme IA v3.0</div>
            <h2 class="lp-section__title">9 critères pour chaque pronostic</h2>
            <p class="lp-section__sub">Chaque prédiction est le résultat d'un algorithme pondéré analysant des dizaines de données par match.</p>

            <div class="criteria-grid">
                @foreach([
                    ['icon' => 'bi-bar-chart-line-fill', 'weight' => '25 pts', 'title' => 'Forme récente', 'desc' => '10 derniers matchs, séries et momentum de l\'équipe'],
                    ['icon' => 'bi-arrow-left-right', 'weight' => '20 pts', 'title' => 'Confrontations directes', 'desc' => '8 derniers H2H avec pondération temporelle'],
                    ['icon' => 'bi-house-fill', 'weight' => '15 pts', 'title' => 'Performance domicile/extérieur', 'desc' => 'Analyse séparée home et away sur la saison'],
                    ['icon' => 'bi-list-ol', 'weight' => '12 pts', 'title' => 'Position au classement', 'desc' => 'Standings actuels et écart de points entre équipes'],
                    ['icon' => 'bi-bullseye', 'weight' => '10 pts', 'title' => 'Statistiques de buts', 'desc' => 'Buts marqués/encaissés, BTTS, clean sheets'],
                    ['icon' => 'bi-clock-fill', 'weight' => '8 pts', 'title' => 'Heure du match', 'desc' => 'Bonus prime time, pénalité pour horaires décalés'],
                    ['icon' => 'bi-cloud-fill', 'weight' => '5 pts', 'title' => 'Météo', 'desc' => 'Température, précipitations, vent via OpenWeatherMap'],
                    ['icon' => 'bi-crosshair2', 'weight' => '3 pts', 'title' => 'Précision des tirs', 'desc' => 'Tirs cadrés et efficacité récente en attaque'],
                    ['icon' => 'bi-activity', 'weight' => '2 pts', 'title' => 'Forme physique', 'desc' => 'Fatigue accumulée, blessures, calendrier chargé'],
                ] as $c)
                    <div class="criteria-card">
                        <div class="criteria-card__icon"><i class="bi {{ $c['icon'] }}"></i></div>
                        <div class="criteria-card__weight">{{ $c['weight'] }} / 100</div>
                        <div class="criteria-card__title">{{ $c['title'] }}</div>
                        <div class="criteria-card__desc">{{ $c['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PREVIEW CARDS --}}
    <section class="lp-section lp-section--alt" id="predictions">
        <div class="container">
            <div class="lp-section__label">Aperçu</div>
            <h2 class="lp-section__title">Des pronostics clairs, chaque jour</h2>
            <p class="lp-section__sub">Cards simples, score de confiance 1–4 étoiles, cote estimée, et coupon IA combiné quotidien.</p>

            <div class="cards-preview">
                @foreach([
                    ['league' => 'Premier League', 'home' => 'MCI', 'away' => 'ARS', 'time' => '20:45', 'pick' => '1 (Victoire MCI)', 'odds' => '1.72', 'stars' => 4, 'status' => 'win'],
                    ['league' => 'La Liga', 'home' => 'BAR', 'away' => 'RM',  'time' => '21:00', 'pick' => 'Les deux équipes marquent', 'odds' => '1.58', 'stars' => 3, 'status' => 'pend'],
                    ['league' => 'Ligue 1', 'home' => 'PSG', 'away' => 'OL',  'time' => '17:05', 'pick' => 'Over 2.5 buts', 'odds' => '1.65', 'stars' => 3, 'status' => 'win'],
                ] as $card)
                    <div class="preview-card">
                        <div class="preview-card__header">
                            <span class="preview-card__league">{{ $card['league'] }}</span>
                            <span class="preview-card__status preview-card__status--{{ $card['status'] }}">
                                @if($card['status'] === 'win')
                                    <i class="bi bi-check-circle-fill"></i> Gagné
                                @else
                                    <i class="bi bi-clock"></i> En attente
                                @endif
                            </span>
                        </div>
                        <div class="preview-card__body">
                            <div class="preview-card__vs">
                                <div class="preview-card__team">
                                    <div class="preview-card__team-abbr">{{ $card['home'] }}</div>
                                    <div class="preview-card__team-name">{{ $card['home'] }}</div>
                                </div>
                                <div class="preview-card__sep">
                                    <div class="preview-card__sep-vs">VS</div>
                                    <div class="preview-card__sep-time">{{ $card['time'] }}</div>
                                </div>
                                <div class="preview-card__team">
                                    <div class="preview-card__team-abbr">{{ $card['away'] }}</div>
                                    <div class="preview-card__team-name">{{ $card['away'] }}</div>
                                </div>
                            </div>
                            <div class="preview-card__pick">
                                <div>
                                    <div class="preview-card__pick-label">Pronostic</div>
                                    <div class="preview-card__pick-val">{{ $card['pick'] }}</div>
                                </div>
                                <span class="preview-card__odds">{{ $card['odds'] }}</span>
                            </div>
                        </div>
                        <div class="preview-card__footer">
                            @for($s = 1; $s <= 4; $s++)
                                <i class="bi bi-star{{ $s <= $card['stars'] ? '-fill' : '' }} star{{ $s > $card['stars'] ? ' star--empty' : '' }}"></i>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA DOWNLOAD --}}
    <section class="lp-cta">
        <div class="lp-cta__glow"></div>
        <h2 class="lp-cta__title">Commencez à gagner<br>avec l'IA</h2>
        <p class="lp-cta__sub">Accès gratuit aux pronostics 1–2 étoiles. Premium pour les 3–4 étoiles.</p>
        <div class="lp-cta__stores">
            <a href="#" class="lp-cta__store-btn lp-cta__store-btn--primary">
                <i class="bi bi-google-play"></i> Google Play
            </a>
            <a href="#" class="lp-cta__store-btn lp-cta__store-btn--secondary">
                <i class="bi bi-apple"></i> App Store
            </a>
            <a href="{{ Route::has('login') ? route('login') : '#' }}" class="lp-cta__store-btn lp-cta__store-btn--secondary">
                <i class="bi bi-globe"></i> Version Web
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="lp-footer">
        <a href="/" class="lp-footer__brand">COT<span>A</span></a>
        <p class="lp-footer__copy">© {{ date('Y') }} COTA. Tous droits réservés.</p>
        <div class="lp-footer__links">
            <a href="#" class="lp-footer__link">Conditions</a>
            <a href="#" class="lp-footer__link">Confidentialité</a>
            <a href="#" class="lp-footer__link">Contact</a>
        </div>
    </footer>

</body>
</html>
