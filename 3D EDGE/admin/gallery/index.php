<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Project Gallery';
$activeNav = 'gallery';

$search  = clean($_GET['q'] ?? '');
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') { $where[] = '(title LIKE ? OR industry LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_projects $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM gallery_projects $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$projects = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Completed projects shown on the public gallery — filterable by industry and service type.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:240px;" placeholder="Search title or industry" value="<?= e($search) ?>">
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
  <a href="/admin/gallery/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Project</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th></th><th>Title</th><th>Industry</th><th>Service</th><th>Materials used</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($projects)): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="bi bi-images"></i><p>No gallery projects have been added yet.</p>
            <a href="/admin/gallery/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Project</a></div></td></tr>
        <?php else: foreach ($projects as $p): ?>
          <tr>
            <td>
              <?php if ($p['image_path']): ?>
                <img src="/admin/uploads/<?= e($p['image_path']) ?>" class="row-thumb" alt="">
              <?php else: ?>
                <div class="row-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </td>
            <td class="fw-medium"><?= e($p['title']) ?></td>
            <td class="text-muted"><?= e($p['industry'] ?: '—') ?></td>
            <td class="text-muted"><?= e($p['service_type'] ?: '—') ?></td>
            <td class="text-muted"><?= e($p['materials_used'] ?: '—') ?></td>
            <td><span class="badge-status <?= status_badge_class($p['is_published'] ? 'published' : 'draft') ?>"><?= $p['is_published'] ? 'Published' : 'Unpublished' ?></span></td>
            <td>
              <div class="row-actions">
                <a href="/admin/gallery/edit.php?id=<?= $p['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete"
                        data-confirm-delete="#delete-gallery-<?= $p['id'] ?>" data-confirm-name="<?= e($p['title']) ?>">
                  <i class="bi bi-trash"></i>
                </button>
                <form id="delete-gallery-<?= $p['id'] ?>" method="post" action="/admin/gallery/delete.php" class="d-none">
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
    <div class="table-card-footer"><span><?= $total ?> project<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
