<?php
/**
 * Spendly - Edit Category
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
$categoryId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($categoryId === false || $categoryId <= 0) {
    http_response_code(404);
    exit('Category not found.');
}

$category = get_managed_category_by_id($pdo, $userId, (int) $categoryId);
if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

$usage = get_category_reference_counts($pdo, $userId, (int) $categoryId);
$formData = [
    'name' => $category['name'],
    'type' => $category['type'],
    'color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $category['color']) ? $category['color'] : '#9ca3af',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $formData['type'] = trim((string) ($_POST['type'] ?? ''));
        $formData['color'] = trim((string) ($_POST['color'] ?? ''));

        $validation = validate_category_data($pdo, $userId, $formData, (int) $categoryId);
        if ($validation['valid']) {
            try {
                update_category($pdo, $userId, (int) $categoryId, $validation['data']);
                set_flash_message('success', 'Category updated successfully.');
                redirect(base_url('categories.php'));
            } catch (PDOException $e) {
                error_log('Category update error: ' . $e->getMessage());
                $errors['general'] = 'The category could not be updated. Check the name and linked records.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Edit Category';
$activeNav = 'categories';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper catalog-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Edit Category</h1>
                <p>Update the name, type, or color of this category.</p>
            </div>
            <a href="<?= base_url('categories.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('category-edit.php?id=' . (int) $categoryId) ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" maxlength="60" value="<?= e($formData['name']) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="form-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Category Type <span class="required">*</span></label>
                <?php if ($usage['transactions'] > 0 || $usage['budgets'] > 0): ?>
                    <input type="hidden" name="type" value="<?= e($formData['type']) ?>">
                    <span class="badge badge-<?= e($formData['type']) ?>"><?= e(ucfirst($formData['type'])) ?></span>
                    <div class="form-hint">The category type is locked because financial records reference this category. You can still rename or recolor it.</div>
                <?php else: ?>
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
                <?php endif; ?>
                <?php if (isset($errors['type'])): ?><div class="form-feedback"><?= e($errors['type']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="color" class="form-label">Category Color</label>
                <div class="color-input-row">
                    <input type="color" id="color" name="color" value="<?= e($formData['color']) ?>" aria-label="Choose category color">
                    <span class="form-hint">Used as the category indicator throughout Spendly.</span>
                </div>
                <?php if (isset($errors['color'])): ?><div class="form-feedback"><?= e($errors['color']) ?></div><?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <a href="<?= base_url('categories.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
