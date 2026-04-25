<?php

declare(strict_types=1);

namespace SCM\Model;

final class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $where = $this->buildWhere(["email = ?"]);
        $params = $this->buildParams([$email]);
        $stmt = $this->db->pdo()->prepare(
            "SELECT id, email, password_hash, full_name, role, is_active
             FROM {$this->table} {$where}"
        );
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $stmt = $this->db->pdo()->prepare(
            "INSERT INTO users (tenant_id, email, password_hash, full_name, role)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tid,
            $data['email'],
            password_hash($data['password'], PASSWORD_ARGON2ID),
            $data['full_name'] ?? 'Utilisateur',
            $data['role'] ?? 'viewer',
        ]);
        return (int) $this->db->pdo()->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->pdo()->prepare(
            "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?"
        )->execute([$id]);
    }

    public function verifyPassword(array $user, string $password): bool
    {
        if (!$user['is_active']) {
            return false;
        }
        return password_verify($password, $user['password_hash']);
    }
}
