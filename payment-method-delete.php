<?php
/**
 * Spendly - Delete Payment Method
 * Methods referenced by transactions cannot be deleted.
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
    redirect(base_url('payment-methods.php'));
}

$user = current_user();
$userId = (int) $user['id'];
$paymentMethodId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($paymentMethodId === false || $paymentMethodId <= 0) {
    set_flash_message('danger', 'The selected payment method is invalid.');
    redirect(base_url('payment-methods.php'));
}

try {
    $deleted = delete_payment_method(get_db_connection(), $userId, (int) $paymentMethodId);
    if ($deleted) {
        set_flash_message('success', 'Payment method deleted successfully.');
    } else {
        set_flash_message('warning', 'Payment method not found or it is still linked to transactions.');
    }
} catch (PDOException $e) {
    error_log('Payment method deletion error: ' . $e->getMessage());
    set_flash_message('danger', 'The payment method could not be deleted because a transaction still references it.');
}

redirect(base_url('payment-methods.php'));
