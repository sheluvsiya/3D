<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Reviews';
$activeNav = 'reviews';

$status  = $_GET['status'] ?? '';
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($status !== '') { $where[] = 'r.status = ?'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name
    FROM reviews r
    LEFT JOIN products p ON p.id = r.product_id
    $whereSql
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$reviews = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Customer reviews are moderated here before they can appear publicly.</p>

<form method="get" class="table-toolbar">
  <select name="status" class="form-select" style="max-width:180px;" data-auto-submit>
    <option value="">All statuses</option>
    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Customer</th><th>Product</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($reviews)): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="bi bi-star"></i><p>No reviews awaiting moderation.</p></div></td></tr>
        <?php else: foreach ($reviews as $r): ?>
          <tr>
            <td class="fw-medium"><?= e($r['customer_name']) ?></td>
            <td class="text-muted"><?= e($r['product_name'] ?? '—') ?></td>
            <td>
              <span class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?><i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star empty' ?>"></i><?php endfor; ?>
              </span>
            </td>
            <td class="text-muted" style="max-width:260px;"><?= e(mb_strimwidth($r['review_text'] ?? '', 0, 80, '…')) ?></td>
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
                <button type="button" class="icon-action-btn danger" title="Delete"
                        data-confirm-delete="#delete-review-<?= $r['id'] ?>" data-confirm-name="this review">
                  <i class="bi bi-trash"></i>
                </button>
                <form id="delete-review-<?= $r['id'] ?>" method="post" action="/admin/reviews/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> review<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
