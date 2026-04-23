<?php
$pageTitle = 'Prendre rendez-vous';
require_once __DIR__ . '/includes/header.php';

$csrf = generateCSRF();
$contacts = getActiveContacts();
$careTypes = [
    'Prélèvement sanguin',
    'Injection',
    'Pansement',
    'Perfusion',
    'Soins de nursing',
    'Vaccination',
    'Suivi diabète',
    'Retrait de fils / agrafes',
    'Sondage urinaire',
    'Autre soin'
];
$maxDays = (int) getSetting('max_advance_days', '30');
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="/">Accueil</a>
                <span>/</span>
                <span>Rendez-vous</span>
            </nav>
            <h1>Prendre rendez-vous</h1>
            <p>Réservez votre créneau en quelques étapes simples. Recevez une confirmation immédiate.</p>
        </div>
    </section>

    <!-- Booking System -->
    <section class="section">
        <div class="container">
            <!-- Progress Steps -->
            <div class="booking-progress">
                <div class="progress-step active" data-step="1">
                    <div class="progress-circle">1</div>
                    <span>Type de soin</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="2">
                    <div class="progress-circle">2</div>
                    <span>Date & heure</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="3">
                    <div class="progress-circle">3</div>
                    <span>Vos informations</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="4">
                    <div class="progress-circle">4</div>
                    <span>Confirmation</span>
                </div>
            </div>

            <form id="booking-form" class="booking-form" method="POST" action="/api/appointments.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="create">

                <!-- Step 1: Care Type -->
                <div class="booking-step" id="step-1">
                    <h2>Quel soin souhaitez-vous ?</h2>
                    <p class="step-desc">Sélectionnez le type de soin dont vous avez besoin.</p>
                    <div class="care-grid">
                        <?php foreach ($careTypes as $i => $type): ?>
                        <label class="care-option">
                            <input type="radio" name="care_type" value="<?= e($type) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                            <span class="care-label"><?= e($type) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group" style="margin-top:1.5rem">
                        <label class="form-label">
                            <input type="checkbox" name="is_home_visit" value="1" id="home-visit">
                            Soins à domicile (une infirmière se déplace chez vous)
                        </label>
                    </div>

                    <?php if (!empty($contacts)): ?>
                    <div class="form-group">
                        <label class="form-label" for="nurse-select">Infirmier(ère) préféré(e) <small>(optionnel)</small></label>
                        <select id="nurse-select" name="nurse_id" class="form-select">
                            <option value="">Pas de préférence</option>
                            <?php foreach ($contacts as $nurse): ?>
                            <option value="<?= $nurse['id'] ?>"><?= e($nurse['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="booking-nav">
                        <div></div>
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">Continuer</button>
                    </div>
                </div>

                <!-- Step 2: Date & Time -->
                <div class="booking-step hidden" id="step-2">
                    <h2>Choisissez votre créneau</h2>
                    <p class="step-desc">Sélectionnez une date puis un horaire disponible.</p>

                    <div class="datetime-grid">
                        <div class="date-picker-wrap">
                            <label class="form-label" for="appointment-date">Date du rendez-vous</label>
                            <input type="date" id="appointment-date" name="appointment_date"
                                   class="form-input"
                                   min="<?= date('Y-m-d') ?>"
                                   max="<?= date('Y-m-d', strtotime("+{$maxDays} days")) ?>"
                                   value="<?= date('Y-m-d') ?>"
                                   required>
                        </div>
                        <div class="time-picker-wrap">
                            <label class="form-label">Horaire disponible</label>
                            <div id="time-slots" class="time-slots">
                                <p class="slots-placeholder">Sélectionnez d'abord une date</p>
                            </div>
                            <input type="hidden" id="appointment-time" name="appointment_time" required>
                        </div>
                    </div>

                    <div class="booking-nav">
                        <button type="button" class="btn btn-outline" onclick="prevStep(1)">Retour</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)" id="btn-step3" disabled>Continuer</button>
                    </div>
                </div>

                <!-- Step 3: Patient Info -->
                <div class="booking-step hidden" id="step-3">
                    <h2>Vos informations</h2>
                    <p class="step-desc">Ces informations sont nécessaires pour confirmer votre rendez-vous.</p>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="patient-lastname">Nom *</label>
                            <input type="text" id="patient-lastname" name="patient_last_name" class="form-input" required autocomplete="family-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="patient-firstname">Prénom *</label>
                            <input type="text" id="patient-firstname" name="patient_first_name" class="form-input" required autocomplete="given-name">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="patient-email">Email *</label>
                            <input type="email" id="patient-email" name="patient_email" class="form-input" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="patient-phone">Téléphone *</label>
                            <input type="tel" id="patient-phone" name="patient_phone" class="form-input" required autocomplete="tel" placeholder="06 12 34 56 78">
                        </div>
                    </div>

                    <div class="form-group" id="address-group" style="display:none">
                        <label class="form-label" for="patient-address">Adresse du domicile *</label>
                        <textarea id="patient-address" name="address" class="form-input" rows="2" placeholder="Numéro, rue, code postal, ville"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="patient-notes">Informations complémentaires <small>(optionnel)</small></label>
                        <textarea id="patient-notes" name="notes" class="form-input" rows="3" placeholder="Ordonnance, précisions sur le soin, accès au domicile..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label consent-label">
                            <input type="checkbox" name="consent_rgpd" value="1" id="consent-rgpd" required>
                            <span>J'accepte que mes données personnelles soient traitées pour la gestion de mon rendez-vous, conformément à la <a href="/mentions-legales.php#rgpd" target="_blank">politique de confidentialité</a>. *</span>
                        </label>
                    </div>

                    <div class="booking-nav">
                        <button type="button" class="btn btn-outline" onclick="prevStep(2)">Retour</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(4)" id="btn-step4">Confirmer le rendez-vous</button>
                    </div>
                </div>

                <!-- Step 4: Confirmation -->
                <div class="booking-step hidden" id="step-4">
                    <div class="confirmation-box">
                        <div class="confirmation-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#0d6e6e" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <h2>Rendez-vous enregistré !</h2>
                        <p>Votre demande a bien été prise en compte. Vous recevrez une confirmation par email.</p>
                        <div id="confirmation-details" class="confirmation-details"></div>
                        <div class="confirmation-actions">
                            <a href="/" class="btn btn-outline">Retour à l'accueil</a>
                            <a href="/rendez-vous.php" class="btn btn-primary">Nouveau rendez-vous</a>
                        </div>
                    </div>
                </div>

                <!-- Error display -->
                <div id="booking-error" class="alert alert-error hidden"></div>
            </form>
        </div>
    </section>

    <!-- Info Section -->
    <section class="section section-light">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <h3>Données sécurisées</h3>
                    <p>Vos informations sont chiffrées et protégées conformément au RGPD.</p>
                </div>
                <div class="info-card">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <h3>Créneaux en temps réel</h3>
                    <p>Les disponibilités sont mises à jour automatiquement.</p>
                </div>
                <div class="info-card">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    <h3>Confirmation rapide</h3>
                    <p>Recevez une confirmation par email et/ou SMS après votre réservation.</p>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
