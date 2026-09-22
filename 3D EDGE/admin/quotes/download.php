<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
// NOTE: once authentication is implemented, this is the page where
// access should be restricted to authorized administrator staff only
// (the project brief specifically calls this out for design files).

$fileId = (int)($_GET['file_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM quote_files WHERE id = ?");
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file || !is_file($file['file_path'])) {
    http_response_code(404);
    die('File not found.');
}

// file_path points at admin/../storage/quote_uploads/<stored_filename> —
// a location outside the publicly served web root. This script is the
// only way to reach the file's contents.

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_filename']) . '"');
header('Content-Length: ' . filesize($file['file_path']));
header('X-Content-Type-Options: nosniff');
readfile($file['file_path']);
exit;
