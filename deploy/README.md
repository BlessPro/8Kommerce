# 8Kommerce production deployment

This bundle runs the verified 8Kommerce image on one Ubuntu VPS with Caddy,
PostgreSQL, Redis, dedicated queue workers, the Laravel scheduler, persistent
uploads, health checks, and daily local backups.

## Security and persistence

- Only Caddy publishes host ports (80 and 443).
- PostgreSQL and Redis are isolated on an internal Docker network.
- Workers have outbound access for Paystack and SMS but no inbound ports.
- PostgreSQL, Redis, Caddy state, and Laravel storage use named volumes.
- `.env` must remain mode `600` and must never be committed.
- Production releases use an immutable GHCR digest, not the mutable `main` tag.

## Bootstrap from a verified image

The deployment bundle is included in the application image so the VPS does not
need repository access.

```bash
IMAGE='ghcr.io/blesspro/8kommerce@sha256:VERIFIED_DIGEST'
docker pull "$IMAGE"
container_id="$(docker create "$IMAGE")"
sudo install -d -m 0750 -o deploy -g deploy /opt/8kommerce
docker cp "$container_id":/var/www/html/deploy/. /opt/8kommerce/
docker rm "$container_id"
sudo chown -R deploy:deploy /opt/8kommerce
cd /opt/8kommerce
cp .env.production.example .env
chmod 600 .env
chmod 750 scripts/*.sh
```

Generate values without printing them into chat or tickets:

```bash
docker run --rm "$IMAGE" php artisan key:generate --show
openssl rand -hex 32
openssl rand -hex 32
```

Use the first value for `APP_KEY`, and separate random values for `DB_PASSWORD`
and `REDIS_PASSWORD`. Configure Paystack, Hubtel, support, plan, and operational
values directly in `/opt/8kommerce/.env`.

For Ghanaian Mobile Money-friendly package payments, set
`BILLING_MANUAL_RENEWAL_ENABLED=true` and
`BILLING_RECURRING_ENABLED=false`. Sellers approve each payment and receive
the configured number of access days. Paystack plan codes remain available for
future card-based recurring billing.

## Pre-launch

Keep `CADDYFILE=./Caddyfile.prelaunch`. This serves only:

- `app.8kommerce.com` (root redirects to seller login)
- `status.8kommerce.com` (root displays the public status page)

Deploy with:

```bash
./scripts/deploy.sh
```

Run and inspect the first backup:

```bash
./scripts/backup.sh manual
ls -lh backups
./scripts/install-backup-timer.sh
```

## Main-domain cutover

After pre-launch testing passes:

1. Change the Hostinger root A record to the VPS IP.
2. Keep `www` as a CNAME to the root domain.
3. Wait until both names resolve to the VPS.
4. Set `CADDYFILE=./Caddyfile.production` in `.env`.
5. Set the public Paystack callback to `https://8kommerce.com/pay/success`.
6. Run `./scripts/deploy.sh` again.
7. Update Paystack's webhook to `https://app.8kommerce.com/webhooks/paystack`.

## Releases and rollback

Update `APP_IMAGE` only to a digest published by the successful release workflow,
then run `./scripts/deploy.sh`. To switch back to a previous verified image:

```bash
./scripts/rollback.sh ghcr.io/blesspro/8kommerce@sha256:PREVIOUS_DIGEST
```

Database migrations are forward-only. A container rollback does not reverse a
migration; migrations must remain backward compatible.

## Backup limitation

The bundled backup is local and complements Contabo Auto Backup. Add encrypted
offsite storage before the platform holds material customer or payment history.
