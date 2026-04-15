<?php
// ============================================================
//  Controller: Auth
//  File      : controllers/AuthController.php
//  Handles   : Login POST, logout, session guard helper
// ============================================================

declare(strict_types=1);

require_once CONFIG_PATH . 'db.php';
require_once MODEL_PATH  . 'User.php';
require_once MODEL_PATH  . 'TransactionLog.php';

$action = $_GET['page'] ?? 'login';

// ── Logout ──────────────────────────────────────────────────
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// ── Already logged in → go to dashboard ─────────────────────
if (isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// ── Detect session timeout message ───────────────────────────
$timeoutMsg = (($_GET['reason'] ?? '') === 'timeout')
    ? 'Your session expired due to inactivity. Please sign in again.'
    : '';

// ── Process login form ───────────────────────────────────────
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION[SESS_USER_ID]   = $user['user_id'];
            $_SESSION[SESS_USERNAME]  = $user['username'];
            $_SESSION[SESS_ROLE]      = $user['role'];
            $_SESSION[SESS_FULL_NAME] = $user['full_name'];

            // Audit log
            $logger = new TransactionLog();
            $logger->log($user['user_id'], 'user_login', "User '{$user['username']}' logged in.");

            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}

// ── Render login view ────────────────────────────────────────
require_once VIEW_PATH . 'auth/login.php';
