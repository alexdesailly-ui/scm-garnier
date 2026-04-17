<?php
/**
 * Installation script for SCM Garnier Infirmier
 * Creates database tables and default admin account
 * DELETE THIS FILE AFTER INSTALLATION
 */

require_once __DIR__ . '/includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $errors[] = "Jeton CSRF invalide.";
    }

    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
    $site_name = trim($_POST['site_name'] ?? 'Cabinet Infirmier Garnier');

    if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }
    if (strlen($admin_password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($admin_password !== $admin_password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();

            // Create tables
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    full_name VARCHAR(255) DEFAULT '',
                    role ENUM('admin', 'nurse', 'viewer') NOT NULL DEFAULT 'viewer',
                    is_active TINYINT(1) DEFAULT 1,
                    last_login DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value TEXT,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS contacts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    full_name VARCHAR(255) NOT NULL,
                    phone VARCHAR(20) NOT NULL,
                    whatsapp_number VARCHAR(20) DEFAULT '',
                    email VARCHAR(255) DEFAULT '',
                    role VARCHAR(100) DEFAULT 'Infirmier(ère)',
                    photo_url VARCHAR(500) DEFAULT '',
                    display_order INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS appointments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reference_code VARCHAR(20) NOT NULL UNIQUE,
                    patient_first_name VARCHAR(100) NOT NULL,
                    patient_last_name VARCHAR(100) NOT NULL,
                    patient_email VARCHAR(255) NOT NULL,
                    patient_phone VARCHAR(20) NOT NULL,
                    care_type VARCHAR(100) NOT NULL,
                    appointment_date DATE NOT NULL,
                    appointment_time TIME NOT NULL,
                    nurse_id INT NULL,
                    address TEXT DEFAULT '',
                    notes TEXT DEFAULT '',
                    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
                    is_home_visit TINYINT(1) DEFAULT 0,
                    consent_rgpd TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE SET NULL,
                    INDEX idx_date (appointment_date),
                    INDEX idx_status (status),
                    INDEX idx_reference (reference_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS available_slots (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nurse_id INT NULL,
                    day_of_week TINYINT NOT NULL COMMENT '0=Sunday, 6=Saturday',
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS blocked_dates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nurse_id INT NULL,
                    blocked_date DATE NOT NULL,
                    reason VARCHAR(255) DEFAULT '',
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE,
                    INDEX idx_blocked (blocked_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS prevention_articles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL UNIQUE,
                    excerpt TEXT DEFAULT '',
                    content LONGTEXT NOT NULL,
                    category VARCHAR(100) DEFAULT 'general',
                    image_url VARCHAR(500) DEFAULT '',
                    author_id INT NULL,
                    is_published TINYINT(1) DEFAULT 0,
                    published_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_slug (slug),
                    INDEX idx_published (is_published, published_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS audit_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    action VARCHAR(100) NOT NULL,
                    entity_type VARCHAR(50) NOT NULL,
                    entity_id INT NULL,
                    details TEXT DEFAULT '',
                    ip_address VARCHAR(45) DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_action (action, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Insert admin user
            $hash = password_hash($admin_password, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, 'Administrateur', 'admin')");
            $stmt->execute([$admin_email, $hash]);

            // Insert default settings
            $defaults = [
                'site_name'        => $site_name,
                'site_description' => 'Cabinet infirmier à Nice - Soins à domicile et au cabinet',
                'address'          => '123 Avenue Jean Médecin, 06000 Nice',
                'phone'            => '04 93 00 00 00',
                'email'            => $admin_email,
                'facebook_url'     => '',
                'instagram_url'    => '',
                'whatsapp_number'  => '',
                'opening_hours'    => 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00',
                'slot_duration'    => '30',
                'max_advance_days' => '30',
                'rgpd_text'        => 'Vos données personnelles sont collectées uniquement pour la gestion de vos rendez-vous et sont conservées conformément au RGPD. Vous pouvez exercer vos droits d\'accès, de rectification et de suppression en nous contactant.',
            ];
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaults as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            // Insert default available slots (Mon-Fri 7:00-19:00, Sat 8:00-12:00)
            $slotStmt = $pdo->prepare("INSERT INTO available_slots (nurse_id, day_of_week, start_time, end_time) VALUES (NULL, ?, ?, ?)");
            for ($d = 1; $d <= 5; $d++) {
                $slotStmt->execute([$d, '07:00', '19:00']);
            }
            $slotStmt->execute([6, '08:00', '12:00']);

            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Erreur base de données : " . $e->getMessage();
        }
    }
}

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - SCM Garnier Infirmier</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:500px;width:100%;padding:2.5rem}
        h1{color:#0d6e6e;font-size:1.5rem;margin-bottom:.5rem}
        p.subtitle{color:#666;margin-bottom:2rem}
        label{display:block;font-weight:600;color:#333;margin-bottom:.25rem;margin-top:1rem}
        input{width:100%;padding:.75rem 1rem;border:2px solid #e2e8f0;border-radius:8px;font-size:1rem;transition:border-color .2s}
        input:focus{outline:none;border-color:#0d6e6e}
        .btn{display:block;width:100%;padding:.85rem;background:#0d6e6e;color:#fff;border:none;border-radius:8px;font-size:1.05rem;font-weight:600;cursor:pointer;margin-top:1.5rem;transition:background .2s}
        .btn:hover{background:#0a5858}
        .error{background:#fee;border:1px solid #fcc;color:#c00;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem}
        .success{background:#efe;border:1px solid #cfc;color:#070;padding:1rem;border-radius:8px;text-align:center}
        .success a{color:#0d6e6e;font-weight:600}
        .icon{text-align:center;margin-bottom:1rem;font-size:2.5rem}
    </style>
</head>
<body>
<div class="card">
    <div class="icon">&#9764;</div>
    <h1>Installation du site</h1>
    <p class="subtitle">SCM Garnier Infirmier - Configuration initiale</p>

    <?php if ($success): ?>
        <div class="success">
            <p><strong>Installation terminée avec succès !</strong></p>
            <p style="margin-top:.5rem">Supprimez ce fichier <code>install.php</code> puis accédez au <a href="/admin/login.php">panneau d'administration</a>.</p>
        </div>
    <?php else: ?>
        <?php foreach ($errors as $err): ?>
            <div class="error"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <label for="site_name">Nom du cabinet</label>
            <input type="text" id="site_name" name="site_name" value="Cabinet Infirmier Garnier" required>

            <label for="admin_email">Email administrateur</label>
            <input type="email" id="admin_email" name="admin_email" placeholder="admin@cabinet-garnier.fr" required>

            <label for="admin_password">Mot de passe</label>
            <input type="password" id="admin_password" name="admin_password" minlength="8" required>

            <label for="admin_password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="admin_password_confirm" name="admin_password_confirm" minlength="8" required>

            <button type="submit" class="btn">Installer</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
