<?php
// admin/auth.php - Session-based authentication helper

require_once __DIR__ . '/db.php';

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return !empty($_SESSION['admin']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function logout(): void {
    startSession();
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

function verifyCsrfToken(): bool {
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function requireCsrfToken(): void {
    if (!verifyCsrfToken()) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}
