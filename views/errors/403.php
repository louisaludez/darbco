<?php
// views/errors/403.php
http_response_code(403);
$pageTitle = '403 — Access Denied';
require_once VIEW_PATH . 'layout/header.php';
?>
<div class="login-wrapper">
    <div class="login-card text-center">
        <div class="login-logo" style="background:#fde8e8; color:#dc3545;">
            <i class="bi bi-shield-exclamation"></i>
        </div>
        <h2>403 — Access Denied</h2>
        <p class="subtitle">You do not have permission to view this page.</p>
        <a href="index.php?page=dashboard" class="btn btn-darbco">
            <i class="bi bi-arrow-left me-2"></i>Go to Dashboard
        </a>
    </div>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
