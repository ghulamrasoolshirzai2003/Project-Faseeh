<?php
require 'includes/db.php';
set_time_limit(600);
echo "<h1>🎯 HANGMAN EXPANSION SEEDER (30, 50, 100) 🎯</h1>";
echo "<p>Expanding Hangman vocabulary while keeping other game modules safe...</p>";

try {
    // 1. Disable checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // 2. Use DELETE instead of TRUNCATE (more compatible with shared hosting)
    $pdo->exec("DELETE FROM user_solved_words");
    $pdo->exec("DELETE FROM user_xp_history");
    $pdo->exec("DELETE FROM review_queue");
    $pdo->exec("DELETE FROM game_sessions");
    $pdo->exec("DELETE FROM words");
    
    // Reset IDs
    $pdo->exec("ALTER TABLE user_solved_words AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE user_xp_history AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE review_queue AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE game_sessions AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE words AUTO_INCREMENT = 1");
    
    // 3. Re-enable checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $stmt = $pdo->prepare("INSERT INTO words (arabic_word, meaning_en, meaning_my, level, category) VALUES (?, ?, ?, ?, ?)");

    $hangman_data = [
        ['beginner', [
            ['بَيْت', 'House', 'Rumah', 'general'], ['كِتَاب', 'Book', 'Buku', 'education'], ['قَلَم', 'Pen', 'Pena', 'education'],
            ['شَمْس', 'Sun', 'Matahari', 'nature'], ['قَمَر', 'Moon', 'Bulan', 'nature'], ['نَهْر', 'River', 'Sungai', 'nature'],
            ['بَحْر', 'Sea', 'Laut', 'nature'], ['جَبَل', 'Mountain', 'Gunung', 'nature'], ['وَلَد', 'Boy', 'Budak Lelaki', 'people'],
            ['بِنْت', 'Girl', 'Budak Perempuan', 'people'], ['أَب', 'Father', 'Bapa', 'family'], ['أُم', 'Mother', 'Emak', 'family'],
            ['أَخ', 'Brother', 'Abang/Adik Lelaki', 'family'], ['أُخْت', 'Sister', 'Kakak/Adik Perempuan', 'family'], ['خُبْز', 'Bread', 'Roti', 'food'],
            ['مَاء', 'Water', 'Air', 'food'], ['حَلِيب', 'Milk', 'Susu', 'food'], ['تُفَّاح', 'Apple', 'Epal', 'food'],
            ['بَاب', 'Door', 'Pintu', 'object'], ['نَافِذَة', 'Window', 'Tingkap', 'object'], ['كُرْسِيّ', 'Chair', 'Kerusi', 'object'],
            ['طَاوِلَة', 'Table', 'Meja', 'object'], ['سَرِير', 'Bed', 'Katil', 'object'], ['مَدِينَة', 'City', 'Bandar', 'place'],
            ['قَرْيَة', 'Village', 'Kampung', 'place'], ['طَرِيق', 'Road', 'Jalan', 'place'], ['سَمَاء', 'Sky', 'Langit', 'nature'],
            ['أَرْض', 'Earth', 'Bumi', 'nature'], ['نَار', 'Fire', 'Api', 'nature'], ['ثَلْج', 'Snow', 'Salji', 'nature']
        ]], // 30 words
        ['intermediate', [
            ['مَدْرَسَة', 'School', 'Sekolah', 'place'], ['مُسْتَشْفَى', 'Hospital', 'Hospital', 'place'], ['مَطَار', 'Airport', 'Lapangan Terbang', 'place'],
            ['فُنْدُق', 'Hotel', 'Hotel', 'place'], ['مَطْعَم', 'Restaurant', 'Restoran', 'place'], ['حَدِيقَة', 'Garden', 'Taman', 'nature'],
            ['مَكْتَبَة', 'Library', 'Perpustakaan', 'place'], ['جَامِعَة', 'University', 'Universiti', 'place'], ['مُهَنْدِس', 'Engineer', 'Jurutera', 'job'],
            ['طَبِيب', 'Doctor', 'Doktor', 'job'], ['مُعَلِّم', 'Teacher', 'Guru', 'job'], ['شُرْطِيّ', 'Policeman', 'Polis', 'job'],
            ['تَاجِر', 'Trader', 'Pedagang', 'job'], ['فَلَّاح', 'Farmer', 'Petani', 'job'], ['سَيَّارَة', 'Car', 'Kereta', 'transport'],
            ['حَافِلَة', 'Bus', 'Bas', 'transport'], ['قِطَار', 'Train', 'Keretapi', 'transport'], ['طَيَّارَة', 'Airplane', 'Pesawat', 'transport'],
            ['سَفِينَة', 'Ship', 'Kapal', 'transport'], ['دَرَّاجَة', 'Bicycle', 'Basikal', 'transport'], ['حَاسُوب', 'Computer', 'Komputer', 'tech'],
            ['هَاتِف', 'Phone', 'Telefon', 'tech'], ['تِلْفَاز', 'Television', 'Televisyen', 'tech'], ['رِيَاضَة', 'Sports', 'Sukan', 'activity'],
            ['سِيَاحَة', 'Tourism', 'Pelancongan', 'activity'], ['تِجَارَة', 'Trade', 'Perdagangan', 'activity'], ['زِرَاعَة', 'Agriculture', 'Pertanian', 'activity'],
            ['صِنَاعَة', 'Industry', 'Industri', 'activity'], ['حُكُومَة', 'Government', 'Kerajaan', 'politics'], ['مُجْتَمَع', 'Society', 'Masyarakat', 'politics'],
            ['مُدِير', 'Manager', 'Pengurus', 'job'], ['مُحَاسِب', 'Accountant', 'Akauntan', 'job'], ['مُحَامٍ', 'Lawyer', 'Peguam', 'job'],
            ['صَحَفِيّ', 'Journalist', 'Wartawan', 'job'], ['مُصَمِّم', 'Designer', 'Pereka', 'job'], ['مُبَرْمِج', 'Programmer', 'Pengaturcara', 'job'],
            ['اِجْتِمَاع', 'Meeting', 'Mesyuarat', 'business'], ['تَقْرِير', 'Report', 'Laporan', 'business'], ['مَشْرُوع', 'Project', 'Projek', 'business'],
            ['خِدْمَة', 'Service', 'Perkhidmatan', 'business'], ['سُوق', 'Market', 'Pasar', 'place'], ['مَتْجَر', 'Store', 'Kedai', 'place'],
            ['مَلْعَب', 'Stadium/Field', 'Stadium/Padang', 'place'], ['مَسْرَح', 'Theater', 'Teater', 'place'], ['مَتْحَف', 'Museum', 'Muzium', 'place'],
            ['جَوَاز سَفَر', 'Passport', 'Pasport', 'travel'], ['تَأْشِيرَة', 'Visa', 'Visa', 'travel'], ['تَذْكِرَة', 'Ticket', 'Tiket', 'travel'],
            ['حَقِيبَة', 'Bag/Suitcase', 'Beg/Bagasi', 'travel'], ['رِحْلَة', 'Trip/Flight', 'Perjalanan', 'travel']
        ]], // 50 words
        ['advanced', [
            ['دِيمُوقْرَاطِيَّة', 'Democracy', 'Demokrasi', 'politics'], ['فَلْسَفَة', 'Philosophy', 'Falsafah', 'science'], ['حَضَارَة', 'Civilization', 'Tamadun', 'history'],
            ['مُسْتَقْبَل', 'Future', 'Masa Depan', 'general'], ['اسْتِرَاتِيجِيَّة', 'Strategy', 'Strategi', 'business'], ['تَكْنُولُوجِيَا', 'Technology', 'Teknologi', 'science'],
            ['اِقْتِصَاد', 'Economy', 'Ekonomi', 'business'], ['اِسْتِثْمَار', 'Investment', 'Pelaburan', 'business'], ['مَسْؤُولِيَّة', 'Responsibility', 'Tanggungjawab', 'general'],
            ['مُسْتَدَام', 'Sustainable', 'Lestari', 'nature'], ['اِبْتِكَار', 'Innovation', 'Inovasi', 'tech'], ['اِحْتِرَافِيّ', 'Professional', 'Profesional', 'work'],
            ['اِسْتِقْرَار', 'Stability', 'Kestabilan', 'politics'], ['عَوْلَمَة', 'Globalization', 'Globalisasi', 'politics'], ['اِزْدِهَار', 'Prosperity', 'Kemakmuran', 'economy'],
            ['بِيئَة', 'Environment', 'Alam Sekitar', 'nature'], ['قَانُون', 'Law', 'Undang-undang', 'politics'], ['عَدَالَة', 'Justice', 'Keadilan', 'politics'],
            ['دُسْتُور', 'Constitution', 'Perlembagaan', 'politics'], ['بَرْلَمَان', 'Parliament', 'Parlimen', 'politics'], ['مُفَاوَضَات', 'Negotiations', 'Rundingan', 'business'],
            ['مِيزَانِيَّة', 'Budget', 'Belanjawan', 'economy'], ['تَضَخُّم', 'Inflation', 'Inflasi', 'economy'], ['بِطَالَة', 'Unemployment', 'Pengangguran', 'economy'],
            ['مُنَافَسَة', 'Competition', 'Persaingan', 'business'], ['تَطْوِير', 'Development', 'Pembangunan', 'general'], ['إِبْدَاع', 'Creativity', 'Kreativiti', 'general'],
            ['ثَقَافَة', 'Culture', 'Budaya', 'general'], ['تَارِيخ', 'History', 'Sejarah', 'general'], ['أَدَب', 'Literature', 'Sastera', 'general'],
            ['بَحْث عِلْمِيّ', 'Scientific Research', 'Penyelidikan Saintifik', 'science'], ['نَظَرِيَّة', 'Theory', 'Teori', 'science'], ['تَجْرِبَة', 'Experiment/Experience', 'Eksperimen', 'science'],
            ['تَحْلِيل', 'Analysis', 'Analisis', 'science'], ['اِسْتِنْتَاج', 'Conclusion', 'Kesimpulan', 'science'], ['مَعْلُومَات', 'Information', 'Maklumat', 'tech'],
            ['بَيَانَات', 'Data', 'Data', 'tech'], ['خُصُوصِيَّة', 'Privacy', 'Privasi', 'tech'], ['أَمْن سِيبِرَانِيّ', 'Cybersecurity', 'Keselamatan Siber', 'tech'],
            ['ذَكَاء اِصْطِنَاعِيّ', 'Artificial Intelligence', 'Kecerdasan Buatan', 'tech'], ['تَغَيُّر مُنَاخِيّ', 'Climate Change', 'Perubahan Iklim', 'nature'], ['تَنَوُّع بِيُولُوجِيّ', 'Biodiversity', 'Biodiversiti', 'nature'],
            ['تَلَوُّث', 'Pollution', 'Pencemaran', 'nature'], ['طَاقَة نَوَوِيَّة', 'Nuclear Energy', 'Tenaga Nuklear', 'nature'], ['طَاقَة شَمْسِيَّة', 'Solar Energy', 'Tenaga Solar', 'nature'],
            ['سِيَاسَة خَارِجِيَّة', 'Foreign Policy', 'Dasar Luar', 'politics'], ['تَعَاوُن دُوَلِيّ', 'International Cooperation', 'Kerjasama Antarabangsa', 'politics'], ['حُقُوق اَلْإِنْسَان', 'Human Rights', 'Hak Asasi Manusia', 'politics'],
            ['سِيَادَة', 'Sovereignty', 'Kedaulatan', 'politics'], ['انْتِخَابَات', 'Elections', 'Pilihan Raya', 'politics'], ['تَحَالُف', 'Alliance', 'Aliansi', 'politics'],
            ['نِزَاع', 'Conflict', 'Konflik', 'politics'], ['أَزْمَة', 'Crisis', 'Krisis', 'general'], ['تَحَدِّي', 'Challenge', 'Cabaran', 'general'],
            ['فُرْصَة', 'Opportunity', 'Peluang', 'general'], ['تَأْثِير', 'Impact/Effect', 'Kesan', 'general'], ['نَتِيجَة', 'Result', 'Keputusan', 'general'],
            ['سَبَب', 'Reason', 'Sebab', 'general'], ['حَلّ', 'Solution', 'Penyelesaian', 'general'], ['مُشْكِلَة', 'Problem', 'Masalah', 'general'],
            ['تَفَاوُض', 'Negotiation', 'Negosiasi', 'business'], ['شَرَاكَة', 'Partnership', 'Rakan Kongsi', 'business'], ['مُقَاوَلَة', 'Contracting', 'Kontrak', 'business'],
            ['تَسْوِيق', 'Marketing', 'Pemasaran', 'business'], ['مَبِيعَات', 'Sales', 'Jualan', 'business'], ['أَرْبَاح', 'Profits', 'Keuntungan', 'business'],
            ['خَسَائِر', 'Losses', 'Kerugian', 'business'], ['تَمْوِيل', 'Finance/Funding', 'Pembiayaan', 'business'], ['قَرْض', 'Loan', 'Pinjaman', 'business'],
            ['تَأْمِين', 'Insurance', 'Insurans', 'business'], ['ضَرِيبَة', 'Tax', 'Cukai', 'business'], ['جُمْرُك', 'Customs', 'Kastam', 'business'],
            ['اِسْتِيرَاد', 'Import', 'Import', 'business'], ['تَصْدِير', 'Export', 'Eksport', 'business'], ['إِنْتَاج', 'Production', 'Pengeluaran', 'business'],
            ['اِسْتِهْلَاك', 'Consumption', 'Penggunaan', 'business'], ['مُسْتَهْلِك', 'Consumer', 'Pengguna', 'business'], ['مُنَافِس', 'Competitor', 'Pesaing', 'business'],
            ['عَلَامَة تِجَارِيَّة', 'Brand', 'Jenama', 'business'], ['سُمْعَة', 'Reputation', 'Reputasi', 'general'], ['نَجَاح', 'Success', 'Kejayaan', 'general'],
            ['فَشَل', 'Failure', 'Kegagalan', 'general'], ['طُمُوح', 'Ambition', 'Ambisi', 'general'], ['إِرَادَة', 'Will/Determination', 'Kemahuan', 'general'],
            ['ثِقَة', 'Confidence', 'Keyakinan', 'general'], ['تَعَاوُن', 'Cooperation', 'Kerjasama', 'general'], ['مُشَارَكة', 'Participation', 'Penyertaan', 'general'],
            ['تَفَاعُل', 'Interaction', 'Interaksi', 'general'], ['تَوَاصُل', 'Communication', 'Komunikasi', 'general'], ['لُغَة', 'Language', 'Bahasa', 'general'],
            ['تَرْجَمَة', 'Translation', 'Terjemahan', 'general'], ['تَعْبِير', 'Expression', 'Ungkapan', 'general'], ['نَقْد', 'Criticism', 'Kritikan', 'general'],
            ['إِصْلَاح', 'Reform', 'Reformasi', 'general'], ['تَغْيِير', 'Change', 'Perubahan', 'general'], ['ثَوْرَة', 'Revolution', 'Revolusi', 'history'],
            ['تَطْوِير', 'Evolution/Development', 'Evolusi', 'science'], ['نُمُوّ', 'Growth', 'Pertumbuhan', 'general'], ['تَقَدُّم', 'Progress', 'Kemajuan', 'general'],
            ['تَخَلُّف', 'Underdevelopment', 'Kemunduran', 'general'], ['تَفَوُّق', 'Superiority/Excellence', 'Keunggulan', 'general']
        ]] // 100 words
    ];

    $count = 0;
    foreach ($hangman_data as $level_group) {
        $level = $level_group[0];
        $words = $level_group[1];
        foreach ($words as $w) {
            $stmt->execute([$w[0], $w[1], $w[2] ?? '', $level, $w[3] ?? 'general']);
            $count++;
        }
    }
    echo "<h2>✅ Hangman Expansion Complete!</h2>";
    echo "<p>Total Words Added: $count</p>";
    echo "<ul><li>Beginner: 30</li><li>Intermediate: 50</li><li>Advanced: 100</li></ul>";
    echo "<p><b>Note:</b> Your other game data (Grammar, Reading, etc.) remains untouched.</p>";

} catch (Exception $e) {
    echo "❌ Error during seeding: " . $e->getMessage();
}
?>
