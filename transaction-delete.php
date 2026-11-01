<?php
/**
 * Spendly - Delete Transaction Action
 *
 * Strictly accepts POST requests with CSRF token verification
 * and enforces authenticated user ownership.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/transactions.php';

require_login();

// Reject any non-POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash_message('danger', 'Method not allowed.');
    redirect(base_url('transactions.php'));
}

// Verify CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($submittedToken)) {
    set_flash_message('danger', 'Security session expired. Please try again.');
    redirect(base_url('transactions.php'));
}

$user = current_user();
$userId = (int) $user['id'];
$transactionId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($transactionId === false || $transactionId <= 0) {
    set_flash_message('danger', 'Invalid transaction specified.');
    redirect(base_url('transactions.php'));
}

$pdo = get_db_connection();

// Perform deletion with strict user_id scoping
$deleted = delete_transaction($pdo, $userId, $transactionId);

if ($deleted) {
    set_flash_message('success', 'Transaction deleted successfully.');
} else {
    set_flash_message('danger', 'Transaction not found or could not be deleted.');
}

redirect(base_url('transactions.php'));
