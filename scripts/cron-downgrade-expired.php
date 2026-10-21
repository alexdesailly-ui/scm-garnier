<?php
/**
 * Backstop for P0-2 (auto-downgrade on subscription end).
 *
 * Stripe webhooks downgrade the tenant plan on customer.subscription.deleted,
 * but webhooks can be missed (Stripe retries cap at 3 days, signing secrets
 * can rotate, etc.). This script catches any subscription whose paid period
 * has ended but whose tenant.plan is still on a paid tier, and force-downgrades.
 *
 * Schedule daily via hPanel cron:
 *   php /home/u398408214/domains/scm-garnier-infirmier.fr/public_html/scripts/cron-downgrade-expired.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

use SCM\Billing\Plan;
use SCM\Core\App;
use SCM\Tenant\TenantRepository;

$app = App::instance();
$pdo = $app->db()->pdo();

$stmt = $pdo->query("
    SELECT s.tenant_id, t.plan AS tenant_plan, s.status, s.ends_at
    FROM subscriptions s
    JOIN tenants t ON t.id = s.tenant_id
    WHERE s.status IN ('cancelled', 'expired')
      AND s.ends_at IS NOT NULL
      AND s.ends_at < NOW()
      AND t.plan <> 'starter'
");

$repo = new TenantRepository($app->db());
$downgraded = 0;

foreach ($stmt->fetchAll() as $row) {
    $repo->updatePlan((int) $row['tenant_id'], Plan::STARTER);
    $downgraded++;
    fwrite(STDOUT, sprintf(
        "[%s] Downgraded tenant_id=%d (was %s, status=%s, ends_at=%s)\n",
        date('c'),
        $row['tenant_id'],
        $row['tenant_plan'],
        $row['status'],
        $row['ends_at']
    ));
}

fwrite(STDOUT, sprintf("[%s] Done. Downgraded %d tenant(s).\n", date('c'), $downgraded));
