<?php
require 'includes/db.php';

$sql = "
CREATE TABLE IF NOT EXISTS `user_active_sessions` (
  `user_id` int(11) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `questions_completed` int(11) DEFAULT 0,
  `total_target` int(11) DEFAULT 10,
  PRIMARY KEY (`user_id`, `mode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql);
    echo "✅ Table 'user_active_sessions' created.<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
