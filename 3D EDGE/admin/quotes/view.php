<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT q.*, s.name AS service_name, m.name AS material_name
    FROM quote_requests q
    LEFT JOIN services s ON s.id = q.service_id
    LEFT JOIN materials m ON m.id = q.material_id
    WHERE q.id = ?
");
$stmt->execute([$id]);
$quote = $stmt->fetch();

if (!$quote) {
    flash_set('error', 'Quote request not found.');
    header('Location: /admin/quotes/index.php');
    exit;
}

$filesStmt = $pdo->prepare("SELECT * FROM quote_files WHERE quote_id = ? ORDER BY uploaded_at ASC");
$filesStmt->execute([$id]);
$files = $filesStmt->fetchAll();

$historyStmt = $pdo->prepare("SELECT * FROM quote_status_history WHERE quote_id = ? ORDER BY changed_at DESC");
$historyStmt->execute([$id]);
$history = $historyStmt->fetchAll();

$services  = $pdo->query("SELECT * FROM services ORDER BY name")->fetchAll();
$materials = $pdo->query("SELECT * FROM materials ORDER BY name")->fetchAll();

$statusOptions = ['pending', 'under_review', 'requires_information', 'priced', 'approved', 'rejected', 'completed'];

$pageTitle = 'Quote #' . str_pad((string)$quote['id'], 4, '0', STR_PAD_LEFT);
$activeNav = 'quotes';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb-row"><a href="/admin/quotes/index.php">Quote Requests</a> / #<?= str_pad((string)$quote['id'], 4, '0', STR_PAD_LEFT) ?></div>
<h1 class="page-title">Quote from <?= e($quote['customer_name']) ?></h1>
<p class="page-subtitle">Submitted <?= format_datetime($quote['submitted_at']) ?></p>

<div class="detail-grid">
  <div>
    <div class="detail-section">
      <h6>Customer information</h6>
      <div class="detail-row"><span class="k">Name</span><span class="v"><?= e($quote['customer_name']) ?></span></div>
      <div class="detail-row"><span class="k">Email</span><span class="v"><?= e($quote['customer_email']) ?></span></div>
      <div class="detail-row"><span class="k">Phone</span><span class="v"><?= e($quote['customer_phone'] ?: '—') ?></span></div>
    </div>

    <div class="detail-section">
      <h6>Project information</h6>
      <div class="detail-row"><span class="k">Service</span><span class="v"><?= e($quote['service_name'] ?? '—') ?></span></div>
      <div class="detail-row"><span class="k">Material</span><span class="v"><?= e($quote['material_name'] ?? '—') ?></span></div>
      <div class="detail-row"><span class="k">Submitted</span><span class="v"><?= format_datetime($quote['submitted_at']) ?></span></div>
      <?php if (!empty($quote['project_description'])): ?>
        <div class="mt-3">
          <div class="text-muted small mb-1">Project description</div>
          <div style="white-space:pre-wrap; font-size:13.5px;"><?= e($quote['project_description']) ?></div>
        </div>
      <?php endif; ?>
    </div>

    <div class="detail-section">
      <h6>Uploaded files</h6>
      <?php if (empty($files)): ?>
        <p class="text-muted small mb-0">No files were uploaded with this request.</p>
      <?php else: foreach ($files as $f): ?>
        <div class="file-row">
          <i class="bi bi-file-earmark-zip"></i>
          <div>
            <div class="fname"><?= e($f['original_filename']) ?></div>
            <div class="fmeta"><?= strtoupper(e($f['file_type'])) ?> &middot; <?= human_filesize((int)$f['file_size']) ?> &middot; uploaded <?= format_datetime($f['uploaded_at']) ?></div>
          </div>
          <div class="file-actions">
            <a href="/admin/quotes/download.php?file_id=<?= $f['id'] ?>" class="btn btn-3de-outline btn-sm"><i class="bi bi-download"></i> Download</a>
          </div>
        </div>
      <?php endforeach; endif; ?>
      <div class="form-hint mt-2">Files are stored outside the public web folder and are only reachable through this authorized download link.</div>
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
      <h6>Review &amp; pricing</h6>
      <form method="post" action="/admin/quotes/update.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $quote['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <?php foreach ($statusOptions as $opt): ?>
              <option value="<?= $opt ?>" <?= $quote['status'] === $opt ? 'selected' : '' ?>><?= status_label($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Estimated price</label>
          <div class="input-group">
            <span class="input-group-text">R</span>
            <input type="number" step="0.01" min="0" name="estimated_price" class="form-control" value="<?= e($quote['estimated_price'] ?? '') ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Estimated production time</label>
          <input type="text" name="estimated_production_time" class="form-control" placeholder="e.g. 5–7 business days" value="<?= e($quote['estimated_production_time'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Administrator notes</label>
          <textarea name="admin_notes" class="form-control" rows="3"><?= e($quote['admin_notes'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Rejection reason <span class="text-muted fw-normal">(required if rejecting)</span></label>
          <textarea name="rejection_reason" class="form-control" rows="2"><?= e($quote['rejection_reason'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-3de-primary w-100">Save changes</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
