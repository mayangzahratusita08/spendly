<?php
/**
 * Spendly - Transaction Management Helpers
 *
 * Provides database operations and validation for transactions.
 * All operations enforce strict user ownership checks.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Retrieves categories belonging to the user, optionally filtered by type.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param string|null $type 'income' | 'expense' | null
 * @return array
 */
function get_user_categories(PDO $pdo, int $userId, ?string $type = null): array
{
    if ($type !== null && in_array($type, ['income', 'expense'], true)) {
        $stmt = $pdo->prepare('
            SELECT id, name, type, color
            FROM categories
            WHERE user_id = :user_id AND type = :type
            ORDER BY name ASC
        ');
        $stmt->execute([':user_id' => $userId, ':type' => $type]);
    } else {
        $stmt = $pdo->prepare('
            SELECT id, name, type, color
            FROM categories
            WHERE user_id = :user_id
            ORDER BY type ASC, name ASC
        ');
        $stmt->execute([':user_id' => $userId]);
    }

    return $stmt->fetchAll();
}

/**
 * Retrieves payment methods belonging to the user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array
 */
function get_user_payment_methods(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('
        SELECT id, name
        FROM payment_methods
        WHERE user_id = :user_id
        ORDER BY name ASC
    ');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Validates transaction form inputs against ownership, types, and formatting rules.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param array $input
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validate_transaction_data(PDO $pdo, int $userId, array $input): array
{
    $errors = [];
    $data = [];

    // 1. Transaction Type
    $type = trim((string) ($input['type'] ?? ''));
    if (!in_array($type, ['income', 'expense'], true)) {
        $errors['type'] = 'Please select a valid transaction type (Income or Expense).';
    } else {
        $data['type'] = $type;
    }

    // 2. Amount (must be positive number)
    $rawAmount = trim((string) ($input['amount'] ?? ''));
    // Normalize comma to dot if entered with European decimal notation
    $normalizedAmount = str_replace(',', '.', $rawAmount);

    if ($rawAmount === '') {
        $errors['amount'] = 'Amount is required.';
    } elseif (!is_numeric($normalizedAmount) || (float) $normalizedAmount <= 0) {
        $errors['amount'] = 'Amount must be a positive number greater than zero.';
    } elseif ((float) $normalizedAmount > 999999999999.99) {
        $errors['amount'] = 'Amount exceeds maximum supported limit.';
    } else {
        $data['amount'] = round((float) $normalizedAmount, 2);
    }

    // 3. Category (must exist, belong to user, and match transaction type)
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    if ($categoryId === false || $categoryId <= 0) {
        $errors['category_id'] = 'Please select a category.';
    } else {
        $catStmt = $pdo->prepare('
            SELECT id, type
            FROM categories
            WHERE id = :id AND user_id = :user_id
            LIMIT 1
        ');
        $catStmt->execute([':id' => $categoryId, ':user_id' => $userId]);
        $category = $catStmt->fetch();

        if (!$category) {
            $errors['category_id'] = 'The selected category does not exist or does not belong to you.';
        } elseif (!empty($data['type']) && $category['type'] !== $data['type']) {
            $errors['category_id'] = sprintf(
                'The selected category is for %ss, but this transaction is marked as %s.',
                $category['type'],
                $data['type']
            );
        } else {
            $data['category_id'] = (int) $category['id'];
        }
    }

    // 4. Payment Method (optional; if selected, must belong to user)
    $paymentMethodId = $input['payment_method_id'] ?? null;
    if ($paymentMethodId === '' || $paymentMethodId === null) {
        $data['payment_method_id'] = null;
    } else {
        $pmIdInt = filter_var($paymentMethodId, FILTER_VALIDATE_INT);
        if ($pmIdInt === false || $pmIdInt <= 0) {
            $errors['payment_method_id'] = 'Invalid payment method selected.';
        } else {
            $pmStmt = $pdo->prepare('
                SELECT id
                FROM payment_methods
                WHERE id = :id AND user_id = :user_id
                LIMIT 1
            ');
            $pmStmt->execute([':id' => $pmIdInt, ':user_id' => $userId]);
            if (!$pmStmt->fetch()) {
                $errors['payment_method_id'] = 'The selected payment method does not belong to you.';
            } else {
                $data['payment_method_id'] = $pmIdInt;
            }
        }
    }

    // 5. Transaction Date (required, valid YYYY-MM-DD)
    $dateStr = trim((string) ($input['transaction_date'] ?? ''));
    if ($dateStr === '') {
        $errors['transaction_date'] = 'Transaction date is required.';
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $dateStr);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $dateStr) {
            $errors['transaction_date'] = 'Please enter a valid date in YYYY-MM-DD format.';
        } else {
            $data['transaction_date'] = $dateStr;
        }
    }

    // 6. Description / Note (optional, max 255 chars)
    $description = trim((string) ($input['description'] ?? ''));
    if (mb_strlen($description) > 255) {
        $errors['description'] = 'Description must not exceed 255 characters.';
    } else {
        $data['description'] = $description !== '' ? $description : null;
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'data'   => $data,
    ];
}

/**
 * Inserts a new transaction record for the authenticated user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param array $data Validated data array
 * @return int Inserted transaction ID
 */
function create_transaction(PDO $pdo, int $userId, array $data): int
{
    $stmt = $pdo->prepare('
        INSERT INTO transactions (
            user_id,
            category_id,
            payment_method_id,
            type,
            amount,
            description,
            transaction_date,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :category_id,
            :payment_method_id,
            :type,
            :amount,
            :description,
            :transaction_date,
            NOW(),
            NOW()
        )
    ');

    $stmt->execute([
        ':user_id'           => $userId,
        ':category_id'       => $data['category_id'],
        ':payment_method_id' => $data['payment_method_id'],
        ':type'              => $data['type'],
        ':amount'            => $data['amount'],
        ':description'       => $data['description'],
        ':transaction_date'  => $data['transaction_date'],
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Updates an existing transaction strictly enforcing user ownership.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $transactionId
 * @param array $data Validated data array
 * @return bool True if updated, false otherwise
 */
function update_transaction(PDO $pdo, int $userId, int $transactionId, array $data): bool
{
    // Verify that the record exists and belongs to the user
    $check = $pdo->prepare('SELECT id FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1');
    $check->execute([':id' => $transactionId, ':user_id' => $userId]);
    if (!$check->fetch()) {
        return false;
    }

    $stmt = $pdo->prepare('
        UPDATE transactions
        SET
            category_id       = :category_id,
            payment_method_id = :payment_method_id,
            type              = :type,
            amount            = :amount,
            description       = :description,
            transaction_date  = :transaction_date,
            updated_at        = NOW()
        WHERE id = :id AND user_id = :user_id
    ');

    $stmt->execute([
        ':category_id'       => $data['category_id'],
        ':payment_method_id' => $data['payment_method_id'],
        ':type'              => $data['type'],
        ':amount'            => $data['amount'],
        ':description'       => $data['description'],
        ':transaction_date'  => $data['transaction_date'],
        ':id'                => $transactionId,
        ':user_id'           => $userId,
    ]);

    return true;
}

/**
 * Deletes a transaction strictly enforcing user ownership.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $transactionId
 * @return bool True if deleted, false otherwise
 */
function delete_transaction(PDO $pdo, int $userId, int $transactionId): bool
{
    $stmt = $pdo->prepare('
        DELETE FROM transactions
        WHERE id = :id AND user_id = :user_id
    ');
    $stmt->execute([
        ':id'      => $transactionId,
        ':user_id' => $userId,
    ]);

    return $stmt->rowCount() > 0;
}

/**
 * Fetches a single transaction by ID, scoped to the authenticated user.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $transactionId
 * @return array|null
 */
function get_transaction_by_id(PDO $pdo, int $userId, int $transactionId): ?array
{
    $stmt = $pdo->prepare('
        SELECT
            t.id,
            t.user_id,
            t.category_id,
            t.payment_method_id,
            t.type,
            t.amount,
            t.description,
            t.transaction_date,
            t.created_at,
            t.updated_at,
            c.name AS category_name,
            c.color AS category_color,
            p.name AS payment_method_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN payment_methods p ON t.payment_method_id = p.id
        WHERE t.id = :id AND t.user_id = :user_id
        LIMIT 1
    ');
    $stmt->execute([
        ':id'      => $transactionId,
        ':user_id' => $userId,
    ]);

    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Retrieves transactions for a user with optional filtering by type, category, and date range.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param array $filters ['type' => string, 'category_id' => int, 'start_date' => string, 'end_date' => string]
 * @return array
 */
function get_filtered_transactions(PDO $pdo, int $userId, array $filters = []): array
{
    $sql = '
        SELECT
            t.id,
            t.user_id,
            t.category_id,
            t.payment_method_id,
            t.type,
            t.amount,
            t.description,
            t.transaction_date,
            t.created_at,
            t.updated_at,
            c.name AS category_name,
            c.color AS category_color,
            p.name AS payment_method_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN payment_methods p ON t.payment_method_id = p.id
        WHERE t.user_id = :user_id
    ';

    $params = [':user_id' => $userId];

    // Filter by type
    if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
        $sql .= ' AND t.type = :type';
        $params[':type'] = $filters['type'];
    }

    // Filter by category
    if (!empty($filters['category_id'])) {
        $catId = (int) $filters['category_id'];
        if ($catId > 0) {
            $sql .= ' AND t.category_id = :category_id';
            $params[':category_id'] = $catId;
        }
    }

    // Filter by start date
    if (!empty($filters['start_date'])) {
        $sql .= ' AND t.transaction_date >= :start_date';
        $params[':start_date'] = $filters['start_date'];
    }

    // Filter by end date
    if (!empty($filters['end_date'])) {
        $sql .= ' AND t.transaction_date <= :end_date';
        $params[':end_date'] = $filters['end_date'];
    }

    $sql .= ' ORDER BY t.transaction_date DESC, t.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
