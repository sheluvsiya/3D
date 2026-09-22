<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Quote Requests';
$activeNav = 'quotes';

$search     = clean($_GET['q'] ?? '');
$status     = $_GET['status'] ?? '';
$serviceId  = $_GET['service'] ?? '';
$materialId = $_GET['material'] ?? '';
$dateFrom   = $_GET['from'] ?? '';
$dateTo     = $_GET['to'] ?? '';
$page       = current_page();
$perPage    = 10;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(q.customer_name LIKE ? OR q.customer_email LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($status !== '')     { $where[] = 'q.status = ?'; $params[] = $status; }
if ($serviceId !== '')  { $where[] = 'q.service_id = ?'; $params[] = $serviceId; }
if ($materialId !== '') { $where[] = 'q.material_id = ?'; $params[] = $materialId; }
if ($dateFrom !== '')   { $where[] = 'DATE(q.submitted_at) >= ?'; $params[] = $dateFrom; }
if ($dateTo !== '')     { $where[] = 'DATE(q.submitted_at) <= ?'; $params[] = $dateTo; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM quote_requests q $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT q.*, s.name AS service_name, m.name AS material_name,
           (SELECT COUNT(*) FROM quote_files f WHERE f.quote_id = q.id) AS file_count
    FROM quote_requests q
    LEFT JOIN services s ON s.id = q.service_id
    LEFT JOIN materials m ON m.id = q.material_id
    $whereSql
    ORDER BY q.submitted_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quotes = $stmt->fetchAll();

$services  = $pdo->query("SELECT * FROM services ORDER BY name")->fetchAll();
$materials = $pdo->query("SELECT * FROM materials ORDER BY name")->fetchAll();

$statusOptions = ['pending', 'under_review', 'requires_information', 'priced', 'approved', 'rejected', 'completed'];

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Custom quotation requests submitted through the website, with uploaded CAD/design files.</p>

<form method="get" class="table-toolbar">
  <input type="text" name="q" class="form-control" style="max-width:200px;" placeholder="Search customer" value="<?= e($search) ?>">
  <select name="status" class="form-select" style="max-width:170px;" data-auto-submit>
    <option value="">All statuses</option>
    <?php foreach ($statusOptions as $opt): ?>
      <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="service" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All services</option>
    <?php foreach ($services as $s): ?><option value="<?= $s['id'] ?>" <?= (string)$serviceId === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
  </select>
  <select name="material" class="form-select" style="max-width:160px;" data-auto-submit>
    <option value="">All materials</option>
    <?php foreach ($materials as $m): ?><option value="<?= $m['id'] ?>" <?= (string)$materialId === (string)$m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option><?php endforeach; ?>
  </select>
  <input type="date" name="from" class="form-control" style="max-width:150px;" value="<?= e($dateFrom) ?>" data-auto-submit>
  <input type="date" name="to" class="form-control" style="max-width:150px;" value="<?= e($dateTo) ?>" data-auto-submit>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Quote ID</th><th>Customer</th><th>Service</th><th>Material</th><th>Date</th><th>File</th><th>Est. Price</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($quotes)): ?>
          <tr><td colspan="9"><div class="empty-state"><i class="bi bi-file-earmark-text"></i><p>No quotation requests found.</p></div></td></tr>
        <?php else: foreach ($quotes as $q): ?>
          <tr>
            <td class="text-muted">#<?= str_pad($q['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td>
              <div class="fw-medium"><?= e($q['customer_name']) ?></div>
              <div class="text-muted small"><?= e($q['customer_email']) ?></div>
            </td>
            <td><?= e($q['service_name'] ?? '—') ?></td>
            <td><?= e($q['material_name'] ?? '—') ?></td>
            <td class="text-muted"><?= format_date($q['submitted_at']) ?></td>
            <td class="text-muted"><?= $q['file_count'] > 0 ? '<i class="bi bi-paperclip"></i> ' . $q['file_count'] : '—' ?></td>
            <td><?= $q['estimated_price'] ? format_currency($q['estimated_price']) : '—' ?></td>
            <td><span class="badge-status <?= status_badge_class($q['status']) ?>"><?= status_label($q['status']) ?></span></td>
            <td class="text-end"><a href="/admin/quotes/view.php?id=<?= $q['id'] ?>" class="btn btn-3de-outline btn-sm">View</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
    <div class="table-card-footer"><span><?= $total ?> request<?= $total === 1 ? '' : 's' ?> total</span><?= render_pagination($page, $totalPages) ?></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
