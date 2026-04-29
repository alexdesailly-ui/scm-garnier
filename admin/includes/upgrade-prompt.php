<?php
require_once __DIR__ . '/auth.php';
adminHeader('Fonctionnalité indisponible');
?>

<div class="card" style="max-width:600px;text-align:center;padding:3rem 2rem">
  <div style="font-size:3rem;margin-bottom:1rem">&#128274;</div>
  <h2 style="margin-bottom:.5rem">Fonctionnalité réservée</h2>
  <p style="color:#64748b;margin-bottom:1.5rem">Cette fonctionnalité n'est pas incluse dans votre plan actuel. Passez à un plan supérieur pour y accéder.</p>
  <a href="/admin/billing.php" class="btn btn-primary">Voir les plans</a>
  <a href="/admin/" class="btn btn-outline" style="margin-left:.5rem">Retour</a>
</div>

<?php adminFooter(); ?>
