# 8Kommerce Project Handbook

This handbook explains 8Kommerce from the business idea through product logic,
software architecture, security, testing, CI/CD, deployment, and day-to-day
operations. It is written for a founder who wants to understand both **why the
system exists** and **how the code makes it work**.

> Source of truth: when this document and the code disagree, the code and its
> automated tests are authoritative. Update this handbook whenever a major
> workflow or architectural decision changes.

## 1. The idea

Small sellers often operate across disconnected tools:

- products and stock are remembered manually or stored in spreadsheets;
- orders arrive through WhatsApp and social media chats;
- customers send payment screenshots;
- delivery details live in messages;
- customers repeatedly ask for order updates;
- the seller cannot easily measure sales, stock, customers, or growth.

8Kommerce turns those fragments into one mobile-friendly commerce workspace.
A seller can create a store, publish products, accept payments, manage orders,
offer delivery or pickup, communicate progress, and understand the business.
Customers get a proper shopping, payment, delivery, and tracking journey.

The product is designed around Ghanaian small businesses, phone-first access,
GHS pricing, Mobile Money-friendly payment flows, and practical operation on
lower-cost devices and connections.

## 2. The product in one sentence

**8Kommerce helps independent sellers turn social-media interest into managed,
trackable commerce without first building a traditional website.**

## 3. People and roles

### Visitor or prospective seller

- reads the public landing page;
- joins the early-access waitlist with a phone number;
- may arrive through a seller referral.

### Seller

- signs in with a phone number and four-digit PIN;
- completes onboarding and business settings;
- manages products, categories, stock, customers, orders, payments and goals;
- designs a public storefront;
- configures delivery and pickup;
- creates invoices and promotion submissions;
- reviews business performance;
- subscribes to a package.

### Customer

- browses a seller storefront or public product page;
- adds items to a cart;
- chooses delivery or pickup;
- pays through Paystack;
- receives important order SMS updates;
- tracks single or bulk orders using phone verification;
- confirms delivery or reports a problem;
- rates the seller after a verified completed order.

### Promoter

- registers separately using a phone number and promoter PIN;
- copies tracked marketplace or product links;
- sees visits, popular products and verified conversions;
- can reset a promoter PIN through SMS verification;
- does not yet receive automated rewards or payouts.

### Administrator

- uses email OTP access restricted by the configured allow-list;
- reviews operational health, failed jobs and reconciliation;
- manages editorial promotions and seller recognition;
- sees waitlist and promoter activity;
- reviews customer delivery disputes and refund decisions.

## 4. Main product journeys

### Seller journey

```text
Waitlist/invitation
      ↓
Phone verification and account activation
      ↓
Four-digit PIN + seller profile
      ↓
Store, payout, delivery and pickup configuration
      ↓
Products, categories and stock
      ↓
Storefront design and publication
      ↓
Orders → fulfilment → delivery → customer confirmation
      ↓
Payments, customers, insights and growth
```

Public sign-up is currently presented as an early-access waitlist. Existing
sellers use `/seller-access`.

### Customer checkout journey

```text
Storefront/product
      ↓
Cart updated without a full-page refresh
      ↓
Delivery/pickup option + customer details
      ↓
Inventory reservation
      ↓
Paystack transaction initialization
      ↓
Provider verification/webhook
      ↓
Paid order + stock commitment
      ↓
Seller fulfilment updates + customer SMS
      ↓
Customer accepts delivery or reports a problem
```

### Customer tracking journey

```text
Phone number → one-time SMS code → verified tracking session
                                      ↓
                           all orders for that phone
                                      ↓
                         order timeline and details
```

### Promoter journey

```text
Registration/login → tracked link → customer visit → verified purchase
       ↓                                                   ↓
private performance workspace                    conversion attribution
```

Click attribution and promoter authentication use different session keys. A
customer clicking a tracked link cannot open the promoter's private workspace.

## 5. Technology stack

| Area | Technology | Purpose |
|---|---|---|
| Backend | PHP 8.2, Laravel 12 | Routes, validation, domain logic, queues and views |
| Frontend | Blade, Tailwind CSS, Alpine.js | Server-rendered responsive interface and lightweight interaction |
| Asset build | Vite 7, Node 20 | Compiles CSS and JavaScript for production |
| Database | PostgreSQL 16 | Durable relational business data |
| Cache/queue/session | Redis 7 | Fast sessions, cache, rate-limit state and background jobs |
| Payments | Paystack | Checkout, verification, subscriptions and seller payout configuration |
| SMS | Hubtel or Twilio | OTP and transactional customer messages |
| Charts | Chart.js | Seller analytics and reporting |
| PDF | DOMPDF | Printable/exportable documents |
| Error reporting | Sentry SDK | Production exception reporting when configured |
| Web edge | Caddy | HTTPS certificates and reverse proxy |
| Runtime | Docker Compose | App, workers, scheduler, PostgreSQL, Redis and Caddy |
| CI/CD | GitHub Actions + GHCR | Tests, container build, signed release artefacts |
| Production host | Contabo Ubuntu VPS | Runs the Docker deployment |

## 6. Architecture at a glance

```text
Browser / installed PWA
         │ HTTPS
         ▼
       Caddy
         │
         ▼
Laravel + Apache application container
   │          │             │
   │          │             ├── Paystack API/webhooks
   │          │             └── Hubtel/Twilio
   │          │
   │          └── Redis ── queue workers + scheduler
   │
   └── PostgreSQL

Persistent Docker volumes:
database · redis · uploaded files · Caddy certificates
```

The application is a modular Laravel monolith. This is deliberate: the product
has many related business workflows, but its current scale does not justify the
operational cost of microservices. Controllers stay relatively thin while
services own reusable business operations.

## 7. How a Laravel request moves through the system

1. Caddy receives HTTPS traffic and forwards it to the app container.
2. Laravel matches the URL in `routes/web.php` or `routes/auth.php`.
3. Middleware checks concerns such as authentication, access, plan and rate
   limits.
4. A controller validates input and coordinates the use case.
5. A service performs reusable domain operations or talks to a provider.
6. Eloquent models read or write PostgreSQL data.
7. Slow or retryable work is dispatched to a queue.
8. The controller returns a Blade view, JSON response or redirect.

Example:

```text
POST storefront checkout
 → throttle middleware
 → PublicListingController
 → InventoryService + PaymentService/PaystackService
 → Order, Payment and InventoryReservation models
 → Paystack redirect
```

## 8. Codebase map

| Path | Responsibility |
|---|---|
| `routes/` | Public, seller, admin, webhook and authentication endpoints |
| `app/Http/Controllers/` | HTTP use cases and response coordination |
| `app/Http/Middleware/` | Access, subscription, onboarding and security gates |
| `app/Models/` | Database entities and relationships |
| `app/Services/` | Payments, inventory, SMS, referrals, tracking and domain logic |
| `app/Jobs/` | Work executed by queue workers |
| `resources/views/` | Blade pages, layouts and reusable components |
| `resources/css/app.css` | Central design system and application styling |
| `resources/js/` | Alpine setup and browser interactions |
| `database/migrations/` | Versioned database schema changes |
| `tests/Feature/` | Behaviour and integration tests |
| `config/` | Plans, providers, authentication and application settings |
| `deploy/` | Production Compose, Caddy, backup and deployment scripts |
| `.github/workflows/` | CI validation and container publication |

## 9. Domain modules

### Identity and access

Important code:

- `app/Http/Controllers/Auth/`
- `app/Services/SmsOtpService.php`
- `app/Support/Phone.php`
- `config/auth_phone.php`
- `resources/views/auth/`

Seller login uses phone + PIN. OTP is used for verification and recovery rather
than for every normal login, reducing cost while keeping account recovery safe.
PINs are hashed and are never stored as readable values.

### Products and categories

Important code:

- `Product`, `ProductCategory`
- `ProductController`, `ProductCategoryController`
- `resources/views/dashboard/products/`

Products belong to a seller. Categories can be selected, created, renamed and
deleted. Public product cards use consistent media aspect ratios so uploaded
images do not create uneven catalogues.

### Storefront and cart

Important code:

- `SellerPublicPreviewController`
- `PublicListingController`
- `PublicCartSession`
- `resources/views/public/partials/listing-content.blade.php`

Storefront settings are stored separately from catalogue data. Changing a
template does not remove products. Cart state is persisted for the browser and
quantity changes happen in the background without requiring a page refresh.

### Inventory reservations

Important code:

- `InventoryReservation`
- `InventoryService`
- `InventoryReservationTest`

A cart does not permanently reduce stock. Checkout creates a temporary
reservation inside a database transaction. This prevents two simultaneous
buyers from both purchasing the final unit.

Possible outcomes:

- successful payment: reservation is committed;
- failed checkout: reservation is released;
- abandoned checkout: scheduled expiry returns stock;
- late payment with unavailable stock: order enters refund review.

### Orders and fulfilment

Important code:

- `Order`, `OrderItem`, `OrderFeedback`
- `NotificationController`
- `OrderTimelineService`, `OrderFeedbackService`
- `OrderFulfillmentLifecycleTest`

The lifecycle includes acceptance, processing, ready, shipped/delivering,
delivery attempts, completion, return, cancellation and refund review. Sellers
can bulk-accept eligible paid orders. Status changes create customer-facing
timeline information and selected SMS notifications.

### Payments and reconciliation

Important code:

- `Payment`, `Invoice`, `WebhookEvent`
- `PaymentService`, `PaystackService`, `PaymentReconciliationService`
- `PaystackWebhookController`
- `WebhookIdempotencyTest`, `PaymentMarkSuccessRaceGuardTest`

The browser redirect is not trusted as proof of payment. The backend verifies
the provider transaction, amount and reference. Signed webhooks provide an
additional asynchronous confirmation path. Webhook events and payment success
side effects are idempotent, so retries do not double-decrement stock, redeem a
coupon twice, or duplicate a conversion.

### Subscriptions

Important code:

- `Subscription`, `SubscriptionInvoice`
- `BillingController`, `SubscriptionService`
- `config/plans.php`
- `docs/subscription-readiness.md`

The system supports Paystack plan codes and manual monthly renewals. Production
can use manual Mobile Money-friendly renewal by enabling
`BILLING_MANUAL_RENEWAL_ENABLED` and disabling recurring billing. Access periods,
grace periods, invoices and renewal reminders are handled by the service and
scheduler.

### Customer tracking and feedback

Important code:

- `CustomerTrackingSession`
- `CustomerOrderHubController`
- `CustomerTrackingService`, `CustomerTrackingOtpService`
- `OrderFeedbackToken`, `SellerRatingService`

Customers verify control of their phone before viewing related orders. Feedback
tokens link ratings to real completed orders, preventing arbitrary ratings from
building a seller's public score.

### Referrals and promoters

Important code:

- `ReferralLink`, `SellerReferral`
- `PromoterProfile`, `PromoterVisit`, `PromoterConversion`
- `SellerReferralService`, `PromoterAttributionService`
- `SellerReferralController`, `PromoterController`

Seller referrals attribute new seller acquisition. Promoters attribute customer
visits and completed purchases. Rewards are intentionally marked as coming soon;
the measurement foundation exists without promising an unfinished payout model.

### Editorial growth

Important code:

- `PromotionSubmission`, `EditorialArticle`
- `FeaturedPlacement`, `SellerRecognition`
- `PromotionController`, `AdminPromotionController`

Sellers nominate products for editorial attention. Administrators decide what
appears on 8Kommerce-owned channels, featured placements and articles. This is
not a social-account integration.

### Waitlist and administration

Important code:

- `WaitlistEntry`, `AdminAuditLog`
- `WaitlistController`, `AdminAudienceController`
- `resources/views/waitlist/`, `resources/views/admin/`

The public waitlist deduplicates normalized phone numbers. Admin tools expose
audience and operational information without turning public phone lookup into
an account-enumeration endpoint.

## 10. Data model by business area

```text
Identity
User ── SellerProfile

Catalogue
User ── Product ── ProductCategory

Commerce
Order ── OrderItem ── Product
  │
  ├── Payment
  ├── InventoryReservation
  ├── OrderFeedback
  └── SellerNotification

Growth
PromoterProfile ── PromoterVisit
                └── PromoterConversion
ReferralLink ── SellerReferral

Billing
Subscription ── SubscriptionInvoice

Reliability and compliance
WebhookEvent · AdminAuditLog · DataDeletionRequest · TwilioMessageLog
```

Laravel migrations are forward-only production history. Never edit an already
deployed migration to change production; create a new migration.

## 11. Queues and scheduled work

The web request should return quickly. Work that can be delayed or retried is
sent to Redis queues.

Production workers:

| Worker | Queues | Purpose |
|---|---|---|
| OTP worker | `otp` | Time-sensitive verification messages |
| General workers | `payments,notifications,default` | Payment, customer communication and background work |
| Scheduler | Laravel schedule | Expiry, reminders, cleanup and periodic commands |

The OTP queue is isolated so a burst of ordinary notifications cannot delay a
login or tracking code. The current VPS runs two general workers and one OTP
worker. Increase replicas only after load testing and database observation.

## 12. Security model

The security strategy uses multiple layers:

- phone and PIN authentication for sellers;
- hashed PINs and passwords;
- OTP expiry, resend cooldown, retry limits and lockout;
- admin email allow-list plus OTP;
- CSRF protection on browser forms;
- Laravel validation and model ownership checks;
- throttling on login, checkout, public payment and sensitive actions;
- signed Paystack and Twilio webhook validation;
- server-side payment amount verification;
- idempotent webhook and payment processing;
- database transactions and row locking for stock;
- current PIN confirmation for payout changes;
- private PostgreSQL and Redis Docker networks;
- HTTPS from Caddy;
- SSH keys, disabled root login, UFW and Fail2ban on the VPS;
- audit records for sensitive administrative events;
- formula protection in CSV exports;
- legal data-deletion workflow.

Security is risk reduction, not a claim that vulnerabilities are impossible.
Keep dependencies patched, review logs, rotate secrets, test backups and perform
periodic security reviews. See `docs/SECURITY_CHECKLIST.md`.

## 13. Frontend and design system

8Kommerce uses server-rendered Blade rather than a separate SPA. This keeps the
system simpler and makes the public store resilient on lower-powered devices.
Alpine.js and small JavaScript modules add interaction where a full page reload
would hurt the experience.

Shared UI lives in:

- `resources/views/layouts/`
- `resources/views/components/`
- `resources/css/app.css`

Design principles:

- restrained radii and shadows;
- flat surfaces and clear borders;
- consistent Ocean Teal and Crimson branding;
- transparent wordmark or monogram according to available space;
- compact, scannable business data;
- consistent GHS formatting;
- responsive seller and storefront interfaces;
- accessible labels, focus states, error messages and status text.

Storefront templates are seller-facing presentation options. They share the
same underlying products, cart and checkout contracts.

## 14. Configuration and secrets

Local configuration starts from `.env.example`. Production configuration is
stored only at `/opt/8kommerce/.env` with mode `600`.

Important groups include:

- application URL, key and environment;
- PostgreSQL credentials;
- Redis password and queue configuration;
- Paystack keys, callbacks and plan codes;
- Hubtel or Twilio credentials;
- admin allowed emails;
- subscription feature flags;
- domain and Caddy configuration;
- worker replica counts;
- monitoring and error-reporting values.

Never commit `.env`, provider secrets, database dumps, personal tokens or SSH
private keys.

## 15. Local development

Requirements:

- PHP 8.2 and Composer 2;
- Node 20 and npm;
- PostgreSQL;
- Redis when testing queues/sessions as production uses them.

Initial setup:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

Start the complete development set:

```bash
composer run dev
```

This starts Laravel, Vite, queue processing, the scheduler and log output. For a
quick single-process check, use `php artisan serve`, but queued work will not be
processed unless a worker is also running.

Useful commands:

```bash
php artisan route:list
php artisan migrate:status
php artisan queue:failed
php artisan schedule:list
php artisan tinker
php artisan test
npm run build
```

## 16. Automated testing

The feature suite is the executable description of expected behaviour. At the
time of this handbook's creation, the suite contains 162 passing tests and 950
assertions.

High-value suites include:

- `InventoryReservationTest`
- `PaymentMarkSuccessRaceGuardTest`
- `WebhookIdempotencyTest`
- `SecurityHardeningTest`
- `OrderFulfillmentLifecycleTest`
- `PublicCheckoutSmokeTest`
- `SubscriptionBillingTest`
- `CustomerOrderHubTest`
- `StorefrontDesignTest`
- `PromoterProgramTest`

Before opening a pull request:

```bash
php artisan test
npm run build
git diff --check
```

## 17. Git and CI/CD

```text
Feature branch
   ↓ push + pull request
GitHub CI
   ├── PHP/Composer validation
   ├── frontend production build
   ├── PostgreSQL migrations
   ├── Laravel tests
   ├── dependency audits
   └── production Docker build
   ↓ required checks pass
Squash merge to main
   ↓
Release container workflow
   ↓
GHCR image tagged main + sha-<commit>
   ↓
Deploy exact immutable sha256 digest to Contabo
```

CI validates code but does not automatically change the VPS. This separation
prevents an accidental merge from becoming an uncontrolled production deploy.

See `CI_CD.md` and `.github/workflows/`.

## 18. Production deployment

Production services are defined in `deploy/compose.production.yml`:

- `caddy`
- `app`
- `worker` (two replicas by default)
- `worker-otp`
- `scheduler`
- `db`
- `redis`

The release procedure is:

1. Merge a pull request after required CI checks pass.
2. Wait for the `Release container` workflow.
3. Pull `ghcr.io/blesspro/8kommerce:main` on the VPS.
4. Resolve it to its immutable repository digest.
5. Save that digest as `APP_IMAGE` in `/opt/8kommerce/.env`.
6. Run `/opt/8kommerce/scripts/deploy.sh`.
7. Verify containers and public health endpoints.

The deployment script:

- validates Compose configuration;
- creates database and uploaded-file backups;
- pulls the exact image;
- recreates changed containers;
- waits for service health;
- runs Laravel optimization;
- restarts queue workers gracefully;
- checks the application and status URLs.

The application entrypoint runs migrations before accepting web traffic. It
retries while PostgreSQL starts and refuses to start if migrations continue to
fail.

Production URLs:

- application: `https://app.8kommerce.com`
- status: `https://status.8kommerce.com`
- Paystack webhook: `https://app.8kommerce.com/webhooks/paystack`

See `deploy/README.md` for the exact runbook.

## 19. Rollback and database caution

Containers can be rolled back to a previous known-good digest:

```bash
./scripts/rollback.sh ghcr.io/blesspro/8kommerce@sha256:PREVIOUS_DIGEST
```

This does **not** reverse database migrations. Therefore, production migrations
must remain backward compatible with the immediately previous application
version whenever practical.

## 20. Backups, health and operations

Current operational protections:

- pre-deployment database and upload backups;
- scheduled local backups;
- Docker restart policies;
- application, database and Redis health checks;
- public `/live`, `/up` and `/status` endpoints;
- failed-job inspection and alerts;
- immutable release images;
- queue isolation and retry policies.

Local backups protect against application mistakes but not total VPS loss. Add
encrypted offsite backups before storing material volumes of customer and
payment data. Regularly practise restoration using
`docs/DB_BACKUP_RESTORE_DRILL.md`.

Useful VPS checks:

```bash
cd /opt/8kommerce
docker compose --env-file .env -f compose.production.yml ps
docker compose --env-file .env -f compose.production.yml logs --since 30m app
docker compose --env-file .env -f compose.production.yml logs --since 30m worker worker-otp
./scripts/backup.sh manual
```

Never paste `.env` or unfiltered logs containing secrets into public channels.

## 21. How to approach a code change

Use this reasoning sequence:

1. Define the user and the problem.
2. Write the desired behaviour and failure cases.
3. Find the route and controller that own the current flow.
4. Find the related service, models and tests.
5. Decide whether the schema must change.
6. Make the smallest central change that preserves existing contracts.
7. Add or update tests for success, authorization, failure and retry behaviour.
8. Build frontend assets and run the full suite.
9. Open a pull request and let CI validate the production container.
10. Deploy the immutable digest and verify the live user journey.

Avoid putting complex provider or domain logic directly into Blade views. Avoid
copying the same business rule into multiple controllers. Prefer a service plus
tests when a rule affects money, inventory, security, communication or status.

## 22. Recommended learning path

### Stage 1: product understanding

1. Read sections 1–4 of this handbook.
2. Use the app as a seller, customer, promoter and admin.
3. Draw each journey in your own words.

### Stage 2: Laravel fundamentals

1. Follow a route into its controller.
2. Read its validation rules.
3. Open the models and relationships it uses.
4. Find the matching Blade page.
5. Run the matching feature test.

Start with products, then storefront/cart, then orders.

### Stage 3: critical business logic

Study in this order:

1. `InventoryService`
2. `PaymentService` and `PaystackService`
3. `PaystackWebhookController`
4. `OrderTimelineService`
5. `SubscriptionService`
6. OTP and SMS services

### Stage 4: infrastructure

1. Read `Dockerfile` and `docker/entrypoint.sh`.
2. Read `deploy/compose.production.yml`.
3. Read `.github/workflows/ci.yml` and `release.yml`.
4. Follow one release from pull request to GHCR digest to Contabo.
5. Practise a backup restore and a rollback in a safe environment.

## 23. Glossary

| Term | Meaning |
|---|---|
| Controller | Receives an HTTP request and coordinates a response |
| Model | PHP representation of database data and relationships |
| Migration | Versioned change to the database structure |
| Middleware | Check that runs before/after a request, such as authentication |
| Service | Reusable business or provider logic |
| Queue | Background work processed outside the browser request |
| Worker | Process that consumes queued jobs |
| Scheduler | Process that triggers periodic Laravel tasks |
| Webhook | Provider-to-server notification, such as Paystack confirming payment |
| Idempotent | Safe to retry without duplicating the result |
| Transaction | Database operation group that succeeds or fails together |
| Reservation | Temporary hold on stock during checkout |
| Digest | Immutable identifier for the exact Docker image deployed |
| CI | Automated validation of code changes |
| CD | Controlled production release and deployment process |
| PWA | Installable web application using browser capabilities |

## 24. Related documentation

- `README.md` — project entry point
- `CI_CD.md` — CI and container release details
- `deploy/README.md` — VPS deployment and rollback
- `docs/SECURITY_CHECKLIST.md` — security review
- `docs/LOAD_TESTING.md` — controlled performance testing
- `docs/DB_BACKUP_RESTORE_DRILL.md` — recovery practice
- `docs/subscription-readiness.md` — subscription configuration
- `docs/AUTH_PHONE_PIN_ROADMAP.md` — phone/PIN design
- `docs/UPLOAD_POLICY.md` — uploaded-file controls
- `docs/VERSIONING.md` — semantic releases

## 25. The mental model to remember

8Kommerce is not merely a collection of pages. It is a controlled movement of
business state:

```text
interest → product → order → reserved stock → verified payment
→ fulfilment → delivery → customer confirmation → reputation and insight
```

Every important technical mechanism exists to keep that movement accurate:

- validation keeps input usable;
- authentication and ownership protect access;
- transactions and reservations protect inventory;
- provider verification protects money;
- idempotency protects against retries;
- queues keep slow communication away from customer requests;
- tests protect behaviour during change;
- immutable images make releases reproducible;
- backups and health checks support recovery.

That is the logical and technical core of the project.
