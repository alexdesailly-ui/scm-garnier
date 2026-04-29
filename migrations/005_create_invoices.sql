CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    stripe_invoice_id VARCHAR(255) NOT NULL DEFAULT '',
    stripe_charge_id VARCHAR(255) NOT NULL DEFAULT '',
    amount_cents INT NOT NULL DEFAULT 0,
    tax_cents INT NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'eur',
    status ENUM('draft','open','paid','void','uncollectible') NOT NULL DEFAULT 'draft',
    invoice_pdf_url TEXT DEFAULT NULL,
    hosted_invoice_url TEXT DEFAULT NULL,
    period_start DATETIME NULL,
    period_end DATETIME NULL,
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_stripe_invoice (stripe_invoice_id),
    CONSTRAINT fk_invoices_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
