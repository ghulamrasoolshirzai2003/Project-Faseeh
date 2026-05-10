<?php
require 'includes/db.php';

echo "<h1>🚀 GENERATING 200+ ACADEMIC LESSONS...</h1>";

$reading_topics = [
    ['The History of Baghdad', 'تاريخ بغداد', 'كانت بغداد عاصمة الخلافة العباسية ومركزاً للعلم والترجمة.', 'Baghdad was the capital of the Abbasid Caliphate and a center for science and translation.'],
    ['Modern Architecture', 'العمارة الحديثة', 'تتميز العمارة الحديثة في العالم العربي بدمج التراث مع التكنولوجيا.', 'Modern architecture in the Arab world is characterized by merging heritage with technology.'],
    ['Environmental Protection', 'حماية البيئة', 'يعد الحفاظ على البيئة من أهم التحديات التي تواجه المجتمعات المعاصرة.', 'Preserving the environment is one of the most important challenges facing contemporary societies.'],
    ['Ancient Philosophy', 'الفلسفة القديمة', 'درست الفلسفة القديمة أصل الوجود وطبيعة المعرفة البشرية.', 'Ancient philosophy studied the origin of existence and the nature of human knowledge.'],
    ['International Law', 'القانون الدولي', 'يهدف القانون الدولي إلى تنظيم العلاقات بين الدول والحفاظ على السلام.', 'International law aims to regulate relations between states and maintain peace.']
];

$writing_topics = [
    ['The Role of Youth', 'دور الشباب', 'Write a paragraph about the importance of youth in developing the national economy.'],
    ['Digital Transformation', 'التحول الرقمي', 'Describe how the internet has changed education in your country.'],
    ['Cultural Heritage', 'التراث الثقافي', 'Explain why it is important to preserve ancient monuments for future generations.'],
    ['Social Media Impact', 'تأثير وسائل التواصل', 'Analyze the pros and cons of social media on family relationships.'],
    ['Future of Energy', 'مستقبل الطاقة', 'Discuss the transition from fossil fuels to renewable energy sources.']
];

try {
    $pdo->exec("TRUNCATE TABLE academy_lessons"); // Start fresh
    $stmt = $pdo->prepare("INSERT INTO academy_lessons (type, level, title, arabic_title, content, translation, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Generate 50 Reading Lessons
    for($i=0; $i<50; $i++) {
        $t = $reading_topics[$i % count($reading_topics)];
        $level = $i < 15 ? 'beginner' : ($i < 35 ? 'intermediate' : 'advanced');
        $stmt->execute(['reading', $level, $t[0] . " ($i)", $t[1], $t[2], $t[3], json_encode(['words' => []])]);
    }

    // Generate 50 Writing Lessons
    for($i=0; $i<50; $i++) {
        $t = $writing_topics[$i % count($writing_topics)];
        $level = $i < 15 ? 'beginner' : ($i < 35 ? 'intermediate' : 'advanced');
        $stmt->execute(['writing', $level, $t[0] . " ($i)", $t[1], $t[2], null, json_encode([])]);
    }

    // Generate 50 Speaking Lessons
    for($i=0; $i<50; $i++) {
        $level = $i < 15 ? 'beginner' : ($i < 35 ? 'intermediate' : 'advanced');
        $stmt->execute(['speaking', $level, "Oral Fluency Test $i", "اختبار الطلاقة $i", "هذا نص تجريبي للتدريب على النطق الفصيح رقم $i", null, json_encode([])]);
    }

    // Generate 50 Listening Lessons
    for($i=0; $i<50; $i++) {
        $level = $i < 15 ? 'beginner' : ($i < 35 ? 'intermediate' : 'advanced');
        $stmt->execute(['listening', $level, "Audio Report $i", "تقرير صوتي $i", "الخبر العاجل رقم $i يقول أن العلم يتقدم بسرعة كبيرة.", null, json_encode(['quiz' => ['question' => 'What is advancing fast?', 'options' => ['Science', 'Art', 'History', 'Sports'], 'correct' => 0]])]);
    }

    echo "<h3>✅ SUCCESS: 200 Professional Lessons have been added to your Academy!</h3>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
