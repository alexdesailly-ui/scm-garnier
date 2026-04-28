<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalide']);
    exit;
}

try {
    $pdo = getDB();

    $pdo->exec("CREATE TABLE IF NOT EXISTS waitlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        source VARCHAR(100) DEFAULT 'demo',
        ip_address VARCHAR(45) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $stmt = $pdo->prepare("INSERT INTO waitlist (email, source, ip_address) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE created_at = NOW()");
    $stmt->execute([
        $email,
        $input['source'] ?? 'demo',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $count = (int) $pdo->query("SELECT COUNT(*) FROM waitlist")->fetchColumn();

    $adminEmail = '';
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'email'");
        $stmt->execute();
        $adminEmail = $stmt->fetchColumn() ?: '';
    } catch (\PDOException $e) {}

    if ($adminEmail) {
        @mail(
            $adminEmail,
            "CabinetFlow — Nouvelle inscription bêta (#$count)",
            "Nouvel inscrit sur la liste d'attente :\n\nEmail : $email\nDate : " . date('d/m/Y H:i') . "\nSource : demo\nIP : " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n\nTotal inscrits : $count",
            "From: noreply@scm-garnier-infirmier.fr\r\nReply-To: $email"
        );
    }

    echo json_encode(['success' => true, 'message' => 'Inscription enregistrée']);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
