<?php
require_once __DIR__ . '/functions.php';

use SCM\Middleware\TenantMiddleware;
use SCM\Security\Headers;

startSecureSession();
TenantMiddleware::handle();
Headers::send();

$siteName = getSetting('site_name', SITE_NAME);
$phone = getSetting('phone', '');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(getSetting('site_description', 'Cabinet infirmier à Nice')) ?>">
    <meta name="theme-color" content="#0d6e6e">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#9764;</text></svg>">
</head>
<body>
    <!-- Skip navigation for accessibility -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <!-- Top bar -->
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-left">
                <?php if ($phone): ?>
                <span class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    <a href="tel:<?= e(str_replace(' ', '', $phone)) ?>"><?= e($phone) ?></a>
                </span>
                <?php endif; ?>
                <span class="topbar-item topbar-hide-mobile">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?= e(getSetting('opening_hours', 'Lun-Ven : 7h-19h')) ?>
                </span>
            </div>
            <div class="topbar-right">
                <?php if ($fb = getSetting('facebook_url')): ?>
                <a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook" class="topbar-social">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($ig = getSetting('instagram_url')): ?>
                <a href="<?= e($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram" class="topbar-social">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <span class="logo-icon">&#9764;</span>
                <div class="logo-text">
                    <span class="logo-name"><?= e($siteName) ?></span>
                    <span class="logo-sub">Soins infirmiers &middot; Nice</span>
                </div>
            </a>

            <nav class="nav" id="nav" aria-label="Navigation principale">
                <ul class="nav-list">
                    <li><a href="/" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">Accueil</a></li>
                    <li><a href="/rendez-vous.php" class="nav-link <?= $currentPage === 'rendez-vous' ? 'active' : '' ?>">Rendez-vous</a></li>
                    <li><a href="/prevention.php" class="nav-link <?= $currentPage === 'prevention' ? 'active' : '' ?>">Prévention</a></li>
                    <li><a href="/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
                    <li><a href="/rendez-vous.php" class="nav-link btn-nav">Prendre RDV</a></li>
                </ul>
            </nav>

            <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <main id="main-content">
