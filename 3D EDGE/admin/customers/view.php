<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
// Passwords are never selected or displayed on this page, by design.

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, name, email, phone, status, created_at FROM users WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    flash_set('error', 'Customer not found.');
    header('Location: /admin/customers/index.php');
    exit;
}

$ordersStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$ordersStmt->execute([$id]);
$orders = $ordersStmt->fetchAll();

$quotesStmt = $pdo->prepare("SELECT * FROM quote_requests WHERE customer_email = ? ORDER BY submitted_at DESC");
$quotesStmt->execute([$customer['email']]);
$quotes = $quotesStmt->fetchAll();

$reviewsStmt = $pdo->prepare("SELECT * FROM reviews WHERE customer_email = ? ORDER BY created_at DESC");
$reviewsStmt->execute([$customer['email']]);
$reviews = $reviewsStmt->fetchAll();

$pageTitle = $customer['name'];
$activeNav = 'customers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb-row"><a href="/admin/customers/index.php">Customers</a> / <?= e($customer['name']) ?></div>
<h1 class="page-title"><?= e($customer['name']) ?></h1>
<p class="page-subtitle">Customer since <?= format_date($customer['created_at']) ?></p>

<div class="detail-grid">
  <div>
    <div class="detail-section">
      <h6>Orders (<?= count($orders) ?>)</h6>
      <?php if (empty($orders)): ?>
        <p class="text-muted small mb-0">No orders yet.</p>
      <?php else: foreach ($orders as $o): ?>
        <div class="detail-row">
          <span class="k"><a href="/admin/orders/view.php?id=<?= $o['id'] ?>"><?= e($o['order_number']) ?></a> — <?= format_date($o['created_at']) ?></span>
          <span class="v"><?= format_currency($o['total_amount']) ?> <span class="badge-status <?= status_badge_class($o['order_status']) ?> ms-1"><?= status_label($o['order_status']) ?></span></span>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="detail-section">
      <h6>Quote requests (<?= count($quotes) ?>)</h6>
      <?php if (empty($quotes)): ?>
        <p class="text-muted small mb-0">No quote requests yet.</p>
      <?php else: foreach ($quotes as $q): ?>
        <div class="detail-row">
          <span class="k"><a href="/admin/quotes/view.php?id=<?= $q['id'] ?>">Quote #<?= str_pad((string)$q['id'], 4, '0', STR_PAD_LEFT) ?></a> — <?= format_date($q['submitted_at']) ?></span>
          <span class="v"><span class="badge-status <?= status_badge_class($q['status']) ?>"><?= status_label($q['status']) ?></span></span>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="detail-section">
      <h6>Reviews (<?= count($reviews) ?>)</h6>
      <?php if (empty($reviews)): ?>
        <p class="text-muted small mb-0">No reviews submitted yet.</p>
      <?php else: foreach ($reviews as $r): ?>
        <div class="detail-row">
          <span class="k"><?= format_date($r['created_at']) ?> — <?= str_repeat('★', (int)$r['rating']) ?></span>
          <span class="v"><span class="badge-status <?= status_badge_class($r['status']) ?>"><?= status_label($r['status']) ?></span></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div>
    <div class="detail-section">
      <h6>Account</h6>
      <div class="detail-row"><span class="k">Email</span><span class="v"><?= e($customer['email']) ?></span></div>
      <div class="detail-row"><span class="k">Phone</span><span class="v"><?= e($customer['phone'] ?: '—') ?></span></div>
      <div class="detail-row"><span class="k">Status</span><span class="v"><span class="badge-status <?= status_badge_class($customer['status']) ?>"><?= status_label($customer['status']) ?></span></span></div>
      <div class="detail-row"><span class="k">Registered</span><span class="v"><?= format_date($customer['created_at']) ?></span></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
