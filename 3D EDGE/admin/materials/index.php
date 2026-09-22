<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Materials';
$activeNav = 'materials';

$search  = clean($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') { $where[] = 'name LIKE ?'; $params[] = "%$search%"; }
if ($status !== '') { $where[] = 'stock_status = ?'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM materials $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM materials $whereSql ORDER BY name ASC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$materials = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Filaments and materials available for printing — PLA, PETG, ABS, ASA, TPU, Nylon and carbon fibre variants.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:220px;" placeholder="Search materials" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:170px;" data-auto-submit>
    <option value="">All statuses</option>
    <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
    <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
  <a href="/admin/materials/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Material</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th></th><th>Material</th><th>Colours</th><th>Price</th><th>Status</th><th>Active</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($materials)): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="bi bi-layers"></i><p>No materials have been added yet.</p><a href="/admin/materials/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Material</a></div></td></tr>
        <?php else: foreach ($materials as $m): ?>
          <tr>
            <td>
              <?php if ($m['image_path']): ?><img src="/admin/uploads/<?= e($m['image_path']) ?>" class="row-thumb" alt="">
              <?php else: ?><div class="row-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-layers"></i></div><?php endif; ?>
            </td>
            <td class="fw-medium"><?= e($m['name']) ?></td>
            <td class="text-muted"><?= e($m['color_options'] ?: '—') ?></td>
            <td><?= format_currency($m['price']) ?> <span class="text-muted small">/ kg</span></td>
            <td><span class="badge-status <?= status_badge_class($m['stock_status']) ?>"><?= status_label($m['stock_status']) ?></span></td>
            <td><span class="badge-status <?= status_badge_class($m['is_active'] ? 'active' : 'inactive') ?>"><?= $m['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="row-actions">
                <a href="/admin/materials/edit.php?id=<?= $m['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete" data-confirm-delete="#del-mat-<?= $m['id'] ?>" data-confirm-name="<?= e($m['name']) ?>"><i class="bi bi-trash"></i></button>
                <form id="del-mat-<?= $m['id'] ?>" method="post" action="/admin/materials/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $m['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> material<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
