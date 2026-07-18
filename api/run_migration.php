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
    $messages = [];

    // ── 1. Check if 'donor_name' already exists in the 'payments' table ──
    $query = $db->query("SHOW COLUMNS FROM payments LIKE 'donor_name'");
    if (!$query->fetch()) {
        $db->exec(
            "ALTER TABLE payments 
             ADD COLUMN donor_name VARCHAR(150) DEFAULT NULL 
             AFTER merchant_request_id"
        );
        $messages[] = 'Column "donor_name" added to "payments" table.';
    }

    // ── 2. Check and add columns for departments_ministries ──
    $columnsDM = [
        'logo_path'        => 'VARCHAR(255) DEFAULT NULL',
        'chair_name'       => 'VARCHAR(150) DEFAULT NULL',
        'chair_photo_path' => 'VARCHAR(255) DEFAULT NULL'
    ];
    foreach ($columnsDM as $col => $definition) {
        $query = $db->query("SHOW COLUMNS FROM departments_ministries LIKE '$col'");
        if (!$query->fetch()) {
            $db->exec("ALTER TABLE departments_ministries ADD COLUMN `$col` $definition");
            $messages[] = "Column \"$col\" added to \"departments_ministries\" table.";
        }
    }

    // ── 3. Check and add columns for missions ──
    $columnsMissions = [
        'logo_path'        => 'VARCHAR(255) DEFAULT NULL',
        'chair_name'       => 'VARCHAR(150) DEFAULT NULL',
        'chair_photo_path' => 'VARCHAR(255) DEFAULT NULL'
    ];
    foreach ($columnsMissions as $col => $definition) {
        $query = $db->query("SHOW COLUMNS FROM missions LIKE '$col'");
        if (!$query->fetch()) {
            $db->exec("ALTER TABLE missions ADD COLUMN `$col` $definition");
            $messages[] = "Column \"$col\" added to \"missions\" table.";
        }
    }

    if (empty($messages)) {
        echo json_encode([
            'success' => true,
            'message' => 'No changes needed: Database schema is already up to date.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Schema migrations completed successfully.',
            'details' => $messages
        ]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Migration failed: ' . $e->getMessage()
    ]);
}
