<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/materials/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
try {
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    $stmt->execute([$id]);
    flash_set('success', 'Material deleted successfully.');
} catch (PDOException $e) {
    flash_set('error', 'Unable to delete this material — it is referenced by existing quote requests.');
}

header('Location: /admin/materials/index.php');
exit;
