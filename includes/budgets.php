<?php
/**
 * Spendly - Monthly Budget Management
 *
 * All budget queries are scoped to the authenticated user.
 * Budgets apply only to expense categories and are stored per calendar month.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Validate a month input in YYYY-MM format.
 */
function is_valid_budget_month(string $month): bool
{
    if (!preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/D', $month, $matches)) {
        return false;
    }

    return checkdate((int) $matches[2], 1, (int) $matches[1]);
}

/**
 * Return expense categories owned by one user.
 */
function get_user_expense_categories(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT id, name, color
        FROM categories
        WHERE user_id = :user_id
          AND type = 'expense'
        ORDER BY name ASC
    ");
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

/**
 * Validate budget form input, including category ownership and duplicate month/category.
 *
 * @return array{valid: bool, errors: array, data: array}
 */
function validate_budget_data(PDO $pdo, int $userId, array $input, ?int $excludeBudgetId = null): array
{
    $errors = [];
    $data = [];

    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    if ($categoryId === false || $categoryId <= 0) {
        $errors['category_id'] = 'Please select an expense category.';
    } else {
        $categoryStmt = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = :category_id
              AND user_id = :user_id
              AND type = 'expense'
            LIMIT 1
        ");
        $categoryStmt->execute([
            ':category_id' => $categoryId,
            ':user_id' => $userId,
        ]);

        if (!$categoryStmt->fetch()) {
            $errors['category_id'] = 'The selected category is invalid or does not belong to you.';
        } else {
            $data['category_id'] = (int) $categoryId;
        }
    }

    $monthInput = trim((string) ($input['budget_month'] ?? ''));
    if (!is_valid_budget_month($monthInput)) {
        $errors['budget_month'] = 'Please select a valid budget month.';
    } else {
        $data['budget_month'] = $monthInput . '-01';
    }

    // Keep the amount as a decimal string to avoid unnecessary floating-point conversion.
    $rawAmount = trim((string) ($input['amount_limit'] ?? ''));
    $normalizedAmount = str_replace(',', '.', $rawAmount);

    if ($rawAmount === '') {
        $errors['amount_limit'] = 'Budget amount is required.';
    } elseif (!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D', $normalizedAmount)) {
        $errors['amount_limit'] = 'Enter a positive amount with up to 2 decimal places.';
    } else {
        $amountParts = explode('.', $normalizedAmount, 2);
        $whole = ltrim($amountParts[0], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = str_pad($amountParts[1] ?? '', 2, '0');
        $decimalAmount = $whole . '.' . $fraction;

        if ((float) $decimalAmount <= 0) {
            $errors['amount_limit'] = 'Budget amount must be greater than zero.';
        } else {
            $data['amount_limit'] = $decimalAmount;
        }
    }

    // Check the unique user/category/month rule before the database constraint is reached.
    if (
        !isset($errors['category_id'])
        && !isset($errors['budget_month'])
        && isset($data['category_id'], $data['budget_month'])
    ) {
        $duplicateSql = '
            SELECT id
            FROM budgets
            WHERE user_id = :user_id
              AND category_id = :category_id
              AND budget_month = :budget_month
        ';
        $duplicateParams = [
            ':user_id' => $userId,
            ':category_id' => $data['category_id'],
            ':budget_month' => $data['budget_month'],
        ];

        if ($excludeBudgetId !== null) {
            $duplicateSql .= ' AND id <> :exclude_id';
            $duplicateParams[':exclude_id'] = $excludeBudgetId;
        }

        $duplicateSql .= ' LIMIT 1';
        $duplicateStmt = $pdo->prepare($duplicateSql);
        $duplicateStmt->execute($duplicateParams);

        if ($duplicateStmt->fetch()) {
            $errors['category_id'] = 'A budget for this category and month already exists.';
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $data,
    ];
}

/**
 * Create a monthly budget and return its ID.
 */
function create_budget(PDO $pdo, int $userId, array $data): int
{
    $stmt = $pdo->prepare('
        INSERT INTO budgets (user_id, category_id, budget_month, amount_limit, created_at, updated_at)
        VALUES (:user_id, :category_id, :budget_month, :amount_limit, NOW(), NOW())
    ');
    $stmt->execute([
        ':user_id' => $userId,
        ':category_id' => $data['category_id'],
        ':budget_month' => $data['budget_month'],
        ':amount_limit' => $data['amount_limit'],
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Fetch one budget owned by the current user.
 */
function get_budget_by_id(PDO $pdo, int $userId, int $budgetId): ?array
{
    $stmt = $pdo->prepare('
        SELECT
            b.id,
            b.category_id,
            b.budget_month,
            b.amount_limit,
            c.name AS category_name
        FROM budgets b
        INNER JOIN categories c
            ON c.id = b.category_id
           AND c.user_id = b.user_id
        WHERE b.id = :id
          AND b.user_id = :user_id
        LIMIT 1
    ');
    $stmt->execute([
        ':id' => $budgetId,
        ':user_id' => $userId,
    ]);

    $budget = $stmt->fetch();
    return $budget ?: null;
}

/**
 * Update one budget owned by the current user.
 */
function update_budget(PDO $pdo, int $userId, int $budgetId, array $data): bool
{
    $stmt = $pdo->prepare('
        UPDATE budgets
        SET category_id = :category_id,
            budget_month = :budget_month,
            amount_limit = :amount_limit,
            updated_at = NOW()
        WHERE id = :id
          AND user_id = :user_id
    ');
    $stmt->execute([
        ':category_id' => $data['category_id'],
        ':budget_month' => $data['budget_month'],
        ':amount_limit' => $data['amount_limit'],
        ':id' => $budgetId,
        ':user_id' => $userId,
    ]);

    return true;
}

/**
 * Delete one budget owned by the current user.
 */
function delete_budget(PDO $pdo, int $userId, int $budgetId): bool
{
    $stmt = $pdo->prepare('
        DELETE FROM budgets
        WHERE id = :id
          AND user_id = :user_id
    ');
    $stmt->execute([
        ':id' => $budgetId,
        ':user_id' => $userId,
    ]);

    return $stmt->rowCount() > 0;
}

/**
 * Fetch a user's budgets for one month with actual expense totals per category.
 *
 * @param string $budgetMonth First day of month in YYYY-MM-01 format.
 */
function get_user_budgets_for_month(PDO $pdo, int $userId, string $budgetMonth): array
{
    $periodStart = $budgetMonth;
    $periodEnd = (new DateTimeImmutable($budgetMonth))->modify('+1 month')->format('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT
            b.id,
            b.category_id,
            b.budget_month,
            b.amount_limit,
            c.name AS category_name,
            c.color AS category_color,
            COALESCE((
                SELECT SUM(t.amount)
                FROM transactions t
                WHERE t.user_id = b.user_id
                  AND t.category_id = b.category_id
                  AND t.type = 'expense'
                  AND t.transaction_date >= :period_start
                  AND t.transaction_date < :period_end
            ), 0) AS amount_spent
        FROM budgets b
        INNER JOIN categories c
            ON c.id = b.category_id
           AND c.user_id = b.user_id
           AND c.type = 'expense'
        WHERE b.user_id = :user_id
          AND b.budget_month = :budget_month
        ORDER BY c.name ASC
    ");
    $stmt->execute([
        ':period_start' => $periodStart,
        ':period_end' => $periodEnd,
        ':user_id' => $userId,
        ':budget_month' => $budgetMonth,
    ]);

    return $stmt->fetchAll();
}
