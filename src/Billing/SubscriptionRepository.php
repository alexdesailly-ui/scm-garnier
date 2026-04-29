<?php

declare(strict_types=1);

namespace SCM\Billing;

use SCM\Core\Database;

final class SubscriptionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findByTenantId(int $tenantId): ?Subscription
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM subscriptions WHERE tenant_id = ?');
        $stmt->execute([$tenantId]);
        $row = $stmt->fetch();

        return $row ? Subscription::fromRow($row) : null;
    }

    public function findByStripeId(string $stripeSubscriptionId): ?Subscription
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM subscriptions WHERE stripe_subscription_id = ?');
        $stmt->execute([$stripeSubscriptionId]);
        $row = $stmt->fetch();

        return $row ? Subscription::fromRow($row) : null;
    }

    public function create(array $data): Subscription
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO subscriptions
                (tenant_id, stripe_subscription_id, stripe_customer_id, stripe_price_id,
                 plan, status, trial_ends_at, current_period_start, current_period_end)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                stripe_subscription_id = VALUES(stripe_subscription_id),
                stripe_customer_id = VALUES(stripe_customer_id),
                stripe_price_id = VALUES(stripe_price_id),
                plan = VALUES(plan),
                status = VALUES(status),
                trial_ends_at = VALUES(trial_ends_at),
                current_period_start = VALUES(current_period_start),
                current_period_end = VALUES(current_period_end)'
        );

        $stmt->execute([
            $data['tenant_id'],
            $data['stripe_subscription_id'] ?? '',
            $data['stripe_customer_id'] ?? '',
            $data['stripe_price_id'] ?? '',
            $data['plan'] ?? 'pro',
            $data['status'] ?? 'active',
            $data['trial_ends_at'] ?? null,
            $data['current_period_start'] ?? null,
            $data['current_period_end'] ?? null,
        ]);

        return $this->findByTenantId((int) $data['tenant_id']);
    }

    public function updateFromStripe(string $stripeSubscriptionId, array $data): void
    {
        $fields = [];
        $params = [];

        $allowed = ['status', 'plan', 'stripe_price_id', 'current_period_start',
                     'current_period_end', 'cancel_at_period_end', 'cancelled_at',
                     'ends_at', 'trial_ends_at'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return;
        }

        $params[] = $stripeSubscriptionId;
        $sql = 'UPDATE subscriptions SET ' . implode(', ', $fields) . ' WHERE stripe_subscription_id = ?';
        $this->db->pdo()->prepare($sql)->execute($params);
    }

    public function updateStatus(int $tenantId, string $status, array $extra = []): void
    {
        $extra['status'] = $status;
        $fields = [];
        $params = [];

        foreach ($extra as $k => $v) {
            $fields[] = "{$k} = ?";
            $params[] = $v;
        }

        $params[] = $tenantId;
        $sql = 'UPDATE subscriptions SET ' . implode(', ', $fields) . ' WHERE tenant_id = ?';
        $this->db->pdo()->prepare($sql)->execute($params);
    }
}
