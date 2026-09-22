<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Edit Product';
$activeNav = 'products';
$isEdit    = true;
$errors    = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Product not found.');
    header('Location: /admin/products/index.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/products/edit.php?id=' . $id);
        exit;
    }

    $name         = clean($_POST['name'] ?? '');
    $sku          = clean($_POST['sku'] ?? '');
    $categoryId   = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $description  = clean($_POST['description'] ?? '');
    $price        = $_POST['price'] ?? '';
    $vatRate      = $_POST['vat_rate'] ?? '15';
    $stockQty     = (int)($_POST['stock_quantity'] ?? 0);
    $stockStatus  = $_POST['stock_status'] ?? 'in_stock';
    $isActive     = (int)($_POST['is_active'] ?? 1);

    if ($name === '') $errors['name'] = 'Product name is required.';
    if ($sku === '') $errors['sku'] = 'SKU is required.';
    if (!is_numeric($price) || (float)$price < 0) $errors['price'] = 'Enter a valid price.';

    if ($sku !== '') {
        $dupe = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND id != ?");
        $dupe->execute([$sku, $id]);
        if ($dupe->fetchColumn() > 0) {
            $errors['sku'] = 'This SKU is already in use.';
        }
    }

    $imagePath = $product['image_path'];
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload(
                $_FILES['image'] ?? [],
                ['jpg', 'jpeg', 'png', 'webp'],
                5 * 1024 * 1024,
                __DIR__ . '/../uploads/products'
            );
            if ($uploaded) {
                $imagePath = 'products/' . $uploaded['stored_filename'];
            }
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, sku = ?, category_id = ?, description = ?, price = ?, vat_rate = ?, stock_quantity = ?, stock_status = ?, image_path = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $sku, $categoryId, $description, $price, $vatRate, $stockQty, $stockStatus, $imagePath, $isActive, $id]);

        flash_set('success', 'Product updated successfully.');
        header('Location: /admin/products/index.php');
        exit;
    }

    $product = array_merge($product, $_POST);
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
