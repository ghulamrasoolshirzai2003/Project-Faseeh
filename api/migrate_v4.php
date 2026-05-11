<?php
/**
 * Faseeh v4.0 - Academic Suite Expansion
 * Adds tables for Sentence Builder, Error Correction, and expands Grammar questions.
 */
require 'includes/db.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Starting Faseeh v4.0 Academic Suite Migration...</h2>";

    // ==========================================
    // 1. EXPAND GRAMMAR QUESTIONS
    // ==========================================
    $grammar_seed = [
        // Beginner
        ["___ وَلَدٌ ذَكِيٌّ", "___ is a smart boy.", "هُوَ", "هِيَ", "هُمْ", "هُنَّ", "Pronoun Matching (Male)", "beginner"],
        ["هَذِهِ ___ جَمِيلَةٌ", "This is a beautiful ___.", "سَيَّارَةٌ", "كِتَابٌ", "بَيْتٌ", "قَلَمٌ", "Gender Matching (Female)", "beginner"],
        ["أَنَا ___ التُّفَّاحَ", "I ___ the apple.", "آكُلُ", "يَأْكُلُ", "تَأْكُلُ", "نَأْكُلُ", "Present Tense (I)", "beginner"],
        
        // Intermediate
        ["الطَّالِبَانِ ___ إِلَى المَدْرَسَةِ", "The two students ___ to the school.", "ذَهَبَا", "ذَهَبُوا", "ذَهَبَتَا", "ذَهَبْنَ", "Dual Past Tense (Male)", "intermediate"],
        ["البَنَاتُ ___ فِي الحَدِيقَةِ", "The girls ___ in the garden.", "يَلْعَبْنَ", "يَلْعَبُونَ", "يَلْعَبَانِ", "تَلْعَبُ", "Plural Present Tense (Female)", "intermediate"],
        
        // Advanced
        ["إِنَّ اللَّهَ ___ بِعِبَادِهِ", "Indeed, Allah is ___ of His servants.", "خَبِيرٌ", "خَبِيرًا", "خَبِيرٍ", "الخَبِيرُ", "Inna and its Sisters (I'rab)", "advanced"],
        ["كَانَ الجَوُّ ___ أَمْسِ", "The weather was ___ yesterday.", "بَارِدًا", "بَارِدٌ", "بَارِدٍ", "البَارِدُ", "Kana and its Sisters (I'rab)", "advanced"],
        ["لَمْ ___ الطَّالِبُ الدَّرْسَ", "The student did not ___ the lesson.", "يَفْهَمْ", "يَفْهَمُ", "يَفْهَمَ", "فَهِمَ", "Jussive Mood (Majzoom)", "advanced"]
    ];

    $insertGrammar = $pdo->prepare("INSERT INTO grammar_questions (sentence_ar, translation_en, correct_answer, wrong_option_1, wrong_option_2, wrong_option_3, grammar_rule, level) SELECT ?, ?, ?, ?, ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM grammar_questions WHERE sentence_ar = ?)");
    
    $grammarCount = 0;
    foreach ($grammar_seed as $q) {
        $insertGrammar->execute([...$q, $q[0]]);
        if($insertGrammar->rowCount() > 0) $grammarCount++;
    }
    echo "✅ Added $grammarCount new Grammar Questions.<br>";

    // ==========================================
    // 2. SENTENCE BUILDER (DRAG & DROP) TABLE
    // ==========================================
    $sql_tarkib = "CREATE TABLE IF NOT EXISTS `sentence_builder` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `correct_sentence` varchar(255) NOT NULL,
        `scrambled_words` varchar(500) NOT NULL,
        `translation_en` varchar(255) NOT NULL,
        `level` varchar(20) DEFAULT 'intermediate',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_tarkib);

    $tarkib_seed = [
        ["ذَهَبَ الوَلَدُ إِلَى المَدْرَسَةِ", '["المَدْرَسَةِ", "إِلَى", "ذَهَبَ", "الوَلَدُ"]', "The boy went to school.", "beginner"],
        ["أَحْمَدُ يَقْرَأُ كِتَابًا جَدِيدًا", '["يَقْرَأُ", "جَدِيدًا", "أَحْمَدُ", "كِتَابًا"]', "Ahmad is reading a new book.", "intermediate"],
        ["تَعَلُّمُ اللُّغَةِ العَرَبِيَّةِ مُهِمٌّ جِدًّا", '["اللُّغَةِ", "مُهِمٌّ", "تَعَلُّمُ", "جِدًّا", "العَرَبِيَّةِ"]', "Learning the Arabic language is very important.", "advanced"]
    ];

    $insertTarkib = $pdo->prepare("INSERT INTO sentence_builder (correct_sentence, scrambled_words, translation_en, level) SELECT ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM sentence_builder WHERE correct_sentence = ?)");
    foreach ($tarkib_seed as $t) {
        $insertTarkib->execute([...$t, $t[0]]);
    }
    echo "✅ Created and seeded 'sentence_builder' table.<br>";

    // ==========================================
    // 3. ERROR CORRECTION (TASHIH) TABLE
    // ==========================================
    $sql_tashih = "CREATE TABLE IF NOT EXISTS `error_correction` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `wrong_sentence` varchar(255) NOT NULL,
        `wrong_word` varchar(100) NOT NULL,
        `correct_word` varchar(100) NOT NULL,
        `grammar_rule` varchar(255) NOT NULL,
        `translation_en` varchar(255) NOT NULL,
        `level` varchar(20) DEFAULT 'advanced',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_tashih);

    $tashih_seed = [
        ["هَذَا سَيَّارَةٌ سَرِيعَةٌ", "هَذَا", "هَذِهِ", "Use feminine demonstrative pronoun for feminine nouns.", "This is a fast car.", "beginner"],
        ["الرِّجَالُ ذَهَبَا إِلَى السُّوقِ", "ذَهَبَا", "ذَهَبُوا", "Verb must be plural to match plural subject.", "The men went to the market.", "intermediate"],
        ["إِنَّ الطَّالِبُ مُجْتَهِدٌ", "الطَّالِبُ", "الطَّالِبَ", "The noun after 'Inna' must have a Fatha (Mansoub).", "Indeed, the student is hardworking.", "advanced"]
    ];

    $insertTashih = $pdo->prepare("INSERT INTO error_correction (wrong_sentence, wrong_word, correct_word, grammar_rule, translation_en, level) SELECT ?, ?, ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM error_correction WHERE wrong_sentence = ?)");
    foreach ($tashih_seed as $e) {
        $insertTashih->execute([...$e, $e[0]]);
    }
    echo "✅ Created and seeded 'error_correction' table.<br>";

    echo "<h3 style='color:green'>Migration v4.0 Complete! 🎉 The Academic Suite is ready.</h3>";
    echo "<a href='academic_hub.php' style='display:inline-block; padding:10px 20px; background:#f2994a; color:white; text-decoration:none; border-radius:10px;'>Go to Academic Hub</a>";

} catch (PDOException $e) {
    die("<h3 style='color:red'>Migration Failed:</h3> " . $e->getMessage());
}
?>
