<?php
/**
 * Spendly Material 3 Expressive playground.
 *
 * This page is deliberately standalone: it does not load the authenticated
 * application shell, query the database, or modify production page styling.
 */
$labCssVersion = @filemtime(__DIR__ . '/assets/css/m3-expressive-lab.css') ?: '1';
$labJsVersion = @filemtime(__DIR__ . '/assets/js/m3-expressive-lab.js') ?: '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="An isolated Material 3 Expressive design playground for Spendly.">
    <title>Spendly · Material 3 Expressive Lab</title>

    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preconnect" href="https://esm.sh" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!--
      Third-party M3 Expressive Web, pinned to 1.8.0.
      It is not an official Google library. Only this playground loads it.
    -->
    <link rel="stylesheet" href="https://unpkg.com/@kyralabs/m3e-tokens@1.8.0/fonts/fonts.css">
    <link rel="stylesheet" href="https://unpkg.com/@kyralabs/m3e-tokens@1.8.0/dist/generated/tokens.css">
    <link rel="stylesheet" href="https://unpkg.com/@kyralabs/m3e-tokens@1.8.0/dist/generated/theme.css">
    <link rel="stylesheet" href="https://unpkg.com/@kyralabs/m3e-tokens@1.8.0/fonts/material-symbols.css">
    <link rel="stylesheet" href="https://unpkg.com/@kyralabs/m3e-css@1.8.0/dist/index.css">
    <link rel="stylesheet" href="assets/css/m3-expressive-lab.css?v=<?= (int) $labCssVersion ?>">
</head>
<body>
<div class="lab-shell">
    <header class="lab-topbar">
        <a class="lab-brand" href="dashboard.php" aria-label="Spendly dashboard">
            <span class="lab-brand-mark" aria-hidden="true"><span>S</span></span>
            <span class="lab-brand-copy">
                <strong>Spendly</strong>
                <span>DESIGN EXPLORATION</span>
            </span>
        </a>

        <div class="lab-topbar-actions">
            <span class="lab-version-note"><span class="lab-status-dot"></span> M3 EXPRESSIVE · 1.8.0</span>
            <button class="m3e-icon-button m3e-icon-button--standard lab-theme-toggle" id="lab-theme-toggle" type="button" aria-label="Switch to dark theme" aria-pressed="false">
                <span class="m3e-icon" aria-hidden="true" data-fill>dark_mode</span>
            </button>
            <a class="m3e-button m3e-button--outlined m3e-button--size-s lab-back-link" href="dashboard.php">
                <span class="m3e-icon" aria-hidden="true">arrow_back</span>
                Back to app
            </a>
        </div>
    </header>

    <main>
        <section class="lab-hero" aria-labelledby="lab-page-title">
            <div class="lab-hero-copy">
                <p class="lab-eyebrow"><span class="lab-eyebrow-line"></span> A DESIGN SYSTEM EXPERIMENT</p>
                <h1 id="lab-page-title">Financial clarity,<br><em>with expression.</em></h1>
                <p class="lab-hero-description">
                    Exploring how Material 3 Expressive can bring more character, clearer hierarchy,
                    and purposeful motion to Spendly.
                </p>
                <div class="lab-hero-actions">
                    <a class="m3e-button m3e-button--filled m3e-button--size-m" href="#component-workbench">
                        Explore components
                        <span class="m3e-icon" aria-hidden="true">arrow_downward</span>
                    </a>
                    <span class="lab-preview-caption">Independent preview · no live account data</span>
                </div>
            </div>

            <div class="lab-hero-visual" aria-hidden="true">
                <div class="lab-hero-orbit lab-hero-orbit-one"></div>
                <div class="lab-hero-orbit lab-hero-orbit-two"></div>
                <div class="lab-hero-material-shape lab-hero-material-shape-back">
                    <svg viewBox="0 0 1 1" focusable="false"><path id="lab-hero-shape-back" d=""></path></svg>
                </div>
                <div class="lab-hero-material-shape lab-hero-material-shape-front">
                    <svg viewBox="0 0 1 1" focusable="false"><path id="lab-hero-morph-path" d=""></path></svg>
                </div>
                <span class="lab-hero-star lab-hero-star-one">✳</span>
                <span class="lab-hero-star lab-hero-star-two">✦</span>
                <div class="lab-hero-caption">
                    <span>01 / 04</span>
                    <span>Material Shapes · Spring Motion</span>
                </div>
            </div>
        </section>

        <section class="lab-dashboard-preview" aria-labelledby="lab-dashboard-title">
            <div class="lab-section-heading">
                <div>
                    <p class="lab-section-kicker">THE PRODUCT, REIMAGINED</p>
                    <h2 id="lab-dashboard-title">A more expressive overview</h2>
                </div>
                <span class="lab-demo-badge">ILLUSTRATIVE DATA</span>
            </div>

            <div class="lab-dashboard-topline">
                <div>
                    <p class="lab-greeting">MONDAY, NOVEMBER 2, 2026</p>
                    <h3>Good morning, Alex <span aria-hidden="true">✳</span></h3>
                    <p class="lab-muted">Here is the shape of your money this month.</p>
                </div>
                <button class="m3e-button m3e-button--filled m3e-button--size-m" type="button" data-lab-action="add-transaction">
                    <span class="m3e-icon" aria-hidden="true">add</span>
                    Add transaction
                </button>
            </div>

            <article class="m3e-card m3e-card--filled lab-balance-card">
                <div class="lab-balance-main">
                    <div class="lab-card-label">
                        <span>Available balance</span>
                        <span class="lab-balance-chip"><span class="m3e-icon" aria-hidden="true">trending_up</span> 8.4% this month</span>
                    </div>
                    <p class="lab-balance-value">Rp 24.850.000</p>
                    <p class="lab-balance-footnote">Across your tracked accounts</p>
                </div>
                <div class="lab-balance-decoration" aria-hidden="true">
                    <div class="lab-balance-decoration-ring"></div>
                    <div class="lab-balance-decoration-core"><span>Rp</span></div>
                    <span class="lab-decoration-star lab-decoration-star-one">✳</span>
                    <span class="lab-decoration-star lab-decoration-star-two">✦</span>
                </div>
                <div class="lab-balance-footer">
                    <span>MONTHLY SNAPSHOT</span>
                    <span>01 — 30 NOVEMBER</span>
                </div>
            </article>

            <div class="lab-metric-grid">
                <article class="m3e-card m3e-card--elevated lab-metric-card">
                    <div class="lab-metric-icon lab-metric-icon-income"><span class="m3e-icon" aria-hidden="true">south_west</span></div>
                    <p class="lab-metric-label">Income</p>
                    <p class="lab-metric-value">Rp 8.500.000</p>
                    <p class="lab-metric-support"><span class="lab-positive">+12.5%</span> from last month</p>
                </article>
                <article class="m3e-card m3e-card--elevated lab-metric-card">
                    <div class="lab-metric-icon lab-metric-icon-expense"><span class="m3e-icon" aria-hidden="true">north_east</span></div>
                    <p class="lab-metric-label">Expenses</p>
                    <p class="lab-metric-value">Rp 4.250.000</p>
                    <p class="lab-metric-support"><span class="lab-positive">−6.2%</span> from last month</p>
                </article>
                <article class="m3e-card m3e-card--elevated lab-metric-card">
                    <div class="lab-metric-icon lab-metric-icon-savings"><span class="m3e-icon" aria-hidden="true">savings</span></div>
                    <p class="lab-metric-label">Saved this month</p>
                    <p class="lab-metric-value">Rp 4.250.000</p>
                    <p class="lab-metric-support">50% of your income</p>
                </article>
            </div>

            <div class="lab-dashboard-grid">
                <article class="m3e-card m3e-card--outlined lab-panel lab-spending-panel">
                    <div class="lab-panel-heading">
                        <div>
                            <p class="lab-panel-overline">YOUR SPENDING RHYTHM</p>
                            <h3>Cash flow</h3>
                        </div>
                        <m3e-button-group variant="connected" size="s" button-variant="tonal" selection-mode="single" disallow-empty-selection label="Cash flow period" id="cash-flow-range">
                            <m3e-button-group-item value="week" selected>Week</m3e-button-group-item>
                            <m3e-button-group-item value="month">Month</m3e-button-group-item>
                        </m3e-button-group>
                    </div>

                    <div class="lab-chart-summary">
                        <strong>Rp 4.250.000</strong>
                        <span>total expenses</span>
                    </div>
                    <div class="lab-chart" role="img" aria-label="Illustrative weekly expense bars for Monday through Sunday">
                        <div class="lab-chart-column"><span class="lab-chart-value">420k</span><div class="lab-chart-bar" style="--bar-height: 39%"></div><span class="lab-chart-label">Mon</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">680k</span><div class="lab-chart-bar" style="--bar-height: 64%"></div><span class="lab-chart-label">Tue</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">510k</span><div class="lab-chart-bar" style="--bar-height: 49%"></div><span class="lab-chart-label">Wed</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">790k</span><div class="lab-chart-bar lab-chart-bar-highlight" style="--bar-height: 78%"></div><span class="lab-chart-label">Thu</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">360k</span><div class="lab-chart-bar" style="--bar-height: 33%"></div><span class="lab-chart-label">Fri</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">920k</span><div class="lab-chart-bar" style="--bar-height: 91%"></div><span class="lab-chart-label">Sat</span></div>
                        <div class="lab-chart-column"><span class="lab-chart-value">280k</span><div class="lab-chart-bar" style="--bar-height: 27%"></div><span class="lab-chart-label">Sun</span></div>
                    </div>
                    <div class="lab-chart-legend"><span class="lab-legend-dot"></span> Daily expenses <span class="lab-chart-legend-note">Values are for visual preview only</span></div>
                </article>

                <article class="m3e-card m3e-card--outlined lab-panel lab-budget-panel">
                    <div class="lab-panel-heading">
                        <div>
                            <p class="lab-panel-overline">A LITTLE ROOM TO BREATHE</p>
                            <h3>Budget watch</h3>
                        </div>
                        <button class="m3e-icon-button m3e-icon-button--standard" type="button" aria-label="Budget options" data-lab-action="budget-options">
                            <span class="m3e-icon" aria-hidden="true">more_horiz</span>
                        </button>
                    </div>
                    <div class="lab-budget-total">
                        <div><strong>Rp 2.850.000</strong><span> of Rp 4.000.000</span></div>
                        <span class="lab-budget-percent">71%</span>
                    </div>
                    <m3e-linear-progress value="0.71" shape="wavy" aria-label="Monthly budget used, 71 percent"></m3e-linear-progress>
                    <div class="lab-budget-remaining">
                        <span>You're doing well</span>
                        <span>Rp 1.150.000 left</span>
                    </div>
                    <div class="lab-budget-divider"></div>
                    <div class="lab-budget-category">
                        <div class="lab-budget-category-icon"><span class="m3e-icon" aria-hidden="true">restaurant</span></div>
                        <div class="lab-budget-category-info"><strong>Food & dining</strong><span>Rp 850k of Rp 1.2m</span></div>
                        <span class="lab-budget-category-state">71%</span>
                    </div>
                    <m3e-linear-progress value="0.71" aria-label="Food and dining budget used, 71 percent"></m3e-linear-progress>
                    <div class="lab-budget-category">
                        <div class="lab-budget-category-icon lab-budget-category-icon-transport"><span class="m3e-icon" aria-hidden="true">directions_car</span></div>
                        <div class="lab-budget-category-info"><strong>Transport</strong><span>Rp 320k of Rp 500k</span></div>
                        <span class="lab-budget-category-state">64%</span>
                    </div>
                    <m3e-linear-progress value="0.64" aria-label="Transport budget used, 64 percent"></m3e-linear-progress>
                </article>
            </div>

            <article class="m3e-card m3e-card--outlined lab-panel lab-transactions-panel">
                <div class="lab-panel-heading">
                    <div>
                        <p class="lab-panel-overline">THE LATEST MOVEMENTS</p>
                        <h3>Recent transactions</h3>
                    </div>
                    <a class="m3e-button m3e-button--text m3e-button--size-s" href="#component-workbench">View patterns <span class="m3e-icon" aria-hidden="true">arrow_forward</span></a>
                </div>
                <div class="lab-transaction-list">
                    <div class="lab-transaction-row">
                        <div class="lab-transaction-icon lab-tx-food"><span class="m3e-icon" aria-hidden="true">restaurant</span></div>
                        <div class="lab-transaction-name"><strong>Lunch at Common Grounds</strong><span>Food & dining · Today, 12:45</span></div>
                        <span class="lab-transaction-amount lab-expense-amount">− Rp 85.000</span>
                    </div>
                    <div class="lab-transaction-row">
                        <div class="lab-transaction-icon lab-tx-income"><span class="m3e-icon" aria-hidden="true">payments</span></div>
                        <div class="lab-transaction-name"><strong>Monthly salary</strong><span>Income · Today, 09:00</span></div>
                        <span class="lab-transaction-amount lab-income-amount">+ Rp 6.500.000</span>
                    </div>
                    <div class="lab-transaction-row">
                        <div class="lab-transaction-icon lab-tx-travel"><span class="m3e-icon" aria-hidden="true">train</span></div>
                        <div class="lab-transaction-name"><strong>Commuter Line</strong><span>Transport · Yesterday, 18:20</span></div>
                        <span class="lab-transaction-amount lab-expense-amount">− Rp 8.000</span>
                    </div>
                </div>
            </article>
        </section>

        <section class="lab-workbench" id="component-workbench" aria-labelledby="lab-workbench-title">
            <div class="lab-section-heading">
                <div>
                    <p class="lab-section-kicker">THE BUILDING BLOCKS</p>
                    <h2 id="lab-workbench-title">Expressive component workbench</h2>
                    <p class="lab-muted">Try the controls. The color theme, selection states, shape morphs, and progress motion are interactive.</p>
                </div>
                <span class="lab-component-count">01 — 06</span>
            </div>

            <div class="lab-workbench-grid">
                <article class="m3e-card m3e-card--outlined lab-component-card lab-button-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">01</span><div><h3>Buttons & emphasis</h3><p>Different levels of action.</p></div></div>
                    <div class="lab-button-samples">
                        <button class="m3e-button m3e-button--filled m3e-button--size-m" type="button" data-lab-action="primary-button">
                            <svg class="lab-inline-shape" viewBox="0 0 1 1" aria-hidden="true" data-material-shape="Cookie4Sided"><path></path></svg>
                            Filled
                        </button>
                        <button class="m3e-button m3e-button--tonal m3e-button--size-m" type="button" data-lab-action="tonal-button">
                            <svg class="lab-inline-shape" viewBox="0 0 1 1" aria-hidden="true" data-material-shape="Flower"><path></path></svg>
                            Tonal
                        </button>
                        <button class="m3e-button m3e-button--outlined m3e-button--size-m" type="button" data-lab-action="outlined-button">
                            <svg class="lab-inline-shape" viewBox="0 0 1 1" aria-hidden="true" data-material-shape="Heart"><path></path></svg>
                            Outlined
                        </button>
                        <button class="m3e-button m3e-button--text m3e-button--size-m" type="button" data-lab-action="text-button">
                            <svg class="lab-inline-shape" viewBox="0 0 1 1" aria-hidden="true" data-material-shape="Clover8Leaf"><path></path></svg>
                            Text action
                        </button>
                    </div>
                    <div class="lab-button-shape-row" role="group" aria-label="Button shape variants">
                        <span>Component shape</span>
                        <button class="m3e-button m3e-button--outlined m3e-button--size-s m3e-button--round" type="button" data-lab-action="round-shape">Round</button>
                        <button class="m3e-button m3e-button--outlined m3e-button--size-s m3e-button--square" type="button" data-lab-action="square-shape">Square</button>
                    </div>
                    <p class="lab-component-footnote">The icons use M3E MaterialShapes presets. The button surfaces retain their native M3E emphasis, shape tokens, and state layers.</p>
                </article>

                <article class="m3e-card m3e-card--outlined lab-component-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">02</span><div><h3>Spring button group</h3><p>Selection with spatial feedback.</p></div></div>
                    <div class="lab-group-demo">
                        <div class="lab-group-demo-heading"><strong><svg viewBox="0 0 1 1" data-material-shape="Slanted" aria-hidden="true"><path></path></svg> Standard · spatial expansion</strong><span>Press a segment</span></div>
                        <m3e-button-group class="lab-workbench-button-group lab-wide-button-group" variant="standard" size="xl" shape="round" button-variant="tonal" selection-mode="single" disallow-empty-selection label="Dashboard period" id="workbench-period">
                            <m3e-button-group-item value="day" icon="today">Today</m3e-button-group-item>
                            <m3e-button-group-item value="week" icon="date_range" selected>This week</m3e-button-group-item>
                            <m3e-button-group-item value="month" icon="calendar_month">This month</m3e-button-group-item>
                        </m3e-button-group>
                        <p class="lab-group-explainer">The pressed item grows while its neighbors compress. The total group width stays constant.</p>
                    </div>
                    <div class="lab-group-demo lab-group-demo-connected">
                        <div class="lab-group-demo-heading"><strong><svg viewBox="0 0 1 1" data-material-shape="Cookie7Sided" aria-hidden="true"><path></path></svg> Connected · shape morph</strong><span>Change selection</span></div>
                        <m3e-button-group class="lab-workbench-button-group lab-wide-button-group" variant="connected" size="xl" shape="square" button-variant="outlined" selection-mode="single" disallow-empty-selection label="Chart type" id="connected-period">
                            <m3e-button-group-item value="bars" icon="bar_chart" selected>Bars</m3e-button-group-item>
                            <m3e-button-group-item value="line" icon="show_chart">Line</m3e-button-group-item>
                            <m3e-button-group-item value="donut" icon="donut_large">Donut</m3e-button-group-item>
                        </m3e-button-group>
                        <p class="lab-group-explainer">The selected segment changes its corner geometry while the connected group remains aligned.</p>
                    </div>
                    <p class="lab-component-footnote" id="lab-period-feedback" aria-live="polite">This week selected. Try both group styles.</p>
                </article>

                <article class="m3e-card m3e-card--outlined lab-component-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">03</span><div><h3>Filter chips</h3><p>Selection that stays lightweight.</p></div></div>
                    <div class="m3e-chip-set lab-chip-set" role="group" aria-label="Transaction type filters">
                        <label class="m3e-filter-chip m3e-filter-chip--expressive lab-filter-chip">
                            <input class="m3e-filter-chip__input" type="checkbox" checked>
                            <span class="m3e-filter-chip__leading" aria-hidden="true">
                                <svg class="m3e-icon lab-chip-shape" viewBox="0 0 1 1" data-material-shape="Cookie4Sided"><path></path></svg>
                                <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                            </span>
                            <span class="m3e-filter-chip__label">All activity</span>
                        </label>
                        <label class="m3e-filter-chip m3e-filter-chip--expressive lab-filter-chip">
                            <input class="m3e-filter-chip__input" type="checkbox">
                            <span class="m3e-filter-chip__leading" aria-hidden="true">
                                <svg class="m3e-icon lab-chip-shape" viewBox="0 0 1 1" data-material-shape="Flower"><path></path></svg>
                                <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                            </span>
                            <span class="m3e-filter-chip__label">Income</span>
                        </label>
                        <label class="m3e-filter-chip m3e-filter-chip--expressive lab-filter-chip">
                            <input class="m3e-filter-chip__input" type="checkbox">
                            <span class="m3e-filter-chip__leading" aria-hidden="true">
                                <svg class="m3e-icon lab-chip-shape" viewBox="0 0 1 1" data-material-shape="Gem"><path></path></svg>
                                <span class="m3e-icon m3e-filter-chip__check" data-fill>check</span>
                            </span>
                            <span class="m3e-filter-chip__label">Expenses</span>
                        </label>
                    </div>
                    <p class="lab-component-footnote">Expressive chips morph their own component shape on selection; the leading MaterialShape swaps to the check mark.</p>
                </article>

                <article class="m3e-card m3e-card--outlined lab-component-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">04</span><div><h3>Expressive feedback</h3><p>Progress and shape transformation.</p></div></div>
                    <div class="lab-feedback-demo">
                        <div class="lab-feedback-shape" aria-hidden="true">
                            <svg viewBox="0 0 1 1" data-material-shape="Sunny"><path></path></svg>
                        </div>
                        <m3e-loading-indicator contained aria-label="Expressive loading indicator"></m3e-loading-indicator>
                        <div class="lab-feedback-progress">
                            <div class="lab-feedback-progress-heading"><strong>Monthly savings goal</strong><span id="lab-progress-value">62%</span></div>
                            <m3e-linear-progress id="lab-demo-progress" value="0.62" shape="wavy" aria-label="Monthly savings goal, 62 percent"></m3e-linear-progress>
                        </div>
                    </div>
                    <label class="lab-range-label" for="lab-progress-range"><span>Adjust progress</span><input id="lab-progress-range" type="range" min="0" max="100" value="62"></label>
                </article>

                <article class="m3e-card m3e-card--outlined lab-component-card lab-theme-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">05</span><div><h3>Dynamic color</h3><p>One seed, a coordinated palette.</p></div></div>
                    <div class="lab-seed-swatches" role="group" aria-label="Choose an expressive theme seed">
                        <button class="lab-seed-button is-selected" type="button" data-seed="#416B50" aria-label="Forest green theme" aria-pressed="true"><span style="--swatch: #416B50"></span><small>Forest</small></button>
                        <button class="lab-seed-button" type="button" data-seed="#4268B2" aria-label="Cobalt blue theme" aria-pressed="false"><span style="--swatch: #4268B2"></span><small>Cobalt</small></button>
                        <button class="lab-seed-button" type="button" data-seed="#8054A5" aria-label="Plum theme" aria-pressed="false"><span style="--swatch: #8054A5"></span><small>Plum</small></button>
                        <button class="lab-seed-button" type="button" data-seed="#A34E33" aria-label="Terracotta theme" aria-pressed="false"><span style="--swatch: #A34E33"></span><small>Clay</small></button>
                    </div>
                    <p class="lab-component-footnote" id="lab-theme-feedback" aria-live="polite">Expressive scheme · Forest green seed</p>
                </article>

                <article class="m3e-card m3e-card--outlined lab-component-card">
                    <div class="lab-component-card-heading"><span class="lab-component-index">06</span><div><h3>Contextual actions</h3><p>Actions appear when they are useful.</p></div></div>
                    <div class="lab-action-demo">
                        <div class="lab-action-shape" aria-hidden="true">
                            <svg viewBox="0 0 1 1" data-material-shape="Burst"><path></path></svg>
                        </div>
                        <m3e-fab-menu label="Quick add" icon="add" close-icon="close" size="large" color="tertiary" align="end">
                            <m3e-fab-menu-item icon="receipt_long" value="expense">Expense</m3e-fab-menu-item>
                            <m3e-fab-menu-item icon="payments" value="income">Income</m3e-fab-menu-item>
                            <m3e-fab-menu-item icon="savings" value="transfer">Transfer</m3e-fab-menu-item>
                        </m3e-fab-menu>
                        <div class="lab-action-copy"><strong>Quick add</strong><span>Open the expressive FAB menu to reveal related actions.</span></div>
                    </div>
                    <p class="lab-component-footnote">Try the FAB. Select an action to trigger a local preview message.</p>
                </article>
            </div>

            <section class="lab-morph-lab" aria-labelledby="lab-morph-title">
                <div class="lab-section-heading">
                    <div>
                        <p class="lab-section-kicker">MATERIAL SHAPES, NOT CSS BLOBS</p>
                        <h2 id="lab-morph-title">A shape is a living component.</h2>
                        <p class="lab-muted">Morph between real Material 3 Expressive shape presets. Choose a shape or play the sequence.</p>
                    </div>
                    <span class="lab-component-count">35 MATERIAL SHAPES</span>
                </div>

                <div class="lab-morph-layout">
                    <div class="lab-morph-stage m3e-card m3e-card--filled">
                        <div class="lab-morph-stage-topline">
                            <span class="lab-morph-stage-overline">LIVE MORPH PREVIEW</span>
                            <span class="lab-morph-state" id="lab-morph-state">READY</span>
                        </div>
                        <div class="lab-morph-canvas">
                            <div class="lab-morph-halo lab-morph-halo-one"></div>
                            <div class="lab-morph-halo lab-morph-halo-two"></div>
                            <svg viewBox="0 0 1 1" role="img" aria-label="Cookie4Sided material shape" id="lab-morph-svg">
                                <path id="lab-morph-path" d=""></path>
                            </svg>
                            <span class="lab-morph-canvas-caption">MATERIAL SHAPE MORPH</span>
                        </div>
                        <div class="lab-morph-stage-footer">
                            <div><span>FROM / TO</span><strong id="lab-morph-pair">Cookie4Sided → Heart</strong></div>
                            <div><span>SPRING PROGRESS</span><strong id="lab-morph-progress">0%</strong></div>
                        </div>
                        <div class="lab-morph-actions">
                            <button class="m3e-button m3e-button--tonal m3e-button--size-m" type="button" id="lab-morph-play">
                                <span class="m3e-icon" aria-hidden="true">play_arrow</span> Play sequence
                            </button>
                            <button class="m3e-icon-button m3e-icon-button--standard" type="button" id="lab-morph-next" aria-label="Morph to next shape">
                                <span class="m3e-icon" aria-hidden="true">arrow_forward</span>
                            </button>
                            <span class="lab-morph-motion-note">Expressive default spatial spring</span>
                        </div>
                    </div>

                    <aside class="lab-shape-picker m3e-card m3e-card--outlined" aria-label="Material shape presets">
                        <div class="lab-shape-picker-heading">
                            <div><strong>Shape family</strong><span>Pick the next form</span></div>
                            <span class="lab-shape-picker-count">08 / 35</span>
                        </div>
                        <div class="lab-shape-presets" id="lab-shape-presets">
                            <button type="button" class="lab-shape-preset is-selected" data-shape="Cookie4Sided" aria-pressed="true"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Cookie</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Heart" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Heart</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Flower" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Flower</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Clover8Leaf" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Clover</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Sunny" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Sunny</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Gem" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Gem</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="Ghostish" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Ghost</span></button>
                            <button type="button" class="lab-shape-preset" data-shape="PixelCircle" aria-pressed="false"><svg viewBox="0 0 1 1" aria-hidden="true"><path></path></svg><span>Pixel</span></button>
                        </div>
                        <label class="lab-all-shapes-control" for="lab-all-shapes">
                            <span>Browse all 35 Material Shapes</span>
                            <select id="lab-all-shapes" aria-label="Choose any of the 35 Material shapes">
                                <option value="Cookie4Sided">Cookie4Sided</option>
                            </select>
                        </label>
                        <p class="lab-component-footnote">Each preset is a RoundedPolygon. The interpolated outline is calculated as cubic Bézier geometry, not cross-faded images.</p>
                    </aside>
                </div>
            </section>

            <section class="lab-spring-lab" aria-labelledby="lab-spring-title">
                <div class="lab-section-heading">
                    <div>
                        <p class="lab-section-kicker">MOTION HAS PERSONALITY</p>
                        <h2 id="lab-spring-title">Feel the spring, not just the easing.</h2>
                        <p class="lab-muted">Compare the actual spatial spring parameters used by the M3 Expressive motion scheme.</p>
                    </div>
                    <span class="lab-component-count">SPRING PHYSICS</span>
                </div>

                <article class="lab-spring-card m3e-card m3e-card--outlined">
                    <div class="lab-spring-card-heading">
                        <div>
                            <span class="lab-panel-overline">FAST SPATIAL SPRING</span>
                            <h3 id="lab-spring-name">Expressive · Fast spatial</h3>
                            <p id="lab-spring-description">More overshoot, lower damping, and a longer return to rest.</p>
                        </div>
                        <button class="m3e-button m3e-button--filled m3e-button--size-m" type="button" id="lab-spring-replay">
                            <span class="m3e-icon" aria-hidden="true">replay</span> Replay motion
                        </button>
                    </div>
                    <div class="lab-spring-track" id="lab-spring-track" aria-label="Spring motion demo track">
                        <div class="lab-spring-track-line"></div>
                        <div class="lab-spring-orb" id="lab-spring-orb"><span></span></div>
                        <span class="lab-spring-track-start">START</span>
                        <span class="lab-spring-track-end">TARGET</span>
                    </div>
                    <div class="lab-spring-presets" role="group" aria-label="Motion spring presets">
                        <button class="lab-spring-preset" type="button" data-spring-preset="standard-fast" aria-pressed="false"><strong>Standard fast</strong><span>ζ 0.9 · k 1400</span></button>
                        <button class="lab-spring-preset is-selected" type="button" data-spring-preset="expressive-fast" aria-pressed="true"><strong>Expressive fast</strong><span>ζ 0.6 · k 800</span></button>
                        <button class="lab-spring-preset" type="button" data-spring-preset="expressive-default" aria-pressed="false"><strong>Expressive default</strong><span>ζ 0.8 · k 380</span></button>
                    </div>
                    <div class="lab-spring-readout">
                        <div><span>DAMPING RATIO</span><strong id="lab-spring-damping">0.6</strong></div>
                        <div><span>STIFFNESS</span><strong id="lab-spring-stiffness">800</strong></div>
                        <div><span>SETTLE TIME</span><strong id="lab-spring-duration">420 ms</strong></div>
                        <div><span>LIVE POSITION</span><strong id="lab-spring-position">0.00</strong></div>
                    </div>
                </article>
            </section>

            <div class="lab-live-region" id="lab-live-region" role="status" aria-live="polite">The component playground is ready.</div>
        </section>
    </main>

    <footer class="lab-footer">
        <span>SPENDLY · DESIGN RESEARCH</span>
        <span>Independent M3 Expressive Web experiment · Illustrative data only</span>
    </footer>
</div>

<script type="module" src="assets/js/m3-expressive-lab.js?v=<?= (int) $labJsVersion ?>"></script>
</body>
</html>
