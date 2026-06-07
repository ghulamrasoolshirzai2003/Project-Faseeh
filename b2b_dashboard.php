<?php
// b2b_dashboard.php
session_start();
require 'includes/db.php';

// STRICT SECURITY GATES: Only parents or teachers/admins can access
$isParent = isset($_SESSION['parent_auth']) && $_SESSION['parent_auth'] === true;
$isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isParent && !$isTeacher && !$isAdmin) {
    die("Access Denied: You do not have permission to view Parent/Teacher intelligence dashboards.");
}

$studentId = $_GET['student_id'] ?? null;
if (!$studentId) {
    die("Error: No student selected.");
}

// Parent verification: Ensure this child belongs to the parent
if ($isParent) {
    $parent_name = $_SESSION['parent_name'];
    $parent_dob = $_SESSION['parent_dob'];
    $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND guardian_name = ? AND guardian_dob = ?");
    $check->execute([$studentId, $parent_name, $parent_dob]);
    if (!$check->fetch()) {
        die("Unauthorized access to child data.");
    }
    $backLink = "parent_child_report.php?id=" . $studentId;
} else {
    // Teacher/Admin verification (optional link back)
    $backLink = "teacher_student_report.php?id=" . $studentId;
}

// Fetch Student/Child details
$stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
$username = $student['full_name'] ?: ($student['username'] ?? 'Student');

// Fetch Main Progress
$stmt = $pdo->prepare("SELECT * FROM progress WHERE user_id = ?");
$stmt->execute([$studentId]);
$prog = $stmt->fetch() ?: ['total_score'=>0, 'xp'=>0, 'accuracy_correct'=>0, 'accuracy_total'=>0, 'current_streak'=>0];

$accuracy = $prog['accuracy_total'] > 0 ? round(($prog['accuracy_correct'] / $prog['accuracy_total']) * 100) : 0;

// Fetch Academic Stats
$stmt = $pdo->prepare("SELECT mode, correct_answers, wrong_answers FROM academic_stats WHERE user_id = ?");
$stmt->execute([$studentId]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$correctData = [];
$wrongData = [];
$weakestLink = "None";
$worstRatio = 100;

foreach ($stats as $row) {
    $mode = ucfirst(str_replace('_', ' ', $row['mode']));
    $labels[] = $mode;
    $correctData[] = (int)$row['correct_answers'];
    $wrongData[] = (int)$row['wrong_answers'];
    
    $total = $row['correct_answers'] + $row['wrong_answers'];
    if ($total > 5) {
        $ratio = ($row['correct_answers'] / $total) * 100;
        if ($ratio < $worstRatio) {
            $worstRatio = $ratio;
            $weakestLink = $mode;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Analytics | Faseeh Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #0f0c29; --bg-mid: #302b63; --bg-light: #24243e;
            --accent: #f2994a; --success: #2ecc71; --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-mid), var(--bg-light));
            color: white; min-height: 100vh; padding: 40px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; }
        .header h1 { font-weight: 800; font-size: 2.5rem; }
        .header .pro-badge { background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000; padding: 5px 15px; border-radius: 50px; font-weight: 800; font-size: 0.9rem; margin-left: 15px; vertical-align: middle; }
        
        .btn-back { padding: 10px 20px; background: var(--glass); border: 1px solid var(--glass-border); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; text-align: center; backdrop-filter: blur(10px); }
        .stat-card h3 { font-size: 1rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .stat-card .val { font-size: 3rem; font-weight: 800; color: var(--accent); }
        
        .chart-container { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; margin-bottom: 40px; }
        
        .action-plan { background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.3); border-radius: 20px; padding: 30px; }
        .action-plan h2 { color: var(--danger); margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><?= htmlspecialchars($username) ?>'s Analytics <span class="pro-badge">PRO</span></h1>
                <p style="opacity: 0.7; margin-top: 5px;">Parent/Teacher Intelligence Dashboard</p>
            </div>
            <a href="<?= $backLink ?>" class="btn-back">← Back to Child Info</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Global Accuracy</h3>
                <div class="val" style="color: <?= $accuracy >= 70 ? 'var(--success)' : 'var(--danger)' ?>"><?= $accuracy ?>%</div>
            </div>
            <div class="stat-card">
                <h3>Total XP Earned</h3>
                <div class="val"><?= number_format($prog['xp']) ?></div>
            </div>
            <div class="stat-card">
                <h3>Current Streak</h3>
                <div class="val" style="color: #f1c40f;"><?= $prog['current_streak'] ?> 🔥</div>
            </div>
        </div>

        <div class="chart-container">
            <h2 style="margin-bottom: 20px;">Module Performance Breakdown</h2>
            <canvas id="performanceChart" height="100"></canvas>
        </div>

        <?php if ($weakestLink !== "None"): ?>
        <div class="action-plan">
            <h2>⚠️ Intervention Required</h2>
            <p>Based on our AI analytics, the student is struggling the most with <strong><?= $weakestLink ?></strong> (<?= round($worstRatio) ?>% accuracy).</p>
            <p style="margin-top: 10px; opacity: 0.8;">Recommendation: Assign 3 daily sessions of <?= $weakestLink ?> for the next week to rebuild foundational syntax.</p>
        </div>
        <?php else: ?>
        <div class="action-plan" style="background: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.3);">
            <h2 style="color: var(--success);">🌟 Excellent Progress</h2>
            <p>The student is performing well across all active modules. No immediate interventions are required.</p>
        </div>
        <?php endif; ?>
        <?php if ($isTeacher || $isAdmin): ?>
        <div class="chart-container" style="margin-top: 40px; background: rgba(242,153,74,0.15); border-color: var(--accent); box-shadow: 0 0 20px rgba(242,153,74,0.15);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
                <div>
                    <h2 style="color: var(--accent); margin-bottom: 5px;">⚔️ Host Classroom Battle (Class Clash)</h2>
                    <p style="opacity: 0.8; font-size: 0.9rem;">Launch a live, Kahoot-style quiz game on your smartboard! Students can join using their mobile devices.</p>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="clash_host.php?level=beginner" target="_blank" style="padding: 12px 25px; background: var(--accent); color: #000; text-decoration: none; border-radius: 12px; font-weight: 800; transition: 0.3s; box-shadow: 0 5px 15px rgba(242,153,74,0.2);">Beginner Clash</a>
                    <a href="clash_host.php?level=intermediate" target="_blank" style="padding: 12px 25px; background: #5E63BA; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 800; transition: 0.3s;">Intermediate Clash</a>
                </div>
            </div>
        </div>

        <!-- HOMEWORK ASSIGNMENT CONSOLE -->
        <div class="chart-container" style="margin-top: 40px; border-color: #5E63BA; background: rgba(94, 99, 186, 0.08);">
            <h2 style="color: #a29bfe; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>📝</span> Classroom Assignment Console
            </h2>
            
            <form id="assign-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end; background: rgba(0,0,0,0.2); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
                <input type="hidden" name="student_id" value="<?= $studentId ?>">
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Game Mode / Activity</label>
                    <select name="game_mode" style="padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; cursor: pointer;" required>
                        <option value="game">🗡️ Hangman</option>
                        <option value="vocab">🔗 Vocab Match-Up</option>
                        <option value="dictation">🎧 Audio Dictation</option>
                        <option value="grammar">🏛️ Fill-in-the-Blanks</option>
                        <option value="sentence_builder">🧩 Sentence Builder</option>
                        <option value="conjugator">⚙️ Verb Conjugator</option>
                        <option value="reading">📖 Reading Comprehension</option>
                        <option value="speaking">🎤 Pronunciation Studio</option>
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Difficulty Level</label>
                    <select name="level" style="padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; cursor: pointer;" required>
                        <option value="beginner">Beginner (Novice)</option>
                        <option value="intermediate">Intermediate (Scholar)</option>
                        <option value="advanced">Advanced (Master)</option>
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">Due Date & Time</label>
                    <input type="datetime-local" name="due_date" style="padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; cursor: pointer;" required>
                </div>
                
                <button type="submit" style="padding: 12px; background: #5E63BA; border: none; color: white; font-weight: bold; border-radius: 10px; cursor: pointer; transition: 0.3s; height: 43px;" onmouseover="this.style.background='#7c5cbf'" onmouseout="this.style.background='#5E63BA'">
                    Issue Assignment ➔
                </button>
            </form>
            
            <h3 style="margin-bottom: 15px; font-size: 1.1rem; opacity: 0.9;">Outstanding & Completed Homework</h3>
            <div style="overflow-x: auto; background: rgba(0,0,0,0.15); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.08);">
                            <th style="padding: 15px;">Activity</th>
                            <th style="padding: 15px;">Level</th>
                            <th style="padding: 15px;">Due Date</th>
                            <th style="padding: 15px;">Status</th>
                            <th style="padding: 15px;">Score</th>
                            <th style="padding: 15px;">Completed At</th>
                        </tr>
                    </thead>
                    <tbody id="assignments-list-body">
                        <!-- Loaded dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Correct Answers',
                        data: <?= json_encode($correctData) ?>,
                        backgroundColor: '#2ecc71',
                        borderRadius: 5
                    },
                    {
                        label: 'Wrong Answers',
                        data: <?= json_encode($wrongData) ?>,
                        backgroundColor: '#e74c3c',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'rgba(255,255,255,0.7)' } },
                    x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.7)' } }
                },
                plugins: {
                    legend: { labels: { color: 'white' } }
                }
            }
        });

        // Load outstanding and completed assignments
        async function loadAssignments() {
            try {
                const response = await fetch('api/assignment_engine.php?action=get_teacher_reports');
                const data = await response.json();
                if (data.reports) {
                    const tbody = document.getElementById('assignments-list-body');
                    // Filter reports strictly for this student
                    const studentReports = data.reports.filter(r => r.student_name === '<?= $student['username'] ?>');
                    
                    if (studentReports.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" style="padding: 20px; text-align: center; opacity: 0.5;">No assignments issued for this student yet.</td></tr>`;
                        return;
                    }
                    
                    tbody.innerHTML = studentReports.map(r => {
                        const statusColor = r.status === 'completed' ? 'var(--success)' : 'var(--danger)';
                        const formattedMode = r.game_mode.charAt(0).toUpperCase() + r.game_mode.slice(1).replace('_', ' ');
                        return `
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); text-align: left;">
                                <td style="padding: 15px; font-weight: 600;">${formattedMode}</td>
                                <td style="padding: 15px;"><span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; text-transform: uppercase;">${r.level}</span></td>
                                <td style="padding: 15px; opacity: 0.8;">${new Date(r.due_date).toLocaleString()}</td>
                                <td style="padding: 15px; color: ${statusColor}; font-weight: bold; text-transform: uppercase;">${r.status}</td>
                                <td style="padding: 15px; font-weight: 800; color: var(--accent);">${r.score || '-'}</td>
                                <td style="padding: 15px; opacity: 0.6;">${r.completed_at ? new Date(r.completed_at).toLocaleString() : '-'}</td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch(e) { console.error(e); }
        }

        // Form submission
        document.getElementById('assign-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const payload = {
                action: 'create_assignment',
                student_id: formData.get('student_id'),
                game_mode: formData.get('game_mode'),
                level: formData.get('level'),
                due_date: formData.get('due_date')
            };
            
            try {
                const response = await fetch('api/assignment_engine.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    alert('Assignment issued successfully!');
                    loadAssignments();
                    e.target.reset();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch(err) {
                alert('Connection error.');
            }
        });

        // Auto load assignments on page open
        if (<?= ($isTeacher || $isAdmin) ? 'true' : 'false' ?>) {
            loadAssignments();
        }
    </script>
</body>
</html>
