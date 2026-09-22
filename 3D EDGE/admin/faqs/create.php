<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

$pageTitle = 'Add FAQ';
$activeNav = 'faqs';
$isEdit    = false;
$faq       = [];
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        header('Location: /admin/faqs/create.php');
        exit;
    }

    $question = clean($_POST['question'] ?? '');
    $answer   = clean($_POST['answer'] ?? '');
    $category = clean($_POST['category'] ?? 'General');
    $isActive = (int)($_POST['is_active'] ?? 1);

    if ($question === '') $errors['question'] = 'Question is required.';
    if ($answer === '') $errors['answer'] = 'Answer is required.';

    if (empty($errors)) {
        $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(display_order), 0) FROM faqs")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$question, $answer, $category, $maxOrder + 1, $isActive]);
        flash_set('success', 'FAQ added successfully.');
        header('Location: /admin/faqs/index.php');
        exit;
    }
    $faq = $_POST;
}

require_once __DIR__ . '/../includes/header.php';
require __DIR__ . '/form.php';
require_once __DIR__ . '/../includes/footer.php';
