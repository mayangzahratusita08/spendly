<?php
/**
 * Spendly - Edit Payment Method
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
$paymentMethodId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($paymentMethodId === false || $paymentMethodId <= 0) {
    http_response_code(404);
    exit('Payment method not found.');
}

$method = get_managed_payment_method_by_id($pdo, $userId, (int) $paymentMethodId);
if (!$method) {
    http_response_code(404);
    exit('Payment method not found.');
}

$formData = ['name' => $method['name']];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $validation = validate_payment_method_data($pdo, $userId, $formData, (int) $paymentMethodId);

        if ($validation['valid']) {
            try {
                update_payment_method($pdo, $userId, (int) $paymentMethodId, $validation['data']);
                set_flash_message('success', 'Payment method updated successfully.');
                redirect(base_url('payment-methods.php'));
            } catch (PDOException $e) {
                error_log('Payment method update error: ' . $e->getMessage());
                $errors['general'] = 'The payment method could not be updated. Check whether the name is already used.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Edit Payment Method';
$activeNav = 'payment-methods';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper catalog-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Edit Payment Method</h1>
                <p>Rename this payment method. Existing transactions will keep their linked method.</p>
            </div>
            <a href="<?= base_url('payment-methods.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('payment-method-edit.php?id=' . (int) $paymentMethodId) ?>" method="POST" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="name" class="form-label">Payment Method Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" maxlength="60" value="<?= e($formData['name']) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="form-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <a href="<?= base_url('payment-methods.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
