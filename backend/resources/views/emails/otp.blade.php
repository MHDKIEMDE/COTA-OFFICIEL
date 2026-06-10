<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code COTA</title>
</head>
<body style="margin:0;padding:0;background:#000;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:480px;margin:0 auto;padding:32px 24px;">
        <h1 style="color:#FFEB3B;font-size:24px;margin:0 0 8px;">COTA</h1>
        <p style="color:#ffffff;font-size:15px;line-height:1.5;">
            Voici votre code de connexion :
        </p>
        <div style="background:#111;border:1px solid rgba(255,235,59,0.3);border-radius:12px;padding:20px;text-align:center;margin:20px 0;">
            <span style="color:#FFEB3B;font-size:32px;font-weight:bold;letter-spacing:8px;">{{ $code }}</span>
        </div>
        <p style="color:#888;font-size:13px;line-height:1.5;">
            Ce code est valable {{ $ttlMinutes }} minutes. Ne le partagez avec personne.
        </p>
        <p style="color:#666;font-size:11px;margin-top:24px;">
            COTA est un outil d'aide à la décision. Pariez de façon responsable.
        </p>
    </div>
</body>
</html>
