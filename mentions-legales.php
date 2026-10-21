<?php
$pageTitle = 'Mentions légales';
require_once __DIR__ . '/includes/header.php';

$address = getSetting('address', '');
$email = getSetting('email', '');
?>

    <section class="page-header">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="/">Accueil</a>
                <span>/</span>
                <span>Mentions légales</span>
            </nav>
            <h1>Mentions légales &amp; Confidentialité</h1>
        </div>
    </section>

    <section class="section">
        <div class="container legal-content">

            <h2>1. Informations légales</h2>
            <p>Le site <strong><?= e(getSetting('site_name', SITE_NAME)) ?></strong> est édité par :</p>
            <ul>
                <li><strong>Raison sociale :</strong> SCM Garnier - Cabinet Infirmier</li>
                <li><strong>Adresse :</strong> <?= e($address) ?></li>
<?php if ($phone): ?><li><strong>Téléphone :</strong> <?= e($phone) ?></li><?php endif; ?>
                <li><strong>Email :</strong> <?= e($email) ?></li>
                <li><strong>Numéro ADELI :</strong> [Numéro ADELI]</li>
                <li><strong>Hébergeur :</strong> Hostinger International Ltd, 61 Lordou Vironos Street, 6023 Larnaca, Chypre</li>
            </ul>

            <h2>2. Propriété intellectuelle</h2>
            <p>L'ensemble du contenu de ce site (textes, images, graphismes, logo, icônes) est la propriété exclusive de SCM Garnier, sauf mention contraire. Toute reproduction, distribution, modification ou utilisation de ce contenu sans autorisation préalable est interdite.</p>

            <h2 id="rgpd">3. Politique de confidentialité (RGPD)</h2>
            <p>Conformément au Règlement Général sur la Protection des Données (RGPD - Règlement UE 2016/679) et à la loi Informatique et Libertés, nous nous engageons à protéger vos données personnelles.</p>

            <h3>3.1. Responsable du traitement</h3>
            <p>Le responsable du traitement des données est SCM Garnier, joignable à l'adresse email : <?= e($email) ?>.</p>

            <h3>3.2. Données collectées</h3>
            <p>Dans le cadre de nos services, nous collectons les données suivantes :</p>
            <ul>
                <li><strong>Prise de rendez-vous :</strong> nom, prénom, email, téléphone, type de soin, adresse (si domicile), notes médicales</li>
                <li><strong>Formulaire de contact :</strong> nom, email, téléphone, message</li>
                <li><strong>Navigation :</strong> données techniques (adresse IP, type de navigateur) via les journaux serveur</li>
            </ul>

            <h3>3.3. Finalités du traitement</h3>
            <p>Vos données sont collectées uniquement pour :</p>
            <ul>
                <li>La gestion et le suivi de vos rendez-vous</li>
                <li>La réponse à vos demandes de contact</li>
                <li>L'amélioration de nos services</li>
                <li>Le respect de nos obligations légales</li>
            </ul>

            <h3>3.4. Base légale</h3>
            <p>Le traitement de vos données repose sur :</p>
            <ul>
                <li><strong>Votre consentement</strong> (article 6.1.a du RGPD) pour la prise de rendez-vous en ligne et le formulaire de contact</li>
                <li><strong>L'exécution du contrat de soins</strong> (article 6.1.b) pour la gestion des rendez-vous</li>
                <li><strong>L'obligation légale</strong> (article 6.1.c) pour la conservation des données médicales</li>
            </ul>

            <h3>3.5. Durée de conservation</h3>
            <ul>
                <li><strong>Données de rendez-vous :</strong> 5 ans après le dernier soin (obligation légale de conservation du dossier de soins infirmiers)</li>
                <li><strong>Données de contact :</strong> 3 ans après le dernier échange</li>
                <li><strong>Journaux techniques :</strong> 12 mois</li>
            </ul>

            <h3>3.6. Sécurité des données</h3>
            <p>Nous mettons en oeuvre les mesures techniques et organisationnelles suivantes :</p>
            <ul>
                <li>Chiffrement des données sensibles (AES-256)</li>
                <li>Connexion HTTPS sur l'ensemble du site</li>
                <li>Mots de passe hachés avec Argon2id</li>
                <li>Accès restreint aux données patient (authentification et permissions)</li>
                <li>Journalisation des accès (audit log)</li>
                <li>Sauvegardes régulières et chiffrées</li>
            </ul>

            <h3>3.7. Vos droits</h3>
            <p>Conformément au RGPD, vous disposez des droits suivants :</p>
            <ul>
                <li><strong>Droit d'accès</strong> (article 15) : obtenir une copie de vos données</li>
                <li><strong>Droit de rectification</strong> (article 16) : corriger vos données</li>
                <li><strong>Droit à l'effacement</strong> (article 17) : demander la suppression de vos données</li>
                <li><strong>Droit à la portabilité</strong> (article 20) : recevoir vos données dans un format lisible</li>
                <li><strong>Droit d'opposition</strong> (article 21) : vous opposer au traitement</li>
                <li><strong>Droit de retirer votre consentement</strong> à tout moment</li>
            </ul>
            <p>Pour exercer vos droits, contactez-nous à : <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
            <p>En cas de litige, vous pouvez saisir la <strong>CNIL</strong> (Commission Nationale de l'Informatique et des Libertés) : <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a></p>

            <h3>3.8. Transferts de données</h3>
            <p>Vos données ne sont en aucun cas vendues ou transmises à des tiers à des fins commerciales. Elles peuvent être communiquées aux sous-traitants techniques (hébergeur) dans le cadre strict de l'exécution de leurs missions, avec des garanties contractuelles de confidentialité.</p>

            <h2>4. Cookies</h2>
            <p>Ce site utilise uniquement des cookies strictement nécessaires au fonctionnement (session de connexion). Aucun cookie de suivi publicitaire ou analytique n'est utilisé.</p>

            <h2>5. Accessibilité</h2>
            <p>Nous nous efforçons de rendre ce site accessible à tous les utilisateurs, conformément aux recommandations RGAA (Référentiel Général d'Accessibilité pour les Administrations). Si vous rencontrez des difficultés, contactez-nous.</p>

            <p class="legal-update">Dernière mise à jour : <?= date('d/m/Y') ?></p>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
