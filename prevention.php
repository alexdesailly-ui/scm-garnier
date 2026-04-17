<?php
$pageTitle = 'Prévention Santé';
require_once __DIR__ . '/includes/header.php';

// Fetch published articles
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM prevention_articles WHERE is_published = 1 ORDER BY published_at DESC");
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $articles = [];
}

$categories = [
    'general'   => ['label' => 'Tous', 'icon' => ''],
    'hygiene'   => ['label' => 'Hygiène de vie', 'icon' => '&#128166;'],
    'nutrition' => ['label' => 'Nutrition', 'icon' => '&#127822;'],
    'vaccination' => ['label' => 'Vaccination', 'icon' => '&#128137;'],
    'chronique' => ['label' => 'Maladies chroniques', 'icon' => '&#128153;'],
    'senior'    => ['label' => 'Seniors', 'icon' => '&#128116;'],
    'pediatrie' => ['label' => 'Pédiatrie', 'icon' => '&#128118;'],
];
?>

    <!-- Page Header -->
    <section class="page-header page-header-prevention">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="/">Accueil</a>
                <span>/</span>
                <span>Prévention Santé</span>
            </nav>
            <h1>Prévention &amp; Éducation Santé</h1>
            <p>Tutoriels, conseils et modules éducatifs pour prendre soin de votre santé au quotidien.</p>
        </div>
    </section>

    <!-- Educational Modules -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Modules éducatifs</span>
                <h2>Apprenez les bons gestes</h2>
            </div>

            <div class="modules-grid">
                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #dbeafe, #93c5fd)">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.5"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                    </div>
                    <h3>Lavage des mains</h3>
                    <p>Technique OMS complète pour un lavage des mains efficace en 6 étapes. Durée recommandée : 30 secondes.</p>
                    <div class="module-steps">
                        <div class="module-step">
                            <span class="step-badge">1</span>
                            <span>Mouiller les mains</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">2</span>
                            <span>Savonner paume contre paume</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">3</span>
                            <span>Frotter les entre-doigts</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">4</span>
                            <span>Frotter le dos des mains</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">5</span>
                            <span>Frotter les pouces</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">6</span>
                            <span>Rincer et sécher</span>
                        </div>
                    </div>
                </div>

                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #fef3c7, #fcd34d)">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <h3>Surveiller sa glycémie</h3>
                    <p>Guide pratique pour les patients diabétiques : quand mesurer, comment interpréter les résultats, et quand alerter.</p>
                    <div class="module-steps">
                        <div class="module-step">
                            <span class="step-badge">1</span>
                            <span>Se laver les mains</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">2</span>
                            <span>Préparer le lecteur</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">3</span>
                            <span>Piquer sur le côté du doigt</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">4</span>
                            <span>Lire et noter le résultat</span>
                        </div>
                    </div>
                </div>

                <div class="module-card">
                    <div class="module-icon" style="background: linear-gradient(135deg, #d1fae5, #6ee7b7)">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3>Prévention des chutes</h3>
                    <p>Conseils pour sécuriser votre domicile et maintenir votre équilibre, particulièrement pour les seniors.</p>
                    <div class="module-steps">
                        <div class="module-step">
                            <span class="step-badge">1</span>
                            <span>Éclairer les passages</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">2</span>
                            <span>Retirer les tapis glissants</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">3</span>
                            <span>Installer des barres d'appui</span>
                        </div>
                        <div class="module-step">
                            <span class="step-badge">4</span>
                            <span>Porter des chaussures adaptées</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Health Tips -->
    <section class="section section-light">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Conseils santé</span>
                <h2>Nos recommandations</h2>
            </div>

            <div class="tips-grid">
                <div class="tip-card">
                    <div class="tip-number">01</div>
                    <h3>Hydratation</h3>
                    <p>Buvez au minimum 1,5 litre d'eau par jour. Augmentez votre consommation en cas de chaleur, d'activité physique ou de fièvre. L'eau est essentielle au bon fonctionnement de vos reins et de votre circulation sanguine.</p>
                </div>
                <div class="tip-card">
                    <div class="tip-number">02</div>
                    <h3>Activité physique</h3>
                    <p>30 minutes d'activité modérée par jour réduisent significativement les risques cardiovasculaires. La marche, la natation ou le vélo sont d'excellentes options accessibles à tous.</p>
                </div>
                <div class="tip-card">
                    <div class="tip-number">03</div>
                    <h3>Sommeil</h3>
                    <p>Visez 7 à 9 heures de sommeil par nuit. Un sommeil réparateur renforce votre système immunitaire et améliore votre concentration. Évitez les écrans 1h avant le coucher.</p>
                </div>
                <div class="tip-card">
                    <div class="tip-number">04</div>
                    <h3>Vaccination</h3>
                    <p>Tenez à jour votre carnet de vaccination. La grippe saisonnière, le COVID-19, le tétanos et le zona sont des vaccinations importantes, surtout après 65 ans.</p>
                </div>
                <div class="tip-card">
                    <div class="tip-number">05</div>
                    <h3>Alimentation équilibrée</h3>
                    <p>Privilégiez les fruits, légumes, céréales complètes et protéines maigres. Limitez le sel, le sucre ajouté et les graisses saturées. 5 portions de fruits et légumes par jour.</p>
                </div>
                <div class="tip-card">
                    <div class="tip-number">06</div>
                    <h3>Dépistages réguliers</h3>
                    <p>Consultez régulièrement votre médecin traitant. Les dépistages (tension, glycémie, cholestérol, cancer) permettent une détection précoce et un meilleur pronostic.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <?php if (!empty($articles)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Articles</span>
                <h2>Nos derniers articles</h2>
            </div>

            <!-- Category Filter -->
            <div class="category-filter" role="tablist">
                <?php foreach ($categories as $key => $cat): ?>
                <button class="filter-btn <?= $key === 'general' ? 'active' : '' ?>" data-category="<?= $key ?>" role="tab">
                    <?= $cat['icon'] ? $cat['icon'] . ' ' : '' ?><?= $cat['label'] ?>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                <article class="article-card" data-category="<?= e($article['category']) ?>">
                    <?php if ($article['image_url']): ?>
                    <div class="article-img">
                        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <div class="article-body">
                        <span class="article-tag"><?= e($categories[$article['category']]['label'] ?? $article['category']) ?></span>
                        <h3><?= e($article['title']) ?></h3>
                        <p><?= e($article['excerpt']) ?></p>
                        <a href="/article.php?slug=<?= e($article['slug']) ?>" class="article-link">Lire la suite &rarr;</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA -->
    <section class="section section-accent">
        <div class="container text-center">
            <h2>Besoin d'un conseil personnalisé ?</h2>
            <p style="max-width:600px;margin:1rem auto 2rem">Nos infirmiers sont disponibles pour répondre à vos questions et vous accompagner dans votre parcours de santé.</p>
            <div class="hero-actions" style="justify-content:center">
                <a href="/contact.php" class="btn btn-white btn-lg">Nous contacter</a>
                <a href="/rendez-vous.php" class="btn btn-outline-white btn-lg">Prendre RDV</a>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
