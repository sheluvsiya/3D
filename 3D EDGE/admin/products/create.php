<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add Product';
$activeNav = 'products';
$isEdit    = false;
$product   = [];
$errors    = [];

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/products/create.php');
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
        $dupe = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $dupe->execute([$sku]);
        if ($dupe->fetchColumn() > 0) {
            $errors['sku'] = 'This SKU is already in use.';
        }
    }

    $imagePath = null;
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
            INSERT INTO products (name, sku, category_id, description, price, vat_rate, stock_quantity, stock_status, image_path, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $sku, $categoryId, $description, $price, $vatRate, $stockQty, $stockStatus, $imagePath, $isActive]);

        flash_set('success', 'Product added successfully.');
        header('Location: /admin/products/index.php');
        exit;
    }

    $product = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
