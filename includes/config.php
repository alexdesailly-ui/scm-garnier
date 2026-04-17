<?php
/**
 * Configuration - SCM Garnier Infirmier
 * Compatible Hostinger hPanel (LiteSpeed + MySQL)
 */

// Load env.php if exists (Hostinger local config)
$env = [];
$envFile = __DIR__ . '/../env.php';
if (file_exists($envFile)) {
    $env = require $envFile;
}

// Helper to read config: env.php > environment variable > default
function conf(string $key, string $default, array $env): string {
    return $env[$key] ?? (getenv($key) ?: $default);
}

// Error handling
$debug = ($env['APP_DEBUG'] ?? false) || getenv('APP_DEBUG');
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
define('APP_DEBUG', (bool) $debug);

// Database (Hostinger format: u123456789_dbname)
define('DB_HOST', conf('DB_HOST', 'localhost', $env));
define('DB_NAME', conf('DB_NAME', 'u000000000_scm_garnier', $env));
define('DB_USER', conf('DB_USER', 'u000000000_admin', $env));
define('DB_PASS', conf('DB_PASS', '', $env));
define('DB_CHARSET', 'utf8mb4');

// Security
define('APP_SECRET', conf('APP_SECRET', bin2hex(random_bytes(32)), $env));
define('CSRF_TOKEN_LIFETIME', 3600);
define('SESSION_LIFETIME', 7200);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// Site
define('SITE_URL', conf('SITE_URL', 'https://scm-garnier-infirmier.fr', $env));
define('SITE_NAME', 'Cabinet Infirmier Garnier');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

// Encryption key for patient data
$encKey = conf('ENCRYPTION_KEY', '', $env);
if (empty($encKey) || $encKey === 'CHANGEZ_CECI_cle_base64_de_32_octets') {
    $encKey = base64_encode(random_bytes(32));
}
define('ENCRYPTION_KEY', $encKey);

/**
 * PDO connection (singleton) - compatible Hostinger MySQL
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET time_zone = '+02:00'");
    }
    return $pdo;
}
