<?php

declare(strict_types=1);

namespace SCM\Model;

use SCM\Core\App;
use SCM\Core\Database;

final class Setting
{
    private Database $db;
    private array $cache = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    private function tenantId(): ?int
    {
        return App::instance()->tenantId();
    }

    public function get(string $key, string $default = ''): string
    {
        $tid = $this->tenantId();
        $cacheKey = ($tid ?? 'null') . ':' . $key;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            if ($tid !== null) {
                $stmt = $this->db->pdo()->prepare(
                    "SELECT setting_value FROM settings WHERE setting_key = ? AND tenant_id = ?"
                );
                $stmt->execute([$key, $tid]);
            } else {
                $stmt = $this->db->pdo()->prepare(
                    "SELECT setting_value FROM settings WHERE setting_key = ?"
                );
                $stmt->execute([$key]);
            }
            $val = $stmt->fetchColumn();
            $this->cache[$cacheKey] = $val !== false ? $val : $default;
        } catch (\PDOException $e) {
            $this->cache[$cacheKey] = $default;
        }

        return $this->cache[$cacheKey];
    }

    public function set(string $key, string $value): bool
    {
        $tid = $this->tenantId();

        try {
            if ($tid !== null) {
                $stmt = $this->db->pdo()->prepare(
                    "INSERT INTO settings (tenant_id, setting_key, setting_value)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                );
                $result = $stmt->execute([$tid, $key, $value]);
            } else {
                $stmt = $this->db->pdo()->prepare(
                    "INSERT INTO settings (setting_key, setting_value)
                     VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                );
                $result = $stmt->execute([$key, $value]);
            }

            $cacheKey = ($tid ?? 'null') . ':' . $key;
            $this->cache[$cacheKey] = $value;
            return $result;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function all(): array
    {
        $tid = $this->tenantId();

        try {
            if ($tid !== null) {
                $stmt = $this->db->pdo()->prepare(
                    "SELECT setting_key, setting_value FROM settings WHERE tenant_id = ?"
                );
                $stmt->execute([$tid]);
            } else {
                $stmt = $this->db->pdo()->query("SELECT setting_key, setting_value FROM settings");
            }

            $settings = [];
            foreach ($stmt->fetchAll() as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;
        } catch (\PDOException $e) {
            return [];
        }
    }
}
