<?php

/**
 * Composants de vue réutilisables (rendu HTML).
 *
 * Extrait le balisage dupliqué entre plusieurs pages afin qu'il n'existe
 * qu'à un seul endroit. Comportement identique au balisage d'origine.
 */

require_once __DIR__ . '/functions.php';

/**
 * Affiche une carte « infirmier » (sections équipe / contacts).
 *
 * @param array $nurse           Ligne contact : full_name, role, phone, photo_url, whatsapp_number.
 * @param bool  $showPhoneNumber true = le bouton affiche le numéro ; false = libellé « Appeler ».
 */
function renderNurseCard(array $nurse, bool $showPhoneNumber = false): void
{
    $phoneLabel = $showPhoneNumber ? e($nurse['phone']) : 'Appeler';
    ?>
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
                <?= $phoneLabel ?>
            </a>
            <?php if ($nurse['whatsapp_number']): ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $nurse['whatsapp_number'])) ?>" target="_blank" rel="noopener" class="team-btn team-btn-wa">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                WhatsApp
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
