<?php
/**
 * Spendly - Create Monthly Budget
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/budgets.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();
$categories = get_user_expense_categories($pdo, $userId);

$monthFromQuery = trim((string) ($_GET['month'] ?? date('Y-m')));
if (!is_valid_budget_month($monthFromQuery)) {
    $monthFromQuery = date('Y-m');
}

$formData = [
    'category_id' => '',
    'budget_month' => $monthFromQuery,
    'amount_limit' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security session expired. Please refresh the page and try again.';
    } else {
        $formData['category_id'] = trim((string) ($_POST['category_id'] ?? ''));
        $formData['budget_month'] = trim((string) ($_POST['budget_month'] ?? ''));
        $formData['amount_limit'] = trim((string) ($_POST['amount_limit'] ?? ''));

        $validation = validate_budget_data($pdo, $userId, $formData);
        if ($validation['valid']) {
            try {
                create_budget($pdo, $userId, $validation['data']);
                set_flash_message('success', 'Monthly budget created successfully.');
                redirect(base_url('budgets.php?month=' . substr($validation['data']['budget_month'], 0, 7)));
            } catch (PDOException $e) {
                error_log('Budget creation error: ' . $e->getMessage());
                $errors['general'] = 'The budget could not be saved. Check whether this category already has a budget for the selected month.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Add Budget';
$activeNav = 'budgets';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper budget-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Add Budget</h1>
                <p>Create a monthly spending limit for one expense category.</p>
            </div>
            <a href="<?= base_url('budgets.php?month=' . e($formData['budget_month'])) ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <h3>No expense categories available</h3>
                <p>Your account needs at least one expense category before a budget can be created.</p>
                <a href="<?= base_url('budgets.php') ?>" class="btn btn-secondary btn-sm">Back to Budgets</a>
            </div>
        <?php else: ?>
            <form action="<?= base_url('budget-create.php') ?>" method="POST" novalidate>
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="category_id" class="form-label">Expense Category <span class="required">*</span></label>
                    <select id="category_id" name="category_id" class="form-control <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Select Expense Category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (string) $formData['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category_id'])): ?><div class="form-feedback"><?= e($errors['category_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="budget_month" class="form-label">Budget Month <span class="required">*</span></label>
                    <input type="month" id="budget_month" name="budget_month" class="form-control <?= isset($errors['budget_month']) ? 'is-invalid' : '' ?>" value="<?= e($formData['budget_month']) ?>" required>
                    <?php if (isset($errors['budget_month'])): ?><div class="form-feedback"><?= e($errors['budget_month']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="amount_limit" class="form-label">Budget Limit (IDR) <span class="required">*</span></label>
                    <div class="input-prefix-wrapper">
                        <span class="input-prefix">Rp</span>
                        <input type="number" id="amount_limit" name="amount_limit" class="form-control input-with-prefix <?= isset($errors['amount_limit']) ? 'is-invalid' : '' ?>" min="0.01" max="999999999999.99" step="0.01" value="<?= e($formData['amount_limit']) ?>" placeholder="0" required>
                    </div>
                    <div class="form-hint">Enter a positive monthly limit, for example 500000.</div>
                    <?php if (isset($errors['amount_limit'])): ?><div class="form-feedback"><?= e($errors['amount_limit']) ?></div><?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Budget</button>
                    <a href="<?= base_url('budgets.php?month=' . e($formData['budget_month'])) ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
