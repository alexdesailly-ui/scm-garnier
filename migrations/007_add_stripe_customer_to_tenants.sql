ALTER TABLE tenants
    ADD COLUMN stripe_customer_id VARCHAR(255) NOT NULL DEFAULT '' AFTER plan,
    ADD INDEX idx_stripe_customer (stripe_customer_id);
