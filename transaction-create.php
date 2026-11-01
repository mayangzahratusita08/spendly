<?php
/**
 * Spendly - Add New Transaction
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

$categories = get_user_categories($pdo, $userId);
$paymentMethods = get_user_payment_methods($pdo, $userId);

$errors = [];
$formData = [
    'type'              => 'expense', // Default to expense
    'amount'            => '',
    'category_id'       => '',
    'payment_method_id' => '',
    'transaction_date'  => date('Y-m-d'),
    'description'       => '',
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
            create_transaction($pdo, $userId, $validation['data']);
            set_flash_message('success', 'Transaction recorded successfully.');
            redirect(base_url('transactions.php'));
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Add Transaction';
$activeNav = 'transactions';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper" style="max-width: 580px;">
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.4rem; margin: 0 0 0.25rem 0;">Add Transaction</h1>
                <p style="margin: 0; font-size: 0.875rem;">Record a new income or expense entry</p>
            </div>
            <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <span><?= e($errors['general']) ?></span>
                <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('transaction-create.php') ?>" method="POST" id="transaction-form" novalidate>
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
                <label for="amount" class="form-label">Amount (IDR) <span class="required">*</span></label>
                <div class="input-prefix-wrapper">
                    <span class="input-prefix">Rp</span>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        class="form-control input-with-prefix <?= isset($errors['amount']) ? 'is-invalid' : '' ?>"
                        placeholder="0"
                        step="0.01"
                        min="1"
                        value="<?= e($formData['amount']) ?>"
                        required
                        autofocus
                    >
                </div>
                <div class="form-hint">Enter a positive number (e.g., 50000 or 150000.50).</div>
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
                <div class="form-hint">Categories automatically match the selected transaction type.</div>
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
                    placeholder="e.g. Lunch with friends, Monthly salary, Grocery shopping"
                    value="<?= e($formData['description']) ?>"
                >
                <?php if (isset($errors['description'])): ?>
                    <div class="form-feedback"><?= e($errors['description']) ?></div>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Transaction</button>
                <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
