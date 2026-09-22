<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/faqs/index.php');
    exit;
}

$id        = (int)($_POST['id'] ?? 0);
$direction = $_POST['direction'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
$stmt->execute([$id]);
$faq = $stmt->fetch();

if ($faq) {
    $cmp = $direction === 'up' ? '<' : '>';
    $ord = $direction === 'up' ? 'DESC' : 'ASC';

    $neighborStmt = $pdo->prepare("
        SELECT * FROM faqs
        WHERE category = ? AND display_order $cmp ?
        ORDER BY display_order $ord
        LIMIT 1
    ");
    $neighborStmt->execute([$faq['category'], $faq['display_order']]);
    $neighbor = $neighborStmt->fetch();

    if ($neighbor) {
        $pdo->prepare("UPDATE faqs SET display_order = ? WHERE id = ?")->execute([$neighbor['display_order'], $faq['id']]);
        $pdo->prepare("UPDATE faqs SET display_order = ? WHERE id = ?")->execute([$faq['display_order'], $neighbor['id']]);
    }
}

header('Location: /admin/faqs/index.php');
exit;
