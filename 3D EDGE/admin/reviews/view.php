<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name
    FROM reviews r
    LEFT JOIN products p ON p.id = r.product_id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$review = $stmt->fetch();

if (!$review) {
    flash_set('error', 'Review not found.');
    header('Location: /admin/reviews/index.php');
    exit;
}

$pageTitle = 'Review from ' . $review['customer_name'];
$activeNav = 'reviews';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb-row"><a href="/admin/reviews/index.php">Reviews</a> / View</div>
<h1 class="page-title">Review from <?= e($review['customer_name']) ?></h1>
<p class="page-subtitle">Submitted <?= format_datetime($review['created_at']) ?></p>

<div class="detail-section" style="max-width:640px;">
  <div class="detail-row"><span class="k">Customer</span><span class="v"><?= e($review['customer_name']) ?></span></div>
  <div class="detail-row"><span class="k">Email</span><span class="v"><?= e($review['customer_email'] ?: '—') ?></span></div>
  <div class="detail-row"><span class="k">Product</span><span class="v"><?= e($review['product_name'] ?? '—') ?></span></div>
  <div class="detail-row"><span class="k">Rating</span><span class="v">
    <span class="rating-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star empty' ?>"></i><?php endfor; ?></span>
  </span></div>
  <div class="detail-row"><span class="k">Status</span><span class="v"><span class="badge-status <?= status_badge_class($review['status']) ?>"><?= status_label($review['status']) ?></span></span></div>

  <div class="mt-3">
    <div class="text-muted small mb-1">Review text</div>
    <div style="white-space:pre-wrap; font-size:13.5px;"><?= e($review['review_text'] ?: '—') ?></div>
  </div>

  <?php if ($review['status'] === 'pending'): ?>
  <div class="d-flex gap-2 mt-4">
    <form method="post" action="/admin/reviews/approve.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $review['id'] ?>">
      <button type="submit" class="btn btn-3de-primary btn-sm"><i class="bi bi-check-lg"></i> Approve</button>
    </form>
    <form method="post" action="/admin/reviews/reject.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $review['id'] ?>">
      <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x-lg"></i> Reject</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
