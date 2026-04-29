<?php
require_once __DIR__ . '/auth.php';
adminHeader('Abonnement expiré');
?>

<div class="card" style="max-width:600px;text-align:center;padding:3rem 2rem">
  <div style="font-size:3rem;margin-bottom:1rem">&#9888;</div>
  <h2 style="color:#991b1b;margin-bottom:.5rem">Abonnement expiré</h2>
  <p style="color:#64748b;margin-bottom:1.5rem">Votre abonnement a expiré ou n'est plus actif. Renouvelez pour retrouver l'accès à toutes vos fonctionnalités.</p>
  <a href="/admin/billing.php" class="btn btn-primary">Renouveler mon abonnement</a>
  <a href="/admin/" class="btn btn-outline" style="margin-left:.5rem">Retour</a>
</div>

<?php adminFooter(); ?>
