<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'FAQs';
$activeNav = 'faqs';

$category = $_GET['category'] ?? '';
$where = [];
$params = [];
if ($category !== '') { $where[] = 'category = ?'; $params[] = $category; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT * FROM faqs $whereSql ORDER BY category, display_order ASC, id ASC");
$stmt->execute($params);
$faqs = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM faqs ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Frequently asked questions shown on the public site, grouped by category.</p>

<form method="get" class="table-toolbar">
  <select name="category" class="form-select" style="max-width:200px;" data-auto-submit>
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
  <a href="/admin/faqs/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add FAQ</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th style="width:70px;">Order</th><th>Question</th><th>Category</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($faqs)): ?>
          <tr><td colspan="5"><div class="empty-state"><i class="bi bi-question-circle"></i><p>No FAQs have been added yet.</p>
            <a href="/admin/faqs/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add FAQ</a></div></td></tr>
        <?php else: foreach ($faqs as $f): ?>
          <tr>
            <td>
              <div class="d-flex gap-1">
                <form method="post" action="/admin/faqs/reorder.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $f['id'] ?>">
                  <input type="hidden" name="direction" value="up">
                  <button type="submit" class="icon-action-btn" title="Move up"><i class="bi bi-arrow-up"></i></button>
                </form>
                <form method="post" action="/admin/faqs/reorder.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $f['id'] ?>">
                  <input type="hidden" name="direction" value="down">
                  <button type="submit" class="icon-action-btn" title="Move down"><i class="bi bi-arrow-down"></i></button>
                </form>
              </div>
            </td>
            <td class="fw-medium"><?= e($f['question']) ?></td>
            <td class="text-muted"><?= e($f['category']) ?></td>
            <td><span class="badge-status <?= status_badge_class($f['is_active'] ? 'active' : 'inactive') ?>"><?= $f['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="row-actions">
                <a href="/admin/faqs/edit.php?id=<?= $f['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete"
                        data-confirm-delete="#delete-faq-<?= $f['id'] ?>" data-confirm-name="this FAQ">
                  <i class="bi bi-trash"></i>
                </button>
                <form id="delete-faq-<?= $f['id'] ?>" method="post" action="/admin/faqs/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $f['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
