<?php

declare(strict_types=1);

namespace SCM\Billing;

final class Subscription
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $stripeSubscriptionId,
        public readonly string $stripeCustomerId,
        public readonly string $stripePriceId,
        public readonly string $plan,
        public readonly string $status,
        public readonly ?string $trialEndsAt,
        public readonly ?string $currentPeriodStart,
        public readonly ?string $currentPeriodEnd,
        public readonly bool $cancelAtPeriodEnd,
        public readonly ?string $cancelledAt,
        public readonly ?string $endsAt,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            tenantId: (int) $row['tenant_id'],
            stripeSubscriptionId: $row['stripe_subscription_id'] ?? '',
            stripeCustomerId: $row['stripe_customer_id'] ?? '',
            stripePriceId: $row['stripe_price_id'] ?? '',
            plan: $row['plan'],
            status: $row['status'],
            trialEndsAt: $row['trial_ends_at'],
            currentPeriodStart: $row['current_period_start'],
            currentPeriodEnd: $row['current_period_end'],
            cancelAtPeriodEnd: (bool) ($row['cancel_at_period_end'] ?? false),
            cancelledAt: $row['cancelled_at'],
            endsAt: $row['ends_at'],
        );
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trialing', 'active'], true);
    }

    public function isOnGracePeriod(): bool
    {
        if (!$this->cancelAtPeriodEnd && $this->status !== 'cancelled') {
            return false;
        }

        return $this->endsAt !== null && strtotime($this->endsAt) > time();
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->status === 'cancelled' && $this->endsAt !== null) {
            return strtotime($this->endsAt) <= time();
        }

        return false;
    }

    public function hasAccess(): bool
    {
        return $this->isActive() || $this->isPastDue() || $this->isOnGracePeriod();
    }

    public function statusLabel(): string
    {
        if ($this->isOnGracePeriod()) {
            return 'Résiliation en cours';
        }

        return match ($this->status) {
            'trialing' => 'Essai gratuit',
            'active' => 'Actif',
            'past_due' => 'Paiement en attente',
            'cancelled' => 'Résilié',
            'expired' => 'Expiré',
            default => $this->status,
        };
    }

    public function statusClass(): string
    {
        if ($this->isOnGracePeriod()) {
            return 'badge-pending';
        }

        return match ($this->status) {
            'trialing', 'active' => 'badge-confirmed',
            'past_due' => 'badge-pending',
            'cancelled', 'expired' => 'badge-cancelled',
            default => '',
        };
    }
}
