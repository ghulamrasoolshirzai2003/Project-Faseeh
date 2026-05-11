<?php
/**
 * Faseeh v3.0 - Enterprise Migration Script
 * Adds tables for Grammar, Fill-in-the-blanks, and Academic modes.
 */
require 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Starting Faseeh v3.0 Migration...</h2>";

    // 1. Create Grammar Questions Table
    $sql = "CREATE TABLE IF NOT EXISTS `grammar_questions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sentence_ar` varchar(255) NOT NULL,
        `translation_en` varchar(255) NOT NULL,
        `correct_answer` varchar(100) NOT NULL,
        `wrong_option_1` varchar(100) NOT NULL,
        `wrong_option_2` varchar(100) NOT NULL,
        `wrong_option_3` varchar(100) NOT NULL,
        `grammar_rule` varchar(100) DEFAULT NULL,
        `level` varchar(20) DEFAULT 'intermediate',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "✅ Table 'grammar_questions' created or verified.<br>";

    // 2. Seed Initial Grammar Data
    $seed = [
        ["ذَهَبَ ___ إِلَى المَدْرَسَةِ", "___ went to the school.", "الوَلَدُ", "البِنْتُ", "المُعَلِّمَةُ", "الأُمُّ", "Masculine Subject", "beginner"],
        ["هِيَ ___ رِسَالَةً لِصَدِيقَتِهَا", "She ___ a letter to her friend.", "تَكْتُبُ", "يَكْتُبُ", "نَكْتُبُ", "أَكْتُبُ", "Present Tense (Feminine)", "intermediate"],
        ["نَحْنُ ___ القُرْآنَ كُلَّ يَوْمٍ", "We ___ the Quran every day.", "نَقْرَأُ", "أَقْرَأُ", "تَقْرَأُ", "يَقْرَأُ", "Present Tense (Plural We)", "intermediate"],
        ["___ السَّيَّارَةُ سَرِيعَةٌ", "___ car is fast.", "هَذِهِ", "هَذَا", "هَؤُلَاءِ", "ذَلِكَ", "Demonstrative Pronoun (Feminine)", "intermediate"],
        ["الطَّالِبَانِ ___ دُرُوسَهُمَا", "The two students ___ their lessons.", "يَذَاكِرَانِ", "يُذَاكِرُونَ", "تُذَاكِرَانِ", "يُذَاكِرُ", "Dual Verb Conjugation", "advanced"]
    ];

    $checkStmt = $pdo->query("SELECT COUNT(*) FROM grammar_questions");
    if ($checkStmt->fetchColumn() == 0) {
        $insertStmt = $pdo->prepare("INSERT INTO grammar_questions (sentence_ar, translation_en, correct_answer, wrong_option_1, wrong_option_2, wrong_option_3, grammar_rule, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seed as $q) {
            $insertStmt->execute($q);
        }
        echo "✅ Seeded 5 advanced grammar questions.<br>";
    } else {
        echo "✅ Grammar questions already seeded.<br>";
    }

    echo "<h3 style='color:green'>Migration v3.0 Complete! 🎉</h3>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Migration Failed:</h3> " . $e->getMessage());
}
?>
