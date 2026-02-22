# Magento 2 AI Agent Reference Document

> **Version**: Magento 2.4.8+ | PHP 8.3+ | OpenSearch 2.x
> **Last Updated**: January 2025
> **Purpose**: Token-efficient reference for AI agents developing Magento 2 solutions

---

## Critical Notes

| Note | Description |
|------|-------------|
| **Core Location** | Core modules are in `vendor/magento/module-*`, NOT `app/code` |
| **Custom Modules** | Always create custom modules in `app/code/Vendor/Module` |
| **Never Edit Core** | Never modify files in `vendor/` - use plugins, preferences, or observers |
| **Editions** | Open Source (CE) vs Adobe Commerce (EE) - some features are Commerce-only |
| **Mage-OS** | Community fork of Magento - fully compatible, faster bug fixes, independent governance |

---

## 1. Mage-OS (Community Distribution)

> **Mage-OS** is an independent, community-driven distribution of Magento Open Source, managed by the non-profit **Mage-OS Association**. It is fully compatible with Magento 2 while offering faster bug fixes, community improvements, and open governance.
>
> - **Website**: https://mage-os.org/
> - **Documentation**: https://devdocs.mage-os.org/
> - **GitHub**: https://github.com/mage-os
> - **Discord**: https://mage-os.org/discord-channel/
> - **License**: Open Software License (OSL 3.0)

### 1.1 Mage-OS vs Magento Open Source

| Aspect | Magento Open Source | Mage-OS |
|--------|---------------------|---------|
| Governance | Adobe-controlled | Non-profit Mage-OS Association |
| Bug Fix Speed | Slower release cycles | Faster community-driven fixes |
| Contributions | Strict Adobe review process | Open PR process, lower barriers |
| Compatibility | Baseline | 100% compatible with Magento modules |
| Package Repository | repo.magento.com | repo.mage-os.org |
| Support | Adobe support (Commerce) | Community support |
| Innovation | Adobe roadmap | Community-driven features |

### 1.2 Key Benefits

| Benefit | Description |
|---------|-------------|
| **Faster Fixes** | Community patches merged without waiting for Adobe release cycles |
| **Open Governance** | Decisions made by elected community members, not corporate interests |
| **Full Compatibility** | Existing Magento 2 themes, extensions, and integrations work seamlessly |
| **Independent Security** | Security patches can be released independently of Adobe schedule |
| **Modern Stack** | Faster adoption of PHP versions and dependency updates |
| **Transparent Development** | All development happens in public GitHub repos |

### 1.3 Installation

**Via Composer (Recommended)**:
```bash
# Create new Mage-OS project
composer create-project --repository-url=https://repo.mage-os.org/ \
    mage-os/project-community-edition .

# Standard Magento setup
bin/magento setup:install \
    --base-url=https://your-store.test \
    --db-host=localhost \
    --db-name=mageos \
    --db-user=magento \
    --db-password=magento \
    --admin-firstname=Admin \
    --admin-lastname=User \
    --admin-email=admin@example.com \
    --admin-user=admin \
    --admin-password=Admin123! \
    --language=en_US \
    --currency=USD \
    --timezone=America/New_York \
    --use-rewrites=1 \
    --search-engine=opensearch \
    --opensearch-host=localhost \
    --opensearch-port=9200

# Post-install
bin/magento deploy:mode:set developer
bin/magento indexer:reindex
bin/magento cron:install
```

**Using Adobe Marketplace Extensions with Mage-OS**:
```bash
# Add both repositories (Mage-OS takes priority)
composer config repositories.mage-os composer https://repo.mage-os.org/
composer config repositories.magento composer https://repo.magento.com/

# Configure repo.magento.com credentials
composer config --auth http-basic.repo.magento.com <public-key> <private-key>
```

### 1.4 Version Mapping

| Mage-OS Version | Based On | Key Changes |
|-----------------|----------|-------------|
| 2.1.0 | Magento 2.4.8-p3 | Bug fixes, expanded DB compatibility, new dev tools |
| 2.0.0 | Magento 2.4.8-p3 | PHP 8.3-8.4, OpenSearch 3, Composer 2.8.8 |
| 1.3.1 | Magento 2.4.8-p2 | Security patches |
| 1.1.0 | Magento 2.4.7 | New admin experience |

### 1.5 Unique Mage-OS Features

**Additional CLI Commands**:
```bash
# Detect unregistered themes
bin/magento theme:detect-unregistered

# Check queue configuration status
bin/magento queue:config:status
```

**Bug Fixes Merged Faster** (examples from 2.1.0):
- Fix FPC not working after customer login
- Fix SRI hash failures with JS bundling
- Fix setup:di:compile area order in plugin generation
- Fix customer group extension attributes being overwritten

### 1.6 Switching from Magento to Mage-OS

```bash
# 1. Update composer.json repositories
composer config repositories.mage-os composer https://repo.mage-os.org/

# 2. Replace magento packages with mage-os equivalents
composer require mage-os/project-community-edition --no-update

# 3. Update dependencies
composer update

# 4. Run setup
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### 1.7 When to Choose Mage-OS

| Choose Mage-OS If... | Choose Magento If... |
|----------------------|----------------------|
| You want faster community bug fixes | You need official Adobe support |
| You prefer open, transparent governance | You're using Adobe Commerce (EE) |
| You want to contribute back more easily | Corporate policy requires vendor backing |
| You value community-driven innovation | You need Adobe Marketplace private extensions |

### 1.8 Mage-OS Repositories

| Repository | Purpose |
|------------|---------|
| `mage-os/mageos-magento2` | Core Mage-OS distribution |
| `mage-os/project-community-edition` | Composer project template |
| `mage-os/mirror-*` | Mirrors of Magento packages |
| `mage-os/mageos-*` | Mage-OS specific packages |

**Important**: Mage-OS is **not** affiliated with Adobe or Magento in any way. It is an independent fork maintained by the community.

---

## 2. Directory Structure

### Root Directory Layout

| Directory | Purpose |
|-----------|---------|
| `app/code/` | Custom modules (Vendor/Module structure) |
| `app/design/` | Themes (frontend/adminhtml) |
| `app/etc/` | Configuration (env.php, config.php, di.xml) |
| `app/i18n/` | Translation packages |
| `vendor/` | Composer dependencies including Magento core |
| `vendor/magento/module-*` | Core Magento modules |
| `pub/` | Web root (static, media) |
| `var/` | Generated files, cache, logs, sessions |
| `generated/` | Generated code (factories, proxies, interceptors) |
| `bin/magento` | CLI tool |
| `dev/` | Development tools, tests |
| `setup/` | Installation scripts |

### Module Structure Template

```
app/code/Vendor/Module/
├── Api/                      # Service contracts (interfaces)
│   └── Data/                 # Data interfaces
├── Block/                    # View layer blocks
├── Console/                  # CLI commands
├── Controller/               # HTTP controllers
│   └── Adminhtml/            # Admin controllers
├── Cron/                     # Cron job classes
├── etc/                      # Configuration
│   ├── module.xml            # Module declaration
│   ├── di.xml                # Dependency injection
│   ├── events.xml            # Event observers
│   ├── routes.xml            # Frontend routes
│   ├── adminhtml/            # Admin-specific config
│   │   ├── di.xml
│   │   ├── routes.xml
│   │   ├── menu.xml
│   │   └── system.xml
│   ├── frontend/             # Frontend-specific config
│   │   ├── di.xml
│   │   ├── routes.xml
│   │   └── events.xml
│   └── webapi.xml            # REST/SOAP API definitions
├── Helper/                   # Helper classes (use sparingly)
├── i18n/                     # Translation CSV files
├── Model/                    # Business logic
│   └── ResourceModel/        # Database layer
│       └── Entity/           # Collection classes
├── Observer/                 # Event observers
├── Plugin/                   # Interceptors
├── Setup/                    # Install/upgrade scripts (legacy)
│   ├── InstallData.php
│   ├── InstallSchema.php
│   ├── UpgradeData.php
│   └── Patch/                # Data/Schema patches (preferred)
│       ├── Data/
│       └── Schema/
├── Test/                     # Unit/integration tests
├── Ui/                       # UI components
│   └── Component/
├── view/                     # Frontend resources
│   ├── adminhtml/
│   │   ├── layout/
│   │   ├── templates/
│   │   ├── ui_component/
│   │   └── web/
│   └── frontend/
│       ├── layout/
│       ├── templates/
│       ├── requirejs-config.js
│       └── web/
│           ├── css/
│           ├── js/
│           └── template/     # Knockout templates
├── ViewModel/                # View models for templates
├── composer.json             # Composer package definition
├── registration.php          # Module registration
└── db_schema.xml             # Declarative schema
```

---

## 3. Core Design Patterns

### 3.1 Dependency Injection (DI)

**Concept**: All dependencies are injected via constructor. Configure in `di.xml`.

**di.xml Configuration Types**:

| Element | Purpose | Example Use |
|---------|---------|-------------|
| `<preference>` | Replace implementation | Swap interface for concrete class |
| `<type>` | Configure specific class | Add plugins, set arguments |
| `<virtualType>` | Create alias without new class | Reuse class with different config |
| `<argument>` | Pass constructor arguments | Inject specific values/objects |

**Example di.xml**:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

    <!-- Interface preference -->
    <preference for="Vendor\Module\Api\ServiceInterface"
                type="Vendor\Module\Model\Service"/>

    <!-- Type configuration with arguments -->
    <type name="Vendor\Module\Model\Service">
        <arguments>
            <argument name="logger" xsi:type="object">Psr\Log\LoggerInterface</argument>
            <argument name="cacheEnabled" xsi:type="boolean">true</argument>
            <argument name="config" xsi:type="array">
                <item name="timeout" xsi:type="number">30</item>
            </argument>
        </arguments>
    </type>

    <!-- Virtual type - reusable configuration -->
    <virtualType name="CustomLogger" type="Magento\Framework\Logger\Monolog">
        <arguments>
            <argument name="name" xsi:type="string">custom_channel</argument>
        </arguments>
    </virtualType>
</config>
```

**Constructor Injection Pattern**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Service
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly array $config = []
    ) {
    }
}
```

### 3.2 Plugins (Interceptors)

**Plugin Types**:

| Type | Method Prefix | When Runs | Can Modify |
|------|---------------|-----------|------------|
| Before | `before` | Before original | Input arguments |
| After | `after` | After original | Return value |
| Around | `around` | Wraps original | Both (use sparingly) |

**Plugin Declaration (di.xml)**:
```xml
<type name="Magento\Catalog\Model\Product">
    <plugin name="vendor_module_product_plugin"
            type="Vendor\Module\Plugin\ProductPlugin"
            sortOrder="10"
            disabled="false"/>
</type>
```

**Plugin Class**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Plugin;

use Magento\Catalog\Model\Product;

class ProductPlugin
{
    // Before: modify input, return array of arguments or null
    public function beforeSetName(Product $subject, string $name): array
    {
        return [trim($name)];
    }

    // After: modify output
    public function afterGetName(Product $subject, string $result): string
    {
        return strtoupper($result);
    }

    // Around: full control (use sparingly - performance impact)
    public function aroundSave(
        Product $subject,
        callable $proceed,
        ...$args
    ): Product {
        // Before logic
        $result = $proceed(...$args); // Call original
        // After logic
        return $result;
    }
}
```

**Plugin Sort Order**:
- Lower numbers execute first for `before` plugins
- Higher numbers execute first for `after` plugins
- Default is 0 if not specified

### 3.3 Observers & Events

**Event Configuration (events.xml)**:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="sales_order_place_after">
        <observer name="vendor_module_order_observer"
                  instance="Vendor\Module\Observer\OrderPlaceAfter"
                  disabled="false"
                  shared="false"/>
    </event>
</config>
```

**Observer Class**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderPlaceAfter implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        // Process order
    }
}
```

**Common Events**:

| Event | Data Available | Use Case |
|-------|----------------|----------|
| `catalog_product_save_after` | product | After product save |
| `sales_order_place_after` | order | After order placement |
| `checkout_cart_add_product_complete` | product, request | After add to cart |
| `customer_login` | customer | After customer login |
| `controller_action_predispatch` | request, controller | Before controller action |
| `layout_load_before` | layout, full_action_name | Before layout loads |
| `sales_quote_collect_totals_after` | quote | After quote totals |

**Dispatching Custom Events**:
```php
$this->eventManager->dispatch('vendor_custom_event', [
    'custom_object' => $object,
    'data' => $data
]);
```

### 3.4 Service Contracts & Repositories

**Interface Structure**:
```
Api/
├── ServiceInterface.php           # Service contract
├── Data/
│   └── EntityInterface.php        # Data interface
└── EntityRepositoryInterface.php  # Repository interface
```

**Repository Interface**:
```php
<?php
namespace Vendor\Module\Api;

use Vendor\Module\Api\Data\EntityInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface EntityRepositoryInterface
{
    public function save(EntityInterface $entity): EntityInterface;
    public function get(int $id): EntityInterface;
    public function getById(int $id): EntityInterface;
    public function delete(EntityInterface $entity): bool;
    public function deleteById(int $id): bool;
    public function getList(SearchCriteriaInterface $criteria): \Magento\Framework\Api\SearchResultsInterface;
}
```

**Data Interface**:
```php
<?php
namespace Vendor\Module\Api\Data;

interface EntityInterface
{
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';

    public function getEntityId(): ?int;
    public function setEntityId(int $id): self;
    public function getName(): ?string;
    public function setName(string $name): self;
}
```

**Repository Implementation**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

use Vendor\Module\Api\EntityRepositoryInterface;
use Vendor\Module\Api\Data\EntityInterface;
use Vendor\Module\Model\ResourceModel\Entity as ResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;

class EntityRepository implements EntityRepositoryInterface
{
    public function __construct(
        private readonly ResourceModel $resource,
        private readonly EntityFactory $entityFactory
    ) {
    }

    public function get(int $id): EntityInterface
    {
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $id);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Entity with ID %1 not found', $id));
        }
        return $entity;
    }

    public function save(EntityInterface $entity): EntityInterface
    {
        $this->resource->save($entity);
        return $entity;
    }
}
```

### 3.5 Factories & Proxies

**Factory Pattern**:
- Auto-generated in `generated/` directory
- Use for creating new instances of objects
- Naming: `{ClassName}Factory`

```php
// Usage
public function __construct(
    private readonly \Magento\Catalog\Model\ProductFactory $productFactory
) {}

public function createProduct(): Product
{
    return $this->productFactory->create();
}
```

**Proxy Pattern**:
- Lazy loading wrapper
- Use for expensive dependencies not always needed
- Configure in di.xml:

```xml
<type name="Vendor\Module\Model\HeavyService">
    <arguments>
        <argument name="session" xsi:type="object">Magento\Customer\Model\Session\Proxy</argument>
    </arguments>
</type>
```

### 3.6 EAV (Entity-Attribute-Value)

**EAV Entities**: Product, Category, Customer, Customer Address

**Key Tables** (Product example):

| Table | Purpose |
|-------|---------|
| `catalog_product_entity` | Main entity table |
| `catalog_product_entity_varchar` | VARCHAR attributes |
| `catalog_product_entity_int` | INT attributes |
| `catalog_product_entity_text` | TEXT attributes |
| `catalog_product_entity_decimal` | DECIMAL attributes |
| `catalog_product_entity_datetime` | DATETIME attributes |
| `eav_attribute` | Attribute definitions |
| `eav_entity_type` | Entity type definitions |

**Creating EAV Attributes** (Data Patch):
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class AddCustomAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'custom_attribute',
            [
                'type' => 'varchar',
                'label' => 'Custom Attribute',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General'
            ]
        );
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
```

---

## 4. Configuration Files Reference

### Module Configuration Files

| File | Location | Purpose |
|------|----------|---------|
| `module.xml` | `etc/` | Module declaration, version, dependencies |
| `di.xml` | `etc/`, `etc/frontend/`, `etc/adminhtml/` | Dependency injection |
| `routes.xml` | `etc/frontend/`, `etc/adminhtml/` | URL routing |
| `events.xml` | `etc/`, `etc/frontend/`, `etc/adminhtml/` | Event observers |
| `acl.xml` | `etc/` | Admin permissions |
| `menu.xml` | `etc/adminhtml/` | Admin menu items |
| `system.xml` | `etc/adminhtml/` | System configuration |
| `config.xml` | `etc/` | Default config values |
| `crontab.xml` | `etc/` | Cron job definitions |
| `webapi.xml` | `etc/` | REST/SOAP API endpoints |
| `db_schema.xml` | `etc/` | Database schema (declarative) |
| `indexer.xml` | `etc/` | Indexer definitions |
| `mview.xml` | `etc/` | Materialized view triggers |
| `widget.xml` | `etc/` | Widget definitions |
| `email_templates.xml` | `etc/` | Email template definitions |
| `csp_whitelist.xml` | `etc/` | Content Security Policy |
| `communication.xml` | `etc/` | Message queue topics |
| `queue_consumer.xml` | `etc/` | Queue consumers |
| `queue_publisher.xml` | `etc/` | Queue publishers |
| `queue_topology.xml` | `etc/` | Queue exchanges/bindings |

### Frontend Configuration Files

| File | Location | Purpose |
|------|----------|---------|
| `layouts.xml` | `view/frontend/` | Layout handle definitions |
| `page_types.xml` | `view/frontend/` | Page type definitions |
| `requirejs-config.js` | `view/frontend/` | RequireJS configuration |
| `*.xml` | `view/frontend/layout/` | Layout XML files |
| `*.phtml` | `view/frontend/templates/` | Template files |
| `*.xml` | `view/frontend/ui_component/` | UI component definitions |

### Example module.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Vendor_Module">
        <sequence>
            <module name="Magento_Catalog"/>
            <module name="Magento_Sales"/>
        </sequence>
    </module>
</config>
```

### Example routes.xml (Frontend)
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:App/etc/routes.xsd">
    <router id="standard">
        <route id="vendor" frontName="custom">
            <module name="Vendor_Module"/>
        </route>
    </router>
</config>
```
URL pattern: `/custom/controller/action` maps to `Controller/Controller/Action.php`

### Example system.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">
    <system>
        <section id="vendor_section" translate="label" sortOrder="100" showInDefault="1">
            <label>Vendor Settings</label>
            <tab>general</tab>
            <resource>Vendor_Module::config</resource>
            <group id="general" translate="label" sortOrder="10" showInDefault="1">
                <label>General Settings</label>
                <field id="enabled" translate="label" type="select" sortOrder="10" showInDefault="1">
                    <label>Enable Module</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
        </section>
    </system>
</config>
```

---

## 5. Key Modules Quick Reference

### Core Magento Modules

| Module | Purpose | Key Interfaces | Common Customization |
|--------|---------|----------------|----------------------|
| `Magento_Catalog` | Products, categories | `ProductRepositoryInterface`, `CategoryRepositoryInterface` | Attributes, pricing, product types |
| `Magento_Sales` | Orders, invoices, shipments | `OrderRepositoryInterface`, `OrderManagementInterface` | Order flow, status, emails |
| `Magento_Quote` | Shopping cart | `CartRepositoryInterface`, `CartManagementInterface` | Cart rules, totals |
| `Magento_Checkout` | Checkout process | `ShippingInformationManagementInterface` | Steps, payment integration |
| `Magento_Customer` | Customer accounts | `CustomerRepositoryInterface`, `AccountManagementInterface` | Registration, attributes |
| `Magento_Payment` | Payment methods | `MethodInterface`, `Gateway\CommandInterface` | Custom payment methods |
| `Magento_Shipping` | Shipping methods | `CarrierInterface` | Custom carriers |
| `Magento_Store` | Multi-store | `StoreManagerInterface` | Store/website logic |
| `Magento_Ui` | Admin grids/forms | `DataProviderInterface` | Admin UI components |
| `Magento_Cms` | CMS pages/blocks | `PageRepositoryInterface`, `BlockRepositoryInterface` | Content management |
| `Magento_InventorySales` | MSI inventory | `GetProductSalableQtyInterface` | Stock management |
| `Magento_Framework` | Core framework | Various | Low-level customization |

### Important Service Classes

| Service | Class/Interface | Usage |
|---------|-----------------|-------|
| Product Repository | `Magento\Catalog\Api\ProductRepositoryInterface` | CRUD products |
| Category Repository | `Magento\Catalog\Api\CategoryRepositoryInterface` | CRUD categories |
| Customer Repository | `Magento\Customer\Api\CustomerRepositoryInterface` | CRUD customers |
| Order Repository | `Magento\Sales\Api\OrderRepositoryInterface` | CRUD orders |
| Cart Repository | `Magento\Quote\Api\CartRepositoryInterface` | CRUD carts |
| Store Manager | `Magento\Store\Model\StoreManagerInterface` | Store context |
| Search Criteria Builder | `Magento\Framework\Api\SearchCriteriaBuilder` | Build queries |
| Filter Builder | `Magento\Framework\Api\FilterBuilder` | Build filters |
| Logger | `Psr\Log\LoggerInterface` | Logging |
| Cache | `Magento\Framework\App\CacheInterface` | Caching |
| Event Manager | `Magento\Framework\Event\ManagerInterface` | Dispatch events |
| Message Manager | `Magento\Framework\Message\ManagerInterface` | Flash messages |
| URL Builder | `Magento\Framework\UrlInterface` | Generate URLs |
| Registry | `Magento\Framework\Registry` | Global registry (deprecated) |
| Session | `Magento\Customer\Model\Session` | Customer session |
| Escaper | `Magento\Framework\Escaper` | XSS prevention |

---

## 6. Frontend Architecture

### 6.1 Layout XML

**Layout Handles**:
- `default` - All pages
- `catalog_product_view` - Product detail page
- `catalog_category_view` - Category page
- `checkout_index_index` - Checkout page
- `customer_account_login` - Login page
- Custom: `{route}_{controller}_{action}`

**Layout XML Structure**:
```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">

    <head>
        <title>Page Title</title>
        <css src="Vendor_Module::css/styles.css"/>
        <script src="Vendor_Module::js/script.js"/>
    </head>

    <body>
        <referenceContainer name="content">
            <block class="Vendor\Module\Block\Custom"
                   name="custom.block"
                   template="Vendor_Module::custom.phtml"
                   before="-"
                   cacheable="true">
                <arguments>
                    <argument name="view_model" xsi:type="object">Vendor\Module\ViewModel\Custom</argument>
                </arguments>
            </block>
        </referenceContainer>

        <referenceBlock name="product.info.details">
            <block class="Magento\Catalog\Block\Product\View"
                   name="custom.tab"
                   template="Vendor_Module::tab.phtml"
                   group="detailed_info">
                <arguments>
                    <argument name="title" xsi:type="string">Custom Tab</argument>
                </arguments>
            </block>
        </referenceBlock>

        <move element="custom.block" destination="sidebar.main" after="-"/>
        <remove name="unwanted.block"/>
    </body>
</page>
```

**Common Containers**:

| Container | Location |
|-----------|----------|
| `header.container` | Header area |
| `page.top` | Below header |
| `columns.top` | Above main columns |
| `main` | Main content wrapper |
| `content` | Main content area |
| `sidebar.main` | Left sidebar |
| `sidebar.additional` | Right sidebar |
| `footer-container` | Footer area |

### 6.2 Blocks & Templates

**Block Class**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Custom extends Template
{
    public function __construct(
        Context $context,
        private readonly \Vendor\Module\ViewModel\Custom $viewModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomData(): array
    {
        return $this->viewModel->getData();
    }
}
```

**ViewModel (Preferred over Block)**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Custom implements ArgumentInterface
{
    public function __construct(
        private readonly \Vendor\Module\Model\Service $service
    ) {
    }

    public function getData(): array
    {
        return $this->service->getItems();
    }
}
```

**Template (PHTML)**:
```php
<?php
/** @var \Vendor\Module\Block\Custom $block */
/** @var \Magento\Framework\Escaper $escaper */
/** @var \Vendor\Module\ViewModel\Custom $viewModel */
$viewModel = $block->getData('view_model');
?>
<div class="custom-block">
    <h2><?= $escaper->escapeHtml(__('Custom Title')) ?></h2>
    <?php foreach ($viewModel->getData() as $item): ?>
        <p><?= $escaper->escapeHtml($item->getName()) ?></p>
    <?php endforeach; ?>
</div>
```

### 6.3 UI Components

**Grid Component** (ui_component/vendor_entity_listing.xml):
```xml
<?xml version="1.0"?>
<listing xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">
    <argument name="data" xsi:type="array">
        <item name="js_config" xsi:type="array">
            <item name="provider" xsi:type="string">vendor_entity_listing.vendor_entity_listing_data_source</item>
        </item>
    </argument>
    <settings>
        <spinner>vendor_entity_columns</spinner>
        <deps>
            <dep>vendor_entity_listing.vendor_entity_listing_data_source</dep>
        </deps>
    </settings>
    <dataSource name="vendor_entity_listing_data_source" component="Magento_Ui/js/grid/provider">
        <settings>
            <storageConfig>
                <param name="indexField" xsi:type="string">entity_id</param>
            </storageConfig>
            <updateUrl path="mui/index/render"/>
        </settings>
        <dataProvider class="Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider"
                      name="vendor_entity_listing_data_source">
            <settings>
                <requestFieldName>id</requestFieldName>
                <primaryFieldName>entity_id</primaryFieldName>
            </settings>
        </dataProvider>
    </dataSource>
    <listingToolbar name="listing_top">
        <settings>
            <sticky>true</sticky>
        </settings>
        <bookmark name="bookmarks"/>
        <columnsControls name="columns_controls"/>
        <filterSearch name="fulltext"/>
        <filters name="listing_filters"/>
        <paging name="listing_paging"/>
    </listingToolbar>
    <columns name="vendor_entity_columns">
        <selectionsColumn name="ids">
            <settings>
                <indexField>entity_id</indexField>
            </settings>
        </selectionsColumn>
        <column name="entity_id">
            <settings>
                <filter>textRange</filter>
                <label translate="true">ID</label>
                <sorting>asc</sorting>
            </settings>
        </column>
        <column name="name">
            <settings>
                <filter>text</filter>
                <label translate="true">Name</label>
            </settings>
        </column>
        <actionsColumn name="actions" class="Vendor\Module\Ui\Component\Listing\Column\Actions">
            <settings>
                <indexField>entity_id</indexField>
            </settings>
        </actionsColumn>
    </columns>
</listing>
```

**Form Component** (ui_component/vendor_entity_form.xml):
```xml
<?xml version="1.0"?>
<form xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">
    <argument name="data" xsi:type="array">
        <item name="js_config" xsi:type="array">
            <item name="provider" xsi:type="string">vendor_entity_form.vendor_entity_form_data_source</item>
        </item>
        <item name="template" xsi:type="string">templates/form/collapsible</item>
    </argument>
    <settings>
        <namespace>vendor_entity_form</namespace>
        <dataScope>data</dataScope>
        <deps>
            <dep>vendor_entity_form.vendor_entity_form_data_source</dep>
        </deps>
    </settings>
    <dataSource name="vendor_entity_form_data_source">
        <argument name="data" xsi:type="array">
            <item name="js_config" xsi:type="array">
                <item name="component" xsi:type="string">Magento_Ui/js/form/provider</item>
            </item>
        </argument>
        <settings>
            <submitUrl path="vendor/entity/save"/>
        </settings>
        <dataProvider class="Vendor\Module\Model\Entity\DataProvider"
                      name="vendor_entity_form_data_source">
            <settings>
                <requestFieldName>entity_id</requestFieldName>
                <primaryFieldName>entity_id</primaryFieldName>
            </settings>
        </dataProvider>
    </dataSource>
    <fieldset name="general">
        <settings>
            <label translate="true">General</label>
        </settings>
        <field name="name" formElement="input">
            <settings>
                <dataType>text</dataType>
                <label translate="true">Name</label>
                <validation>
                    <rule name="required-entry" xsi:type="boolean">true</rule>
                </validation>
            </settings>
        </field>
    </fieldset>
</form>
```

### 6.4 RequireJS & JavaScript

**requirejs-config.js**:
```javascript
var config = {
    map: {
        '*': {
            'customModule': 'Vendor_Module/js/custom-module'
        }
    },
    paths: {
        'externalLib': 'https://cdn.example.com/lib'
    },
    shim: {
        'externalLib': {
            exports: 'ExternalLib'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Vendor_Module/js/view/shipping-mixin': true
            }
        }
    }
};
```

**JavaScript Module**:
```javascript
define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function ($, urlBuilder, alert) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function () {
            $.ajax({
                url: urlBuilder.build('vendor/ajax/action'),
                type: 'POST',
                dataType: 'json',
                data: { id: config.entityId },
                success: function (response) {
                    alert({ content: response.message });
                }
            });
        });
    };
});
```

**JavaScript Mixin**:
```javascript
define([
    'jquery'
], function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();
                // Custom initialization
            },

            customMethod: function () {
                // Additional functionality
            }
        });
    };
});
```

### 6.5 KnockoutJS Templates

**Template** (web/template/custom.html):
```html
<div class="custom-component" data-bind="scope: 'customComponent'">
    <!-- ko template: getTemplate() --><!-- /ko -->
</div>

<script type="text/x-magento-init">
{
    "*": {
        "Magento_Ui/js/core/app": {
            "components": {
                "customComponent": {
                    "component": "Vendor_Module/js/view/custom",
                    "template": "Vendor_Module/custom-content"
                }
            }
        }
    }
}
</script>
```

**KnockoutJS Component**:
```javascript
define([
    'uiComponent',
    'ko'
], function (Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vendor_Module/custom-content',
            items: []
        },

        initialize: function () {
            this._super();
            this.items = ko.observableArray([]);
            return this;
        },

        addItem: function (item) {
            this.items.push(item);
        }
    });
});
```

### 6.6 Theme Inheritance

**Theme Registration** (theme.xml):
```xml
<theme xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/theme.xsd">
    <title>Custom Theme</title>
    <parent>Magento/luma</parent>
</theme>
```

**Theme Structure**:
```
app/design/frontend/Vendor/theme/
├── registration.php
├── theme.xml
├── composer.json
├── etc/
│   └── view.xml
├── media/
│   └── preview.jpg
├── web/
│   ├── css/
│   │   └── source/
│   │       └── _extend.less
│   ├── images/
│   └── js/
├── Magento_Theme/
│   ├── layout/
│   │   └── default.xml
│   └── templates/
│       └── html/
│           └── header.phtml
└── Magento_Catalog/
    ├── layout/
    └── templates/
```

---

## 7. Hyvä Theme (Modern Frontend Alternative)

> **Hyvä** is a modern, high-performance frontend theme for Magento 2 that replaces Luma's RequireJS/KnockoutJS stack with **Alpine.js** and **Tailwind CSS**.
>
> - **Website**: https://www.hyva.io/
> - **Documentation**: https://docs.hyva.io/
> - **GitHub**: https://github.com/hyva-themes
> - **License**: Open Software License (OSL 3.0) - Free to use

### 7.1 Hyvä vs Luma Comparison

| Aspect | Luma (Default) | Hyvä |
|--------|----------------|------|
| JavaScript Framework | RequireJS + KnockoutJS + jQuery | Alpine.js (lightweight) |
| CSS Framework | LESS compilation | Tailwind CSS (utility-first) |
| Bundle Size | ~300KB+ JS | ~30KB JS |
| Page Speed | Moderate | Excellent (90+ Lighthouse) |
| Learning Curve | Steep (legacy patterns) | Modern, intuitive |
| Third-Party Modules | Works out of box | Needs compatibility modules |
| GraphQL | Optional | Primary data fetching method |

### 7.2 Installation

**Requirements**:
- Magento 2.4.4-p9 or higher
- PHP 8.1, 8.2, 8.3, or 8.4
- Node.js ≥ 20.0.0 (for Tailwind compilation)

**Installation Steps**:
```bash
# Step 1: Get free license key from hyva.io and configure composer
composer config --auth http-basic.hyva-themes.repo.packagist.com token YOUR_LICENSE_KEY
composer config repositories.private-packagist composer https://hyva-themes.repo.packagist.com/YOUR_PROJECT/

# Step 2: Install the theme
composer require hyva-themes/magento2-default-theme

# Step 3: Run setup
bin/magento setup:upgrade

# Step 4: Activate theme in Admin
# Admin > Content > Design > Configuration > Select "Hyva/default"

# Step 5: Deploy static content (production mode)
bin/magento setup:static-content:deploy
```

**Alternative: Install from GitHub** (no license key required):
```bash
composer config repositories.hyva-themes/magento2-theme-module git https://github.com/hyva-themes/magento2-theme-module.git
composer config repositories.hyva-themes/magento2-default-theme git https://github.com/hyva-themes/magento2-default-theme.git
composer require hyva-themes/magento2-default-theme
```

### 7.3 Theme Structure

```
app/design/frontend/Hyva/default/
├── registration.php
├── theme.xml
├── composer.json
├── etc/
│   └── view.xml
├── Magento_Theme/
│   ├── layout/
│   │   └── default.xml
│   ├── templates/
│   │   └── html/
│   │       ├── header.phtml
│   │       └── footer.phtml
│   └── page_layout/
├── web/
│   ├── css/
│   │   └── styles.css          # Compiled Tailwind output
│   ├── tailwind/
│   │   ├── tailwind-source.css # Tailwind source with @apply
│   │   └── tailwind.config.js  # Tailwind configuration
│   └── js/
└── Magento_Catalog/
    ├── layout/
    └── templates/
```

### 7.4 Creating a Child Theme

**theme.xml**:
```xml
<theme xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/theme.xsd">
    <title>My Hyvä Child Theme</title>
    <parent>Hyva/default</parent>
</theme>
```

**Directory Structure**:
```
app/design/frontend/Vendor/hyva-child/
├── registration.php
├── theme.xml
├── composer.json
├── web/
│   └── tailwind/
│       ├── tailwind-source.css
│       └── tailwind.config.js
└── Magento_Theme/
    └── templates/
```

### 7.5 Tailwind CSS

**Build Process**:
```bash
# Navigate to theme directory
cd app/design/frontend/Vendor/hyva-child/web/tailwind

# Install dependencies
npm install

# Development (watch mode)
npm run watch

# Production build
npm run build-prod
```

**tailwind.config.js**:
```javascript
const { theme } = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

module.exports = {
    content: [
        // Scan these paths for Tailwind classes
        '../../**/*.phtml',
        '../../../Hyva/default/**/*.phtml',
        '../../../../code/**/*.phtml',
    ],
    theme: {
        extend: {
            colors: {
                primary: colors.blue,
                secondary: colors.gray,
                accent: colors.amber,
            },
            fontFamily: {
                sans: ['Inter', ...theme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

**tailwind-source.css**:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
    .btn-primary {
        @apply px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition;
    }

    .card {
        @apply bg-white rounded-lg shadow-md p-6;
    }
}
```

### 7.6 Alpine.js Patterns

**Basic Component** (replacing KnockoutJS):
```html
<!-- In .phtml template -->
<div x-data="initProductGallery()" x-init="init()">
    <div class="main-image">
        <img :src="activeImage" :alt="activeAlt" />
    </div>
    <div class="thumbnails flex gap-2">
        <template x-for="(image, index) in images" :key="index">
            <img :src="image.thumb"
                 :class="{ 'ring-2 ring-primary-500': activeIndex === index }"
                 @click="setActive(index)"
                 class="w-16 h-16 cursor-pointer" />
        </template>
    </div>
</div>

<script>
function initProductGallery() {
    return {
        images: <?= $block->getGalleryImagesJson() ?>,
        activeIndex: 0,
        get activeImage() {
            return this.images[this.activeIndex]?.full || '';
        },
        get activeAlt() {
            return this.images[this.activeIndex]?.alt || '';
        },
        setActive(index) {
            this.activeIndex = index;
        },
        init() {
            // Initialization logic
        }
    }
}
</script>
```

**Add to Cart with Alpine.js**:
```html
<div x-data="initAddToCart()">
    <form @submit.prevent="addToCart">
        <input type="number" x-model="qty" min="1" class="w-20 border rounded px-2 py-1" />
        <button type="submit"
                :disabled="isLoading"
                class="btn-primary">
            <span x-show="!isLoading">Add to Cart</span>
            <span x-show="isLoading">Adding...</span>
        </button>
    </form>
    <div x-show="message" x-text="message" class="mt-2 text-green-600"></div>
</div>

<script>
function initAddToCart() {
    return {
        qty: 1,
        isLoading: false,
        message: '',
        async addToCart() {
            this.isLoading = true;
            try {
                const response = await fetch('/rest/V1/carts/mine/items', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cartItem: {
                            sku: '<?= $block->getProduct()->getSku() ?>',
                            qty: this.qty
                        }
                    })
                });
                this.message = 'Added to cart!';
                window.dispatchEvent(new CustomEvent('reload-customer-section-data'));
            } catch (error) {
                this.message = 'Error adding to cart';
            }
            this.isLoading = false;
        }
    }
}
</script>
```

**Global Alpine Store** (for shared state like cart):
```javascript
// In a layout XML or head block
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        total: 0,
        async refresh() {
            const response = await fetch('/customer/section/load/?sections=cart');
            const data = await response.json();
            this.items = data.cart?.items || [];
            this.total = data.cart?.subtotal || 0;
        }
    });
});
</script>

<!-- Usage in components -->
<span x-data x-text="$store.cart.items.length"></span>
```

### 7.7 View Models

Hyvä uses View Models extensively (preferred over Block classes):

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductData implements ArgumentInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function getProductBySku(string $sku): ?ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function getFormattedPrice(ProductInterface $product): string
    {
        return '$' . number_format((float) $product->getPrice(), 2);
    }
}
```

**Layout XML**:
```xml
<block name="product.data"
       template="Vendor_Module::product/info.phtml">
    <arguments>
        <argument name="view_model" xsi:type="object">Vendor\Module\ViewModel\ProductData</argument>
    </arguments>
</block>
```

**Template**:
```php
<?php
/** @var \Magento\Framework\View\Element\Template $block */
/** @var \Vendor\Module\ViewModel\ProductData $viewModel */
$viewModel = $block->getData('view_model');
$product = $viewModel->getProductBySku('SKU123');
?>
<div class="product-info">
    <h2 class="text-2xl font-bold"><?= $escaper->escapeHtml($product->getName()) ?></h2>
    <p class="text-xl text-primary-600"><?= $viewModel->getFormattedPrice($product) ?></p>
</div>
```

### 7.8 Compatibility Modules

**Why Needed**: Third-party modules built for Luma use RequireJS/KnockoutJS which don't work in Hyvä.

**Finding Compatibility Modules**:
- Hyvä Module Tracker: https://gitlab.hyva.io/hyva-public/module-tracker
- Many available via Hyvä's private packagist

**Common Compatibility Modules**:

| Module | Hyvä Compat Package |
|--------|---------------------|
| Amasty | `hyva-themes/magento2-amasty-*` |
| Mirasvit | `hyva-themes/magento2-mirasvit-*` |
| Smile ElasticSuite | `hyva-themes/magento2-smile-elasticsuite` |
| Mageplaza | Various community modules |
| Payment Gateways | Check module tracker |

**Registering Custom Module** (hyva-themes.json):
```json
{
    "Vendor_Module": {
        "src": "app/code/Vendor/Module"
    }
}
```

### 7.9 GraphQL Usage

Hyvä heavily uses GraphQL for data fetching:

```html
<div x-data="initProductList()" x-init="fetchProducts()">
    <template x-for="product in products" :key="product.sku">
        <div class="product-card">
            <img :src="product.small_image.url" :alt="product.name" />
            <h3 x-text="product.name"></h3>
            <span x-text="product.price_range.minimum_price.final_price.value"></span>
        </div>
    </template>
</div>

<script>
function initProductList() {
    return {
        products: [],
        async fetchProducts() {
            const query = `{
                products(filter: { category_id: { eq: "10" } }, pageSize: 12) {
                    items {
                        sku
                        name
                        small_image { url }
                        price_range {
                            minimum_price {
                                final_price { value currency }
                            }
                        }
                    }
                }
            }`;

            const response = await fetch('/graphql', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query })
            });

            const data = await response.json();
            this.products = data.data.products.items;
        }
    }
}
</script>
```

### 7.10 Required Configuration

**Disable Incompatible Features**:

| Setting | Recommendation |
|---------|----------------|
| JS Bundling | Disable (not needed with Alpine) |
| JS Minification | Disable (use Tailwind's purge) |
| CSS Minification | Disable (handled by Tailwind build) |
| HTML Minification | Optional |
| Captcha | Disable default, use Google reCAPTCHA |

**Required GraphQL Modules** (must be enabled):
- `Magento_BundleGraphQl`
- `Magento_CatalogGraphQl`
- `Magento_CatalogInventoryGraphQl`
- `Magento_CmsGraphQl`
- `Magento_ConfigurableProductGraphQl`
- `Magento_CustomerGraphQl`
- `Magento_DirectoryGraphQl`
- `Magento_DownloadableGraphQl`
- `Magento_GroupedProductGraphQl`
- `Magento_QuoteGraphQl`
- `Magento_ReviewGraphQl`
- `Magento_SalesGraphQl`
- `Magento_StoreGraphQl`
- `Magento_SwatchesGraphQl`
- `Magento_TaxGraphQl`
- `Magento_UrlRewriteGraphQl`
- `Magento_WishlistGraphQl`

### 7.11 Hyvä Best Practices

| Practice | Description |
|----------|-------------|
| Use View Models | Prefer over Block classes for data |
| Alpine.js stores | Share state across components |
| Tailwind utilities | Prefer utilities over custom CSS |
| GraphQL for data | Use for dynamic content loading |
| Lazy loading | Load components/images on demand |
| SVG icons | Use Heroicons (included) over font icons |
| Purge unused CSS | Configure content paths in tailwind.config.js |
| Child themes | Always extend, never modify default theme |

### 7.12 Hyvä Product Suite

| Product | Description |
|---------|-------------|
| **Hyvä Theme** | Core theme (free, open source) |
| **Hyvä Checkout** | Optimized React-based checkout (paid) |
| **Hyvä Commerce** | Full commerce platform solution |
| **Hyvä Enterprise** | Adobe Commerce compatibility |
| **Hyvä UI** | Component library |
| **Hyvä Admin** | Admin theme improvements |

---

## 8. API Patterns

### 8.1 REST API

**Endpoints Structure**:

| URL Pattern | Scope |
|-------------|-------|
| `https://magento.test/rest/V1/` | Default store |
| `https://magento.test/rest/{store_code}/V1/` | Specific store |
| `https://magento.test/rest/all/V1/` | All stores |

**webapi.xml Configuration**:
```xml
<?xml version="1.0"?>
<routes xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Webapi:etc/webapi.xsd">

    <!-- Admin authenticated endpoint -->
    <route url="/V1/vendor/entities" method="GET">
        <service class="Vendor\Module\Api\EntityRepositoryInterface" method="getList"/>
        <resources>
            <resource ref="Vendor_Module::entity_view"/>
        </resources>
    </route>

    <route url="/V1/vendor/entities/:id" method="GET">
        <service class="Vendor\Module\Api\EntityRepositoryInterface" method="get"/>
        <resources>
            <resource ref="Vendor_Module::entity_view"/>
        </resources>
    </route>

    <route url="/V1/vendor/entities" method="POST">
        <service class="Vendor\Module\Api\EntityRepositoryInterface" method="save"/>
        <resources>
            <resource ref="Vendor_Module::entity_save"/>
        </resources>
    </route>

    <route url="/V1/vendor/entities/:id" method="PUT">
        <service class="Vendor\Module\Api\EntityRepositoryInterface" method="save"/>
        <resources>
            <resource ref="Vendor_Module::entity_save"/>
        </resources>
    </route>

    <route url="/V1/vendor/entities/:id" method="DELETE">
        <service class="Vendor\Module\Api\EntityRepositoryInterface" method="deleteById"/>
        <resources>
            <resource ref="Vendor_Module::entity_delete"/>
        </resources>
    </route>

    <!-- Anonymous access (no auth required) -->
    <route url="/V1/vendor/public" method="GET">
        <service class="Vendor\Module\Api\PublicInterface" method="getData"/>
        <resources>
            <resource ref="anonymous"/>
        </resources>
    </route>

    <!-- Customer self-service (customer token required) -->
    <route url="/V1/vendor/customer/me" method="GET">
        <service class="Vendor\Module\Api\CustomerDataInterface" method="getMyData"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>
</routes>
```

**Authentication Types**:

| Type | Resource Ref | How to Authenticate |
|------|--------------|---------------------|
| Admin Integration | `Vendor_Module::resource` | Bearer token from integration or admin token |
| Customer | `self` | Bearer token from customer login |
| Anonymous | `anonymous` | No authentication needed |

**Token Generation**:

```bash
# Admin token (expires in 4 hours by default)
curl -X POST "https://magento.test/rest/V1/integration/admin/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Customer token
curl -X POST "https://magento.test/rest/V1/integration/customer/token" \
  -H "Content-Type: application/json" \
  -d '{"username":"customer@example.com","password":"password123"}'

# Using token in requests
curl -X GET "https://magento.test/rest/V1/products/SKU123" \
  -H "Authorization: Bearer {token}"
```

**Pagination & Filtering (SearchCriteria)**:

```bash
# Pagination
GET /V1/products?searchCriteria[pageSize]=20&searchCriteria[currentPage]=1

# Filtering (eq, neq, like, in, nin, gt, lt, gteq, lteq)
GET /V1/products?searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=eq

# Sorting
GET /V1/products?searchCriteria[sortOrders][0][field]=created_at&searchCriteria[sortOrders][0][direction]=DESC

# Combined example
GET /V1/products?searchCriteria[filter_groups][0][filters][0][field]=type_id&searchCriteria[filter_groups][0][filters][0][value]=simple&searchCriteria[pageSize]=10&searchCriteria[currentPage]=1&searchCriteria[sortOrders][0][field]=name&searchCriteria[sortOrders][0][direction]=ASC
```

**REST API Best Practices**:

| Practice | Description |
|----------|-------------|
| Use service contracts | Interface methods in `Api/` directory |
| Proper HTTP methods | GET (read), POST (create), PUT (update), DELETE (remove) |
| Versioning | Always use `/V1/` prefix for versioning |
| Error handling | Return proper HTTP status codes (400, 401, 403, 404, 500) |
| Rate limiting | Configure in `env.php` or use WAF |
| Input validation | Validate in service layer, not controller |

### 8.2 SOAP API

**WSDL Endpoints**:
- Single store: `https://magento.test/soap/default?wsdl&services=<service>`
- Specific store: `https://magento.test/soap/{store_code}?wsdl&services=<service>`
- List all services: `https://magento.test/soap/default?wsdl_list=1`

**Service Naming Convention**:
- Format: `{moduleName}{ServiceName}V{version}`
- Example: `catalogProductRepositoryV1`

**SOAP Request Example**:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
              xmlns:ns1="http://magento.test/soap/default?services=catalogProductRepositoryV1">
    <env:Header>
        <ns1:AuthHeader>
            <ns1:token>admin_token_here</ns1:token>
        </ns1:AuthHeader>
    </env:Header>
    <env:Body>
        <ns1:catalogProductRepositoryV1GetRequest>
            <sku>SKU123</sku>
        </ns1:catalogProductRepositoryV1GetRequest>
    </env:Body>
</env:Envelope>
```

**PHP SOAP Client**:
```php
<?php
$opts = ['http' => ['header' => "Authorization: Bearer {$token}"]];
$context = stream_context_create($opts);

$client = new SoapClient(
    'https://magento.test/soap/default?wsdl&services=catalogProductRepositoryV1',
    [
        'stream_context' => $context,
        'soap_version' => SOAP_1_2
    ]
);

$result = $client->catalogProductRepositoryV1Get(['sku' => 'SKU123']);
```

**SOAP vs REST Comparison**:

| Aspect | REST | SOAP |
|--------|------|------|
| Format | JSON | XML |
| Performance | Faster | Slower (XML overhead) |
| Caching | Easy (HTTP cache) | Difficult |
| Use case | Modern integrations | Legacy systems, B2B |
| Authentication | Bearer token, OAuth | WS-Security, token |

### 8.3 GraphQL

**Endpoint**: `https://magento.test/graphql`

**Schema Declaration** (etc/schema.graphqls):
```graphql
type Query {
    vendorEntity(id: Int! @doc(description: "Entity ID")): VendorEntity
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\Entity")
        @doc(description: "Get entity by ID")
        @cache(cacheIdentity: "Vendor\\Module\\Model\\Resolver\\Entity\\Identity")

    vendorEntities(
        filter: VendorEntityFilterInput @doc(description: "Filter criteria")
        pageSize: Int = 20 @doc(description: "Page size")
        currentPage: Int = 1 @doc(description: "Current page")
    ): VendorEntities
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\Entities")
        @doc(description: "Get entities list")
}

type Mutation {
    createVendorEntity(input: VendorEntityInput!): VendorEntity
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\CreateEntity")
        @doc(description: "Create new entity")

    updateVendorEntity(id: Int!, input: VendorEntityInput!): VendorEntity
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\UpdateEntity")
        @doc(description: "Update entity")

    deleteVendorEntity(id: Int!): Boolean
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\DeleteEntity")
        @doc(description: "Delete entity")
}

type VendorEntity @doc(description: "Vendor entity") {
    id: Int @doc(description: "Entity ID")
    name: String @doc(description: "Entity name")
    status: Boolean @doc(description: "Entity status")
    created_at: String @doc(description: "Creation date")
    related_products: [ProductInterface]
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\RelatedProducts")
        @doc(description: "Related products")
}

type VendorEntities @doc(description: "Entity collection") {
    items: [VendorEntity] @doc(description: "Array of entities")
    total_count: Int @doc(description: "Total count")
    page_info: SearchResultPageInfo @doc(description: "Pagination info")
}

input VendorEntityInput @doc(description: "Entity input") {
    name: String! @doc(description: "Entity name")
    status: Boolean @doc(description: "Entity status")
}

input VendorEntityFilterInput @doc(description: "Filter input") {
    id: FilterEqualTypeInput @doc(description: "Filter by ID")
    name: FilterMatchTypeInput @doc(description: "Filter by name")
    or: VendorEntityFilterInput @doc(description: "OR condition")
}

# Extend existing types
extend type Customer {
    vendor_entities: [VendorEntity]
        @resolver(class: "Vendor\\Module\\Model\\Resolver\\CustomerEntities")
        @doc(description: "Customer's entities")
}
```

**GraphQL Resolver with Authorization**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vendor\Module\Api\EntityRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Entity implements ResolverInterface
{
    public function __construct(
        private readonly EntityRepositoryInterface $repository
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        // Authorization check
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('Customer is not authorized.'));
        }

        // Input validation
        if (!isset($args['id']) || $args['id'] <= 0) {
            throw new GraphQlInputException(__('Invalid entity ID.'));
        }

        try {
            $entity = $this->repository->get((int) $args['id']);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Entity not found.'));
        }

        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'status' => $entity->getStatus(),
            'created_at' => $entity->getCreatedAt(),
            'model' => $entity  // Pass model for child resolvers
        ];
    }
}
```

**GraphQL Cache Identity**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\Resolver\Entity;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Vendor\Module\Model\Entity;

class Identity implements IdentityInterface
{
    private string $cacheTag = Entity::CACHE_TAG;

    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        if (isset($resolvedData['id'])) {
            $ids[] = sprintf('%s_%s', $this->cacheTag, $resolvedData['id']);
        }
        return $ids;
    }
}
```

**GraphQL DataProvider for Batch Loading**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchServiceContractResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestInterface;

class BatchEntity implements BatchServiceContractResolverInterface
{
    public function getServiceContract(): array
    {
        return [EntityRepositoryInterface::class, 'getList'];
    }

    public function convertToServiceArgument(ResolveRequestInterface $request): array
    {
        $args = $request->getArgs();
        return ['searchCriteria' => $this->buildSearchCriteria($args)];
    }

    public function convertFromServiceResult($result, ResolveRequestInterface $request): array
    {
        // Transform service result to GraphQL format
        return $result->getItems();
    }
}
```

**GraphQL Authentication**:
```bash
# Get customer token
mutation {
  generateCustomerToken(email: "customer@example.com", password: "password") {
    token
  }
}

# Use in header
Authorization: Bearer <customer_token>

# Check authentication in resolver
$customerId = $context->getUserId();
$isCustomer = $context->getExtensionAttributes()->getIsCustomer();
```

**GraphQL Best Practices**:

| Practice | Description |
|----------|-------------|
| Use DataProviders | Batch queries to avoid N+1 problems |
| Implement caching | Use `@cache` directive and IdentityInterface |
| Proper error handling | Use specific GraphQL exceptions |
| Field-level resolvers | Lazy load expensive relations |
| Input validation | Validate in resolver before service call |
| Authorization | Check context for customer/admin access |
| Schema documentation | Use `@doc` directive extensively |

**Common GraphQL Queries**:
```graphql
# Products with filters
query {
  products(
    filter: { sku: { like: "WSH%" } }
    pageSize: 10
    currentPage: 1
    sort: { price: DESC }
  ) {
    items {
      sku
      name
      price_range { minimum_price { regular_price { value currency } } }
    }
    total_count
    page_info { current_page page_size total_pages }
  }
}

# Cart operations
mutation {
  createEmptyCart
}

mutation {
  addProductsToCart(
    cartId: "{ CART_ID }"
    cartItems: [{ quantity: 1, sku: "SKU123" }]
  ) {
    cart { items { quantity product { sku name } } }
  }
}
```

---

## 9. Database Patterns

### 9.1 Declarative Schema (db_schema.xml)

```xml
<?xml version="1.0"?>
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">

    <table name="vendor_entity" resource="default" engine="innodb" comment="Vendor Entity Table">
        <column xsi:type="int" name="entity_id" unsigned="true" nullable="false" identity="true"
                comment="Entity ID"/>
        <column xsi:type="varchar" name="name" nullable="false" length="255" comment="Name"/>
        <column xsi:type="text" name="description" nullable="true" comment="Description"/>
        <column xsi:type="smallint" name="status" unsigned="true" nullable="false" default="1"
                comment="Status"/>
        <column xsi:type="decimal" name="price" precision="12" scale="4" nullable="true"
                comment="Price"/>
        <column xsi:type="int" name="store_id" unsigned="true" nullable="false" default="0"
                comment="Store ID"/>
        <column xsi:type="timestamp" name="created_at" nullable="false" default="CURRENT_TIMESTAMP"
                comment="Created At"/>
        <column xsi:type="timestamp" name="updated_at" nullable="false" default="CURRENT_TIMESTAMP"
                on_update="true" comment="Updated At"/>

        <constraint xsi:type="primary" referenceId="PRIMARY">
            <column name="entity_id"/>
        </constraint>

        <constraint xsi:type="foreign" referenceId="VENDOR_ENTITY_STORE_ID_STORE_STORE_ID"
                    table="vendor_entity" column="store_id"
                    referenceTable="store" referenceColumn="store_id"
                    onDelete="CASCADE"/>

        <constraint xsi:type="unique" referenceId="VENDOR_ENTITY_NAME_STORE_ID">
            <column name="name"/>
            <column name="store_id"/>
        </constraint>

        <index referenceId="VENDOR_ENTITY_STATUS" indexType="btree">
            <column name="status"/>
        </index>

        <index referenceId="VENDOR_ENTITY_NAME" indexType="fulltext">
            <column name="name"/>
            <column name="description"/>
        </index>
    </table>
</schema>
```

**Generate Whitelist** (after schema changes):
```bash
bin/magento setup:db-declaration:generate-whitelist --module-name=Vendor_Module
```

### 9.1.1 Database Index Strategy for Large Catalogs

**When to Add Custom Indexes**:

| Scenario | Action |
|----------|--------|
| Queries filtering on non-indexed columns | Add single-column index |
| Multi-column WHERE clauses | Add composite index (most selective column first) |
| Slow admin grids | Check for missing indexes on filter/sort columns |
| JOIN operations on custom tables | Ensure foreign keys have indexes |

**Index Recommendations for Scale (50k+ Products)**:

| Table | Index | Purpose |
|-------|-------|---------|
| `catalog_product_entity` | `(type_id, attribute_set_id)` | Product type filtering |
| `cataloginventory_stock_status` | `(website_id, stock_id, stock_status)` | In-stock queries |
| Custom tables | Always index foreign keys | JOIN performance |

**Index Impact on Write Performance**:

| Factor | Impact |
|--------|--------|
| Each additional index | Slower INSERT/UPDATE operations |
| Composite indexes | Higher maintenance cost than single-column |
| FULLTEXT indexes | Significant write overhead |
| Recommendation | Add indexes only when queries prove slow |

**Indexer Mode for Large Catalogs**:
```bash
# Set all indexers to "Update by Schedule" (required for 50k+ products)
bin/magento indexer:set-mode schedule

# Check current modes
bin/magento indexer:status

# Critical for imports: disable realtime indexing
bin/magento indexer:set-mode schedule catalog_product_attribute catalog_product_price cataloginventory_stock
```

> **Production Rule**: Always run indexers in "Update by Schedule" mode for catalogs with 50k+ products. Realtime indexing causes severe performance degradation during imports and bulk updates.

### 9.2 Model/ResourceModel/Collection Pattern

**Model**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

use Magento\Framework\Model\AbstractModel;
use Vendor\Module\Api\Data\EntityInterface;

class Entity extends AbstractModel implements EntityInterface
{
    protected $_eventPrefix = 'vendor_entity';

    protected function _construct(): void
    {
        $this->_init(\Vendor\Module\Model\ResourceModel\Entity::class);
    }

    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID) ? (int) $this->getData(self::ENTITY_ID) : null;
    }

    public function setEntityId(int $id): EntityInterface
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setName(string $name): EntityInterface
    {
        return $this->setData(self::NAME, $name);
    }
}
```

**ResourceModel**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entity extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('vendor_entity', 'entity_id');
    }
}
```

**Collection**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\ResourceModel\Entity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\Module\Model\Entity;
use Vendor\Module\Model\ResourceModel\Entity as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(Entity::class, ResourceModel::class);
    }
}
```

### 9.3 SearchCriteria Usage

```php
<?php
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrderBuilder;

// Build search criteria
$searchCriteria = $this->searchCriteriaBuilder
    ->addFilter('status', 1)
    ->addFilter('store_id', $storeId)
    ->addFilter('name', '%search%', 'like')
    ->setPageSize(20)
    ->setCurrentPage(1)
    ->create();

// Add sort order
$sortOrder = $this->sortOrderBuilder
    ->setField('created_at')
    ->setDirection('DESC')
    ->create();

$searchCriteria = $this->searchCriteriaBuilder
    ->addSortOrder($sortOrder)
    ->create();

// Execute
$result = $this->repository->getList($searchCriteria);
$items = $result->getItems();
$totalCount = $result->getTotalCount();
```

---

## 10. CLI Commands

### Setup Commands

| Command | Purpose |
|---------|---------|
| `bin/magento setup:upgrade` | Run setup scripts, update schema |
| `bin/magento setup:di:compile` | Compile dependency injection |
| `bin/magento setup:static-content:deploy -f` | Deploy static files (-f for developer mode) |
| `bin/magento setup:static-content:deploy -j 4` | Deploy with 4 parallel jobs |
| `bin/magento setup:db:status` | Check if schema/data upgrades needed |
| `bin/magento setup:db-schema:upgrade` | Run schema upgrades only |
| `bin/magento setup:db-data:upgrade` | Run data upgrades only |
| `bin/magento setup:db-declaration:generate-whitelist` | Generate db_schema_whitelist.json |
| `bin/magento setup:config:set --key=value` | Update env.php config |
| `bin/magento setup:store-config:set --base-url="http://..."` | Set store URLs |
| `bin/magento setup:uninstall` | Uninstall Magento (destructive) |

### Cache Commands

| Command | Purpose |
|---------|---------|
| `bin/magento cache:status` | Show cache status |
| `bin/magento cache:enable` | Enable all caches |
| `bin/magento cache:disable` | Disable all caches |
| `bin/magento cache:clean` | Clean (invalidate) cache |
| `bin/magento cache:flush` | Flush (purge) cache storage |
| `bin/magento cache:clean config full_page layout block_html` | Clean specific types |

**Cache Types**:

| Type | Description |
|------|-------------|
| `config` | Configuration |
| `layout` | Layout XML |
| `block_html` | Block HTML output |
| `collections` | Collection data |
| `reflection` | API reflection |
| `db_ddl` | Database schema |
| `eav` | EAV attributes |
| `full_page` | Full page cache |
| `config_integration` | Integration config |
| `config_integration_api` | Integration API config |
| `translate` | Translations |
| `config_webservice` | Web services config |
| `compiled_config` | Compiled config |
| `customer_notification` | Customer notifications |
| `graphql_query_resolver_result` | GraphQL resolvers |

### Indexer Commands

| Command | Purpose |
|---------|---------|
| `bin/magento indexer:status` | Show indexer status |
| `bin/magento indexer:info` | List all indexers |
| `bin/magento indexer:reindex` | Reindex all |
| `bin/magento indexer:reindex catalog_product_price catalog_category_product` | Reindex specific |
| `bin/magento indexer:set-mode realtime` | Set all to real-time |
| `bin/magento indexer:set-mode schedule` | Set all to "Update by Schedule" |
| `bin/magento indexer:set-mode schedule catalog_product_price` | Set specific indexer mode |
| `bin/magento indexer:reset` | Reset all to "invalid" state |
| `bin/magento indexer:show-mode` | Show current modes |
| `bin/magento indexer:set-dimensions-mode catalog_product_price website` | Set dimension mode |

**Indexer Names**:

| Indexer | Description |
|---------|-------------|
| `catalog_category_product` | Category products |
| `catalog_product_category` | Product categories |
| `catalog_product_price` | Product price |
| `catalog_product_attribute` | Product EAV |
| `cataloginventory_stock` | Stock (legacy) |
| `inventory` | MSI inventory |
| `catalogrule_rule` | Catalog rule |
| `catalogrule_product` | Catalog rule products |
| `catalogsearch_fulltext` | Catalog search |
| `design_config_grid` | Design config |
| `customer_grid` | Customer grid |
| `salesrule_rule` | Sales rule |

### Module Commands

| Command | Purpose |
|---------|---------|
| `bin/magento module:status` | List all modules |
| `bin/magento module:status --enabled` | List enabled modules |
| `bin/magento module:status --disabled` | List disabled modules |
| `bin/magento module:enable Vendor_Module` | Enable module |
| `bin/magento module:disable Vendor_Module` | Disable module |
| `bin/magento module:uninstall Vendor_Module` | Uninstall module |
| `bin/magento module:uninstall -r Vendor_Module` | Remove module from codebase |

### Store & Config Commands

| Command | Purpose |
|---------|---------|
| `bin/magento store:list` | List all stores |
| `bin/magento store:website:list` | List all websites |
| `bin/magento config:show` | Show config values |
| `bin/magento config:show catalog/search/engine` | Show specific config |
| `bin/magento config:set path/to/config value` | Set config value |
| `bin/magento config:set --scope=stores --scope-code=default path value` | Set store-specific |
| `bin/magento config:sensitive:set` | Set sensitive config (prompts for value) |
| `bin/magento app:config:dump` | Dump config to config.php |
| `bin/magento app:config:import` | Import config from config.php |

### Admin User Commands

| Command | Purpose |
|---------|---------|
| `bin/magento admin:user:create` | Create admin user (interactive) |
| `bin/magento admin:user:create --admin-user=admin --admin-password=Admin123! --admin-email=admin@example.com --admin-firstname=Admin --admin-lastname=User` | Create with args |
| `bin/magento admin:user:unlock admin` | Unlock locked admin account |
| `bin/magento security:recaptcha:disable-for-user-forgot-password` | Disable reCAPTCHA for forgot password |

### Cron Commands

| Command | Purpose |
|---------|---------|
| `bin/magento cron:install` | Install crontab entries |
| `bin/magento cron:remove` | Remove crontab entries |
| `bin/magento cron:run` | Run cron jobs |
| `bin/magento cron:run --group=default` | Run specific cron group |
| `bin/magento cron:run --group=index` | Run indexer cron group |

### Queue / Message Commands

| Command | Purpose |
|---------|---------|
| `bin/magento queue:consumers:list` | List all consumers |
| `bin/magento queue:consumers:start <consumer>` | Start specific consumer |
| `bin/magento queue:consumers:start async.operations.all` | Start async operations consumer |
| `bin/magento queue:consumers:start --max-messages=100 consumer_name` | Limit messages processed |

### Maintenance Commands

| Command | Purpose |
|---------|---------|
| `bin/magento maintenance:enable` | Enable maintenance mode |
| `bin/magento maintenance:disable` | Disable maintenance mode |
| `bin/magento maintenance:status` | Show status |
| `bin/magento maintenance:enable --ip=192.168.1.1` | Enable with IP exemption |
| `bin/magento maintenance:allow-ips 192.168.1.1 192.168.1.2` | Allow IPs during maintenance |

### Deploy Mode Commands

| Command | Purpose |
|---------|---------|
| `bin/magento deploy:mode:show` | Show current mode |
| `bin/magento deploy:mode:set developer` | Set developer mode |
| `bin/magento deploy:mode:set production` | Set production mode |
| `bin/magento deploy:mode:set production --skip-compilation` | Skip compilation |

### Development Commands

| Command | Purpose |
|---------|---------|
| `bin/magento dev:source-theme:deploy` | Deploy LESS source |
| `bin/magento dev:template-hints:enable` | Enable template hints (storefront) |
| `bin/magento dev:template-hints:disable` | Disable template hints |
| `bin/magento dev:template-hints:enable --type=adminhtml` | Admin template hints |
| `bin/magento dev:query-log:enable` | Enable query logging |
| `bin/magento dev:query-log:disable` | Disable query logging |
| `bin/magento dev:di:info "Class\Name"` | Show DI info for class |
| `bin/magento dev:profiler:enable` | Enable profiler |
| `bin/magento dev:profiler:disable` | Disable profiler |
| `bin/magento dev:urn-catalog:generate .idea/misc.xml` | Generate URNs for IDE |

### i18n Commands

| Command | Purpose |
|---------|---------|
| `bin/magento i18n:collect-phrases -o output.csv` | Collect translation phrases |
| `bin/magento i18n:pack -m merge output.csv en_US` | Create language pack |
| `bin/magento i18n:uninstall en_US` | Uninstall language pack |

### Info Commands

| Command | Purpose |
|---------|---------|
| `bin/magento info:adminuri` | Show admin URI |
| `bin/magento info:backups:list` | List backups |
| `bin/magento info:currency:list` | List currencies |
| `bin/magento info:dependencies:show-modules` | Module dependencies |
| `bin/magento info:dependencies:show-modules-circular` | Circular dependencies |
| `bin/magento info:language:list` | List languages |
| `bin/magento info:timezone:list` | List timezones |

### Customer Commands

| Command | Purpose |
|---------|---------|
| `bin/magento customer:hash:upgrade` | Upgrade password hashes |

### Media Commands

| Command | Purpose |
|---------|---------|
| `bin/magento catalog:images:resize` | Resize catalog images |
| `bin/magento media:dump` | Dump media file references |

### Search Engine Commands

| Command | Purpose |
|---------|---------|
| `bin/magento indexer:reindex catalogsearch_fulltext` | Reindex search |
| `bin/magento config:set catalog/search/engine opensearch` | Set search engine |

### Encryption Commands

| Command | Purpose |
|---------|---------|
| `bin/magento encryption:payment-data:update` | Re-encrypt payment data |

---

### Creating Custom CLI Commands

**Basic Command Structure**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class AdvancedCommand extends Command
{
    private const ARG_ID = 'id';
    private const OPT_DRY_RUN = 'dry-run';
    private const OPT_LIMIT = 'limit';

    public function __construct(
        private readonly \Vendor\Module\Model\Processor $processor,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('vendor:process:items')
            ->setDescription('Process items with various options')
            ->setHelp('This command processes items. Use --dry-run to preview changes.')
            ->addArgument(
                self::ARG_ID,
                InputArgument::OPTIONAL,
                'Specific item ID to process'
            )
            ->addOption(
                self::OPT_DRY_RUN,
                'd',
                InputOption::VALUE_NONE,
                'Preview without making changes'
            )
            ->addOption(
                self::OPT_LIMIT,
                'l',
                InputOption::VALUE_REQUIRED,
                'Limit number of items to process',
                100 // default value
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $itemId = $input->getArgument(self::ARG_ID);
        $dryRun = $input->getOption(self::OPT_DRY_RUN);
        $limit = (int) $input->getOption(self::OPT_LIMIT);

        // Colored output
        $output->writeln('<info>Starting process...</info>');
        $output->writeln('<comment>Warning: This may take a while</comment>');
        $output->writeln('<error>Error messages look like this</error>');

        if ($dryRun) {
            $output->writeln('<comment>DRY RUN - No changes will be made</comment>');
        }

        // Interactive confirmation
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue? [y/N] ', false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        // Progress bar
        $items = $this->processor->getItems($limit);
        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $results = [];
        foreach ($items as $item) {
            $result = $this->processor->process($item, $dryRun);
            $results[] = ['id' => $item->getId(), 'status' => $result ? 'OK' : 'FAIL'];
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(''); // New line after progress bar

        // Table output
        $table = new Table($output);
        $table->setHeaders(['ID', 'Status']);
        $table->setRows($results);
        $table->render();

        $output->writeln('<info>Process complete!</info>');

        return Command::SUCCESS;
    }
}
```

**Command with Area Code (for Admin/Frontend context)**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AreaAwareCommand extends Command
{
    public function __construct(
        private readonly State $state,
        private readonly \Vendor\Module\Model\Service $service,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area code already set
        }

        $this->service->execute();

        return Command::SUCCESS;
    }
}
```

**Registration** (etc/di.xml):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Framework\Console\CommandList">
        <arguments>
            <argument name="commands" xsi:type="array">
                <item name="vendor_process_items" xsi:type="object">
                    Vendor\Module\Console\Command\AdvancedCommand
                </item>
                <item name="vendor_area_aware" xsi:type="object">
                    Vendor\Module\Console\Command\AreaAwareCommand
                </item>
            </argument>
        </arguments>
    </type>
</config>
```

**Command Best Practices**:

| Practice | Description |
|----------|-------------|
| Set area code | Required for store-aware operations |
| Use dependency injection | Inject services via constructor |
| Return proper exit codes | `Command::SUCCESS`, `Command::FAILURE`, `Command::INVALID` |
| Add help text | Use `setHelp()` for detailed usage |
| Support dry-run | Allow previewing changes |
| Progress feedback | Use progress bars for long operations |
| Colored output | Use `<info>`, `<comment>`, `<error>` tags |
| Confirmation prompts | Ask before destructive operations |

---

## 11. Infrastructure Services

### 11.1 Redis

**Purpose**: Cache backend, session storage, page cache.

**env.php Configuration**:
```php
'cache' => [
    'frontend' => [
        'default' => [
            'id_prefix' => 'site1_',
            'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
            'backend_options' => [
                'server' => 'redis',
                'port' => '6379',
                'database' => '0',
                'password' => '',
                'compress_data' => '1',
                'compression_lib' => 'gzip',
                'preload_keys' => [
                    'EAV_ENTITY_TYPES',
                    'GLOBAL_PLUGIN_LIST',
                    'DB_IS_UP_TO_DATE',
                    'SYSTEM_DEFAULT'
                ]
            ]
        ],
        'page_cache' => [
            'id_prefix' => 'site1_',
            'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
            'backend_options' => [
                'server' => 'redis',
                'port' => '6379',
                'database' => '1',
                'password' => '',
                'compress_data' => '0'
            ]
        ]
    ],
    'allow_parallel_generation' => false
],
'session' => [
    'save' => 'redis',
    'redis' => [
        'host' => 'redis',
        'port' => '6379',
        'password' => '',
        'timeout' => '2.5',
        'persistent_identifier' => '',
        'database' => '2',
        'compression_threshold' => '2048',
        'compression_library' => 'gzip',
        'log_level' => '4',
        'max_concurrency' => '6',
        'break_after_frontend' => '5',
        'break_after_adminhtml' => '30',
        'first_lifetime' => '600',
        'bot_first_lifetime' => '60',
        'bot_lifetime' => '7200',
        'disable_locking' => '0',
        'min_lifetime' => '60',
        'max_lifetime' => '2592000'
    ]
]
```

**Redis CLI Commands**:
```bash
# Connect to Redis
redis-cli -h redis -p 6379

# Monitor commands in real-time
redis-cli monitor

# Check memory usage
redis-cli info memory

# List all keys with pattern
redis-cli keys "site1_*" | head -20

# Flush specific database
redis-cli -n 0 flushdb

# Flush all databases (careful!)
redis-cli flushall

# Check key TTL
redis-cli ttl "key_name"

# Get key type
redis-cli type "key_name"
```

**Redis Best Practices**:

| Practice | Description |
|----------|-------------|
| Separate databases | Use different databases for cache (0), FPC (1), sessions (2) |
| Set `id_prefix` | Unique prefix per environment to avoid conflicts |
| Enable compression | Set `compress_data` for large caches |
| Tune timeouts | Adjust `timeout` based on network latency |
| Monitor memory | Set `maxmemory` and `maxmemory-policy` |
| Use persistence | Enable RDB/AOF for session data durability |
| Cluster mode | Use Redis Cluster or Sentinel for HA |
| Separate instances | Use dedicated Redis for sessions vs cache in production |

**Redis Memory Policies**:

| Policy | Description | Use Case |
|--------|-------------|----------|
| `volatile-lru` | Evict keys with TTL using LRU | Default cache |
| `allkeys-lru` | Evict any key using LRU | Cache without TTL |
| `volatile-lfu` | Evict keys with TTL using LFU | Frequency-based cache |
| `noeviction` | Return error when full | Sessions (critical data) |

**Redis Configuration (redis.conf)** - Self-Hosted Only:
```conf
# Memory limit
maxmemory 2gb
maxmemory-policy volatile-lru

# Persistence
save 900 1
save 300 10
save 60 10000
appendonly yes
appendfsync everysec

# Performance
tcp-keepalive 300
timeout 0
```

**Cloud-Managed Redis (AWS ElastiCache / Azure Cache)**:

| Setting | Self-Hosted | Cloud-Managed |
|---------|-------------|---------------|
| `maxmemory` | Configure in redis.conf | Managed by service tier |
| `maxmemory-policy` | Configure in redis.conf | Set via cloud console |
| Persistence (RDB/AOF) | Configure in redis.conf | Managed via console |
| Connection | `IP:port` | FQDN endpoint (e.g., `cache.xxx.amazonaws.com:6379`) |
| Cluster mode | Manual setup | Requires Redis Cluster client mode |
| SSL/TLS | Optional configuration | Often required/default |

> **Note**: When using cloud-managed Redis, the `server` parameter in `env.php` should use the FQDN endpoint provided by the service, not an IP address.

### 11.2 RabbitMQ (Message Queue)

**Purpose**: Async operations, bulk processing, decoupled architecture.

**env.php Configuration**:
```php
'queue' => [
    'amqp' => [
        'host' => 'rabbitmq',
        'port' => '5672',
        'user' => 'magento',
        'password' => 'magento',
        'virtualhost' => '/',
        'ssl' => false,
        'ssl_options' => [
            'cafile' => '/path/to/ca.pem',
            'certfile' => '/path/to/cert.pem',
            'keyfile' => '/path/to/key.pem'
        ]
    ],
    'consumers_wait_for_messages' => 1
]
```

**Queue Configuration Files**:

| File | Purpose |
|------|---------|
| `etc/communication.xml` | Define topics and handlers |
| `etc/queue_topology.xml` | Define exchanges and bindings |
| `etc/queue_consumer.xml` | Define consumers |
| `etc/queue_publisher.xml` | Define publishers |

**communication.xml** (Define topics):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Communication/etc/communication.xsd">
    <topic name="vendor.module.process" request="Vendor\Module\Api\Data\MessageInterface">
        <handler name="vendorModuleProcessor" type="Vendor\Module\Model\Consumer" method="process"/>
    </topic>
</config>
```

**queue_topology.xml** (Define exchanges):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
    <exchange name="magento-topic-based-exchange" type="topic" connection="amqp">
        <binding id="vendorModuleProcessBinding" topic="vendor.module.process"
                 destinationType="queue" destination="vendor.module.process.queue"/>
    </exchange>
</config>
```

**queue_consumer.xml** (Define consumers):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
    <consumer name="vendor.module.process.consumer"
              queue="vendor.module.process.queue"
              handler="Vendor\Module\Model\Consumer::process"
              consumerInstance="Magento\Framework\MessageQueue\Consumer"
              connection="amqp"
              maxMessages="1000"/>
</config>
```

**Publisher Class**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

use Magento\Framework\MessageQueue\PublisherInterface;

class Publisher
{
    private const TOPIC_NAME = 'vendor.module.process';

    public function __construct(
        private readonly PublisherInterface $publisher
    ) {
    }

    public function publish(array $data): void
    {
        $this->publisher->publish(self::TOPIC_NAME, json_encode($data));
    }
}
```

**Consumer Class**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

use Psr\Log\LoggerInterface;

class Consumer
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Processor $processor
    ) {
    }

    public function process(string $message): void
    {
        try {
            $data = json_decode($message, true);
            $this->processor->execute($data);
        } catch (\Exception $e) {
            $this->logger->error('Consumer error: ' . $e->getMessage());
            throw $e; // Re-throw to trigger retry/dead-letter
        }
    }
}
```

**RabbitMQ CLI Commands**:
```bash
# List consumers
bin/magento queue:consumers:list

# Start specific consumer
bin/magento queue:consumers:start vendor.module.process.consumer

# Start with message limit
bin/magento queue:consumers:start vendor.module.process.consumer --max-messages=1000

# Start in background (production)
bin/magento queue:consumers:start vendor.module.process.consumer &

# RabbitMQ management commands
rabbitmqctl list_queues name messages consumers
rabbitmqctl list_exchanges
rabbitmqctl list_bindings
```

**Cron-based Consumer (crontab.xml)**:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
    <group id="default">
        <job name="vendor_module_consumer" instance="Magento\MessageQueue\Model\Cron\ConsumersRunner" method="run">
            <schedule>* * * * *</schedule>
        </job>
    </group>
</config>
```

**env.php Consumer Configuration**:
```php
'cron_consumers_runner' => [
    'cron_run' => true,
    'max_messages' => 1000,
    'consumers' => [
        'vendor.module.process.consumer'
    ]
]
```

**RabbitMQ Best Practices**:

| Practice | Description |
|----------|-------------|
| Use dead-letter queues | Handle failed messages gracefully |
| Set message TTL | Prevent queue bloat with `x-message-ttl` |
| Implement idempotency | Messages may be delivered more than once |
| Monitor queue depth | Alert on growing queues |
| Use prefetch count | Limit unacknowledged messages per consumer |
| Enable persistence | Durable queues survive restarts |
| Cluster for HA | Use mirrored queues in production |
| Separate vhosts | Isolate environments |

**Built-in Magento Consumers**:

| Consumer | Purpose |
|----------|---------|
| `async.operations.all` | Async bulk operations |
| `product_action_attribute.update` | Mass attribute updates |
| `product_action_attribute.website.update` | Mass website updates |
| `exportProcessor` | Export processing |
| `inventory.source.items.cleanup` | MSI cleanup |
| `inventory.mass.update` | MSI bulk updates |
| `media.storage.catalog.image.resize` | Image resizing |

### 11.3 OpenSearch / Elasticsearch

**Purpose**: Catalog search, layered navigation, search suggestions.

**OpenSearch is the default for Magento 2.4.6+**. Elasticsearch 7.x support continues but OpenSearch is recommended.

**env.php Configuration**:
```php
'system' => [
    'default' => [
        'catalog' => [
            'search' => [
                'engine' => 'opensearch',
                'opensearch_server_hostname' => 'opensearch',
                'opensearch_server_port' => '9200',
                'opensearch_index_prefix' => 'magento2',
                'opensearch_enable_auth' => '0',
                'opensearch_username' => '',
                'opensearch_password' => '',
                'opensearch_server_timeout' => '15',
                'opensearch_minimum_should_match' => '1'
            ]
        ]
    ]
]
```

**CLI Configuration**:
```bash
# Set search engine
bin/magento config:set catalog/search/engine opensearch

# Configure connection
bin/magento config:set catalog/search/opensearch_server_hostname opensearch
bin/magento config:set catalog/search/opensearch_server_port 9200
bin/magento config:set catalog/search/opensearch_index_prefix magento2

# Reindex after configuration change
bin/magento indexer:reindex catalogsearch_fulltext
```

**Index Structure**:
```
magento2_product_1_v1           # Products for store 1
magento2_product_2_v1           # Products for store 2
magento2_category_1_v1          # Categories for store 1
magento2_catalogsearch_fulltext_scope1_v1  # Search index
```

**OpenSearch/Elasticsearch REST API**:
```bash
# Check cluster health
curl -X GET "opensearch:9200/_cluster/health?pretty"

# List all indices
curl -X GET "opensearch:9200/_cat/indices?v"

# Get index mapping
curl -X GET "opensearch:9200/magento2_product_1_v1/_mapping?pretty"

# Search products
curl -X GET "opensearch:9200/magento2_product_1_v1/_search?pretty" \
  -H 'Content-Type: application/json' \
  -d '{"query": {"match": {"name": "shirt"}}}'

# Delete index (careful!)
curl -X DELETE "opensearch:9200/magento2_product_1_v1"

# Get index stats
curl -X GET "opensearch:9200/magento2_product_1_v1/_stats?pretty"
```

**Custom Search Adapter** (di.xml):
```xml
<type name="Magento\Elasticsearch\SearchAdapter\Mapper">
    <plugin name="vendor_custom_search_mapper"
            type="Vendor\Module\Plugin\SearchMapperPlugin"/>
</type>
```

**Adding Custom Fields to Index**:
```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

class CustomFieldMapper implements BatchDataMapperInterface
{
    public function map(array $documentData, $storeId, array $context = []): array
    {
        foreach ($documentData as $productId => $document) {
            $documentData[$productId]['custom_field'] = $this->getCustomFieldValue($productId);
        }
        return $documentData;
    }

    private function getCustomFieldValue(int $productId): string
    {
        // Custom logic
        return 'value';
    }
}
```

**OpenSearch Configuration Best Practices**:

| Setting | Recommendation | Purpose |
|---------|----------------|---------|
| `index.number_of_shards` | 1-5 per index | Balance search/index performance |
| `index.number_of_replicas` | 1+ in production | High availability |
| `index.refresh_interval` | 30s for indexing | Reduce refresh overhead |
| `indices.memory.index_buffer_size` | 10% of heap | Indexing buffer |
| `thread_pool.search.size` | CPU cores + 1 | Search thread pool |
| `bootstrap.memory_lock` | true | Prevent swapping |
| JVM Heap | 50% of RAM (max 31GB) | Memory allocation |

**opensearch.yml / elasticsearch.yml**:
```yaml
cluster.name: magento-cluster
node.name: node-1
network.host: 0.0.0.0
discovery.type: single-node  # For development

# Production cluster
# discovery.seed_hosts: ["opensearch-node1", "opensearch-node2"]
# cluster.initial_master_nodes: ["node-1", "node-2"]

# Security (OpenSearch)
plugins.security.disabled: false
plugins.security.ssl.transport.enabled: true
plugins.security.ssl.http.enabled: true

# Performance
indices.query.bool.max_clause_count: 10000
search.max_buckets: 100000

# Index settings
action.auto_create_index: true
```

**Search Performance Best Practices**:

| Practice | Description |
|----------|-------------|
| Use replicas | Read operations distributed across replicas |
| Optimize mapping | Use `keyword` for exact match, `text` for full-text |
| Limit fields | Only index searchable/filterable attributes |
| Bulk indexing | Index in batches, not one-by-one |
| Refresh interval | Increase during bulk operations |
| Use filters | Filters are cached, queries are not |
| Shard sizing | Target 10-50GB per shard |
| Avoid wildcards | Leading wildcards (`*term`) are expensive |

**Troubleshooting**:

| Issue | Solution |
|-------|----------|
| Index not updating | Run `bin/magento indexer:reindex catalogsearch_fulltext` |
| Search returns no results | Check index exists, verify mapping |
| Slow searches | Add replicas, optimize queries |
| Out of memory | Increase heap, add nodes |
| Connection refused | Check host/port, firewall rules |
| Authentication errors | Verify credentials in env.php |

**Monitoring Queries** (Slow log):
```yaml
# opensearch.yml
index.search.slowlog.threshold.query.warn: 10s
index.search.slowlog.threshold.query.info: 5s
index.search.slowlog.threshold.fetch.warn: 1s
```

**Cloud-Managed OpenSearch (AWS OpenSearch Service / Azure Cognitive Search)**:

| Setting | Self-Hosted | Cloud-Managed |
|---------|-------------|---------------|
| JVM heap | Configure manually | Determined by instance type |
| Shards/replicas | Configure per index | Use service defaults or console |
| `opensearch.yml` | Full control | Limited/no access |
| Authentication | Basic auth or certificates | IAM roles recommended (AWS) |
| Index Lifecycle (ILM) | Elasticsearch ILM policies | Use ISM policies (different syntax) |
| Cluster scaling | Manual node management | Auto-scaling via service |
| Snapshots | Configure S3/filesystem | Automated via service |

> **AWS OpenSearch Service Notes**:
> - Use IAM-based authentication instead of basic auth when possible
> - ISM (Index State Management) replaces ILM - different JSON syntax
> - Fine-grained access control requires different credential configuration in `env.php`
> - VPC endpoints require different network configuration than public endpoints

---

## 12. Best Practices 2025+

### PHP Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.3+ |
| OpenSearch | 2.x |
| MySQL | 8.0+ |
| MariaDB | 10.6+ |
| Redis | 7.0+ |
| Varnish | 7.x |
| Composer | 2.x |

### Coding Standards

- **PSR-12** coding standard
- Use **strict types** (`declare(strict_types=1)`)
- Use **constructor property promotion**
- Use **readonly properties** where applicable
- Use **typed properties** and return types
- Use **null coalescing** and **null safe operator**

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model;

class Service
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?ConfigInterface $config = null
    ) {
    }

    public function process(?DataInterface $data): ?ResultInterface
    {
        return $data?->process() ?? $this->config?->getDefault();
    }
}
```

### Performance Best Practices

| Area | Best Practice |
|------|---------------|
| **Caching** | Use full page cache, block cache, config cache |
| **Indexing** | Set indexers to "Update by Schedule" in production |
| **Flat Tables** | **DEPRECATED** - Do not enable; EAV with proper indexing is faster |
| **Images** | Use WebP, lazy loading, image optimization |
| **JavaScript** | Bundle, minify, defer non-critical JS |
| **Database** | Use declarative schema, optimize queries |
| **Sessions** | Use Redis for sessions |
| **Queue** | Use RabbitMQ for async operations |
| **CDN** | Offload static content to CDN |

### Security Best Practices

| Area | Requirement |
|------|-------------|
| **PCI DSS** | Required for payment processing |
| **Security Patches** | Apply promptly |
| **Admin URL** | Custom admin URL path |
| **2FA** | Enable two-factor authentication |
| **CSP** | Configure Content Security Policy |
| **HTTPS** | Enforce HTTPS everywhere |
| **reCAPTCHA** | Enable on forms |
| **Input Validation** | Always validate/sanitize input |
| **Output Escaping** | Use `$escaper` in templates |
| **File Permissions** | Restrict file system permissions |

### Module Development Checklist

- [ ] Use service contracts (interfaces in `Api/`)
- [ ] Implement data interfaces (`Api/Data/`)
- [ ] Use dependency injection (no ObjectManager)
- [ ] Create unit tests
- [ ] Use declarative schema
- [ ] Follow ACL best practices
- [ ] Document public APIs
- [ ] Use proper logging
- [ ] Handle exceptions properly
- [ ] Validate all input

### Large Catalog Operations (50k+ Products)

**Batch Processing Patterns**:

| Pattern | Use | Avoid |
|---------|-----|-------|
| Batch saves | `insertMultiple()`/`insertOnDuplicate()` | Individual `save()` in loops |
| Collection iteration | `CollectionByPagesIterator` or `setPageSize()` | `getItems()` on large collections |
| Index mode | "Update by Schedule" | Realtime indexing during imports |
| Memory | Clear collections after each batch | Loading entire dataset into memory |
| Batch size | 100-500 records per batch | Processing all records at once |

**Bulk Insert Pattern**:
```php
<?php
// CORRECT: Bulk insert using ResourceConnection
$connection = $this->resourceConnection->getConnection();
$tableName = $this->resourceConnection->getTableName('your_table');

$data = []; // Array of rows
foreach ($items as $item) {
    $data[] = [
        'column1' => $item->getValue1(),
        'column2' => $item->getValue2(),
    ];

    // Batch every 500 records
    if (count($data) >= 500) {
        $connection->insertMultiple($tableName, $data);
        $data = [];
    }
}

// Insert remaining
if (!empty($data)) {
    $connection->insertMultiple($tableName, $data);
}
```

**Collection Iterator Pattern**:
```php
<?php
// CORRECT: Process large collections in pages
$collection = $this->collectionFactory->create();
$collection->setPageSize(100);
$lastPage = $collection->getLastPageNumber();

for ($page = 1; $page <= $lastPage; $page++) {
    $collection->setCurPage($page);

    foreach ($collection as $item) {
        $this->processItem($item);
    }

    $collection->clear(); // Critical: free memory
}
```

**Configuration for Large Imports**:

| Setting | Value | Location |
|---------|-------|----------|
| `general/file/bunch_size` | 200-500 | Admin config or env.php |
| `mysql max_allowed_packet` | ≥100M | MySQL server config |
| `php memory_limit` | ≥2GB for CLI | php.ini (CLI) |
| Indexer mode | Schedule | `bin/magento indexer:set-mode schedule` |

**Common Mistakes Causing Slow Imports**:

| Mistake | Impact | Fix |
|---------|--------|-----|
| Individual `save()` in loops | 100x slower | Use `insertMultiple()` |
| Realtime indexing | Exponential slowdown | Set to Schedule mode |
| Not clearing collections | Memory exhaustion | Call `clear()` each iteration |
| Loading all products first | OOM errors | Use iterator/pagination |
| Missing `--keep-generated` on upgrade | Unnecessary recompilation | Always use the flag |

**Cloud Database Considerations (AWS RDS / Azure Database)**:

| Setting | Self-Hosted | Cloud-Managed |
|---------|-------------|---------------|
| Buffer pool size | Configure in my.cnf | Determined by instance type |
| Max connections | Configure in my.cnf | Auto-scaled by service |
| Backups | Configure manually | Automated via cloud console |
| Query cache | Optional (deprecated in 8.0) | Not available |
| Slow query log | File-based | CloudWatch/Azure Logs |

> **Note**: Most MySQL/MariaDB tuning parameters in traditional guides are pre-configured or managed by cloud database services. Focus on query optimization and indexing rather than server tuning.

---

## 13. Common Pitfalls & Gotchas

### ObjectManager Antipattern

**Wrong**:
```php
// NEVER do this
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
```

**Correct**:
```php
// Use constructor injection
public function __construct(
    private readonly ProductFactory $productFactory
) {}

public function createProduct(): ProductInterface
{
    return $this->productFactory->create();
}
```

### Plugin Sort Order Conflicts

**Issue**: Multiple plugins on same method can conflict.

**Solution**:
- Use unique, high sort orders (100, 200, 300)
- Document plugin interactions
- Prefer observers when possible
- Avoid around plugins when before/after suffice

### EAV Performance Traps

| Trap | Solution |
|------|----------|
| Loading all attributes | Use `addAttributeToSelect(['name', 'price'])` |
| N+1 queries | Use collections, not individual loads |
| Individual saves in loops | Use `insertMultiple()`/`insertOnDuplicate()` for bulk operations |
| Full EAV joins | Index frequently filtered attributes |
| Loading entire collections | Use `CollectionByPagesIterator` or `setPageSize()` with iteration |
| Realtime indexing during imports | Set indexers to "Schedule" mode before bulk operations |
| Not clearing collections | Call `clear()` on collections after processing each batch |

### Cache Invalidation Issues

| Issue | Solution |
|-------|----------|
| Stale data after save | Use proper cache tags |
| Missing cache invalidation | Implement `IdentityInterface` |
| Over-invalidation | Use granular cache tags |
| Cache not clearing | Check cache configuration |

**Proper Cache Tags**:
```php
class Entity extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'vendor_entity';

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
```

### Common Configuration Mistakes

| Mistake | Consequence | Fix |
|---------|-------------|-----|
| Wrong di.xml scope | Plugin not applied | Use correct area folder |
| Missing module dependency | Random failures | Add to `module.xml` sequence |
| Wrong ACL resource | Permission denied | Check `acl.xml` hierarchy |
| Missing route | 404 errors | Check `routes.xml` structure |

### Layout XML Issues

| Issue | Solution |
|-------|----------|
| Block not showing | Check container exists, check cacheable |
| Wrong template | Verify template path and permissions |
| JavaScript not loading | Check requirejs-config.js |
| CSS not applying | Run static content deploy |

### Database Migration Issues

| Issue | Solution |
|-------|----------|
| Schema changes not applying | Regenerate whitelist |
| Data patches not running | Check dependencies |
| Foreign key errors | Correct table creation order |

---

## 14. Adobe Commerce Features (Commerce Edition Only)

### B2B Modules

| Module | Purpose | Key Features |
|--------|---------|--------------|
| `Magento_Company` | Company accounts | Hierarchies, roles, permissions |
| `Magento_SharedCatalog` | Custom catalogs | B2B pricing, product access |
| `Magento_NegotiableQuote` | Quote negotiation | Price negotiation workflow |
| `Magento_RequisitionList` | Saved lists | Quick reorder, multiple lists |
| `Magento_QuickOrder` | Fast ordering | SKU entry, CSV upload |
| `Magento_PurchaseOrder` | Approval workflows | Multi-level approval |
| `Magento_CompanyCredit` | Payment on account | Credit limits, history |

**Company Structure**:
```
Company (Root)
├── Division (Team)
│   ├── User (Buyer)
│   └── User (Purchaser)
└── Division (Team)
    └── User (Admin)
```

**Key B2B Interfaces**:
```php
// Company management
Magento\Company\Api\CompanyRepositoryInterface
Magento\Company\Api\CompanyManagementInterface

// Shared catalog
Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
Magento\SharedCatalog\Api\ProductManagementInterface

// Negotiable quotes
Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface
```

### Content Staging & Preview

**Concept**: Schedule content changes (campaigns) for future dates.

**Staged Entities**:
- Products
- Categories
- CMS Pages/Blocks
- Catalog Rules
- Cart Rules
- Widgets

**Key Tables**:

| Table | Purpose |
|-------|---------|
| `staging_update` | Update/campaign definitions |
| `sequence_*` | Entity version sequences |
| `*_entity` (modified) | Includes `row_id`, `created_in`, `updated_in` |

**Key Interfaces**:
```php
// Staging management
Magento\Staging\Api\UpdateRepositoryInterface
Magento\Staging\Model\VersionManager

// Preview
Magento\Staging\Model\Preview\UrlBuilder
```

**Staging CLI**:
```bash
bin/magento staging:synchronize:entities
```

**Important Notes**:
- Staging modifies core entity tables (adds versioning columns)
- Preview mode uses special URL parameters
- Campaigns can overlap (latest wins)
- Rollback creates new update, doesn't delete

### Page Builder

**Structure**:
```
app/code/Vendor/Module/
└── view/
    └── adminhtml/
        └── pagebuilder/
            ├── content_type/
            │   └── custom_type.xml
            └── template/
                └── custom_type/
                    ├── default/
                    │   └── master.html
                    └── preview/
                        └── master.html
```

**Content Type Definition** (content_type/custom_type.xml):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_PageBuilder:etc/content_type.xsd">
    <type name="custom_type"
          label="Custom Type"
          menu_section="elements"
          component="Vendor_Module/js/content-type/custom-type"
          preview_component="Vendor_Module/js/content-type/custom-type/preview"
          master_component="Vendor_Module/js/content-type/custom-type/master"
          form="vendor_pagebuilder_custom_type_form"
          icon="icon-pagebuilder-custom"
          sortOrder="100"
          translate="label">
        <appearances>
            <appearance name="default"
                       default="true"
                       preview_template="Vendor_Module/content-type/custom-type/default/preview"
                       master_template="Vendor_Module/content-type/custom-type/default/master"
                       reader="Magento_PageBuilder/js/master-format/read/configurable">
                <elements>
                    <element name="main">
                        <attribute name="data-content-type" source="data-content-type"/>
                        <attribute name="data-appearance" source="data-appearance"/>
                    </element>
                </elements>
            </appearance>
        </appearances>
    </type>
</config>
```

**Built-in Content Types**:

| Type | Purpose |
|------|---------|
| `row` | Container row |
| `column` | Column within row |
| `heading` | Heading text |
| `text` | Rich text content |
| `html` | Raw HTML |
| `block` | CMS block reference |
| `image` | Image with options |
| `video` | Video embed |
| `banner` | Banner with link |
| `slider` | Image slider |
| `buttons` | Button group |
| `tabs` | Tabbed content |
| `map` | Google Map |
| `products` | Product carousel |

### Customer Segments

**Purpose**: Dynamic customer grouping based on conditions.

**Segment Conditions**:
- Customer attributes
- Address attributes
- Order history
- Shopping cart contents
- Product views
- Wishlist items

**Key Tables**:

| Table | Purpose |
|-------|---------|
| `magento_customersegment_segment` | Segment definitions |
| `magento_customersegment_customer` | Segment-customer mapping |
| `magento_customersegment_event` | Events triggering refresh |

**Key Interface**:
```php
Magento\CustomerSegment\Api\SegmentRepositoryInterface
```

**Using Segments**:
```php
// Check if customer in segment
$this->segmentCustomer->getCustomerSegmentIdsForWebsite(
    $customerId,
    $websiteId
);
```

### Related Products Rules

**Purpose**: Automated related/cross-sell/up-sell based on conditions.

**Rule Types**:
- Related Products
- Cross-sells
- Up-sells

**Conditions Available**:
- Product attributes
- Category
- Price range
- SKU patterns

### Advanced Reporting

**Features**:
- Business Intelligence integration
- Advanced sales reports
- Customer behavior analytics
- Product performance

**Data Collection**:
```bash
bin/magento analytics:collect-data
```

### Gift Cards (Commerce)

**Types**:
- Virtual (email delivery)
- Physical (shipped)
- Combined

**Key Tables**:
| Table | Purpose |
|-------|---------|
| `magento_giftcard_amount` | Gift card amounts |
| `magento_giftcardaccount` | Gift card accounts |
| `magento_giftcardaccount_pool` | Code pool |

**Key Interface**:
```php
Magento\GiftCardAccount\Api\GiftCardAccountRepositoryInterface
```

### Store Credit

**Purpose**: Customer credit balance for purchases.

**Key Tables**:
| Table | Purpose |
|-------|---------|
| `magento_customerbalance` | Customer balances |
| `magento_customerbalance_history` | Balance history |

**Key Interface**:
```php
Magento\CustomerBalance\Api\BalanceManagementInterface
```

### Reward Points

**Purpose**: Loyalty program with points.

**Point Earning**:
- Registration
- Newsletter signup
- Review submission
- Purchase

**Key Tables**:
| Table | Purpose |
|-------|---------|
| `magento_reward` | Customer points |
| `magento_reward_history` | Points history |
| `magento_reward_rate` | Exchange rates |

**Key Interface**:
```php
Magento\Reward\Api\RewardManagementInterface
```

### RMA (Returns)

**Purpose**: Return merchandise authorization workflow.

**RMA Statuses**:
- Pending
- Authorized
- Partially Authorized
- Received
- Processed/Closed
- Denied

**Key Tables**:
| Table | Purpose |
|-------|---------|
| `magento_rma` | RMA requests |
| `magento_rma_item_entity` | RMA items |
| `magento_rma_status_history` | Status changes |

**Key Interface**:
```php
Magento\Rma\Api\RmaRepositoryInterface
Magento\Rma\Api\RmaManagementInterface
```

### Gift Registry

**Purpose**: Customer wish lists for events (wedding, birthday).

**Features**:
- Multiple registry types
- Sharing with guests
- Privacy controls

### Target Rules (Related Products)

**Purpose**: Automated product recommendations.

**Configuration**:
- Admin > Marketing > Related Products Rules
- Conditions based on attributes
- Automatic or manual application

---

## 15. Testing

### Test Types

| Type | Location | Command |
|------|----------|---------|
| Unit | `Test/Unit/` | `vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist` |
| Integration | `dev/tests/integration/` | `vendor/bin/phpunit -c dev/tests/integration/phpunit.xml.dist` |
| API Functional | `dev/tests/api-functional/` | `vendor/bin/phpunit -c dev/tests/api-functional/phpunit.xml` |
| Static | `dev/tests/static/` | `vendor/bin/phpunit -c dev/tests/static/phpunit.xml.dist` |
| MFTF | `dev/tests/acceptance/` | `vendor/bin/mftf run:test` |

### Unit Test Example

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Vendor\Module\Model\Service;
use Psr\Log\LoggerInterface;

class ServiceTest extends TestCase
{
    private Service $service;
    private LoggerInterface|MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->service = new Service($this->loggerMock);
    }

    public function testProcess(): void
    {
        $result = $this->service->process(['data' => 'test']);
        $this->assertNotEmpty($result);
    }

    public function testProcessWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->process([]);
    }
}
```

### Integration Test Example

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Vendor\Module\Api\EntityRepositoryInterface;

class EntityRepositoryTest extends TestCase
{
    private EntityRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = Bootstrap::getObjectManager()
            ->get(EntityRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Vendor_Module::Test/Integration/_files/entity.php
     */
    public function testGet(): void
    {
        $entity = $this->repository->get(1);
        $this->assertEquals('Test Entity', $entity->getName());
    }
}
```

---

## 16. Quick Troubleshooting

| Symptom | Likely Cause | Fix |
|---------|--------------|-----|
| 404 on frontend | Missing route, disabled module | Check routes.xml, module status |
| Class not found | DI compile needed | `bin/magento setup:di:compile` |
| Changes not visible | Cache | `bin/magento cache:clean` |
| Static content old | Deploy needed | `bin/magento setup:static-content:deploy` |
| Database errors | Schema outdated | `bin/magento setup:upgrade` |
| White page | Exception, check logs | Check `var/log/exception.log` |
| Slow performance | Missing indexes | `bin/magento indexer:reindex` |
| GraphQL errors | Schema cache | `bin/magento cache:clean config` |
| Payment not showing | Config/ACL issue | Check system config, module status |
| Cron not running | Crontab missing | `bin/magento cron:install` |

### Important Log Files

| Log | Location | Content |
|-----|----------|---------|
| System | `var/log/system.log` | General system logs |
| Exception | `var/log/exception.log` | PHP exceptions |
| Debug | `var/log/debug.log` | Debug information |
| Report | `var/report/` | Detailed error reports |
| Apache/Nginx | Server logs | Request/access logs |

### Debug Mode Commands

```bash
# Enable developer mode
bin/magento deploy:mode:set developer

# Enable logging
bin/magento dev:query-log:enable

# Enable template hints (admin)
bin/magento dev:template-hints:enable

# Profiler
bin/magento dev:profiler:enable html
```

---

## 17. Useful Code Snippets

### Get Current Store/Customer

```php
// Store Manager
$store = $this->storeManager->getStore();
$storeId = $this->storeManager->getStore()->getId();
$websiteId = $this->storeManager->getWebsite()->getId();

// Customer Session (frontend)
$customerId = $this->customerSession->getCustomerId();
$customer = $this->customerSession->getCustomer();
$isLoggedIn = $this->customerSession->isLoggedIn();
```

### Load Product/Category

```php
// By ID
$product = $this->productRepository->getById($productId);

// By SKU
$product = $this->productRepository->get($sku);

// Category
$category = $this->categoryRepository->get($categoryId);
```

### Create Order Programmatically

```php
// Get quote
$quote = $this->cartRepository->getActiveForCustomer($customerId);

// Add product
$quote->addProduct($product, $buyRequest);

// Set addresses
$quote->getShippingAddress()->addData($shippingData);
$quote->getBillingAddress()->addData($billingData);

// Set shipping method
$quote->getShippingAddress()
    ->setCollectShippingRates(true)
    ->collectShippingRates()
    ->setShippingMethod('flatrate_flatrate');

// Set payment
$quote->setPaymentMethod('checkmo');
$quote->getPayment()->importData(['method' => 'checkmo']);

// Submit order
$quote->collectTotals();
$this->cartRepository->save($quote);
$orderId = $this->cartManagement->placeOrder($quote->getId());
```

### Send Email

```php
$transport = $this->transportBuilder
    ->setTemplateIdentifier('custom_email_template')
    ->setTemplateOptions([
        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
        'store' => $storeId
    ])
    ->setTemplateVars(['customer' => $customer, 'data' => $data])
    ->setFromByScope('general')
    ->addTo($email, $name)
    ->getTransport();

$transport->sendMessage();
```

### Add System Configuration Programmatically

```php
$this->configWriter->save(
    'section/group/field',
    $value,
    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
    0
);

// Clear config cache
$this->cacheTypeList->cleanType('config');
```

---

## 18. Deployment & CI/CD

### 18.1 Two-Step Deployment Pattern (Recommended)

Separating build from deploy minimizes production downtime by preparing artifacts in CI where no database connection is required.

**Build Phase (CI Server - No Database Required)**:
```bash
# Install dependencies (no dev packages for production)
composer install --no-dev --prefer-dist --optimize-autoloader

# Compile dependency injection
bin/magento setup:di:compile

# Deploy static content (all required locales/themes)
bin/magento setup:static-content:deploy en_US en_GB -f --jobs=$(nproc)

# Create deployable artifact
tar -czf artifact.tar.gz app bin generated lib pub/static vendor

# Upload artifact to deployment server or artifact storage (S3, etc.)
```

**Deploy Phase (Production - Minimal Downtime)**:
```bash
# Stop queue consumers FIRST (prevents database issues)
bin/magento cron:remove
supervisorctl stop magento-consumers:*  # or systemctl stop magento-consumers

# Extract artifact to release directory
tar -xzf artifact.tar.gz -C /var/www/magento/releases/$(date +%Y%m%d%H%M%S)

# Update symlink (atomic operation)
ln -sfn /var/www/magento/releases/$(date +%Y%m%d%H%M%S) /var/www/magento/current

# Enable maintenance mode (only now!)
bin/magento maintenance:enable

# Run database upgrades with keep-generated flag
bin/magento setup:upgrade --keep-generated

# Disable maintenance mode
bin/magento maintenance:disable

# Flush caches
bin/magento cache:flush

# Clear OPcache (CRITICAL - prevents stale code execution)
# Option 1: Reload PHP-FPM
sudo systemctl reload php-fpm
# Option 2: Cachetool
cachetool opcache:reset

# Restart queue consumers
bin/magento cron:install
supervisorctl start magento-consumers:*
```

### 18.2 What Requires Maintenance Mode

| Operation | Maintenance Mode Required? | Notes |
|-----------|---------------------------|-------|
| `setup:upgrade` | **YES** | Database changes can break frontend |
| `setup:di:compile` | NO | Run in build phase |
| `setup:static-content:deploy` | NO | Run in build phase |
| `composer install` | NO | Run in build phase |
| `cache:flush` | NO | Brief inconsistency acceptable |
| `indexer:reindex` | NO (usually) | May affect frontend during reindex |
| `config:set` | NO | Requires cache flush after |

### 18.3 Common Deployment Mistakes

| Mistake | Impact | Fix |
|---------|--------|-----|
| Running all commands on production | 10-30+ min downtime | Use 2-step deployment with build artifacts |
| Omitting `--keep-generated` | Regenerates DI/static unnecessarily | Always include flag with `setup:upgrade` |
| Static deploy in production phase | Deployment fails if compilation errors | Move to build phase |
| Not stopping queue consumers before maintenance | Database deadlocks, failed jobs | Stop consumers before `maintenance:enable` |
| Not clearing OPcache after deploy | Stale PHP code executes | Reload PHP-FPM or use cachetool |
| Running `composer install` on production | Network dependency, slow | Install in build phase, deploy artifacts |
| Using `setup:upgrade` without maintenance mode | Users see errors during schema changes | Always enable maintenance mode first |
| Forgetting to restart cron | Scheduled tasks don't run | Reinstall cron after deployment |

### 18.4 Zero-Downtime Deployment Strategy

For true zero-downtime deployments:

1. **Blue-Green Deployment**: Maintain two identical environments, deploy to inactive, switch load balancer
2. **Rolling Deployment**: Deploy to servers one at a time behind load balancer
3. **Database Migrations**: Use backward-compatible schema changes (add columns, never remove)

**Backward-Compatible Schema Changes**:
```xml
<!-- GOOD: Adding nullable column (doesn't break existing code) -->
<column xsi:type="varchar" name="new_field" nullable="true" length="255"/>

<!-- BAD: Removing column (breaks code expecting it) -->
<!-- Never remove columns in same release as code change -->
```

**Migration Strategy for Breaking Changes**:
1. Release 1: Add new column, deploy code that writes to both old and new
2. Release 2: Migrate data, deploy code that reads from new only
3. Release 3: Remove old column (after confirming no code uses it)

### 18.5 Environment-Specific Commands

**Development**:
```bash
bin/magento deploy:mode:set developer
bin/magento setup:di:compile  # Optional in developer mode
bin/magento cache:disable full_page block_html
```

**Staging/Production**:
```bash
bin/magento deploy:mode:set production --skip-compilation
# Compilation should be done in build phase, not here
```

### 18.6 Deployment Checklist

- [ ] Queue consumers stopped before maintenance mode
- [ ] Cron disabled/removed before maintenance mode
- [ ] Maintenance mode enabled before `setup:upgrade`
- [ ] `--keep-generated` flag used with `setup:upgrade`
- [ ] Maintenance mode disabled after database upgrade
- [ ] All caches flushed
- [ ] OPcache cleared (PHP-FPM reloaded)
- [ ] Cron reinstalled
- [ ] Queue consumers restarted
- [ ] Smoke test critical paths (homepage, category, product, checkout)

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Jan 2025 | Initial release for Magento 2.4.8+ |

---

*This document is optimized for AI agent consumption. For official documentation, visit [Adobe Commerce Documentation](https://experienceleague.adobe.com/docs/commerce.html).*
