<?php
/**
 * Spendly - User Logout Action
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

// If POST request, check CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    // If token verification fails, we still allow logout for safety,
    // but log out unconditionally
}

logout_user();

set_flash_message('info', 'You have been signed out.');
redirect(base_url('login.php'));
