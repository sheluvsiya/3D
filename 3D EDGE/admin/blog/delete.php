<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/blog/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
flash_set('success', 'Blog post deleted successfully.');
header('Location: /admin/blog/index.php');
exit;
