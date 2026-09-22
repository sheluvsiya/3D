<?php
$service = $service ?? [];
$errors  = $errors ?? [];
$isEdit  = $isEdit ?? false;
function old_s(string $key, array $row) { return $_POST[$key] ?? ($row[$key] ?? ''); }
?>
<div class="breadcrumb-row"><a href="/admin/services/index.php">Services</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit service' : 'Add service' ?></h1>
<p class="page-subtitle"><?= $isEdit ? 'Update this service.' : 'Add a new service offered to customers.' ?></p>

<div class="form-card">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Service name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e(old_s('name', $service)) ?>">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select">
          <option value="1" <?= (string)old_s('is_active', $service ?: ['is_active' => '1']) === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (string)old_s('is_active', $service) === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= e(old_s('description', $service)) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Service image</label>
        <?php if (!empty($service['image_path'])): ?>
          <div><img id="service-image-preview" src="/admin/uploads/<?= e($service['image_path']) ?>" class="current-image-preview"></div>
        <?php else: ?>
          <img id="service-image-preview" class="current-image-preview" style="display:none;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control" data-image-preview="service-image-preview">
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save service' ?></button>
      <a href="/admin/services/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
