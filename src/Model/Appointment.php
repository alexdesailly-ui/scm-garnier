<?php

declare(strict_types=1);

namespace SCM\Model;

final class Appointment extends BaseModel
{
    protected string $table = 'appointments';

    public function create(array $data): array
    {
        $ref = 'RDV-' . strtoupper(bin2hex(random_bytes(4)));
        $tid = $this->tenantId();

        $stmt = $this->db->pdo()->prepare(
            "INSERT INTO appointments
                (tenant_id, reference_code, patient_first_name, patient_last_name,
                 patient_email, patient_phone, care_type, appointment_date,
                 appointment_time, nurse_id, address, notes, is_home_visit,
                 consent_rgpd, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')"
        );

        $stmt->execute([
            $tid,
            $ref,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['care_type'],
            $data['date'],
            $data['time'] . ':00',
            $data['nurse_id'] ?? null,
            $data['address'] ?? '',
            $data['notes'] ?? '',
            $data['home_visit'] ?? 0,
            $data['consent'] ?? 0,
        ]);

        return [
            'id' => (int) $this->db->pdo()->lastInsertId(),
            'reference' => $ref,
        ];
    }

    public function updateStatus(int $id, string $status): bool
    {
        $where = $this->buildWhere(["id = ?"]);
        $params = array_merge([$status], $this->buildParams([$id]));
        $stmt = $this->db->pdo()->prepare("UPDATE {$this->table} SET status = ? {$where}");
        return $stmt->execute($params);
    }

    public function listFiltered(string $filter = 'all', int $limit = 500): array
    {
        $conditions = [];
        $params = [];

        if ($filter === 'today') {
            $conditions[] = 'a.appointment_date = CURDATE()';
        } elseif ($filter === 'pending') {
            $conditions[] = "a.status = 'pending'";
        } elseif ($filter === 'upcoming') {
            $conditions[] = "a.appointment_date >= CURDATE() AND a.status IN ('pending','confirmed')";
        }

        $where = $this->buildWhere($conditions, 'a');
        $params = $this->buildParams($params);

        $sql = "SELECT a.*, c.full_name as nurse_name
                FROM appointments a
                LEFT JOIN contacts c ON a.nurse_id = c.id
                {$where}
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recent(int $limit = 10): array
    {
        $where = $this->buildWhere([]);
        $params = $this->buildParams([]);
        $params[] = $limit;

        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int
    {
        return $this->count("status = ?", [$status]);
    }

    public function countToday(): int
    {
        return $this->count("appointment_date = CURDATE()");
    }

    public function bookedTimes(string $date, ?int $nurseId = null): array
    {
        $conditions = ["appointment_date = ?", "status IN ('pending','confirmed')"];
        $params = [$date];

        if ($nurseId !== null) {
            $conditions[] = "nurse_id = ?";
            $params[] = $nurseId;
        }

        $where = $this->buildWhere($conditions);
        $params = $this->buildParams($params);

        $stmt = $this->db->pdo()->prepare(
            "SELECT appointment_time FROM {$this->table} {$where}"
        );
        $stmt->execute($params);
        return array_column($stmt->fetchAll(), 'appointment_time');
    }
}
