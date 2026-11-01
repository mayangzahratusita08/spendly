<?php
/**
 * Spendly - Create Payment Method
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/catalog.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();
$formData = ['name' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $validation = validate_payment_method_data($pdo, $userId, $formData);

        if ($validation['valid']) {
            try {
                create_payment_method($pdo, $userId, $validation['data']);
                set_flash_message('success', 'Payment method created successfully.');
                redirect(base_url('payment-methods.php'));
            } catch (PDOException $e) {
                error_log('Payment method creation error: ' . $e->getMessage());
                $errors['general'] = 'The payment method could not be saved. A method with this name may already exist.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Add Payment Method';
$activeNav = 'payment-methods';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper catalog-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Add Payment Method</h1>
                <p>Add a way to identify how a transaction was paid.</p>
            </div>
            <a href="<?= base_url('payment-methods.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('payment-method-create.php') ?>" method="POST" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name" class="form-label">Payment Method Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" maxlength="60" value="<?= e($formData['name']) ?>" placeholder="e.g. Cash, Bank Transfer, E-Wallet" required>
                <?php if (isset($errors['name'])): ?><div class="form-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Payment Method</button>
                <a href="<?= base_url('payment-methods.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
