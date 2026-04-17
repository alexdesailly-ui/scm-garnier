<?php
/**
 * Helper functions - SCM Garnier Infirmier
 */

require_once __DIR__ . '/config.php';

/**
 * Start a secure session
 */
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Generate CSRF token
 */
function generateCSRF(): string {
    startSecureSession();
    if (empty($_SESSION['csrf_token']) || (time() - ($_SESSION['csrf_time'] ?? 0)) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRF(string $token): bool {
    startSecureSession();
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get a site setting
 */
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        $cache[$key] = $val !== false ? $val : $default;
    } catch (PDOException $e) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}

/**
 * Get all settings
 */
function getAllSettings(): array {
    try {
        $pdo = getDB();
        $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update a setting
 */
function updateSetting(string $key, string $value): bool {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Sanitize output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generate unique appointment reference
 */
function generateReference(): string {
    return 'RDV-' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Encrypt sensitive data (patient info)
 */
function encryptData(string $data): string {
    $key = base64_decode(ENCRYPTION_KEY);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function decryptData(string $data): string {
    $key = base64_decode(ENCRYPTION_KEY);
    $raw = base64_decode($data);
    $iv = substr($raw, 0, 16);
    $encrypted = substr($raw, 16);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

/**
 * Log an audit action (RGPD compliance)
 */
function auditLog(string $action, string $entityType, ?int $entityId = null, string $details = ''): void {
    try {
        startSecureSession();
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $entityType,
            $entityId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (PDOException $e) {
        // Silent fail for audit log
    }
}

/**
 * Check rate limiting for login
 */
function isLoginLocked(string $email): bool {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log WHERE action = 'login_failed' AND details = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([$email, LOGIN_LOCKOUT_MINUTES]);
        return $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Format date in French
 */
function formatDateFR(string $date): string {
    $months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $days = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
    $ts = strtotime($date);
    return $days[date('w', $ts)] . ' ' . date('j', $ts) . ' ' . $months[date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/**
 * Format time in French (HH:MM -> HhMM)
 */
function formatTimeFR(string $time): string {
    $parts = explode(':', $time);
    return $parts[0] . 'h' . $parts[1];
}

/**
 * Get available time slots for a given date
 */
function getAvailableSlots(string $date, ?int $nurseId = null): array {
    try {
        $pdo = getDB();
        $dayOfWeek = date('w', strtotime($date));
        $duration = (int) getSetting('slot_duration', '30');

        // Get schedule for that day of week
        $sql = "SELECT start_time, end_time FROM available_slots WHERE day_of_week = ? AND is_active = 1";
        $params = [$dayOfWeek];
        if ($nurseId) {
            $sql .= " AND (nurse_id = ? OR nurse_id IS NULL)";
            $params[] = $nurseId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $schedules = $stmt->fetchAll();

        if (empty($schedules)) return [];

        // Check blocked dates
        $sql2 = "SELECT COUNT(*) FROM blocked_dates WHERE blocked_date = ?";
        $params2 = [$date];
        if ($nurseId) {
            $sql2 .= " AND (nurse_id = ? OR nurse_id IS NULL)";
            $params2[] = $nurseId;
        }
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute($params2);
        if ($stmt2->fetchColumn() > 0) return [];

        // Get already booked slots
        $sql3 = "SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status IN ('pending','confirmed')";
        $params3 = [$date];
        if ($nurseId) {
            $sql3 .= " AND nurse_id = ?";
            $params3[] = $nurseId;
        }
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute($params3);
        $booked = array_column($stmt3->fetchAll(), 'appointment_time');

        // Generate available time slots
        $slots = [];
        foreach ($schedules as $sched) {
            $start = strtotime($sched['start_time']);
            $end = strtotime($sched['end_time']);
            while ($start + ($duration * 60) <= $end) {
                $timeStr = date('H:i:s', $start);
                if (!in_array($timeStr, $booked)) {
                    // Don't show past slots for today
                    if ($date === date('Y-m-d') && $start <= time()) {
                        $start += $duration * 60;
                        continue;
                    }
                    $slots[] = date('H:i', $start);
                }
                $start += $duration * 60;
            }
        }

        sort($slots);
        return $slots;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Send JSON response (for API)
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validate phone number format (French)
 */
function isValidPhone(string $phone): bool {
    $cleaned = preg_replace('/[\s\.\-]/', '', $phone);
    return (bool) preg_match('/^(\+33|0)[1-9]\d{8}$/', $cleaned);
}

/**
 * Get active contacts (nurses)
 */
function getActiveContacts(): array {
    try {
        $pdo = getDB();
        return $pdo->query("SELECT * FROM contacts WHERE is_active = 1 ORDER BY display_order, full_name")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
