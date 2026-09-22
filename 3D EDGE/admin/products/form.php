<?php
/**
 * Shared form markup for products/create.php and products/edit.php.
 * Expects: $product (array, empty for create), $categories, $errors, $isEdit
 */
$product = $product ?? [];
$errors  = $errors ?? [];
$isEdit  = $isEdit ?? false;

function old(string $key, array $product) {
    return $_POST[$key] ?? ($product[$key] ?? '');
}
?>
<div class="breadcrumb-row"><a href="/admin/products/index.php">Products</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit product' : 'Add product' ?></h1>
<p class="page-subtitle"><?= $isEdit ? 'Update this catalogue item.' : 'Create a new item in the product catalogue.' ?></p>

<div class="form-card">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Product name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= e(old('name', $product)) ?>">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control <?= isset($errors['sku']) ? 'is-invalid' : '' ?>" value="<?= e(old('sku', $product)) ?>">
        <?php if (isset($errors['sku'])): ?><div class="invalid-feedback"><?= e($errors['sku']) ?></div><?php endif; ?>
      </div>

      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (string)old('category_id', $product) === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select">
          <option value="1" <?= (string)old('is_active', $product ?: ['is_active' => '1'])  === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (string)old('is_active', $product) === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= e(old('description', $product)) ?></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Price (excl. VAT)</label>
        <div class="input-group">
          <span class="input-group-text">R</span>
          <input type="number" step="0.01" min="0" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= e(old('price', $product)) ?>">
        </div>
        <?php if (isset($errors['price'])): ?><div class="text-danger small mt-1"><?= e($errors['price']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">VAT rate (%)</label>
        <input type="number" step="0.01" min="0" name="vat_rate" class="form-control" value="<?= e(old('vat_rate', $product ?: ['vat_rate' => '15'])) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock quantity</label>
        <input type="number" min="0" name="stock_quantity" class="form-control" value="<?= e(old('stock_quantity', $product ?: ['stock_quantity' => '0'])) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Stock status</label>
        <select name="stock_status" class="form-select">
          <?php foreach (['in_stock' => 'In stock', 'low_stock' => 'Low stock', 'out_of_stock' => 'Out of stock'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= old('stock_status', $product) === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-hint">Products marked out of stock won't be offered for quote/order selection.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Product image</label>
        <?php if (!empty($product['image_path'])): ?>
          <div><img id="product-image-preview" src="/admin/uploads/<?= e($product['image_path']) ?>" class="current-image-preview"></div>
        <?php else: ?>
          <img id="product-image-preview" class="current-image-preview" style="display:none;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control" data-image-preview="product-image-preview">
        <div class="form-hint">JPG or PNG. Leave empty to keep the current image.</div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save product' ?></button>
      <a href="/admin/products/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
