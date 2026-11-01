<?php
/**
 * Spendly - Payment Method Management
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/catalog.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$paymentMethods = get_managed_payment_methods(get_db_connection(), $userId);

$pageTitle = 'Payment Methods';
$activeNav = 'payment-methods';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <div>
        <h1>Payment Methods</h1>
        <p>Manage how you record the way money moves in and out.</p>
    </div>
    <a href="<?= base_url('payment-method-create.php') ?>" class="btn btn-primary">+ Add Method</a>
</div>

<section class="card">
    <div class="section-heading">
        <div>
            <h2>Your Payment Methods</h2>
            <p class="card-subtitle">Methods are available when adding or editing transactions.</p>
        </div>
        <span class="milestone-badge"><?= count($paymentMethods) ?> methods</span>
    </div>

    <?php if (empty($paymentMethods)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128179;</div>
            <h3>No payment methods yet</h3>
            <p>Add a payment method such as Cash, Bank Transfer, or E-Wallet.</p>
            <a href="<?= base_url('payment-method-create.php') ?>" class="btn btn-primary btn-sm">Add First Method</a>
        </div>
    <?php else: ?>
        <div class="catalog-list">
            <?php foreach ($paymentMethods as $method): ?>
                <article class="catalog-item">
                    <div class="catalog-item-main">
                        <span class="payment-method-icon">&#128179;</span>
                        <div>
                            <strong><?= e($method['name']) ?></strong>
                            <small><?= (int) $method['transaction_count'] ?> linked transactions</small>
                        </div>
                    </div>
                    <div class="catalog-item-actions">
                        <a href="<?= base_url('payment-method-edit.php?id=' . (int) $method['id']) ?>" class="btn-action edit">Edit</a>
                        <?php if ((int) $method['transaction_count'] > 0): ?>
                            <span class="catalog-protected-label">In use</span>
                        <?php else: ?>
                            <form action="<?= base_url('payment-method-delete.php') ?>" method="POST" onsubmit="return confirm('Delete this payment method? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $method['id'] ?>">
                                <button type="submit" class="btn-action delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="catalog-note">
    Payment methods linked to transactions are protected from deletion. You can still rename them.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
