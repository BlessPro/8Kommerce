# Subscription readiness audit

## Verdict

8Kommerce can represent trials and time-limited plan access, but it does not yet have a production subscription lifecycle. Keep `BILLING_RECURRING_ENABLED=false` until the items below are implemented and tested together.

## What exists

- Starter, Growth, and Enterprise pricing and limits.
- Trial start/end and paid plan start/end fields on users.
- Access middleware based on trial or plan dates.
- Paystack credentials and a signed, idempotent transaction webhook.
- Local-only manual activation protected by configuration and environment checks.
- Environment placeholders for Paystack plan codes.

## Gaps blocking production recurring billing

1. Phone-only accounts may have no email, while Paystack subscriptions require a provider customer identified by email or customer code. Billing must capture and verify a billing email without reintroducing email login.
2. There is no subscription record for provider customer code, subscription code, email token, status, plan code, next payment date, cancellation state, or the latest invoice.
3. No billing checkout initializes a transaction with a Paystack `plan_code`.
4. The existing webhook intentionally ignores every event except `charge.success`. Subscription billing also needs `subscription.create`, `invoice.create`, `invoice.update`, `invoice.payment_failed`, `subscription.not_renew`, and `subscription.disable` handling.
5. Plan access is currently date-based and is not synchronized from verified provider subscription events.
6. There is no cancel, resume, update-payment-method, failed-renewal, or grace-period workflow.
7. Paystack automatic subscriptions use reusable card authorizations. Direct debit is available in Nigeria; Mobile Money should not be advertised as an automatic recurring method.

## Configuration changes made

- Removed the duplicate `enterprise` configuration key that overwrote the actual Enterprise plan.
- Aligned Growth feature copy with its configured product and admin limits.
- Added explicit provider, recurring-enabled, and provider plan-code settings.
- Disabled production plan activation controls until the lifecycle is implemented.

## Recommended implementation sequence

1. Add billing-email capture and verification.
2. Add a dedicated subscriptions table and model.
3. Initialize the first Paystack transaction with the selected plan code.
4. Persist subscription creation and invoice events idempotently.
5. Derive access from verified provider state with a documented grace policy.
6. Add hosted payment-method management and cancellation flows.
7. Add webhook, renewal, failure, cancellation, and race-condition tests before enabling recurring billing.

References: [Paystack subscriptions](https://paystack.com/docs/payments/subscriptions/) and [Subscription API](https://paystack.com/docs/api/subscription/).
