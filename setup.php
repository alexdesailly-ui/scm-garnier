<?php
/**
 * SCM Garnier Infirmier - Setup complet (première installation)
 *
 * 1. Configure env.php (identifiants MySQL)
 * 2. Crée les tables
 * 3. Crée le compte admin
 *
 * SUPPRIMER CE FICHIER APRÈS INSTALLATION
 */

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If env.php already exists and DB works, redirect to install.php
if (file_exists(__DIR__ . '/env.php')) {
    try {
        $env = require __DIR__ . '/env.php';
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'], $env['DB_NAME']);
        new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
        // DB works, check if users table exists and has data
        header('Location: /install.php');
        exit;
    } catch (Exception $e) {
        // env.php exists but DB doesn't work, continue setup
    }
}

$step = $_POST['step'] ?? '1';
$errors = [];
$success = false;

// Step 2: Test DB + create env.php + migrate + create admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '2') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = "Session expirée. Rechargez la page.";
    } else {
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPass = $_POST['admin_password'] ?? '';
        $adminPassConfirm = $_POST['admin_password_confirm'] ?? '';

        if (empty($dbName)) $errors[] = "Nom de la base requis.";
        if (empty($dbUser)) $errors[] = "Utilisateur MySQL requis.";
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Email admin invalide.";
        if (strlen($adminPass) < 8) $errors[] = "Mot de passe admin : 8 caractères minimum.";
        if ($adminPass !== $adminPassConfirm) $errors[] = "Les mots de passe ne correspondent pas.";

        if (empty($errors)) {
            // Test MySQL connection
            try {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                $errors[] = "Connexion MySQL échouée : " . $e->getMessage();
            }
        }

        if (empty($errors)) {
            // Generate security keys
            $appSecret = bin2hex(random_bytes(32));
            $encryptionKey = base64_encode(random_bytes(32));

            // Write env.php
            $envContent = "<?php\nreturn [\n"
                . "    'DB_HOST'        => " . var_export($dbHost, true) . ",\n"
                . "    'DB_NAME'        => " . var_export($dbName, true) . ",\n"
                . "    'DB_USER'        => " . var_export($dbUser, true) . ",\n"
                . "    'DB_PASS'        => " . var_export($dbPass, true) . ",\n"
                . "    'SITE_URL'       => 'https://scm-garnier-infirmier.fr',\n"
                . "    'APP_SECRET'     => '$appSecret',\n"
                . "    'ENCRYPTION_KEY' => '$encryptionKey',\n"
                . "    'APP_DEBUG'      => false,\n"
                . "];\n";

            $written = file_put_contents(__DIR__ . '/env.php', $envContent);
            if ($written === false) {
                $errors[] = "Impossible d'écrire env.php. Vérifiez les permissions du dossier.";
            }
        }

        if (empty($errors)) {
            // Run migration - create all tables
            $tables = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    full_name VARCHAR(255) DEFAULT '',
                    role ENUM('admin','nurse','viewer') NOT NULL DEFAULT 'viewer',
                    is_active TINYINT(1) DEFAULT 1,
                    last_login DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value TEXT,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS contacts (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS appointments (
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
                    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
                    is_home_visit TINYINT(1) DEFAULT 0,
                    consent_rgpd TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE SET NULL,
                    INDEX idx_date (appointment_date),
                    INDEX idx_status (status),
                    INDEX idx_reference (reference_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS available_slots (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nurse_id INT NULL,
                    day_of_week TINYINT NOT NULL,
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS blocked_dates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nurse_id INT NULL,
                    blocked_date DATE NOT NULL,
                    reason VARCHAR(255) DEFAULT '',
                    FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE,
                    INDEX idx_blocked (blocked_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS prevention_articles (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS audit_log (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];

            foreach ($tables as $sql) {
                try { $pdo->exec($sql); } catch (PDOException $e) {
                    $errors[] = "Erreur table : " . $e->getMessage();
                }
            }
        }

        if (empty($errors)) {
            // Create admin user
            $hash = password_hash($adminPass, PASSWORD_ARGON2ID);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, 'Administrateur', 'admin')");
                $stmt->execute([$adminEmail, $hash]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = "Cet email admin existe déjà.";
                } else {
                    $errors[] = "Erreur création admin : " . $e->getMessage();
                }
            }
        }

        if (empty($errors)) {
            // Insert default settings
            $defaults = [
                'site_name'        => 'Cabinet Infirmier Garnier',
                'site_description' => 'Cabinet infirmier à Nice - Soins à domicile et au cabinet',
                'address'          => '123 Avenue Jean Médecin, 06000 Nice',
                'phone'            => '',
                'email'            => $adminEmail,
                'facebook_url'     => '',
                'instagram_url'    => '',
                'whatsapp_number'  => '',
                'opening_hours'    => 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00',
                'slot_duration'    => '30',
                'max_advance_days' => '30',
            ];
            $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);

            // Insert default slots
            $slotStmt = $pdo->prepare("INSERT INTO available_slots (nurse_id, day_of_week, start_time, end_time) VALUES (NULL, ?, ?, ?)");
            for ($d = 1; $d <= 5; $d++) $slotStmt->execute([$d, '07:00', '19:00']);
            $slotStmt->execute([6, '08:00', '12:00']);

            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Installation - SCM Garnier Infirmier</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:560px;width:100%;padding:2.5rem}
h1{color:#0d6e6e;font-size:1.5rem;margin-bottom:.25rem}
.sub{color:#666;margin-bottom:1.5rem;font-size:.95rem}
.icon{text-align:center;font-size:2.5rem;margin-bottom:.5rem}
h2{font-size:1.1rem;color:#334155;margin:1.5rem 0 .5rem;padding-top:1rem;border-top:1px solid #e2e8f0}
h2:first-of-type{border-top:none;margin-top:.5rem}
label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.2rem;margin-top:.75rem;color:#334155}
label small{font-weight:400;color:#94a3b8}
input{width:100%;padding:.65rem .9rem;border:2px solid #e2e8f0;border-radius:8px;font-size:.95rem}
input:focus{outline:none;border-color:#0d6e6e}
.row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.btn{display:block;width:100%;padding:.85rem;background:#0d6e6e;color:#fff;border:none;border-radius:8px;font-size:1.05rem;font-weight:600;cursor:pointer;margin-top:1.5rem}
.btn:hover{background:#0a5858}
.error{background:#fee;border:1px solid #fcc;color:#c00;padding:.6rem .9rem;border-radius:8px;margin-bottom:.75rem;font-size:.9rem}
.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:1.2rem;border-radius:12px;text-align:center}
.success h2{color:#166534;border:none;margin:0 0 .5rem;padding:0}
.success a{color:#0d6e6e;font-weight:600}
.success code{background:#e2e8f0;padding:.15rem .4rem;border-radius:4px;font-size:.85rem}
.warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:.75rem;border-radius:8px;margin-top:1rem;font-size:.85rem}
.help{font-size:.8rem;color:#94a3b8;margin-top:.2rem}
</style>
</head>
<body>
<div class="card">
<div class="icon">&#9764;</div>
<h1>Installation du cabinet</h1>
<p class="sub">SCM Garnier Infirmier - Configuration en une étape</p>

<?php if ($success): ?>
<div class="success">
    <h2>Installation terminée !</h2>
    <p>Votre site est prêt. Connectez-vous à l'administration :</p>
    <p style="margin:.75rem 0"><a href="/admin/login.php" style="display:inline-block;background:#0d6e6e;color:#fff;padding:.6rem 1.5rem;border-radius:8px;text-decoration:none">Accéder à l'admin</a></p>
    <p style="margin-top:1rem;font-size:.85rem;color:#64748b">Email : <code><?= htmlspecialchars($adminEmail) ?></code></p>
</div>
<div class="warn">
    <strong>Important :</strong> Supprimez les fichiers <code>setup.php</code> et <code>install.php</code> via le Gestionnaire de fichiers hPanel pour sécuriser votre site.
</div>

<?php else: ?>
<?php foreach ($errors as $err): ?>
<div class="error"><?= htmlspecialchars($err) ?></div>
<?php endforeach; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="step" value="2">

<h2>Base de données MySQL</h2>
<p style="font-size:.85rem;color:#64748b">hPanel > Bases de données > MySQL Databases</p>

<label>Serveur <small>(ne pas changer)</small></label>
<input type="text" name="db_host" value="localhost" readonly>

<div class="row">
<div>
    <label>Nom de la base</label>
    <input type="text" name="db_name" value="u398408214_scm_garnier" required placeholder="u398408214_scm_garnier">
    <p class="help">Créez-la d'abord dans hPanel</p>
</div>
<div>
    <label>Utilisateur MySQL</label>
    <input type="text" name="db_user" value="u398408214_admin" required placeholder="u398408214_admin">
</div>
</div>

<label>Mot de passe MySQL</label>
<input type="password" name="db_pass" required placeholder="Mot de passe créé dans hPanel">

<h2>Compte administrateur</h2>

<label>Email administrateur</label>
<input type="email" name="admin_email" required placeholder="admin@scm-garnier-infirmier.fr">

<div class="row">
<div>
    <label>Mot de passe</label>
    <input type="password" name="admin_password" minlength="8" required>
    <p class="help">8 caractères minimum</p>
</div>
<div>
    <label>Confirmer</label>
    <input type="password" name="admin_password_confirm" minlength="8" required>
</div>
</div>

<button type="submit" class="btn">Installer le site</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
