<?php
/**
 * Spendly - Edit Existing Transaction
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/transactions.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();

// Validate ID from GET parameter
$transactionId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($transactionId === false || $transactionId <= 0) {
    set_flash_message('danger', 'Invalid transaction specified.');
    redirect(base_url('transactions.php'));
}

// Strictly verify that the transaction belongs to the authenticated user
$transaction = get_transaction_by_id($pdo, $userId, $transactionId);
if (!$transaction) {
    set_flash_message('danger', 'Transaction not found or access denied.');
    redirect(base_url('transactions.php'));
}

$categories = get_user_categories($pdo, $userId);
$paymentMethods = get_user_payment_methods($pdo, $userId);

$errors = [];
$formData = [
    'type'              => $transaction['type'],
    'amount'            => $transaction['amount'],
    'category_id'       => $transaction['category_id'],
    'payment_method_id' => $transaction['payment_method_id'],
    'transaction_date'  => $transaction['transaction_date'],
    'description'       => $transaction['description'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submittedToken)) {
        $errors['general'] = 'Security session expired. Please refresh the page and try again.';
    } else {
        $formData['type']              = trim((string) ($_POST['type'] ?? 'expense'));
        $formData['amount']            = trim((string) ($_POST['amount'] ?? ''));
        $formData['category_id']       = trim((string) ($_POST['category_id'] ?? ''));
        $formData['payment_method_id'] = trim((string) ($_POST['payment_method_id'] ?? ''));
        $formData['transaction_date']  = trim((string) ($_POST['transaction_date'] ?? date('Y-m-d')));
        $formData['description']       = trim((string) ($_POST['description'] ?? ''));

        $validation = validate_transaction_data($pdo, $userId, $formData);

        if ($validation['valid']) {
            $updated = update_transaction($pdo, $userId, $transactionId, $validation['data']);
            if ($updated) {
                set_flash_message('success', 'Transaction updated successfully.');
            } else {
                set_flash_message('danger', 'Transaction not found or could not be updated.');
            }
            redirect(base_url('transactions.php'));
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Edit Transaction #' . $transactionId;
$activeNav = 'transactions';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper" style="max-width: 580px;">
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.4rem; margin: 0 0 0.25rem 0;">Edit Transaction</h1>
                <p style="margin: 0; font-size: 0.875rem;">Modify transaction details</p>
            </div>
            <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <span><?= e($errors['general']) ?></span>
                <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('transaction-edit.php?id=' . $transactionId) ?>" method="POST" id="transaction-form" novalidate>
            <?= csrf_field() ?>

            <!-- Transaction Type Selector -->
            <div class="form-group">
                <label class="form-label">Transaction Type <span class="required">*</span></label>
                <div class="type-selector">
                    <label class="type-pill <?= $formData['type'] === 'expense' ? 'active-expense' : '' ?>">
                        <input type="radio" name="type" value="expense" <?= $formData['type'] === 'expense' ? 'checked' : '' ?>>
                        Expense
                    </label>
                    <label class="type-pill <?= $formData['type'] === 'income' ? 'active-income' : '' ?>">
                        <input type="radio" name="type" value="income" <?= $formData['type'] === 'income' ? 'checked' : '' ?>>
                        Income
                    </label>
                </div>
                <?php if (isset($errors['type'])): ?>
                    <div class="form-feedback"><?= e($errors['type']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Amount -->
            <div class="form-group">
                <md-outlined-text-field
                    id="amount"
                    name="amount"
                    label="Amount (IDR)"
                    type="number"
                    prefix-text="Rp"
                    min="1"
                    step="0.01"
                    value="<?= e($formData['amount']) ?>"
                    supporting-text="Enter a positive number."
                    <?= isset($errors['amount']) ? 'error error-text="' . e($errors['amount']) . '"' : '' ?>
                    required
                    
                ></md-outlined-text-field>
                <?php if (isset($errors['amount'])): ?>
                    <div class="form-feedback"><?= e($errors['amount']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category_id" class="form-label">Category <span class="required">*</span></label>
                <select
                    id="category_id"
                    name="category_id"
                    class="form-control <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>"
                    required
                >
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option
                            value="<?= (int) $cat['id'] ?>"
                            data-type="<?= e($cat['type']) ?>"
                            <?= (string) $formData['category_id'] === (string) $cat['id'] ? 'selected' : '' ?>
                        >
                            <?= e($cat['name']) ?> (<?= e(ucfirst($cat['type'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="form-feedback"><?= e($errors['category_id']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Payment Method (Optional) -->
            <div class="form-group">
                <label for="payment_method_id" class="form-label">Payment Method <small style="color: var(--color-text-muted);">(Optional)</small></label>
                <select
                    id="payment_method_id"
                    name="payment_method_id"
                    class="form-control <?= isset($errors['payment_method_id']) ? 'is-invalid' : '' ?>"
                >
                    <option value="">-- Unspecified / None --</option>
                    <?php foreach ($paymentMethods as $pm): ?>
                        <option
                            value="<?= (int) $pm['id'] ?>"
                            <?= (string) $formData['payment_method_id'] === (string) $pm['id'] ? 'selected' : '' ?>
                        >
                            <?= e($pm['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['payment_method_id'])): ?>
                    <div class="form-feedback"><?= e($errors['payment_method_id']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Transaction Date -->
            <div class="form-group">
                <label for="transaction_date" class="form-label">Transaction Date <span class="required">*</span></label>
                <input
                    type="date"
                    id="transaction_date"
                    name="transaction_date"
                    class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>"
                    value="<?= e($formData['transaction_date']) ?>"
                    required
                >
                <?php if (isset($errors['transaction_date'])): ?>
                    <div class="form-feedback"><?= e($errors['transaction_date']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Description / Note -->
            <div class="form-group">
                <label for="description" class="form-label">Description / Note <small style="color: var(--color-text-muted);">(Optional, max 255 chars)</small></label>
                <input
                    type="text"
                    id="description"
                    name="description"
                    class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                    maxlength="255"
                    value="<?= e($formData['description']) ?>"
                >
                <?php if (isset($errors['description'])): ?>
                    <div class="form-feedback"><?= e($errors['description']) ?></div>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <md-filled-button type="submit" style="flex: 1;">Update Transaction</md-filled-button>
                <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
