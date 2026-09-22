<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/reviews/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
$stmt->execute([$id]);
flash_set('success', 'Review approved successfully.');
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/reviews/index.php'));
exit;
