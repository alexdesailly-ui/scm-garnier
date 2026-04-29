<?php

use SCM\Core\App;
use SCM\Billing\Plan;

$__app = App::instance();
$__sub = $__app->subscription();
$__tenant = $__app->tenant();

if ($__tenant === null) return;

if ($__app->isSubscriptionRestricted() && Plan::requiresPayment($__tenant->plan)): ?>
<div class="alert alert-error" style="display:flex;justify-content:space-between;align-items:center">
  <span>Votre abonnement a expiré. Certaines fonctionnalités sont restreintes.</span>
  <a href="/admin/billing.php" class="btn btn-primary btn-sm">Renouveler</a>
</div>
<?php elseif ($__sub && $__sub->isPastDue()): ?>
<div class="alert" style="background:#fef3c7;color:#92400e;display:flex;justify-content:space-between;align-items:center">
  <span>Un paiement est en attente. Mettez à jour vos informations de paiement.</span>
  <a href="/admin/billing.php" class="btn btn-outline btn-sm">Mettre à jour</a>
</div>
<?php elseif ($__sub && $__sub->isOnGracePeriod()): ?>
<div class="alert" style="background:#fef3c7;color:#92400e;display:flex;justify-content:space-between;align-items:center">
  <span>Votre abonnement prend fin le <?= date('d/m/Y', strtotime($__sub->endsAt ?? $__sub->currentPeriodEnd ?? '')) ?>.</span>
  <a href="/admin/billing.php" class="btn btn-outline btn-sm">Réactiver</a>
</div>
<?php endif; ?>
