<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Edit Service';
$activeNav = 'services';
$isEdit    = true;
$errors    = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    flash_set('error', 'Service not found.');
    header('Location: /admin/services/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/services/edit.php?id=' . $id);
        exit;
    }

    $name        = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $isActive    = (int)($_POST['is_active'] ?? 1);

    if ($name === '') $errors['name'] = 'Service name is required.';

    $imagePath = $service['image_path'];
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/services');
            if ($uploaded) $imagePath = 'services/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, image_path = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $description, $imagePath, $isActive, $id]);
        flash_set('success', 'Service updated successfully.');
        header('Location: /admin/services/index.php');
        exit;
    }
    $service = array_merge($service, $_POST);
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
