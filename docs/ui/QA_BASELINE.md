# 8Kommerce UI QA baseline

This checklist is the regression boundary for the staged interface redesign. A phase is not complete until the affected journeys pass at desktop and mobile widths without changing the existing route, request payload, authorization, payment, inventory, or order behaviour.

## Reference viewports

| Name | Viewport | Primary use |
| --- | --- | --- |
| Mobile small | 360 × 800 | Small Android devices |
| Mobile standard | 390 × 844 | iPhone 14-class devices |
| Tablet | 768 × 1024 | Tablet and narrow split-screen |
| Desktop | 1440 × 900 | Standard seller workspace |
| Desktop wide | 1728 × 1117 | Wide tables and storefront editor |

Run each applicable journey in light and dark mode. Storefront templates are checked with their seller-selected palette rather than the seller workspace dark theme.

## Critical journeys

### Authentication and onboarding

- Register with phone verification.
- Sign in with phone and four-digit PIN.
- Request and complete PIN recovery using OTP.
- Complete PIN setup and confirm non-four-digit PINs are rejected without clearing the form.
- Minimize, restore, advance, and dismiss onboarding.

### Seller catalogue and orders

- Create one product with image, category, GHS price, stock and status.
- Trigger validation, confirm valid fields and image selection remain understandable, then submit successfully.
- Create multiple products and comma-separated categories.
- Edit and delete a product after explicit confirmation.
- Filter products by stock status and export inventory.
- Accept, reject, process, mark ready, ship and complete an order where allowed.

### Customers and money

- Find a customer and open purchase history.
- Filter and export payments.
- Request a refund and confirm the transaction state updates.
- Create, share and open an invoice; complete its public payment journey.
- Create and update a coupon.

### Store and account

- Change storefront template, palette, logo and cover; preview inside the phone and publish.
- Confirm the editor scrolls while the phone remains fixed, and the content inside the phone scrolls independently.
- Save seller profile, payout, delivery and pickup settings through the single save action.
- Refresh an unfinished settings form and confirm the draft is restored.
- Resolve a supported payout account and verify the detected provider and account name.
- Open security settings without exposing PIN or password values.
- Start subscription checkout, return from Paystack and open a receipt.

### Customer storefront and fulfilment

- Browse Perfume, Marketplace and Fashion storefronts with mixed image dimensions.
- Search/filter products, open quick view, choose an option and add to cart.
- Change cart quantity without a page reload and verify totals and stock guards.
- Select delivery or pickup, apply a coupon and complete checkout.
- Sign in to order tracking with phone and OTP.
- Review individual and bulk products, accept delivery, report a problem and submit a rating.

### Public, operations and resilience

- Browse landing, sellers, Discover, pricing, privacy and terms pages.
- Use admin OTP sign-in and review sellers, invoices, reconciliation, failed jobs, disputes and promotions.
- Verify 403, 404, 419, 429, 500 and 503 layouts.
- Verify `/up` returns the expected public health state without exposing secrets.
- Verify reduced-motion mode, keyboard-only navigation and visible focus.

## Required page states

Every redesigned page must be checked for:

- Default, loading, empty, success, validation error, permission denied and service-failure states.
- Long names, large GHS values, missing images and unusually long descriptions.
- Keyboard order, visible focus, labelled icon-only controls and dialog focus return.
- 200% browser zoom without clipped actions or overlapping sticky elements.
- No horizontal page scrolling at 360px, except inside an explicitly scrollable data table.
- Unsaved form data surviving server validation; draft-enabled forms also survive an accidental refresh.

## Automated baseline

Before and after every phase run:

```powershell
npm run build
php artisan test
```

The CI application and production-container checks remain required on every pull request.
