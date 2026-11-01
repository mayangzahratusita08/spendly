# Spendly Material 3 Design System v2

**Direction:** Material 3-inspired, calm, tonal, and finance-first.  
**Brand character:** Clear, considered, dependable, and quietly modern.  
**Implementation:** Semantic CSS tokens and native HTML controls. No UI framework, icon package, font CDN, or chart dependency is required.

## 1. Design principles

1. **Financial clarity comes first.** Amounts, dates, statuses, and transaction descriptions must remain easy to scan.
2. **Use semantic color roles.** Components consume primary, surface, outline, error, and supporting roles instead of inventing page-specific colors.
3. **Build hierarchy with tonal surfaces.** Use small changes in surface tone before adding borders, shadows, or decorative effects.
4. **Keep actions predictable.** Filled buttons indicate the primary action, outlined buttons indicate secondary actions, and text actions stay visually quiet.
5. **Use a consistent shape language.** Controls are comfortably rounded, cards have a larger radius, and navigation indicators use pill shapes.
6. **Make state visible.** Hover, focus, selected, disabled, success, warning, and error states must be distinguishable.
7. **Never rely on color alone.** Financial direction and budget status also use labels or signs.
8. **Preserve readable data surfaces.** Tables, forms, and financial information remain opaque. Blur is limited to navigation surfaces.
9. **Respect reduced motion.** Transitions are brief and nonessential; reduced-motion preferences remove them.

## 2. Color system

### Brand and surface roles

| Role | Value | Use |
|---|---|---|
| Page / surface | #F5F7F4 | Main application canvas |
| Surface container lowest | #FFFFFF | Cards, forms, tables |
| Surface container low | #F0F4EF | Subtle grouping, table headers |
| Surface container | #EAF0E9 | Secondary tonal surfaces |
| Surface container high | #E4EBE3 | Progress tracks and deeper surface separation |
| Surface container highest | #DFE7DE | Stronger neutral grouping |
| Primary | #386A4F | Main actions and active iconography |
| On primary | #FFFFFF | Text and icons on primary |
| Primary container | #DCEBDD | Selected navigation and balance card |
| On primary container | #153B26 | Text and icons on primary container |
| Secondary container | #E2EAE1 | Supporting financial metric surface |
| Outline | #737D74 | Form outlines and visible boundaries |
| Outline variant | #C4CEC4 | Dividers and low-emphasis borders |
| Error | #B3261E | Expense values and destructive states |
| Error container | #F9DEDB | Expense metric and error surfaces |

### Financial semantics

| Role | Foreground | Container |
|---|---|---|
| Income | #13795B | #DFF3E8 |
| Expense | #B3261E | #F9DEDB |
| Warning | #8A5A0A | #FCEFCF |
| Information | #315F9B | #DCE8F8 |

Category colors remain user-configurable and are not replaced by the system palette.

## 3. Typography

Use a system-first stack: Roboto when available, then Inter, followed by platform sans-serif fallbacks. Do not load a remote font.

| Role | Typical size | Weight | Purpose |
|---|---:|---:|---|
| Page title | 28–35px | 600–650 | Current page context |
| Section title | 17–18px | 600–650 | Panels and grouped content |
| Body | 14–15px | 400 | Descriptions and regular content |
| Label | 12–14px | 500–600 | Form labels and table headings |
| Supporting | 12–13px | 400–500 | Captions and helper text |
| Financial value | 24–30px | 650–700 | Balances and summaries |

Financial numbers use tabular numerals where supported. Uppercase section labels are reserved for short navigation and dashboard context labels.

## 4. Shape and elevation

| Token | Value | Typical use |
|---|---:|---|
| Small | 10px | Inputs and compact controls |
| Medium | 16px | Cards and panels |
| Large | 24px | Larger dialog or prominent grouping |
| Full | 999px | Buttons, chips, and navigation indicators |

Elevation is deliberately restrained. The dashboard uses a near-flat card treatment with a subtle one-pixel shadow. Avoid stacked shadows, glowing edges, or decorative gradients.

## 5. Component rules

### Navigation
- Desktop uses a persistent 264px navigation drawer.
- Each destination has a consistent 20px outlined icon and label.
- The active destination uses a full-width primary-container pill with on-primary-container content.
- The account identity and sign-out action stay anchored at the bottom.
- Tablet and mobile use the existing accessible slide-in drawer, backdrop, Escape handling, and focus management.

### Buttons
- Primary: filled primary surface, white label, full pill shape.
- Secondary: outlined surface, neutral label, full pill shape.
- Compact actions retain a 36px minimum height.
- Hover and pressed states are perceptible without large movement or dramatic elevation.

### Cards and dashboard metrics
- Standard cards and panels use opaque white surfaces, a 16px radius, and outline-variant borders.
- The current-balance metric uses the primary container.
- Income uses a muted secondary container; expenses use the error container.
- Budget status is shown with a tonal status chip and a labeled progress indicator.

### Forms
- Inputs use a 48px minimum touch target, white fill, 10px radius, and visible outline.
- Focus uses a two-pixel primary outline without a heavy glow.
- Validation messages remain adjacent to their relevant fields.
- Native browser control behavior is preserved.

### Tables and data
- Headers use a low-tonal surface and compact but legible labels.
- Row separators remain subtle.
- Hover feedback does not obscure amounts or categories.
- Currency values align consistently and use tabular numerals.

### Alerts and status
- Success, warning, error, and information use distinct tonal containers.
- Status chips include text, not color alone.
- Destructive actions use the error role only when relevant.

## 6. Layout and responsive behavior

- Main content is centered within a maximum width of approximately 1320px.
- Desktop dashboard uses three summary metrics, a full-width budget panel, then a wide activity area with a narrower account-setup column.
- Tablet collapses the activity and setup columns into a single flow.
- Mobile stacks metric cards and panels, keeps actions comfortably tappable, and permits horizontal scrolling for wide transaction tables.
- The sidebar becomes a drawer at the existing 980px breakpoint.

## 7. Motion and accessibility

- Use short color and surface transitions for interaction feedback.
- Avoid animating financial values or delaying access to page content.
- Keep keyboard focus visible on links, buttons, and fields.
- Preserve semantic heading order, table headers, form labels, and accessible names.
- Respect `prefers-reduced-motion`.
- Validate text and control contrast during browser review when the rendered environment is available.

## 8. Implementation constraints

- Do not introduce a frontend framework or external icon dependency.
- Do not modify transaction calculations, authentication, database queries, CSRF handling, or ownership checks as part of visual refinements.
- Keep content surfaces opaque; translucency remains limited to the application navigation shell.
- Prefer shared CSS tokens and component selectors over page-specific inline styling.
