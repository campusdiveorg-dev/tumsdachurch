<?php
// api/middleware/RequireAuth.php
// Middleware functions for session verification and CSRF protection.

declare(strict_types=1);

function requireAuth(): array {
    if (empty($_SESSION['user_id'])) {
        jsonError('Unauthorized — please log in.', 401);
    }
    return [
        'id'   => (int) $_SESSION['user_id'],
        'role' => $_SESSION['user_role'] ?? 'member',
    ];
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        jsonError('Forbidden — admin access required.', 403);
    }
    return $user;
}

/**
 * Validates CSRF token on state-changing requests (POST, PUT, PATCH, DELETE).
 * The React client must send the token as header: X-CSRF-Token.
 */
function requireCsrf(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) return;

    $clientToken  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token']       ?? '';

    if (empty($sessionToken) || !hash_equals($sessionToken, $clientToken)) {
        jsonError('Invalid or missing CSRF token.', 403);
    }
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
