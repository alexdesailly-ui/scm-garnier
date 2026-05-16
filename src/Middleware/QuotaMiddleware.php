<?php

declare(strict_types=1);

namespace SCM\Middleware;

use SCM\Billing\Plan;
use SCM\Core\App;
use SCM\Quota\QuotaExceededException;
use SCM\Quota\UsageTracker;

final class QuotaMiddleware
{
    /**
     * Throws QuotaExceededException if creating one more of $metric would
     * exceed the current tenant's plan limit. Pass-through for unlimited (-1)
     * and for unknown metrics.
     */
    public static function enforce(string $metric): void
    {
        $app = App::instance();
        $tenant = $app->tenant();

        if ($tenant === null) {
            return;
        }

        $limit = Plan::limit($tenant->plan, $metric);

        if ($limit === null || $limit === -1) {
            return;
        }
        if ($limit === false || $limit === 0) {
            throw new QuotaExceededException($metric, 0, 0);
        }

        $current = (new UsageTracker($app->db()))->currentUsage($tenant->id, $metric);

        if ($current >= (int) $limit) {
            throw new QuotaExceededException($metric, (int) $limit, $current);
        }
    }

    public static function record(string $metric, int $by = 1): void
    {
        $app = App::instance();
        $tenant = $app->tenant();

        if ($tenant === null) {
            return;
        }

        (new UsageTracker($app->db()))->increment($tenant->id, $metric, $by);
    }
}
