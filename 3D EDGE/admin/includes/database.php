<?php
/**
 * Central PDO database connection for the 3D Edge admin panel.
 * Every page includes this file once (directly or via header.php)
 * and reuses the same $pdo instance — no per-page connections.
 */

$host = 'localhost';
$db   = '3dedge';
$user = 'root';
$pass = 'Siyab0ng@';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Never leak connection details to the browser.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Unable to connect to the database. Please check the server configuration.');
}
