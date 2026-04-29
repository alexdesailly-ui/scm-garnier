<?php

declare(strict_types=1);

namespace SCM\Middleware;

use SCM\Billing\Plan;
use SCM\Billing\SubscriptionRepository;
use SCM\Core\App;

final class SubscriptionMiddleware
{
    public static function handle(): void
    {
        $app = App::instance();
        $tenant = $app->tenant();

        if ($tenant === null) {
            return;
        }

        if (!Plan::requiresPayment($tenant->plan)) {
            return;
        }

        try {
            $repo = new SubscriptionRepository($app->db());
            $sub = $repo->findByTenantId($tenant->id);

            if ($sub === null || !$sub->hasAccess()) {
                $app->setSubscriptionRestricted(true);
            } else {
                $app->setSubscription($sub);
            }
        } catch (\PDOException $e) {
            // DB not ready — don't block
        }
    }

    public static function requireFeature(string $feature): void
    {
        $app = App::instance();
        $tenant = $app->tenant();

        if ($tenant === null) {
            return;
        }

        if (!Plan::canAccess($tenant->plan, $feature)) {
            http_response_code(403);
            include dirname(__DIR__, 2) . '/admin/includes/upgrade-prompt.php';
            exit;
        }

        if (Plan::requiresPayment($tenant->plan) && $app->isSubscriptionRestricted()) {
            http_response_code(402);
            include dirname(__DIR__, 2) . '/admin/includes/subscription-expired.php';
            exit;
        }
    }
}
