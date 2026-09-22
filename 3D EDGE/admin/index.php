<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_admin_login();

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Overview of catalogue, sales and customer activity';
$activeNav    = 'dashboard';

/* ---------------------------------------------------------------------
 * Stats — every figure below comes from MySQL. Nothing is hard-coded.
 * When tables are empty these simply resolve to 0.
 * ------------------------------------------------------------------- */
$stats = [
    'total_products'    => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'total_customers'   => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'pending_quotes'    => (int)$pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'pending'")->fetchColumn(),
    'pending_orders'    => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn(),
    'pending_reviews'   => (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
    'contact_messages'  => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn(),
    'revenue'           => (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
    'out_of_stock'      => (int)$pdo->query("SELECT (SELECT COUNT(*) FROM products WHERE stock_status = 'out_of_stock') + (SELECT COUNT(*) FROM materials WHERE stock_status = 'out_of_stock')")->fetchColumn(),
];

$recentQuotes = $pdo->query("
    SELECT q.id, q.customer_name, q.status, q.submitted_at, s.name AS service_name, m.name AS material_name
    FROM quote_requests q
    LEFT JOIN services s ON s.id = q.service_id
    LEFT JOIN materials m ON m.id = q.material_id
    ORDER BY q.submitted_at DESC
    LIMIT 5
")->fetchAll();

$recentOrders = $pdo->query("
    SELECT id, order_number, customer_name, created_at, total_amount, payment_status, order_status
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$recentReviews = $pdo->query("
    SELECT r.id, r.customer_name, r.rating, r.review_text, r.status, r.created_at, p.name AS product_name
    FROM reviews r
    LEFT JOIN products p ON p.id = r.product_id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<p class="page-subtitle"><?= e($pageSubtitle) ?></p>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Total Products</span><span class="stat-icon"><i class="bi bi-box-seam"></i></span></div>
      <div class="stat-value"><?= $stats['total_products'] ?></div>
      <div class="stat-note">In catalogue</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Total Customers</span><span class="stat-icon"><i class="bi bi-people"></i></span></div>
      <div class="stat-value"><?= $stats['total_customers'] ?></div>
      <div class="stat-note">Registered accounts</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Pending Quotes</span><span class="stat-icon"><i class="bi bi-file-earmark-text"></i></span></div>
      <div class="stat-value"><?= $stats['pending_quotes'] ?></div>
      <div class="stat-note">Need review</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Pending Orders</span><span class="stat-icon"><i class="bi bi-bag-check"></i></span></div>
      <div class="stat-value"><?= $stats['pending_orders'] ?></div>
      <div class="stat-note">Awaiting confirmation</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Reviews Awaiting Approval</span><span class="stat-icon"><i class="bi bi-star"></i></span></div>
      <div class="stat-value"><?= $stats['pending_reviews'] ?></div>
      <div class="stat-note">Not yet public</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Contact Messages</span><span class="stat-icon"><i class="bi bi-envelope"></i></span></div>
      <div class="stat-value"><?= $stats['contact_messages'] ?></div>
      <div class="stat-note">Unread</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Revenue</span><span class="stat-icon"><i class="bi bi-graph-up"></i></span></div>
      <div class="stat-value"><?= format_currency($stats['revenue']) ?></div>
      <div class="stat-note">From paid orders</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-top"><span class="stat-label">Out of Stock</span><span class="stat-icon"><i class="bi bi-exclamation-triangle"></i></span></div>
      <div class="stat-value"><?= $stats['out_of_stock'] ?></div>
      <div class="stat-note">Products + materials</div>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="card-plain p-3 mb-4">
  <div class="fw-semibold mb-2" style="font-size:13px;">Quick actions</div>
  <div class="quick-actions">
    <a href="/admin/products/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add Product</a>
    <a href="/admin/services/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add Service</a>
    <a href="/admin/materials/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add Material</a>
    <a href="/admin/quotes/index.php" class="quick-action-btn"><i class="bi bi-file-earmark-text"></i> View Quotes</a>
    <a href="/admin/orders/index.php" class="quick-action-btn"><i class="bi bi-bag-check"></i> View Orders</a>
    <a href="/admin/blog/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add Blog Post</a>
    <a href="/admin/faqs/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add FAQ</a>
    <a href="/admin/gallery/create.php" class="quick-action-btn"><i class="bi bi-plus-circle"></i> Add Gallery Project</a>
  </div>
</div>

<!-- Recent quote requests -->
<div class="d-flex align-items-center justify-content-between mb-2">
  <h2 class="h6 fw-semibold mb-0" style="font-family:'Space Grotesk',sans-serif;">Recent quote requests</h2>
  <a href="/admin/quotes/index.php" class="small">View all</a>
</div>
<div class="table-card mb-4">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Quote ID</th><th>Customer</th><th>Service</th><th>Material</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($recentQuotes)): ?>
          <tr><td colspan="7"><div class="empty-state py-3"><p class="mb-0">No quotation requests found.</p></div></td></tr>
        <?php else: foreach ($recentQuotes as $q): ?>
          <tr>
            <td class="text-muted">#<?= str_pad($q['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td class="fw-medium"><?= e($q['customer_name']) ?></td>
            <td><?= e($q['service_name'] ?? '—') ?></td>
            <td><?= e($q['material_name'] ?? '—') ?></td>
            <td class="text-muted"><?= format_date($q['submitted_at']) ?></td>
            <td><span class="badge-status <?= status_badge_class($q['status']) ?>"><?= status_label($q['status']) ?></span></td>
            <td class="text-end"><a href="/admin/quotes/view.php?id=<?= $q['id'] ?>" class="icon-action-btn" title="View"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent orders -->
<div class="d-flex align-items-center justify-content-between mb-2">
  <h2 class="h6 fw-semibold mb-0" style="font-family:'Space Grotesk',sans-serif;">Recent orders</h2>
  <a href="/admin/orders/index.php" class="small">View all</a>
</div>
<div class="table-card mb-4">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($recentOrders)): ?>
          <tr><td colspan="7"><div class="empty-state py-3"><p class="mb-0">No orders found.</p></div></td></tr>
        <?php else: foreach ($recentOrders as $o): ?>
          <tr>
            <td class="text-muted"><?= e($o['order_number']) ?></td>
            <td class="fw-medium"><?= e($o['customer_name']) ?></td>
            <td class="text-muted"><?= format_date($o['created_at']) ?></td>
            <td><?= format_currency($o['total_amount']) ?></td>
            <td><span class="badge-status <?= status_badge_class($o['payment_status']) ?>"><?= status_label($o['payment_status']) ?></span></td>
            <td><span class="badge-status <?= status_badge_class($o['order_status']) ?>"><?= status_label($o['order_status']) ?></span></td>
            <td class="text-end"><a href="/admin/orders/view.php?id=<?= $o['id'] ?>" class="icon-action-btn" title="View"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent reviews -->
<div class="d-flex align-items-center justify-content-between mb-2">
  <h2 class="h6 fw-semibold mb-0" style="font-family:'Space Grotesk',sans-serif;">Recent reviews</h2>
  <a href="/admin/reviews/index.php" class="small">View all</a>
</div>
<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Customer</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($recentReviews)): ?>
          <tr><td colspan="6"><div class="empty-state py-3"><p class="mb-0">No reviews awaiting moderation.</p></div></td></tr>
        <?php else: foreach ($recentReviews as $r): ?>
          <tr>
            <td class="fw-medium"><?= e($r['customer_name']) ?></td>
            <td>
              <span class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?><i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star empty' ?>"></i><?php endfor; ?>
              </span>
            </td>
            <td class="text-muted" style="max-width:260px;"><?= e(mb_strimwidth($r['review_text'], 0, 70, '…')) ?></td>
            <td class="text-muted"><?= format_date($r['created_at']) ?></td>
            <td><span class="badge-status <?= status_badge_class($r['status']) ?>"><?= status_label($r['status']) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <?php if ($r['status'] === 'pending'): ?>
                <form method="post" action="/admin/reviews/approve.php" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="icon-action-btn success" title="Approve"><i class="bi bi-check-lg"></i></button>
                </form>
                <form method="post" action="/admin/reviews/reject.php" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="icon-action-btn danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                </form>
                <?php endif; ?>
                <a href="/admin/reviews/view.php?id=<?= $r['id'] ?>" class="icon-action-btn" title="View"><i class="bi bi-eye"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
