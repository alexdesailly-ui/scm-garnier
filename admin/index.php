<?php
require_once __DIR__ . '/includes/auth.php';
adminHeader('Tableau de bord');

try {
    $pdo = getDB();
    $totalRdv = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
    $pendingRdv = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
    $todayRdv = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE()")->fetchColumn();
    $totalContacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_active=1")->fetchColumn();
    $recent = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 10")->fetchAll();
} catch (PDOException $e) {
    $totalRdv=$pendingRdv=$todayRdv=$totalContacts=0;$recent=[];
}

$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmé','completed'=>'Terminé','cancelled'=>'Annulé'];
$statusClasses = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','completed'=>'badge-completed','cancelled'=>'badge-cancelled'];
?>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-num"><?= $todayRdv ?></div><div class="stat-label">RDV aujourd'hui</div></div>
  <div class="stat-card"><div class="stat-num"><?= $pendingRdv ?></div><div class="stat-label">En attente</div></div>
  <div class="stat-card"><div class="stat-num"><?= $totalRdv ?></div><div class="stat-label">Total RDV</div></div>
  <div class="stat-card"><div class="stat-num"><?= $totalContacts ?></div><div class="stat-label">Infirmiers actifs</div></div>
</div>

<div class="card">
  <h2>Derniers rendez-vous</h2>
  <?php if(empty($recent)): ?>
    <p style="color:#64748b;font-size:.9rem">Aucun rendez-vous pour le moment.</p>
  <?php else: ?>
  <table>
    <thead><tr><th>Réf.</th><th>Patient</th><th>Soin</th><th>Date</th><th>Heure</th><th>Statut</th></tr></thead>
    <tbody>
    <?php foreach($recent as $r): ?>
    <tr>
      <td><strong><?= e($r['reference_code']) ?></strong></td>
      <td><?= e($r['patient_first_name'].' '.$r['patient_last_name']) ?></td>
      <td><?= e($r['care_type']) ?></td>
      <td><?= e($r['appointment_date']) ?></td>
      <td><?= e(substr($r['appointment_time'],0,5)) ?></td>
      <td><span class="badge <?= $statusClasses[$r['status']]??'' ?>"><?= e($statusLabels[$r['status']]??$r['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php adminFooter(); ?>
