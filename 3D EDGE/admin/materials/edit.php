<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Edit Material';
$activeNav = 'materials';
$isEdit    = true;
$errors    = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->execute([$id]);
$material = $stmt->fetch();

if (!$material) {
    flash_set('error', 'Material not found.');
    header('Location: /admin/materials/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/materials/edit.php?id=' . $id);
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

    $imagePath = $material['image_path'];
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
            UPDATE materials
            SET name = ?, description = ?, specifications = ?, color_options = ?, price = ?, stock_status = ?, recommended_settings = ?, image_path = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $specs, $colors, $price, $stockStatus, $settings, $imagePath, $isActive, $id]);
        flash_set('success', 'Material updated successfully.');
        header('Location: /admin/materials/index.php');
        exit;
    }
    $material = array_merge($material, $_POST);
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
