<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Order not found.');
    header('Location: /admin/orders/index.php');
    exit;
}

$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$paymentsStmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
$paymentsStmt->execute([$id]);
$payment = $paymentsStmt->fetch();

$historyStmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY changed_at DESC");
$historyStmt->execute([$id]);
$history = $historyStmt->fetchAll();

$orderStatusOptions   = ['pending', 'confirmed', 'in_production', 'ready', 'shipped', 'completed', 'cancelled'];
$paymentStatusOptions = ['pending', 'paid', 'failed', 'refunded'];

$pageTitle = 'Order ' . $order['order_number'];
$activeNav = 'orders';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb-row"><a href="/admin/orders/index.php">Orders</a> / <?= e($order['order_number']) ?></div>
<h1 class="page-title">Order <?= e($order['order_number']) ?></h1>
<p class="page-subtitle">Placed <?= format_datetime($order['created_at']) ?></p>

<div class="detail-grid">
  <div>
    <div class="detail-section">
      <h6>Customer</h6>
      <div class="detail-row"><span class="k">Name</span><span class="v"><?= e($order['customer_name']) ?></span></div>
      <div class="detail-row"><span class="k">Email</span><span class="v"><?= e($order['customer_email']) ?></span></div>
      <div class="detail-row"><span class="k">Phone</span><span class="v"><?= e($order['customer_phone'] ?: '—') ?></span></div>
    </div>

    <div class="detail-section">
      <h6>Order items</h6>
      <?php if (empty($items)): ?>
        <p class="text-muted small mb-0">No line items recorded for this order.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th class="text-end">Line total</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?= e($it['product_name']) ?></td>
                  <td><?= (int)$it['quantity'] ?></td>
                  <td><?= format_currency($it['unit_price']) ?></td>
                  <td class="text-end"><?= format_currency($it['line_total']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <hr>
        <div class="detail-row"><span class="k">Subtotal</span><span class="v"><?= format_currency($order['subtotal']) ?></span></div>
        <div class="detail-row"><span class="k">VAT</span><span class="v"><?= format_currency($order['vat_amount']) ?></span></div>
        <div class="detail-row"><span class="k">Shipping</span><span class="v"><?= format_currency($order['shipping_amount']) ?></span></div>
        <div class="detail-row"><span class="k fw-semibold">Total</span><span class="v fw-semibold"><?= format_currency($order['total_amount']) ?></span></div>
      <?php endif; ?>
    </div>

    <div class="detail-section">
      <h6>Payment</h6>
      <div class="detail-row"><span class="k">Status</span><span class="v"><span class="badge-status <?= status_badge_class($order['payment_status']) ?>"><?= status_label($order['payment_status']) ?></span></span></div>
      <div class="detail-row"><span class="k">Reference</span><span class="v"><?= e($payment['payment_reference'] ?? '—') ?></span></div>
      <div class="detail-row"><span class="k">Payment date</span><span class="v"><?= $payment && $payment['payment_date'] ? format_datetime($payment['payment_date']) : '—' ?></span></div>
    </div>

    <div class="detail-section">
      <h6>Shipping</h6>
      <div class="detail-row"><span class="k">Address</span><span class="v" style="white-space:pre-wrap; text-align:right;"><?= e($order['shipping_address'] ?: '—') ?></span></div>
      <div class="detail-row"><span class="k">Delivery method</span><span class="v"><?= e($order['delivery_method'] ?: '—') ?></span></div>
    </div>

    <?php if (!empty($history)): ?>
    <div class="detail-section">
      <h6>Status history</h6>
      <ul class="status-timeline">
        <?php foreach ($history as $h): ?>
          <li>
            <span class="dot"></span>
            <div>
              <div class="tl-label"><?= e(status_label($h['previous_status'] ?? 'created')) ?> → <?= e(status_label($h['new_status'])) ?></div>
              <div class="tl-meta"><?= format_datetime($h['changed_at']) ?> &middot; <?= e($h['changed_by']) ?><?= $h['notes'] ? ' — ' . e($h['notes']) : '' ?></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="detail-section">
      <h6>Update status</h6>
      <form method="post" action="/admin/orders/update-status.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $order['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Order status</label>
          <select name="order_status" class="form-select">
            <?php foreach ($orderStatusOptions as $opt): ?>
              <option value="<?= $opt ?>" <?= $order['order_status'] === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Payment status</label>
          <select name="payment_status" class="form-select">
            <?php foreach ($paymentStatusOptions as $opt): ?>
              <option value="<?= $opt ?>" <?= $order['payment_status'] === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Note <span class="text-muted fw-normal">(optional)</span></label>
          <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Handed to courier"></textarea>
        </div>

        <button type="submit" class="btn btn-3de-primary w-100">Save changes</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
