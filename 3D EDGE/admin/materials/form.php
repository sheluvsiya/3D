<?php
$material = $material ?? [];
$errors   = $errors ?? [];
$isEdit   = $isEdit ?? false;
function old_m(string $key, array $row) { return $_POST[$key] ?? ($row[$key] ?? ''); }
?>
<div class="breadcrumb-row"><a href="/admin/materials/index.php">Materials</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit material' : 'Add material' ?></h1>
<p class="page-subtitle"><?= $isEdit ? 'Update this material.' : 'Add a new printable material.' ?></p>

<div class="form-card">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Material name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e(old_m('name', $material)) ?>" placeholder="e.g. PETG">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-3">
        <label class="form-label">Price (per kg)</label>
        <div class="input-group">
          <span class="input-group-text">R</span>
          <input type="number" step="0.01" min="0" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= e(old_m('price', $material)) ?>">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Stock status</label>
        <select name="stock_status" class="form-select">
          <option value="available" <?= old_m('stock_status', $material) === 'available' ? 'selected' : '' ?>>Available</option>
          <option value="out_of_stock" <?= old_m('stock_status', $material) === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= e(old_m('description', $material)) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Colour options</label>
        <input type="text" name="color_options" class="form-control" value="<?= e(old_m('color_options', $material)) ?>" placeholder="e.g. Black, White, Grey">
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select">
          <option value="1" <?= (string)old_m('is_active', $material ?: ['is_active' => '1']) === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (string)old_m('is_active', $material) === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Specifications</label>
        <textarea name="specifications" class="form-control" rows="3" placeholder="Tensile strength, temperature resistance, etc."><?= e(old_m('specifications', $material)) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Recommended print settings</label>
        <textarea name="recommended_settings" class="form-control" rows="3" placeholder="Nozzle temp, bed temp, speed, etc."><?= e(old_m('recommended_settings', $material)) ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label">Material image</label>
        <?php if (!empty($material['image_path'])): ?>
          <div><img id="material-image-preview" src="/admin/uploads/<?= e($material['image_path']) ?>" class="current-image-preview"></div>
        <?php else: ?>
          <img id="material-image-preview" class="current-image-preview" style="display:none;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control" data-image-preview="material-image-preview">
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save material' ?></button>
      <a href="/admin/materials/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
