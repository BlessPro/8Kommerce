# 8Kommerce Case Study

## Making ecommerce accessible to small businesses

**Doe Bless — Product Designer & Full-Stack Developer**

8Kommerce is a mobile-first commerce platform that helps small businesses move
from scattered social-media selling to one organized system for products,
orders, payments, delivery, customer tracking and business insights.

**My role:** Founder, product strategist, researcher, product designer and
full-stack developer  
**Project type:** End-to-end product design and engineering  
**Current stage:** Production-ready private beta  
**Primary market:** Small and growing businesses in Ghana

> **Suggested opening visual:** A strong composition showing the seller
> dashboard, a storefront on a phone and the customer order-tracking experience.

---

## The motivation

I started 8Kommerce because I wanted ecommerce to be accessible to small
businesses—not only businesses that could afford a custom website, a technical
team or several disconnected software subscriptions.

I also wanted these businesses to grow with real data. A seller should be able
to understand which products attract customers, what is selling, which orders
need attention, how stock is moving and where the business is improving.

The goal became larger than creating online storefronts. I wanted to connect
the full journey between product discovery, payment, fulfilment and customer
communication.

## The problem

Many small businesses already sell online, but their process is often manual.
A seller posts products on WhatsApp or social media, sends product images to
customers one account at a time, answers questions in separate chats and keeps
the conversation going until the customer decides to buy.

This creates several risks:

- product information is repeatedly copied between conversations;
- orders and customer decisions can disappear inside busy chats;
- stock is tracked manually or remembered by the seller;
- payment screenshots must be checked and matched to orders;
- delivery details are scattered across messages;
- customers repeatedly ask for progress updates;
- useful sales and customer data is lost;
- the seller cannot clearly measure business performance.

The core problem was not that these businesses had no digital presence. They
were already digital. The problem was that their commerce activity was
fragmented and difficult to manage.

> **Suggested visual:** A “before 8Kommerce” journey showing product images,
> chat conversations, payment screenshots and delivery notes spread across
> different places.

## Research

I conducted research and usability conversations with approximately **27
participants**. The discussions focused on how small businesses shared
products, communicated with customers, recorded orders and completed sales.

Several patterns appeared consistently:

1. **Sharing products created repetitive work.** Sellers regularly sent the
   same images and information to individual customer accounts.
2. **Chats acted as an informal order-management system.** Important details
   were mixed with ordinary conversation.
3. **The buying journey depended heavily on the seller being available.** A
   customer often needed the seller to answer questions, confirm stock, confirm
   payment and explain delivery.
4. **Business data disappeared after the conversation.** Even successful sales
   did not automatically become useful inventory, customer or performance data.
5. **The solution needed to feel familiar and mobile-first.** A complicated
   enterprise dashboard would create a new barrier instead of removing one.

### Research insight

Small businesses did not simply need “a website.” They needed a practical
commerce system that worked alongside the social channels where customers were
already discovering them.

## Reframing the challenge

I reframed the initial idea from:

> How might I help small businesses create an online store?

to:

> How might I help a small business turn social-media interest into an
> organized, measurable order without losing the familiarity of phone-first
> selling?

This reframing shaped both the design and technical architecture.

## Product principles

I used five principles to guide decisions:

### 1. Mobile before desktop

The main workflows had to remain understandable and usable on a phone.

### 2. Familiar before clever

Phone numbers, four-digit PINs, clear statuses and ordinary business language
were prioritized over unfamiliar technical concepts.

### 3. One connected business record

Products, customers, stock, orders, payments and delivery should contribute to
the same system instead of becoming separate data silos.

### 4. Reliability is part of the experience

Preventing overselling, verifying payment and protecting customer information
are user-experience decisions—not only backend concerns.

### 5. Data should help the seller act

Insights should lead to practical decisions rather than present decorative
charts without context.

## Designing the solution

### Phone-first seller access

Seller access uses a phone number and four-digit PIN instead of requiring email
and password as the primary login method. OTP is reserved for verification and
recovery, balancing accessibility, security and recurring SMS cost.

**Why this mattered:** the login model matches how many target users already
identify and communicate with customers.

> **Suggested visual:** Seller access screen and PIN recovery journey.

### A focused seller workspace

The seller workspace brings together:

- products and categories;
- stock levels and low-stock states;
- customers and orders;
- payment records and invoices;
- delivery and pickup settings;
- storefront design;
- goals and business insights;
- referrals and editorial promotion.

Navigation was reorganized around seller tasks instead of exposing every
technical feature as a separate menu item. Quick actions help sellers reach
frequent tasks without searching through a large sidebar.

> **Suggested visual:** Dashboard overview and the minimized navigation state.

### Storefronts that match different businesses

Sellers can select presentation styles suited to different product categories,
including marketplace, perfume and fashion experiences. They can configure
their logo, cover image, colour palette, typography, product presentation,
announcement and social links.

The templates change presentation without changing the underlying products,
stock or checkout system.

**Design decision:** the editor remains on the left while a fixed, internally
scrollable phone preview stays visible on the right. This creates a direct
relationship between a setting and its result.

> **Suggested visual:** Storefront editor beside the phone preview, followed by
> two or three storefront examples.

### Consistent product presentation

Uploaded images can have different dimensions. Product cards therefore use a
consistent image frame and controlled object fitting so catalogues remain
aligned and easy to scan.

Product information was made denser by reducing unnecessary vertical gaps and
separating actions such as wishlist and WhatsApp contact from stock status.

> **Suggested visual:** A two-column mobile product grid demonstrating
> consistent image sizes.

### Checkout, delivery and pickup

Customers see delivery or pickup options before payment. Sellers configure
their own areas, stations, fees and supporting details from store settings.

The checkout calculates the selected fulfilment fee and keeps the customer
informed before money is collected.

**Why this mattered:** delivery is part of the purchase decision. Hiding it
until after payment creates mistrust and operational disputes.

> **Suggested visual:** Cart, delivery selection and final payment breakdown.

### Inventory reservations

When several customers attempt to purchase limited stock, the system creates a
temporary reservation during checkout. A successful payment commits the stock;
a failed or expired checkout releases it.

This protects both the seller and customer from the experience of paying for an
item that another person purchased moments earlier.

### Order fulfilment and customer communication

Sellers can move orders through acceptance, processing, ready, delivering and
completed stages. Eligible orders can be accepted in bulk.

Customers receive important SMS messages when an order is accepted and when it
is moving through delivery. They can verify their phone to see individual and
bulk order histories, timelines and details.

After delivery, the customer can accept the order or report a problem before
the workflow is treated as fully resolved.

> **Suggested visual:** Seller order worklist beside the customer tracking
> timeline.

### Payments and subscriptions

Paystack supports customer checkout, seller payout configuration and platform
subscriptions. Payments are confirmed on the server rather than trusting a
browser success page or payment screenshot.

Subscription options support both configured recurring plans and manual monthly
renewals suitable for Mobile Money-oriented users.

### Promoters and seller referrals

Sellers can invite other sellers using referral links. Promoters have a
separate phone-and-PIN workspace where they can copy tracked marketplace or
product links and review visits and verified conversions.

Promoter rewards are intentionally marked as coming soon. The product records
real activity without promising a payout model that has not yet been finalized.

> **Suggested visual:** Promoter link tools and performance summary.

## Evolving the interface

The early interface used several common SaaS patterns: excessive cards,
generous spacing, rounded containers and weak hierarchy. I audited the frontend
and introduced a more deliberate design system.

The revised interface uses:

- restrained corner radii;
- clearer typography and spacing scales;
- one primary Ocean Teal colour with a Crimson accent;
- mostly flat surfaces and subtle borders;
- consistent form, button, table and status patterns;
- denser business information;
- transparent logo and monogram variants;
- responsive layouts designed around actual tasks.

The redesign was implemented centrally through shared layouts, components and
styles instead of page-by-page visual patches.

> **Suggested visual:** Before-and-after comparison of the dashboard, settings
> or storefront editor.

## Technical architecture

I built 8Kommerce as a modular Laravel monolith. This kept closely related
commerce workflows in one system while avoiding the operational cost of
microservices at an early stage.

```text
Customer or seller browser
          │
          ▼
     Caddy / HTTPS
          │
          ▼
Laravel 12 application
   │        │         │
   │        │         ├── Paystack
   │        │         └── Hubtel or Twilio
   │        │
   │        └── Redis queues, cache and sessions
   │
   └── PostgreSQL
```

The system uses:

- Laravel 12 and PHP 8.2;
- Blade, Tailwind CSS and Alpine.js;
- PostgreSQL for business data;
- Redis for queues, cache and sessions;
- Paystack for payments and subscriptions;
- Hubtel or Twilio for OTP and transactional SMS;
- Docker Compose for production services;
- Caddy for HTTPS and routing;
- GitHub Actions and GitHub Container Registry for releases;
- a secured Contabo VPS for production hosting.

> **Suggested visual:** Turn the architecture above into a simple branded
> diagram rather than showing source code.

## Reliability and security decisions

The product handles money, customer information and stock, so I treated
reliability as a core part of the design.

Key protections include:

- hashed PINs and passwords;
- OTP expiry, cooldown, retry limits and lockout;
- rate limits on sensitive public actions;
- seller ownership and authorization checks;
- signed Paystack and Twilio webhook validation;
- server-side amount and reference verification;
- idempotent webhook and payment processing;
- database transactions and stock reservations;
- PIN confirmation for payout changes;
- private database and Redis networks;
- HTTPS, SSH keys, firewall rules and Fail2ban;
- pre-deployment backups and health checks;
- admin audit records and data-deletion requests.

## Important trade-offs

### PIN login versus OTP login

OTP-only login would be simple for users but would create a recurring SMS cost
on every sign-in. A PIN supports affordable daily access while OTP protects
verification and recovery.

### Modular monolith versus microservices

Microservices could separate domains, but they would introduce deployment,
observability and consistency costs too early. A modular monolith is easier to
operate while the business validates demand.

### Server-rendered interface versus a full SPA

A separate JavaScript application could provide complex interaction, but Blade
with Alpine.js offers faster delivery, good performance on ordinary phones and
fewer moving parts. Background cart updates are added only where they improve
the experience.

### Feature breadth versus controlled launch

The platform now covers a large part of the commerce journey. I chose a private
beta launch so real seller behaviour can determine which workflows deserve
deeper investment before broad acquisition begins.

## Validation and current outcome

The product has not yet collected enough public-market data to claim business
growth outcomes. I therefore separate **what has been built and validated** from
**what still needs market measurement**.

Validated outcomes:

- research and usability conversations with approximately 27 participants;
- a connected seller, customer, promoter and administrator experience;
- production deployment on a secured VPS;
- reproducible Docker releases through GitHub Actions;
- 162 automated tests with 950 assertions at the latest documented release;
- isolated queue workers for OTP and ordinary background jobs;
- payment, inventory, delivery and retry protections tested in code;
- live health endpoints, backups and rollback support.

Market outcomes to measure during the private beta:

- seller activation and onboarding completion;
- time required to publish the first product and store;
- checkout completion rate;
- successful versus abandoned payments;
- order fulfilment time;
- customer tracking usage;
- support questions per order;
- repeat customers and seller retention;
- stock accuracy and prevented overselling events.

## What I learned

### Ecommerce is an operational system, not only a storefront

The attractive shop is the visible layer. Trust is created by what happens
after the customer clicks: accurate stock, clear fees, verified payment,
delivery updates and a fair way to resolve problems.

### User experience continues into backend behaviour

An interface cannot feel reliable if payment can be duplicated, stock can be
oversold or an OTP is delayed behind ordinary jobs. Architecture decisions
directly influence the customer experience.

### Small businesses need clarity more than feature theatre

Useful statuses, familiar language and focused actions provide more value than
decorative dashboards or complex configuration.

### Building as a founder requires connecting disciplines

Research informed product priorities. Product decisions shaped the interface.
The interface exposed technical risks. Engineering and deployment then made the
designed experience dependable in production.

## Next steps

1. Onboard a controlled group of trusted sellers.
2. Observe real product publishing, checkout and fulfilment behaviour.
3. Measure activation, conversion, support demand and retention.
4. Improve the highest-friction workflows based on evidence.
5. Add encrypted offsite backups and independent production monitoring.
6. Validate promoter incentives before introducing rewards.
7. Expand marketing only after operational reliability is demonstrated.

## Closing reflection

8Kommerce began with a simple motivation: make ecommerce accessible to small
businesses and help them grow with real data.

The project evolved into a connected commerce platform because the research
showed that publishing products was only one part of the problem. Small
businesses needed a dependable bridge between discovery, conversation,
payment, fulfilment and insight.

As founder, product designer and full-stack developer, I carried that journey
from an observed problem to research, product strategy, visual design,
engineering, security, automated testing and production deployment.

The next chapter is learning from real businesses at market scale.

---

## Case study credits

**Doe Bless**  
Product Designer & Full-Stack Developer  
Founder, 8Kommerce

