<?php
/**
 * Spendly - Delete Monthly Budget
 *
 * This endpoint accepts POST only and never deletes financial transactions.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/budgets.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash_message('danger', 'Security verification failed. Please try again.');
    redirect(base_url('budgets.php'));
}

$user = current_user();
$userId = (int) $user['id'];
$budgetId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$month = trim((string) ($_POST['budget_month'] ?? date('Y-m')));

if (!is_valid_budget_month($month)) {
    $month = date('Y-m');
}

if ($budgetId === false || $budgetId <= 0) {
    set_flash_message('danger', 'The selected budget is invalid.');
    redirect(base_url('budgets.php?month=' . $month));
}

$deleted = delete_budget(get_db_connection(), $userId, (int) $budgetId);
if ($deleted) {
    set_flash_message('success', 'Monthly budget deleted. Your transactions were not changed.');
} else {
    set_flash_message('warning', 'Budget not found or you do not have permission to delete it.');
}

redirect(base_url('budgets.php?month=' . $month));
