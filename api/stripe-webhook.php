<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use SCM\Billing\Stripe\StripeClient;
use SCM\Billing\Stripe\WebhookHandler;
use SCM\Core\App;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$app = App::boot();
$webhookSecret = $app->config()->get('STRIPE_WEBHOOK_SECRET', '');

if ($webhookSecret === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Webhook secret not configured']);
    exit;
}

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = StripeClient::verifyWebhookSignature($payload, $sigHeader, $webhookSecret);
} catch (\RuntimeException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

try {
    $handler = new WebhookHandler($app->db());
    $handler->handle($event);
    echo json_encode(['received' => true]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Processing failed']);
}
