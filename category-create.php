<?php
/**
 * Spendly - Create Category
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

$formData = [
    'name' => '',
    'type' => 'expense',
    'color' => '#4b7b65',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Security verification failed. Please refresh the page and try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $formData['type'] = trim((string) ($_POST['type'] ?? ''));
        $formData['color'] = trim((string) ($_POST['color'] ?? ''));

        $validation = validate_category_data($pdo, $userId, $formData);
        if ($validation['valid']) {
            try {
                create_category($pdo, $userId, $validation['data']);
                set_flash_message('success', 'Category created successfully.');
                redirect(base_url('categories.php'));
            } catch (PDOException $e) {
                error_log('Category creation error: ' . $e->getMessage());
                $errors['general'] = 'The category could not be saved. A category with the same name and type may already exist.';
            }
        } else {
            $errors = $validation['errors'];
        }
    }
}

$pageTitle = 'Add Category';
$activeNav = 'categories';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper catalog-form-wrapper">
    <div class="card">
        <div class="form-page-heading">
            <div>
                <h1>Add Category</h1>
                <p>Create a category for income or expenses.</p>
            </div>
            <a href="<?= base_url('categories.php') ?>" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('category-create.php') ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" maxlength="60" value="<?= e($formData['name']) ?>" placeholder="e.g. Freelance, Groceries" required>
                <?php if (isset($errors['name'])): ?><div class="form-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Category Type <span class="required">*</span></label>
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
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Category</button>
                <a href="<?= base_url('categories.php') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
