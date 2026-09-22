<?php
$faq    = $faq ?? [];
$errors = $errors ?? [];
$isEdit = $isEdit ?? false;
$categories = ['Orders', 'Materials', 'Delivery', 'Design Services', 'General'];

function old_faq(string $key, array $faq) {
    return $_POST[$key] ?? ($faq[$key] ?? '');
}
?>
<div class="breadcrumb-row"><a href="/admin/faqs/index.php">FAQs</a> / <?= $isEdit ? 'Edit' : 'Add' ?></div>
<h1 class="page-title"><?= $isEdit ? 'Edit FAQ' : 'Add FAQ' ?></h1>

<div class="form-card">
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Question</label>
        <input type="text" name="question" class="form-control <?= isset($errors['question']) ? 'is-invalid' : '' ?>" value="<?= e(old_faq('question', $faq)) ?>">
        <?php if (isset($errors['question'])): ?><div class="invalid-feedback"><?= e($errors['question']) ?></div><?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Answer</label>
        <textarea name="answer" class="form-control <?= isset($errors['answer']) ? 'is-invalid' : '' ?>" rows="4"><?= e(old_faq('answer', $faq)) ?></textarea>
        <?php if (isset($errors['answer'])): ?><div class="invalid-feedback"><?= e($errors['answer']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= (old_faq('category', $faq) ?: 'General') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select">
          <option value="1" <?= (string)old_faq('is_active', $faq ?: ['is_active' => '1']) === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (string)old_faq('is_active', $faq) === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-3de-primary"><?= $isEdit ? 'Save changes' : 'Save FAQ' ?></button>
      <a href="/admin/faqs/index.php" class="btn btn-3de-outline">Cancel</a>
    </div>
  </form>
</div>
