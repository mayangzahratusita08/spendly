<?php
/**
 * Spendly - Monthly Budgets
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

$selectedMonth = trim((string) ($_GET['month'] ?? date('Y-m')));
if (!is_valid_budget_month($selectedMonth)) {
    $selectedMonth = date('Y-m');
}
$budgetMonth = $selectedMonth . '-01';
$budgets = get_user_budgets_for_month($pdo, $userId, $budgetMonth);

$totalBudget = 0.0;
$totalSpent = 0.0;
foreach ($budgets as $budget) {
    $totalBudget += (float) $budget['amount_limit'];
    $totalSpent += (float) $budget['amount_spent'];
}
$netRemaining = $totalBudget - $totalSpent;
$monthLabel = date('F Y', strtotime($budgetMonth));

$pageTitle = 'Budgets';
$activeNav = 'budgets';
$enableM3Expressive = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Monthly Budgets</h1>
        <p>Set spending limits and keep each expense category on track.</p>
    </div>
    <a href="<?= base_url('budget-create.php?month=' . e($selectedMonth)) ?>" class="m3e-button m3e-button--filled m3e-button--size-m"><span class="m3e-icon" aria-hidden="true">add</span> Add Budget</a>
</div>

<div class="card budget-month-filter">
    <form method="GET" action="<?= base_url('budgets.php') ?>" class="budget-month-form">
        <div class="form-group" style="margin: 0;">
            <label for="budget-month" class="form-label">Budget Month</label>
            <input
                type="month"
                id="budget-month"
                name="month"
                class="form-control"
                value="<?= e($selectedMonth) ?>"
                required
            >
        </div>
        <button type="submit" class="m3e-button m3e-button--tonal m3e-button--size-m">View Month</button>
        <a href="<?= base_url('budgets.php') ?>" class="m3e-button m3e-button--text m3e-button--size-s budget-reset-link">Current Month</a>
    </form>
</div>

<div class="summary-cards budget-summary-cards">
    <div class="summary-card">
        <div class="summary-label">Total Budgeted</div>
        <div class="summary-value"><?= format_currency($totalBudget) ?></div>
        <div class="summary-meta"><?= count($budgets) ?> expense categories</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Tracked Spending</div>
        <div class="summary-value expense"><?= format_currency($totalSpent) ?></div>
        <div class="summary-meta"><?= e($monthLabel) ?> spending in budgeted categories</div>
    </div>
    <div class="summary-card">
        <div class="summary-label"><?= $netRemaining >= 0 ? 'Budget Remaining' : 'Over Budget' ?></div>
        <div class="summary-value <?= $netRemaining >= 0 ? 'income' : 'expense' ?>">
            <?= format_currency(abs($netRemaining)) ?>
        </div>
        <div class="summary-meta">Combined across the budgets shown</div>
    </div>
</div>

<div class="section-heading">
    <div>
        <h2>Category Budgets</h2>
        <p class="card-subtitle">Your spending progress for <?= e($monthLabel) ?>.</p>
    </div>
    <span class="milestone-badge"><?= count($budgets) ?> active <?= count($budgets) === 1 ? 'budget' : 'budgets' ?></span>
</div>

<?php if (empty($budgets)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">&#128176;</div>
            <h3>No budgets for <?= e($monthLabel) ?></h3>
            <p>Create a monthly limit for an expense category. Spendly will compare it with your actual transactions.</p>
            <a href="<?= base_url('budget-create.php?month=' . e($selectedMonth)) ?>" class="m3e-button m3e-button--filled m3e-button--size-m">Create Your First Budget</a>
        </div>
    </div>
<?php else: ?>
    <div class="budget-grid">
        <?php foreach ($budgets as $budget): ?>
            <?php
                $limit = (float) $budget['amount_limit'];
                $spent = (float) $budget['amount_spent'];
                $percentage = $limit > 0 ? ($spent / $limit) * 100 : 0;
                $progressWidth = min(100, max(0, $percentage));
                $progressFraction = min(1, max(0, $percentage / 100));
                $status = $spent > $limit ? 'over' : ($percentage >= 80 ? 'warning' : 'on-track');
                $remaining = $limit - $spent;
                $categoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $budget['category_color'])
                    ? $budget['category_color']
                    : '#9ca3af';
            ?>
            <article class="card budget-card">
                <div class="budget-card-heading">
                    <div class="budget-category">
                        <span class="budget-category-dot" style="background-color: <?= e($categoryColor) ?>;"></span>
                        <h3><?= e($budget['category_name']) ?></h3>
                    </div>
                    <span class="budget-status budget-status--<?= e($status) ?>">
                        <?= $status === 'over' ? 'Over budget' : ($status === 'warning' ? 'Near limit' : 'On track') ?>
                    </span>
                </div>

                <div class="budget-amounts">
                    <div>
                        <span class="budget-muted-label">Spent</span>
                        <strong><?= format_currency($spent) ?></strong>
                    </div>
                    <div class="budget-limit">
                        <span class="budget-muted-label">Budget limit</span>
                        <strong><?= format_currency($limit) ?></strong>
                    </div>
                </div>

                <m3e-linear-progress
                    class="m3e-budget-progress m3e-budget-progress--<?= e($status) ?>"
                    value="<?= e(number_format($progressFraction, 3, '.', '')) ?>"
                    shape="wavy"
                    aria-label="<?= e($budget['category_name']) ?> budget usage, <?= e((string) min(100, round($percentage))) ?> percent"
                ></m3e-linear-progress>

                <div class="budget-progress-meta">
                    <strong><?= number_format($percentage, 1) ?>% used</strong>
                    <span class="<?= $remaining >= 0 ? 'budget-remaining' : 'budget-overage' ?>">
                        <?= $remaining >= 0 ? format_currency($remaining) . ' left' : format_currency(abs($remaining)) . ' over' ?>
                    </span>
                </div>

                <div class="budget-card-actions">
                    <a href="<?= base_url('budget-edit.php?id=' . (int) $budget['id']) ?>" class="btn-action edit">Edit</a>
                    <form
                        action="<?= base_url('budget-delete.php') ?>"
                        method="POST"
                        onsubmit="return confirm('Delete this monthly budget? Your transactions will not be affected.');"
                    >
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $budget['id'] ?>">
                        <input type="hidden" name="budget_month" value="<?= e($selectedMonth) ?>">
                        <button type="submit" class="btn-action delete">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-hint budget-footnote">
    Spending is calculated from expense transactions dated within the selected calendar month. Income transactions are not included.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
