<?php
/**
 * Spendly - Shared Application Shell
 *
 * Authenticated pages use the persistent sidebar.
 * Guest pages keep a compact header without application navigation.
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
<div class="app-shell <?= $loggedIn ? 'app-shell-authenticated' : 'app-shell-guest' ?>">
    <?php if ($loggedIn): ?>
        <aside class="app-sidebar" id="app-sidebar" aria-label="Main sidebar">
            <div class="sidebar-brand-area">
                <a href="<?= base_url('dashboard.php') ?>" class="sidebar-brand" aria-label="Spendly dashboard">
                    <span class="sidebar-brand-mark" aria-hidden="true">S</span>
                    <span class="sidebar-brand-copy">
                        <span class="brand-name">Spendly</span>
                        <span class="sidebar-brand-caption">PERSONAL FINANCE</span>
                    </span>
                </a>
            </div>

            <nav class="sidebar-navigation" aria-label="Primary navigation">
                <div class="sidebar-nav-group">
                    <p class="sidebar-section-label">OVERVIEW</p>
                    <ul class="sidebar-links">
                        <li>
                            <a href="<?= base_url('dashboard.php') ?>" class="sidebar-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" <?= $activeNav === 'dashboard' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">01</span>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('reports.php') ?>" class="sidebar-link <?= $activeNav === 'reports' ? 'active' : '' ?>" <?= $activeNav === 'reports' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">02</span>
                                <span>Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-nav-group">
                    <p class="sidebar-section-label">MANAGE</p>
                    <ul class="sidebar-links">
                        <li>
                            <a href="<?= base_url('transactions.php') ?>" class="sidebar-link <?= $activeNav === 'transactions' ? 'active' : '' ?>" <?= $activeNav === 'transactions' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">03</span>
                                <span>Transactions</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('budgets.php') ?>" class="sidebar-link <?= $activeNav === 'budgets' ? 'active' : '' ?>" <?= $activeNav === 'budgets' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">04</span>
                                <span>Budgets</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('categories.php') ?>" class="sidebar-link <?= $activeNav === 'categories' ? 'active' : '' ?>" <?= $activeNav === 'categories' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">05</span>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('payment-methods.php') ?>" class="sidebar-link <?= $activeNav === 'payment-methods' ? 'active' : '' ?>" <?= $activeNav === 'payment-methods' ? 'aria-current="page"' : '' ?>>
                                <span class="sidebar-link-mark" aria-hidden="true">06</span>
                                <span>Payment methods</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-account">
                    <span class="sidebar-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($user['full_name'] ?? 'A'), 0, 1))) ?></span>
                    <span class="sidebar-account-copy">
                        <strong><?= e($user['full_name'] ?? 'Account') ?></strong>
                        <small>Personal account</small>
                    </span>
                </div>
                <form action="<?= base_url('logout.php') ?>" method="POST" class="sidebar-signout-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="sidebar-signout">
                        <span aria-hidden="true">↗</span>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <button type="button" class="sidebar-backdrop" aria-label="Close navigation menu" tabindex="-1"></button>

        <div class="app-workspace">
            <header class="mobile-appbar">
                <button
                    type="button"
                    class="sidebar-toggle"
                    aria-label="Open navigation menu"
                    aria-controls="app-sidebar"
                    aria-expanded="false"
                >
                    <span aria-hidden="true">☰</span>
                </button>
                <a href="<?= base_url('dashboard.php') ?>" class="mobile-appbar-brand">Spendly</a>
                <span class="mobile-appbar-context"><?= e($pageTitle ?? 'Overview') ?></span>
            </header>
    <?php else: ?>
        <header class="guest-header">
            <div class="guest-header-inner">
                <a href="<?= base_url('index.php') ?>" class="brand">
                    <span class="brand-name">Spendly</span>
                    <span class="brand-tagline">Track your spending. Stay in control.</span>
                </a>
                <nav class="guest-navigation" aria-label="Account navigation">
                    <a href="<?= base_url('login.php') ?>" class="nav-link <?= $activeNav === 'login' ? 'active' : '' ?>">Sign in</a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-primary btn-sm">Create account</a>
                </nav>
            </div>
        </header>
        <div class="app-workspace app-workspace-guest">
    <?php endif; ?>

    <main class="app-main">
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
