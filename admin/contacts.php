<?php
require_once __DIR__ . '/includes/auth.php';

use SCM\Core\App;

requireRole('admin');

$msg = '';
$pdo = getDB();
$tid = App::instance()->tenantId();

// Delete
if (isset($_GET['delete']) && verifyCSRF($_GET['token'] ?? '')) {
    $id = (int)$_GET['delete'];
    if ($tid !== null) {
        $pdo->prepare("UPDATE contacts SET is_active=0 WHERE id=? AND tenant_id=?")->execute([$id, $tid]);
    } else {
        $pdo->prepare("UPDATE contacts SET is_active=0 WHERE id=?")->execute([$id]);
    }
    auditLog('contact_deleted', 'contact', $id);
    $msg = 'Contact désactivé.';
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $wa = trim($_POST['whatsapp_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'Infirmier(ère)');
    $order = (int)($_POST['display_order'] ?? 0);

    if ($name && $phone) {
        if ($id > 0) {
            if ($tid !== null) {
                $stmt = $pdo->prepare("UPDATE contacts SET full_name=?,phone=?,whatsapp_number=?,email=?,role=?,display_order=? WHERE id=? AND tenant_id=?");
                $stmt->execute([$name,$phone,$wa,$email,$role,$order,$id,$tid]);
            } else {
                $stmt = $pdo->prepare("UPDATE contacts SET full_name=?,phone=?,whatsapp_number=?,email=?,role=?,display_order=? WHERE id=?");
                $stmt->execute([$name,$phone,$wa,$email,$role,$order,$id]);
            }
            auditLog('contact_updated', 'contact', $id);
            $msg = 'Contact mis à jour.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO contacts (tenant_id,full_name,phone,whatsapp_number,email,role,display_order) VALUES(?,?,?,?,?,?,?)");
            $stmt->execute([$tid,$name,$phone,$wa,$email,$role,$order]);
            auditLog('contact_created', 'contact', (int)$pdo->lastInsertId());
            $msg = 'Contact ajouté.';
        }
    }
}

if ($tid !== null) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE tenant_id=? AND is_active=1 ORDER BY display_order,full_name");
    $stmt->execute([$tid]);
    $contacts = $stmt->fetchAll();
} else {
    $contacts = $pdo->query("SELECT * FROM contacts WHERE is_active=1 ORDER BY display_order,full_name")->fetchAll();
}
$edit = null;
if (isset($_GET['edit'])) {
    if ($tid !== null) {
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id=? AND tenant_id=?");
        $stmt->execute([(int)$_GET['edit'], $tid]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id=?");
        $stmt->execute([(int)$_GET['edit']]);
    }
    $edit = $stmt->fetch();
}
$csrf = generateCSRF();
adminHeader('Contacts / Équipe');
?>
<?php if($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="card">
<h2><?= $edit ? 'Modifier le contact' : 'Ajouter un contact' ?></h2>
<form method="POST" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
  <div><label>Nom complet *</label><input type="text" name="full_name" value="<?= e($edit['full_name']??'') ?>" required></div>
  <div><label>Rôle</label><input type="text" name="role" value="<?= e($edit['role']??'Infirmier(ère)') ?>"></div>
  <div><label>Téléphone *</label><input type="tel" name="phone" value="<?= e($edit['phone']??'') ?>" required></div>
  <div><label>WhatsApp</label><input type="tel" name="whatsapp_number" value="<?= e($edit['whatsapp_number']??'') ?>" placeholder="+33612345678"></div>
  <div><label>Email</label><input type="email" name="email" value="<?= e($edit['email']??'') ?>"></div>
  <div><label>Ordre d'affichage</label><input type="number" name="display_order" value="<?= e($edit['display_order']??'0') ?>"></div>
</div>
<button type="submit" class="btn btn-primary" style="margin-top:1rem"><?= $edit ? 'Mettre à jour' : 'Ajouter' ?></button>
<?php if($edit): ?> <a href="/admin/contacts.php" class="btn btn-outline" style="margin-top:1rem">Annuler</a><?php endif; ?>
</form>
</div>

<div class="card">
<h2>Équipe (<?= count($contacts) ?>)</h2>
<?php if(empty($contacts)): ?>
<p style="color:#64748b;font-size:.9rem">Aucun contact. Ajoutez votre premier infirmier ci-dessus.</p>
<?php else: ?>
<table>
<thead><tr><th>Nom</th><th>Rôle</th><th>Téléphone</th><th>WhatsApp</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($contacts as $c): ?>
<tr>
  <td><strong><?= e($c['full_name']) ?></strong></td>
  <td><?= e($c['role']) ?></td>
  <td><?= e($c['phone']) ?></td>
  <td><?= $c['whatsapp_number'] ? e($c['whatsapp_number']) : '<em style="color:#94a3b8">—</em>' ?></td>
  <td class="actions">
    <a href="?edit=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Modifier</a>
    <a href="?delete=<?= $c['id'] ?>&token=<?= $csrf ?>" class="btn btn-danger btn-sm" onclick="return confirm('Désactiver ce contact ?')">Supprimer</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php adminFooter(); ?>
