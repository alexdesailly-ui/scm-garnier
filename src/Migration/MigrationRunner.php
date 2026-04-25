<?php

declare(strict_types=1);

namespace SCM\Migration;

use SCM\Core\Database;

final class MigrationRunner
{
    private Database $db;
    private string $migrationsDir;

    public function __construct(Database $db, ?string $migrationsDir = null)
    {
        $this->db = $db;
        $this->migrationsDir = $migrationsDir ?? dirname(__DIR__, 2) . '/migrations';
    }

    public function run(): array
    {
        $this->ensureMigrationsTable();
        $applied = $this->getApplied();
        $pending = $this->getPending($applied);
        $results = [];

        foreach ($pending as $file) {
            $name = basename($file, '.sql');
            $sql = file_get_contents($file);

            try {
                $this->db->pdo()->exec($sql);
                $this->markApplied($name);
                $results[] = ['migration' => $name, 'status' => 'ok'];
            } catch (\PDOException $e) {
                $results[] = ['migration' => $name, 'status' => 'error', 'message' => $e->getMessage()];
                break;
            }
        }

        return $results;
    }

    public function getApplied(): array
    {
        $stmt = $this->db->pdo()->query('SELECT migration_name FROM migrations ORDER BY applied_at');

        return array_column($stmt->fetchAll(), 'migration_name');
    }

    private function getPending(array $applied): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }

        $files = glob($this->migrationsDir . '/*.sql');
        sort($files);

        return array_filter($files, function (string $file) use ($applied) {
            return !in_array(basename($file, '.sql'), $applied, true);
        });
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function markApplied(string $name): void
    {
        $stmt = $this->db->pdo()->prepare('INSERT INTO migrations (migration_name) VALUES (?)');
        $stmt->execute([$name]);
    }
}
