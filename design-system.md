# Spendly Design System v1

**Direction:** Material-inspired structure with restrained Liquid Glass translucency.

**Brand character:** Clean, precise, calm, refined, functional.

## 1. Design principles

1. Content first. Financial information must remain the primary visual focus.
2. Use consistent components and spacing across pages.
3. White remains the primary content surface.
4. Green is the brand and action color, not decoration for every component.
5. Translucency is reserved for navigation and floating navigation surfaces.
6. Tables, forms, cards, and financial values use opaque backgrounds.
7. Motion is subtle, purposeful, and respects reduced-motion preferences.
8. No decorative charts, fake metrics, or unnecessary iconography.

## 2. Color tokens

| Token | Value | Purpose |
|---|---|---|
| Page background | #F6F8F6 | Soft neutral canvas |
| Surface | #FFFFFF | Cards, forms, tables |
| Subtle surface | #F2F5F2 | Table headers, secondary areas |
| Border | #E5EBE6 | Dividers and outlines |
| Primary | #47765E | Brand and primary actions |
| Primary hover | #365F49 | Hover state |
| Primary light | #EAF1EC | Active navigation and soft accents |
| Main text | #202923 | Headings and important values |
| Muted text | #68756D | Supporting copy |
| Income | #14805D | Positive financial values |
| Expense | #D94B4B | Negative financial values |
| Warning | #B7791F | Budget warnings |

## 3. Typography

- Use Inter when locally available, with a system sans-serif fallback.
- Page headings: approximately 28–32px, semibold.
- Section headings: approximately 17px, semibold.
- Body: 14–15px.
- Supporting labels: 12–13px.
- Financial values: semibold/bold with tabular-friendly numeric alignment.
- Avoid tiny labels and excessive uppercase text.

## 4. Shape and elevation

- Inputs and buttons: 8px radius.
- Cards and panels: 10–12px radius.
- Dialogs: 14px radius.
- Borders: 1px neutral border.
- Shadows: subtle separation only, never a floating-card pile.

## 5. Glass treatment

Use a translucent white surface with blur only for the sticky site navigation and navigation overlays.

Recommended treatment:
- White at approximately 84–90% opacity.
- 16–18px blur.
- Light neutral border.
- No colorful glow, strong specular shine, or exaggerated depth.

Never apply glass blur to:
- Financial metric cards.
- Transaction tables.
- Forms and input controls.
- Budget progress content.
- Report data and charts.

Provide a nearly opaque white fallback when backdrop-filter is unavailable.

## 6. Layout

- Main content max width: 1240px.
- Header content max width: 1360px.
- Desktop dashboard: three metric columns; activity and setup in a two-column layout.
- Tablet: stack activity and setup as needed.
- Mobile: single-column metrics and panels, horizontally scrollable transaction table.

## 7. Dashboard reference

The dashboard establishes:
- Introductory heading and primary actions.
- Three financial metric cards.
- Monthly budget list with status and progress.
- Recent transaction table.
- Category and payment-method setup panels.

Future page refinements should reuse these tokens and component conventions rather than inventing a separate visual language.
