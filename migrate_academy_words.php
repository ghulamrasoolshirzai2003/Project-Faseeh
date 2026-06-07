<?php
require 'includes/db.php';

echo "<h1>🔄 Updating Reading Lesson Word Metadata...</h1>";

$reading_metadata = [
    'The Beauty of Arabic' => [
        'اللغة' => ['root' => 'ل غ و', 'mean' => 'The language', 'grammar' => 'Noun'],
        'العربية' => ['root' => 'ع ر ب', 'mean' => 'Arabic', 'grammar' => 'Adjective'],
        'لغة' => ['root' => 'ل غ و', 'mean' => 'Language of', 'grammar' => 'Noun'],
        'القرآن' => ['root' => 'ق ر أ', 'mean' => 'The Quran', 'grammar' => 'Proper Noun'],
        'الكريم' => ['root' => 'ك ر م', 'mean' => 'The Noble / Holy', 'grammar' => 'Adjective'],
        'ولغة' => ['root' => 'ل غ و', 'mean' => 'And language of', 'grammar' => 'Conjunction + Noun'],
        'أهل' => ['root' => 'أ ه ل', 'mean' => 'People of / Dwellers of', 'grammar' => 'Noun'],
        'الجنة' => ['root' => 'ج ن ن', 'mean' => 'Paradise / Heaven', 'grammar' => 'Noun']
    ],
    'Al-Andalus History' => [
        'كانت' => ['root' => 'ك و ن', 'mean' => 'Was (fem.)', 'grammar' => 'Verb'],
        'الأندلس' => ['root' => 'N/A', 'mean' => 'Al-Andalus', 'grammar' => 'Proper Noun'],
        'مركزاً' => ['root' => 'ر ك ز', 'mean' => 'A center', 'grammar' => 'Noun'],
        'للعلم' => ['root' => 'ع ل م', 'mean' => 'For knowledge / science', 'grammar' => 'Preposition + Noun'],
        'والثقافة' => ['root' => 'ث ق ف', 'mean' => 'And culture', 'grammar' => 'Conjunction + Noun'],
        'في' => ['root' => 'N/A', 'mean' => 'In', 'grammar' => 'Preposition'],
        'العصور' => ['root' => 'ع ص ر', 'mean' => 'The eras / ages', 'grammar' => 'Plural Noun'],
        'الوسطى' => ['root' => 'و س ط', 'mean' => 'The middle / medieval', 'grammar' => 'Adjective']
    ],
    'Philosophy of Ibn Rushd' => [
        'يعتبر' => ['root' => 'ع ب ر', 'mean' => 'Is considered', 'grammar' => 'Passive Verb'],
        'ابن' => ['root' => 'ب ن ي', 'mean' => 'Son of', 'grammar' => 'Noun'],
        'رشد' => ['root' => 'ر ش د', 'mean' => 'Rushd (proper name)', 'grammar' => 'Proper Noun'],
        'من' => ['root' => 'N/A', 'mean' => 'From / Of', 'grammar' => 'Preposition'],
        'أهم' => ['root' => 'ه م م', 'mean' => 'Most important', 'grammar' => 'Elative Noun'],
        'الفلاسفة' => ['root' => 'ف ل س', 'mean' => 'The philosophers', 'grammar' => 'Plural Noun'],
        'الذين' => ['root' => 'N/A', 'mean' => 'Who / Those who', 'grammar' => 'Relative Pronoun'],
        'حاولوا' => ['root' => 'ح و ل', 'mean' => 'Tried / Attempted', 'grammar' => 'Verb (plural)'],
        'التوفيق' => ['root' => 'و ف ق', 'mean' => 'Reconciliation / Harmony', 'grammar' => 'Verbal Noun (Masdar)'],
        'بين' => ['root' => 'ب ي ن', 'mean' => 'Between', 'grammar' => 'Preposition / Adverb'],
        'الدين' => ['root' => 'د ي ن', 'mean' => 'The religion', 'grammar' => 'Noun'],
        'والفلسفة' => ['root' => 'ف ل س', 'mean' => 'And philosophy', 'grammar' => 'Conjunction + Noun']
    ]
];

try {
    $stmt = $pdo->prepare("UPDATE academy_lessons SET metadata = ? WHERE type = 'reading' AND title = ?");
    
    foreach ($reading_metadata as $title => $words) {
        // Fetch current lesson metadata first
        $select = $pdo->prepare("SELECT metadata FROM academy_lessons WHERE type = 'reading' AND title = ?");
        $select->execute([$title]);
        $row = $select->fetch();
        
        $meta = [];
        if ($row && !empty($row['metadata'])) {
            $meta = json_decode($row['metadata'], true);
        }
        
        // Merge the words data
        $meta['words'] = $words;
        
        // Save back
        $stmt->execute([json_encode($meta), $title]);
        echo "✅ Updated metadata for: <strong>$title</strong><br>";
    }
    
    echo "<h3>🚀 Migration Complete!</h3>";
    
} catch (Exception $e) {
    echo "❌ Migration Failed: " . $e->getMessage();
}
?>
