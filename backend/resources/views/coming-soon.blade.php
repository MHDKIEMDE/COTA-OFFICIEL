<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0b0d10">
<title>COTA — Bientôt disponible</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    background:#0b0d10; color:#f4efe2; min-height:100vh;
    font-family:"Space Grotesk",system-ui,sans-serif;
    display:flex; align-items:center; justify-content:center; padding:24px;
    background-image:radial-gradient(900px 460px at 50% -10%, rgba(232,255,54,.12), transparent 65%);
  }
  .box { text-align:center; max-width:460px; }
  .logo {
    width:64px; height:64px; border-radius:18px; background:#e8ff36; color:#0b0d10;
    font-family:"Archivo",sans-serif; font-weight:900; font-size:34px;
    display:grid; place-items:center; margin:0 auto 26px;
  }
  h1 { font-family:"Archivo",sans-serif; font-weight:900; font-size:34px; letter-spacing:-.5px; }
  .accent { color:#e8ff36; }
  p { color:#c7c4b8; font-size:16px; margin-top:14px; line-height:1.55; }
  .pill {
    display:inline-block; margin-top:26px; font-size:13px; font-weight:600;
    color:#e8ff36; background:rgba(232,255,54,.10); border:1px solid rgba(232,255,54,.25);
    padding:9px 18px; border-radius:999px;
  }
  .stores { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:30px; }
  .store-btn {
    display:inline-flex; align-items:center; gap:11px; text-decoration:none;
    background:#15181d; border:1px solid #2a2e36; border-radius:14px;
    padding:12px 20px; color:#f4efe2; transition:border-color .15s, transform .15s;
  }
  .store-btn:hover { border-color:#e8ff36; transform:translateY(-2px); }
  .store-btn .ic { font-size:26px; line-height:1; }
  .store-btn .t { text-align:left; }
  .store-btn .t small { display:block; color:#8b8a85; font-size:10px; letter-spacing:.6px; text-transform:uppercase; }
  .store-btn .t b { font-family:"Archivo",sans-serif; font-size:16px; }
  .foot { margin-top:40px; color:#5a5d63; font-size:13px; }
</style>
</head>
<body>
  <div class="box">
    <div class="logo">C</div>
    <h1>COTA arrive <span class="accent">bientôt</span> sur le web</h1>
    <p>Notre site est en cours de préparation. En attendant, profite de tous les pronostics directement dans l'application mobile.</p>

    <div class="stores">
      <a href="https://apps.apple.com/app/cota" class="store-btn" target="_blank" rel="noopener">
        <span class="ic">&#63743;</span>
        <span class="t"><small>Télécharger sur</small><b>App Store</b></span>
      </a>
      <a href="https://play.google.com/store/apps/details?id=com.cotafoot.app" class="store-btn" target="_blank" rel="noopener">
        <span class="ic">&#9654;</span>
        <span class="t"><small>Disponible sur</small><b>Google Play</b></span>
      </a>
    </div>

    <div class="foot">© {{ date('Y') }} COTA</div>
  </div>
</body>
</html>
