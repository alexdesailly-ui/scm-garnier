-- Add tenant_id to all tenant-scoped tables
-- Uses NULL default so existing rows remain valid during migration

ALTER TABLE users
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant (tenant_id),
    ADD CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE contacts
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_contacts (tenant_id),
    ADD CONSTRAINT fk_contacts_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE appointments
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_appointments (tenant_id),
    ADD CONSTRAINT fk_appointments_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE settings
    ADD COLUMN tenant_id INT NULL FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (tenant_id, setting_key),
    ADD CONSTRAINT fk_settings_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE available_slots
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_slots (tenant_id),
    ADD CONSTRAINT fk_slots_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE blocked_dates
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_blocked (tenant_id),
    ADD CONSTRAINT fk_blocked_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE prevention_articles
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_articles (tenant_id),
    ADD CONSTRAINT fk_articles_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE audit_log
    ADD COLUMN tenant_id INT NULL AFTER id,
    ADD INDEX idx_tenant_audit (tenant_id),
    ADD CONSTRAINT fk_audit_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
