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

/**
 * Load persistent auto-generated secrets, creating them once if absent.
 *
 * When env.php does not provide APP_SECRET / ENCRYPTION_KEY, we must NOT
 * generate them fresh on every request — that would reset every session and
 * make previously encrypted data undecryptable. Instead we persist a pair to
 * a private, gitignored file and reuse it. If the file cannot be written
 * (e.g. read-only filesystem), we fall back to per-request values so the site
 * still runs — no worse than before.
 *
 * @return array{APP_SECRET:string,ENCRYPTION_KEY:string}
 */
function loadOrCreatePersistentSecrets(string $path): array {
    if (is_file($path)) {
        $data = @include $path;
        if (is_array($data) && !empty($data['APP_SECRET']) && !empty($data['ENCRYPTION_KEY'])) {
            return $data;
        }
    }
    $secrets = [
        'APP_SECRET'     => bin2hex(random_bytes(32)),
        'ENCRYPTION_KEY' => base64_encode(random_bytes(32)),
    ];
    $php = "<?php\n// Secrets générés automatiquement — ne pas modifier ni committer.\nreturn "
        . var_export($secrets, true) . ";\n";
    if (@file_put_contents($path, $php, LOCK_EX) !== false) {
        @chmod($path, 0600);
    }
    return $secrets;
}

// Legacy constants (kept for backward compatibility during migration)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $app->isDebug());
    define('DB_HOST', $cfg->get('DB_HOST', 'localhost'));
    define('DB_NAME', $cfg->get('DB_NAME', 'u000000000_scm_garnier'));
    define('DB_USER', $cfg->get('DB_USER', 'u000000000_admin'));
    define('DB_PASS', $cfg->get('DB_PASS', ''));
    define('DB_CHARSET', 'utf8mb4');

    define('CSRF_TOKEN_LIFETIME', 3600);
    define('SESSION_LIFETIME', 7200);
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOGIN_LOCKOUT_MINUTES', 15);

    define('SITE_URL', $cfg->get('SITE_URL', 'https://scm-garnier-infirmier.fr'));
    define('SITE_NAME', 'Cabinet Infirmier Garnier');
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
    define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

    // Secrets: prefer env.php; otherwise use stable persisted secrets rather
    // than fresh random values on every request.
    $placeholders = [
        '',
        'CHANGEZ_CECI_cle_base64_de_32_octets',
        'CHANGEZ_CECI_avec_une_chaine_aleatoire_de_64_caracteres_minimum',
    ];
    $appSecret = (string) $cfg->get('APP_SECRET', '');
    $encKey    = (string) $cfg->get('ENCRYPTION_KEY', '');
    if (in_array($appSecret, $placeholders, true) || in_array($encKey, $placeholders, true)) {
        $persisted = loadOrCreatePersistentSecrets(dirname(__DIR__) . '/env.secrets.php');
        if (in_array($appSecret, $placeholders, true)) {
            $appSecret = $persisted['APP_SECRET'];
        }
        if (in_array($encKey, $placeholders, true)) {
            $encKey = $persisted['ENCRYPTION_KEY'];
        }
    }
    define('APP_SECRET', $appSecret);
    define('ENCRYPTION_KEY', $encKey);
}

/**
 * PDO connection — delegates to SCM\Core\Database
 */
function getDB(): PDO {
    return App::instance()->db()->pdo();
}
