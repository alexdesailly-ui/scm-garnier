<?php

require_once __DIR__ . '/includes/auth.php';

use SCM\Billing\BillingService;
use SCM\Billing\Plan;
use SCM\Core\App;
use SCM\Middleware\SubscriptionMiddleware;

requireAuth();
SubscriptionMiddleware::handle();

$app = App::instance();
$tenant = $app->tenant();
$tenantId = $app->tenantId();

if ($tenant === null || $tenantId === null) {
    header('Location: /admin/');
    exit;
}

$currentPlan = $tenant->plan;
$billing = BillingService::create();
$subscription = $billing->getSubscription($tenantId);
$invoices = $billing->getInvoices($tenantId);
$csrf = generateCSRF();

adminHeader('Abonnement');
?>

<?php if (isset($_GET['cancelled'])): ?>
<div class="alert alert-error">Paiement annulé. Vous pouvez réessayer à tout moment.</div>
<?php endif; ?>

<div class="billing-grid">

<div class="card">
  <h2>Plan actuel</h2>
  <div class="plan-current">
    <span class="plan-name"><?= e(Plan::label($currentPlan)) ?></span>
    <span class="plan-price"><?= e(Plan::price($currentPlan)) ?></span>
  </div>

  <?php if ($subscription): ?>
  <div class="subscription-info">
    <div class="sub-row">
      <span class="sub-label">Statut</span>
      <span class="badge <?= e($subscription->statusClass()) ?>"><?= e($subscription->statusLabel()) ?></span>
    </div>
    <?php if ($subscription->currentPeriodEnd): ?>
    <div class="sub-row">
      <span class="sub-label"><?= $subscription->cancelAtPeriodEnd ? 'Accès jusqu\'au' : 'Prochaine facturation' ?></span>
      <span><?= e(date('d/m/Y', strtotime($subscription->currentPeriodEnd))) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($subscription->trialEndsAt && $subscription->status === 'trialing'): ?>
    <div class="sub-row">
      <span class="sub-label">Fin de l'essai</span>
      <span><?= e(date('d/m/Y', strtotime($subscription->trialEndsAt))) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <div class="sub-actions">
    <?php if ($subscription->isActive() && !$subscription->cancelAtPeriodEnd): ?>
      <button class="btn btn-outline btn-sm" onclick="billingAction('portal')">Gérer le paiement</button>
      <button class="btn btn-danger btn-sm" onclick="confirmCancel()">Résilier</button>
    <?php elseif ($subscription->isOnGracePeriod()): ?>
      <button class="btn btn-primary btn-sm" onclick="billingAction('resume')">Réactiver l'abonnement</button>
    <?php elseif ($subscription->isExpired() || $subscription->isCancelled()): ?>
      <button class="btn btn-primary btn-sm" onclick="billingAction('create-checkout', '<?= e($currentPlan) ?>')">Se réabonner</button>
    <?php endif; ?>
    <?php if ($subscription->isPastDue()): ?>
      <button class="btn btn-primary btn-sm" onclick="billingAction('portal')">Mettre à jour le paiement</button>
    <?php endif; ?>
  </div>

  <?php elseif (Plan::requiresPayment($currentPlan)): ?>
  <div class="alert alert-error" style="margin-top:1rem">Aucun abonnement actif. Souscrivez pour activer toutes les fonctionnalités.</div>
  <button class="btn btn-primary" onclick="billingAction('create-checkout', '<?= e($currentPlan) ?>')">Souscrire maintenant</button>

  <?php else: ?>
  <p style="margin-top:1rem;color:#64748b">Plan gratuit — aucun paiement requis.</p>
  <?php endif; ?>
</div>

<?php if ($currentPlan !== Plan::ENTERPRISE): ?>
<div class="card">
  <h2>Changer de plan</h2>
  <div class="plans-compare">
    <?php foreach ([Plan::STARTER, Plan::PRO, Plan::ENTERPRISE] as $p): ?>
    <?php if ($p === $currentPlan) continue; ?>
    <div class="plan-option <?= $p === Plan::PRO ? 'plan-highlighted' : '' ?>">
      <h3><?= e(Plan::label($p)) ?></h3>
      <div class="plan-option-price"><?= e(Plan::price($p)) ?></div>
      <ul>
        <?php if ($p === Plan::STARTER): ?>
          <li>50 rendez-vous / mois</li><li>2 praticiens</li><li>Support standard</li>
        <?php elseif ($p === Plan::PRO): ?>
          <li>Rendez-vous illimités</li><li>Praticiens illimités</li><li>Articles prévention</li><li>Domaine personnalisé</li><li>WhatsApp</li><li>Support prioritaire</li>
        <?php else: ?>
          <li>Tout Pro +</li><li>Accès API</li><li>Support dédié</li><li>Configuration sur mesure</li>
        <?php endif; ?>
      </ul>
      <?php if ($p === Plan::ENTERPRISE): ?>
        <a href="mailto:contact@cabinetflow.fr" class="btn btn-outline btn-sm">Nous contacter</a>
      <?php elseif ($p === Plan::STARTER): ?>
        <button class="btn btn-outline btn-sm" disabled>Plan actuel non modifiable en ligne</button>
      <?php elseif ($subscription && $subscription->isActive()): ?>
        <button class="btn btn-primary btn-sm" onclick="billingAction('change-plan', '<?= e($p) ?>')">Passer à <?= e(Plan::label($p)) ?></button>
      <?php else: ?>
        <button class="btn btn-primary btn-sm" onclick="billingAction('create-checkout', '<?= e($p) ?>')">Choisir <?= e(Plan::label($p)) ?></button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($invoices)): ?>
<div class="card">
  <h2>Historique de facturation</h2>
  <table>
    <thead><tr><th>Date</th><th>Montant</th><th>Statut</th><th>Facture</th></tr></thead>
    <tbody>
    <?php foreach ($invoices as $inv): ?>
    <tr>
      <td><?= e($inv['paid_at'] ? date('d/m/Y', strtotime($inv['paid_at'])) : date('d/m/Y', strtotime($inv['created_at']))) ?></td>
      <td><?= number_format(($inv['amount_cents'] ?? 0) / 100, 2, ',', ' ') ?> €</td>
      <td><span class="badge <?= $inv['status'] === 'paid' ? 'badge-confirmed' : 'badge-pending' ?>"><?= $inv['status'] === 'paid' ? 'Payée' : 'En attente' ?></span></td>
      <td>
        <?php if (!empty($inv['invoice_pdf_url'])): ?>
        <a href="<?= e($inv['invoice_pdf_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">PDF</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

</div>

<style>
.billing-grid{max-width:900px}
.plan-current{display:flex;justify-content:space-between;align-items:baseline;margin:.5rem 0}
.plan-name{font-size:1.5rem;font-weight:700;color:#0d6e6e}
.plan-price{font-size:1.2rem;font-weight:600;color:#64748b}
.subscription-info{border-top:1px solid #e2e8f0;padding-top:1rem;margin-top:1rem}
.sub-row{display:flex;justify-content:space-between;padding:.4rem 0;font-size:.9rem}
.sub-label{color:#64748b}
.sub-actions{margin-top:1.25rem;display:flex;gap:.5rem;flex-wrap:wrap}
.plans-compare{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
.plan-option{border:2px solid #e2e8f0;border-radius:12px;padding:1.25rem;text-align:center}
.plan-highlighted{border-color:#0d6e6e;background:#f0fdfa}
.plan-option h3{font-size:1.1rem;margin-bottom:.25rem}
.plan-option-price{font-size:1.3rem;font-weight:700;color:#0d6e6e;margin-bottom:.75rem}
.plan-option ul{list-style:none;text-align:left;margin-bottom:1rem;font-size:.85rem;color:#475569}
.plan-option li{padding:.2rem 0;padding-left:1.2rem;position:relative}
.plan-option li::before{content:"✓";position:absolute;left:0;color:#0d6e6e;font-weight:700}
</style>

<script>
function billingAction(action, plan) {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('csrf_token', '<?= $csrf ?>');
    if (plan) fd.append('plan', plan);

    fetch('/api/billing.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            if (data.url) { window.location.href = data.url; return; }
            if (data.success) { window.location.reload(); }
        })
        .catch(() => alert('Erreur réseau, veuillez réessayer.'));
}

function confirmCancel() {
    if (confirm('Êtes-vous sûr de vouloir résilier ? Vous conserverez l\'accès jusqu\'à la fin de votre période de facturation.')) {
        billingAction('cancel');
    }
}
</script>

<?php adminFooter(); ?>
