<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add Gallery Project';
$activeNav = 'gallery';
$isEdit    = false;
$project   = [];
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/gallery/create.php');
        exit;
    }

    $title         = clean($_POST['title'] ?? '');
    $description   = clean($_POST['description'] ?? '');
    $industry      = clean($_POST['industry'] ?? '');
    $serviceType   = clean($_POST['service_type'] ?? '');
    $materialsUsed = clean($_POST['materials_used'] ?? '');
    $isPublished   = (int)($_POST['is_published'] ?? 1);

    if ($title === '') $errors['title'] = 'Project title is required.';

    $imagePath = null;
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/gallery');
            if ($uploaded) $imagePath = 'gallery/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO gallery_projects (title, description, industry, service_type, materials_used, image_path, is_published)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $industry, $serviceType, $materialsUsed, $imagePath, $isPublished]);
        flash_set('success', 'Gallery project added successfully.');
        header('Location: /admin/gallery/index.php');
        exit;
    }
    $project = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
