<?php
// views/errors/404.php
http_response_code(404);
$pageTitle = '404 — Page Not Found';
require_once VIEW_PATH . 'layout/header.php';
?>
<div class="login-wrapper">
    <div class="login-card text-center">
        <div class="login-logo" style="background:#e8f0fe; color:#0d6efd;">
            <i class="bi bi-question-circle"></i>
        </div>
        <h2>404 — Page Not Found</h2>
        <p class="subtitle">The page you are looking for does not exist.</p>
        <a href="index.php?page=dashboard" class="btn btn-darbco">
            <i class="bi bi-arrow-left me-2"></i>Go to Dashboard
        </a>
    </div>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
