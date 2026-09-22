<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Settings';
$activeNav = 'settings';

$fields = ['company_name', 'company_tagline', 'vat_rate', 'currency', 'max_upload_mb', 'allowed_extensions'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/settings/index.php');
        exit;
    }
    foreach ($fields as $key) {
        set_setting($pdo, $key, clean($_POST[$key] ?? ''));
    }
    flash_set('success', 'Settings updated successfully.');
    header('Location: /admin/settings/index.php');
    exit;
}

$values = [];
foreach ($fields as $key) {
    $values[$key] = get_setting($pdo, $key);
}

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Core business settings. VAT and currency are configurable here instead of being hard-coded.</p>

<div class="form-card" style="max-width:640px;">
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Company name</label>
        <input type="text" name="company_name" class="form-control" value="<?= e($values['company_name']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" class="form-control" value="<?= e($values['currency'] ?: 'ZAR') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Tagline</label>
        <input type="text" name="company_tagline" class="form-control" value="<?= e($values['company_tagline']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">VAT rate (%)</label>
        <input type="number" step="0.01" min="0" name="vat_rate" class="form-control" value="<?= e($values['vat_rate'] ?: '15') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Max upload size (MB)</label>
        <input type="number" min="1" name="max_upload_mb" class="form-control" value="<?= e($values['max_upload_mb'] ?: '50') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Allowed file extensions</label>
        <input type="text" name="allowed_extensions" class="form-control" value="<?= e($values['allowed_extensions'] ?: 'stl,step,stp,igs,iges,obj,zip') ?>">
        <div class="form-hint">Comma-separated, no dots.</div>
      </div>
    </div>
    <button type="submit" class="btn btn-3de-primary mt-4">Save changes</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
