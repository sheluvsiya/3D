<?php
/**
 * Authentication placeholder.
 *
 * Authentication is intentionally NOT implemented or enforced in this
 * development phase. This file exists so every admin page can already
 * include it — e.g. `require_once __DIR__ . '/../includes/auth.php';` —
 * without needing to be rewritten when login is added later.
 *
 * When authentication is implemented, this file should:
 *   1. Start/verify the session.
 *   2. Check for a logged-in admin (e.g. $_SESSION['admin_id']).
 *   3. Redirect to /admin/login.php if not authenticated.
 *   4. Optionally enforce role-based access (super_admin vs staff).
 *
 * For now it only exposes a placeholder "current admin" used by the
 * UI (top-right profile area, activity logging "changed_by" fields)
 * so those parts of the interface don't need to change later either.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Placeholder admin identity — replace with real session data once
// authentication is implemented.
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin (dev mode)';
}

define('CURRENT_ADMIN_NAME', $_SESSION['admin_name']);

function require_admin_login(): void
{
    // Intentionally a no-op for now.
    // Future implementation:
    // if (empty($_SESSION['admin_id'])) {
    //     header('Location: /admin/login.php');
    //     exit;
    // }
}
