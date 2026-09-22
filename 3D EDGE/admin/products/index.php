<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Products';
$activeNav = 'products';

$search      = clean($_GET['q'] ?? '');
$categoryId  = $_GET['category'] ?? '';
$stockStatus = $_GET['stock'] ?? '';
$page        = current_page();
$perPage     = 10;

$where  = [];
$params = [];

if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categoryId !== '') {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryId;
}
if ($stockStatus !== '') {
    $where[] = 'p.stock_status = ?';
    $params[] = $stockStatus;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN product_categories c ON c.id = p.category_id
    $whereSql
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<p class="page-subtitle">Manage the 3D printers, printed parts and accessories in your catalogue.</p>

<form method="get" class="table-toolbar">
  <div class="position-relative" style="max-width:260px;">
    <input type="text" name="q" class="form-control" placeholder="Search name or SKU" value="<?= e($search) ?>">
  </div>
  <select name="category" class="form-select" style="max-width:180px;" data-auto-submit>
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= (string)$categoryId === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="stock" class="form-select" style="max-width:170px;" data-auto-submit>
    <option value="">All stock statuses</option>
    <option value="in_stock" <?= $stockStatus === 'in_stock' ? 'selected' : '' ?>>In stock</option>
    <option value="low_stock" <?= $stockStatus === 'low_stock' ? 'selected' : '' ?>>Low stock</option>
    <option value="out_of_stock" <?= $stockStatus === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
  </select>
  <button type="submit" class="btn btn-3de-outline btn-sm">Filter</button>
  <a href="/admin/products/create.php" class="btn btn-3de-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Product</a>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr><th></th><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr><td colspan="8">
            <div class="empty-state">
              <i class="bi bi-box-seam"></i>
              <p>No products have been added yet.</p>
              <a href="/admin/products/create.php" class="btn btn-3de-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
            </div>
          </td></tr>
        <?php else: foreach ($products as $p): ?>
          <tr>
            <td>
              <?php if ($p['image_path']): ?>
                <img src="/admin/uploads/<?= e($p['image_path']) ?>" class="row-thumb" alt="">
              <?php else: ?>
                <div class="row-thumb d-flex align-items-center justify-content-center text-muted"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </td>
            <td class="fw-medium"><?= e($p['name']) ?></td>
            <td class="text-muted"><?= e($p['sku']) ?></td>
            <td class="text-muted"><?= e($p['category_name'] ?? '—') ?></td>
            <td><?= format_currency($p['price'] * (1 + $p['vat_rate'] / 100)) ?> <span class="text-muted small">incl. VAT</span></td>
            <td class="text-muted"><?= (int)$p['stock_quantity'] ?></td>
            <td>
              <span class="badge-status <?= status_badge_class($p['stock_status']) ?>"><?= status_label($p['stock_status']) ?></span>
              <?php if (!$p['is_active']): ?><span class="badge-status <?= status_badge_class('inactive') ?> ms-1">Inactive</span><?php endif; ?>
            </td>
            <td>
              <div class="row-actions">
                <a href="/admin/products/edit.php?id=<?= $p['id'] ?>" class="icon-action-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                <button type="button" class="icon-action-btn danger" title="Delete"
                        data-confirm-delete="#delete-form-<?= $p['id'] ?>" data-confirm-name="<?= e($p['name']) ?>">
                  <i class="bi bi-trash"></i>
                </button>
                <form id="delete-form-<?= $p['id'] ?>" method="post" action="/admin/products/delete.php" class="d-none">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($total > 0): ?>
  <div class="table-card-footer">
    <span><?= $total ?> product<?= $total === 1 ? '' : 's' ?> total</span>
    <?= render_pagination($page, $totalPages) ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
