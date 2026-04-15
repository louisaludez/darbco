<?php
// ============================================================
//  Controller: Profile / Account Settings
//  File      : controllers/ProfileController.php
//  Access    : all authenticated users
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}

require_once MODEL_PATH . 'User.php';
require_once MODEL_PATH . 'TransactionLog.php';

$userModel = new User();
$logger    = new TransactionLog();
$userId    = (int) $_SESSION[SESS_USER_ID];
$message   = '';
$error     = '';

// ── Load current user profile ─────────────────────────────
$profile = $userModel->findById($userId);

// ── Handle change-password POST ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])
    && $_POST['_action'] === 'change_password') {

    Csrf::verify();

    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password']     ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPass, $profile['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $userModel->updatePassword($userId, $newHash);

        $logger->log(
            $userId,
            'user_update',
            "User #{$userId} changed their password.",
            'users',
            $userId
        );
        $message = 'Password changed successfully.';

        // Reload profile
        $profile = $userModel->findById($userId);
    }
}

require_once VIEW_PATH . 'profile/index.php';
