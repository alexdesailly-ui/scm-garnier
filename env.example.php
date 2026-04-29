<?php
/**
 * Configuration environnement - SCM Garnier Infirmier
 *
 * INSTRUCTIONS HOSTINGER (hPanel) :
 * 1. Copiez ce fichier en "env.php" via le Gestionnaire de fichiers hPanel
 * 2. Remplissez les valeurs avec vos identifiants MySQL (hPanel > Bases de données > MySQL)
 * 3. Ne JAMAIS commit env.php dans Git (il est dans .gitignore)
 */

return [
    // Base de données MySQL (hPanel > Bases de données > MySQL Databases)
    // Format Hostinger : u123456789_nombase / u123456789_nomuser
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'u398408214_scm_garnier',
    'DB_USER' => 'u398408214_admin',
    'DB_PASS' => 'VotreMotDePasseMySQL',

    // URL du site (sans slash final)
    'SITE_URL' => 'https://scm-garnier-infirmier.fr',

    // Clé secrète (générez une chaîne aléatoire de 64 caractères)
    'APP_SECRET' => 'CHANGEZ_CECI_avec_une_chaine_aleatoire_de_64_caracteres_minimum',

    // Clé de chiffrement des données patients (ne JAMAIS la changer après installation)
    'ENCRYPTION_KEY' => 'CHANGEZ_CECI_cle_base64_de_32_octets',

    // Mode debug (false en production)
    'APP_DEBUG' => false,

    // Stripe (hPanel > ou dashboard.stripe.com)
    'STRIPE_SECRET_KEY' => 'sk_test_...',
    'STRIPE_PUBLISHABLE_KEY' => 'pk_test_...',
    'STRIPE_WEBHOOK_SECRET' => 'whsec_...',
    'STRIPE_PRICE_PRO' => 'price_...',
    'STRIPE_PRICE_ENTERPRISE' => 'price_...',
];
