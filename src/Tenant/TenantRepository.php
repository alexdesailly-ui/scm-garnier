<?php

declare(strict_types=1);

namespace SCM\Tenant;

use SCM\Core\Database;

final class TenantRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Tenant
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM tenants WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? Tenant::fromRow($row) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM tenants WHERE slug = ? AND is_active = 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row ? Tenant::fromRow($row) : null;
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM tenants WHERE domain = ? AND is_active = 1');
        $stmt->execute([$domain]);
        $row = $stmt->fetch();

        return $row ? Tenant::fromRow($row) : null;
    }

    public function create(string $slug, string $name, string $domain = '', string $plan = 'starter'): Tenant
    {
        $encryptionKey = base64_encode(random_bytes(32));

        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO tenants (slug, name, domain, encryption_key, plan) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$slug, $name, $domain, $encryptionKey, $plan]);

        $id = (int) $this->db->pdo()->lastInsertId();

        return $this->findById($id);
    }

    public function listAll(): array
    {
        $rows = $this->db->pdo()->query('SELECT * FROM tenants ORDER BY name')->fetchAll();

        return array_map(fn(array $row) => Tenant::fromRow($row), $rows);
    }

    public function updatePlan(int $tenantId, string $plan): void
    {
        $stmt = $this->db->pdo()->prepare('UPDATE tenants SET plan = ? WHERE id = ?');
        $stmt->execute([$plan, $tenantId]);
    }
}
