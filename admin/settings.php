<?php
require_once __DIR__ . '/includes/auth.php';
requireRole('admin');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $fields = ['site_name','site_description','address','phone','email','facebook_url','instagram_url','whatsapp_number','opening_hours','slot_duration','max_advance_days'];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) updateSetting($f, trim($_POST[$f]));
    }
    auditLog('settings_updated', 'settings');
    $msg = 'Paramètres enregistrés avec succès.';
}

$s = getAllSettings();
$csrf = generateCSRF();
adminHeader('Paramètres');
?>
<?php if($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<form method="POST" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<div class="card">
<h2>Informations du cabinet</h2>
<label>Nom du cabinet</label>
<input type="text" name="site_name" value="<?= e($s['site_name']??'') ?>">
<label>Description</label>
<textarea name="site_description" rows="2"><?= e($s['site_description']??'') ?></textarea>
<label>Adresse</label>
<input type="text" name="address" value="<?= e($s['address']??'') ?>">
<label>Téléphone</label>
<input type="tel" name="phone" value="<?= e($s['phone']??'') ?>">
<label>Email</label>
<input type="email" name="email" value="<?= e($s['email']??'') ?>">
<label>Horaires d'ouverture</label>
<input type="text" name="opening_hours" value="<?= e($s['opening_hours']??'') ?>" placeholder="Lun-Ven : 7h-19h | Sam : 8h-12h">
</div>

<div class="card">
<h2>Réseaux sociaux</h2>
<label>URL Facebook</label>
<input type="url" name="facebook_url" value="<?= e($s['facebook_url']??'') ?>" placeholder="https://facebook.com/...">
<label>URL Instagram</label>
<input type="url" name="instagram_url" value="<?= e($s['instagram_url']??'') ?>" placeholder="https://instagram.com/...">
<label>Numéro WhatsApp (international)</label>
<input type="tel" name="whatsapp_number" value="<?= e($s['whatsapp_number']??'') ?>" placeholder="+33612345678">
</div>

<div class="card">
<h2>Paramètres de rendez-vous</h2>
<label>Durée d'un créneau (minutes)</label>
<input type="number" name="slot_duration" value="<?= e($s['slot_duration']??'30') ?>" min="10" max="120">
<label>Jours de réservation à l'avance</label>
<input type="number" name="max_advance_days" value="<?= e($s['max_advance_days']??'30') ?>" min="1" max="365">
</div>

<button type="submit" class="btn btn-primary" style="margin-top:1rem">Enregistrer les modifications</button>
</form>
<?php adminFooter(); ?>
