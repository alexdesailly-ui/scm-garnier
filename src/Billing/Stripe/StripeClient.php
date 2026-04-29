<?php

declare(strict_types=1);

namespace SCM\Billing\Stripe;

final class StripeClient
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function createCustomer(string $email, string $name, array $metadata = []): array
    {
        $params = ['email' => $email, 'name' => $name];
        foreach ($metadata as $k => $v) {
            $params["metadata[{$k}]"] = $v;
        }

        return $this->post('/customers', $params);
    }

    public function createCheckoutSession(
        string $customerId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
        ?int $trialDays = null,
    ): array {
        $params = [
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => '1',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => 'fr',
            'payment_method_types[0]' => 'card',
            'payment_method_types[1]' => 'link',
        ];

        if ($trialDays !== null && $trialDays > 0) {
            $params['subscription_data[trial_period_days]'] = (string) $trialDays;
        }

        return $this->post('/checkout/sessions', $params);
    }

    public function getSubscription(string $subscriptionId): array
    {
        return $this->get("/subscriptions/{$subscriptionId}");
    }

    public function cancelAtPeriodEnd(string $subscriptionId): array
    {
        return $this->post("/subscriptions/{$subscriptionId}", [
            'cancel_at_period_end' => 'true',
        ]);
    }

    public function cancelImmediately(string $subscriptionId): array
    {
        return $this->delete("/subscriptions/{$subscriptionId}");
    }

    public function resumeSubscription(string $subscriptionId): array
    {
        return $this->post("/subscriptions/{$subscriptionId}", [
            'cancel_at_period_end' => 'false',
        ]);
    }

    public function changePrice(string $subscriptionId, string $newPriceId): array
    {
        $sub = $this->getSubscription($subscriptionId);
        $itemId = $sub['items']['data'][0]['id'] ?? '';

        return $this->post("/subscriptions/{$subscriptionId}", [
            'items[0][id]' => $itemId,
            'items[0][price]' => $newPriceId,
            'proration_behavior' => 'always_invoice',
        ]);
    }

    public function createPortalSession(string $customerId, string $returnUrl): array
    {
        return $this->post('/billing_portal/sessions', [
            'customer' => $customerId,
            'return_url' => $returnUrl,
            'locale' => 'fr',
        ]);
    }

    public static function verifyWebhookSignature(string $payload, string $sigHeader, string $webhookSecret): array
    {
        $parts = [];
        foreach (explode(',', $sigHeader) as $item) {
            [$key, $value] = explode('=', $item, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';

        if ($timestamp === '' || $signature === '') {
            throw new \RuntimeException('Invalid signature header');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            throw new \RuntimeException('Webhook timestamp too old');
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);

        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('Signature verification failed');
        }

        $event = json_decode($payload, true);

        if ($event === null) {
            throw new \RuntimeException('Invalid JSON payload');
        }

        return $event;
    }

    private function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    private function post(string $endpoint, array $params = []): array
    {
        return $this->request('POST', $endpoint, $params);
    }

    private function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        $ch = curl_init();

        $url = self::BASE_URL . $endpoint;

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            throw new \RuntimeException("Stripe API error: {$error}");
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            $msg = $data['error']['message'] ?? "HTTP {$httpCode}";
            throw new \RuntimeException("Stripe: {$msg}");
        }

        return $data;
    }
}
