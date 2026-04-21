<?php
/**
 * Database migration script - SCM Garnier Infirmier
 * Run: php scripts/migrate.php
 * Safe to re-run (uses IF NOT EXISTS)
 */

require_once __DIR__ . '/../includes/config.php';

echo "=== SCM Garnier - Migration de la base de données ===\n\n";

try {
    $pdo = getDB();
    echo "[OK] Connexion MySQL réussie (" . DB_HOST . "/" . DB_NAME . ")\n";
} catch (PDOException $e) {
    echo "[ERREUR] Connexion impossible : " . $e->getMessage() . "\n";
    exit(1);
}

$tables = [
    'users' => "
        CREATE TABLE IF NOT EXISTS users (
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

    'settings' => "
        CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'contacts' => "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'appointments' => "
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

    'available_slots' => "
        CREATE TABLE IF NOT EXISTS available_slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nurse_id INT NULL,
            day_of_week TINYINT NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'blocked_dates' => "
        CREATE TABLE IF NOT EXISTS blocked_dates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nurse_id INT NULL,
            blocked_date DATE NOT NULL,
            reason VARCHAR(255) DEFAULT '',
            FOREIGN KEY (nurse_id) REFERENCES contacts(id) ON DELETE CASCADE,
            INDEX idx_blocked (blocked_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'prevention_articles' => "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'audit_log' => "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK] Table '$name' prête\n";
    } catch (PDOException $e) {
        echo "[ERREUR] Table '$name' : " . $e->getMessage() . "\n";
    }
}

// Insert default settings if empty
$count = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
if ($count == 0) {
    echo "\n--- Insertion des paramètres par défaut ---\n";
    $defaults = [
        'site_name'        => 'Cabinet Infirmier Garnier',
        'site_description' => 'Cabinet infirmier à Nice - Soins à domicile et au cabinet',
        'address'          => '123 Avenue Jean Médecin, 06000 Nice',
        'phone'            => '04 93 00 00 00',
        'email'            => 'contact@cabinet-garnier.fr',
        'facebook_url'     => '',
        'instagram_url'    => '',
        'whatsapp_number'  => '',
        'opening_hours'    => 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00',
        'slot_duration'    => '30',
        'max_advance_days' => '30',
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $key => $val) {
        $stmt->execute([$key, $val]);
    }
    echo "[OK] Paramètres par défaut insérés\n";
}

// Insert default slots if empty
$slotCount = $pdo->query("SELECT COUNT(*) FROM available_slots")->fetchColumn();
if ($slotCount == 0) {
    echo "\n--- Insertion des créneaux par défaut ---\n";
    $stmt = $pdo->prepare("INSERT INTO available_slots (nurse_id, day_of_week, start_time, end_time) VALUES (NULL, ?, ?, ?)");
    for ($d = 1; $d <= 5; $d++) $stmt->execute([$d, '07:00', '19:00']);
    $stmt->execute([6, '08:00', '12:00']);
    echo "[OK] Créneaux Lun-Ven 7h-19h + Sam 8h-12h\n";
}

echo "\n=== Migration terminée avec succès ===\n";
