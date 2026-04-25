<?php
/**
 * Configuration - SCM Garnier Infirmier
 * Compatible Hostinger hPanel (LiteSpeed + MySQL)
 */

require_once __DIR__ . '/../src/autoload.php';

use SCM\Core\App;

// Boot the application (loads env.php, configures error reporting)
$app = App::boot();
$cfg = $app->config();

// Legacy constants (kept for backward compatibility during migration)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $app->isDebug());
    define('DB_HOST', $cfg->get('DB_HOST', 'localhost'));
    define('DB_NAME', $cfg->get('DB_NAME', 'u000000000_scm_garnier'));
    define('DB_USER', $cfg->get('DB_USER', 'u000000000_admin'));
    define('DB_PASS', $cfg->get('DB_PASS', ''));
    define('DB_CHARSET', 'utf8mb4');

    define('APP_SECRET', $cfg->get('APP_SECRET', bin2hex(random_bytes(32))));
    define('CSRF_TOKEN_LIFETIME', 3600);
    define('SESSION_LIFETIME', 7200);
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOGIN_LOCKOUT_MINUTES', 15);

    define('SITE_URL', $cfg->get('SITE_URL', 'https://scm-garnier-infirmier.fr'));
    define('SITE_NAME', 'Cabinet Infirmier Garnier');
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
    define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

    $encKey = $cfg->get('ENCRYPTION_KEY', '');
    if (empty($encKey) || $encKey === 'CHANGEZ_CECI_cle_base64_de_32_octets') {
        $encKey = base64_encode(random_bytes(32));
    }
    define('ENCRYPTION_KEY', $encKey);
}

/**
 * PDO connection — delegates to SCM\Core\Database
 */
function getDB(): PDO {
    return App::instance()->db()->pdo();
}
