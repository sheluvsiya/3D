<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/products/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    flash_set('success', 'Product deleted successfully.');
} catch (PDOException $e) {
    // Likely referenced by an order_items row — keep the product rather
    // than break historical order records.
    flash_set('error', 'Unable to delete this product because it is referenced by existing orders. Mark it inactive instead.');
}

header('Location: /admin/products/index.php');
exit;
