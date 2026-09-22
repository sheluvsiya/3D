<?php
/**
 * Shared page header.
 * Expects (optionally) before inclusion:
 *   $pageTitle    - string shown in <title> and topbar
 *   $pageSubtitle - string shown under the page title
 *   $activeNav    - key used by sidebar.php to highlight the current section
 * Expects $pdo to already be available (include database.php first).
 */

$pageTitle    = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$activeNav    = $activeNav ?? '';

$notifCounts = get_notification_counts($pdo);
$totalNotifs = $notifCounts['pending_quotes'] + $notifCounts['pending_orders'] + $notifCounts['pending_reviews'] + $notifCounts['new_messages'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · 3D Edge Admin</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/admin/assets/css/style.css">
</head>
<body>
<div class="admin-shell">

  <?php require_once __DIR__ . '/sidebar.php'; ?>

  <div class="admin-main">
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="admin-content">
      <?php $flash = flash_get(); ?>
      <?php if ($flash): ?>
        <div class="alert flash-alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> d-flex align-items-center gap-2 mb-3">
          <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle' ?>"></i>
          <div><?= e($flash['message']) ?></div>
        </div>
      <?php endif; ?>
