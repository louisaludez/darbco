<?php
// controllers/WorkerController.php

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}
$allowedRoles = [ROLE_ADMIN, ROLE_FINANCE, ROLE_PAYROLL, ROLE_PRODUCTION];
if (!in_array($_SESSION[SESS_ROLE], $allowedRoles, true)) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'Worker.php';
require_once MODEL_PATH . 'TransactionLog.php';

$workerModel = new Worker();
$logger      = new TransactionLog();
$action      = $_GET['action'] ?? 'list';
$message     = '';
$error       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify();

    try {
        if ($action === 'store') {
            $data = [
                'first_name'     => trim($_POST['first_name']),
                'last_name'      => trim($_POST['last_name']),
                'contact_number' => trim($_POST['contact_number'] ?? ''),
                'is_active'      => 1
            ];
            $newId = $workerModel->create($data);
            $logger->log((int)$_SESSION[SESS_USER_ID], 'user_create', "Registered new worker: {$data['first_name']} {$data['last_name']}", 'workers', $newId);
            $message = "Worker registered successfully.";

        } elseif ($action === 'update') {
            $id = (int) $_POST['worker_id'];
            $data = [
                'first_name'     => trim($_POST['first_name']),
                'last_name'      => trim($_POST['last_name']),
                'contact_number' => trim($_POST['contact_number'] ?? ''),
                'is_active'      => isset($_POST['is_active']) ? 1 : 0
            ];
            $workerModel->update($id, $data);
            $logger->log((int)$_SESSION[SESS_USER_ID], 'user_update', "Updated worker profile (ID: {$id})", 'workers', $id);
            $message = "Worker profile updated.";

        } elseif ($action === 'toggle') {
            $id = (int) $_POST['worker_id'];
            $workerModel->toggleStatus($id);
            $logger->log((int)$_SESSION[SESS_USER_ID], 'user_update', "Toggled active status of worker ID: {$id}", 'workers', $id);
            $message = "Worker status updated.";
        }
    } catch (\Throwable $e) {
        error_log('[WorkerCtrl] ' . $e->getMessage());
        $error = "Failed to process request. Please try again.";
    }
}

$workers = $workerModel->getAll();
require_once VIEW_PATH . 'workers/index.php';
