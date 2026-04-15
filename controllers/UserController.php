<?php
// ============================================================
//  Controller: User Management
//  File      : controllers/UserController.php
//  Access    : admin only
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID]) || $_SESSION[SESS_ROLE] !== ROLE_ADMIN) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'TransactionLog.php';

$userModel = new User();
$logger    = new TransactionLog();
$action    = $_GET['action'] ?? 'list';
$message   = '';
$error     = '';

// ── CREATE USER ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    Csrf::verify();
    try {
        $data = [
            'full_name' => trim($_POST['full_name']),
            'username'  => trim($_POST['username']),
            'email'     => trim($_POST['email']),
            'password'  => $_POST['password'],
            'role'      => $_POST['role'],
        ];
        $newId = $userModel->create($data);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'user_create',
            "User '{$data['username']}' (id:{$newId}) created.",
            'users',
            $newId
        );
        $message = "User '{$data['username']}' created successfully.";
    } catch (\Throwable $e) {
        error_log('[UserCtrl] ' . $e->getMessage());
        $error = 'Failed to create user. Username or email may already exist.';
    }
}

// ── TOGGLE ACTIVE ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle') {
    Csrf::verify();
    $uid = (int) $_POST['user_id'];
    $status = (bool) (int) $_POST['is_active'];
    $userModel->setActive($uid, $status);
    $message = 'User status updated.';
}

$users = $userModel->getAll();
require_once VIEW_PATH . 'users/index.php';
