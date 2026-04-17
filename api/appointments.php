<?php
require_once __DIR__ . '/../includes/functions.php';
startSecureSession();

header('Content-Type: application/json; charset=utf-8');

// GET: fetch available slots
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'slots') {
    $date = $_GET['date'] ?? '';
    $nurseId = !empty($_GET['nurse_id']) ? (int)$_GET['nurse_id'] : null;

    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonResponse(['error' => 'Date invalide'], 400);
    }

    $slots = getAvailableSlots($date, $nurseId);
    jsonResponse(['slots' => $slots]);
}

// POST: create appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action !== 'create') jsonResponse(['error' => 'Action invalide'], 400);

    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRF($token)) jsonResponse(['error' => 'Session expirée. Rechargez la page.'], 403);

    $careType  = trim($_POST['care_type'] ?? '');
    $date      = trim($_POST['appointment_date'] ?? '');
    $time      = trim($_POST['appointment_time'] ?? '');
    $firstName = trim($_POST['patient_first_name'] ?? '');
    $lastName  = trim($_POST['patient_last_name'] ?? '');
    $email     = trim($_POST['patient_email'] ?? '');
    $phone     = trim($_POST['patient_phone'] ?? '');
    $nurseId   = !empty($_POST['nurse_id']) ? (int)$_POST['nurse_id'] : null;
    $address   = trim($_POST['address'] ?? '');
    $notes     = trim($_POST['notes'] ?? '');
    $homeVisit = !empty($_POST['is_home_visit']) ? 1 : 0;
    $consent   = !empty($_POST['consent_rgpd']) ? 1 : 0;

    if (!$careType || !$date || !$time || !$firstName || !$lastName || !$email || !$phone) {
        jsonResponse(['error' => 'Champs obligatoires manquants.'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Email invalide.'], 400);
    }
    if (!$consent) {
        jsonResponse(['error' => 'Consentement RGPD requis.'], 400);
    }

    try {
        $pdo = getDB();
        $ref = generateReference();
        $stmt = $pdo->prepare("INSERT INTO appointments (reference_code, patient_first_name, patient_last_name, patient_email, patient_phone, care_type, appointment_date, appointment_time, nurse_id, address, notes, is_home_visit, consent_rgpd, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
        $stmt->execute([$ref, $firstName, $lastName, $email, $phone, $careType, $date, $time.':00', $nurseId, $address, $notes, $homeVisit, $consent]);

        auditLog('appointment_created', 'appointment', (int)$pdo->lastInsertId(), $ref);

        jsonResponse([
            'success'   => true,
            'reference' => $ref,
            'care_type' => $careType,
            'date'      => formatDateFR($date),
            'time'      => formatTimeFR($time),
            'patient'   => $firstName . ' ' . $lastName
        ]);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Erreur lors de l\'enregistrement.'], 500);
    }
}

jsonResponse(['error' => 'Méthode non autorisée'], 405);
