<?php
/**
 * Spendly - Transaction History & Management
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/transactions.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$pdo = get_db_connection();

// Collect and clean filters from GET
$filterType = trim((string) ($_GET['type'] ?? ''));
$filterCategory = filter_var($_GET['category_id'] ?? null, FILTER_VALIDATE_INT);
$filterStartDate = trim((string) ($_GET['start_date'] ?? ''));
$filterEndDate = trim((string) ($_GET['end_date'] ?? ''));

// Validate date formats for filters if provided
if ($filterStartDate !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $filterStartDate);
    if (!$d || $d->format('Y-m-d') !== $filterStartDate) {
        $filterStartDate = '';
    }
}
if ($filterEndDate !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $filterEndDate);
    if (!$d || $d->format('Y-m-d') !== $filterEndDate) {
        $filterEndDate = '';
    }
}

$filters = [
    'type'        => in_array($filterType, ['income', 'expense'], true) ? $filterType : '',
    'category_id' => $filterCategory > 0 ? $filterCategory : null,
    'start_date'  => $filterStartDate,
    'end_date'    => $filterEndDate,
];

// Fetch filtered transactions
$transactions = get_filtered_transactions($pdo, $userId, $filters);

// Compute filtered totals
$totalIncome = 0.0;
$totalExpense = 0.0;
foreach ($transactions as $tx) {
    if ($tx['type'] === 'income') {
        $totalIncome += (float) $tx['amount'];
    } else {
        $totalExpense += (float) $tx['amount'];
    }
}
$netTotal = $totalIncome - $totalExpense;

// Fetch user categories for filter dropdown
$userCategories = get_user_categories($pdo, $userId);

$hasActiveFilters = !empty($filters['type']) || !empty($filters['category_id']) || !empty($filters['start_date']) || !empty($filters['end_date']);

$pageTitle = 'Transactions';
$activeNav = 'transactions';
$enableM3Expressive = true;
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h1 style="margin: 0 0 0.25rem 0;">Transactions</h1>
        <p style="margin: 0;">Review, filter, and manage your income and expenses.</p>
    </div>
    <div>
        <a href="<?= base_url('transaction-create.php') ?>" class="m3e-button m3e-button--filled m3e-button--size-m">
            <span class="m3e-icon" aria-hidden="true">add</span> Add Transaction
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="<?= base_url('transactions.php') ?>" class="filter-form" id="transaction-filter-form">
        <div class="filter-grid">
            <div class="form-group m3e-legacy-type-filter" style="margin: 0;">
                <label for="filter-type" class="form-label" style="font-size: 0.8rem;">Type</label>
                <select id="filter-type" name="type" class="form-control" style="padding: 0.45rem 0.65rem;">
                    <option value="">All Types</option>
                    <option value="income" <?= $filters['type'] === 'income' ? 'selected' : '' ?>>Income</option>
                    <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>

            <div class="form-group m3e-filter-chip-group" style="margin: 0;" hidden>
                <span class="form-label" id="transaction-type-chip-label">Type</span>
                <m3e-chip-set class="m3e-transaction-filter-chips" label="Transaction type">
                    <label class="m3e-filter-chip m3e-filter-chip--expressive">
                        <input class="m3e-filter-chip__input" type="checkbox" data-m3e-filter-type="all" <?= $filters['type'] === '' ? 'checked' : '' ?>>
                        <span class="m3e-filter-chip__leading" aria-hidden="true">
                            <svg class="m3e-icon m3e-filter-shape" viewBox="0 0 1 1" data-material-shape="Cookie4Sided"><path></path></svg>
                            <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                        </span>
                        <span class="m3e-filter-chip__label">All</span>
                    </label>
                    <label class="m3e-filter-chip m3e-filter-chip--expressive">
                        <input class="m3e-filter-chip__input" type="checkbox" data-m3e-filter-type="income" <?= $filters['type'] === 'income' ? 'checked' : '' ?>>
                        <span class="m3e-filter-chip__leading" aria-hidden="true">
                            <svg class="m3e-icon m3e-filter-shape" viewBox="0 0 1 1" data-material-shape="Flower"><path></path></svg>
                            <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                        </span>
                        <span class="m3e-filter-chip__label">Income</span>
                    </label>
                    <label class="m3e-filter-chip m3e-filter-chip--expressive">
                        <input class="m3e-filter-chip__input" type="checkbox" data-m3e-filter-type="expense" <?= $filters['type'] === 'expense' ? 'checked' : '' ?>>
                        <span class="m3e-filter-chip__leading" aria-hidden="true">
                            <svg class="m3e-icon m3e-filter-shape" viewBox="0 0 1 1" data-material-shape="Gem"><path></path></svg>
                            <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                        </span>
                        <span class="m3e-filter-chip__label">Expense</span>
                    </label>
                </m3e-chip-set>
            </div>

            <div class="form-group" style="margin: 0;">
                <label for="filter-category" class="form-label" style="font-size: 0.8rem;">Category</label>
                <select id="filter-category" name="category_id" class="form-control" style="padding: 0.45rem 0.65rem;">
                    <option value="">All Categories</option>
                    <?php foreach ($userCategories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $filters['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?> (<?= e(ucfirst($cat['type'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label for="filter-start-date" class="form-label" style="font-size: 0.8rem;">From Date</label>
                <input
                    type="date"
                    id="filter-start-date"
                    name="start_date"
                    class="form-control"
                    style="padding: 0.45rem 0.65rem;"
                    value="<?= e($filters['start_date']) ?>"
                >
            </div>

            <div class="form-group" style="margin: 0;">
                <label for="filter-end-date" class="form-label" style="font-size: 0.8rem;">To Date</label>
                <input
                    type="date"
                    id="filter-end-date"
                    name="end_date"
                    class="form-control"
                    style="padding: 0.45rem 0.65rem;"
                    value="<?= e($filters['end_date']) ?>"
                >
            </div>

            <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-secondary btn-sm" style="height: 36px; padding: 0 1rem;">
                    Apply Filter
                </button>
                <?php if ($hasActiveFilters): ?>
                    <a href="<?= base_url('transactions.php') ?>" class="btn btn-sm" style="height: 36px; padding: 0.5rem 0.75rem; color: var(--color-text-muted);">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Filter Summary Strip -->
<div class="summary-cards" style="margin-bottom: 1.5rem;">
    <div class="summary-card" style="padding: 1rem 1.25rem;">
        <div class="summary-label">Transactions Count</div>
        <div class="summary-value" style="font-size: 1.35rem;"><?= count($transactions) ?></div>
        <div class="summary-meta"><?= $hasActiveFilters ? 'Matching filters' : 'Total records' ?></div>
    </div>
    <div class="summary-card" style="padding: 1rem 1.25rem;">
        <div class="summary-label">Filtered Income</div>
        <div class="summary-value income" style="font-size: 1.35rem;"><?= format_currency($totalIncome) ?></div>
        <div class="summary-meta">Total earnings in view</div>
    </div>
    <div class="summary-card" style="padding: 1rem 1.25rem;">
        <div class="summary-label">Filtered Expenses</div>
        <div class="summary-value expense" style="font-size: 1.35rem;"><?= format_currency($totalExpense) ?></div>
        <div class="summary-meta">Total spending in view</div>
    </div>
</div>

<!-- Transactions Table / Empty State -->
<div class="card" style="padding: 0; overflow: hidden;">
    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128203;</div>
            <?php if ($hasActiveFilters): ?>
                <h3>No transactions match your filters</h3>
                <p>Try adjusting your search criteria or reset the filters to see all records.</p>
                <a href="<?= base_url('transactions.php') ?>" class="btn btn-secondary btn-sm">Clear Filters</a>
            <?php else: ?>
                <h3>No transactions recorded yet</h3>
                <p>Start recording your daily income and expenses to keep your finances in control.</p>
                <a href="<?= base_url('transaction-create.php') ?>" class="btn btn-primary btn-sm">Add First Transaction</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Method</th>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: center; width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= format_date($tx['transaction_date']) ?></td>
                            <td>
                                <span class="badge badge-<?= $tx['type'] === 'income' ? 'income' : 'expense' ?>">
                                    <?= ucfirst($tx['type']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: <?= e($tx['category_color'] ?? '#9ca3af') ?>;"></span>
                                    <?= e($tx['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td style="color: var(--color-text-muted);">
                                <?= e($tx['payment_method_name'] ?? '-') ?>
                            </td>
                            <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= e($tx['description'] ?? '') ?>">
                                <?= e($tx['description'] ?? '-') ?>
                            </td>
                            <td style="text-align: right; font-weight: 600; white-space: nowrap; color: <?= $tx['type'] === 'income' ? 'var(--color-income)' : 'var(--color-expense)' ?>;">
                                <?= ($tx['type'] === 'income' ? '+' : '-') . ' ' . format_currency($tx['amount']) ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <a href="<?= base_url('transaction-edit.php?id=' . (int) $tx['id']) ?>" class="btn-action edit" title="Edit">
                                        Edit
                                    </a>
                                    <form action="<?= base_url('transaction-delete.php') ?>" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $tx['id'] ?>">
                                        <button type="submit" class="btn-action delete" title="Delete">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
