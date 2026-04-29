<?php
require_once __DIR__ . '/../../includes/functions.php';

use SCM\Core\App;
use SCM\Middleware\SubscriptionMiddleware;
use SCM\Middleware\TenantMiddleware;

startSecureSession();
TenantMiddleware::handle();
SubscriptionMiddleware::handle();

function requireAuth(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireAuth();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo 'Accès refusé.';
        exit;
    }
}

function loginUser(string $email, string $password): bool {
    if (isLoginLocked($email)) return false;

    try {
        $pdo = getDB();
        $tid = App::instance()->tenantId();

        if ($tid !== null) {
            $stmt = $pdo->prepare("SELECT id, email, password_hash, full_name, role, is_active FROM users WHERE email = ? AND tenant_id = ?");
            $stmt->execute([$email, $tid]);
        } else {
            $stmt = $pdo->prepare("SELECT id, email, password_hash, full_name, role, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        $user = $stmt->fetch();

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            auditLog('login_failed', 'user', null, $email);
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['tenant_id'] = $tid;

        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        auditLog('login_success', 'user', $user['id']);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function logoutUser(): void {
    auditLog('logout', 'user', $_SESSION['user_id'] ?? null);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function adminHeader(string $title): void {
    requireAuth();
    $userName = e($_SESSION['user_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title) ?> - Administration</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,sans-serif;background:#f1f5f9;color:#1e293b;display:flex;min-height:100vh}
.sidebar{width:240px;background:#1e293b;color:#94a3b8;padding:1.5rem 0;position:fixed;height:100vh;overflow-y:auto}
.sidebar-logo{padding:0 1.5rem 1.5rem;border-bottom:1px solid #334155;display:flex;align-items:center;gap:.5rem;color:#fff;font-weight:700}
.sidebar-nav{padding:1rem 0}
.sidebar-nav a{display:flex;align-items:center;gap:.75rem;padding:.7rem 1.5rem;color:#94a3b8;font-size:.9rem;transition:all .2s}
.sidebar-nav a:hover,.sidebar-nav a.active{background:#334155;color:#fff}
.sidebar-nav a svg{width:18px;height:18px;flex-shrink:0}
.main{margin-left:240px;flex:1;padding:2rem}
.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
.top-bar h1{font-size:1.5rem}
.top-bar-right{display:flex;align-items:center;gap:1rem;font-size:.9rem}
.top-bar-right a{color:#0d6e6e;font-weight:500}
.card{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 8px rgba(0,0,0,.06);margin-bottom:1.5rem}
.card h2{font-size:1.1rem;margin-bottom:1rem}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem}
.stat-card{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 8px rgba(0,0,0,.06);text-align:center}
.stat-num{font-size:2rem;font-weight:700;color:#0d6e6e}
.stat-label{font-size:.85rem;color:#64748b;margin-top:.25rem}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.9rem}
th{font-weight:600;color:#64748b;font-size:.8rem;text-transform:uppercase}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:12px;font-size:.75rem;font-weight:600}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-confirmed{background:#d1fae5;color:#065f46}
.badge-completed{background:#dbeafe;color:#1e40af}
.badge-cancelled{background:#fee2e2;color:#991b1b}
.admin-form label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.25rem;margin-top:.75rem}
.admin-form input,.admin-form select,.admin-form textarea{width:100%;padding:.6rem .8rem;border:2px solid #e2e8f0;border-radius:8px;font-size:.9rem;font-family:inherit}
.admin-form input:focus,.admin-form select:focus,.admin-form textarea:focus{outline:none;border-color:#0d6e6e}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;border-radius:8px;font-weight:600;font-size:.9rem;border:none;cursor:pointer;transition:all .2s}
.btn-primary{background:#0d6e6e;color:#fff}.btn-primary:hover{background:#0a5858}
.btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
.btn-outline{background:transparent;color:#0d6e6e;border:2px solid #0d6e6e}.btn-outline:hover{background:#0d6e6e;color:#fff}
.btn-sm{padding:.35rem .75rem;font-size:.8rem}
.actions{display:flex;gap:.5rem}
.alert{padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem}
.alert-success{background:#d1fae5;color:#065f46}
.alert-error{background:#fee2e2;color:#991b1b}
@media(max-width:768px){.sidebar{display:none}.main{margin-left:0}.stats-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><span style="font-size:1.3rem">&#9764;</span> Administration</div>
  <nav class="sidebar-nav">
    <a href="/admin/" class="<?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg> Tableau de bord</a>
    <a href="/admin/appointments.php" class="<?= basename($_SERVER['PHP_SELF'])==='appointments.php'?'active':'' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg> Rendez-vous</a>
    <a href="/admin/contacts.php" class="<?= basename($_SERVER['PHP_SELF'])==='contacts.php'?'active':'' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Contacts / Équipe</a>
    <a href="/admin/settings.php" class="<?= basename($_SERVER['PHP_SELF'])==='settings.php'?'active':'' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg> Paramètres</a>
    <a href="/admin/billing.php" class="<?= basename($_SERVER['PHP_SELF'])==='billing.php'?'active':'' ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Abonnement</a>
    <a href="/admin/login.php?logout=1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Déconnexion</a>
  </nav>
</aside>
<div class="main">
<div class="top-bar"><h1><?= e($title) ?></h1><div class="top-bar-right"><span><?= $userName ?></span> <a href="/" target="_blank">Voir le site</a></div></div>
<?php include __DIR__ . '/billing-banner.php'; ?>
<?php }

function adminFooter(): void { ?>
</div></body></html>
<?php }
