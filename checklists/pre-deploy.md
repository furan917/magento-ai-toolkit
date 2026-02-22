# Pre-Deploy Checklist

Run through this list before every production deployment. Tick each item before proceeding to the next phase.

---

## 1. Code Readiness

- [ ] All feature branches merged into the release branch
- [ ] No merge conflicts remaining
- [ ] `composer install --no-dev` runs cleanly on a fresh checkout
- [ ] `bin/magento setup:di:compile` passes with zero errors (run on a staging build server, not production)
- [ ] `bin/magento setup:static-content:deploy` passes for all locales
- [ ] No new `vendor/` modifications (all customisations are in `app/code/` or `app/design/`)

## 2. Database Migration Review

- [ ] `db_schema.xml` changes reviewed — no destructive column drops without whitelist entry
- [ ] `db_schema_whitelist.json` regenerated: `bin/magento setup:db-declaration:generate-whitelist --module-name=...`
- [ ] Data patches (`DataPatchInterface`) reviewed for idempotency (safe to re-run)
- [ ] `bin/magento setup:db:status` shows pending migrations on staging — confirms they run clean

## 3. Configuration & Secrets

- [ ] `app/etc/env.php` is NOT committed to version control
- [ ] Production `env.php` has Redis `id_prefix` set and DB assignments correct (DB 0 cache, DB 1 FPC, DB 2 sessions)
- [ ] All required environment variables / secrets are set in deployment pipeline
- [ ] Store email addresses and payment gateway keys point to production endpoints (not sandbox)

## 4. Staging Sign-Off

- [ ] Deployment ran successfully on staging within the last 24 hours
- [ ] QA tested all changed user journeys on staging
- [ ] No PHP errors in `var/log/exception.log` on staging after deployment
- [ ] Performance baseline unchanged on staging (homepage TTFB within 10% of previous)

## 5. Rollback Preparation

- [ ] Database backup taken immediately before deployment begins
- [ ] Previous release tag / git SHA recorded
- [ ] Rollback procedure documented and shared with the team
- [ ] Responsible engineer available for 1 hour after go-live

## 6. Deployment Window

- [ ] Low-traffic window selected (check analytics — typically 02:00–05:00 local)
- [ ] Stakeholders notified of maintenance window
- [ ] Support team briefed on what changed and what to watch for

---

## Deploy Commands (in order)

```bash
bin/magento maintenance:enable
git pull origin release/x.y.z   # or rsync / deploy tool
composer install --no-dev --optimize-autoloader
bin/magento setup:upgrade --keep-generated
bin/magento setup:di:compile     # only if not pre-compiled in build phase
bin/magento cache:flush
bin/magento maintenance:disable
```

> **After deploy:** Reload PHP-FPM (`sudo systemctl reload php8.x-fpm`) to clear OPcache.
> Then run smoke tests before declaring the deployment successful.
