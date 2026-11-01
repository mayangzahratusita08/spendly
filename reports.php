<?php
/**
 * Spendly - Analytics & Reports
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/budgets.php';
require_once __DIR__ . '/includes/reports.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();

$selectedMonth = trim((string) ($_GET['month'] ?? date('Y-m')));
if (!is_valid_budget_month($selectedMonth)) {
    $selectedMonth = date('Y-m');
}

$period = get_report_period($selectedMonth);
$summary = get_report_month_summary($pdo, $userId, $period['start'], $period['end']);
$trends = get_report_monthly_trends($pdo, $userId, $selectedMonth);
$expenseCategories = get_report_category_breakdown($pdo, $userId, 'expense', $period['start'], $period['end']);
$incomeCategories = get_report_category_breakdown($pdo, $userId, 'income', $period['start'], $period['end']);
$paymentMethods = get_report_payment_method_breakdown($pdo, $userId, $period['start'], $period['end']);

$totalIncome = (float) $summary['income'];
$totalExpenses = (float) $summary['expenses'];
$netCashFlow = $totalIncome - $totalExpenses;
$savingsRate = $totalIncome > 0 ? ($netCashFlow / $totalIncome) * 100 : null;

$maxTrendValue = 0.0;
foreach ($trends as $trend) {
    $maxTrendValue = max($maxTrendValue, (float) $trend['income'], (float) $trend['expenses']);
}
$maxTrendValue = max(1, $maxTrendValue);

$totalExpenseCategory = array_sum(array_map(static function ($row) {
    return (float) $row['total_amount'];
}, $expenseCategories));
$totalIncomeCategory = array_sum(array_map(static function ($row) {
    return (float) $row['total_amount'];
}, $incomeCategories));

$budgetMonth = $period['start'];
$budgetRows = get_user_budgets_for_month($pdo, $userId, $budgetMonth);
$totalBudget = array_sum(array_map(static function ($row) {
    return (float) $row['amount_limit'];
}, $budgetRows));

$budgetTrackedSpent = array_sum(array_map(static function ($row) {
    return (float) $row['amount_spent'];
}, $budgetRows));

$budgetRemaining = $totalBudget - $budgetTrackedSpent;
$budgetUsage = $totalBudget > 0 ? ($budgetTrackedSpent / $totalBudget) * 100 : null;
$topExpenseCategory = $expenseCategories[0] ?? null;

$pageTitle = 'Reports';
$activeNav = 'reports';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading report-page-heading">
    <div>
        <h1>Analytics &amp; Reports</h1>
        <p>Understand your financial activity and spending patterns.</p>
    </div>
    <span class="milestone-badge">Personal Finance Overview</span>
</div>

<div class="card report-filter-card">
    <form method="GET" action="<?= base_url('reports.php') ?>" class="report-month-form">
        <div class="form-group" style="margin: 0;">
            <label for="report-month" class="form-label">Report Month</label>
            <input type="month" id="report-month" name="month" class="form-control" value="<?= e($selectedMonth) ?>" required>
        </div>
        <button type="submit" class="btn btn-secondary">Apply Period</button>
        <a href="<?= base_url('reports.php') ?>" class="btn btn-sm report-reset-link">Current Month</a>
    </form>
    <p class="report-period-caption">Showing <?= e($period['label']) ?>. The trend chart includes the selected month and the previous five months.</p>
</div>

<div class="summary-cards report-summary-cards">
    <article class="summary-card">
        <div class="summary-label">Total Income</div>
        <div class="summary-value income"><?= format_currency($totalIncome) ?></div>
        <div class="summary-meta"><?= (int) $summary['transaction_count'] ?> total transactions in period</div>
    </article>
    <article class="summary-card">
        <div class="summary-label">Total Expenses</div>
        <div class="summary-value expense"><?= format_currency($totalExpenses) ?></div>
        <div class="summary-meta">Recorded spending in <?= e($period['label']) ?></div>
    </article>
    <article class="summary-card">
        <div class="summary-label">Net Cash Flow</div>
        <div class="summary-value <?= $netCashFlow >= 0 ? 'income' : 'expense' ?>"><?= ($netCashFlow < 0 ? '- ' : '') . format_currency(abs($netCashFlow)) ?></div>
        <div class="summary-meta">Income minus expenses</div>
    </article>
    <article class="summary-card">
        <div class="summary-label">Savings Rate</div>
        <div class="summary-value"><?= $savingsRate === null ? '—' : number_format($savingsRate, 1) . '%' ?></div>
        <div class="summary-meta"><?= $totalIncome > 0 ? 'Net cash flow as a share of income' : 'No income recorded in this period' ?></div>
    </article>
</div>

<section class="card report-section">
    <div class="card-header">
        <div>
            <h2 class="card-title">Income vs. Expenses</h2>
            <p class="card-subtitle">Six-month trend ending in <?= e($period['label']) ?>.</p>
        </div>
        <span class="report-legend">
            <span><i class="report-legend-dot report-legend-income"></i>Income</span>
            <span><i class="report-legend-dot report-legend-expense"></i>Expenses</span>
        </span>
    </div>

    <?php if (array_sum(array_map(static function ($row) { return (float) $row['income'] + (float) $row['expenses']; }, $trends)) <= 0): ?>
        <div class="empty-state report-empty-state">
            <div class="empty-state-icon">&#128200;</div>
            <h3>No financial activity in this period</h3>
            <p>Add transactions to build a monthly income and expense trend.</p>
            <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary btn-sm">Add Transaction</a>
        </div>
    <?php else: ?>
        <div class="trend-chart" role="img" aria-label="Bar chart comparing income and expenses over six months">
            <?php foreach ($trends as $trend): ?>
                <?php
                    $incomeValue = (float) $trend['income'];
                    $expenseValue = (float) $trend['expenses'];
                    $incomeHeight = $incomeValue > 0 ? max(3, ($incomeValue / $maxTrendValue) * 100) : 0;
                    $expenseHeight = $expenseValue > 0 ? max(3, ($expenseValue / $maxTrendValue) * 100) : 0;
                ?>
                <div class="trend-month">
                    <div class="trend-bars">
                        <div class="trend-bar-column">
                            <span class="trend-bar-value"><?= format_currency($incomeValue) ?></span>
                            <div class="trend-bar-track">
                                <span class="trend-bar trend-bar-income" style="height: <?= e((string) $incomeHeight) ?>%;"></span>
                            </div>
                        </div>
                        <div class="trend-bar-column">
                            <span class="trend-bar-value"><?= format_currency($expenseValue) ?></span>
                            <div class="trend-bar-track">
                                <span class="trend-bar trend-bar-expense" style="height: <?= e((string) $expenseHeight) ?>%;"></span>
                            </div>
                        </div>
                    </div>
                    <strong><?= e($trend['label']) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="report-chart-footnote">Each month uses the same scale. Hover or focus a bar to view its exact amount.</div>
    <?php endif; ?>
</section>

<div class="report-two-column">
    <section class="card report-section">
        <div class="card-header">
            <div>
                <h2 class="card-title">Expenses by Category</h2>
                <p class="card-subtitle">Where your spending went in <?= e($period['label']) ?>.</p>
            </div>
        </div>

        <?php if (empty($expenseCategories)): ?>
            <div class="empty-state report-empty-state">
                <h3>No expense records</h3>
                <p>Expense category analysis will appear after you record spending.</p>
            </div>
        <?php else: ?>
            <div class="breakdown-list">
                <?php foreach ($expenseCategories as $category): ?>
                    <?php
                        $amount = (float) $category['total_amount'];
                        $share = $totalExpenseCategory > 0 ? ($amount / $totalExpenseCategory) * 100 : 0;
                        $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $category['category_color'])
                            ? $category['category_color']
                            : '#9ca3af';
                    ?>
                    <div class="breakdown-item">
                        <div class="breakdown-item-heading">
                            <span class="breakdown-name">
                                <i class="breakdown-dot" style="background-color: <?= e($color) ?>;"></i>
                                <?= e($category['category_name']) ?>
                            </span>
                            <strong><?= format_currency($amount) ?></strong>
                        </div>
                        <div class="breakdown-track">
                            <span style="width: <?= e((string) $share) ?>%; background-color: <?= e($color) ?>;"></span>
                        </div>
                        <div class="breakdown-meta">
                            <span><?= (int) $category['transaction_count'] ?> transactions</span>
                            <span><?= number_format($share, 1) ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($topExpenseCategory): ?>
                <div class="report-insight">
                    Highest expense category: <strong><?= e($topExpenseCategory['category_name']) ?></strong>
                    with <?= format_currency($topExpenseCategory['total_amount']) ?>.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="card report-section">
        <div class="card-header">
            <div>
                <h2 class="card-title">Income Sources</h2>
                <p class="card-subtitle">Income grouped by category.</p>
            </div>
        </div>

        <?php if (empty($incomeCategories)): ?>
            <div class="empty-state report-empty-state">
                <h3>No income records</h3>
                <p>Income source analysis will appear after you record earnings.</p>
            </div>
        <?php else: ?>
            <div class="breakdown-list">
                <?php foreach ($incomeCategories as $category): ?>
                    <?php
                        $amount = (float) $category['total_amount'];
                        $share = $totalIncomeCategory > 0 ? ($amount / $totalIncomeCategory) * 100 : 0;
                        $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $category['category_color'])
                            ? $category['category_color']
                            : '#9ca3af';
                    ?>
                    <div class="breakdown-item">
                        <div class="breakdown-item-heading">
                            <span class="breakdown-name">
                                <i class="breakdown-dot" style="background-color: <?= e($color) ?>;"></i>
                                <?= e($category['category_name']) ?>
                            </span>
                            <strong><?= format_currency($amount) ?></strong>
                        </div>
                        <div class="breakdown-track">
                            <span style="width: <?= e((string) $share) ?>%; background-color: <?= e($color) ?>;"></span>
                        </div>
                        <div class="breakdown-meta">
                            <span><?= (int) $category['transaction_count'] ?> transactions</span>
                            <span><?= number_format($share, 1) ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="report-two-column">
    <section class="card report-section">
        <div class="card-header">
            <div>
                <h2 class="card-title">Spending by Payment Method</h2>
                <p class="card-subtitle">Expense transactions grouped by payment method.</p>
            </div>
        </div>

        <?php if (empty($paymentMethods)): ?>
            <div class="empty-state report-empty-state">
                <h3>No payment method data</h3>
                <p>Payment method totals will appear when expenses are recorded.</p>
            </div>
        <?php else: ?>
            <div class="report-simple-list">
                <?php foreach ($paymentMethods as $method): ?>
                    <div class="report-simple-row">
                        <span>
                            <strong><?= e($method['payment_method_name']) ?></strong>
                            <small><?= (int) $method['transaction_count'] ?> transactions</small>
                        </span>
                        <strong><?= format_currency($method['total_amount']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card report-section">
        <div class="card-header">
            <div>
                <h2 class="card-title">Budget Performance</h2>
                <p class="card-subtitle">Actual spending compared with budgets for <?= e($period['label']) ?>.</p>
            </div>
            <a href="<?= base_url('budgets.php?month=' . e($selectedMonth)) ?>" class="btn btn-secondary btn-sm">View Budgets</a>
        </div>

        <?php if (empty($budgetRows)): ?>
            <div class="empty-state report-empty-state">
                <h3>No budgets for this month</h3>
                <p>Create category budgets to compare your spending with your planned limits.</p>
                <a href="<?= base_url('budget-create.php?month=' . e($selectedMonth)) ?>" class="btn btn-primary btn-sm">Create Budget</a>
            </div>
        <?php else: ?>
            <div class="budget-performance-summary">
                <div>
                    <span class="budget-muted-label">Budgeted</span>
                    <strong><?= format_currency($totalBudget) ?></strong>
                </div>
                <div>
                    <span class="budget-muted-label">Spent in budgeted categories</span>
                    <strong><?= format_currency($budgetTrackedSpent) ?></strong>
                </div>
                <div>
                    <span class="budget-muted-label"><?= $budgetRemaining >= 0 ? 'Remaining' : 'Over budget' ?></span>
                    <strong class="<?= $budgetRemaining >= 0 ? 'budget-remaining' : 'budget-overage' ?>">
                        <?= format_currency(abs($budgetRemaining)) ?>
                    </strong>
                </div>
            </div>
            <div class="budget-progress" role="progressbar" aria-label="Combined budget usage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) min(100, round($budgetUsage ?? 0))) ?>">
                <span class="budget-progress-fill <?= $budgetRemaining < 0 ? 'budget-progress-fill--over' : (($budgetUsage ?? 0) >= 80 ? 'budget-progress-fill--warning' : 'budget-progress-fill--on-track') ?>" style="width: <?= e((string) min(100, max(0, $budgetUsage ?? 0))) ?>%;"></span>
            </div>
            <div class="breakdown-meta">
                <span><?= number_format($budgetUsage ?? 0, 1) ?>% of combined budget used</span>
                <span><?= count($budgetRows) ?> categories budgeted</span>
            </div>
            <p class="report-chart-footnote">This comparison includes only categories with a budget configured. Unbudgeted expenses are not included in the tracked spending figure.</p>
        <?php endif; ?>
    </section>
</div>

<div class="report-footer-note">
    Reports are calculated from your saved transactions. Changes to transactions are reflected automatically when this page is refreshed.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
