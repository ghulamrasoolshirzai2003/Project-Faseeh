<?php
require 'includes/db.php';

try {
    // 1. Add class_code column to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS class_code VARCHAR(10) UNIQUE");

    // 2. Generate unique codes for existing teachers
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'teacher' AND class_code IS NULL");
    $stmt->execute();
    $teachers = $stmt->fetchAll();

    foreach ($teachers as $t) {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $update = $pdo->prepare("UPDATE users SET class_code = ? WHERE id = ?");
        $update->execute([$code, $t['id']]);
    }

    echo "✅ Migration Successful: Class codes generated for all teachers.";
} catch (Exception $e) {
    echo "❌ Migration Failed: " . $e->getMessage();
}
?>
