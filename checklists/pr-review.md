# PR Review Checklist

Use this when reviewing a Magento module pull request. Work through each section in order — stop and request changes if a blocker is found.

---

## Blockers (must fix before merge)

- [ ] No `ObjectManager::getInstance()` calls — all dependencies via constructor injection
- [ ] No `InstallSchema.php` or `UpgradeSchema.php` — use `db_schema.xml` only
- [ ] No modifications inside `vendor/` — all customisations via plugin or preference
- [ ] No `float` column type for monetary values — must be `decimal` with `precision="12" scale="4"`
- [ ] No hardcoded store IDs, currency codes, or base URLs in PHP or XML
- [ ] No raw SQL queries in Model or service classes — use ResourceModel / CollectionFactory
- [ ] No secrets, credentials, or `env.php` values committed to the repository
- [ ] `declare(strict_types=1)` present in every new PHP file

## Architecture

- [ ] Plugins use the correct type: `before` modifies args, `after` modifies result, `around` only when interception of `$proceed` is truly needed
- [ ] Observers implement `ObserverInterface`, use `shared="false"` in `events.xml`, and delegate heavy logic to a service
- [ ] REST endpoints in `webapi.xml` point to `Api/` interfaces, not Model classes
- [ ] `Api/` interfaces have complete PHPDoc `@param`, `@return`, `@throws` on every method
- [ ] New tables use `db_schema.xml`; `db_schema_whitelist.json` is committed alongside it
- [ ] FK columns in `db_schema.xml` have a matching `<index>` element

## Code Quality

- [ ] No `var_dump`, `echo`, `print_r`, `die()` left in any file
- [ ] No commented-out blocks of code (use version control instead)
- [ ] Constructor parameters are all used — no injected-but-unused dependencies
- [ ] No overly long methods (>50 lines is a smell — consider extracting)
- [ ] Service classes are not instantiated with `new` — always injected via DI

## Configuration Files

- [ ] `module.xml` `<sequence>` lists every module this code depends on at runtime
- [ ] `di.xml` is area-scoped where possible (`etc/frontend/di.xml`, `etc/adminhtml/di.xml`)
- [ ] `crontab.xml` jobs use sensible schedules (no `* * * * *` unless justified)
- [ ] `acl.xml` includes ACL resources for every new admin endpoint or menu item

## Tests

- [ ] Unit tests cover the core business logic (repository methods, service calculations)
- [ ] No test uses `ObjectManager::getInstance()` — use `Bootstrap::getObjectManager()` for integration tests only
- [ ] Mocks use `createMock()` for interfaces, `getMockBuilder()->disableOriginalConstructor()` for concrete classes
- [ ] Integration tests use `@magentoDbIsolation enabled` to avoid polluting the test database

## Database Migrations

- [ ] `db_schema_whitelist.json` is present and up-to-date if columns were added or removed
- [ ] Data patches implement `DataPatchInterface` (not `InstallData` or `UpgradeData`)
- [ ] Data patches are idempotent — safe to run more than once without side effects
- [ ] `getAliases()` returns `[]` unless there is a genuine migration from an old patch

## Final Sign-Off

- [ ] PR description explains the *why*, not just the *what*
- [ ] `bin/magento setup:di:compile` confirmed passing in CI or locally
- [ ] No unresolved review comments
- [ ] Reviewer has tested the change in a browser (for UI changes) or via API client (for REST/GraphQL)
