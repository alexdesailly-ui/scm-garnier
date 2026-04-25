<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/autoload.php';

use SCM\Core\App;
use SCM\Migration\MigrationRunner;

echo "SCM Garnier — Database Migration\n";
echo str_repeat('─', 40) . "\n";

$app = App::boot();

try {
    $pdo = $app->db()->pdo();
    echo "[OK] Connected to MySQL\n\n";
} catch (PDOException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}

// Phase 1: Run legacy table creation (idempotent via IF NOT EXISTS)
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
        echo "  [OK] $name\n";
    } catch (PDOException $e) {
        echo "  [FAIL] $name — " . $e->getMessage() . "\n";
    }
}

// Phase 2: Run versioned migrations (tenants, tenant_id, etc.)
echo "\n--- Versioned migrations ---\n";
$runner = new MigrationRunner($app->db());
$results = $runner->run();

if (empty($results)) {
    echo "  Nothing to migrate — up to date.\n";
} else {
    foreach ($results as $r) {
        $icon = $r['status'] === 'ok' ? 'OK' : 'FAIL';
        echo "  [{$icon}] {$r['migration']}";
        if (isset($r['message'])) {
            echo " — {$r['message']}";
        }
        echo "\n";
    }
}

// Phase 3: Seed defaults if empty
$count = (int) $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
if ($count === 0) {
    echo "\n--- Seeding defaults ---\n";
    $defaults = [
        'site_name'        => 'Cabinet Infirmier Garnier',
        'site_description' => 'Cabinet infirmier à Nice - Soins à domicile et au cabinet',
        'address'          => '123 Avenue Jean Médecin, 06000 Nice',
        'phone'            => '04 93 00 00 00',
        'email'            => 'contact@cabinet-garnier.fr',
        'opening_hours'    => 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00',
        'slot_duration'    => '30',
        'max_advance_days' => '30',
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    $slotStmt = $pdo->prepare("INSERT INTO available_slots (nurse_id, day_of_week, start_time, end_time) VALUES (NULL, ?, ?, ?)");
    for ($d = 1; $d <= 5; $d++) {
        $slotStmt->execute([$d, '07:00', '19:00']);
    }
    $slotStmt->execute([6, '08:00', '12:00']);
    echo "  [OK] Default settings + time slots\n";
}

$applied = $runner->getApplied();
echo "\n" . str_repeat('─', 40) . "\n";
echo "Total versioned migrations: " . count($applied) . "\n";
echo "Done.\n";
