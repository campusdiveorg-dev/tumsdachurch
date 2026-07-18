<?php
// api/controllers/ContentController.php
// Generic CRUD handler for: departments, ministries, leadership,
// sermons, events, weekly_meetings, resources, missions.

declare(strict_types=1);

class ContentController {

    private static array $allowedTables = [
        'departments'    => ['name', 'description', 'scripture_quote', 'scripture_reference', 'external_link', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
        'ministries'     => ['name', 'description', 'scripture_quote', 'scripture_reference', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
        'leadership'     => ['name', 'position', 'photo_path', 'statement', 'sort_order'],
        'sermons'        => ['title', 'youtube_url', 'description', 'is_featured', 'published_at'],
        'events'         => ['title', 'event_date', 'facilitator', 'description'],
        'weekly_meetings'=> ['day_of_week', 'time_range', 'program_name', 'sort_order'],
        'resources'      => ['title', 'description', 'icon_path', 'link_url', 'category', 'sort_order'],
        'missions'       => ['title', 'theme_text', 'theme_verse', 'theme_song', 'start_date', 'end_date', 'description', 'is_upcoming', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
        'announcements'  => ['title', 'content', 'sort_order'],
        'word_of_the_day'=> ['content', 'reference'],
    ];

    private static array $requiredFields = [
        'departments'    => ['name'],
        'ministries'     => ['name'],
        'leadership'     => ['name', 'position'],
        'sermons'        => ['title', 'youtube_url'],
        'events'         => ['title', 'event_date'],
        'weekly_meetings'=> ['day_of_week', 'time_range', 'program_name'],
        'resources'      => ['title', 'link_url'],
        'missions'       => ['title'],
        'announcements'  => ['title', 'content'],
        'word_of_the_day'=> ['content', 'reference'],
    ];

    // GET /api/{table}
    public function list(string $table): void {
        $this->validateTable($table);
        $db = getDB();

        $orderCol = match($table) {
            'events'          => 'event_date',
            'sermons'         => 'published_at DESC, id',
            'weekly_meetings' => 'sort_order, id',
            'word_of_the_day' => 'id DESC',
            default           => 'sort_order, id',
        };

        $realTable = $table;
        $where = '';
        if ($table === 'departments' || $table === 'ministries') {
            $realTable = 'departments_ministries';
            $typeVal   = ($table === 'departments') ? 'department' : 'ministry';
            $where     = "WHERE `type` = '$typeVal'";
        }

        $rows = $db->query("SELECT * FROM `$realTable` $where ORDER BY $orderCol")->fetchAll();
        jsonResponse($rows);
    }

    // GET /api/{table}/{id}
    public function get(string $table, int $id): void {
        $this->validateTable($table);
        $realTable = ($table === 'departments' || $table === 'ministries') ? 'departments_ministries' : $table;
        $stmt = getDB()->prepare("SELECT * FROM `$realTable` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) jsonError("$table item not found.", 404);
        jsonResponse($row);
    }

    // POST /api/{table}
    public function create(string $table, array $actor): void {
        requireCsrf();
        requireAdmin();

        $this->validateTable($table);
        $body   = getRequestBody();

        $required = self::$requiredFields[$table] ?? [];
        foreach ($required as $field) {
            if (!isset($body[$field]) || (is_string($body[$field]) && trim($body[$field]) === '')) {
                $fieldName = ucwords(str_replace('_', ' ', $field));
                jsonError("Field '$fieldName' is required and cannot be empty.", 400);
            }
        }

        $fields = self::$allowedTables[$table];
        $data   = $this->filterBody($body, $fields);

        if (empty($data)) jsonError('No valid fields provided.', 422);

        $realTable = $table;
        if ($table === 'departments' || $table === 'ministries') {
            $realTable = 'departments_ministries';
            $data['type'] = ($table === 'departments') ? 'department' : 'ministry';
        }

        $db      = getDB();
        $cols    = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $holders = implode(', ', array_fill(0, count($data), '?'));
        $stmt    = $db->prepare("INSERT INTO `$realTable` ($cols) VALUES ($holders)");
        $stmt->execute(array_values($data));
        $newId = (int) $db->lastInsertId();

        auditLog($actor['id'], 'create', $table, $newId);
        $this->get($table, $newId); // return new row
    }

    // PUT /api/{table}/{id}
    public function update(string $table, int $id, array $actor): void {
        requireCsrf();
        requireAdmin();

        $this->validateTable($table);
        $body   = getRequestBody();

        $required = self::$requiredFields[$table] ?? [];
        foreach ($required as $field) {
            if (!isset($body[$field]) || (is_string($body[$field]) && trim($body[$field]) === '')) {
                $fieldName = ucwords(str_replace('_', ' ', $field));
                jsonError("Field '$fieldName' is required and cannot be empty.", 400);
            }
        }

        $fields = self::$allowedTables[$table];
        $data   = $this->filterBody($body, $fields);

        if (empty($data)) jsonError('No valid fields provided.', 422);

        $realTable = ($table === 'departments' || $table === 'ministries') ? 'departments_ministries' : $table;
        $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $stmt = getDB()->prepare("UPDATE `$realTable` SET $sets WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);

        if ($stmt->rowCount() === 0) jsonError("$table item not found.", 404);

        auditLog($actor['id'], 'update', $table, $id);
        $this->get($table, $id);
    }

    // DELETE /api/{table}/{id}
    public function delete(string $table, int $id, array $actor): void {
        requireCsrf();
        requireAdmin();

        $this->validateTable($table);
        $realTable = ($table === 'departments' || $table === 'ministries') ? 'departments_ministries' : $table;
        $stmt = getDB()->prepare("DELETE FROM `$realTable` WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) jsonError("$table item not found.", 404);

        auditLog($actor['id'], 'delete', $table, $id);
        jsonResponse(['message' => "Deleted $table #$id."]);
    }

    // ── Private ──────────────────────────────────────────────────────────
    private function validateTable(string $table): void {
        if (!isset(self::$allowedTables[$table])) {
            jsonError("Unknown resource: $table", 404);
        }
    }

    private function filterBody(array $body, array $allowed): array {
        $out = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $out[$field] = $body[$field] === '' ? null : $body[$field];
            }
        }
        return $out;
    }
}
