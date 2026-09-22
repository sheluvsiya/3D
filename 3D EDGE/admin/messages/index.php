<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Contact Messages';
$activeNav = 'messages';

$search  = clean($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$page    = current_page();
$perPage = 10;

$where = [];
$params = [];
if ($search !== '') { $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM contact_messages $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$messages = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Submissions from the public contact form.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:220px;" placeholder="Search name, email, subject" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All statuses</option>
    <?php foreach (['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'] as $val => $label): ?>
      <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Name</th><th>Subject</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr><td colspan="5"><div class="empty-state"><i class="bi bi-envelope"></i><p>No contact messages found.</p></div></td></tr>
        <?php else: foreach ($messages as $m): ?>
          <tr>
            <td>
              <div class="fw-medium"><?= e($m['name']) ?></div>
              <div class="text-muted small"><?= e($m['email']) ?></div>
            </td>
            <td class="text-muted"><?= e($m['subject'] ?: '—') ?></td>
            <td class="text-muted"><?= format_date($m['created_at']) ?></td>
            <td><span class="badge-status <?= status_badge_class($m['status']) ?>"><?= status_label($m['status']) ?></span></td>
            <td class="text-end"><a href="/admin/messages/view.php?id=<?= $m['id'] ?>" class="btn btn-3de-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> message<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
