<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';

$contacts = getActiveContacts();
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <span class="hero-badge">Cabinet Infirmier &middot; Nice</span>
            <h1>Des soins infirmiers<br><span class="text-accent">professionnels et humains</span></h1>
            <p class="hero-desc">Notre équipe d'infirmiers diplômés vous accompagne avec bienveillance, au cabinet ou à domicile, pour tous vos soins infirmiers à Nice et ses environs.</p>
            <div class="hero-actions">
                <a href="/rendez-vous.php" class="btn btn-primary btn-lg">Prendre rendez-vous</a>
                <?php if ($phone): ?>
                <a href="tel:<?= e(str_replace(' ', '', $phone)) ?>" class="btn btn-outline btn-lg">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    Appeler
                </a>
                <?php endif; ?>
            </div>
            <div class="hero-trust">
                <div class="trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Diplômés d'État
                </div>
                <div class="trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Conventionnés
                </div>
                <div class="trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    7j/7
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section" id="services">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Nos services</span>
                <h2>Des soins adaptés à vos besoins</h2>
                <p>Nous proposons une large gamme de soins infirmiers, réalisés avec expertise et bienveillance.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0016.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 002 8.5c0 2.3 1.5 4.05 3 5.5l7 7 7-7z"/></svg>
                    </div>
                    <h3>Soins à domicile</h3>
                    <p>Injections, pansements, perfusions, nursing... Nos infirmiers se déplacent chez vous pour vos soins quotidiens.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3>Prélèvements sanguins</h3>
                    <p>Prises de sang à jeun, bilans sanguins complets réalisés au cabinet ou à domicile avec tout le matériel nécessaire.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h3>Suivi des traitements</h3>
                    <p>Administration de traitements injectables, surveillance des anticoagulants, insulinothérapie et suivi post-opératoire.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    </div>
                    <h3>Pansements</h3>
                    <p>Pansements simples et complexes, soins d'escarres, suivi de cicatrisation et soins post-chirurgicaux.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <h3>Soins de nursing</h3>
                    <p>Toilettes médicalisées, aide à la mobilisation, alimentation entérale, accompagnement des patients dépendants.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <h3>Prévention santé</h3>
                    <p>Éducation thérapeutique, dépistage, vaccination, conseils nutritionnels et accompagnement des maladies chroniques.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking CTA -->
    <section class="section section-accent">
        <div class="container">
            <div class="cta-box">
                <div class="cta-content">
                    <h2>Réservez votre rendez-vous en ligne</h2>
                    <p>Un parcours simple et rapide inspiré des meilleures pratiques : choisissez votre soin, sélectionnez un créneau, confirmez. C'est tout.</p>
                    <div class="cta-steps">
                        <div class="cta-step">
                            <span class="step-num">1</span>
                            <span>Choisir le soin</span>
                        </div>
                        <div class="cta-step">
                            <span class="step-num">2</span>
                            <span>Sélectionner un créneau</span>
                        </div>
                        <div class="cta-step">
                            <span class="step-num">3</span>
                            <span>Confirmer</span>
                        </div>
                    </div>
                </div>
                <div class="cta-action">
                    <a href="/rendez-vous.php" class="btn btn-white btn-lg">Réserver maintenant</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <?php if (!empty($contacts)): ?>
    <section class="section" id="equipe">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Notre équipe</span>
                <h2>Des professionnels à votre écoute</h2>
                <p>Notre équipe d'infirmiers diplômés d'État est passionnée et engagée pour votre bien-être.</p>
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
                            Appeler
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

    <!-- Prevention Preview -->
    <section class="section section-light" id="prevention-preview">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Prévention santé</span>
                <h2>Prenez soin de votre santé au quotidien</h2>
                <p>Retrouvez nos conseils, tutoriels et modules éducatifs pour une meilleure santé au quotidien.</p>
            </div>
            <div class="prevention-grid">
                <div class="prevention-card">
                    <div class="prevention-img" style="background: linear-gradient(135deg, #e0f2fe, #bae6fd)">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0d6e6e" stroke-width="1.5"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0016.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 002 8.5c0 2.3 1.5 4.05 3 5.5l7 7 7-7z"/></svg>
                    </div>
                    <div class="prevention-body">
                        <span class="prevention-tag">Hygiène de vie</span>
                        <h3>Les gestes essentiels pour préserver sa santé</h3>
                        <p>Découvrez les habitudes simples à adopter au quotidien pour maintenir votre bien-être.</p>
                    </div>
                </div>
                <div class="prevention-card">
                    <div class="prevention-img" style="background: linear-gradient(135deg, #fef3c7, #fde68a)">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="prevention-body">
                        <span class="prevention-tag">Vaccination</span>
                        <h3>Tout savoir sur le calendrier vaccinal</h3>
                        <p>Informez-vous sur les vaccins recommandés et restez à jour sur vos rappels.</p>
                    </div>
                </div>
                <div class="prevention-card">
                    <div class="prevention-img" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0)">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="prevention-body">
                        <span class="prevention-tag">Maladies chroniques</span>
                        <h3>Vivre avec le diabète au quotidien</h3>
                        <p>Conseils pratiques, gestion de l'insuline et suivi glycémique pour les patients diabétiques.</p>
                    </div>
                </div>
            </div>
            <div class="section-action">
                <a href="/prevention.php" class="btn btn-primary">Tous nos conseils santé</a>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
