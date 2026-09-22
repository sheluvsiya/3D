<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Blog / News';
$activeNav = 'blog';

$search  = clean($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') { $where[] = 'title LIKE ?'; $params[] = "%$search%"; }
if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM blog_posts $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Create, edit and publish articles for the public blog / news section.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:220px;" placeholder="Search title" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All statuses</option>
    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
  <a href="/admin/blog/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Blog Post</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th></th><th>Title</th><th>Author</th><th>Published</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($posts)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="bi bi-newspaper"></i><p>No blog posts have been created yet.</p>
            <a href="/admin/blog/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Blog Post</a></div></td></tr>
        <?php else: foreach ($posts as $p): ?>
          <tr>
            <td>
              <?php if ($p['image_path']): ?>
                <img src="/admin/uploads/<?= e($p['image_path']) ?>" class="row-thumb" alt="">
              <?php else: ?>
                <div class="row-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </td>
            <td class="fw-medium"><?= e($p['title']) ?></td>
            <td class="text-muted"><?= e($p['author']) ?></td>
            <td class="text-muted"><?= $p['published_at'] ? format_date($p['published_at']) : '—' ?></td>
            <td><span class="badge-status <?= status_badge_class($p['status']) ?>"><?= status_label($p['status']) ?></span></td>
            <td>
              <div class="row-actions">
                <a href="/admin/blog/edit.php?id=<?= $p['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete"
                        data-confirm-delete="#delete-post-<?= $p['id'] ?>" data-confirm-name="<?= e($p['title']) ?>">
                  <i class="bi bi-trash"></i>
                </button>
                <form id="delete-post-<?= $p['id'] ?>" method="post" action="/admin/blog/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> post<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
