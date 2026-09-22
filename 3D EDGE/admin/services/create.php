<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add Service';
$activeNav = 'services';
$isEdit    = false;
$service   = [];
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/services/create.php');
        exit;
    }

    $name        = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $isActive    = (int)($_POST['is_active'] ?? 1);

    if ($name === '') $errors['name'] = 'Service name is required.';

    $imagePath = null;
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/services');
            if ($uploaded) $imagePath = 'services/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, image_path, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $imagePath, $isActive]);
        flash_set('success', 'Service added successfully.');
        header('Location: /admin/services/index.php');
        exit;
    }
    $service = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
