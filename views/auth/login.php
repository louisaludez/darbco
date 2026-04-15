<?php
// views/auth/login.php
$pageTitle = 'Sign In';
require_once VIEW_PATH . 'layout/header.php';
?>
<div class="login-wrapper">
    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo">
            <i class="bi bi-leaf-fill"></i>
        </div>

        <h2><?= APP_NAME ?></h2>
        <p class="subtitle">Banana Production &amp; Export Management</p>

        <!-- Session timeout notice -->
        <?php if (!empty($timeoutMsg)): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-clock-history flex-shrink-0"></i>
            <span><?= htmlspecialchars($timeoutMsg) ?></span>
        </div>
        <?php endif; ?>

        <!-- Error notice -->
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-auto-dismiss d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="index.php?page=login" autocomplete="off" novalidate id="loginForm">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-control border-start-0 ps-0"
                           placeholder="Enter your username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required
                           autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control border-start-0 ps-0"
                           placeholder="Enter your password"
                           required>
                    <button class="btn btn-outline-secondary border-start-0"
                            type="button"
                            id="togglePassword"
                            aria-label="Toggle password visibility"
                            onclick="const p=document.getElementById('password');
                                     const i=this.querySelector('i');
                                     p.type=p.type==='password'?'text':'password';
                                     i.classList.toggle('bi-eye');
                                     i.classList.toggle('bi-eye-slash');">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-darbco w-100 py-2 fw-semibold" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:.75rem;">
            DARBCO v<?= APP_VERSION ?> &mdash; Authorized personnel only
        </p>
    </div>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
