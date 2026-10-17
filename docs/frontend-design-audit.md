# 8Kommerce frontend design audit

## Existing implementation

The application uses server-rendered Laravel Blade templates with Tailwind CSS, Alpine.js, Vite, and Chart.js. Shared dashboard, public, guest, admin, and application layouts provide the main structure, while Blade components cover the core button, input, and modal patterns.

## Findings

- Large radii, pill actions, shadows, gradients, and nested cards were used too frequently, weakening hierarchy.
- Important operational information competed with decorative dashboard treatments and oversized spacing.
- Similar controls used different colours, sizes, and corner radii across screens.
- Public catalogue cards relied on lift and scale effects instead of clear borders and information structure.
- Tables, filters, forms, and empty states did not share one consistent visual system.
- The standalone marketing page used a separate font setup and could fall back to the browser serif font.

## Design system

- Typography: Source Sans 3 with a compact 15px application base and restrained heading scale.
- Colour: neutral grey-green canvas, white working surfaces, dark ink, and one deep green primary accent.
- Shape: 4px status treatments, 6px controls, and 8px containers.
- Surfaces: thin borders and flat fills by default; shadows reserved for floating or modal elevation.
- Spacing: denser 4px-based rhythm with smaller navigation, form, and table gaps.
- Actions: rectangular primary, secondary, and danger controls with consistent 40px minimum height.
- Data: tabular numerals, consolidated summaries, underlined filters, divided rows, and semantic status colour.
- Accessibility: visible focus treatment, minimum touch targets, reduced-motion support, stronger contrast, and horizontal table/filter overflow where necessary.

## Implementation approach

The redesign is applied primarily through shared CSS tokens, compatibility rules, layouts, and Blade components. High-traffic dashboard, catalogue, payment, customer, invoice, and product-management views receive focused structural refinements. Routes, controllers, form names, methods, JavaScript hooks, API contracts, and database behaviour are unchanged.
