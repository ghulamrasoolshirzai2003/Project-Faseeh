<?php
require 'd:/faseeh/includes/db.php';
echo "USERS: ";
foreach($pdo->query("DESCRIBE users") as $r) echo $r['Field'] . " ";
echo "\nPROGRESS: ";
foreach($pdo->query("DESCRIBE progress") as $r) echo $r['Field'] . " ";
?>
