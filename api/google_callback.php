<?php
/**
 * ============================================================
 * FASEEH — GOOGLE OAUTH CALLBACK HANDLER
 * ============================================================
 * Google redirects users here after they sign in.
 * This script:
 *   1. Exchanges the auth code for an access token
 *   2. Fetches the user's Google profile (name, email)
 *   3. Creates a new account OR logs them in (if they exist)
 *   4. Redirects to the dashboard
 * ============================================================
 */
session_start();
require '../includes/db.php';
require '../includes/google_config.php';

// ---------------------------------------------------------
// STEP 1: Verify we received an authorization code
// ---------------------------------------------------------
if (!isset($_GET['code'])) {
    header('Location: ../index.php');
    exit;
}

$code = $_GET['code'];

// ---------------------------------------------------------
// STEP 2: Exchange auth code for access token
// ---------------------------------------------------------
$tokenData = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$token = json_decode($tokenResponse, true);

if (!isset($token['access_token'])) {
    // Token exchange failed — redirect back with error
    header('Location: ../index.php?error=google_failed');
    exit;
}

// ---------------------------------------------------------
// STEP 3: Fetch user's Google profile
// ---------------------------------------------------------
$ch = curl_init(GOOGLE_USER_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token['access_token']]);
$profileResponse = curl_exec($ch);
curl_close($ch);

$profile = json_decode($profileResponse, true);

if (!isset($profile['email'])) {
    header('Location: ../index.php?error=google_failed');
    exit;
}

$google_email = $profile['email'];
$google_name  = $profile['name'] ?? 'Faseeh User';

// ---------------------------------------------------------
// STEP 4: Check if user already exists (by email)
// ---------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$google_email]);
$user = $stmt->fetch();

if ($user) {
    // -------------------------------------------------------
    // EXISTING USER — Check if they match the forced role
    // -------------------------------------------------------
    $forced_role = $_SESSION['force_role'] ?? null;
    
    if ($forced_role && $user['role'] !== $forced_role) {
        // ERROR: User is trying to enter a portal they don't belong to
        unset($_SESSION['force_role']);
        $redirect = ($forced_role === 'teacher') ? '../teacher_login.php' : '../index.php';
        header('Location: ' . $redirect . '?error=role_mismatch&expected=' . $forced_role . '&found=' . $user['role']);
        exit;
    }

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

} else {
    // -------------------------------------------------------
    // NEW USER — Auto-register them
    // -------------------------------------------------------
    $pending_role = $_SESSION['force_role'] ?? ($_COOKIE['pending_role'] ?? 'student');
    unset($_SESSION['force_role']);
    // Ensure the role is valid
    if (!in_array($pending_role, ['student', 'teacher', 'admin'])) $pending_role = 'student';

    // Generate a unique username
    $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $google_email)[0]));
    $username = $base_username;
    $counter = 1;
    while (true) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->rowCount() == 0) break;
        $username = $base_username . $counter;
        $counter++;
    }

    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$google_name, $username, $google_email, $random_password, $pending_role]);
        $user_id = $pdo->lastInsertId();

        $stmt_p = $pdo->prepare("INSERT INTO progress (user_id, total_score, xp, current_streak, daily_streak) VALUES (?, 0, 0, 0, 0)");
        $stmt_p->execute([$user_id]);
        $pdo->commit();

        $_SESSION['user_id']  = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $pending_role;
        $user = ['role' => $pending_role]; // For final redirect

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header('Location: ../index.php?error=google_failed');
        exit;
    }
}

// ---------------------------------------------------------
// STEP 5: Redirect based on Role
// ---------------------------------------------------------
if ($user['role'] == 'admin') {
    header("Location: ../admin_panel.php");
} elseif ($user['role'] == 'teacher') {
    header("Location: ../teacher_dashboard.php");
} elseif ($user['role'] == 'parent') {
    header("Location: ../parent_dashboard.php");
} else {
    header("Location: ../level_select.php");
}
exit;
?>
