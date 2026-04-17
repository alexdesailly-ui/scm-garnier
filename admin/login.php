<?php
require_once __DIR__ . '/includes/auth.php';

if (isset($_GET['logout'])) { logoutUser(); header('Location: /admin/login.php'); exit; }
if (!empty($_SESSION['user_id'])) { header('Location: /admin/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRF($token)) { $error = 'Session expirée.'; }
    elseif (isLoginLocked($email)) { $error = 'Trop de tentatives. Réessayez dans 15 minutes.'; }
    elseif (loginUser($email, $password)) { header('Location: /admin/'); exit; }
    else { $error = 'Identifiants incorrects.'; }
}
$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Connexion - Administration</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:420px;width:100%;padding:2.5rem;margin:1rem}
.login-icon{text-align:center;font-size:2.5rem;margin-bottom:.5rem}
h1{text-align:center;font-size:1.3rem;color:#1e293b;margin-bottom:.25rem}
.subtitle{text-align:center;color:#64748b;font-size:.9rem;margin-bottom:1.5rem}
label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.25rem;margin-top:1rem}
input{width:100%;padding:.7rem 1rem;border:2px solid #e2e8f0;border-radius:8px;font-size:.95rem}
input:focus{outline:none;border-color:#0d6e6e}
.btn{display:block;width:100%;padding:.8rem;background:#0d6e6e;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;margin-top:1.5rem}
.btn:hover{background:#0a5858}
.error{background:#fee2e2;color:#991b1b;padding:.6rem 1rem;border-radius:8px;font-size:.9rem;margin-bottom:.5rem}
.back{text-align:center;margin-top:1rem;font-size:.85rem}
.back a{color:#0d6e6e}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-icon">&#9764;</div>
  <h1>Administration</h1>
  <p class="subtitle">Connectez-vous pour gérer votre cabinet</p>
  <?php if($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required autofocus>
    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" required>
    <button type="submit" class="btn">Se connecter</button>
  </form>
  <p class="back"><a href="/">Retour au site</a></p>
</div>
</body>
</html>
