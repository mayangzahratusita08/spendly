# Spendly Material 3 Design System

**Direction:** A Material 3-inspired personal-finance application built with semantic HTML and CSS.  
**Brand source color:** Spendly forest green.  
**Implementation:** Native PHP-rendered HTML, semantic CSS custom properties, inline SVG icons, and vanilla JavaScript.

This system is based on Material 3's coordinated color, typography, shape, elevation, and component foundations. It is not a collection of rounded cards: the same semantic roles and component rules govern the navigation drawer, app bar, financial summaries, fields, buttons, tables, and status communication.

Reference: [Material 3](https://m3.material.io/) · [Material 3 theming and component guidance](https://developer.android.com/develop/ui/compose/designsystems/material3)

## 1. Design principles

1. **One coherent system.** Color, typography, shape, elevation, and component variants are designed as related foundations.
2. **Semantic color, not decoration.** Use role pairs such as `primary / on-primary`, `primary-container / on-primary-container`, `surface-container / on-surface`, and `error-container / on-error-container`.
3. **Surface hierarchy before shadow.** Separate content with tonal surface containers and outline variants. Reserve shadow for meaningful elevation.
4. **Emphasis has levels.** Use filled buttons for the primary action, outlined or tonal buttons for secondary actions, and text actions for low-emphasis navigation.
5. **Shape is component-specific.** Buttons and navigation indicators may be fully rounded; cards, fields, and data surfaces use measured corner treatments. Do not round every component identically.
6. **Typography follows a scale.** Use clear display/headline/title/body/label roles and sentence case rather than relying on uppercase micro-labels.
7. **Readable financial data.** Currency, dates, status labels, and table rows remain scannable, with tabular numerals for amounts.
8. **Accessibility is structural.** Visible keyboard focus, sufficient contrast, semantic HTML, useful accessible names, and reduced-motion support are required.

## 2. Color scheme

Spendly uses a brand-derived forest-green key color with supporting secondary and tertiary roles. The values below are a practical light theme for the current application.

### Primary, secondary, and tertiary roles

| Role | Value | Usage |
|---|---|---|
| Primary | #416B50 | Filled actions, active states, prominent controls |
| On primary | #FFFFFF | Content placed on primary |
| Primary container | #D7EAD9 | Selected navigation and balance emphasis |
| On primary container | #163A23 | Text/icons placed on primary container |
| Secondary | #526451 | Supporting actions |
| On secondary | #FFFFFF | Content placed on secondary |
| Secondary container | #DCE8D7 | Navigation drawer selected state and supporting accents |
| On secondary container | #344B38 | Selected navigation text/icons |
| Tertiary | #755847 | Optional contrasting accent role |
| On tertiary | #FFFFFF | Content placed on tertiary |
| Tertiary container | #F1DFD2 | Account avatar surface |
| On tertiary container | #2B170B | Avatar content |

### Surface roles

| Role | Value | Usage |
|---|---|---|
| Surface | #F7F9F6 | Application canvas |
| Surface dim | #D8E0D8 | Deep neutral surface role |
| Surface bright | #F7FAF7 | Bright neutral surface role |
| Surface container lowest | #FFFFFF | Main cards, forms, tables |
| Surface container low | #F1F4EF | Permanent navigation drawer and low-emphasis grouping |
| Surface container | #EBF0EA | Budget panel and table headers |
| Surface container high | #E5EBE4 | Progress tracks and hover grouping |
| Surface container highest | #DFE6DE | Stronger neutral grouping |
| On surface | #1B1F1B | Primary content |
| On surface variant | #444B44 | Secondary content |
| Outline | #717971 | Outlined fields and controls |
| Outline variant | #C2CBC2 | Dividers and subtle component boundaries |

### Error roles

| Role | Value | Usage |
|---|---|---|
| Error | #BA1A1A | Expense values, errors, destructive actions |
| On error | #FFFFFF | Content placed on error |
| Error container | #FFDAD6 | Expense and error surfaces |
| On error container | #410002 | Content placed on error container |

Income and budget-warning states use distinct semantic roles. User-defined category colors remain independent of the design-system palette.

## 3. Typography

The target typeface is Roboto, with local system fallbacks. Do not load a remote font dependency.

| Role | Size / line height | Weight | Typical usage |
|---|---|---:|---|
| Display small | 36 / 44px | 400 | Large page-level numeric emphasis when needed |
| Headline large | 32 / 40px | 400 | Page heading |
| Headline medium | 28 / 36px | 400 | Prominent secondary heading |
| Headline small | 24 / 32px | 400 | Section emphasis |
| Title large | 22 / 28px | 400 | Major panel or app-bar title |
| Title medium | 16 / 24px | 500 | Card title and key list item |
| Body large | 16 / 24px | 400 | Prominent content |
| Body medium | 14 / 20px | 400 | Default application content |
| Body small | 12 / 16px | 400 | Captions and metadata |
| Label large | 14 / 20px | 500 | Button and control labels |
| Label medium | 12 / 16px | 500 | Table headings, chips |

Use sentence case for navigation and section labels. Keep currency figures tabular and do not reduce body text below comfortable reading sizes.

## 4. Shape scale

| Role | Radius | Usage |
|---|---:|---|
| Extra small | 4px | Dense text fields |
| Small | 8px | Type selectors, compact action controls |
| Medium | 12px | Cards and grouped panels |
| Large | 16px | Navigation drawer destination indicator and larger component groupings |
| Extra large | 24px | Optional prominent container |
| Full | 999px | Buttons and chips where the component calls for a pill form |

Shape communicates component role. Cards and fields are not full pills. Buttons are rounded by default, while compact table actions can remain outlined rectangles.

## 5. Elevation and surfaces

- The application canvas uses `surface`.
- The permanent navigation drawer uses `surface-container-low`.
- Main cards and data surfaces use `surface-container-lowest`.
- The budget overview uses `surface-container` as a grouped section.
- Table headings and subdued supporting areas use tonal containers.
- Shadows stay subtle. Avoid decorative stacked card shadows.
- Translucent blur is not part of the authenticated app shell in this system; use solid tonal surfaces for more consistent hierarchy and readability.

## 6. Components

### App bar
- Desktop uses a 64px top app bar with current page title and concise date/context.
- Mobile uses a 64px bar with a menu button and current page title.
- App-bar surfaces use the application surface role and a restrained bottom divider.

### Navigation drawer
- Desktop uses a 280px permanent drawer.
- Mobile uses a modal slide-in drawer with the existing backdrop, Escape handling, and focus management.
- Destination rows use 48px minimum height, 16px corner treatment, and 24px line icons.
- The selected destination uses `secondary-container` and `on-secondary-container`, not a decorative accent bar.
- Group labels use label-medium styling and sentence case.

### Buttons
- Filled primary: main page action.
- Outlined: secondary action.
- Text: low-emphasis inline navigation.
- Compact table actions use the small shape rather than the full button pill.
- Hover, focus, pressed, and disabled states must remain visible and consistent.

### Cards
- Cards use medium shape and tonal or outlined separation.
- Summary metrics use neutral containers, with the current balance receiving primary-container emphasis.
- Income and expense are communicated by financial text roles, not by tinting every entire card.

### Text fields
- Outlined fields use the extra-small shape and a 56px minimum height where the component is a standard text field.
- Focus uses a 2px primary outline.
- Validation uses error roles and remains adjacent to the field.
- Native browser input behavior is preserved.

### Chips and status
- Chips use short, legible labels and appropriate tonal backgrounds.
- Budget status is communicated using both text and color.
- Category colors remain user-defined.

### Tables and lists
- Use clear column labels, 56px-or-comfortable row density, subtle dividers, and aligned monetary values.
- Avoid excessive header decoration.
- Hover feedback is low contrast and does not hide category or amount information.

## 7. Layout and responsive behavior

- Desktop application shell: 280px permanent drawer and flexible content workspace.
- Content max width: 1440px.
- Desktop dashboard: welcome heading and primary action, three metric summaries, a full-width budget overview, then activity plus account-setup panels.
- Tablet: replace the permanent drawer with a modal drawer and collapse secondary dashboard columns.
- Mobile: stack summary cards and dashboard panels, keep touch targets usable, and retain horizontal scrolling for wide transaction tables.

## 8. Accessibility and motion

- All interactive elements expose visible keyboard focus.
- Form labels remain programmatically associated with their controls.
- SVG navigation icons are decorative and hidden from assistive technology when an adjacent label is present.
- Selected navigation uses `aria-current="page"`.
- Progress components retain `role="progressbar"` and numeric ARIA values.
- Reduced-motion preferences remove nonessential transitions and animations.
- Color is never the only way income, expense, or budget state is communicated.

## 9. Implementation constraints

- No CSS framework, icon package, remote font, or chart library is introduced.
- PHP business logic, queries, validation, authentication, CSRF protection, and ownership checks are outside the scope of UI changes.
- Keep shared token and component styles in `assets/css/app.css`.
- Keep reusable navigation and app-bar markup in `includes/header.php`.
