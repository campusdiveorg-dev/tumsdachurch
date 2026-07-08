<?php
// api/helpers.php
// Shared response helpers used by all controllers.

declare(strict_types=1);

function jsonResponse(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $status = 400): never {
    jsonResponse(['error' => $message], $status);
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function requireFields(array $body, array $fields): void {
    foreach ($fields as $field) {
        if (!isset($body[$field]) || (is_string($body[$field]) && trim($body[$field]) === '')) {
            jsonError("Missing required field: $field", 422);
        }
    }
}

function auditLog(int|null $userId, string $action, string $entity, int|null $entityId = null): void {
    try {
        $db   = \getDB();
        $stmt = $db->prepare(
            'INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $action, $entity, $entityId]);
    } catch (\Throwable) {
        // Audit failures must never break the request
    }
}
