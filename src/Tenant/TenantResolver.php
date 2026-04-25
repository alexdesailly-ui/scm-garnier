<?php

declare(strict_types=1);

namespace SCM\Tenant;

use SCM\Core\App;
use SCM\Core\Database;

final class TenantResolver
{
    private TenantRepository $repo;

    public function __construct(Database $db)
    {
        $this->repo = new TenantRepository($db);
    }

    public function resolve(?string $host = null): ?Tenant
    {
        $host = $host ?? ($_SERVER['HTTP_HOST'] ?? '');
        $host = strtolower(trim($host));

        // Strip port
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        // 1. Try exact domain match
        if ($host !== '') {
            $tenant = $this->repo->findByDomain($host);
            if ($tenant !== null) {
                return $tenant;
            }
        }

        // 2. Try subdomain extraction (e.g. garnier.app.example.com -> garnier)
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            $slug = $parts[0];
            $tenant = $this->repo->findBySlug($slug);
            if ($tenant !== null) {
                return $tenant;
            }
        }

        // 3. Try slug from query string (?tenant=garnier) — dev/testing convenience
        $slugParam = $_GET['tenant'] ?? '';
        if ($slugParam !== '') {
            return $this->repo->findBySlug($slugParam);
        }

        return null;
    }

    public function resolveOrDefault(?string $host = null): Tenant
    {
        $tenant = $this->resolve($host);

        if ($tenant !== null) {
            return $tenant;
        }

        // Fallback: first active tenant (single-tenant backward compat)
        $all = $this->repo->listAll();
        foreach ($all as $t) {
            if ($t->isActive) {
                return $t;
            }
        }

        throw new \RuntimeException('No active tenant found. Run migrations first.');
    }
}
