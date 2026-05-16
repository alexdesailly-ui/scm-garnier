-- Per-tenant usage counters for runtime quota enforcement (P0-1).
-- One row per (tenant, metric, period). Period is "YYYY-MM" for monthly
-- metrics (e.g. appointments_per_month) and "total" for cumulative ones.

CREATE TABLE IF NOT EXISTS tenant_usage (
    tenant_id INT NOT NULL,
    metric VARCHAR(64) NOT NULL,
    period_key VARCHAR(16) NOT NULL,
    count INT NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, metric, period_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
