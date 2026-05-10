<?php
require 'includes/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("DESCRIBE progress");
    $columns = $stmt->fetchAll();
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
