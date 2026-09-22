<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Edit Blog Post';
$activeNav = 'blog';
$isEdit    = true;
$errors    = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    flash_set('error', 'Blog post not found.');
    header('Location: /admin/blog/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/blog/edit.php?id=' . $id);
        exit;
    }

    $title            = clean($_POST['title'] ?? '');
    $shortDescription = clean($_POST['short_description'] ?? '');
    $content          = $_POST['content'] ?? '';
    $author           = clean($_POST['author'] ?? 'Administrator');
    $status           = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';

    if ($title === '') $errors['title'] = 'Title is required.';

    $imagePath = $post['image_path'];
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/blog');
            if ($uploaded) $imagePath = 'blog/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $publishedAt = $post['published_at'];
        if ($status === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $stmt = $pdo->prepare("
            UPDATE blog_posts
            SET title = ?, short_description = ?, content = ?, image_path = ?, author = ?, status = ?, published_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $shortDescription, $content, $imagePath, $author, $status, $publishedAt, $id]);
        flash_set('success', 'Blog post updated successfully.');
        header('Location: /admin/blog/index.php');
        exit;
    }
    $post = array_merge($post, $_POST);
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
