<?php

declare(strict_types=1);

namespace SCM\Quota;

use SCM\Core\Database;

final class UsageTracker
{
    public function __construct(private Database $db) {}

    public static function periodKey(string $metric, ?\DateTimeImmutable $now = null): string
    {
        $now = $now ?? new \DateTimeImmutable();

        if (str_ends_with($metric, '_per_month')) {
            return $now->format('Y-m');
        }
        if (str_ends_with($metric, '_per_day')) {
            return $now->format('Y-m-d');
        }

        return 'total';
    }

    public function currentUsage(int $tenantId, string $metric): int
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT count FROM tenant_usage
             WHERE tenant_id = ? AND metric = ? AND period_key = ?'
        );
        $stmt->execute([$tenantId, $metric, self::periodKey($metric)]);
        $val = $stmt->fetchColumn();

        return $val === false ? 0 : (int) $val;
    }

    public function increment(int $tenantId, string $metric, int $by = 1): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO tenant_usage (tenant_id, metric, period_key, count)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE count = count + VALUES(count)'
        );
        $stmt->execute([$tenantId, $metric, self::periodKey($metric), $by]);
    }
}
