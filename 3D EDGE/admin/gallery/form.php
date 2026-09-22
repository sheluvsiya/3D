<?php
/**
 * Shared form markup for gallery/create.php and gallery/edit.php.
 * Expects: $project (array, empty for create), $errors, $isEdit
 */
$project = $project ?? [];
$errors  = $errors ?? [];
$isEdit  = $isEdit ?? false;

function old_gallery(string $key, array $project) {
    return $_POST[$key] ?? ($project[$key] ?? '');
}
?>
<div class="breadcrumb-row"><a href="/admin/gallery/index.php">Project Gallery</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit project' : 'Add project' ?></h1>
<p class="page-subtitle"><?= $isEdit ? 'Update this gallery project.' : 'Showcase a completed project on the public gallery.' ?></p>

<div class="form-card">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Project title</label>
        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= e(old_gallery('title', $project)) ?>">
        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= e(old_gallery('description', $project)) ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Industry</label>
        <input type="text" name="industry" class="form-control" placeholder="e.g. Automotive" value="<?= e(old_gallery('industry', $project)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Service type</label>
        <input type="text" name="service_type" class="form-control" placeholder="e.g. Rapid Prototyping" value="<?= e(old_gallery('service_type', $project)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Materials used</label>
        <input type="text" name="materials_used" class="form-control" placeholder="e.g. PETG, Carbon Fibre" value="<?= e(old_gallery('materials_used', $project)) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Publication status</label>
        <select name="is_published" class="form-select">
          <option value="1" <?= (string)old_gallery('is_published', $project ?: ['is_published' => '1']) === '1' ? 'selected' : '' ?>>Published</option>
          <option value="0" <?= (string)old_gallery('is_published', $project) === '0' ? 'selected' : '' ?>>Unpublished (draft)</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Main image</label>
        <?php if (!empty($project['image_path'])): ?>
          <div><img id="gallery-image-preview" src="/admin/uploads/<?= e($project['image_path']) ?>" class="current-image-preview"></div>
        <?php else: ?>
          <img id="gallery-image-preview" class="current-image-preview" style="display:none;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control" data-image-preview="gallery-image-preview">
        <div class="form-hint">JPG or PNG. Leave empty to keep the current image.</div>
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save project' ?></button>
      <a href="/admin/gallery/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
