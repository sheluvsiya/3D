<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    flash_set('error', 'Message not found.');
    header('Location: /admin/messages/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/messages/view.php?id=' . $id);
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        flash_set('success', 'Message deleted successfully.');
        header('Location: /admin/messages/index.php');
        exit;
    }

    $newStatus = in_array($action, ['read', 'replied', 'archived'], true) ? $action : $message['status'];
    $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    flash_set('success', 'Message updated successfully.');
    header('Location: /admin/messages/view.php?id=' . $id);
    exit;
}

// Viewing marks a New message as Read automatically.
if ($message['status'] === 'new') {
    $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")->execute([$id]);
    $message['status'] = 'read';
}

$pageTitle = 'Message from ' . $message['name'];
$activeNav = 'messages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="breadcrumb-row"><a href="/admin/messages/index.php">Contact Messages</a> / View</div>
<h1 class="page-title">Message from <?= e($message['name']) ?></h1>
<p class="page-subtitle">Received <?= format_datetime($message['created_at']) ?></p>

<div class="detail-section" style="max-width:640px;">
  <div class="detail-row"><span class="k">Name</span><span class="v"><?= e($message['name']) ?></span></div>
  <div class="detail-row"><span class="k">Email</span><span class="v"><?= e($message['email']) ?></span></div>
  <div class="detail-row"><span class="k">Subject</span><span class="v"><?= e($message['subject'] ?: '—') ?></span></div>
  <div class="detail-row"><span class="k">Status</span><span class="v"><span class="badge-status <?= status_badge_class($message['status']) ?>"><?= status_label($message['status']) ?></span></span></div>

  <div class="mt-3">
    <div class="text-muted small mb-1">Message</div>
    <div style="white-space:pre-wrap; font-size:13.5px;"><?= e($message['message']) ?></div>
  </div>

  <div class="d-flex gap-2 mt-4 flex-wrap">
    <a href="mailto:<?= e($message['email']) ?>" class="btn btn-3de-primary btn-sm"><i class="bi bi-reply"></i> Reply by email</a>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="replied">
      <button type="submit" class="btn btn-3de-outline btn-sm">Mark as replied</button>
    </form>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="archived">
      <button type="submit" class="btn btn-3de-outline btn-sm">Archive</button>
    </form>
    <form method="post" data-confirm-inline onsubmit="return confirm('Delete this message permanently?');">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete">
      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
