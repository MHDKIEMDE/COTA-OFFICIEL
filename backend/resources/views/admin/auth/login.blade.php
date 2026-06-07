<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — COTA Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg:#0b0d10; --bg-2:#15181d;
            --line:#1d2026; --line-2:#2a2e36;
            --ink:#f4efe2; --dim:#8b8a85; --dim-2:#5a5d63;
            --accent:#e8ff36; --loss:#ff5b3a; --win:#3ddc91;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Space Grotesk', sans-serif;
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-2);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 40px 36px;
        }

        /* Wordmark */
        .wordmark {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }
        .wordmark-icon {
            width: 40px; height: 40px;
            background: var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .wordmark-icon svg { width: 24px; height: 24px; }
        .wordmark-text {
            font-family: Archivo, sans-serif;
            font-weight: 900;
            font-size: 20px;
            color: var(--ink);
            letter-spacing: .04em;
        }
        .wordmark-sub {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .14em;
            color: var(--dim);
            text-transform: uppercase;
        }

        /* Alert */
        .alert { display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:500; }
        .alert-error   { background:rgba(255,91,58,.10); border:1px solid rgba(255,91,58,.30); color:var(--loss); }
        .alert-success { background:rgba(61,220,145,.10); border:1px solid rgba(61,220,145,.30); color:var(--win); }

        /* Form */
        .field { margin-bottom: 18px; }
        .field label { display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:var(--dim); letter-spacing:.06em; text-transform:uppercase; }
        .input-wrap { position: relative; }
        .input-wrap .icon { position:absolute; inset-y:0; left:14px; display:flex; align-items:center; color:var(--dim-2); font-size:13px; pointer-events:none; }
        .input-brand {
            width:100%; height:48px; padding:0 14px 0 40px;
            background: var(--bg);
            border: 1px solid var(--line-2);
            border-radius: 10px;
            color: var(--ink);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color .15s;
        }
        .input-brand:focus { border-color: var(--accent); }
        .input-brand::placeholder { color: var(--dim-2); }

        .field-error { margin-top: 5px; font-size: 12px; color: var(--loss); }

        /* Checkbox */
        .check-row { display:flex; align-items:center; gap:8px; margin-bottom:24px; }
        .check-row input[type=checkbox] { width:16px; height:16px; accent-color:var(--accent); cursor:pointer; }
        .check-row label { font-size:13px; color:var(--dim); cursor:pointer; }

        /* Button */
        .btn-submit {
            width:100%; height:48px;
            background: var(--accent);
            color: #0b0d10;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .04em;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .15s;
        }
        .btn-submit:hover { opacity: .88; }

        .footer-note { margin-top: 24px; text-align:center; font-size:12px; color:var(--dim-2); }
    </style>
</head>
<body>
    <div class="login-card">

        <!-- Wordmark -->
        <div class="wordmark">
            <div class="wordmark-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <text x="12" y="18" text-anchor="middle" font-family="Archivo,sans-serif" font-weight="900" font-size="18" fill="#0b0d10">C</text>
                </svg>
            </div>
            <div>
                <div class="wordmark-text">COTA</div>
                <div class="wordmark-sub">Admin Panel</div>
            </div>
        </div>

        <!-- Alertes -->
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulaire -->
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <!-- Email -->
            <div class="field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" id="email" name="email"
                           class="input-brand"
                           value="{{ old('email') }}"
                           placeholder="admin@cota.app"
                           required autofocus>
                </div>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <!-- Mot de passe -->
            <div class="field">
                <label for="password">Mot de passe</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="password" name="password"
                           class="input-brand"
                           placeholder="••••••••"
                           required>
                </div>
            </div>

            <!-- Remember -->
            <div class="check-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>

            <!-- Soumettre -->
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>
        </form>

        <p class="footer-note">Accès réservé aux super administrateurs</p>
    </div>
</body>
</html>
