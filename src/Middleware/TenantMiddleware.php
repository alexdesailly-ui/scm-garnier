<?php

declare(strict_types=1);

namespace SCM\Middleware;

use SCM\Core\App;
use SCM\Tenant\TenantResolver;

final class TenantMiddleware
{
    public static function handle(): void
    {
        $app = App::instance();

        // Skip if tenant already set (e.g. in tests)
        if ($app->tenant() !== null) {
            return;
        }

        // Skip tenant resolution if DB isn't configured yet (setup flow)
        try {
            $resolver = new TenantResolver($app->db());
            $tenant = $resolver->resolveOrDefault();
            $app->setTenant($tenant);
        } catch (\PDOException|\RuntimeException $e) {
            // DB not available or no tenants — single-tenant fallback
        }
    }
}
