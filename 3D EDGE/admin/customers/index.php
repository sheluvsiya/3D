<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Customers';
$activeNav = 'customers';

$search  = clean($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') { $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($status !== '') { $where[] = 'u.status = ?'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
    FROM users u
    $whereSql
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$customers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Registered customer accounts. Passwords are never displayed here.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:240px;" placeholder="Search name, email or phone" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All statuses</option>
    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Orders</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($customers)): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><p>No customers found.</p></div></td></tr>
        <?php else: foreach ($customers as $c): ?>
          <tr>
            <td class="fw-medium"><?= e($c['name']) ?></td>
            <td class="text-muted"><?= e($c['email']) ?></td>
            <td class="text-muted"><?= e($c['phone'] ?: '—') ?></td>
            <td class="text-muted"><?= format_date($c['created_at']) ?></td>
            <td class="text-muted"><?= (int)$c['order_count'] ?></td>
            <td><span class="badge-status <?= status_badge_class($c['status']) ?>"><?= status_label($c['status']) ?></span></td>
            <td class="text-end"><a href="/admin/customers/view.php?id=<?= $c['id'] ?>" class="btn btn-3de-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> customer<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
