<?php
/**
 * Spendly - Edit Monthly Budget
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
$budgetId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($budgetId === false || $budgetId <= 0) {
    http_response_code(404);
    exit('Budget not found.');
}

$budget = get_budget_by_id($pdo, $userId, (int) $budgetId);
if (!$budget) {
    http_response_code(404);
    exit('Budget not found.');
}

$categories = get_user_expense_categories($pdo, $userId);
$formData = [
    'category_id' => (string) $budget['category_id'],
    'budget_month' => substr($budget['budget_month'], 0, 7),
    'amount_limit' => (string) $budget['amount_limit'],
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security session expired. Please refresh the page and try again.';
    } else {
        $formData['category_id'] = trim((string) ($_POST['category_id'] ?? ''));
        $formData['budget_month'] = trim((string) ($_POST['budget_month'] ?? ''));
        $formData['amount_limit'] = trim((string) ($_POST['amount_limit'] ?? ''));

        $validation = validate_budget_data($pdo, $userId, $formData, (int) $budgetId);
        if ($validation['valid']) {
            try {
                update_budget($pdo, $userId, (int) $budgetId, $validation['data']);
                set_flash_message('success', 'Monthly budget updated successfully.');
                redirect(base_url('budgets.php?month=' . substr($validation['data']['budget_month'], 0, 7)));
            } catch (PDOException $e) {
                error_log('Budget update error: ' . $e->getMessage());
                $errors['general'] = 'The budget could not be updated. Check whether this category already has a budget for the selected month.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Edit Budget';
$activeNav = 'budgets';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper budget-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Edit Budget</h1>
                <p>Update the category, month, or spending limit.</p>
            </div>
            <a href="<?= base_url('budgets.php?month=' . e($formData['budget_month'])) ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('budget-edit.php?id=' . (int) $budgetId) ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="category_id" class="form-label">Expense Category <span class="required">*</span></label>
                <select id="category_id" name="category_id" class="form-control <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
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
                    <input type="number" id="amount_limit" name="amount_limit" class="form-control input-with-prefix <?= isset($errors['amount_limit']) ? 'is-invalid' : '' ?>" min="0.01" max="999999999999.99" step="0.01" value="<?= e($formData['amount_limit']) ?>" required>
                </div>
                <?php if (isset($errors['amount_limit'])): ?><div class="form-feedback"><?= e($errors['amount_limit']) ?></div><?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Update Budget</button>
                <a href="<?= base_url('budgets.php?month=' . e($formData['budget_month'])) ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
