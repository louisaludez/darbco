<?php
// Sidebar navigation — included by all authenticated views.
$currentPage = $_GET['page'] ?? 'dashboard';
$role        = $_SESSION[SESS_ROLE] ?? '';
?>
<nav id="sidebar" class="sidebar d-flex flex-column flex-shrink-0 p-3">
    <a href="index.php?page=dashboard" class="d-flex align-items-center mb-4 text-decoration-none sidebar-brand">
        <i class="bi bi-leaf-fill me-2 fs-4"></i>
        <span class="fs-5 fw-bold">DARBCO</span>
    </a>
    <hr class="sidebar-divider">

    <ul class="nav nav-pills flex-column mb-auto gap-1">

        <!-- Dashboard — all roles -->
        <li class="nav-item">
            <a href="index.php?page=dashboard"
               class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <!-- Production — clerk + admin -->
        <?php if (in_array($role, [ROLE_PRODUCTION, ROLE_ADMIN], true)): ?>
        <li class="nav-item">
            <a href="index.php?page=production"
               class="nav-link <?= $currentPage === 'production' ? 'active' : '' ?>">
                <i class="bi bi-boxes me-2"></i> Production
            </a>
        </li>
        <?php endif; ?>

        <!-- Inventory — bookkeeper + admin -->
        <?php if (in_array($role, [ROLE_BOOKKEEPER, ROLE_ADMIN], true)): ?>
        <li class="nav-item">
            <a href="index.php?page=inventory"
               class="nav-link <?= $currentPage === 'inventory' ? 'active' : '' ?>">
                <i class="bi bi-archive me-2"></i> Inventory
            </a>
        </li>
        <?php endif; ?>

        <!-- Payroll — payroll + finance + admin -->
        <?php if (in_array($role, [ROLE_PAYROLL, ROLE_FINANCE, ROLE_ADMIN], true)): ?>
        <li class="nav-item">
            <a href="index.php?page=payroll"
               class="nav-link <?= $currentPage === 'payroll' ? 'active' : '' ?>">
                <i class="bi bi-cash-stack me-2"></i> Payroll
            </a>
        </li>
        <?php endif; ?>

        <!-- Reports — admin + finance + payroll + production -->
        <?php if (in_array($role, [ROLE_ADMIN, ROLE_FINANCE, ROLE_PAYROLL, ROLE_PRODUCTION], true)): ?>
        <li class="nav-item">
            <a href="index.php?page=reports"
               class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line me-2"></i> Reports
            </a>
        </li>
        <?php endif; ?>

        <!-- User Management — admin only -->
        <?php if ($role === ROLE_ADMIN || $role === ROLE_PRODUCTION): ?>
        <li><hr class="sidebar-divider my-1"></li>
        <li class="nav-item">
            <a href="index.php?page=workers"
               class="nav-link <?= $currentPage === 'workers' ? 'active' : '' ?>">
                <i class="bi bi-person-badge me-2"></i> Workers Tracker
            </a>
        </li>
        <?php endif; ?>

        <!-- System Management — admin only -->
        <?php if ($role === ROLE_ADMIN): ?>
        <li><hr class="sidebar-divider my-1"></li>
        <li class="nav-item">
            <a href="index.php?page=users"
               class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a href="index.php?page=logs"
               class="nav-link <?= $currentPage === 'logs' ? 'active' : '' ?>">
                <i class="bi bi-journal-text me-2"></i> Audit Logs
            </a>
        </li>
        <?php endif; ?>

    </ul>

    <hr class="sidebar-divider">

    <!-- User info + profile + logout -->
    <div class="sidebar-user d-flex align-items-center gap-2">
        <a href="index.php?page=profile" class="text-decoration-none flex-grow-1 d-flex align-items-center gap-2"
           title="My Profile">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($_SESSION[SESS_FULL_NAME] ?? 'U', 0, 1)) ?>
            </div>
            <div class="lh-sm overflow-hidden">
                <span class="d-block fw-semibold small text-truncate sidebar-name">
                    <?= htmlspecialchars($_SESSION[SESS_FULL_NAME] ?? '') ?>
                </span>
                <span class="d-block sidebar-role">
                    <?= ucwords(str_replace('_', ' ', $role)) ?>
                </span>
            </div>
        </a>
        <a href="index.php?page=logout" class="btn btn-sm sidebar-logout-btn" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>
