<?php
require_once __DIR__ . '/includes/auth.php';

use SCM\Core\App;

requireAuth();

$pdo = getDB();
$tid = App::instance()->tenantId();
$msg = '';

// Update status
if (isset($_GET['status'], $_GET['id']) && verifyCSRF($_GET['token'] ?? '')) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $valid = ['pending','confirmed','completed','cancelled'];
    if (in_array($status, $valid)) {
        if ($tid !== null) {
            $pdo->prepare("UPDATE appointments SET status=? WHERE id=? AND tenant_id=?")->execute([$status, $id, $tid]);
        } else {
            $pdo->prepare("UPDATE appointments SET status=? WHERE id=?")->execute([$status, $id]);
        }
        auditLog('appointment_status_changed', 'appointment', $id, $status);
        $msg = 'Statut mis à jour.';
    }
}

// Filters
$filter = $_GET['filter'] ?? 'all';
$conditions = [];
$params = [];

if ($tid !== null) {
    $conditions[] = "a.tenant_id = ?";
    $params[] = $tid;
}
if ($filter === 'today') { $conditions[] = "a.appointment_date = CURDATE()"; }
elseif ($filter === 'pending') { $conditions[] = "a.status = 'pending'"; }
elseif ($filter === 'upcoming') { $conditions[] = "a.appointment_date >= CURDATE() AND a.status IN ('pending','confirmed')"; }

$where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $pdo->prepare("SELECT a.*, c.full_name as nurse_name FROM appointments a LEFT JOIN contacts c ON a.nurse_id=c.id $where ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmé','completed'=>'Terminé','cancelled'=>'Annulé'];
$statusClasses = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','completed'=>'badge-completed','cancelled'=>'badge-cancelled'];
$csrf = generateCSRF();
adminHeader('Rendez-vous');
?>
<?php if($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap">
  <a href="?filter=all" class="btn <?= $filter==='all'?'btn-primary':'btn-outline' ?> btn-sm">Tous</a>
  <a href="?filter=today" class="btn <?= $filter==='today'?'btn-primary':'btn-outline' ?> btn-sm">Aujourd'hui</a>
  <a href="?filter=upcoming" class="btn <?= $filter==='upcoming'?'btn-primary':'btn-outline' ?> btn-sm">À venir</a>
  <a href="?filter=pending" class="btn <?= $filter==='pending'?'btn-primary':'btn-outline' ?> btn-sm">En attente</a>
</div>

<div class="card">
<h2>Rendez-vous (<?= count($appointments) ?>)</h2>
<?php if(empty($appointments)): ?>
<p style="color:#64748b;font-size:.9rem">Aucun rendez-vous trouvé.</p>
<?php else: ?>
<div style="overflow-x:auto">
<table>
<thead><tr><th>Réf.</th><th>Patient</th><th>Contact</th><th>Soin</th><th>Date</th><th>Heure</th><th>Infirmier</th><th>Statut</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($appointments as $a): ?>
<tr>
  <td><strong><?= e($a['reference_code']) ?></strong></td>
  <td><?= e($a['patient_first_name'].' '.$a['patient_last_name']) ?></td>
  <td style="font-size:.8rem"><?= e($a['patient_phone']) ?><br><?= e($a['patient_email']) ?></td>
  <td><?= e($a['care_type']) ?><?= $a['is_home_visit']?' <em>(domicile)</em>':'' ?></td>
  <td><?= e($a['appointment_date']) ?></td>
  <td><?= e(substr($a['appointment_time'],0,5)) ?></td>
  <td><?= $a['nurse_name'] ? e($a['nurse_name']) : '—' ?></td>
  <td><span class="badge <?= $statusClasses[$a['status']]??'' ?>"><?= e($statusLabels[$a['status']]??$a['status']) ?></span></td>
  <td class="actions" style="white-space:nowrap">
    <?php if($a['status']==='pending'): ?>
    <a href="?id=<?= $a['id'] ?>&status=confirmed&token=<?= $csrf ?>&filter=<?= $filter ?>" class="btn btn-primary btn-sm">Confirmer</a>
    <?php endif; ?>
    <?php if(in_array($a['status'],['pending','confirmed'])): ?>
    <a href="?id=<?= $a['id'] ?>&status=completed&token=<?= $csrf ?>&filter=<?= $filter ?>" class="btn btn-outline btn-sm">Terminer</a>
    <a href="?id=<?= $a['id'] ?>&status=cancelled&token=<?= $csrf ?>&filter=<?= $filter ?>" class="btn btn-danger btn-sm" onclick="return confirm('Annuler ce RDV ?')">Annuler</a>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php adminFooter(); ?>
