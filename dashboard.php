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

<div class="m3-dashboard">
    <section class="m3-dashboard-hero" aria-labelledby="dashboard-welcome">
        <div class="m3-dashboard-hero__copy">
            <p class="m3-eyebrow"><span class="m3-eyebrow__dot"></span> YOUR PERSONAL FINANCE SPACE</p>
            <h1 id="dashboard-welcome">Welcome back,<br><span><?= e($user['full_name']) ?></span></h1>
            <p class="m3-dashboard-hero__description">A clearer view of your money, your plans, and the small decisions that add up.</p>
        </div>
        <aside class="m3-month-card" aria-label="Current reporting month">
            <span class="m3-overline">MONTHLY SNAPSHOT</span>
            <strong><?= date('F Y') ?></strong>
            <span class="m3-month-card__note">Your income, spending, and budgets at a glance.</span>
            <span class="m3-month-card__rule" aria-hidden="true"></span>
            <span class="m3-month-card__footer">PERSONAL WORKSPACE <span aria-hidden="true">↗</span></span>
        </aside>
    </section>

    <section class="m3-overview-section" aria-labelledby="financial-overview-title">
        <div class="m3-section-heading">
            <div>
                <p class="m3-eyebrow">AT A GLANCE</p>
                <h2 id="financial-overview-title">Financial overview</h2>
            </div>
            <div class="m3-section-actions">
                <md-outlined-button href="<?= base_url('reports.php') ?>">View reports</md-outlined-button>
                <md-filled-button href="<?= base_url('transaction-create.php') ?>">
                    <svg slot="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Add transaction
                </md-filled-button>
            </div>
        </div>

        <div class="m3-financial-grid">
            <article class="m3-balance-card">
                <div class="m3-balance-card__top">
                    <span class="m3-balance-card__label">Current balance</span>
                    <span class="m3-balance-card__badge">ALL TIME</span>
                </div>
                <p class="m3-balance-card__value"><?= format_currency($currentBalance) ?></p>
                <div class="m3-balance-card__bottom">
                    <span>Net balance across your recorded transactions</span>
                    <span class="m3-balance-card__indicator" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 16.5 9 11l3.5 3.5L20 7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </article>

            <article class="m3-financial-card m3-financial-card--income">
                <div class="m3-financial-card__top">
                    <span class="m3-financial-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 19V5m-6 6 6-6 6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="m3-overline">THIS MONTH</span>
                </div>
                <p class="m3-financial-card__label">Income</p>
                <p class="m3-financial-card__value"><?= format_currency($monthlyIncome) ?></p>
                <p class="m3-financial-card__footnote"><?= date('F Y') ?> · Money received</p>
            </article>

            <article class="m3-financial-card m3-financial-card--expense">
                <div class="m3-financial-card__top">
                    <span class="m3-financial-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14m-6-6 6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="m3-overline">THIS MONTH</span>
                </div>
                <p class="m3-financial-card__label">Expenses</p>
                <p class="m3-financial-card__value"><?= format_currency($monthlyExpense) ?></p>
                <p class="m3-financial-card__footnote"><?= date('F Y') ?> · Money spent</p>
            </article>
        </div>
    </section>

    <div class="m3-dashboard-grid">
        <div class="m3-dashboard-main-column">
            <section class="m3-surface-card m3-budget-panel" aria-labelledby="monthly-budgets-title">
                <div class="m3-panel-heading">
                    <div>
                        <p class="m3-eyebrow">PLAN WITH INTENTION</p>
                        <h2 id="monthly-budgets-title">Monthly budgets</h2>
                        <p class="m3-panel-description">Keep your spending aligned with the limits you set for <?= date('F Y') ?>.</p>
                    </div>
                    <md-text-button href="<?= base_url('budgets.php') ?>">Manage budgets</md-text-button>
                </div>

                <?php if (empty($dashboardBudgets)): ?>
                    <div class="m3-empty-state">
                        <span class="m3-empty-state__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 7.5h16v12H4zM4 10h16M8 4.5v3M16 4.5v3M8 14h3m2 0h3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="m3-empty-state__copy">
                            <strong>No budgets set for this month</strong>
                            <p>Give an expense category a monthly limit and keep an eye on your progress.</p>
                        </div>
                        <md-outlined-button href="<?= base_url('budget-create.php') ?>">Create a budget</md-outlined-button>
                    </div>
                <?php else: ?>
                    <div class="m3-budget-list">
                        <?php foreach (array_slice($dashboardBudgets, 0, 4) as $budget): ?>
                            <?php
                                $limit = (float) $budget['amount_limit'];
                                $spent = (float) $budget['amount_spent'];
                                $percentage = $limit > 0 ? ($spent / $limit) * 100 : 0;
                                $status = $spent > $limit ? 'over' : ($percentage >= 80 ? 'warning' : 'on-track');
                                $categoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $budget['category_color'])
                                    ? $budget['category_color']
                                    : '#8a938b';
                                $progressFraction = min(1, max(0, $percentage / 100));
                            ?>
                            <article class="m3-budget-item">
                                <div class="m3-budget-item__top">
                                    <div class="m3-budget-item__identity">
                                        <span class="m3-category-marker" style="--category-color: <?= e($categoryColor) ?>;"></span>
                                        <strong><?= e($budget['category_name']) ?></strong>
                                        <span class="m3-status-chip m3-status-chip--<?= e($status) ?>">
                                            <?= $status === 'over' ? 'Over budget' : ($status === 'warning' ? 'Near limit' : 'On track') ?>
                                        </span>
                                    </div>
                                    <div class="m3-budget-item__amount">
                                        <strong><?= format_currency($spent) ?></strong>
                                        <span>of <?= format_currency($limit) ?></span>
                                    </div>
                                </div>
                                <md-linear-progress
                                    class="m3-budget-progress m3-budget-progress--<?= e($status) ?>"
                                    value="<?= e(number_format($progressFraction, 3, '.', '')) ?>"
                                    aria-label="<?= e($budget['category_name']) ?> budget usage"
                                ></md-linear-progress>
                                <div class="m3-budget-item__meta">
                                    <span><?= number_format($percentage, 1) ?>% used</span>
                                    <strong class="<?= ($limit - $spent) >= 0 ? 'm3-positive' : 'm3-negative' ?>">
                                        <?= ($limit - $spent) >= 0
                                            ? format_currency($limit - $spent) . ' remaining'
                                            : format_currency(abs($limit - $spent)) . ' over' ?>
                                    </strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($dashboardBudgets) > 4): ?>
                        <div class="m3-panel-footnote">Showing 4 of <?= count($dashboardBudgets) ?> budgets.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="m3-surface-card m3-activity-panel" aria-labelledby="recent-transactions-title">
                <div class="m3-panel-heading">
                    <div>
                        <p class="m3-eyebrow">THE LATEST</p>
                        <h2 id="recent-transactions-title">Recent transactions</h2>
                        <p class="m3-panel-description">Your latest income and expenses, in one place.</p>
                    </div>
                    <md-text-button href="<?= base_url('transactions.php') ?>">View all</md-text-button>
                </div>

                <?php if (empty($recentTransactions)): ?>
                    <div class="m3-empty-state m3-empty-state--large">
                        <span class="m3-empty-state__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M5 5.5h14v13H5zM8 9h8M8 12.5h5M8 16h3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="m3-empty-state__copy">
                            <strong>Your activity will appear here</strong>
                            <p>Record your first transaction to start building a useful picture of your finances.</p>
                        </div>
                        <md-filled-button href="<?= base_url('transaction-create.php') ?>">Record a transaction</md-filled-button>
                    </div>
                <?php else: ?>
                    <div class="m3-transaction-list">
                        <?php foreach ($recentTransactions as $tx): ?>
                            <?php
                                $transactionCategoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($tx['category_color'] ?? ''))
                                    ? $tx['category_color']
                                    : '#8a938b';
                                $transactionTitle = trim((string) ($tx['description'] ?? ''));
                                if ($transactionTitle === '') {
                                    $transactionTitle = (string) ($tx['category_name'] ?? 'Transaction');
                                }
                            ?>
                            <a class="m3-transaction-item" href="<?= base_url('transaction-edit.php?id=' . (int) $tx['id']) ?>">
                                <span class="m3-transaction-item__icon" style="--category-color: <?= e($transactionCategoryColor) ?>;" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M6.5 3.75h8l3 3v13.5h-11zM14.5 3.75v3.5h3M9 11h6M9 14.5h6M9 18h3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="m3-transaction-item__details">
                                    <strong><?= e($transactionTitle) ?></strong>
                                    <span><?= format_date($tx['transaction_date']) ?> <span aria-hidden="true">·</span> <?= e($tx['category_name'] ?? 'Uncategorized') ?> <span aria-hidden="true">·</span> <?= e($tx['payment_method_name'] ?? 'No method') ?></span>
                                </span>
                                <strong class="m3-transaction-item__amount <?= $tx['type'] === 'income' ? 'm3-positive' : 'm3-negative' ?>">
                                    <?= ($tx['type'] === 'income' ? '+' : '−') . ' ' . format_currency($tx['amount']) ?>
                                </strong>
                                <span class="m3-transaction-item__arrow" aria-hidden="true">↗</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <aside class="m3-dashboard-side-column" aria-label="Your financial setup">
            <section class="m3-surface-card m3-setup-panel" aria-labelledby="categories-title">
                <div class="m3-panel-heading m3-panel-heading--compact">
                    <div>
                        <p class="m3-eyebrow">ORGANIZE</p>
                        <h2 id="categories-title">Your categories</h2>
                    </div>
                    <md-text-button href="<?= base_url('categories.php') ?>">Manage</md-text-button>
                </div>
                <div class="m3-category-summary">
                    <div><strong><?= $incomeCategoryCount ?></strong><span>Income</span></div>
                    <div><strong><?= $expenseCategoryCount ?></strong><span>Expense</span></div>
                </div>
                <?php if (empty($userCategories)): ?>
                    <p class="m3-muted-copy">No categories configured yet.</p>
                <?php else: ?>
                    <div class="m3-category-list">
                        <?php foreach (array_slice($userCategories, 0, 6) as $cat): ?>
                            <?php
                                $setupCategoryColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($cat['color'] ?? ''))
                                    ? $cat['color']
                                    : '#8a938b';
                            ?>
                            <div class="m3-category-list__item">
                                <span class="m3-category-marker" style="--category-color: <?= e($setupCategoryColor) ?>;"></span>
                                <span><?= e($cat['name']) ?></span>
                                <small><?= $cat['type'] === 'income' ? 'Income' : 'Expense' ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($userCategories) > 6): ?>
                        <p class="m3-panel-footnote"><?= count($userCategories) - 6 ?> more categories</p>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="m3-surface-card m3-setup-panel" aria-labelledby="payment-methods-title">
                <div class="m3-panel-heading m3-panel-heading--compact">
                    <div>
                        <p class="m3-eyebrow">HOW YOU PAY</p>
                        <h2 id="payment-methods-title">Payment methods</h2>
                    </div>
                    <md-text-button href="<?= base_url('payment-methods.php') ?>">Manage</md-text-button>
                </div>
                <?php if (empty($userPaymentMethods)): ?>
                    <p class="m3-muted-copy">No payment methods configured yet.</p>
                <?php else: ?>
                    <div class="m3-payment-chip-list">
                        <?php foreach ($userPaymentMethods as $pm): ?>
                            <span class="m3-payment-chip"><?= e($pm['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>