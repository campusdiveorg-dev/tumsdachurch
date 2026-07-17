<?php
// api/run_migration.php
// One-off database migration helper for production server (e.g. Railway).
// Run this once after deploying to update your live database schema.
// WARNING: Delete this file from your repository after running it.

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Autoload database config
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();

    // ── 1. Check if 'donor_name' already exists in the 'payments' table ──
    $query = $db->query("SHOW COLUMNS FROM payments LIKE 'donor_name'");
    $columnExists = $query->fetch();

    if (!$columnExists) {
        // Execute the ALTER TABLE query
        $db->exec(
            "ALTER TABLE payments 
             ADD COLUMN donor_name VARCHAR(150) DEFAULT NULL 
             AFTER merchant_request_id"
        );
        echo json_encode([
            'success' => true,
            'message' => 'Schema migration completed: "donor_name" column added to "payments" table.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No changes needed: "donor_name" column already exists in "payments" table.'
        ]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Migration failed: ' . $e->getMessage()
    ]);
}
