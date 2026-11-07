# Controlled load testing

The included k6 scenario measures public read traffic without creating orders,
charging customers, sending OTPs, or changing production data.

Start with smoke, then steady. Run spike only during a maintenance window and
watch the VPS CPU, memory, database connections, queue depth, and application
logs. Stop if error rate rises above 1% or p95 response time exceeds 1.2 seconds.

```bash
docker run --rm -i grafana/k6 run - \
  -e BASE_URL=https://app.8kommerce.com \
  -e STORE_SLUG=your-public-store-slug \
  -e PRODUCT_SLUG=your-product-slug \
  -e PROFILE=smoke < scripts/load/k6-storefront.js
```

Available profiles:

- `smoke`: 2 virtual users for 30 seconds.
- `steady`: ramps to 25 virtual users for a five-minute baseline.
- `spike`: ramps to 100 virtual users. Use only after steady passes.

Do not load-test Paystack, Hubtel, Twilio, checkout, OTP, or webhooks against
production. Those flows need an isolated staging environment with test provider
credentials and disposable data.

The production deployment starts two general queue workers and one dedicated
OTP worker by default. Change `QUEUE_WORKER_REPLICAS` only after measuring queue
age and CPU. Keep at least one CPU core available for the web app and database.
