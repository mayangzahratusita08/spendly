<?php
/**
 * Spendly - Category Management
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/catalog.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$categories = get_managed_categories(get_db_connection(), $userId);
$incomeCategories = array_values(array_filter($categories, static function ($category) {
    return $category['type'] === 'income';
}));
$expenseCategories = array_values(array_filter($categories, static function ($category) {
    return $category['type'] === 'expense';
}));

$pageTitle = 'Categories';
$activeNav = 'categories';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Categories</h1>
        <p>Organize income and expenses with categories that make sense to you.</p>
    </div>
    <a href="<?= base_url('category-create.php') ?>" class="btn btn-primary">+ Add Category</a>
</div>

<div class="catalog-tabs">
    <a href="#income-categories">Income <span><?= count($incomeCategories) ?></span></a>
    <a href="#expense-categories">Expense <span><?= count($expenseCategories) ?></span></a>
</div>

<section class="card catalog-section" id="income-categories">
    <div class="section-heading">
        <div>
            <h2>Income Categories</h2>
            <p class="card-subtitle">Money coming into your account.</p>
        </div>
        <span class="badge badge-income"><?= count($incomeCategories) ?> categories</span>
    </div>

    <?php if (empty($incomeCategories)): ?>
        <div class="empty-state">
            <h3>No income categories yet</h3>
            <p>Add a category to organize your income records.</p>
        </div>
    <?php else: ?>
        <div class="catalog-list">
            <?php foreach ($incomeCategories as $category): ?>
                <?php
                    $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $category['color'])
                        ? $category['color']
                        : '#9ca3af';
                    $inUse = (int) $category['transaction_count'] > 0 || (int) $category['budget_count'] > 0;
                ?>
                <article class="catalog-item">
                    <div class="catalog-item-main">
                        <span class="catalog-color-dot" style="background-color: <?= e($color) ?>;"></span>
                        <div>
                            <strong><?= e($category['name']) ?></strong>
                            <small><?= (int) $category['transaction_count'] ?> transactions · <?= (int) $category['budget_count'] ?> budgets</small>
                        </div>
                    </div>
                    <div class="catalog-item-actions">
                        <a href="<?= base_url('category-edit.php?id=' . (int) $category['id']) ?>" class="btn-action edit">Edit</a>
                        <?php if ($inUse): ?>
                            <span class="catalog-protected-label" title="Linked records must be preserved">In use</span>
                        <?php else: ?>
                            <form action="<?= base_url('category-delete.php') ?>" method="POST" onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button type="submit" class="btn-action delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="card catalog-section" id="expense-categories">
    <div class="section-heading">
        <div>
            <h2>Expense Categories</h2>
            <p class="card-subtitle">Where your money goes.</p>
        </div>
        <span class="badge badge-expense"><?= count($expenseCategories) ?> categories</span>
    </div>

    <?php if (empty($expenseCategories)): ?>
        <div class="empty-state">
            <h3>No expense categories yet</h3>
            <p>Add a category to organize your spending records.</p>
        </div>
    <?php else: ?>
        <div class="catalog-list">
            <?php foreach ($expenseCategories as $category): ?>
                <?php
                    $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $category['color'])
                        ? $category['color']
                        : '#9ca3af';
                    $inUse = (int) $category['transaction_count'] > 0 || (int) $category['budget_count'] > 0;
                ?>
                <article class="catalog-item">
                    <div class="catalog-item-main">
                        <span class="catalog-color-dot" style="background-color: <?= e($color) ?>;"></span>
                        <div>
                            <strong><?= e($category['name']) ?></strong>
                            <small><?= (int) $category['transaction_count'] ?> transactions · <?= (int) $category['budget_count'] ?> budgets</small>
                        </div>
                    </div>
                    <div class="catalog-item-actions">
                        <a href="<?= base_url('category-edit.php?id=' . (int) $category['id']) ?>" class="btn-action edit">Edit</a>
                        <?php if ($inUse): ?>
                            <span class="catalog-protected-label" title="Linked records must be preserved">In use</span>
                        <?php else: ?>
                            <form action="<?= base_url('category-delete.php') ?>" method="POST" onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                <button type="submit" class="btn-action delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="catalog-note">
    Categories linked to transactions or budgets are protected from deletion. You can still rename or recolor them.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
