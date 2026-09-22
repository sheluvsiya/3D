<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Edit FAQ';
$activeNav = 'faqs';
$isEdit    = true;
$errors    = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
$stmt->execute([$id]);
$faq = $stmt->fetch();

if (!$faq) {
    flash_set('error', 'FAQ not found.');
    header('Location: /admin/faqs/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/faqs/edit.php?id=' . $id);
        exit;
    }

    $question = clean($_POST['question'] ?? '');
    $answer   = clean($_POST['answer'] ?? '');
    $category = clean($_POST['category'] ?? 'General');
    $isActive = (int)($_POST['is_active'] ?? 1);

    if ($question === '') $errors['question'] = 'Question is required.';
    if ($answer === '') $errors['answer'] = 'Answer is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$question, $answer, $category, $isActive, $id]);
        flash_set('success', 'FAQ updated successfully.');
        header('Location: /admin/faqs/index.php');
        exit;
    }
    $faq = array_merge($faq, $_POST);
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
