<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    $saved = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || !is_string($saved) || $saved === '' || !hash_equals($saved, $sent)) {
        http_response_code(403);
        exit('Invalid or expired form token. Go back, refresh the page and try again.');
    }
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function valid_status(string $status): bool {
    return in_array($status, ['Pending', 'In Progress', 'Completed'], true);
}
function valid_priority(string $priority): bool {
    return in_array($priority, ['Low', 'Medium', 'High'], true);
}
function current_user_role(): string {
    return $_SESSION['user_role'] ?? 'user';
}

function is_admin(): bool {
    return current_user_role() === 'admin';
}

function require_admin(): void {
    require_login();

    if (!is_admin()) {
        http_response_code(403);
        exit('Access denied. Admin permission required.');
    }
}