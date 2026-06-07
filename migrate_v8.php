<?php
require 'includes/db.php';

try {
    // 1. Root Words Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS root_word_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(20) DEFAULT 'beginner',
        complex_word VARCHAR(100),
        correct_root VARCHAR(50),
        translation_en VARCHAR(255),
        wrong_option_1 VARCHAR(50),
        wrong_option_2 VARCHAR(50),
        wrong_option_3 VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Conjugator Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS conjugation_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(20) DEFAULT 'beginner',
        verb_root VARCHAR(50),
        pronoun VARCHAR(50),
        tense VARCHAR(50),
        correct_conjugation VARCHAR(100),
        translation_en VARCHAR(255),
        wrong_option_1 VARCHAR(100),
        wrong_option_2 VARCHAR(100),
        wrong_option_3 VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Dictation Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS dictation_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(20) DEFAULT 'beginner',
        sentence_ar TEXT,
        translation_en TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. Vocab Match Table (Groups of 5 pairs)
    $pdo->exec("CREATE TABLE IF NOT EXISTS vocab_match_sets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(20) DEFAULT 'beginner',
        pair_1_ar VARCHAR(100), pair_1_en VARCHAR(100),
        pair_2_ar VARCHAR(100), pair_2_en VARCHAR(100),
        pair_3_ar VARCHAR(100), pair_3_en VARCHAR(100),
        pair_4_ar VARCHAR(100), pair_4_en VARCHAR(100),
        pair_5_ar VARCHAR(100), pair_5_en VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 5. Reading Comprehension Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reading_comprehension (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(20) DEFAULT 'advanced',
        paragraph_ar TEXT,
        translation_en TEXT,
        question_ar VARCHAR(255),
        correct_answer VARCHAR(255),
        wrong_1 VARCHAR(255),
        wrong_2 VARCHAR(255),
        wrong_3 VARCHAR(255)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "✅ Success: Created all 5 new game tables!<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
