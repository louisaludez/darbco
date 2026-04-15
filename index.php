<?php
// ============================================================
//  DARBCO System — Front Controller / Router
//  File   : index.php
//  Purpose: Single entry point. Bootstraps the application,
//           starts the session, loads config, and dispatches
//           the request to the correct controller.
// ============================================================

declare(strict_types=1);

// ── Bootstrap ──────────────────────────────────────────────
require_once __DIR__ . '/config/constants.php';

// Configure Error Reporting based on Environment
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL); // Log all errors
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/storage/logs/php_error.log');
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/csrf.php';

// ── Session ────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => (APP_ENV === 'production'),
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

// ── Session Idle Timeout ───────────────────────────────────
if (isset($_SESSION[SESS_USER_ID])) {
    $lastActivity = $_SESSION['_last_activity'] ?? time();
    if ((time() - $lastActivity) > SESSION_IDLE_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: index.php?page=login&reason=timeout');
        exit;
    }
    $_SESSION['_last_activity'] = time();
}

// ── Simple Router ──────────────────────────────────────────
// Determines which controller to load from ?page= query param.
// In Phase 2 we'll replace this with an .htaccess rewrite router.

$page = isset($_GET['page']) ? preg_replace('/[^a-z_\-]/', '', $_GET['page']) : 'login';

$routes = [
    'login'      => CTRL_PATH . 'AuthController.php',
    'logout'     => CTRL_PATH . 'AuthController.php',
    'dashboard'  => CTRL_PATH . 'DashboardController.php',
    'production' => CTRL_PATH . 'ProductionController.php',
    'inventory'  => CTRL_PATH . 'InventoryController.php',
    'payroll'    => CTRL_PATH . 'PayrollController.php',
    'workers'    => CTRL_PATH . 'WorkerController.php',
    'users'      => CTRL_PATH . 'UserController.php',
    'logs'       => CTRL_PATH . 'LogController.php',
    'reports'    => CTRL_PATH . 'ReportController.php',
    'profile'    => CTRL_PATH . 'ProfileController.php',
];

if (array_key_exists($page, $routes) && file_exists($routes[$page])) {
    require_once $routes[$page];
} else {
    http_response_code(404);
    require_once VIEW_PATH . 'errors/404.php';
}
