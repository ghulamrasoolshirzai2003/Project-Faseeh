<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

// If user selects a level, save to session
if(isset($_GET['level'])) {
    $_SESSION['academic_level'] = $_GET['level'];
    header("Location: academic_hub.php");
    exit;
}

if(isset($_GET['length'])) {
    $length = (int)$_GET['length'];
    $_SESSION['quiz_length'] = $length;
    
    // PERSISTENCE: Update target length but DO NOT wipe progress
    $stmt = $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'grammar', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?");
    $stmt->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'sentence_builder', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'error_correction', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'root_word', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'conjugator', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'dictation', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'vocab_match', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);
    $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, 'reading', 0, ?) ON DUPLICATE KEY UPDATE total_target = ?")->execute([$_SESSION['user_id'], $length, $length]);

    header("Location: academic_hub.php");
    exit;
}

if(isset($_GET['restart'])) {
    $mode = $_GET['restart'];
    $length = $_SESSION['quiz_length'] ?? 10;
    
    // Start fresh game by resetting progress to 0
    $stmt = $pdo->prepare("INSERT INTO user_active_sessions (user_id, mode, questions_completed, total_target) VALUES (?, ?, 0, ?) ON DUPLICATE KEY UPDATE questions_completed = 0, total_target = ?");
    $stmt->execute([$_SESSION['user_id'], $mode, $length, $length]);
    
    header("Location: {$mode}.php");
    exit;
}

// Fetch current progress for all modes
$stmt = $pdo->prepare("SELECT mode, questions_completed, total_target FROM user_active_sessions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$sessions = [];
while ($row = $stmt->fetch()) {
    $sessions[$row['mode']] = $row;
}

// Academic Rank Calc (Based ONLY on Correct Answers)
try { $pdo->exec("ALTER TABLE progress ADD COLUMN academic_correct_count INT DEFAULT 0"); } catch(Exception $e){}
$ansStmt = $pdo->prepare("SELECT academic_correct_count FROM progress WHERE user_id = ?");
$ansStmt->execute([$_SESSION['user_id']]);
$academic_q_count = $ansStmt->fetch()['academic_correct_count'] ?? 0;

$academic_ranks = [
    ["name" => "مبتدئ (Novice)", "min_q" => 0, "color" => "#bdc3c7", "icon" => "🌱"],
    ["name" => "متدرب (Apprentice)", "min_q" => 50, "color" => "#2ecc71", "icon" => "📘"],
    ["name" => "طالب علم (Scholar)", "min_q" => 150, "color" => "#3498db", "icon" => "🎓"],
    ["name" => "نحوي (Grammarian)", "min_q" => 300, "color" => "#9b59b6", "icon" => "✒️"],
    ["name" => "لغوي (Linguist)", "min_q" => 600, "color" => "#e67e22", "icon" => "📜"],
    ["name" => "أستاذ (Master)", "min_q" => 1000, "color" => "#e74c3c", "icon" => "🏛️"],
    ["name" => "فصيح (Legend)", "min_q" => 1500, "color" => "#f1c40f", "icon" => "👑"]
];
$current_rank = $academic_ranks[0];
$next_rank = $academic_ranks[1] ?? null;
foreach ($academic_ranks as $i => $r) {
    if ($academic_q_count >= $r['min_q']) {
        $current_rank = $r;
        $next_rank = $academic_ranks[$i + 1] ?? null;
    }
}

$level = $_SESSION['academic_level'] ?? 'beginner';
$quizLength = $_SESSION['quiz_length'] ?? 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faseeh Academic Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --gold: #f1c40f;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light));
            color: white; min-height: 100vh;
        }

        .nav {
            padding: 20px 40px; display: flex; justify-content: space-between;
            align-items: center; background: rgba(0,0,0,0.2); backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }
        .nav a { color: white; text-decoration: none; font-weight: 600; padding: 8px 15px; border-radius: 10px; transition: 0.3s; }
        .nav a:hover { background: var(--glass); }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-family: 'Amiri', serif; font-size: 3rem; color: var(--gold); margin-bottom: 10px; }
        .header p { opacity: 0.7; font-size: 1.1rem; }

        .level-selector { display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; flex-wrap: wrap; }
        .lvl-btn {
            padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600;
            background: var(--glass); color: rgba(255,255,255,0.7); border: 1px solid var(--glass-border);
            transition: all 0.3s; font-size: 0.95rem;
        }
        .lvl-btn:hover { color: white; background: rgba(255,255,255,0.1); }
        .lvl-btn.active { background: var(--accent); color: white; border-color: var(--accent); box-shadow: 0 5px 15px rgba(242,153,74,0.4); }

        .length-selector { display: flex; justify-content: center; gap: 10px; margin-bottom: 40px; }
        .len-btn { padding: 8px 20px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; font-weight: 600; background: rgba(0,0,0,0.3); color: rgba(255,255,255,0.6); border: 1px solid var(--glass-border); transition: 0.3s; }
        .len-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .len-btn.active { background: var(--success); color: white; border-color: var(--success); box-shadow: 0 5px 15px rgba(46,204,113,0.3); }

        .modes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .mode-card {
            background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px;
            padding: 30px; text-align: center; text-decoration: none; color: white;
            transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column; align-items: center;
        }
        .mode-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); border-color: var(--gold); }
        .mode-icon { font-size: 3.5rem; margin-bottom: 15px; }
        .mode-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 10px; }
        .mode-desc { font-size: 0.9rem; opacity: 0.6; line-height: 1.5; margin-bottom: 20px; }
        
        .mode-btn {
            margin-top: auto; padding: 10px 25px; border-radius: 12px; background: rgba(0,0,0,0.3);
            font-weight: 600; border: 1px solid var(--glass-border); color: white; transition: 0.3s;
        }
        .mode-card:hover .mode-btn { background: var(--accent); border-color: var(--accent); }

        .coming-soon { opacity: 0.5; pointer-events: none; filter: grayscale(1); }
    </style>
</head>
<body>

    <div class="nav">
        <a href="level_select.php">← Main Menu</a>
        <div style="font-weight: 700; color: var(--gold);">FASEEH ACADEMIC</div>
    </div>

    <div class="container">
        <div class="header">
            <h1>أكاديمية فصيح</h1>
            <p>Master Arabic syntax, morphology, and grammar.</p>
            <div style="margin-top: 20px; display: inline-block; background: var(--glass); padding: 10px 25px; border-radius: 50px; border: 1px solid var(--glass-border);">
                <div style="font-size: 0.85rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Your Academic Rank</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: <?= $current_rank['color'] ?>;">
                    <?= $current_rank['icon'] ?> <?= $current_rank['name'] ?>
                </div>
                <div style="font-size: 0.85rem; margin-top: 5px;">
                    <strong><?= $academic_q_count ?></strong> correct answers
                    <?php if($next_rank): ?>
                        <span style="opacity: 0.5;">• <?= ($next_rank['min_q'] - $academic_q_count) ?> more to <?= $next_rank['name'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="level-selector">
            <a href="?level=beginner" class="lvl-btn <?= $level=='beginner'?'active':'' ?>">Beginner (مبتدئ)</a>
            <a href="?level=intermediate" class="lvl-btn <?= $level=='intermediate'?'active':'' ?>">Intermediate (متوسط)</a>
            <a href="?level=advanced" class="lvl-btn <?= $level=='advanced'?'active':'' ?>">Advanced (متقدم)</a>
        </div>

        <div style="text-align: center; margin-bottom: 15px; font-size: 0.9rem; opacity: 0.7; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Session Length</div>
        <div class="length-selector">
            <a href="?length=10" class="len-btn <?= $quizLength==10?'active':'' ?>">⚡ Fast (10)</a>
            <a href="?length=50" class="len-btn <?= $quizLength==50?'active':'' ?>">📚 Normal (50)</a>
            <a href="?length=100" class="len-btn <?= $quizLength==100?'active':'' ?>">🏆 Marathon (100)</a>
        </div>

        <div class="modes-grid">
            
            <div class="mode-card">
                <div class="mode-icon">🌳</div>
                <div class="mode-title">Root Word Finder</div>
                <div class="mode-desc">Extract the original 3-letter Arabic root from complex derived words.</div>
                <?php
                $r_sess = $sessions['root_word'] ?? null;
                if ($r_sess && $r_sess['questions_completed'] > 0 && $r_sess['questions_completed'] < $r_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="root_word.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $r_sess['questions_completed'] ?>/<?= $r_sess['total_target'] ?>)</a>
                        <a href="?restart=root_word" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=root_word" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🏛️</div>
                <div class="mode-title">Fill-in-the-Blanks</div>
                <div class="mode-desc">Test your knowledge of conjugations and grammar rules in context.</div>
                <?php
                $g_sess = $sessions['grammar'] ?? null;
                if ($g_sess && $g_sess['questions_completed'] > 0 && $g_sess['questions_completed'] < $g_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="grammar.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $g_sess['questions_completed'] ?>/<?= $g_sess['total_target'] ?>)</a>
                        <a href="?restart=grammar" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=grammar" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🧩</div>
                <div class="mode-title">Sentence Builder</div>
                <div class="mode-desc">Drag and drop scrambled words into the correct grammatical order.</div>
                <?php
                $s_sess = $sessions['sentence_builder'] ?? null;
                if ($s_sess && $s_sess['questions_completed'] > 0 && $s_sess['questions_completed'] < $s_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="sentence_builder.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $s_sess['questions_completed'] ?>/<?= $s_sess['total_target'] ?>)</a>
                        <a href="?restart=sentence_builder" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=sentence_builder" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🕵️</div>
                <div class="mode-title">Error Correction</div>
                <div class="mode-desc">Find and correct the deliberate grammatical mistake in the sentence.</div>
                <?php
                $e_sess = $sessions['error_correction'] ?? null;
                if ($e_sess && $e_sess['questions_completed'] > 0 && $e_sess['questions_completed'] < $e_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="error_correction.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $e_sess['questions_completed'] ?>/<?= $e_sess['total_target'] ?>)</a>
                        <a href="?restart=error_correction" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=error_correction" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>
            
            <div class="mode-card">
                <div class="mode-icon">⚙️</div>
                <div class="mode-title">Verb Conjugator</div>
                <div class="mode-desc">Conjugate Arabic verbs perfectly for given pronouns and tenses.</div>
                <?php
                $c_sess = $sessions['conjugator'] ?? null;
                if ($c_sess && $c_sess['questions_completed'] > 0 && $c_sess['questions_completed'] < $c_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="conjugator.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $c_sess['questions_completed'] ?>/<?= $c_sess['total_target'] ?>)</a>
                        <a href="?restart=conjugator" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=conjugator" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🎧</div>
                <div class="mode-title">Audio Dictation</div>
                <div class="mode-desc">Listen to a sentence and type exactly what you hear in Arabic.</div>
                <?php
                $d_sess = $sessions['dictation'] ?? null;
                if ($d_sess && $d_sess['questions_completed'] > 0 && $d_sess['questions_completed'] < $d_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="dictation.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $d_sess['questions_completed'] ?>/<?= $d_sess['total_target'] ?>)</a>
                        <a href="?restart=dictation" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=dictation" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🔗</div>
                <div class="mode-title">Vocab Match-Up</div>
                <div class="mode-desc">Connect advanced Arabic academic terms to their English definitions.</div>
                <?php
                $v_sess = $sessions['vocab_match'] ?? null;
                if ($v_sess && $v_sess['questions_completed'] > 0 && $v_sess['questions_completed'] < $v_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="vocab_match.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $v_sess['questions_completed'] ?>/<?= $v_sess['total_target'] ?>)</a>
                        <a href="?restart=vocab_match" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=vocab_match" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">📖</div>
                <div class="mode-title">Reading Comprehension</div>
                <div class="mode-desc">Read professional Arabic paragraphs and answer complex questions.</div>
                <?php
                $rc_sess = $sessions['reading'] ?? null;
                if ($rc_sess && $rc_sess['questions_completed'] > 0 && $rc_sess['questions_completed'] < $rc_sess['total_target']):
                ?>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: auto;">
                        <a href="reading.php" class="mode-btn" style="flex: 1; background: var(--success); border-color: var(--success); text-align: center; text-decoration: none;">Resume (<?= $rc_sess['questions_completed'] ?>/<?= $rc_sess['total_target'] ?>)</a>
                        <a href="?restart=reading" class="mode-btn" style="flex: 1; background: var(--danger); border-color: var(--danger); text-align: center; text-decoration: none;">Restart</a>
                    </div>
                <?php else: ?>
                    <a href="?restart=reading" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Game</a>
                <?php endif; ?>
            </div>

            <div class="mode-card">
                <div class="mode-icon">🤖</div>
                <div class="mode-title">AI Essay Grader</div>
                <div class="mode-desc">Write an essay and get instant feedback from our AI tutor.</div>
                <a href="essay_grader.php" class="mode-btn" style="margin-top: auto; width: 100%; text-align: center; text-decoration: none;">Start Writing</a>
            </div>
        </div>
    </div>

</body>
</html>
