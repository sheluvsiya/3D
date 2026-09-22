<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Services';
$activeNav = 'services';

$search  = clean($_GET['q'] ?? '');
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = 'name LIKE ?';
    $params[] = "%$search%";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM services $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM services $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$services = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">CAD design, reverse engineering, printing and manufacturing services offered to customers.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:260px;" placeholder="Search services" value="<?= e($search) ?>">
  <button type="submit" class="btn btn-3de-outline btn-sm">Search</button>
  <a href="/admin/services/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Service</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th></th><th>Service</th><th>Description</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($services)): ?>
          <tr><td colspan="5"><div class="empty-state"><i class="bi bi-tools"></i><p>No services have been added yet.</p><a href="/admin/services/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Service</a></div></td></tr>
        <?php else: foreach ($services as $s): ?>
          <tr>
            <td>
              <?php if ($s['image_path']): ?><img src="/admin/uploads/<?= e($s['image_path']) ?>" class="row-thumb" alt="">
              <?php else: ?><div class="row-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-tools"></i></div><?php endif; ?>
            </td>
            <td class="fw-medium"><?= e($s['name']) ?></td>
            <td class="text-muted" style="max-width:360px;"><?= e(mb_strimwidth($s['description'] ?? '', 0, 90, '…')) ?></td>
            <td><span class="badge-status <?= status_badge_class($s['is_active'] ? 'active' : 'inactive') ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="row-actions">
                <a href="/admin/services/edit.php?id=<?= $s['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete" data-confirm-delete="#del-svc-<?= $s['id'] ?>" data-confirm-name="<?= e($s['name']) ?>"><i class="bi bi-trash"></i></button>
                <form id="del-svc-<?= $s['id'] ?>" method="post" action="/admin/services/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> service<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
