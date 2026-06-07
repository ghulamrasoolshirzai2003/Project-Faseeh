-- 1. DROP OLD TABLES (Start Fresh)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `game_sessions`, `review_queue`, `daily_goals`, `user_achievements`, `achievements`, `user_progress`, `progress`, `words`, `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. USERS TABLE
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `selected_level` varchar(20) DEFAULT 'beginner',
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. WORDS TABLE
CREATE TABLE `words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arabic_word` varchar(100) NOT NULL,
  `meaning_en` varchar(200) NOT NULL,
  `meaning_my` varchar(200) DEFAULT NULL,
  `root` varchar(50) DEFAULT NULL,
  `level` varchar(20) DEFAULT 'beginner',
  `category` varchar(50) DEFAULT 'general',
  `audio_file` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. PROGRESS TABLE
CREATE TABLE `progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_score` int(11) DEFAULT 0,
  `xp` int(11) DEFAULT 0,
  `current_streak` int(11) DEFAULT 0,
  `daily_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `total_words_learned` int(11) DEFAULT 0,
  `accuracy_total` int(11) DEFAULT 0,
  `accuracy_correct` int(11) DEFAULT 0,
  `wins` int(11) DEFAULT 0,
  `mcq_wins` int(11) DEFAULT 0,
  `mcq_losses` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `last_active` datetime DEFAULT NULL,
  `last_play_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. USER PROGRESS (SOLVED WORDS)
CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `word_id` int(11) NOT NULL,
  `solved_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_word` (`user_id`, `word_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`word_id`) REFERENCES `words` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. ACHIEVEMENTS SYSTEM
CREATE TABLE `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL UNIQUE,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(10) DEFAULT '🏅',
  `xp_reward` int(11) DEFAULT 0,
  `requirement_type` varchar(50) DEFAULT NULL,
  `requirement_value` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `unlocked_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ua` (`user_id`,`achievement_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. SPACED REPETITION & ENGAGEMENT
CREATE TABLE `daily_goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `goal_date` date NOT NULL,
  `words_target` int(11) DEFAULT 5,
  `words_completed` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_goal` (`user_id`,`goal_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `review_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `word_id` int(11) NOT NULL,
  `ease_factor` decimal(3,2) DEFAULT 2.50,
  `interval_days` int(11) DEFAULT 1,
  `repetitions` int(11) DEFAULT 0,
  `next_review` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_review` (`user_id`,`word_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`word_id`) REFERENCES `words` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. ANALYTICS
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_type` enum('hangman','mcq','review') DEFAULT 'hangman',
  `word_id` int(11) DEFAULT NULL,
  `result` enum('win','lose') NOT NULL,
  `time_taken` int(11) DEFAULT 0,
  `score_change` int(11) DEFAULT 0,
  `played_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. SEED DATA (Achievements)
INSERT INTO `achievements` (`slug`, `title`, `description`, `icon`, `xp_reward`, `requirement_type`, `requirement_value`) VALUES
('first_word', 'First Word', 'Complete your first word', '🌟', 10, 'total_words', 1),
('ten_words', 'Vocabulary Builder', 'Learn 10 words', '📚', 25, 'total_words', 10),
('fifty_words', 'Word Scholar', 'Learn 50 words', '🎓', 100, 'total_words', 50),
('hundred_words', 'Century', 'Learn 100 words', '💯', 250, 'total_words', 100),
('streak_3', 'Getting Warm', '3 day daily streak', '🔥', 15, 'daily_streak', 3),
('streak_7', 'On Fire', '7 day daily streak', '🔥', 50, 'daily_streak', 7),
('streak_30', 'Unstoppable', '30 day daily streak', '💎', 200, 'daily_streak', 30),
('first_win', 'First Victory', 'Win your first game', '🏆', 10, 'wins', 1),
('ten_wins', 'Champion', 'Win 10 games', '🏆', 50, 'wins', 10),
('fifty_wins', 'Grandmaster', 'Win 50 games', '👑', 150, 'wins', 50),
('speed_demon', 'Speed Demon', 'Complete a word in under 10 seconds', '⚡', 30, 'speed', 10),
('perfect_mcq', 'Perfect Round', 'Get 10/10 in MCQ mode', '✨', 75, 'perfect_mcq', 1),
('accuracy_90', 'Sharp Mind', 'Maintain 90% accuracy', '🎯', 100, 'accuracy', 90),
('score_500', 'Rising Star', 'Reach 500 total score', '⭐', 50, 'total_score', 500),
('score_1000', 'Legend', 'Reach 1000 total score', '🌟', 150, 'total_score', 1000),
('all_beginner', 'Beginner Complete', 'Complete all beginner words', '🌱', 100, 'level_complete', 1),
('all_intermediate', 'Intermediate Complete', 'Complete all intermediate words', '🚀', 200, 'level_complete', 2),
('all_advanced', 'Advanced Complete', 'Complete all advanced words', '🔥', 500, 'level_complete', 3);

-- 10. SEED DATA (Starter Words)
INSERT INTO `words` (`arabic_word`, `meaning_en`, `meaning_my`, `root`, `level`, `category`) VALUES
('كِتَاب', 'Book', 'Buku', 'ك-ت-ب', 'beginner', 'education'),
('قَلَم', 'Pen', 'Pena', 'ق-ل-م', 'beginner', 'education'),
('بَيْت', 'House', 'Rumah', 'ب-ي-ت', 'beginner', 'general'),
('شَمْس', 'Sun', 'Matahari', 'ش-م-س', 'beginner', 'nature'),
('قَمَر', 'Moon', 'Bulan', 'ق-م-ر', 'beginner', 'nature'),
('مَدْرَسَة', 'School', 'Sekolah', 'د-ر-س', 'intermediate', 'education'),
('مُسْتَشْفَى', 'Hospital', 'Hospital', 'ش-ف-ي', 'intermediate', 'place'),
('طَيَّارَة', 'Airplane', 'Pesawat', 'ط-ي-ر', 'intermediate', 'transport'),
('سَيَّارَة', 'Car', 'Kereta', 'س-ي-ر', 'intermediate', 'transport'),
('حَدِيقَة', 'Garden', 'Taman', 'ح-د-ق', 'intermediate', 'nature'),
('تَكْنُولُوجِيَا', 'Technology', 'Teknologi', NULL, 'advanced', 'science'),
('دِيمُوقْرَاطِيَّة', 'Democracy', 'Demokrasi', NULL, 'advanced', 'politics'),
('فَلْسَفَة', 'Philosophy', 'Falsafah', NULL, 'advanced', 'science'),
('حَضَارَة', 'Civilization', 'Tamadun', 'ح-ض-ر', 'advanced', 'history'),
('مُسْتَقْبَل', 'Future', 'Masa Depan', 'ق-ب-ل', 'advanced', 'general');

-- 11. SEED DATA (Default Admin - Password is '123')
INSERT INTO `users` (`full_name`, `username`, `email`, `password`, `role`) VALUES
('System Admin', 'admin', 'admin@faseeh.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrQOufMZaHUKcVqiJ9A2.HlHh3Ib8F2dMlO', 'admin');
