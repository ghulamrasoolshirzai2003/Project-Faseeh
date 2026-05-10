<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(["error" => "Not logged in"]); exit; }

try {
    $level = $_SESSION['academic_level'] ?? 'beginner';
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM vocab_match_sets WHERE level = ? AND id NOT IN (SELECT question_id FROM user_answered WHERE user_id = ? AND mode = 'vocab_match') ORDER BY RAND() LIMIT 1");
    $stmt->execute([$level, $userId]);
    $q = $stmt->fetch();

    if ($q) {
        $ar_terms = [
            ["id"=>1, "text"=>$q['pair_1_ar']], ["id"=>2, "text"=>$q['pair_2_ar']],
            ["id"=>3, "text"=>$q['pair_3_ar']], ["id"=>4, "text"=>$q['pair_4_ar']], ["id"=>5, "text"=>$q['pair_5_ar']]
        ];
        $en_terms = [
            ["id"=>1, "text"=>$q['pair_1_en']], ["id"=>2, "text"=>$q['pair_2_en']],
            ["id"=>3, "text"=>$q['pair_3_en']], ["id"=>4, "text"=>$q['pair_4_en']], ["id"=>5, "text"=>$q['pair_5_en']]
        ];
        shuffle($ar_terms);
        shuffle($en_terms);
        echo json_encode(["id" => $q['id'], "ar_terms" => $ar_terms, "en_terms" => $en_terms]);
    } else {
        echo json_encode(["completed" => true]);
    }
} catch (Exception $e) { echo json_encode(["error" => "Database error: " . $e->getMessage()]); }
?>
