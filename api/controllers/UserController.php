<?php
// api/controllers/UserController.php
// Admin-only member management: list, view, edit role/status.

declare(strict_types=1);

class UserController {

    // GET /api/users
    public function list(): void {
        requireAdmin();
        $db   = getDB();
        $rows = $db->query(
            'SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC'
        )->fetchAll();
        jsonResponse($rows);
    }

    // GET /api/users/{id}
    public function get(int $id): void {
        requireAdmin();
        $stmt = getDB()->prepare('SELECT id, name, email, role, is_active, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) jsonError('User not found.', 404);
        jsonResponse($row);
    }

    // PUT /api/users/{id}
    public function update(int $id, array $actor): void {
        requireCsrf();
        requireAdmin();

        $body    = getRequestBody();
        $allowed = ['name', 'email', 'role', 'is_active'];
        $data    = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $body[$field];
            }
        }
        if (empty($data)) jsonError('No valid fields to update.', 422);

        // Validate role if provided
        if (isset($data['role']) && !in_array($data['role'], ['admin', 'member'], true)) {
            jsonError('Invalid role. Must be admin or member.', 422);
        }

        $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $stmt = getDB()->prepare("UPDATE users SET $sets WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);

        if ($stmt->rowCount() === 0) jsonError('User not found.', 404);

        auditLog($actor['id'], 'update', 'users', $id);
        $this->get($id);
    }

    // DELETE /api/users/{id}  — deactivates rather than hard-deletes
    public function deactivate(int $id, array $actor): void {
        requireCsrf();
        requireAdmin();

        if ($id === $actor['id']) {
            jsonError('You cannot deactivate your own account.', 403);
        }

        $stmt = getDB()->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonError('User not found.', 404);

        auditLog($actor['id'], 'deactivate', 'users', $id);
        jsonResponse(['message' => "User #$id has been deactivated."]);
    }
}
