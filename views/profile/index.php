<?php
// views/profile/index.php
$pageTitle = 'My Profile';
require_once VIEW_PATH . 'layout/header.php';

$roleLabels = [
    'admin'               => ['Admin / Manager',    'success'],
    'production_clerk'    => ['Production Clerk',   'primary'],
    'payroll_personnel'   => ['Payroll Personnel',  'info'],
    'finance_officer'     => ['Finance Officer',    'warning'],
    'bookkeeper'          => ['Bookkeeper',          'secondary'],
];
[$roleLabel, $roleColor] = $roleLabels[$profile['role']] ?? [$profile['role'], 'secondary'];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="bi bi-person-circle me-2 text-success"></i>My Profile</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard" class="text-success">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-lg">
                        <?= strtoupper(substr($profile['full_name'], 0, 1)) ?>
                    </div>
                    <h5 class="mb-1 fw-700"><?= htmlspecialchars($profile['full_name']) ?></h5>
                    <span class="badge bg-white text-<?= $roleColor ?> px-3 py-2 rounded-pill fw-semibold">
                        <?= $roleLabel ?>
                    </span>
                </div>
                <div class="p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center py-2 border-bottom">
                            <i class="bi bi-person me-3 text-success"></i>
                            <div>
                                <div class="small text-muted">Username</div>
                                <div class="fw-600"><?= htmlspecialchars($profile['username']) ?></div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center py-2 border-bottom">
                            <i class="bi bi-envelope me-3 text-success"></i>
                            <div>
                                <div class="small text-muted">Email</div>
                                <div class="fw-600"><?= htmlspecialchars($profile['email']) ?></div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center py-2 border-bottom">
                            <i class="bi bi-shield-check me-3 text-success"></i>
                            <div>
                                <div class="small text-muted">Role</div>
                                <div class="fw-600"><?= $roleLabel ?></div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center py-2">
                            <i class="bi bi-calendar3 me-3 text-success"></i>
                            <div>
                                <div class="small text-muted">Member Since</div>
                                <div class="fw-600"><?= date('F j, Y', strtotime($profile['created_at'])) ?></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-key me-2 text-warning"></i>Change Password
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">
                        Choose a strong password with at least 8 characters mixing letters, numbers, and symbols.
                    </p>
                    <form method="POST" action="index.php?page=profile" id="changePasswordForm"
                          autocomplete="off" novalidate>
                        <input type="hidden" name="_action" value="change_password">
                        <?= Csrf::field() ?>

                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control" required placeholder="Enter your current password">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwdField('current_password', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="new_password" id="new_password"
                                       class="form-control" required minlength="8"
                                       placeholder="Minimum 8 characters">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwdField('new_password', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <!-- Strength meter -->
                            <div class="mt-2">
                                <div class="progress" style="height:5px; border-radius:4px;">
                                    <div id="strengthBar" class="progress-bar" style="width:0%;transition:width .3s;"></div>
                                </div>
                                <small id="strengthLabel" class="text-muted">Enter a new password</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password"
                                       class="form-control" required minlength="8"
                                       placeholder="Re-enter new password">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwdField('confirm_password', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <div id="matchMsg" class="small mt-1"></div>
                        </div>

                        <hr>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php?page=dashboard" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-darbco px-4" id="savePasswordBtn">
                                <i class="bi bi-shield-check me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Session Info Card -->
            <div class="card mt-4">
                <div class="card-header"><i class="bi bi-activity me-2 text-primary"></i>Session Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-4 text-center">
                            <div class="small text-muted">Session Started</div>
                            <div class="fw-600"><?= date('H:i:s') ?></div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="small text-muted">Idle Timeout</div>
                            <div class="fw-600">30 minutes</div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="small text-muted">Current Role</div>
                            <div class="fw-600 text-success"><?= $roleLabel ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.row -->

</main>
</div>

<script>
function togglePwdField(fieldId, btn) {
    const f = document.getElementById(fieldId);
    const i = btn.querySelector('i');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('bi-eye');
    i.classList.toggle('bi-eye-slash');
}

// Password strength meter
document.getElementById('new_password').addEventListener('input', function () {
    const v   = this.value;
    const bar = document.getElementById('strengthBar');
    const lbl = document.getElementById('strengthLabel');
    let score = 0;
    if (v.length >= 8)                score++;
    if (/[A-Z]/.test(v))             score++;
    if (/[0-9]/.test(v))             score++;
    if (/[^A-Za-z0-9]/.test(v))      score++;

    const configs = [
        { w: '0%',   cls: 'bg-secondary', text: 'Enter a new password' },
        { w: '25%',  cls: 'bg-danger',    text: 'Weak' },
        { w: '50%',  cls: 'bg-warning',   text: 'Fair' },
        { w: '75%',  cls: 'bg-primary',   text: 'Good' },
        { w: '100%', cls: 'bg-success',   text: 'Strong ✓' },
    ];
    const cfg = configs[score];
    bar.style.width = cfg.w;
    bar.className   = 'progress-bar ' + cfg.cls;
    lbl.textContent = cfg.text;
    lbl.className   = 'small text-' + (score >= 3 ? 'success' : score === 2 ? 'primary' : 'muted');
});

// Confirm match
document.getElementById('confirm_password').addEventListener('input', function () {
    const msg = document.getElementById('matchMsg');
    const np  = document.getElementById('new_password').value;
    if (this.value === '' ) { msg.textContent = ''; return; }
    msg.textContent = np === this.value ? '✓ Passwords match' : '✗ Passwords do not match';
    msg.className   = 'small mt-1 ' + (np === this.value ? 'text-success' : 'text-danger');
});
</script>

<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
