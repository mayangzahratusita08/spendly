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

// 5. User seeded categories & payment methods overview (Milestone 1 foundation check)
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

<div class="dashboard-header">
    <div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1>Hello, <?= e($user['full_name']) ?></h1>
            <p>Here is your financial summary for <?= date('F Y') ?>.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary btn-sm">+ Add Transaction</a>
            <span class="milestone-badge">Milestone 2 Transactions Active</span>
        </div>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-label">Current Balance</div>
        <div class="summary-value"><?= format_currency($currentBalance) ?></div>
        <div class="summary-meta">All-time net balance</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Monthly Income</div>
        <div class="summary-value income"><?= format_currency($monthlyIncome) ?></div>
        <div class="summary-meta"><?= date('M Y') ?> total earnings</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Monthly Expenses</div>
        <div class="summary-value expense"><?= format_currency($monthlyExpense) ?></div>
        <div class="summary-meta"><?= date('M Y') ?> total spending</div>
    </div>
</div>


<!-- Monthly Budget Overview -->
<section class="card dashboard-budget-section">
    <div class="card-header">
        <div>
            <h2 class="card-title">Monthly Budget Overview</h2>
            <p class="card-subtitle">Your category spending limits for <?= date('F Y') ?>.</p>
        </div>
        <a href="<?= base_url('budgets.php') ?>" class="btn btn-secondary btn-sm">Manage Budgets</a>
    </div>

    <?php if (empty($dashboardBudgets)): ?>
        <div class="empty-state" style="padding: 1.5rem 1rem;">
            <div class="empty-state-icon">&#128176;</div>
            <h3>No monthly budgets yet</h3>
            <p>Create a budget for an expense category to start tracking how much you spend against your limit.</p>
            <a href="<?= base_url('budget-create.php') ?>" class="btn btn-primary btn-sm">Create First Budget</a>
        </div>
    <?php else: ?>
        <div class="dashboard-budget-grid">
            <?php foreach (array_slice($dashboardBudgets, 0, 3) as $budget): ?>
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
                <div class="dashboard-budget-item">
                    <div class="budget-card-heading">
                        <div class="budget-category">
                            <span class="budget-category-dot" style="background-color: <?= e($categoryColor) ?>;"></span>
                            <h3><?= e($budget['category_name']) ?></h3>
                        </div>
                        <span class="budget-status budget-status--<?= e($status) ?>">
                            <?= $status === 'over' ? 'Over' : ($status === 'warning' ? 'Near limit' : 'On track') ?>
                        </span>
                    </div>
                    <div class="budget-amounts">
                        <div>
                            <span class="budget-muted-label">Spent</span>
                            <strong><?= format_currency($spent) ?></strong>
                        </div>
                        <div class="budget-limit">
                            <span class="budget-muted-label">Limit</span>
                            <strong><?= format_currency($limit) ?></strong>
                        </div>
                    </div>
                    <div class="budget-progress" role="progressbar" aria-label="<?= e($budget['category_name']) ?> budget usage" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e((string) min(100, round($percentage))) ?>">
                        <span class="budget-progress-fill budget-progress-fill--<?= e($status) ?>" style="width: <?= e((string) $progressWidth) ?>%;"></span>
                    </div>
                    <div class="budget-progress-meta">
                        <strong><?= number_format($percentage, 1) ?>% used</strong>
                        <span class="<?= ($limit - $spent) >= 0 ? 'budget-remaining' : 'budget-overage' ?>">
                            <?= ($limit - $spent) >= 0 ? format_currency($limit - $spent) . ' left' : format_currency(abs($limit - $spent)) . ' over' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($dashboardBudgets) > 3): ?>
            <p class="card-subtitle" style="margin-top: 0.85rem;">Showing 3 of <?= count($dashboardBudgets) ?> category budgets.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Dashboard Content Grid -->
<div class="dashboard-sections">
    <!-- Left Column: Transactions / Empty State -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Recent Transactions</h2>
                <p class="card-subtitle">Your latest financial activity</p>
            </div>
            <?php if (!empty($recentTransactions)): ?>
                <div>
                    <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary btn-sm">View All</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($recentTransactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128203;</div>
                <h3>No transactions recorded yet</h3>
                <p>
                    Start tracking your spending! Click the button below to record your first income or expense.
                </p>
                <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary btn-sm">Record First Transaction</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">
                            <th style="padding: 0.6rem 0.5rem;">Date</th>
                            <th style="padding: 0.6rem 0.5rem;">Category</th>
                            <th style="padding: 0.6rem 0.5rem;">Method</th>
                            <th style="padding: 0.6rem 0.5rem;">Description</th>
                            <th style="padding: 0.6rem 0.5rem; text-align: right;">Amount</th>
                            <th style="padding: 0.6rem 0.5rem; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 0.6rem 0.5rem; white-space: nowrap;"><?= format_date($tx['transaction_date']) ?></td>
                                <td style="padding: 0.6rem 0.5rem;"><?= e($tx['category_name'] ?? 'Uncategorized') ?></td>
                                <td style="padding: 0.6rem 0.5rem; color: var(--color-text-muted);"><?= e($tx['payment_method_name'] ?? '-') ?></td>
                                <td style="padding: 0.6rem 0.5rem; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= e($tx['description'] ?? '-') ?></td>
                                <td style="padding: 0.6rem 0.5rem; text-align: right; font-weight: 600; white-space: nowrap; color: <?= $tx['type'] === 'income' ? 'var(--color-income)' : 'var(--color-expense)' ?>;">
                                    <?= ($tx['type'] === 'income' ? '+' : '-') . ' ' . format_currency($tx['amount']) ?>
                                </td>
                                <td style="padding: 0.6rem 0.5rem; text-align: center; white-space: nowrap;">
                                    <a href="<?= base_url('transaction-edit.php?id=' . (int) $tx['id']) ?>" class="btn-action edit" title="Edit">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Account Seeded Defaults & Milestone Status -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Configured Categories Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Default Categories</h2>
                    <p class="card-subtitle">Automatically seeded upon registration</p>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                <?php foreach ($userCategories as $cat): ?>
                    <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.55rem; background: var(--color-surface-subtle); border-radius: 9999px; font-size: 0.775rem; color: var(--color-text-main); border: 1px solid var(--color-border);">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: <?= e($cat['color'] ?? '#9ca3af') ?>;"></span>
                        <?= e($cat['name']) ?>
                        <small style="color: var(--color-text-muted); font-size: 0.7rem;">(<?= e($cat['type']) ?>)</small>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Configured Payment Methods Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Default Payment Methods</h2>
                    <p class="card-subtitle">Labels ready for your records</p>
                </div>
            </div>

            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.4rem;">
                <?php foreach ($userPaymentMethods as $pm): ?>
                    <li style="display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0.65rem; background: var(--color-surface-subtle); border-radius: var(--radius-sm); font-size: 0.85rem;">
                        <span><?= e($pm['name']) ?></span>
                        <span style="color: var(--color-text-muted); font-size: 0.75rem;">Active</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
