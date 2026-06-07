<?php
require 'includes/db.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM words");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "COLUMNS IN WORDS TABLE:\n";
    foreach($columns as $col) {
        echo "- " . $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
