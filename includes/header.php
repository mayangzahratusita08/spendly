<?php
/**
 * Spendly - Shared Header Template
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

$title = isset($pageTitle) ? e($pageTitle) . ' — Spendly' : 'Spendly — Personal Expense Tracker';
$loggedIn = is_logged_in();
$user = current_user();
$activeNav = $activeNav ?? '';
$flashMessages = get_flash_messages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <header class="site-header">
        <nav class="site-nav">
            <a href="<?= base_url($loggedIn ? 'dashboard.php' : 'index.php') ?>" class="brand">
                <span class="brand-name">Spendly</span>
                <span class="brand-tagline">Track your spending. Stay in control.</span>
            </a>

            <button type="button" class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">
                &#9776;
            </button>

            <div class="nav-menu">
                <?php if ($loggedIn): ?>
                    <ul class="nav-links">
                        <li>
                            <a href="<?= base_url('dashboard.php') ?>" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('transactions.php') ?>" class="nav-link <?= $activeNav === 'transactions' ? 'active' : '' ?>">
                                Transactions
                            </a>
                        </li>
                    </ul>
                    <div class="nav-user">
                        <span class="user-badge"><?= e($user['full_name'] ?? 'Account') ?></span>
                        <form action="<?= base_url('logout.php') ?>" method="POST" style="margin: 0; display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-signout">Sign Out</button>
                        </form>
                    </div>
                <?php else: ?>
                    <ul class="nav-links">
                        <li>
                            <a href="<?= base_url('login.php') ?>" class="nav-link <?= $activeNav === 'login' ? 'active' : '' ?>">
                                Sign In
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('register.php') ?>" class="btn btn-primary btn-sm">
                                Create Account
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="app-main">
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
