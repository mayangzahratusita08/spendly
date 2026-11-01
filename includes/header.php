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
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4.5 4.5h6v6h-6zM13.5 4.5h6v6h-6zM4.5 13.5h6v6h-6zM13.5 13.5h6v6h-6z"/></svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('reports.php') ?>" class="sidebar-link <?= $activeNav === 'reports' ? 'active' : '' ?>" <?= $activeNav === 'reports' ? 'aria-current="page"' : '' ?>>
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M5 19V12M12 19V5M19 19V9M3.5 19.5h17"/></svg>
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
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M6 3.5h12A1.5 1.5 0 0 1 19.5 5v15l-2.5-1.5-2.5 1.5-2.5-1.5-2.5 1.5-2.5-1.5-2.5 1.5V5A1.5 1.5 0 0 1 6 3.5zM8.5 8h7M8.5 12h7M8.5 16h4"/></svg>
                                <span>Transactions</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('budgets.php') ?>" class="sidebar-link <?= $activeNav === 'budgets' ? 'active' : '' ?>" <?= $activeNav === 'budgets' ? 'aria-current="page"' : '' ?>>
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4.5 6.5h15A1.5 1.5 0 0 1 21 8v9.5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h13M3 10h18M16.5 14h.01"/></svg>
                                <span>Budgets</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('categories.php') ?>" class="sidebar-link <?= $activeNav === 'categories' ? 'active' : '' ?>" <?= $activeNav === 'categories' ? 'aria-current="page"' : '' ?>>
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4.5 4.5h6v6h-6zM13.5 4.5h6v6h-6zM4.5 13.5h6v6h-6zM13.5 13.5h6v6h-6z"/></svg>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('payment-methods.php') ?>" class="sidebar-link <?= $activeNav === 'payment-methods' ? 'active' : '' ?>" <?= $activeNav === 'payment-methods' ? 'aria-current="page"' : '' ?>>
                                <svg class="sidebar-link-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><rect x="2.75" y="5.25" width="18.5" height="13.5" rx="2"/><path d="M3 9.5h18M6.5 14.5h3.5"/></svg>
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
                        <svg class="sidebar-signout-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M10 17l5-5-5-5M15 12H3M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <button type="button" class="sidebar-backdrop" aria-label="Close navigation menu" aria-hidden="true" tabindex="-1"></button>

        <div class="app-workspace">
            <header class="mobile-appbar">
                <button
                    type="button"
                    class="sidebar-toggle"
                    aria-label="Open navigation menu"
                    aria-controls="app-sidebar"
                    aria-expanded="false"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <span class="mobile-appbar-title"><?= e($pageTitle ?? 'Overview') ?></span>
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

    <?php if ($loggedIn): ?>
        <header class="workspace-topbar">
            <div class="workspace-topbar-inner">
                <h1 class="workspace-topbar-title"><?= e($pageTitle ?? 'Overview') ?></h1>
                <div class="workspace-topbar-meta">
                    <span>Personal finance</span>
                    <span aria-hidden="true"> · </span>
                    <time datetime="<?= e(date('Y-m-d')) ?>"><?= e(date('l, j F Y')) ?></time>
                </div>
            </div>
        </header>
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
