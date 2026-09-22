<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add Material';
$activeNav = 'materials';
$isEdit    = false;
$material  = [];
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/materials/create.php');
        exit;
    }

    $name          = clean($_POST['name'] ?? '');
    $description   = clean($_POST['description'] ?? '');
    $specs         = clean($_POST['specifications'] ?? '');
    $colors        = clean($_POST['color_options'] ?? '');
    $price         = $_POST['price'] ?? '0';
    $stockStatus   = $_POST['stock_status'] ?? 'available';
    $settings      = clean($_POST['recommended_settings'] ?? '');
    $isActive      = (int)($_POST['is_active'] ?? 1);

    if ($name === '') $errors['name'] = 'Material name is required.';
    if (!is_numeric($price) || (float)$price < 0) $errors['price'] = 'Enter a valid price.';

    $imagePath = null;
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/materials');
            if ($uploaded) $imagePath = 'materials/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO materials (name, description, specifications, color_options, price, stock_status, recommended_settings, image_path, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $specs, $colors, $price, $stockStatus, $settings, $imagePath, $isActive]);
        flash_set('success', 'Material added successfully.');
        header('Location: /admin/materials/index.php');
        exit;
    }
    $material = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
