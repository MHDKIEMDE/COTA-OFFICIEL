<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b0d10">
    <title>Politique de confidentialité — COTA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:   #0b0d10;
            --bg2:  #15181d;
            --bg3:  #1e2028;
            --ink:  #f4efe2;
            --dim:  #b8b4a8;
            --acc:  #e8ff36;
            --line: #2a2e36;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Space Grotesk', sans-serif;
            line-height: 1.7;
        }

        .pp-wrap {
            max-width: 680px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        .pp-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--dim);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 36px;
        }
        .pp-back:hover { color: var(--ink); }
        .pp-back__arrow { font-size: 16px; }

        .pp-header { margin-bottom: 40px; }

        .pp-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 28px;
        }
        .pp-logo__icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--acc);
            display: flex; align-items: center; justify-content: center;
        }
        .pp-logo__letter {
            font-family: 'Archivo', sans-serif;
            font-size: 20px; font-weight: 900;
            color: var(--bg);
        }
        .pp-logo__name {
            font-family: 'Archivo', sans-serif;
            font-size: 20px; font-weight: 900;
            color: var(--ink);
            letter-spacing: -.02em;
        }

        .pp-title {
            font-family: 'Archivo', sans-serif;
            font-size: 28px; font-weight: 900;
            color: var(--ink);
            letter-spacing: -.02em;
            margin-bottom: 6px;
        }
        .pp-date {
            font-size: 12px;
            color: var(--dim);
        }

        .pp-intro {
            font-size: 14px;
            color: var(--dim);
            background: var(--bg2);
            border: 1px solid var(--line);
            border-left: 3px solid var(--acc);
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .pp-section { margin-bottom: 28px; }

        .pp-section__title {
            font-family: 'Archivo', sans-serif;
            font-size: 14px; font-weight: 900;
            color: var(--acc);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 12px;
        }

        .pp-section p {
            font-size: 14px;
            color: var(--dim);
            margin-bottom: 10px;
        }

        .pp-section ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .pp-section ul li {
            font-size: 14px;
            color: var(--dim);
            padding-left: 16px;
            position: relative;
            line-height: 1.6;
        }
        .pp-section ul li::before {
            content: '—';
            position: absolute;
            left: 0;
            color: var(--acc);
            font-weight: 700;
        }

        .pp-section strong { color: var(--ink); font-weight: 700; }

        a { color: var(--acc); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .pp-divider {
            height: 1px;
            background: var(--line);
            margin: 36px 0;
        }

        .pp-footer {
            font-size: 11px;
            color: var(--dim);
            text-align: center;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="pp-wrap">

    <a href="/" class="pp-back">
        <span class="pp-back__arrow">←</span> Retour à l'accueil
    </a>

    <div class="pp-header">
        <a href="/" class="pp-logo">
            <div class="pp-logo__icon">
                <span class="pp-logo__letter">C</span>
            </div>
            <span class="pp-logo__name">COTA</span>
        </a>
        <h1 class="pp-title">Politique de confidentialité</h1>
        <p class="pp-date">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    <div class="pp-intro">
        COTA est une application mobile de pronostics football pour l'Afrique de l'Ouest.
        Cette politique décrit comment nous collectons, utilisons et protégeons vos données personnelles,
        conformément au RGPD, à la loi ivoirienne sur la protection des données et aux exigences du
        Google Play Store et de l'App Store d'Apple.
    </div>

    <div class="pp-section">
        <div class="pp-section__title">1. Données collectées</div>
        <ul>
            <li><strong>Compte :</strong> numéro de téléphone ou adresse e-mail, utilisés pour l'authentification OTP.</li>
            <li><strong>Connexion sociale (optionnel) :</strong> identifiant public, nom d'affichage et adresse e-mail si vous utilisez Google ou Facebook.</li>
            <li><strong>Localisation approximative (IP) :</strong> nous détectons votre pays via votre adresse IP pour adapter l'indicatif téléphonique et les bookmakers affichés. Aucune position GPS n'est collectée.</li>
            <li><strong>Données de paiement :</strong> les transactions Wave, Orange Money, MTN et Moov sont traitées par Paydunya. Nous ne stockons aucune donnée de carte bancaire.</li>
            <li><strong>Token FCM :</strong> identifiant de notification push pour vous envoyer les pronostics du jour et les résultats.</li>
            <li><strong>Données de parrainage :</strong> code de parrainage utilisé lors de l'inscription.</li>
            <li><strong>Feedbacks :</strong> messages que vous nous envoyez via le formulaire de retour.</li>
        </ul>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">2. Utilisation des données</div>
        <ul>
            <li>Authentification et gestion de votre compte.</li>
            <li>Envoi des pronostics quotidiens et résultats par notification push.</li>
            <li>Calcul de votre abonnement premium et activation des accès.</li>
            <li>Gestion du système de parrainage et attribution des récompenses.</li>
            <li>Amélioration de l'algorithme de prédiction via les statistiques agrégées et anonymisées.</li>
        </ul>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">3. Authentification biométrique</div>
        <p>
            Si vous activez Face ID ou l'empreinte digitale, cette donnée est traitée exclusivement par le système
            d'exploitation de votre appareil (iOS Secure Enclave / Android Keystore). COTA ne reçoit jamais
            vos données biométriques — seul un résultat booléen (authentifié / non authentifié) nous est transmis.
        </p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">4. Partage des données</div>
        <p>Nous ne vendons jamais vos données. Elles peuvent être partagées uniquement avec :</p>
        <ul>
            <li><strong>Paydunya</strong> — traitement des paiements Mobile Money.</li>
            <li><strong>Firebase (Google)</strong> — envoi des notifications push (FCM).</li>
            <li><strong>API-Football / Sportradar</strong> — aucune donnée utilisateur n'est transmise, uniquement des requêtes de données sportives.</li>
        </ul>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">5. Conservation des données</div>
        <p>
            Vos données sont conservées aussi longtemps que votre compte est actif. Vous pouvez demander
            la suppression de votre compte et de toutes vos données à tout moment en nous contactant.
            Les données de transaction sont conservées 5 ans à des fins légales et comptables.
        </p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">6. Vos droits</div>
        <ul>
            <li>Accès, rectification ou suppression de vos données personnelles.</li>
            <li>Opposition au traitement ou limitation de celui-ci.</li>
            <li>Portabilité de vos données.</li>
            <li>Retrait du consentement à tout moment pour les notifications push (via les paramètres de l'app).</li>
        </ul>
        <p style="margin-top:12px;">Pour exercer ces droits : <a href="mailto:privacy@cota.app">privacy@cota.app</a></p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">7. Sécurité</div>
        <p>
            Les communications entre l'application et nos serveurs sont chiffrées via HTTPS/TLS.
            Les tokens d'authentification sont stockés dans le trousseau sécurisé de l'appareil
            (iOS Keychain / Android Keystore via flutter_secure_storage).
            Les mots de passe sont hachés avec bcrypt côté serveur.
        </p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">8. Mineurs</div>
        <p>
            COTA est réservé aux personnes majeures (18 ans et plus). Nous ne collectons pas sciemment
            de données concernant des mineurs. Si vous pensez qu'un mineur a créé un compte,
            contactez-nous pour suppression immédiate.
        </p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">9. Modifications</div>
        <p>
            Toute modification sera notifiée via l'application avant son entrée en vigueur.
            La date de mise à jour en haut de cette page fait foi.
        </p>
    </div>

    <div class="pp-section">
        <div class="pp-section__title">10. Contact</div>
        <p>
            COTA — <a href="mailto:privacy@cota.app">privacy@cota.app</a><br>
            Abidjan, Côte d'Ivoire
        </p>
    </div>

    <div class="pp-divider"></div>

    <div class="pp-footer">
        © {{ date('Y') }} COTA · Pariez de manière responsable · Jeu interdit aux mineurs
    </div>

</div>
</body>
</html>
