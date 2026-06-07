<?php
/**
 * ============================================================
 * FASEEH — GOOGLE OAUTH 2.0 CONFIGURATION
 * ============================================================
 * 
 * HOW TO GET YOUR GOOGLE CREDENTIALS:
 * ------------------------------------
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a new project (or select existing)
 * 3. Go to "APIs & Services" → "Credentials"
 * 4. Click "+ CREATE CREDENTIALS" → "OAuth client ID"
 * 5. Choose "Web application"
 * 6. Under "Authorized redirect URIs", add:
 *    http://YOUR-DOMAIN.com/api/google_callback.php
 * 7. Copy the Client ID and Client Secret below
 * 
 * IMPORTANT: Also go to "OAuth consent screen" and:
 *    - Set app name to "Faseeh Academy"
 *    - Add your domain
 *    - Add scopes: email, profile, openid
 *    - Publish the app (move from Testing to Production)
 * ============================================================
 */

// =====================================================
// 🔑 PASTE YOUR GOOGLE CREDENTIALS HERE
// =====================================================
define('GOOGLE_CLIENT_ID',     getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET_HERE');

// =====================================================
// 🌐 REDIRECT URI (Must match Google Console exactly!)
// =====================================================
// Auto-detect the base URL of the site
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('GOOGLE_REDIRECT_URI', $protocol . '://' . $host . '/api/google_callback.php');

// =====================================================
// Google OAuth endpoints (Don't change these)
// =====================================================
define('GOOGLE_AUTH_URL',  'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_URL',  'https://www.googleapis.com/oauth2/v2/userinfo');

/**
 * Build the Google login URL
 */
function getGoogleLoginUrl() {
    $params = [
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'access_type'   => 'online',
        'prompt'        => 'select_account'
    ];
    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}
?>
