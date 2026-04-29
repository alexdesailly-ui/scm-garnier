<?php

declare(strict_types=1);

namespace SCM\Billing;

use SCM\Billing\Stripe\StripeClient;
use SCM\Core\App;
use SCM\Core\Database;

final class BillingService
{
    private StripeClient $stripe;
    private SubscriptionRepository $subscriptions;
    private InvoiceRepository $invoices;
    private Database $db;

    public function __construct(Database $db, StripeClient $stripe)
    {
        $this->db = $db;
        $this->stripe = $stripe;
        $this->subscriptions = new SubscriptionRepository($db);
        $this->invoices = new InvoiceRepository($db);
    }

    public static function create(): self
    {
        $app = App::instance();
        $key = $app->config()->get('STRIPE_SECRET_KEY', '');

        return new self($app->db(), new StripeClient($key));
    }

    public function getSubscription(int $tenantId): ?Subscription
    {
        return $this->subscriptions->findByTenantId($tenantId);
    }

    public function getInvoices(int $tenantId): array
    {
        return $this->invoices->findByTenantId($tenantId);
    }

    public function createCheckoutUrl(int $tenantId, string $plan): string
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ?');
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            throw new \RuntimeException('Tenant not found');
        }

        $customerId = $tenant['stripe_customer_id'] ?? '';
        if ($customerId === '') {
            $customer = $this->stripe->createCustomer(
                $tenant['email'] ?? '',
                $tenant['name'],
                ['tenant_id' => (string) $tenantId, 'tenant_slug' => $tenant['slug']],
            );
            $customerId = $customer['id'];
            $pdo->prepare('UPDATE tenants SET stripe_customer_id = ? WHERE id = ?')
                ->execute([$customerId, $tenantId]);
        }

        $priceId = Plan::priceId($plan);
        if ($priceId === '') {
            throw new \RuntimeException("No Stripe price configured for plan: {$plan}");
        }

        $baseUrl = App::instance()->config()->get('SITE_URL', '');
        $session = $this->stripe->createCheckoutSession(
            $customerId,
            $priceId,
            "{$baseUrl}/admin/billing-success.php?session_id={CHECKOUT_SESSION_ID}",
            "{$baseUrl}/admin/billing.php?cancelled=1",
            14,
        );

        return $session['url'] ?? '';
    }

    public function getPortalUrl(int $tenantId): string
    {
        $stmt = $this->db->pdo()->prepare('SELECT stripe_customer_id FROM tenants WHERE id = ?');
        $stmt->execute([$tenantId]);
        $customerId = $stmt->fetchColumn();

        if (!$customerId) {
            throw new \RuntimeException('No Stripe customer for this tenant');
        }

        $baseUrl = App::instance()->config()->get('SITE_URL', '');
        $session = $this->stripe->createPortalSession($customerId, "{$baseUrl}/admin/billing.php");

        return $session['url'] ?? '';
    }

    public function cancelSubscription(int $tenantId): void
    {
        $sub = $this->subscriptions->findByTenantId($tenantId);

        if ($sub === null || $sub->stripeSubscriptionId === '') {
            return;
        }

        $this->stripe->cancelAtPeriodEnd($sub->stripeSubscriptionId);
    }

    public function resumeSubscription(int $tenantId): void
    {
        $sub = $this->subscriptions->findByTenantId($tenantId);

        if ($sub === null || $sub->stripeSubscriptionId === '') {
            return;
        }

        $this->stripe->resumeSubscription($sub->stripeSubscriptionId);
    }

    public function changePlan(int $tenantId, string $newPlan): void
    {
        $sub = $this->subscriptions->findByTenantId($tenantId);

        if ($sub === null || $sub->stripeSubscriptionId === '') {
            return;
        }

        $newPriceId = Plan::priceId($newPlan);
        if ($newPriceId === '') {
            throw new \RuntimeException("No price for plan: {$newPlan}");
        }

        $this->stripe->changePrice($sub->stripeSubscriptionId, $newPriceId);

        $this->db->pdo()->prepare('UPDATE tenants SET plan = ? WHERE id = ?')
            ->execute([$newPlan, $tenantId]);
    }
}
