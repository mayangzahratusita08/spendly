# Spendly Material 3 Expressive Playground

## Purpose

An isolated design experiment for evaluating Material 3 Expressive in Spendly's PHP-rendered application. It is not a production redesign and does not replace the existing Material Web integration.

Local URL:

`http://localhost/spendly/m3-expressive-lab.php`

## Implementation

- `m3-expressive-lab.php`: standalone preview page. It does not include the authenticated app shell, query the database, or submit transactions.
- `assets/css/m3-expressive-lab.css`: styles scoped to this page. Native M3E component shape, color, and motion tokens are not overridden.
- `assets/js/m3-expressive-lab.js`: dynamic theme generation, real MaterialShapes interpolation, spring motion, and component interactions.
- Existing Spendly pages continue to use their current assets and Material Web bundle.

## Library and version

Uses Kyra Labs' third-party **M3 Expressive Web 1.8.0**, licensed Apache-2.0:

- Project: https://github.com/kyra-labs/m3exp-web
- Documentation: https://m3e.kyralabs.dev/
- This is a community implementation, not an official Google web library.
- Its upstream fidelity target is Jetpack Compose Material 3 1.5.0-beta01, as recorded in the library's `SOURCES.md`.

The CDN URLs are version-pinned. The CSS links follow the published package export layout:

- `@kyralabs/m3e-tokens/fonts/fonts.css`
- `@kyralabs/m3e-tokens/dist/generated/tokens.css`
- `@kyralabs/m3e-tokens/dist/generated/theme.css`
- `@kyralabs/m3e-tokens/fonts/material-symbols.css`
- `@kyralabs/m3e-css/dist/index.css`

Browser modules are loaded from esm.sh:

- `@kyralabs/m3exp-web/theme`
- `@kyralabs/m3e-shapes`
- `@kyralabs/m3e-motion`
- M3E custom elements: button group, FAB menu, linear progress, loading indicator, and ripple.

The page requires an internet connection for these third-party assets. Do not replace pinned URLs with unversioned `latest` URLs.

## The building blocks: native M3E components

The component workbench uses the published M3E component classes/custom elements:

1. **Buttons & emphasis:** native filled, tonal, outlined, and text buttons; native round and square shape variants; preset MaterialShapes are rendered as SVG iconography inside the buttons.
2. **Spring button groups:** native standard group (press-width redistribution) and connected group (selected/pressed corner morphing). The examples keep their own M3E round/square component shapes.
3. **Filter chips:** native checkbox-based expressive filter chips. The leading MaterialShape icon gives way to the selected check, while the chip's own expressive shape morphs using M3E's component tokens.
4. **Expressive feedback:** native loading indicator (its own morphing shape choreography) and wavy linear progress component; a MaterialShape accent illustrates the shape family.
5. **Dynamic color:** Expressive theme generated from a seed color. Light and dark schemes are regenerated explicitly when the appearance toggle changes.
6. **Contextual actions:** native M3E FAB menu with its opening/closing spring, focus behavior, item staggering, and local preview actions; a MaterialShape accent complements the menu.

The delegated `ripple.js` entry enables the M3E press ripple/state-layer behavior on the plain HTML buttons and filter chips.

## Shape and motion labs

- `@kyralabs/m3e-shapes@1.8.0` supplies all 35 MaterialShapes as `RoundedPolygon` presets, `Morph`, and SVG path serialization.
- The shape lab interpolates cubic Bézier geometry between presets. It does not cross-fade two pictures or approximate morphing with `border-radius`.
- `@kyralabs/m3e-motion@1.8.0` supplies Compose-compatible spring calculations.
- Shape morphs use the expressive **default spatial** spring (damping ratio 0.8, stiffness 380).
- The motion workbench compares Standard fast spatial (0.9 / 1400), Expressive fast spatial (0.6 / 800), and Expressive default spatial (0.8 / 380).
- `prefers-reduced-motion` is respected by the custom morph and spring demos. The M3E components also provide their own reduced-motion behavior.

## Safety and scope

- All financial values and account details are fictional illustrative content.
- No database, authentication, authorization, CSRF, transaction, budget, or account logic is imported or changed.
- No npm dependency or lockfile change.
- The existing `@material/web` bundle is not modified.
- The page is marked `noindex, nofollow` and intended for local design review only.

## Local review checklist

1. Confirm all version-pinned CSS assets load with HTTP 200 from unpkg, and all browser modules load from esm.sh.
2. Test filled/tonal/outlined/text buttons, round/square variants, ripple, hover, focus, and pressed states.
3. Test filter chip selection and confirm its icon/check and component shape transition.
4. Compare standard button-group width redistribution with connected button-group corner morphing.
5. Confirm the loading indicator morphs, the wavy progress renders, and the progress slider updates the value.
6. Open the contextual FAB menu, test keyboard focus and dismissal, and select each local preview action.
7. Browse all 35 MaterialShapes, run the morph sequence, and inspect the spatial spring overshoot/settlement.
8. Compare all three spring presets and their live readout.
9. Change seed colors in both light and dark mode.
10. Test desktop/mobile layout and `prefers-reduced-motion`.
11. Confirm the normal dashboard and transaction pages still use their existing assets and behavior.

JavaScript static inspection has been performed, but PHP lint and browser/XAMPP verification have not. Complete the local checklist before considering production adoption.
