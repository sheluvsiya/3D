<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add Blog Post';
$activeNav = 'blog';
$isEdit    = false;
$post      = [];
$errors    = [];

function make_slug(string $title, PDO $pdo, ?int $excludeId = null): string
{
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    $base = $base !== '' ? $base : 'post';
    $slug = $base;
    $i = 1;
    while (true) {
        $sql = "SELECT COUNT(*) FROM blog_posts WHERE slug = ?" . ($excludeId ? " AND id != $excludeId" : "");
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) break;
        $slug = $base . '-' . (++$i);
    }
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/blog/create.php');
        exit;
    }

    $title            = clean($_POST['title'] ?? '');
    $shortDescription = clean($_POST['short_description'] ?? '');
    $content          = $_POST['content'] ?? '';
    $author           = clean($_POST['author'] ?? 'Administrator');
    $status           = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';

    if ($title === '') $errors['title'] = 'Title is required.';

    $imagePath = null;
    if (empty($errors)) {
        try {
            $uploaded = handle_secure_upload($_FILES['image'] ?? [], ['jpg', 'jpeg', 'png', 'webp'], 5 * 1024 * 1024, __DIR__ . '/../uploads/blog');
            if ($uploaded) $imagePath = 'blog/' . $uploaded['stored_filename'];
        } catch (RuntimeException $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $slug = make_slug($title, $pdo);
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (title, slug, short_description, content, image_path, author, status, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $shortDescription, $content, $imagePath, $author, $status, $publishedAt]);
        flash_set('success', 'Blog post added successfully.');
        header('Location: /admin/blog/index.php');
        exit;
    }
    $post = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
