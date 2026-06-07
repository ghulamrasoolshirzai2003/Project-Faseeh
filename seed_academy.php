<?php
require 'includes/db.php';

echo "<h1>🌱 SEEDING FASEEH ACADEMY (Massive Content Pack)</h1>";

$lessons = [
    // --- READING LESSONS ---
    [
        'type' => 'reading', 'level' => 'beginner',
        'title' => 'The Beauty of Arabic', 'arabic_title' => 'جمال اللغة العربية',
        'content' => 'اللغة العربية لغة القرآن الكريم ولغة أهل الجنة.',
        'translation' => 'The Arabic language is the language of the Holy Quran and the people of Paradise.',
        'metadata' => json_encode(['words' => [
            'اللغة' => ['root' => 'ل غ و', 'mean' => 'The language', 'grammar' => 'Noun'],
            'العربية' => ['root' => 'ع ر ب', 'mean' => 'Arabic', 'grammar' => 'Adjective'],
            'لغة' => ['root' => 'ل غ و', 'mean' => 'Language of', 'grammar' => 'Noun'],
            'القرآن' => ['root' => 'ق ر أ', 'mean' => 'The Quran', 'grammar' => 'Proper Noun'],
            'الكريم' => ['root' => 'ك ر م', 'mean' => 'The Noble / Holy', 'grammar' => 'Adjective'],
            'ولغة' => ['root' => 'ل غ و', 'mean' => 'And language of', 'grammar' => 'Conjunction + Noun'],
            'أهل' => ['root' => 'أ ه ل', 'mean' => 'People of / Dwellers of', 'grammar' => 'Noun'],
            'الجنة' => ['root' => 'ج ن ن', 'mean' => 'Paradise / Heaven', 'grammar' => 'Noun']
        ]])
    ],
    [
        'type' => 'reading', 'level' => 'intermediate',
        'title' => 'Al-Andalus History', 'arabic_title' => 'تاريخ الأندلس',
        'content' => 'كانت الأندلس مركزاً للعلم والثقافة في العصور الوسطى.',
        'translation' => 'Al-Andalus was a center for science and culture in the Middle Ages.',
        'metadata' => json_encode(['words' => [
            'كانت' => ['root' => 'ك و ن', 'mean' => 'Was (fem.)', 'grammar' => 'Verb'],
            'الأندلس' => ['root' => 'N/A', 'mean' => 'Al-Andalus', 'grammar' => 'Proper Noun'],
            'مركزاً' => ['root' => 'ر ك ز', 'mean' => 'A center', 'grammar' => 'Noun'],
            'للعلم' => ['root' => 'ع ل م', 'mean' => 'For knowledge / science', 'grammar' => 'Preposition + Noun'],
            'والثقافة' => ['root' => 'ث ق ف', 'mean' => 'And culture', 'grammar' => 'Conjunction + Noun'],
            'في' => ['root' => 'N/A', 'mean' => 'In', 'grammar' => 'Preposition'],
            'العصور' => ['root' => 'ع ص ر', 'mean' => 'The eras / ages', 'grammar' => 'Plural Noun'],
            'الوسطى' => ['root' => 'و س ط', 'mean' => 'The middle / medieval', 'grammar' => 'Adjective']
        ]])
    ],
    [
        'type' => 'reading', 'level' => 'advanced',
        'title' => 'Philosophy of Ibn Rushd', 'arabic_title' => 'فلسفة ابن رشد',
        'content' => 'يعتبر ابن رشد من أهم الفلاسفة الذين حاولوا التوفيق بين الدين والفلسفة.',
        'translation' => 'Ibn Rushd is considered one of the most important philosophers who tried to reconcile religion and philosophy.',
        'metadata' => json_encode(['words' => [
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
        ]])
    ],

    // --- NEW READING LESSONS ---
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'The Golden Age of Science',
        'arabic_title' => 'العصر الذهبي للعلوم',
        'content' => 'في العصر الذهبي، تفوق العلماء العرب في الرياضيات والفلك والطب.',
        'translation' => 'During the Golden Age, Arab scholars excelled in mathematics, astronomy, and medicine.',
        'metadata' => json_encode(['words' => [
            'العصر' => ['root'=>'ع ص ر','mean'=>'era','grammar'=>'Noun'],
            'الذهبي' => ['root'=>'ذ ه ب','mean'=>'golden','grammar'=>'Adjective'],
            'العلم' => ['root'=>'ع ل م','mean'=>'science','grammar'=>'Noun'],
            'الرياضيات' => ['root'=>'ر ب ع','mean'=>'mathematics','grammar'=>'Plural Noun'],
            'الفلك' => ['root'=>'ف ل ك','mean'=>'astronomy','grammar'=>'Noun'],
            'الطب' => ['root'=>'ط ب ب','mean'=>'medicine','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Islamic Architecture',
        'arabic_title' => 'العمارة الإسلامية',
        'content' => 'تتميز المساجد العربية بقبابها المهيبة ومآذنها الشاهقة.',
        'translation' => 'Arab mosques are known for their majestic domes and towering minarets.',
        'metadata' => json_encode(['words' => [
            'المساجد' => ['root'=>'س ج د','mean'=>'mosques','grammar'=>'Plural Noun'],
            'قباب' => ['root'=>'ق ب ب','mean'=>'domes','grammar'=>'Plural Noun'],
            'مآذن' => ['root'=>'أ ذ ن','mean'=>'minarets','grammar'=>'Plural Noun'],
            'عربية' => ['root'=>'ع ر ب','mean'=>'Arab','grammar'=>'Adjective']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'Classical Arabic Poetry',
        'arabic_title' => 'الشعر العربي الكلاسيكي',
        'content' => 'تجسد القصيدة العربية القديمة قيم الفخر والحكمة.',
        'translation' => 'Classical Arabic poetry embodies values of pride and wisdom.',
        'metadata' => json_encode(['words' => [
            'القصيدة' => ['root'=>'ق ص د','mean'=>'poem','grammar'=>'Noun'],
            'العربية' => ['root'=>'ع ر ب','mean'=>'Arabic','grammar'=>'Adjective'],
            'القديمة' => ['root'=>'ق د م','mean'=>'ancient','grammar'=>'Adjective'],
            'الفخر' => ['root'=>'ف خ ر','mean'=>'pride','grammar'=>'Noun'],
            'الحكمة' => ['root'=>'ح ك م','mean'=>'wisdom','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arab Contributions to Medicine',
        'arabic_title' => 'مساهمات العرب في الطب',
        'content' => 'طُورت العديد من الأدوات الطبية مثل الجراحة وعلوم الأدوية في العصور الوسطى.',
        'translation' => 'Many medical tools such as surgery and pharmacology were developed in the medieval era.',
        'metadata' => json_encode(['words' => [
            'الأدوات' => ['root'=>'د و ر','mean'=>'tools','grammar'=>'Plural Noun'],
            'الطب' => ['root'=>'ط ب ب','mean'=>'medicine','grammar'=>'Noun'],
            'الجراحة' => ['root'=>'ج ر ح','mean'=>'surgery','grammar'=>'Noun'],
            'الأدوية' => ['root'=>'د و ي','mean'=>'pharmaceuticals','grammar'=>'Plural Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Historical Trade Routes',
        'arabic_title' => 'طرق التجارة التاريخية',
        'content' => 'قادت طرق الحرير إلى تبادل سلع وثقافات بين الشرق والغرب.',
        'translation' => 'Silk routes facilitated exchange of goods and cultures between East and West.',
        'metadata' => json_encode(['words' => [
            'الحرير' => ['root'=>'ح ر ر','mean'=>'silk','grammar'=>'Noun'],
            'السلع' => ['root'=>'س ي ل','mean'=>'goods','grammar'=>'Plural Noun'],
            'الثقافات' => ['root'=>'ق ف ي','mean'=>'cultures','grammar'=>'Plural Noun'],
            'الشرق' => ['root'=>'ش ر ق','mean'=>'East','grammar'=>'Noun'],
            'الغرب' => ['root'=>'غ ر ب','mean'=>'West','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arabic Calligraphy',
        'arabic_title' => 'الخط العربي',
        'content' => 'تُعَدُّ الخطوط الهندسية مثل الثلث والكوفي من أجمل الفنون العربية.',
        'translation' => 'Geometric scripts like Thuluth and Kufi are among the most beautiful Arabic arts.',
        'metadata' => json_encode(['words' => [
            'الخطوط' => ['root'=>'خ ط ط','mean'=>'scripts','grammar'=>'Plural Noun'],
            'الهندسية' => ['root'=>'ه ن د س','mean'=>'geometric','grammar'=>'Adjective'],
            'الثلث' => ['root'=>'ث ل ث','mean'=>'Thuluth','grammar'=>'Proper Noun'],
            'الكوفي' => ['root'=>'ك ف ي','mean'=>'Kufi','grammar'=>'Proper Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'The Rise of Modern Arabic Literature',
        'arabic_title' => 'نهوض الأدب العربي الحديث',
        'content' => 'شهد الأدب العربي في القرن العشرين تطورات مع كتاب مثل نجيب محفوظ.',
        'translation' => 'In the 20th century, Arabic literature evolved with authors like Naguib Mahfouz.',
        'metadata' => json_encode(['words' => [
            'الأدب' => ['root'=>'د ب ر','mean'=>'literature','grammar'=>'Noun'],
            'العربي' => ['root'=>'ع ر ب','mean'=>'Arabic','grammar'=>'Adjective'],
            'الحديث' => ['root'=>'ح د ث','mean'=>'modern','grammar'=>'Adjective'],
            'نجيب محفوظ' => ['root'=>'N/A','mean'=>'Naguib Mahfouz','grammar'=>'Proper Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Arabic Proverbs',
        'arabic_title' => 'الأمثال العربية',
        'content' => '"الصبر مفتاح الفرج" مثال على حكمة عربية تُستَخدم في الحياة اليومية.',
        'translation' => '"Patience is the key to relief" is a common Arabic proverb used daily.',
        'metadata' => json_encode(['words' => [
            'الصبر' => ['root'=>'ص ب ر','mean'=>'patience','grammar'=>'Noun'],
            'مفتاح' => ['root'=>'ف ت ح','mean'=>'key','grammar'=>'Noun'],
            'الفرج' => ['root'=>'ف ر ج','mean'=>'relief','grammar'=>'Noun'],
            'مثال' => ['root'=>'م ث ل','mean'=>'example','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arabic Cuisine',
        'arabic_title' => 'المطبخ العربي',
        'content' => 'تشتهر المأكولات العربية بمأكولات مثل الكبة والفتوش.',
        'translation' => 'Arabic cuisine is famous for dishes like kibbeh and fattoush.',
        'metadata' => json_encode(['words' => [
            'المأكولات' => ['root'=>'أ ك ل','mean'=>'foods','grammar'=>'Plural Noun'],
            'الكبة' => ['root'=>'ك ب ة','mean'=>'kibbeh','grammar'=>'Noun'],
            'الفتوش' => ['root'=>'ف ت ش','mean'=>'fattoush','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'Arab Scientific Instruments',
        'arabic_title' => 'الأدوات العلمية العربية',
        'content' => 'اخترع العلماء العرب الساعات المائية والاسطرلاب لتحديد الوقت والموقع.',
        'translation' => 'Arab scholars invented water clocks and astrolabes for time and position.',
        'metadata' => json_encode(['words' => [
            'الساعات' => ['root'=>'س ع ة','mean'=>'clocks','grammar'=>'Plural Noun'],
            'المائية' => ['root'=>'م و ي','mean'=>'water','grammar'=>'Adjective'],
            'الاسطرلاب' => ['root'=>'N/A','mean'=>'astrolabe','grammar'=>'Noun'],
            'الموقع' => ['root'=>'و ج د','mean'=>'position','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Arab Folktales',
        'arabic_title' => 'القصص الشعبية العربية',
        'content' => 'تحكي القصص مثل علي بابا وجنته عن الذكاء والشجاعة.',
        'translation' => 'Stories like Ali Baba and the Forty Thieves talk about intelligence and bravery.',
        'metadata' => json_encode(['words' => [
            'القصص' => ['root'=>'ق ص ر','mean'=>'stories','grammar'=>'Plural Noun'],
            'الشعبية' => ['root'=>'ش ع ب','mean'=>'popular','grammar'=>'Adjective'],
            'علي بابا' => ['root'=>'N/A','mean'=>'Ali Baba','grammar'=>'Proper Noun'],
            'الذكاء' => ['root'=>'ذ ك ا','mean'=>'intelligence','grammar'=>'Noun'],
            'الشجاعة' => ['root'=>'ش ج ع','mean'=>'bravery','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arabian Night Tales',
        'arabic_title' => 'حكايات ليلة عربية',
        'content' => 'مجموعة قصص من "ألف ليلة وليلة" التي تجمع بين الخيال والتاريخ.',
        'translation' => 'A collection of stories from "One Thousand and One Nights" blending fantasy and history.',
        'metadata' => json_encode(['words' => [
            'مجموعة' => ['root'=>'ج م ع','mean'=>'collection','grammar'=>'Noun'],
            'الخيال' => ['root'=>'خ ي ل','mean'=>'fantasy','grammar'=>'Noun'],
            'التاريخ' => ['root'=>'ت ا ر خ','mean'=>'history','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'Arabic Music Instruments',
        'arabic_title' => 'آلات الموسيقى العربية',
        'content' => 'العود والربابة من أشهر الآلات الموسيقية في التراث العربي.',
        'translation' => 'The oud and rebab are among the most famous Arabic musical instruments.',
        'metadata' => json_encode(['words' => [
            'العود' => ['root'=>'ع و د','mean'=>'oud','grammar'=>'Noun'],
            'الربابة' => ['root'=>'ر ب ب','mean'=>'rebab','grammar'=>'Noun'],
            'التراث' => ['root'=>'ر ث ث','mean'=>'heritage','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Arabian Desert Life',
        'arabic_title' => 'حياة الصحراء العربية',
        'content' => 'يمتاز نمط الحياة في الصحراء بالبساطة والاعتماد على الإبل.',
        'translation' => 'Desert life is characterized by simplicity and reliance on camels.',
        'metadata' => json_encode(['words' => [
            'الصحراء' => ['root'=>'ص ح ر','mean'=>'desert','grammar'=>'Noun'],
            'الإبل' => ['root'=>'ب ل ل','mean'=>'camels','grammar'=>'Plural Noun'],
            'البساطة' => ['root'=>'ب س ط','mean'=>'simplicity','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arabian Mathematics',
        'arabic_title' => 'الرياضيات العربية',
        'content' => 'قدم العلماء العرب مفهوم الجبر والتفاضل في القرون الوسطى.',
        'translation' => 'Arab scholars introduced algebra and calculus concepts in the medieval period.',
        'metadata' => json_encode(['words' => [
            'الجبر' => ['root'=>'ج ب ر','mean'=>'algebra','grammar'=>'Noun'],
            'التفاضل' => ['root'=>'ف ض ل','mean'=>'calculus','grammar'=>'Noun'],
            'القرون' => ['root'=>'ق ر ن','mean'=>'centuries','grammar'=>'Plural Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'Arabian Astronomy',
        'arabic_title' => 'الفلك العربي',
        'content' => 'استخدم العلماء العرب النجوم لتحديد المواقع وتطوير التقويم.',
        'translation' => 'Arab astronomers used stars for navigation and calendar development.',
        'metadata' => json_encode(['words' => [
            'النجوم' => ['root'=>'ن ج م','mean'=>'stars','grammar'=>'Plural Noun'],
            'المواقع' => ['root'=>'و ق ع','mean'=>'locations','grammar'=>'Plural Noun'],
            'التقويم' => ['root'=>'ق و م','mean'=>'calendar','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'beginner',
        'title' => 'Arabic Hospitality',
        'arabic_title' => 'الضيافة العربية',
        'content' => 'تُعرف الثقافة العربية بالضيافة السخية والكرم.',
        'translation' => 'Arab culture is known for generous hospitality and generosity.',
        'metadata' => json_encode(['words' => [
            'الضيافة' => ['root'=>'ض ي ف','mean'=>'hospitality','grammar'=>'Noun'],
            'السخية' => ['root'=>'س خ ي','mean'=>'generous','grammar'=>'Adjective'],
            'الكرم' => ['root'=>'ك ر م','mean'=>'generosity','grammar'=>'Noun']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'intermediate',
        'title' => 'Arabian Poetry Forms',
        'arabic_title' => 'أشكال الشعر العربي',
        'content' => 'القصيدة المقطوعة والقصيدة الحرة من أشهر الأنماط.',
        'translation' => 'The qasida and free verse are popular poetic forms.',
        'metadata' => json_encode(['words' => [
            'القصيدة' => ['root'=>'ق ص د','mean'=>'poem','grammar'=>'Noun'],
            'المقطوعة' => ['root'=>'ق ط ع','mean'=>'qasida','grammar'=>'Noun'],
            'الحرّة' => ['root'=>'ح ر ر','mean'=>'free','grammar'=>'Adjective']
        ]]),
    ],
    [
        'type' => 'reading',
        'level' => 'advanced',
        'title' => 'Arabian Trade Goods',
        'arabic_title' => 'سلع التجارة العربية',
        'content' => 'التوابل والحرير والعنبر كانت من أهم السلع في الأسواق العربية.',
        'translation' => 'Spices, silk, and amber were key trade goods in Arab markets.',
        'metadata' => json_encode(['words' => [
            'التوابل' => ['root'=>'و ب ل','mean'=>'spices','grammar'=>'Plural Noun'],
            'العنبر' => ['root'=>'ن ب ر','mean'=>'amber','grammar'=>'Noun'],
            'الأسواق' => ['root'=>'س و ق','mean'=>'markets','grammar'=>'Plural Noun']
        ]]),
    ],

    ['type' => 'writing', 'level' => 'beginner', 'title' => 'Personal Intro', 'arabic_title' => 'التعريف بالنفس', 'content' => 'Write a short paragraph introducing yourself, your hobby, and your favorite city.'],
    ['type' => 'writing', 'level' => 'intermediate', 'title' => 'Formal Email', 'arabic_title' => 'بريد إلكتروني رسمي', 'content' => 'Write a formal email to a company applying for an internship in the Translation department.'],
    ['type' => 'writing', 'level' => 'advanced', 'title' => 'Political Essay', 'arabic_title' => 'مقال سياسي', 'content' => 'Analyze the impact of globalization on traditional Arab culture in 200 words.'],

    // --- SPEAKING EXERCISES ---
    ['type' => 'speaking', 'level' => 'beginner', 'title' => 'Daily Greetings', 'arabic_title' => 'التحيات اليومية', 'content' => 'السلام عليكم ورحمة الله وبركاته'],
    ['type' => 'speaking', 'level' => 'intermediate', 'title' => 'Wisdom of the Day', 'arabic_title' => 'حكمة اليوم', 'content' => 'من جد وجد ومن زرع حصد'],
    ['type' => 'speaking', 'level' => 'advanced', 'title' => 'Complex Articulation', 'arabic_title' => 'النطق المعقد', 'content' => 'لا يُستطاع العلم براحة الجسم'],

    // --- LISTENING MODULES ---
    [
        'type' => 'listening', 'level' => 'beginner',
        'title' => 'Weather Report', 'arabic_title' => 'حالة الطقس',
        'content' => 'الطقس اليوم مشمس وجميل في مدينة دبي.',
        'metadata' => json_encode(['quiz' => [
            'question' => 'How is the weather in Dubai today?',
            'options' => ['Rainy', 'Cloudy', 'Sunny and Beautiful', 'Stormy'],
            'correct' => 2
        ]])
    ],
    [
        'type' => 'listening', 'level' => 'intermediate',
        'title' => 'Sports News', 'arabic_title' => 'أخبار الرياضة',
        'content' => 'فاز المنتخب الوطني بالمباراة النهائية بعد أداء رائع.',
        'metadata' => json_encode(['quiz' => [
            'question' => 'What did the national team win?',
            'options' => ['A Friendly Match', 'The Final Match', 'The League', 'A Training Session'],
            'correct' => 1
        ]])
    ]
];

try {
    $stmtCheck = $pdo->prepare("SELECT id FROM academy_lessons WHERE type = ? AND title = ?");
    $stmtInsert = $pdo->prepare("INSERT INTO academy_lessons (type, level, title, arabic_title, content, translation, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtUpdate = $pdo->prepare("UPDATE academy_lessons SET level = ?, arabic_title = ?, content = ?, translation = ?, metadata = ? WHERE id = ?");
    
    foreach ($lessons as $l) {
        $stmtCheck->execute([$l['type'], $l['title']]);
        $existing = $stmtCheck->fetch();
        if ($existing) {
            $stmtUpdate->execute([
                $l['level'],
                $l['arabic_title'],
                $l['content'],
                $l['translation'] ?? null,
                $l['metadata'] ?? null,
                $existing['id']
            ]);
            echo "🔄 Updated: {$l['title']} ({$l['type']})<br>";
        } else {
            $stmtInsert->execute([
                $l['type'],
                $l['level'],
                $l['title'],
                $l['arabic_title'],
                $l['content'],
                $l['translation'] ?? null,
                $l['metadata'] ?? null
            ]);
            echo "✅ Inserted: {$l['title']} ({$l['type']})<br>";
        }
    }

    echo "<h3>🚀 Seeding Complete!</h3>";
    echo "<p>Your Academy is now full of content.</p>";

} catch (Exception $e) {
    echo "❌ Seeding Failed: " . $e->getMessage();
}
?>
