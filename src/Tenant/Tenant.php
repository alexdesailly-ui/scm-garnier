<?php

declare(strict_types=1);

namespace SCM\Tenant;

final class Tenant
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $domain,
        public readonly string $encryptionKey,
        public readonly string $plan,
        public readonly bool $isActive,
        public readonly string $createdAt,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            slug: $row['slug'],
            name: $row['name'],
            domain: $row['domain'] ?? '',
            encryptionKey: $row['encryption_key'] ?? '',
            plan: $row['plan'] ?? 'starter',
            isActive: (bool) ($row['is_active'] ?? true),
            createdAt: $row['created_at'] ?? '',
        );
    }
}
