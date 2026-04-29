<?php

require_once __DIR__ . '/includes/auth.php';

requireAuth();

adminHeader('Paiement confirmé');
?>

<div class="card" style="max-width:600px;text-align:center;padding:3rem 2rem">
  <div style="font-size:3rem;margin-bottom:1rem">&#10003;</div>
  <h2 style="color:#065f46;margin-bottom:.5rem">Paiement réussi !</h2>
  <p style="color:#64748b;margin-bottom:1.5rem">Votre abonnement est maintenant actif. Toutes les fonctionnalités de votre plan sont débloquées.</p>
  <a href="/admin/billing.php" class="btn btn-primary">Voir mon abonnement</a>
  <a href="/admin/" class="btn btn-outline" style="margin-left:.5rem">Tableau de bord</a>
</div>

<?php adminFooter(); ?>
