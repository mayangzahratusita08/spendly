<?php
/**
 * Spendly - Cross-Site Request Forgery (CSRF) Protection
 *
 * csrf.php is always loaded via auth.php, which requires functions.php first.
 * safe_session_start() (defined in functions.php) is therefore always available here.
 */

/**
 * Returns the current CSRF token from the session, generating one if needed.
 *
 * @return string
 */
function get_csrf_token(): string
{
    // Use the application's canonical session-start routine so cookie
    // parameters are always consistent, even after a session_destroy().
    safe_session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Renders a hidden HTML input containing the CSRF token.
 *
 * @return string
 */
function csrf_field(): string
{
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifies a submitted CSRF token against the session token.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool
{
    // Use the application's canonical session-start routine (see get_csrf_token above).
    safe_session_start();

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}
