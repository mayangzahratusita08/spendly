<?php
/**
 * Spendly - User Registration Page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

require_guest();

$errors = [];
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submittedToken)) {
        $errors['general'] = 'Security session expired. Please refresh the page and try again.';
    } else {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        $pdo = get_db_connection();
        $result = register_user($pdo, $_POST);

        if ($result['success']) {
            set_flash_message('success', 'Your account has been created! Please sign in with your email and password.');
            redirect(base_url('login.php'));
        } else {
            $errors = $result['errors'];
        }
    }
}

$pageTitle = 'Create Account';
$activeNav = 'register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card">
        <div class="auth-header">
            <h1>Create your account</h1>
            <p>Start tracking your income, expenses, and monthly budgets</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <span><?= e($errors['general']) ?></span>
                <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
            </div>
        <?php endif; ?>

        <form id="register-form" action="<?= base_url('register.php') ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                    value="<?= e($fullName) ?>"
                    maxlength="100"
                    required
                    autocomplete="name"
                    autofocus
                >
                <?php if (isset($errors['full_name'])): ?>
                    <div class="form-feedback"><?= e($errors['full_name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                    value="<?= e($email) ?>"
                    maxlength="190"
                    required
                    autocomplete="email"
                >
                <?php if (isset($errors['email'])): ?>
                    <div class="form-feedback"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
                <div class="form-hint">Must be at least 8 characters long.</div>
                <?php if (isset($errors['password'])): ?>
                    <div class="form-feedback"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password <span class="required">*</span></label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
                <?php if (isset($errors['password_confirmation'])): ?>
                    <div class="form-feedback"><?= e($errors['password_confirmation']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register Account</button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="<?= base_url('login.php') ?>">Sign in here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
