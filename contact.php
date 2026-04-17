<?php
$pageTitle = 'Contact';
require_once __DIR__ . '/includes/header.php';

$csrf = generateCSRF();
$contacts = getActiveContacts();
$address = getSetting('address', '123 Avenue Jean Médecin, 06000 Nice');
$email = getSetting('email', '');
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="/">Accueil</a>
                <span>/</span>
                <span>Contact</span>
            </nav>
            <h1>Nous contacter</h1>
            <p>Une question ? Besoin d'un rendez-vous ? N'hésitez pas à nous joindre par le moyen de votre choix.</p>
        </div>
    </section>

    <!-- Contact Info -->
    <section class="section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-card">
                        <div class="contact-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <h3>Adresse</h3>
                            <p><?= e($address) ?></p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        </div>
                        <div>
                            <h3>Téléphone</h3>
                            <p><a href="tel:<?= e(str_replace(' ', '', $phone)) ?>"><?= e($phone) ?></a></p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <h3>Email</h3>
                            <p><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <h3>Horaires</h3>
                            <p><?= nl2br(e(str_replace(' | ', "\n", getSetting('opening_hours', '')))) ?></p>
                        </div>
                    </div>

                    <?php if ($wa = getSetting('whatsapp_number')): ?>
                    <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wa)) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg" style="margin-top:1rem">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        Nous écrire sur WhatsApp
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-wrap">
                    <h2>Envoyez-nous un message</h2>
                    <form id="contact-form" class="contact-form" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="contact-name">Nom complet *</label>
                                <input type="text" id="contact-name" name="name" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-email">Email *</label>
                                <input type="email" id="contact-email" name="email" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-phone">Téléphone</label>
                            <input type="tel" id="contact-phone" name="phone" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-subject">Sujet *</label>
                            <select id="contact-subject" name="subject" class="form-select" required>
                                <option value="">Choisir un sujet</option>
                                <option value="rdv">Demande de rendez-vous</option>
                                <option value="info">Demande d'information</option>
                                <option value="devis">Demande de devis</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-message">Message *</label>
                            <textarea id="contact-message" name="message" class="form-input" rows="5" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label consent-label">
                                <input type="checkbox" name="consent" value="1" required>
                                <span>J'accepte que mes données soient utilisées pour traiter ma demande.</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Envoyer</button>
                        <div id="contact-status" class="hidden"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Team / WhatsApp Contacts -->
    <?php if (!empty($contacts)): ?>
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">L'équipe</span>
                <h2>Contactez directement nos infirmiers</h2>
                <p>Vous pouvez joindre chaque membre de l'équipe par téléphone ou WhatsApp.</p>
            </div>
            <div class="team-grid">
                <?php foreach ($contacts as $nurse): ?>
                <div class="team-card">
                    <div class="team-avatar">
                        <?php if ($nurse['photo_url']): ?>
                            <img src="<?= e($nurse['photo_url']) ?>" alt="<?= e($nurse['full_name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?= e(mb_substr($nurse['full_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <h3><?= e($nurse['full_name']) ?></h3>
                    <p class="team-role"><?= e($nurse['role']) ?></p>
                    <div class="team-contact">
                        <a href="tel:<?= e(str_replace(' ', '', $nurse['phone'])) ?>" class="team-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            <?= e($nurse['phone']) ?>
                        </a>
                        <?php if ($nurse['whatsapp_number']): ?>
                        <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $nurse['whatsapp_number'])) ?>" target="_blank" rel="noopener" class="team-btn team-btn-wa">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                            WhatsApp
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
