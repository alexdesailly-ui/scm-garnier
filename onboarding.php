<?php
require_once __DIR__ . '/includes/functions.php';

use SCM\Core\App;
use SCM\Tenant\TenantOnboarding;

startSecureSession();

$result = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRF($token)) {
        $errors[] = 'Session expirée. Rechargez la page.';
    } else {
        try {
            $onboarding = new TenantOnboarding(App::instance()->db());
            $result = $onboarding->provision([
                'slug' => $_POST['slug'] ?? '',
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'plan' => $_POST['plan'] ?? 'starter',
            ]);

            if (!$result['success']) {
                $errors = $result['errors'];
            }
        } catch (\PDOException $e) {
            $errors[] = 'Erreur de base de données. Veuillez réessayer.';
        }
    }
}

$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Créer votre cabinet — SCM Garnier SaaS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:560px;width:100%;padding:2.5rem}
.icon{text-align:center;font-size:2.5rem;margin-bottom:.5rem}
h1{text-align:center;color:#1e293b;font-size:1.5rem;margin-bottom:.25rem}
.sub{text-align:center;color:#64748b;margin-bottom:1.5rem;font-size:.95rem}
h2{font-size:1.1rem;color:#334155;margin:1.5rem 0 .5rem;padding-top:1rem;border-top:1px solid #e2e8f0}
h2:first-of-type{border-top:none;margin-top:.5rem}
label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.2rem;margin-top:.75rem;color:#334155}
label small{font-weight:400;color:#94a3b8}
input,select{width:100%;padding:.65rem .9rem;border:2px solid #e2e8f0;border-radius:8px;font-size:.95rem}
input:focus,select:focus{outline:none;border-color:#0d6e6e}
.row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.btn{display:block;width:100%;padding:.85rem;background:#0d6e6e;color:#fff;border:none;border-radius:8px;font-size:1.05rem;font-weight:600;cursor:pointer;margin-top:1.5rem}
.btn:hover{background:#0a5858}
.error{background:#fee;border:1px solid #fcc;color:#c00;padding:.6rem .9rem;border-radius:8px;margin-bottom:.75rem;font-size:.9rem}
.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:1.2rem;border-radius:12px;text-align:center}
.success h2{color:#166534;border:none;margin:0 0 .5rem;padding:0}
.success a{color:#0d6e6e;font-weight:600}
.help{font-size:.8rem;color:#94a3b8;margin-top:.2rem}
.plans{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-top:.5rem}
.plan-opt{border:2px solid #e2e8f0;border-radius:8px;padding:.75rem;text-align:center;cursor:pointer;transition:all .2s}
.plan-opt:hover,.plan-opt.selected{border-color:#0d6e6e;background:#f0fdfa}
.plan-opt input{display:none}
.plan-name{font-weight:600;font-size:.9rem;color:#1e293b}
.plan-price{font-size:.8rem;color:#64748b;margin-top:.25rem}
</style>
</head>
<body>
<div class="card">
<div class="icon">&#9764;</div>
<h1>Créer votre cabinet</h1>
<p class="sub">Lancez votre site infirmier en 30 secondes</p>

<?php if ($result && $result['success']): ?>
<div class="success">
    <h2>Cabinet créé avec succès !</h2>
    <p>Votre espace <strong><?= e($result['tenant']->name) ?></strong> est prêt.</p>
    <p style="margin:.75rem 0">
        <a href="/admin/login.php?tenant=<?= e($result['tenant']->slug) ?>" style="display:inline-block;background:#0d6e6e;color:#fff;padding:.6rem 1.5rem;border-radius:8px;text-decoration:none">
            Accéder à l'administration
        </a>
    </p>
    <p style="font-size:.85rem;color:#64748b;margin-top:1rem">
        Identifiant : <code style="background:#e2e8f0;padding:.1rem .3rem;border-radius:4px"><?= e($result['tenant']->slug) ?></code>
    </p>
</div>

<?php else: ?>
<?php foreach ($errors as $err): ?>
<div class="error"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<h2>Votre cabinet</h2>

<label>Nom du cabinet *</label>
<input type="text" name="name" required placeholder="Cabinet Infirmier Dupont" value="<?= e($_POST['name'] ?? '') ?>">

<label>Identifiant <small>(dans l'URL)</small></label>
<input type="text" name="slug" required pattern="[a-z0-9][a-z0-9-]*[a-z0-9]" minlength="3" maxlength="63" placeholder="dupont" value="<?= e($_POST['slug'] ?? '') ?>">
<p class="help">Lettres minuscules, chiffres et tirets uniquement</p>

<h2>Compte administrateur</h2>

<label>Email *</label>
<input type="email" name="email" required placeholder="admin@cabinet-dupont.fr" value="<?= e($_POST['email'] ?? '') ?>">

<label>Mot de passe * <small>(8 caractères min.)</small></label>
<input type="password" name="password" required minlength="8">

<h2>Formule</h2>
<div class="plans">
    <label class="plan-opt selected" onclick="this.parentNode.querySelectorAll('.plan-opt').forEach(p=>p.classList.remove('selected'));this.classList.add('selected')">
        <input type="radio" name="plan" value="starter" checked>
        <div class="plan-name">Starter</div>
        <div class="plan-price">Gratuit</div>
    </label>
    <label class="plan-opt" onclick="this.parentNode.querySelectorAll('.plan-opt').forEach(p=>p.classList.remove('selected'));this.classList.add('selected')">
        <input type="radio" name="plan" value="pro">
        <div class="plan-name">Pro</div>
        <div class="plan-price">29€/mois</div>
    </label>
    <label class="plan-opt" onclick="this.parentNode.querySelectorAll('.plan-opt').forEach(p=>p.classList.remove('selected'));this.classList.add('selected')">
        <input type="radio" name="plan" value="enterprise">
        <div class="plan-name">Enterprise</div>
        <div class="plan-price">Sur devis</div>
    </label>
</div>

<button type="submit" class="btn">Créer mon cabinet</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
