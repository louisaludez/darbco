<?php
// ============================================================
//  DARBCO System — Application Constants
//  File   : config/constants.php
//  Purpose: Central place for all application-wide constants
//           (paths, roles, rate settings, etc.)
// ============================================================

declare(strict_types=1);

// --------------- Paths ----------------------------------------
define('BASE_PATH',  dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', BASE_PATH . 'config'      . DIRECTORY_SEPARATOR);
define('MODEL_PATH',  BASE_PATH . 'models'      . DIRECTORY_SEPARATOR);
define('VIEW_PATH',   BASE_PATH . 'views'        . DIRECTORY_SEPARATOR);
define('CTRL_PATH',   BASE_PATH . 'controllers' . DIRECTORY_SEPARATOR);

// --------------- Application ----------------------------------
define('APP_NAME',    'DARBCO System');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // 'development' | 'production'

// --------------- Session keys ---------------------------------
define('SESS_USER_ID',   'darbco_user_id');
define('SESS_USERNAME',  'darbco_username');
define('SESS_ROLE',      'darbco_role');
define('SESS_FULL_NAME', 'darbco_full_name');

// --------------- Roles ----------------------------------------
define('ROLE_ADMIN',      'admin');
define('ROLE_PRODUCTION', 'production_clerk');
define('ROLE_PAYROLL',    'payroll_personnel');
define('ROLE_FINANCE',    'finance_officer');
define('ROLE_BOOKKEEPER', 'bookkeeper');

// --------------- Payroll Defaults -----------------------------
// Overridable per computation run; stored here as system default
define('DEFAULT_RATE_PER_BOX', 12.50);  // PHP per box

// --------------- Inventory Alerts ----------------------------
define('LOW_STOCK_MULTIPLIER', 1.5);  // alert when qty < reorder_level * 1.5

// --------------- Pagination ----------------------------------
define('ROWS_PER_PAGE', 15);

// --------------- Session ----------------------------------------
define('SESSION_IDLE_TIMEOUT', 1800); // 30 minutes in seconds

// --------------- Chart Colors (used in JS via data-attributes) --
define('CHART_GREEN',  '#1a7f4b');
define('CHART_YELLOW', '#f5a623');
define('CHART_RED',    '#dc3545');
define('CHART_BLUE',   '#0d6efd');
define('CHART_TEAL',   '#0dcaf0');

// --------------- Payroll Status Labels --------------------------
define('PAYROLL_STATUS_LABELS', [
    'pending_review' => 'Pending Review',
    'reviewed'       => 'Reviewed',
    'approved'       => 'Approved',
]);

// --------------- Report Periods ---------------------------------
define('REPORT_PERIODS', [
    'this_month'  => 'This Month',
    'last_month'  => 'Last Month',
    'this_quarter'=> 'This Quarter',
    'this_year'   => 'This Year',
    'custom'      => 'Custom Range',
]);
