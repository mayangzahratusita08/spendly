<?php
/**
 * Spendly - Categories & Payment Methods Management
 *
 * CRUD helpers for user-owned categories and payment methods.
 * Existing financial records are protected from accidental reference deletion.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Retrieve all categories for the current user with reference counts.
 */
function get_managed_categories(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('
        SELECT
            c.id,
            c.name,
            c.type,
            c.color,
            c.created_at,
            (
                SELECT COUNT(*)
                FROM transactions t
                WHERE t.category_id = c.id AND t.user_id = c.user_id
            ) AS transaction_count,
            (
                SELECT COUNT(*)
                FROM budgets b
                WHERE b.category_id = c.id AND b.user_id = c.user_id
            ) AS budget_count
        FROM categories c
        WHERE c.user_id = :user_id
        ORDER BY c.type ASC, c.name ASC
    ');
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

/**
 * Retrieve one category owned by the user.
 */
function get_managed_category_by_id(PDO $pdo, int $userId, int $categoryId): ?array
{
    $stmt = $pdo->prepare('
        SELECT id, name, type, color
        FROM categories
        WHERE id = :id AND user_id = :user_id
        LIMIT 1
    ');
    $stmt->execute([
        ':id' => $categoryId,
        ':user_id' => $userId,
    ]);

    $category = $stmt->fetch();
    return $category ?: null;
}

/**
 * Count financial records that reference a category.
 */
function get_category_reference_counts(PDO $pdo, int $userId, int $categoryId): array
{
    $stmt = $pdo->prepare('
        SELECT
            (SELECT COUNT(*) FROM transactions WHERE category_id = :transaction_category AND user_id = :transaction_user) AS transaction_count,
            (SELECT COUNT(*) FROM budgets WHERE category_id = :budget_category AND user_id = :budget_user) AS budget_count
    ');
    $stmt->execute([
        ':transaction_category' => $categoryId,
        ':transaction_user' => $userId,
        ':budget_category' => $categoryId,
        ':budget_user' => $userId,
    ]);

    $counts = $stmt->fetch() ?: [];
    return [
        'transactions' => (int) ($counts['transaction_count'] ?? 0),
        'budgets' => (int) ($counts['budget_count'] ?? 0),
    ];
}

/**
 * Validate a category name, type, and color.
 *
 * A category type can be changed only while it has no linked transactions or budgets.
 *
 * @return array{valid: bool, errors: array, data: array}
 */
function validate_category_data(PDO $pdo, int $userId, array $input, ?int $excludeCategoryId = null): array
{
    $errors = [];
    $data = [];

    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '') {
        $errors['name'] = 'Category name is required.';
    } elseif (mb_strlen($name) > 60) {
        $errors['name'] = 'Category name must not exceed 60 characters.';
    } else {
        $duplicateSql = '
            SELECT id
            FROM categories
            WHERE user_id = :user_id
              AND name = :name
        ';
        $duplicateParams = [
            ':user_id' => $userId,
            ':name' => $name,
        ];

        $type = trim((string) ($input['type'] ?? ''));
        if (in_array($type, ['income', 'expense'], true)) {
            $duplicateSql .= ' AND type = :type';
            $duplicateParams[':type'] = $type;
        } else {
            $errors['type'] = 'Please select a valid category type.';
        }

        if ($excludeCategoryId !== null) {
            $duplicateSql .= ' AND id <> :exclude_id';
            $duplicateParams[':exclude_id'] = $excludeCategoryId;
        }

        if (!isset($errors['type'])) {
            $duplicateSql .= ' LIMIT 1';
            $duplicateStmt = $pdo->prepare($duplicateSql);
            $duplicateStmt->execute($duplicateParams);

            if ($duplicateStmt->fetch()) {
                $errors['name'] = 'A category with this name and type already exists.';
            }
        }

        if (!isset($errors['type'])) {
            $data['name'] = $name;
            $data['type'] = $type;
        }
    }

    $color = trim((string) ($input['color'] ?? '#9ca3af'));
    if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $color)) {
        $errors['color'] = 'Please choose a valid category color.';
    } else {
        $data['color'] = strtolower($color);
    }

    if ($excludeCategoryId !== null && isset($data['type'])) {
        $existing = get_managed_category_by_id($pdo, $userId, $excludeCategoryId);
        if (!$existing) {
            $errors['general'] = 'Category not found.';
        } elseif ($existing['type'] !== $data['type']) {
            $usage = get_category_reference_counts($pdo, $userId, $excludeCategoryId);
            if ($usage['transactions'] > 0 || $usage['budgets'] > 0) {
                $errors['type'] = 'Category type cannot be changed while transactions or budgets are linked to it.';
            }
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $data,
    ];
}

/**
 * Create a category for one user.
 */
function create_category(PDO $pdo, int $userId, array $data): int
{
    $stmt = $pdo->prepare('
        INSERT INTO categories (user_id, name, type, color, created_at)
        VALUES (:user_id, :name, :type, :color, NOW())
    ');
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $data['name'],
        ':type' => $data['type'],
        ':color' => $data['color'],
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Update a category, scoped to its owner.
 */
function update_category(PDO $pdo, int $userId, int $categoryId, array $data): bool
{
    $stmt = $pdo->prepare('
        UPDATE categories
        SET name = :name,
            type = :type,
            color = :color
        WHERE id = :id AND user_id = :user_id
    ');
    $stmt->execute([
        ':name' => $data['name'],
        ':type' => $data['type'],
        ':color' => $data['color'],
        ':id' => $categoryId,
        ':user_id' => $userId,
    ]);

    return true;
}

/**
 * Delete a category only when no transactions or budgets reference it.
 */
function delete_category(PDO $pdo, int $userId, int $categoryId): bool
{
    $usage = get_category_reference_counts($pdo, $userId, $categoryId);
    if ($usage['transactions'] > 0 || $usage['budgets'] > 0) {
        return false;
    }

    $stmt = $pdo->prepare('
        DELETE FROM categories
        WHERE id = :id AND user_id = :user_id
    ');
    $stmt->execute([
        ':id' => $categoryId,
        ':user_id' => $userId,
    ]);

    return $stmt->rowCount() > 0;
}

/**
 * Retrieve payment methods for one user with transaction reference counts.
 */
function get_managed_payment_methods(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('
        SELECT
            p.id,
            p.name,
            p.created_at,
            (
                SELECT COUNT(*)
                FROM transactions t
                WHERE t.payment_method_id = p.id AND t.user_id = p.user_id
            ) AS transaction_count
        FROM payment_methods p
        WHERE p.user_id = :user_id
        ORDER BY p.name ASC
    ');
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

/**
 * Retrieve one payment method owned by the user.
 */
function get_managed_payment_method_by_id(PDO $pdo, int $userId, int $paymentMethodId): ?array
{
    $stmt = $pdo->prepare('
        SELECT id, name
        FROM payment_methods
        WHERE id = :id AND user_id = :user_id
        LIMIT 1
    ');
    $stmt->execute([
        ':id' => $paymentMethodId,
        ':user_id' => $userId,
    ]);

    $method = $stmt->fetch();
    return $method ?: null;
}

/**
 * Validate a payment method name.
 *
 * @return array{valid: bool, errors: array, data: array}
 */
function validate_payment_method_data(PDO $pdo, int $userId, array $input, ?int $excludePaymentMethodId = null): array
{
    $errors = [];
    $name = trim((string) ($input['name'] ?? ''));

    if ($name === '') {
        $errors['name'] = 'Payment method name is required.';
    } elseif (mb_strlen($name) > 60) {
        $errors['name'] = 'Payment method name must not exceed 60 characters.';
    } else {
        $duplicateSql = '
            SELECT id
            FROM payment_methods
            WHERE user_id = :user_id AND name = :name
        ';
        $params = [
            ':user_id' => $userId,
            ':name' => $name,
        ];

        if ($excludePaymentMethodId !== null) {
            $duplicateSql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludePaymentMethodId;
        }

        $duplicateSql .= ' LIMIT 1';
        $stmt = $pdo->prepare($duplicateSql);
        $stmt->execute($params);

        if ($stmt->fetch()) {
            $errors['name'] = 'A payment method with this name already exists.';
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => ['name' => $name],
    ];
}

/**
 * Create a payment method for one user.
 */
function create_payment_method(PDO $pdo, int $userId, array $data): int
{
    $stmt = $pdo->prepare('
        INSERT INTO payment_methods (user_id, name, created_at)
        VALUES (:user_id, :name, NOW())
    ');
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $data['name'],
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Update a payment method, scoped to its owner.
 */
function update_payment_method(PDO $pdo, int $userId, int $paymentMethodId, array $data): bool
{
    $stmt = $pdo->prepare('
        UPDATE payment_methods
        SET name = :name
        WHERE id = :id AND user_id = :user_id
    ');
    $stmt->execute([
        ':name' => $data['name'],
        ':id' => $paymentMethodId,
        ':user_id' => $userId,
    ]);

    return true;
}

/**
 * Delete a payment method only when no transaction references it.
 */
function delete_payment_method(PDO $pdo, int $userId, int $paymentMethodId): bool
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
        FROM transactions
        WHERE payment_method_id = :id AND user_id = :user_id
    ');
    $stmt->execute([
        ':id' => $paymentMethodId,
        ':user_id' => $userId,
    ]);

    if ((int) $stmt->fetchColumn() > 0) {
        return false;
    }

    $deleteStmt = $pdo->prepare('
        DELETE FROM payment_methods
        WHERE id = :id AND user_id = :user_id
    ');
    $deleteStmt->execute([
        ':id' => $paymentMethodId,
        ':user_id' => $userId,
    ]);

    return $deleteStmt->rowCount() > 0;
}
