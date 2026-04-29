<?php

declare(strict_types=1);

namespace SCM\Billing;

use SCM\Core\Database;

final class InvoiceRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function upsertFromStripe(int $tenantId, array $d): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO invoices
                (tenant_id, stripe_invoice_id, stripe_charge_id, amount_cents, tax_cents,
                 currency, status, invoice_pdf_url, hosted_invoice_url,
                 period_start, period_end, paid_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                paid_at = VALUES(paid_at),
                invoice_pdf_url = VALUES(invoice_pdf_url),
                hosted_invoice_url = VALUES(hosted_invoice_url)'
        );

        $stmt->execute([
            $tenantId,
            $d['stripe_invoice_id'] ?? '',
            $d['stripe_charge_id'] ?? '',
            $d['amount_cents'] ?? 0,
            $d['tax_cents'] ?? 0,
            $d['currency'] ?? 'eur',
            $d['status'] ?? 'draft',
            $d['invoice_pdf_url'] ?? null,
            $d['hosted_invoice_url'] ?? null,
            $d['period_start'] ?? null,
            $d['period_end'] ?? null,
            $d['paid_at'] ?? null,
        ]);
    }

    public function findByTenantId(int $tenantId, int $limit = 20): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM invoices WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->execute([$tenantId, $limit]);

        return $stmt->fetchAll();
    }
}
