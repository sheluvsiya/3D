<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Website Content';
$activeNav = 'content';

$fields = ['about_us', 'mission', 'vision', 'company_address', 'company_phone', 'company_email', 'business_hours'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/content/index.php');
        exit;
    }
    foreach ($fields as $key) {
        set_setting($pdo, $key, clean($_POST[$key] ?? ''));
    }
    flash_set('success', 'Website content updated successfully.');
    header('Location: /admin/content/index.php');
    exit;
}

$values = [];
foreach ($fields as $key) {
    $values[$key] = get_setting($pdo, $key);
}

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Basic public-site content: company story, mission/vision and contact details.</p>

<div class="form-card" style="max-width:760px;">
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">About Us</label>
        <textarea name="about_us" class="form-control" rows="4"><?= e($values['about_us']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Mission</label>
        <textarea name="mission" class="form-control" rows="3"><?= e($values['mission']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Vision</label>
        <textarea name="vision" class="form-control" rows="3"><?= e($values['vision']) ?></textarea>
      </div>
      <div class="col-12"><hr></div>
      <div class="col-12">
        <label class="form-label">Business address</label>
        <input type="text" name="company_address" class="form-control" value="<?= e($values['company_address']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Business phone</label>
        <input type="text" name="company_phone" class="form-control" value="<?= e($values['company_phone']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Business email</label>
        <input type="email" name="company_email" class="form-control" value="<?= e($values['company_email']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Business hours</label>
        <input type="text" name="business_hours" class="form-control" value="<?= e($values['business_hours']) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-3de-primary mt-4">Save changes</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
