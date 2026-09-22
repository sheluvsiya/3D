<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Orders';
$activeNav = 'orders';

$search        = clean($_GET['q'] ?? '');
$paymentStatus = $_GET['payment'] ?? '';
$orderStatus   = $_GET['status'] ?? '';
$dateFrom      = $_GET['from'] ?? '';
$dateTo        = $_GET['to'] ?? '';
$page          = current_page();
$perPage       = 10;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($paymentStatus !== '') { $where[] = 'payment_status = ?'; $params[] = $paymentStatus; }
if ($orderStatus !== '')   { $where[] = 'order_status = ?'; $params[] = $orderStatus; }
if ($dateFrom !== '')      { $where[] = 'DATE(created_at) >= ?'; $params[] = $dateFrom; }
if ($dateTo !== '')        { $where[] = 'DATE(created_at) <= ?'; $params[] = $dateTo; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$orderStatusOptions   = ['pending', 'confirmed', 'in_production', 'ready', 'shipped', 'completed', 'cancelled'];
$paymentStatusOptions = ['pending', 'paid', 'failed', 'refunded'];

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Customer orders, from Pending through to Shipped and Completed.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:220px;" placeholder="Search order # or customer" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:170px;" data-auto-submit>
    <option value="">All order statuses</option>
    <?php foreach ($orderStatusOptions as $opt): ?>
      <option value="<?= $opt ?>" <?= $orderStatus === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="payment" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All payment statuses</option>
    <?php foreach ($paymentStatusOptions as $opt): ?>
      <option value="<?= $opt ?>" <?= $paymentStatus === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="from" class="form-control" style="max-width:150px;" value="<?= e($dateFrom) ?>" data-auto-submit>
  <input type="date" name="to" class="form-control" style="max-width:150px;" value="<?= e($dateTo) ?>" data-auto-submit>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="bi bi-bag-check"></i><p>No orders found.</p></div></td></tr>
        <?php else: foreach ($orders as $o): ?>
          <tr>
            <td class="fw-medium text-muted"><?= e($o['order_number']) ?></td>
            <td>
              <div class="fw-medium"><?= e($o['customer_name']) ?></div>
              <div class="text-muted small"><?= e($o['customer_email']) ?></div>
            </td>
            <td class="text-muted"><?= format_date($o['created_at']) ?></td>
            <td><?= format_currency($o['total_amount']) ?></td>
            <td><span class="badge-status <?= status_badge_class($o['payment_status']) ?>"><?= status_label($o['payment_status']) ?></span></td>
            <td><span class="badge-status <?= status_badge_class($o['order_status']) ?>"><?= status_label($o['order_status']) ?></span></td>
            <td class="text-end"><a href="/admin/orders/view.php?id=<?= $o['id'] ?>" class="btn btn-3de-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> order<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
