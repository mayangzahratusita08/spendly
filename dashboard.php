<?php
/**
 * Spendly - User Dashboard
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/budgets.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();

// Current month date boundaries (always calculated from the 1st of the month)
$startOfMonth = date('Y-m-01');
$startOfNextMonth = date('Y-m-d', strtotime($startOfMonth . ' +1 month'));

// 1. Current Balance: all-time income minus all-time expenses
$balanceStmt = $pdo->prepare("
    SELECT COALESCE(
        SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END),
        0
    ) AS current_balance
    FROM transactions
    WHERE user_id = :user_id
");
$balanceStmt->execute([':user_id' => $userId]);
$currentBalance = (float) $balanceStmt->fetchColumn();

// 2. Monthly Income: current month
$incomeStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS monthly_income
    FROM transactions
    WHERE user_id = :user_id
      AND type = 'income'
      AND transaction_date >= :start_date
      AND transaction_date < :end_date
");
$incomeStmt->execute([
    ':user_id'    => $userId,
    ':start_date' => $startOfMonth,
    ':end_date'   => $startOfNextMonth,
]);
$monthlyIncome = (float) $incomeStmt->fetchColumn();

// 3. Monthly Expenses: current month
$expenseStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS monthly_expense
    FROM transactions
    WHERE user_id = :user_id
      AND type = 'expense'
      AND transaction_date >= :start_date
      AND transaction_date < :end_date
");
$expenseStmt->execute([
    ':user_id'    => $userId,
    ':start_date' => $startOfMonth,
    ':end_date'   => $startOfNextMonth,
]);
$monthlyExpense = (float) $expenseStmt->fetchColumn();

// 4. Recent Transactions (latest 5)
$txStmt = $pdo->prepare('
    SELECT
        t.id,
        t.type,
        t.amount,
        t.description,
        t.transaction_date,
        c.name AS category_name,
        c.color AS category_color,
        p.name AS payment_method_name
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.id
    LEFT JOIN payment_methods p ON t.payment_method_id = p.id
    WHERE t.user_id = :user_id
    ORDER BY t.transaction_date DESC, t.id DESC
    LIMIT 5
');
$txStmt->execute([':user_id' => $userId]);
$recentTransactions = $txStmt->fetchAll();

// 4b. Monthly category budgets and actual expense totals
$dashboardBudgets = get_user_budgets_for_month($pdo, $userId, $startOfMonth);

// 5. Current user categories and payment methods for dashboard shortcuts
$catStmt = $pdo->prepare('
    SELECT id, name, type, color
    FROM categories
    WHERE user_id = :user_id
    ORDER BY type ASC, name ASC
');
$catStmt->execute([':user_id' => $userId]);
$userCategories = $catStmt->fetchAll();

$pmStmt = $pdo->prepare('
    SELECT id, name
    FROM payment_methods
    WHERE user_id = :user_id
    ORDER BY name ASC
');
$pmStmt->execute([':user_id' => $userId]);
$userPaymentMethods = $pmStmt->fetchAll();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<?php
$incomeCategoryCount = count(array_filter($userCategories, static function ($category) {
    return $category['type'] === 'income';
}));
$expenseCategoryCount = count(array_filter($userCategories, static function ($category) {
    return $category['type'] === 'expense';
}));
?>

<div class="dashboard-page">
    <section class="dashboard-intro">
        <div class="dashboard-intro-copy">
            <p class="dashboard-eyebrow">Personal overview</p>
            <h1>Welcome back, <?= e($user['full_name']) ?></h1>
            <p>Your financial picture for <?= date('F Y') ?>.</p>
        </div>
        <div class="dashboard-actions">
            <a href="<?= base_url('reports.php') ?>" class="btn btn-secondary">View reports</a>
            <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary">
                <svg class="btn-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
                Add transaction
            </a>
        </div>
    </section>

    <section class="dashboard-metrics" aria-label="Financial summary">
        <article class="metric-card metric-card-balance">
            <div class="metric-label">Current balance</div>
            <div class="metric-value"><?= format_currency($currentBalance) ?></div>
            <div class="metric-caption">All-time net balance</div>
        </article>
        <article class="metric-card">
            <div class="metric-label">Monthly income</div>
            <div class="metric-value metric-value-income"><?= format_currency($monthlyIncome) ?></div>
            <div class="metric-caption"><?= date('F Y') ?> · Total earnings</div>
        </article>
        <article class="metric-card">
            <div class="metric-label">Monthly expenses</div>
            <div class="metric-value metric-value-expense"><?= format_currency($monthlyExpense) ?></div>
            <div class="metric-caption"><?= date('F Y') ?> · Total spending</div>
        </article>
    </section>

    <section class="dashboard-panel budget-panel">
        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-section-kicker">Budget overview</p>
                <h2>Monthly budgets</h2>
                <p>Keep an eye on spending limits for <?= date('F Y') ?>.</p>
            </div>
            <a href="<?= base_url('budgets.php') ?>" class="text-action">Manage budgets <span aria-hidden="true">↗</span></a>
        </div>

        <?php if (empty($dashboardBudgets)): ?>
            <div class="dashboard-inline-empty">
                <div>
                    <strong>No budgets set for this month</strong>
                    <p>Create a limit for an expense category to track your spending.</p>
                </div>
                <a href="<?= base_url('budget-create.php') ?>" class="btn btn-secondary btn-sm">Create a budget</a>
            </div>
        <?php else: ?>
            <div class="dashboard-budget-list">
                <?php foreach (array_slice($dashboardBudgets, 0, 4) as $budget): ?>
                    <?php
                        $limit = (float) $budget['amount_limit'];
                        $spent = (float) $budget['amount_spent'];
                        $percentage = $limit > 0 ? ($spent / $limit) * 100 : 0;
                        $progressWidth = min(100, max(0, $percentage));
                        $status = $spent > $limit ? 'over' : ($percentage >= 80 ? 'warning' : 'on-track');
                        $categoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $budget['category_color'])
                            ? $budget['category_color']
                            : '#9ca3af';
                    ?>
                    <article class="dashboard-budget-row">
                        <div class="dashboard-budget-row-main">
                            <div class="dashboard-budget-name">
                                <span class="dashboard-category-dot" style="background-color: <?= e($categoryColor) ?>;"></span>
                                <strong><?= e($budget['category_name']) ?></strong>
                                <span class="dashboard-budget-status dashboard-budget-status--<?= e($status) ?>">
                                    <?= $status === 'over' ? 'Over budget' : ($status === 'warning' ? 'Near limit' : 'On track') ?>
                                </span>
                            </div>
                            <div class="dashboard-budget-amount">
                                <strong><?= format_currency($spent) ?></strong>
                                <span>of <?= format_currency($limit) ?></span>
                            </div>
                        </div>
                        <div class="dashboard-progress-track" role="progressbar"
                             aria-label="<?= e($budget['category_name']) ?> budget usage"
                             aria-valuemin="0" aria-valuemax="100"
                             aria-valuenow="<?= e((string) min(100, round($percentage))) ?>">
                            <span class="dashboard-progress-fill dashboard-progress-fill--<?= e($status) ?>"
                                  style="width: <?= e((string) $progressWidth) ?>%;"></span>
                        </div>
                        <div class="dashboard-budget-meta">
                            <span><?= number_format($percentage, 1) ?>% used</span>
                            <strong class="<?= ($limit - $spent) >= 0 ? 'text-positive' : 'text-negative' ?>">
                                <?= ($limit - $spent) >= 0
                                    ? format_currency($limit - $spent) . ' remaining'
                                    : format_currency(abs($limit - $spent)) . ' over' ?>
                            </strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (count($dashboardBudgets) > 4): ?>
                <div class="dashboard-panel-footnote">Showing 4 of <?= count($dashboardBudgets) ?> budgets.</div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <div class="dashboard-content-grid">
        <section class="dashboard-panel activity-panel">
            <div class="dashboard-section-heading">
                <div>
                    <p class="dashboard-section-kicker">Recent activity</p>
                    <h2>Recent transactions</h2>
                    <p>Your latest income and expenses.</p>
                </div>
                <a href="<?= base_url('transactions.php') ?>" class="text-action">View all <span aria-hidden="true">↗</span></a>
            </div>

            <?php if (empty($recentTransactions)): ?>
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-mark" aria-hidden="true">↗</div>
                    <strong>No transactions yet</strong>
                    <p>Your recent financial activity will appear here once you add a transaction.</p>
                    <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary btn-sm">Record a transaction</a>
                </div>
            <?php else: ?>
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Category</th>
                                <th scope="col">Description</th>
                                <th scope="col">Method</th>
                                <th scope="col" class="table-numeric">Amount</th>
                                <th scope="col"><span class="visually-hidden">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $tx): ?>
                                <?php
                                    $transactionCategoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($tx['category_color'] ?? ''))
                                        ? $tx['category_color']
                                        : '#9ca3af';
                                ?>
                                <tr>
                                    <td class="table-date"><?= format_date($tx['transaction_date']) ?></td>
                                    <td>
                                        <span class="table-category">
                                            <i class="dashboard-category-dot" style="background-color: <?= e($transactionCategoryColor) ?>;"></i>
                                            <?= e($tx['category_name'] ?? 'Uncategorized') ?>
                                        </span>
                                    </td>
                                    <td class="table-description"><?= e($tx['description'] ?: '—') ?></td>
                                    <td class="table-muted"><?= e($tx['payment_method_name'] ?? '—') ?></td>
                                    <td class="table-numeric">
                                        <strong class="<?= $tx['type'] === 'income' ? 'text-positive' : 'text-negative' ?>">
                                            <?= ($tx['type'] === 'income' ? '+' : '−') . ' ' . format_currency($tx['amount']) ?>
                                        </strong>
                                    </td>
                                    <td class="table-action">
                                        <a href="<?= base_url('transaction-edit.php?id=' . (int) $tx['id']) ?>" class="table-edit-link">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <aside class="dashboard-side-stack" aria-label="Account setup">
            <section class="dashboard-panel setup-panel">
                <div class="dashboard-section-heading">
                    <div>
                        <p class="dashboard-section-kicker">Your setup</p>
                        <h2>Your categories</h2>
                    </div>
                    <a href="<?= base_url('categories.php') ?>" class="text-action">Manage</a>
                </div>

                <div class="setup-counts">
                    <div>
                        <strong><?= $incomeCategoryCount ?></strong>
                        <span>Income</span>
                    </div>
                    <div>
                        <strong><?= $expenseCategoryCount ?></strong>
                        <span>Expense</span>
                    </div>
                </div>

                <?php if (empty($userCategories)): ?>
                    <p class="setup-empty-text">No categories configured yet.</p>
                <?php else: ?>
                    <div class="setup-category-list">
                        <?php foreach ($userCategories as $cat): ?>
                            <?php
                                $setupCategoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($cat['color'] ?? ''))
                                    ? $cat['color']
                                    : '#9ca3af';
                            ?>
                            <div class="setup-category-item">
                                <span class="dashboard-category-dot" style="background-color: <?= e($setupCategoryColor) ?>;"></span>
                                <span><?= e($cat['name']) ?></span>
                                <small><?= $cat['type'] === 'income' ? 'Income' : 'Expense' ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-panel setup-panel">
                <div class="dashboard-section-heading">
                    <div>
                        <p class="dashboard-section-kicker">PAYMENT</p>
                        <h2>Payment methods</h2>
                    </div>
                    <a href="<?= base_url('payment-methods.php') ?>" class="text-action">Manage</a>
                </div>

                <?php if (empty($userPaymentMethods)): ?>
                    <p class="setup-empty-text">No payment methods configured yet.</p>
                <?php else: ?>
                    <ul class="setup-payment-list">
                        <?php foreach ($userPaymentMethods as $pm): ?>
                            <li><?= e($pm['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>