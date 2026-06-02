<?php
/**
 * Page de maintenance — SCM Garnier Infirmier
 *
 * Page autonome (aucune dépendance base de données) affichée lorsque le site
 * est en travaux. Renvoie un statut HTTP 503 + Retry-After afin que les
 * moteurs de recherche ne désindexent pas le site pendant l'intervention.
 *
 * L'activation/désactivation se fait via le bloc « Maintenance mode » du
 * fichier .htaccess (à la racine). L'accès à /admin/ reste autorisé.
 */

http_response_code(503);
header('Retry-After: 3600');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Numéro de contact (valeur par défaut, modifiable ici si besoin)
$phone = '04 93 00 00 00';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Site en maintenance | Cabinet Infirmier Garnier</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#9764;</text></svg>">
    <style>
        :root {
            --teal: #0d6e6e;
            --teal-dark: #0a5757;
            --bg: #f4f8f8;
            --text: #1f2d2d;
            --muted: #5b6b6b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(160deg, var(--bg) 0%, #e3efef 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(13, 110, 110, 0.12);
            max-width: 560px;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
        }
        .icon {
            font-size: 3.5rem;
            color: var(--teal);
            line-height: 1;
        }
        h1 {
            font-size: 1.7rem;
            margin: 1.25rem 0 0.75rem;
            color: var(--teal-dark);
        }
        p {
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.65;
            margin-bottom: 0.75rem;
        }
        .divider {
            height: 1px;
            background: #e3efef;
            margin: 1.75rem 0;
        }
        .contact {
            font-size: 0.95rem;
            color: var(--muted);
        }
        .contact a {
            display: inline-block;
            margin-top: 0.75rem;
            background: var(--teal);
            color: #fff;
            text-decoration: none;
            padding: 0.7rem 1.6rem;
            border-radius: 999px;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .contact a:hover { background: var(--teal-dark); }
        .footer-note {
            margin-top: 1.75rem;
            font-size: 0.8rem;
            color: #9aa9a9;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#9764;</div>
        <h1>Site en cours de maintenance</h1>
        <p>Notre site est temporairement indisponible le temps d'une intervention technique.</p>
        <p>Nous serons de retour très prochainement. Merci de votre compréhension.</p>
        <div class="divider"></div>
        <div class="contact">
            <p>Pour toute urgence ou prise de rendez-vous, contactez-nous :</p>
            <a href="tel:<?= htmlspecialchars(str_replace(' ', '', $phone), ENT_QUOTES) ?>"><?= htmlspecialchars($phone, ENT_QUOTES) ?></a>
        </div>
        <p class="footer-note">Cabinet Infirmier Garnier &middot; Nice</p>
    </div>
</body>
</html>
