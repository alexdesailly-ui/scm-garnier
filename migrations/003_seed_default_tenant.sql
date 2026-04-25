-- Seed the default tenant (Garnier) and backfill existing rows

INSERT INTO tenants (slug, name, domain, encryption_key, plan)
VALUES ('garnier', 'Cabinet Infirmier Garnier', 'scm-garnier-infirmier.fr', '', 'pro')
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET @tid = (SELECT id FROM tenants WHERE slug = 'garnier');

UPDATE users SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE contacts SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE appointments SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE settings SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE available_slots SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE blocked_dates SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE prevention_articles SET tenant_id = @tid WHERE tenant_id IS NULL;
UPDATE audit_log SET tenant_id = @tid WHERE tenant_id IS NULL;
