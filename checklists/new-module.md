# New Module Checklist

Use this when creating a Magento module from scratch. Complete Phase 1 before writing any feature code.

---

## Phase 1 — Module Skeleton (always required)

- [ ] Directory created: `app/code/Vendor/Module/`
- [ ] `registration.php` created — `ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Vendor_Module', __DIR__)`
- [ ] `etc/module.xml` created — `<module name="Vendor_Module">` with `<sequence>` listing all dependencies
- [ ] Module name in `registration.php` **exactly matches** `etc/module.xml` (copy-paste, don't retype)
- [ ] `bin/magento module:enable Vendor_Module` runs without error
- [ ] `bin/magento setup:upgrade` runs without error
- [ ] `bin/magento module:status | grep Vendor_Module` shows `Enabled`

## Phase 2 — Database (if the module has its own tables)

- [ ] `etc/db_schema.xml` created with correct column types (no `float` for money, no `InstallSchema`)
- [ ] Every `<column>` has a `comment` attribute
- [ ] Primary key declared as `identity="true"` on the PK column
- [ ] FK columns have a matching `<index>` element (MySQL doesn't auto-create FK indexes)
- [ ] `bin/magento setup:db-declaration:generate-whitelist --module-name=Vendor_Module` run
- [ ] `db_schema_whitelist.json` committed alongside `db_schema.xml`
- [ ] `bin/magento setup:upgrade` applies schema with no errors

## Phase 3 — Dependency Injection & Models (if CRUD entity)

- [ ] `Api/EntityInterface.php` — data interface with PHPDoc `@return` / `@param` on every method
- [ ] `Api/EntityRepositoryInterface.php` — CRUD interface with `getById`, `getList`, `save`, `deleteById`
- [ ] `Model/Entity.php` — extends `AbstractModel`, `_init` points to resource model
- [ ] `Model/ResourceModel/Entity.php` — extends `AbstractDb`, `_init` with table name and PK
- [ ] `Model/ResourceModel/Entity/Collection.php` — extends `AbstractCollection`, `_init` with both model and resource model
- [ ] `etc/di.xml` — preference mapping `EntityInterface` → `Entity`, `EntityRepositoryInterface` → `EntityRepository`

## Phase 4 — REST API (if exposing via REST)

- [ ] `etc/webapi.xml` routes point to `Api/` interfaces, never to Model classes directly
- [ ] `etc/acl.xml` declares ACL resources for each protected endpoint
- [ ] All `Api/` interface methods have complete PHPDoc (REST serialiser depends on it)
- [ ] `bin/magento cache:clean config` run after adding `webapi.xml`
- [ ] Endpoint tested: `curl -H "Authorization: Bearer {token}" /rest/V1/vendor/entities`

## Phase 5 — Events / Plugins (if hooking into Magento behaviour)

- [ ] Plugin declared in `etc/di.xml` (area-scoped file where possible: `etc/frontend/di.xml`)
- [ ] Plugin type correct for the use case: `before` (modify args), `after` (modify result), `around` (last resort)
- [ ] Observer declared in `etc/events.xml` with `shared="false"`
- [ ] Observer class implements `ObserverInterface` with `execute(Observer $observer): void`

## Phase 6 — Admin UI (if adding a grid or form)

- [ ] `etc/adminhtml/menu.xml` adds the menu item
- [ ] `etc/adminhtml/routes.xml` declares the route with `router id="admin"`
- [ ] `etc/acl.xml` includes the menu item's ACL resource
- [ ] Admin role re-saved in Magento admin after adding new ACL resources

## Phase 7 — Final Checks

- [ ] `bin/magento setup:di:compile` passes with zero errors
- [ ] No `var_dump`, `echo`, `print_r` left in any PHP file
- [ ] No `ObjectManager::getInstance()` calls (use constructor injection)
- [ ] `declare(strict_types=1)` at the top of every PHP file
- [ ] Unit tests written for service/model logic
- [ ] `bin/magento cache:flush` run before testing in browser
