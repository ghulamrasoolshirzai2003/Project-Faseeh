<?php
/**
 * FASEEH LOCAL ROUTER
 * This mimics the vercel.json routing for local development.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Root redirect
if ($uri === '/') {
    require __DIR__ . '/api/index.php';
    exit;
}

// 2. Specific routes
if ($uri === '/reset_db') {
    require __DIR__ . '/api/reset_db.php';
    exit;
}

if ($uri === '/reset_pass') {
    require __DIR__ . '/api/reset_pass.php';
    exit;
}

// 3. Fallback to /api/
if (file_exists(__DIR__ . '/api' . $uri)) {
    require __DIR__ . '/api' . $uri;
    exit;
}

// 4. If it's a real file (css, js, images), let PHP handle it
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// 5. Catch-all: Route everything else to /api/ (mimicking /(.*) rule)
$apiPath = __DIR__ . '/api' . $uri;
if (file_exists($apiPath)) {
    require $apiPath;
} else {
    // If it's something like /dashboard, try /api/dashboard.php
    if (file_exists($apiPath . '.php')) {
        require $apiPath . '.php';
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found (Local Router)";
    }
}
