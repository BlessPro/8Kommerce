# 8Kommerce design system foundation

The seller workspace is an operational business application. It uses restrained surfaces, compact spacing and strong hierarchy. Storefront templates may be more expressive, but they inherit the same accessibility and interaction rules.

## Tokens

Tokens live in `resources/css/app.css`; components consume semantic names rather than hard-coded Tailwind colours.

| Role | Light value | Usage |
| --- | --- | --- |
| Brand | `#073B4C` | Primary actions, active navigation and links |
| Brand dark | `#032B3A` | Hover and high-emphasis text |
| Accent | `#7A1F3D` | Focus, selective brand emphasis |
| Shell | `#FFF8F2` | Warm branded surfaces |
| Canvas | `#F4F7F7` | Application background |
| Surface | `#FFFFFF` | Main panels and controls |
| Danger | `#B42318` | Delete, logout and destructive outcomes |
| Success | `#067647` | Completed and verified outcomes |
| Warning | `#946200` | Pending or attention-required outcomes |
| Information | `#175CD3` | Neutral system information |

Dark mode changes token values, not the meaning of the component. Storefront seller palettes remain independent from application tokens.

## Shape, spacing and elevation

- Standard radius: 6px; large panels: 8px; compact badges: 4px.
- Controls are at least 40px high; primary mobile controls should normally be 44–48px.
- Prefer a border over a shadow. Use the raised shadow only for dialogs, floating save bars and menus.
- Use the existing Tailwind spacing scale. Page sections normally use 24–32px separation; related fields use 12–20px.

## Buttons

Use `<x-ui.button>` for new work.

| Variant | Meaning |
| --- | --- |
| `brand` | Main action on a page |
| `primary` | High-emphasis neutral action |
| `secondary` | Alternative or reversible action |
| `success` | Explicit confirmation/completion |
| `warning` | Action requiring caution |
| `danger` | Delete, cancel, logout or destructive change |
| `ghost` | Low-emphasis toolbar action |

Only one main brand action should dominate a section. Colour communicates meaning; it is not decorative.

```blade
<x-ui.button variant="brand" icon="plus">Add product</x-ui.button>
<x-ui.button variant="danger" icon="trash">Delete product</x-ui.button>
```

## Icons

`<x-ui.icon>` uses build-time Iconify output and the Tabler family. It does not call an icon CDN or API at runtime. Decorative icons are hidden from assistive technology; standalone icons require a `label`.

- Navigation and standard buttons: 20px.
- Dense table metadata: 16px.
- Empty-state illustration marks: 24–32px.
- Keep icon and text centred within the same flex container.

Do not mix outline families in the primary product navigation. Brand marks such as WhatsApp may retain their official form.

## Forms

- Use `<x-ui.field>` to group a label, help text and error.
- Use `.lp-form-control` or `<x-text-input>` for controls.
- Required state is written for screen readers and not represented by colour alone.
- Errors appear beside the field and use `role="alert"`.
- Do not clear valid input after a server-side validation error.
- Draft-enabled forms identify themselves using the existing `data-form-draft` behaviour.
- Currency labels and formatted output use `GHS` consistently.
- PIN inputs use masked numeric entry and exactly four digits.

## Status, alert and empty state

Use `<x-ui.badge>` for compact state, `<x-ui.alert>` for page feedback and `<x-ui.empty-state>` when a collection has no results. Status text must remain understandable without colour.

```blade
<x-ui.badge tone="success" icon="check">Paid</x-ui.badge>
<x-ui.alert tone="warning" title="Payment pending">We are still confirming this transaction.</x-ui.alert>
```

## Tables and responsive lists

- Keep the primary identity in the first column and actions in the last.
- Align numeric values to the right where comparison matters.
- Use sentence-case headers and compact status badges.
- On small screens, convert operational rows into structured cards; do not simply shrink a wide table.
- Horizontal scrolling is acceptable for audit/reconciliation tables where preserving columns is essential.

## Loading

Skeletons belong inside the region whose content is loading. Do not cover the entire application for ordinary navigation. Respect `prefers-reduced-motion`.

## Accessibility contract

- Maintain a visible focus indicator with at least a 2px-equivalent perimeter.
- Icon-only controls require an accessible name.
- Dialogs trap focus, close on Escape and return focus to their trigger.
- Colour is never the sole indicator of error, state or selection.
- Content remains usable at 200% zoom and at a 360px viewport.
