<?php
/**
 * Spendly - Application Entry Point
 *
 * Redirects authenticated users to their dashboard,
 * and guests to the login page.
 */

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(base_url('dashboard.php'));
} else {
    redirect(base_url('login.php'));
}
