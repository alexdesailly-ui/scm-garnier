-- Enforce tenant_id NOT NULL at the DB layer (P0-3).
-- Backstop against any application code path that forgets to set the tenant
-- scope on writes. Idempotent: migration 003 already backfills the default
-- tenant, this re-runs as a safety net before the constraint switch.

SET @default_tenant_id = (SELECT id FROM tenants ORDER BY id LIMIT 1);

UPDATE users               SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE contacts            SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE appointments        SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE available_slots     SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE blocked_dates       SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE prevention_articles SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;
UPDATE audit_log           SET tenant_id = @default_tenant_id WHERE tenant_id IS NULL;

ALTER TABLE users               MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE contacts            MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE appointments        MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE available_slots     MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE blocked_dates       MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE prevention_articles MODIFY COLUMN tenant_id INT NOT NULL;
ALTER TABLE audit_log           MODIFY COLUMN tenant_id INT NOT NULL;
