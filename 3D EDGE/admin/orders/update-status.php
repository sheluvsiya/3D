<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid request.');
    header('Location: /admin/orders/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Order not found.');
    header('Location: /admin/orders/index.php');
    exit;
}

$orderStatusOptions   = ['pending', 'confirmed', 'in_production', 'ready', 'shipped', 'completed', 'cancelled'];
$paymentStatusOptions = ['pending', 'paid', 'failed', 'refunded'];

$newOrderStatus   = in_array($_POST['order_status'] ?? '', $orderStatusOptions, true) ? $_POST['order_status'] : $order['order_status'];
$newPaymentStatus = in_array($_POST['payment_status'] ?? '', $paymentStatusOptions, true) ? $_POST['payment_status'] : $order['payment_status'];
$notes            = clean($_POST['notes'] ?? '');

$stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
$stmt->execute([$newOrderStatus, $newPaymentStatus, $id]);

if ($newOrderStatus !== $order['order_status']) {
    log_status_change($pdo, 'order_status_history', 'order_id', $id, $order['order_status'], $newOrderStatus, $notes ?: null);
}

flash_set('success', 'Order status updated successfully.');
header('Location: /admin/orders/view.php?id=' . $id);
exit;
