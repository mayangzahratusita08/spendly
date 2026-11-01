<?php
/**
 * Spendly - Delete Category
 * Categories referenced by transactions or budgets cannot be deleted.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/catalog.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash_message('danger', 'Security verification failed. Please try again.');
    redirect(base_url('categories.php'));
}

$user = current_user();
$userId = (int) $user['id'];
$categoryId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($categoryId === false || $categoryId <= 0) {
    set_flash_message('danger', 'The selected category is invalid.');
    redirect(base_url('categories.php'));
}

try {
    $deleted = delete_category(get_db_connection(), $userId, (int) $categoryId);
    if ($deleted) {
        set_flash_message('success', 'Category deleted successfully.');
    } else {
        set_flash_message('warning', 'Category not found or it is still linked to transactions or budgets.');
    }
} catch (PDOException $e) {
    error_log('Category deletion error: ' . $e->getMessage());
    set_flash_message('danger', 'The category could not be deleted because a financial record still references it.');
}

redirect(base_url('categories.php'));
