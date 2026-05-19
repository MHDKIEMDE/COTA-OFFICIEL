<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('code') — COTA</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #0b0d10;
      color: #f4efe2;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 32px 24px;
    }

    .wordmark {
      font-size: 13px;
      font-weight: 900;
      letter-spacing: 0.25em;
      color: #e8ff36;
      margin-bottom: 56px;
    }

    .icon {
      width: 64px;
      height: 64px;
      margin-bottom: 28px;
    }

    .code {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.2em;
      color: #e8ff36;
      margin-bottom: 12px;
      font-family: 'SF Mono', 'Fira Code', monospace;
    }

    h1 {
      font-size: 20px;
      font-weight: 900;
      letter-spacing: 0.06em;
      color: #f4efe2;
      margin-bottom: 12px;
      text-align: center;
    }

    p {
      font-size: 14px;
      color: #8b8a85;
      line-height: 1.6;
      text-align: center;
      max-width: 380px;
      margin-bottom: 40px;
    }

    .btn {
      display: inline-block;
      background: #e8ff36;
      color: #0b0d10;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 0.1em;
      text-decoration: none;
      padding: 14px 28px;
      border-radius: 10px;
    }

    .btn:hover { opacity: 0.88; }

    .error-ref {
      margin-top: 48px;
      font-size: 10px;
      font-family: 'SF Mono', 'Fira Code', monospace;
      color: #3a3a3a;
      letter-spacing: 0.08em;
    }
  </style>
</head>
<body>

  <div class="wordmark">COTA</div>

  @yield('icon')

  <div class="code">@yield('code')</div>
  <h1>@yield('title')</h1>
  <p>@yield('subtitle')</p>

  <a href="/" class="btn">← RETOUR À L'ACCUEIL</a>

  <div class="error-ref">
    ref · {{ request()->url() }} · {{ now()->format('Y-m-d H:i:s') }}
  </div>

</body>
</html>
