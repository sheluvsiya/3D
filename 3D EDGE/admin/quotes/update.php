<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/quotes/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE id = ?");
$stmt->execute([$id]);
$quote = $stmt->fetch();

if (!$quote) {
    flash_set('error', 'Quote request not found.');
    header('Location: /admin/quotes/index.php');
    exit;
}

$validStatuses = ['pending', 'under_review', 'requires_information', 'priced', 'approved', 'rejected', 'completed'];

$newStatus       = $_POST['status'] ?? $quote['status'];
$estimatedPrice  = $_POST['estimated_price'] !== '' ? (float)$_POST['estimated_price'] : null;
$productionTime  = clean($_POST['estimated_production_time'] ?? '');
$adminNotes      = clean($_POST['admin_notes'] ?? '');
$rejectionReason = clean($_POST['rejection_reason'] ?? '');

if (!in_array($newStatus, $validStatuses, true)) {
    $newStatus = $quote['status'];
}

if ($newStatus === 'rejected' && $rejectionReason === '') {
    flash_set('error', 'Please enter a rejection reason before rejecting this quote.');
    header('Location: /admin/quotes/view.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE quote_requests
    SET status = ?, estimated_price = ?, estimated_production_time = ?, admin_notes = ?, rejection_reason = ?
    WHERE id = ?
");
$stmt->execute([$newStatus, $estimatedPrice, $productionTime, $adminNotes, $rejectionReason, $id]);

if ($newStatus !== $quote['status']) {
    log_status_change($pdo, 'quote_status_history', 'quote_id', $id, $quote['status'], $newStatus);
}

flash_set('success', 'Quote status updated successfully.');
header('Location: /admin/quotes/view.php?id=' . $id);
exit;
