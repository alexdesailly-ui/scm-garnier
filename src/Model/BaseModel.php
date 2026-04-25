<?php

declare(strict_types=1);

namespace SCM\Model;

use SCM\Core\App;
use SCM\Core\Database;

abstract class BaseModel
{
    protected Database $db;
    protected string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    protected function tenantId(): ?int
    {
        return App::instance()->tenantId();
    }

    protected function scopedWhere(string $alias = ''): string
    {
        $tid = $this->tenantId();
        if ($tid === null) {
            return '';
        }
        $col = $alias !== '' ? "{$alias}.tenant_id" : 'tenant_id';
        return "{$col} = ?";
    }

    protected function buildWhere(array $conditions, string $alias = ''): string
    {
        $scope = $this->scopedWhere($alias);
        if ($scope !== '') {
            array_unshift($conditions, $scope);
        }
        return !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    protected function buildParams(array $params): array
    {
        $tid = $this->tenantId();
        if ($tid !== null) {
            array_unshift($params, $tid);
        }
        return $params;
    }

    public function findById(int $id): ?array
    {
        $where = $this->buildWhere(["id = ?"]);
        $params = $this->buildParams([$id]);
        $stmt = $this->db->pdo()->prepare("SELECT * FROM {$this->table} {$where}");
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function count(string $extraWhere = '', array $extraParams = []): int
    {
        $conditions = $extraWhere !== '' ? [$extraWhere] : [];
        $where = $this->buildWhere($conditions);
        $params = $this->buildParams($extraParams);
        $stmt = $this->db->pdo()->prepare("SELECT COUNT(*) FROM {$this->table} {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
