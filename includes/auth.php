<?php
/**
 * Spendly - Authentication & User Management
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

/**
 * Initializes a secure session with recommended cookie parameters.
 *
 * Delegates to safe_session_start() in functions.php (which is required above)
 * so there is a single canonical session-start routine for the whole app.
 *
 * @return void
 */
function start_session_safe(): void
{
    safe_session_start(); // Defined in functions.php
}

/**
 * Checks whether the current visitor is logged in.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    start_session_safe();
    return !empty($_SESSION['user_id']);
}

/**
 * Returns the currently authenticated user array or null.
 *
 * @return array|null
 */
function current_user(): ?array
{
    start_session_safe();
    return $_SESSION['user'] ?? null;
}

/**
 * Reusable authentication guard for private pages.
 * Redirects guests to the login page.
 *
 * @return void
 */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash_message('warning', 'Please sign in to access that page.');
        redirect(base_url('login.php'));
    }
}

/**
 * Guard for guest-only pages (e.g., login, register).
 * Redirects authenticated users to the dashboard.
 *
 * @return void
 */
function require_guest(): void
{
    if (is_logged_in()) {
        redirect(base_url('dashboard.php'));
    }
}

/**
 * Seeds default categories and payment methods for a newly registered user.
 * Must be executed inside an active database transaction.
 *
 * @param PDO $pdo
 * @param int $userId
 * @return void
 */
function seed_default_user_data(PDO $pdo, int $userId): void
{
    // Default categories specified in blueprint.md
    $defaultCategories = [
        // Income
        ['name' => 'Salary',        'type' => 'income',  'color' => '#10b981'],
        ['name' => 'Freelance',     'type' => 'income',  'color' => '#059669'],
        ['name' => 'Gift',          'type' => 'income',  'color' => '#34d399'],
        ['name' => 'Other Income',  'type' => 'income',  'color' => '#6ee7b7'],
        // Expense
        ['name' => 'Food',          'type' => 'expense', 'color' => '#f59e0b'],
        ['name' => 'Transportation','type' => 'expense', 'color' => '#3b82f6'],
        ['name' => 'Shopping',      'type' => 'expense', 'color' => '#ec4899'],
        ['name' => 'Bills',         'type' => 'expense', 'color' => '#ef4444'],
        ['name' => 'Education',     'type' => 'expense', 'color' => '#8b5cf6'],
        ['name' => 'Health',        'type' => 'expense', 'color' => '#14b8a6'],
        ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#6366f1'],
        ['name' => 'Other Expense', 'type' => 'expense', 'color' => '#9ca3af'],
    ];

    $catStmt = $pdo->prepare('
        INSERT INTO categories (user_id, name, type, color, created_at)
        VALUES (:user_id, :name, :type, :color, NOW())
    ');

    foreach ($defaultCategories as $cat) {
        $catStmt->execute([
            ':user_id' => $userId,
            ':name'    => $cat['name'],
            ':type'    => $cat['type'],
            ':color'   => $cat['color'],
        ]);
    }

    // Default payment methods specified in blueprint.md
    $defaultPaymentMethods = [
        'Cash',
        'Bank Transfer',
        'E-Wallet',
        'Debit / Credit Card',
    ];

    $pmStmt = $pdo->prepare('
        INSERT INTO payment_methods (user_id, name, created_at)
        VALUES (:user_id, :name, NOW())
    ');

    foreach ($defaultPaymentMethods as $pmName) {
        $pmStmt->execute([
            ':user_id' => $userId,
            ':name'    => $pmName,
        ]);
    }
}

/**
 * Registers a new user with validation, password hashing, and default seeds.
 *
 * @param PDO $pdo
 * @param array $data ['full_name', 'email', 'password', 'password_confirmation']
 * @return array ['success' => bool, 'errors' => array, 'user_id' => ?int]
 */
function register_user(PDO $pdo, array $data): array
{
    $errors = [];

    $fullName         = trim((string) ($data['full_name'] ?? ''));
    $email            = trim((string) ($data['email'] ?? ''));
    $password         = (string) ($data['password'] ?? '');
    $passwordConfirm = (string) ($data['password_confirmation'] ?? '');

    // Validation
    if ($fullName === '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (mb_strlen($fullName) > 100) {
        $errors['full_name'] = 'Full name must not exceed 100 characters.';
    }

    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 190) {
        $errors['email'] = 'Email address must not exceed 190 characters.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if ($password !== $passwordConfirm) {
        $errors['password_confirmation'] = 'Passwords do not match.';
    }

    // Check unique email if format is valid
    if (empty($errors['email'])) {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            $errors['email'] = 'An account with this email address already exists.';
        }
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors, 'user_id' => null];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $userStmt = $pdo->prepare('
            INSERT INTO users (full_name, email, password_hash, created_at, updated_at)
            VALUES (:full_name, :email, :password_hash, NOW(), NOW())
        ');
        $userStmt->execute([
            ':full_name'     => $fullName,
            ':email'         => $email,
            ':password_hash' => $passwordHash,
        ]);

        $userId = (int) $pdo->lastInsertId();

        // Seed default categories & payment methods for this user
        seed_default_user_data($pdo, $userId);

        $pdo->commit();

        return ['success' => true, 'errors' => [], 'user_id' => $userId];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Registration error: ' . $e->getMessage());
        return [
            'success' => false,
            'errors'  => ['general' => 'A system error occurred during registration. Please try again.'],
            'user_id' => null,
        ];
    }
}

/**
 * Authenticates user credentials and initiates a session.
 *
 * @param PDO $pdo
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'error' => ?string]
 */
function login_user(PDO $pdo, string $email, string $password): array
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        return ['success' => false, 'error' => 'Please provide both email and password.'];
    }

    $stmt = $pdo->prepare('
        SELECT id, full_name, email, password_hash
        FROM users
        WHERE email = :email
        LIMIT 1
    ');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Generic error message to prevent account enumeration
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    start_session_safe();
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
    ];

    return ['success' => true, 'error' => null];
}

/**
 * Safely terminates the user session and cleans cookies.
 *
 * @return void
 */
function logout_user(): void
{
    start_session_safe();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
