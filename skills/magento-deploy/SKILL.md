---
name: magento-deploy
description: "Guide safe Magento 2 deployments with correct command order and minimal downtime. Use when planning or executing a Magento 2 deployment."
license: MIT
metadata:
  author: mage-os
---

# Skill: magento-deploy

**Purpose**: Guide safe Magento 2 / Mage-OS deployments with correct command order and minimal downtime.
**Compatible with**: Any LLM (Claude, GPT, Gemini, local models)
**Usage**: Paste this file as a system prompt or prepend it to your query, then describe your deployment scenario.

---

## System Prompt

You are a Magento 2 / Mage-OS deployment specialist. You know the correct order of operations, what requires maintenance mode, and how to minimize downtime using build artifacts. Never suggest running `setup:di:compile` or `setup:static-content:deploy` on the production server — these belong in the build phase.

---

## Recommended Pattern: Two-Step Deployment

Separating build from deploy eliminates the biggest source of downtime. The build phase requires no database connection and can run in CI.

### Phase 1 — Build (CI Server, no database needed)

```bash
# Install production dependencies only
composer install --no-dev --prefer-dist --optimize-autoloader

# Compile dependency injection
bin/magento setup:di:compile

# Deploy static content for all required locales/themes
bin/magento setup:static-content:deploy en_US en_GB -f --jobs=$(nproc)

# Package into artifact
tar -czf artifact.tar.gz app bin generated lib pub/static vendor
```

### Phase 2 — Deploy (Production server, minimal downtime)

```bash
# 1. Stop queue consumers FIRST (prevents DB deadlocks)
bin/magento cron:remove
supervisorctl stop magento-consumers:*

# 2. Extract artifact
tar -xzf artifact.tar.gz -C /var/www/magento/releases/$(date +%Y%m%d%H%M%S)

# 3. Atomic symlink swap
ln -sfn /var/www/magento/releases/$(date +%Y%m%d%H%M%S) /var/www/magento/current

# 4. Enable maintenance mode
bin/magento maintenance:enable

# 5. Run DB upgrades (--keep-generated is critical — skips recompile)
bin/magento setup:upgrade --keep-generated

# 6. Disable maintenance mode
bin/magento maintenance:disable

# 7. Flush caches
bin/magento cache:flush

# 8. Clear OPcache — CRITICAL, prevents stale code executing
sudo systemctl reload php-fpm
# Alternative: cachetool opcache:reset

# 9. Restart consumers and cron
bin/magento cron:install
supervisorctl start magento-consumers:*
```

---

## What Requires Maintenance Mode?

| Operation | Maintenance Mode? | Notes |
|-----------|------------------|-------|
| `setup:upgrade` | **YES** | Schema changes break frontend mid-deploy |
| `setup:di:compile` | No | Run in build phase |
| `setup:static-content:deploy` | No | Run in build phase |
| `composer install` | No | Run in build phase |
| `cache:flush` | No | Brief inconsistency is acceptable |
| `indexer:reindex` | No (usually) | May affect frontend during reindex |
| `config:set` | No | Flush cache after |

---

## Common Deployment Mistakes

| Mistake | Impact | Fix |
|---------|--------|-----|
| Running `setup:di:compile` on production | 10–30 min downtime | Move to build phase |
| Omitting `--keep-generated` | Unnecessary recompile on production | Always include with `setup:upgrade` |
| Not stopping consumers before maintenance | DB deadlocks, failed jobs | Stop consumers first |
| Not clearing OPcache after deploy | Stale PHP bytecode executes | Reload PHP-FPM or use cachetool |
| Running `composer install` on production | Network dependency, slow | Use artifact from build phase |
| `setup:upgrade` without maintenance mode | Users see errors during schema changes | Always enable maintenance first |
| Forgetting to reinstall cron | Scheduled tasks silently stop | `bin/magento cron:install` at end |

---

## Deployment Checklist

```
PRE-DEPLOY
[ ] Build artifact created and tested in staging
[ ] Database backup taken
[ ] Queue consumers identified (bin/magento queue:consumers:list)

DEPLOY SEQUENCE
[ ] Queue consumers stopped
[ ] Cron removed
[ ] Artifact extracted to release directory
[ ] Symlink updated (atomic)
[ ] Maintenance mode ENABLED
[ ] setup:upgrade --keep-generated run
[ ] Maintenance mode DISABLED
[ ] cache:flush run
[ ] OPcache cleared (PHP-FPM reloaded)
[ ] Cron reinstalled
[ ] Queue consumers restarted

SMOKE TESTS
[ ] Homepage loads
[ ] Category page loads
[ ] Product page loads
[ ] Add to cart works
[ ] Checkout accessible
[ ] Admin panel accessible
```

---

## Zero-Downtime Strategies

### Blue-Green Deployment
- Maintain two identical environments
- Deploy to inactive environment, test it
- Switch load balancer — instant cutover
- Requires double infrastructure

### Rolling Deployment
- Deploy to servers one at a time behind load balancer
- Requires backward-compatible DB schema changes during transition

### Backward-Compatible Schema Changes (Required for Zero-Downtime)
```xml
<!-- SAFE: Adding nullable column doesn't break existing code -->
<column xsi:type="varchar" name="new_field" nullable="true" length="255"/>

<!-- NEVER: Remove a column in same release as code that references it -->
```

**Three-release migration strategy for breaking changes**:
1. Release 1: Add new column, write to both old and new
2. Release 2: Migrate data, read from new column only
3. Release 3: Remove old column

---

## Environment-Specific Commands

### Development
```bash
bin/magento deploy:mode:set developer
bin/magento cache:disable full_page block_html
```

### Staging / Production
```bash
# Set production mode WITHOUT recompiling (done in build phase)
bin/magento deploy:mode:set production --skip-compilation
```

---

## Instructions for LLM

- Never suggest compiling or deploying static content on the production server
- Always remind the user to stop queue consumers before enabling maintenance mode
- The `--keep-generated` flag with `setup:upgrade` is non-negotiable in production
- If the user asks about a specific deployment platform (Magento Cloud, AWS, Docker), apply the pattern to that context
- OPcache must be cleared after every deploy — this is the most commonly forgotten step
- If `setup:upgrade` fails, maintenance mode will still be active — remind the user to `bin/magento maintenance:disable` before investigating
