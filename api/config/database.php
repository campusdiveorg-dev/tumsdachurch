<?php
// api/config/database.php
// Loads credentials from the .env file and returns a PDO MySQL connection.

declare(strict_types=1);

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env from project root (one level above /api)
loadEnv(dirname(__DIR__, 2) . '/.env');

// Reads a config value from $_ENV, then $_SERVER, then getenv() —
// some environments (e.g. FrankenPHP) don't populate $_ENV by default,
// so this chain makes sure Railway-provided variables are actually found.
function env(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value !== false && $value !== null && $value !== '') ? $value : $default;
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'tumsda');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASSWORD', ''); // was DB_PASS — didn't match the Railway variable name

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ]);
    } catch (PDOException $e) {
        // Fallback for default local WAMP/XAMPP (empty password) on localhost
        if (($host === 'localhost' || $host === '127.0.0.1') && $pass !== '') {
            try {
                $pdo = new PDO($dsn, $user, '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                ]);
            } catch (PDOException $ex) {
                throw $e; // Throw original exception if fallback also fails
            }
        } else {
            throw $e;
        }
    }
    return $pdo;
}
