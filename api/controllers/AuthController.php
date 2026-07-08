<?php
// api/controllers/AuthController.php
// Handles visitor self-registration, login, logout, and session info.

declare(strict_types=1);

class AuthController {

    // POST /api/auth/register
    public function register(): void {
        requireCsrf();
        $body = getRequestBody();
        requireFields($body, ['name', 'email', 'password']);

        $name     = trim($body['name']);
        $email    = strtolower(trim($body['email']));
        $password = $body['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email address.', 422);
        }
        if (strlen($password) < 8) {
            jsonError('Password must be at least 8 characters.', 422);
        }

        $db = getDB();

        // Check duplicate
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonError('An account with this email already exists.', 409);
        }

        // First-ever user automatically becomes admin
        $countStmt = $db->query('SELECT COUNT(*) FROM users');
        $userCount = (int) $countStmt->fetchColumn();
        $role = $userCount === 0 ? 'admin' : 'member';

        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $db->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash, $role]);
        $userId = (int) $db->lastInsertId();

        auditLog($userId, 'register', 'users', $userId);

        // Auto-login: open session so client is immediately authenticated
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_role'] = $role;

        jsonResponse([
            'message'    => 'Account created successfully.',
            'csrf_token' => generateCsrfToken(),
            'user'       => ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role],
        ], 201);
    }

    // POST /api/auth/login
    public function login(): void {
        // Simple rate-limit: max 10 attempts per IP per 15 min stored in session
        $this->checkLoginRateLimit();

        $body = getRequestBody();
        requireFields($body, ['email', 'password']);

        $email    = strtolower(trim($body['email']));
        $password = $body['password'];

        $db   = getDB();
        $stmt = $db->prepare('SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            jsonError('Invalid email or password.', 401);
        }
        if (!$user['is_active']) {
            jsonError('This account has been deactivated. Please contact an administrator.', 403);
        }

        // Success — regenerate session to prevent fixation
        session_regenerate_id(true);
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['login_attempts'] = 0;

        jsonResponse([
            'message'    => 'Logged in successfully.',
            'csrf_token' => generateCsrfToken(),
            'user'       => [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ]);
    }

    // POST /api/auth/logout
    public function logout(): void {
        session_destroy();
        jsonResponse(['message' => 'Logged out.']);
    }

    // GET /api/auth/me
    public function me(): void {
        $user = requireAuth();

        $db   = getDB();
        $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();

        if (!$row) jsonError('User not found.', 404);

        jsonResponse([
            'user'       => $row,
            'csrf_token' => generateCsrfToken(),
        ]);
    }

    // GET /api/auth/csrf  — fetches a fresh CSRF token (used on first load)
    public function csrf(): void {
        jsonResponse(['csrf_token' => generateCsrfToken()]);
    }

    // ── Private ──────────────────────────────────────────────────────────
    private function checkLoginRateLimit(): void {
        $maxAttempts   = 10;
        $windowSeconds = 900; // 15 min

        $attempts  = $_SESSION['login_attempts']   ?? 0;
        $windowStart = $_SESSION['login_window_start'] ?? time();

        if (time() - $windowStart > $windowSeconds) {
            // Reset window
            $_SESSION['login_attempts']    = 0;
            $_SESSION['login_window_start'] = time();
            return;
        }

        if ($attempts >= $maxAttempts) {
            jsonError('Too many login attempts. Please try again in 15 minutes.', 429);
        }
    }
}
