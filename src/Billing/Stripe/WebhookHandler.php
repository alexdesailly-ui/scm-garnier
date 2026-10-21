<?php

declare(strict_types=1);

namespace SCM\Billing\Stripe;

use SCM\Billing\InvoiceRepository;
use SCM\Billing\Plan;
use SCM\Billing\SubscriptionRepository;
use SCM\Core\Database;
use SCM\Tenant\TenantRepository;

final class WebhookHandler
{
    private Database $db;
    private SubscriptionRepository $subscriptions;
    private InvoiceRepository $invoices;
    private TenantRepository $tenants;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->subscriptions = new SubscriptionRepository($db);
        $this->invoices = new InvoiceRepository($db);
        $this->tenants = new TenantRepository($db);
    }

    public function handle(array $event): void
    {
        $eventId = $event['id'] ?? '';
        $type = $event['type'] ?? '';

        if ($this->isDuplicate($eventId)) {
            return;
        }

        match ($type) {
            'checkout.session.completed' => $this->onCheckoutCompleted($event),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($event),
            'invoice.paid' => $this->onInvoicePaid($event),
            'invoice.payment_failed' => $this->onInvoicePaymentFailed($event),
            default => null,
        };

        $tenantId = $this->resolveTenantId($event);
        $this->markProcessed($eventId, $type, $tenantId, json_encode($event));
    }

    private function onCheckoutCompleted(array $event): void
    {
        $session = $event['data']['object'] ?? [];
        $customerId = $session['customer'] ?? '';
        $subscriptionId = $session['subscription'] ?? '';
        $tenantSlug = $session['metadata']['tenant_slug'] ?? '';

        if ($subscriptionId === '' || $tenantSlug === '') {
            return;
        }

        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('SELECT id, plan FROM tenants WHERE slug = ?');
        $stmt->execute([$tenantSlug]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            return;
        }

        $pdo->prepare('UPDATE tenants SET stripe_customer_id = ? WHERE id = ?')
            ->execute([$customerId, $tenant['id']]);

        $this->subscriptions->create([
            'tenant_id' => $tenant['id'],
            'stripe_subscription_id' => $subscriptionId,
            'stripe_customer_id' => $customerId,
            'stripe_price_id' => $session['metadata']['price_id'] ?? '',
            'plan' => $tenant['plan'],
            'status' => 'active',
        ]);
    }

    private function onSubscriptionUpdated(array $event): void
    {
        $sub = $event['data']['object'] ?? [];
        $stripeId = $sub['id'] ?? '';

        if ($stripeId === '') {
            return;
        }

        $status = match ($sub['status'] ?? '') {
            'trialing' => 'trialing',
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'cancelled',
            'unpaid' => 'expired',
            default => null,
        };

        if ($status === null) {
            return;
        }

        $existing = $this->subscriptions->findByStripeId($stripeId);

        $data = [
            'status' => $status,
            'cancel_at_period_end' => ($sub['cancel_at_period_end'] ?? false) ? 1 : 0,
            'current_period_start' => isset($sub['current_period_start'])
                ? date('Y-m-d H:i:s', $sub['current_period_start']) : null,
            'current_period_end' => isset($sub['current_period_end'])
                ? date('Y-m-d H:i:s', $sub['current_period_end']) : null,
        ];

        if (!empty($sub['cancel_at'])) {
            $data['ends_at'] = date('Y-m-d H:i:s', $sub['cancel_at']);
        } elseif ($sub['cancel_at_period_end'] ?? false) {
            $data['ends_at'] = date('Y-m-d H:i:s', $sub['current_period_end']);
        }

        if (!empty($sub['canceled_at'])) {
            $data['cancelled_at'] = date('Y-m-d H:i:s', $sub['canceled_at']);
        }

        if (!empty($sub['trial_end'])) {
            $data['trial_ends_at'] = date('Y-m-d H:i:s', $sub['trial_end']);
        }

        $this->subscriptions->updateFromStripe($stripeId, $data);

        // If the paid period has actually ended, drop the tenant back to the
        // free plan so feature gating reflects reality. We leave grace-period
        // and past_due alone (the tenant still has access during retries).
        if ($existing !== null && in_array($status, ['cancelled', 'expired'], true)) {
            $endsAt = $data['ends_at'] ?? $existing->endsAt;
            if ($endsAt !== null && strtotime($endsAt) <= time()) {
                $this->tenants->updatePlan($existing->tenantId, Plan::STARTER);
            }
        }
    }

    private function onSubscriptionDeleted(array $event): void
    {
        $sub = $event['data']['object'] ?? [];
        $stripeId = $sub['id'] ?? '';

        if ($stripeId === '') {
            return;
        }

        $existing = $this->subscriptions->findByStripeId($stripeId);

        $this->subscriptions->updateFromStripe($stripeId, [
            'status' => 'cancelled',
            'ends_at' => date('Y-m-d H:i:s'),
        ]);

        if ($existing !== null) {
            $this->tenants->updatePlan($existing->tenantId, Plan::STARTER);
        }
    }

    private function onInvoicePaid(array $event): void
    {
        $invoice = $event['data']['object'] ?? [];
        $tenantId = $this->resolveTenantIdFromCustomer($invoice['customer'] ?? '');

        if ($tenantId === null) {
            return;
        }

        $this->invoices->upsertFromStripe($tenantId, [
            'stripe_invoice_id' => $invoice['id'] ?? '',
            'stripe_charge_id' => $invoice['charge'] ?? '',
            'amount_cents' => $invoice['amount_paid'] ?? 0,
            'tax_cents' => $invoice['tax'] ?? 0,
            'currency' => $invoice['currency'] ?? 'eur',
            'status' => 'paid',
            'invoice_pdf_url' => $invoice['invoice_pdf'] ?? null,
            'hosted_invoice_url' => $invoice['hosted_invoice_url'] ?? null,
            'period_start' => isset($invoice['period_start'])
                ? date('Y-m-d H:i:s', $invoice['period_start']) : null,
            'period_end' => isset($invoice['period_end'])
                ? date('Y-m-d H:i:s', $invoice['period_end']) : null,
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function onInvoicePaymentFailed(array $event): void
    {
        $invoice = $event['data']['object'] ?? [];
        $subscriptionId = $invoice['subscription'] ?? '';

        if ($subscriptionId !== '') {
            $this->subscriptions->updateFromStripe($subscriptionId, [
                'status' => 'past_due',
            ]);
        }
    }

    private function resolveTenantId(array $event): ?int
    {
        $obj = $event['data']['object'] ?? [];
        $customerId = $obj['customer'] ?? '';

        return $this->resolveTenantIdFromCustomer($customerId);
    }

    private function resolveTenantIdFromCustomer(string $customerId): ?int
    {
        if ($customerId === '') {
            return null;
        }

        $stmt = $this->db->pdo()->prepare('SELECT id FROM tenants WHERE stripe_customer_id = ?');
        $stmt->execute([$customerId]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    private function isDuplicate(string $eventId): bool
    {
        if ($eventId === '') {
            return false;
        }

        $stmt = $this->db->pdo()->prepare('SELECT COUNT(*) FROM payment_events WHERE stripe_event_id = ?');
        $stmt->execute([$eventId]);

        return $stmt->fetchColumn() > 0;
    }

    private function markProcessed(string $eventId, string $type, ?int $tenantId, string $payload): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT IGNORE INTO payment_events (stripe_event_id, event_type, tenant_id, payload, processed_at)
             VALUES (?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$eventId, $type, $tenantId, $payload]);
    }
}
