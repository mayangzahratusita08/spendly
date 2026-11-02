# Spendly Material 3 Expressive Playground

## Purpose

This is an isolated design experiment for evaluating Material 3 Expressive in the existing PHP-rendered Spendly application. It is not a production redesign and does not replace the existing Material Web integration.

Open the page locally at:

`http://localhost/spendly/m3-expressive-lab.php`

## Implementation

- `m3-expressive-lab.php` is a standalone preview page. It does not include the authenticated app shell, execute database queries, or submit transactions.
- `assets/css/m3-expressive-lab.css` contains styles scoped to the playground page.
- `assets/js/m3-expressive-lab.js` loads the M3 Expressive theme generator and only the custom elements used in this page.
- Existing Spendly pages continue to load their current styles, scripts, and Material Web bundle.

## Library and version

The playground uses **M3 Expressive Web 1.8.0** by Kyra Labs:

- Project: https://github.com/kyra-labs/m3exp-web
- Documentation: https://m3e.kyralabs.dev/
- License: Apache-2.0
- Upstream fidelity target: Jetpack Compose Material 3 1.5.0-beta01, as recorded by the library's `SOURCES.md`.

This is a third-party community implementation, not an official Google web library.

To keep the repository's existing npm dependencies and lockfile untouched, this isolated page loads version-pinned CSS from unpkg and browser modules from esm.sh. The page therefore requires an internet connection for the third-party assets. The package version is pinned in every URL; do not replace these with unversioned `latest` URLs.

The M3E theme generator uses Material Color Utilities to create an **Expressive** dynamic color scheme from the selected seed. The color-scheme CSS is applied to the document root and the playground surfaces reference the resulting semantic color roles.

## Demonstrated components and behavior

- Filled, tonal, outlined, and text buttons.
- Standard single-selection button group with spring-based layout behavior.
- Native checkbox filter chips using M3E component styles.
- Expressive loading indicator and real SVG morphing between the complete 35-shape MaterialShapes collection.
- A shape-family picker with eight featured presets plus a selector for all 35 shapes.
- A spring-physics workbench comparing Standard fast spatial, Expressive fast spatial, and Expressive default spatial motion.
- Standard and connected button groups shown side by side to demonstrate width expansion versus selected-corner morphing.
- Linear progress indicator, including a wavy example.
- Floating action button menu with Expense, Income, and Transfer actions.
- Seed-based Expressive color themes and a light/dark appearance toggle.
- Responsive finance-dashboard composition with illustrative cash-flow, budget, and transaction content.

## Safety and scope

- All financial figures and account details shown are fictional illustrative content.
- No database, authentication, authorization, CSRF, transaction, budget, or account logic is imported or changed.
- No package dependencies are added to `package.json` or `package-lock.json`.
- The existing `@material/web` bundle is not modified.
- The page is marked `noindex, nofollow` and is intended for local design review only.

## Local review checklist

1. Confirm the M3E CSS assets load (HTTP 200) from unpkg.
2. Confirm the shape, motion, theme, and component modules load (HTTP 200) from esm.sh.
3. Select several Material Shapes and confirm the SVG outline morphs between cubic paths rather than cross-fading.
4. Browse all 35 shape presets and run the morph sequence; confirm the default spatial spring overshoot and settlement.
5. Compare standard button-group width expansion with connected button-group corner morphing.
6. Switch between Standard and Expressive spring presets; check damping, stiffness, settle-time, and the moving orb.
7. Confirm the FAB menu opens, closes, supports keyboard navigation, and announces selected actions.
8. Confirm the linear progress slider updates the determinate progress value.
9. Confirm switching between seed colors changes the M3E token palette.
10. Confirm light/dark mode, desktop layout, mobile layout, and `prefers-reduced-motion`.
11. Confirm the normal dashboard and transaction pages still use their existing assets and behavior.

Browser verification is intentionally not claimed by this change. Complete the checklist against the local XAMPP instance before considering any reuse of these components in production.
