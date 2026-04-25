<?php

declare(strict_types=1);

namespace SCM\Model;

final class Contact extends BaseModel
{
    protected string $table = 'contacts';

    public function active(): array
    {
        $where = $this->buildWhere(["is_active = 1"]);
        $params = $this->buildParams([]);
        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM {$this->table} {$where} ORDER BY display_order, full_name"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $tid = $this->tenantId();
        $stmt = $this->db->pdo()->prepare(
            "INSERT INTO contacts (tenant_id, full_name, phone, whatsapp_number, email, role, display_order)
             VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $tid,
            $data['full_name'],
            $data['phone'],
            $data['whatsapp_number'] ?? '',
            $data['email'] ?? '',
            $data['role'] ?? 'Infirmier(ère)',
            $data['display_order'] ?? 0,
        ]);
        return (int) $this->db->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $where = $this->buildWhere(["id = ?"]);
        $params = [
            $data['full_name'],
            $data['phone'],
            $data['whatsapp_number'] ?? '',
            $data['email'] ?? '',
            $data['role'] ?? 'Infirmier(ère)',
            $data['display_order'] ?? 0,
        ];
        $params = array_merge($params, $this->buildParams([$id]));

        $stmt = $this->db->pdo()->prepare(
            "UPDATE {$this->table}
             SET full_name=?, phone=?, whatsapp_number=?, email=?, role=?, display_order=?
             {$where}"
        );
        return $stmt->execute($params);
    }

    public function deactivate(int $id): bool
    {
        $where = $this->buildWhere(["id = ?"]);
        $params = $this->buildParams([$id]);
        $stmt = $this->db->pdo()->prepare("UPDATE {$this->table} SET is_active = 0 {$where}");
        return $stmt->execute($params);
    }

    public function countActive(): int
    {
        return $this->count("is_active = 1");
    }
}
