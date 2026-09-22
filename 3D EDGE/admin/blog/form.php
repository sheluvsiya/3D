<?php
/**
 * Shared form markup for blog/create.php and blog/edit.php.
 * Expects: $post (array, empty for create), $errors, $isEdit
 */
$post   = $post ?? [];
$errors = $errors ?? [];
$isEdit = $isEdit ?? false;

function old_blog(string $key, array $post) {
    return $_POST[$key] ?? ($post[$key] ?? '');
}
?>
<div class="breadcrumb-row"><a href="/admin/blog/index.php">Blog / News</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit post' : 'Add blog post' ?></h1>
<p class="page-subtitle"><?= $isEdit ? 'Update this article.' : 'Write a new article for the public blog.' ?></p>

<div class="form-card" style="max-width:820px;">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= e(old_blog('title', $post)) ?>">
        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Short description</label>
        <textarea name="short_description" class="form-control" rows="2" maxlength="500"><?= e(old_blog('short_description', $post)) ?></textarea>
        <div class="form-hint">Shown in blog listings and link previews. Max 500 characters.</div>
      </div>
      <div class="col-12">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="10"><?= e(old_blog('content', $post)) ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Author</label>
        <input type="text" name="author" class="form-control" value="<?= e(old_blog('author', $post) ?: 'Administrator') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= (old_blog('status', $post) ?: 'draft') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Featured image</label>
        <?php if (!empty($post['image_path'])): ?>
          <div><img id="blog-image-preview" src="/admin/uploads/<?= e($post['image_path']) ?>" class="current-image-preview"></div>
        <?php else: ?>
          <img id="blog-image-preview" class="current-image-preview" style="display:none;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" class="form-control" data-image-preview="blog-image-preview">
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save post' ?></button>
      <a href="/admin/blog/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
