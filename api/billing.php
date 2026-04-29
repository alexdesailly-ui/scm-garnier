<?php

require_once __DIR__ . '/../admin/includes/auth.php';

use SCM\Billing\BillingService;
use SCM\Billing\Plan;
use SCM\Core\App;

requireAuth();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$token = $_POST['csrf_token'] ?? '';

if (!verifyCSRF($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Session expirée']);
    exit;
}

$app = App::instance();
$tenantId = $app->tenantId();

if ($tenantId === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Tenant not found']);
    exit;
}

try {
    $billing = BillingService::create();

    switch ($action) {
        case 'create-checkout':
            $plan = $_POST['plan'] ?? 'pro';
            if (!in_array($plan, ['pro', 'enterprise'], true)) {
                echo json_encode(['error' => 'Plan invalide']);
                exit;
            }
            $url = $billing->createCheckoutUrl($tenantId, $plan);
            echo json_encode(['url' => $url]);
            break;

        case 'portal':
            $url = $billing->getPortalUrl($tenantId);
            echo json_encode(['url' => $url]);
            break;

        case 'cancel':
            $billing->cancelSubscription($tenantId);
            auditLog('subscription_cancelled', 'subscription', $tenantId);
            echo json_encode(['success' => true, 'message' => 'Abonnement résilié en fin de période']);
            break;

        case 'resume':
            $billing->resumeSubscription($tenantId);
            auditLog('subscription_resumed', 'subscription', $tenantId);
            echo json_encode(['success' => true, 'message' => 'Abonnement réactivé']);
            break;

        case 'change-plan':
            $newPlan = $_POST['plan'] ?? '';
            if (!in_array($newPlan, ['pro', 'enterprise'], true)) {
                echo json_encode(['error' => 'Plan invalide']);
                exit;
            }
            $billing->changePlan($tenantId, $newPlan);
            auditLog('plan_changed', 'subscription', $tenantId, $newPlan);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action invalide']);
    }
} catch (\RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
