<?php
/**
 * Shared helper functions used across the admin panel.
 * Kept framework-free and dependency-free on purpose.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------------------
 * Sanitization / output escaping
 * ------------------------------------------------------------------- */

function clean(string $value): string
{
    return trim($value);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------------------
 * CSRF protection
 * ------------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ---------------------------------------------------------------------
 * Flash messages (success / error banners after redirects)
 * ------------------------------------------------------------------- */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------------------------------------------------------------------
 * Formatting helpers
 * ------------------------------------------------------------------- */

function format_currency(?float $amount): string
{
    return 'R' . number_format((float)$amount, 2);
}

function format_date(?string $datetime, string $format = 'd M Y'): string
{
    if (empty($datetime)) {
        return '—';
    }
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '—';
}

function format_datetime(?string $datetime): string
{
    return format_date($datetime, 'd M Y, H:i');
}

/**
 * Maps a status string to a Bootstrap-style badge class.
 * Extend the $map as new statuses are introduced.
 */
function status_badge_class(string $status): string
{
    $map = [
        // generic
        'active'                => 'bg-success-subtle text-success-emphasis',
        'inactive'               => 'bg-secondary-subtle text-secondary-emphasis',
        'in_stock'                => 'bg-success-subtle text-success-emphasis',
        'low_stock'                => 'bg-warning-subtle text-warning-emphasis',
        'out_of_stock'              => 'bg-danger-subtle text-danger-emphasis',
        'available'                => 'bg-success-subtle text-success-emphasis',
        // quotes
        'pending'                => 'bg-warning-subtle text-warning-emphasis',
        'under_review'            => 'bg-info-subtle text-info-emphasis',
        'requires_information'      => 'bg-warning-subtle text-warning-emphasis',
        'priced'                => 'bg-info-subtle text-info-emphasis',
        'approved'                => 'bg-success-subtle text-success-emphasis',
        'rejected'                => 'bg-danger-subtle text-danger-emphasis',
        'completed'                => 'bg-success-subtle text-success-emphasis',
        // orders
        'confirmed'                => 'bg-info-subtle text-info-emphasis',
        'in_production'            => 'bg-primary-subtle text-primary-emphasis',
        'ready'                    => 'bg-info-subtle text-info-emphasis',
        'shipped'                => 'bg-primary-subtle text-primary-emphasis',
        'cancelled'                => 'bg-danger-subtle text-danger-emphasis',
        // payment
        'paid'                    => 'bg-success-subtle text-success-emphasis',
        'failed'                => 'bg-danger-subtle text-danger-emphasis',
        'refunded'                => 'bg-secondary-subtle text-secondary-emphasis',
        // reviews / messages / blog
        'new'                    => 'bg-info-subtle text-info-emphasis',
        'read'                    => 'bg-secondary-subtle text-secondary-emphasis',
        'replied'                => 'bg-success-subtle text-success-emphasis',
        'archived'                => 'bg-secondary-subtle text-secondary-emphasis',
        'draft'                    => 'bg-secondary-subtle text-secondary-emphasis',
        'published'                => 'bg-success-subtle text-success-emphasis',
        'suspended'                => 'bg-danger-subtle text-danger-emphasis',
    ];
    return $map[$status] ?? 'bg-secondary-subtle text-secondary-emphasis';
}

function status_label(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

/* ---------------------------------------------------------------------
 * Pagination
 * ------------------------------------------------------------------- */

function current_page(): int
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    return $page > 0 ? $page : 1;
}

/**
 * Renders a Bootstrap pagination component and preserves the current
 * query string (search/filter values) across pages.
 */
function render_pagination(int $currentPage, int $totalPages): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $params = $_GET;
    $buildUrl = function (int $page) use ($params) {
        $params['page'] = $page;
        return '?' . http_build_query($params);
    };

    $html = '<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">';

    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . e($buildUrl(max(1, $currentPage - 1))) . '">Prev</a></li>';

    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($buildUrl(1)) . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($buildUrl($i)) . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . e($buildUrl($totalPages)) . '">' . $totalPages . '</a></li>';
    }

    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    $html .= '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . e($buildUrl(min($totalPages, $currentPage + 1))) . '">Next</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

/* ---------------------------------------------------------------------
 * Secure file uploads
 * ------------------------------------------------------------------- */

/**
 * Handles a single uploaded file securely:
 *  - validates extension, MIME type and size
 *  - generates a random server-side filename (never trusts the original)
 *  - stores it in $destDir
 *
 * Returns an array on success: [original_filename, stored_filename, storage_path, file_type, file_size]
 * Returns null if no file was submitted (i.e. the field was left empty).
 * Throws RuntimeException with a user-facing message on validation failure.
 */
function handle_secure_upload(array $file, array $allowedExtensions, int $maxSizeBytes, string $destDir): ?array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The file could not be uploaded (upload error code ' . $file['error'] . ').');
    }

    if ($file['size'] > $maxSizeBytes) {
        throw new RuntimeException('File exceeds the maximum allowed size of ' . round($maxSizeBytes / 1048576) . 'MB.');
    }

    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Invalid file type. Allowed types: ' . strtoupper(implode(', ', $allowedExtensions)) . '.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload.');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destPath   = rtrim($destDir, '/') . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Failed to save the uploaded file.');
    }

    return [
        'original_filename' => basename($originalName),
        'stored_filename'   => $storedName,
        'storage_path'      => $destPath,
        'file_type'         => $extension,
        'file_size'         => $file['size'],
    ];
}

function human_filesize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

/* ---------------------------------------------------------------------
 * Dashboard notification counts (used by navbar + dashboard cards)
 * ------------------------------------------------------------------- */

function get_notification_counts(PDO $pdo): array
{
    return [
        'pending_quotes'   => (int)$pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'pending'")->fetchColumn(),
        'pending_orders'   => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn(),
        'pending_reviews'  => (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
        'new_messages'     => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn(),
    ];
}

/* ---------------------------------------------------------------------
 * Site settings (key-value store used by content.php + settings.php)
 * ------------------------------------------------------------------- */

function get_setting(PDO $pdo, string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare("
        INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$key, $value]);
}

/**
 * Generates a unique, human-readable order number, e.g. 3DE-000042.
 */
function generate_order_number(PDO $pdo): string
{
    $count = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    return '3DE-' . str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);
}

/**
 * Records a status change in a *_status_history table. Used by both
 * quotes/update.php and orders/update-status.php.
 */
function log_status_change(PDO $pdo, string $table, string $fkColumn, int $recordId, ?string $previous, string $new, ?string $notes = null): void
{
    $stmt = $pdo->prepare("INSERT INTO {$table} ({$fkColumn}, previous_status, new_status, changed_by, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$recordId, $previous, $new, CURRENT_ADMIN_NAME, $notes]);
}
