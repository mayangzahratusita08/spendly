<?php
/**
 * Spendly - Analytics & Reports Query Helpers
 *
 * All report data is calculated from real transactions owned by the current user.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Get the first day of a selected calendar month.
 *
 * @return array{start: string, end: string, label: string}
 */
function get_report_period(string $month): array
{
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $month . '-01');
    if (!$start || $start->format('Y-m-d') !== $month . '-01') {
        throw new InvalidArgumentException('Invalid report month.');
    }

    return [
        'start' => $start->format('Y-m-d'),
        'end' => $start->modify('+1 month')->format('Y-m-d'),
        'label' => $start->format('F Y'),
    ];
}

/**
 * Get income, expenses, and transaction count for the selected month.
 */
function get_report_month_summary(PDO $pdo, int $userId, string $startDate, string $endDate): array
{
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expenses,
            COUNT(*) AS transaction_count
        FROM transactions
        WHERE user_id = :user_id
          AND transaction_date >= :start_date
          AND transaction_date < :end_date
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
    ]);

    $row = $stmt->fetch() ?: [];

    return [
        'income' => (string) ($row['total_income'] ?? '0.00'),
        'expenses' => (string) ($row['total_expenses'] ?? '0.00'),
        'transaction_count' => (int) ($row['transaction_count'] ?? 0),
    ];
}

/**
 * Get six consecutive months of income and expenses, ending at the selected month.
 *
 * @return array<int, array{month: string, label: string, income: string, expenses: string}>
 */
function get_report_monthly_trends(PDO $pdo, int $userId, string $selectedMonth): array
{
    $selectedStart = DateTimeImmutable::createFromFormat('!Y-m-d', $selectedMonth . '-01');
    if (!$selectedStart) {
        throw new InvalidArgumentException('Invalid report month.');
    }

    $trendStart = $selectedStart->modify('-5 months');
    $trendEnd = $selectedStart->modify('+1 month');

    $stmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(transaction_date, '%Y-%m') AS month_key,
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expenses
        FROM transactions
        WHERE user_id = :user_id
          AND transaction_date >= :start_date
          AND transaction_date < :end_date
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month_key ASC
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':start_date' => $trendStart->format('Y-m-d'),
        ':end_date' => $trendEnd->format('Y-m-d'),
    ]);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[$row['month_key']] = $row;
    }

    $result = [];
    for ($offset = 0; $offset < 6; $offset++) {
        $month = $trendStart->modify('+' . $offset . ' months');
        $key = $month->format('Y-m');
        $row = $rows[$key] ?? [];

        $result[] = [
            'month' => $key,
            'label' => $month->format('M'),
            'income' => (string) ($row['total_income'] ?? '0.00'),
            'expenses' => (string) ($row['total_expenses'] ?? '0.00'),
        ];
    }

    return $result;
}

/**
 * Get category totals for one transaction type in the selected month.
 */
function get_report_category_breakdown(PDO $pdo, int $userId, string $type, string $startDate, string $endDate): array
{
    if (!in_array($type, ['income', 'expense'], true)) {
        throw new InvalidArgumentException('Invalid transaction type.');
    }

    $stmt = $pdo->prepare("
        SELECT
            c.id AS category_id,
            c.name AS category_name,
            c.color AS category_color,
            COALESCE(SUM(t.amount), 0) AS total_amount,
            COUNT(t.id) AS transaction_count
        FROM transactions t
        INNER JOIN categories c
            ON c.id = t.category_id
           AND c.user_id = t.user_id
        WHERE t.user_id = :user_id
          AND t.type = :type
          AND t.transaction_date >= :start_date
          AND t.transaction_date < :end_date
        GROUP BY c.id, c.name, c.color
        ORDER BY total_amount DESC, c.name ASC
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':type' => $type,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
    ]);

    return $stmt->fetchAll();
}

/**
 * Get expense totals grouped by payment method.
 * Transactions without a selected method are grouped under "Unspecified".
 */
function get_report_payment_method_breakdown(PDO $pdo, int $userId, string $startDate, string $endDate): array
{
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(p.name, 'Unspecified') AS payment_method_name,
            COALESCE(SUM(t.amount), 0) AS total_amount,
            COUNT(t.id) AS transaction_count
        FROM transactions t
        LEFT JOIN payment_methods p
            ON p.id = t.payment_method_id
           AND p.user_id = t.user_id
        WHERE t.user_id = :user_id
          AND t.type = 'expense'
          AND t.transaction_date >= :start_date
          AND t.transaction_date < :end_date
        GROUP BY p.id, p.name
        ORDER BY total_amount DESC, payment_method_name ASC
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
    ]);

    return $stmt->fetchAll();
}
