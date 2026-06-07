<?php
require 'includes/db.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sentence_builder'");
    if($stmt->fetch()) {
        $count = $pdo->query("SELECT COUNT(*) FROM sentence_builder")->fetchColumn();
        echo "TABLE EXISTS: $count rows";
    } else {
        echo "TABLE MISSING";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
