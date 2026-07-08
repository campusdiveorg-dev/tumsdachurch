<?php
// api/index.php
// API entry point — bootstraps session, CORS, autoload, and dispatches routes.

declare(strict_types=1);

// ── Load .env first ─────────────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';

// ── Session ────────────────────────────────────────────────────────────────
$appUrl    = $_ENV['APP_URL']       ?? 'http://localhost';
$isHttps   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$sessionName = $_ENV['SESSION_NAME'] ?? 'tumsda_session';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,     // HTTPS only in production
    'httponly' => true,
    'samesite' => $isHttps ? 'None' : 'Lax',
]);
session_name($sessionName);
session_start();

// ── Autoload other files ───────────────────────────────────────────────────
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/middleware/RequireAuth.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ContentController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/routes.php';

// ── CORS Headers ───────────────────────────────────────────────────────────
// Allow the admin SPA origin. Adjust if hosted on a separate subdomain.
$allowedOrigins = [
    'http://localhost:5173',      // Vite dev server
    rtrim($appUrl, '/') . '/admin',
    rtrim($appUrl, '/'),
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Route Parsing ──────────────────────────────────────────────────────────
// Dynamically locate /api/ in the request URI to handle different subdirectories or production domains.
$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$apiPos      = strpos($requestUri, '/api/');
if ($apiPos !== false) {
    $pathAfterBase = substr($requestUri, $apiPos + 5);
} else {
    $pathAfterBase = ltrim($requestUri, '/');
}

$segments = array_filter(explode('/', $pathAfterBase), fn($s) => $s !== '');
$segments = array_values($segments);
$method   = $_SERVER['REQUEST_METHOD'];

try {
    resolveRoute($method, $segments);
} catch (PDOException $e) {
    error_log('[TUMSDA API] DB error: ' . $e->getMessage());
    jsonError('A database error occurred.', 500);
} catch (Throwable $e) {
    error_log('[TUMSDA API] Error: ' . $e->getMessage());
    jsonError('An unexpected error occurred.', 500);
}
