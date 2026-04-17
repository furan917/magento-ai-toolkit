---
name: magento-indexer
description: "Build custom Magento 2 indexers with ActionInterface, indexer.xml, and mview.xml for full and incremental reindexing. Use when creating flat tables, denormalized data, or custom index structures."
license: MIT
metadata:
  author: mage-os
---

# Skill: magento-indexer

**Purpose**: Build custom Magento 2 indexers — full reindex, incremental (mview), flat table generation, and denormalised data structures.
**Compatible with**: Any LLM (Claude, GPT, Gemini, local models)
**Usage**: Paste this file as a system prompt, then describe the data you want to index and its source tables.

---

## System Prompt

You are a Magento 2 indexer specialist. You implement custom indexers using `ActionInterface`, declare them in `indexer.xml`, and wire incremental reindexing via `mview.xml`. You always choose the least expensive indexing strategy for the use case, advise on schedule vs realtime mode, and know how to isolate indexer load from frontend reads.

---

## Indexer Architecture

Magento's indexer framework has two layers:

| Layer | File | Purpose |
|-------|------|---------|
| **Indexer declaration** | `etc/indexer.xml` | Registers the indexer in the admin grid and CLI |
| **Materialized view (mview)** | `etc/mview.xml` | Subscribes to table changes for incremental reindex |
| **Action class** | `Model/Indexer/*.php` | Implements the three reindex entry points |

**Full reindex** (`executeFull`) — rebuilds the entire index from scratch.
**Partial reindex** (`executeList`) — rebuilds only changed entity IDs, triggered by mview.
**Row reindex** (`executeRow`) — rebuilds a single entity, triggered by realtime save events.

---

## Step 1 — Indexer Declaration (`etc/indexer.xml`)

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Indexer/etc/indexer.xsd">

    <indexer id="vendor_module_custom"
             view_id="vendor_module_custom"
             class="Vendor\Module\Model\Indexer\CustomIndexerAction">
        <title translate="true">Vendor Module Custom Index</title>
        <description translate="true">Indexes vendor module data for fast frontend reads.</description>
    </indexer>

</config>
```

**Key attributes**:
- `id` — unique indexer identifier, used in CLI (`bin/magento indexer:reindex vendor_module_custom`)
- `view_id` — must match the `id` in `mview.xml` to link incremental reindex
- `class` — the action class that implements `ActionInterface`

### Optional: dimensions and fieldsets

**Before adding `<fieldsets>` or `<dimensions>` to `indexer.xml`, ask the user whether they are required.** They are specialised features used by a small number of core indexers (notably `catalog_product_price`) and almost never needed for a typical custom indexer.

| Element | When to ask the user to add it | Example |
|---------|-------------------------------|---------|
| `<dimensions>` | Multi-store / multi-website / multi-customer-group indexes where a separate index table per dimension is required | `catalog_product_price` (per website and customer group) |
| `<fieldsets>` | Compound indexes that merge fields from multiple source select builders (rare outside of core price indexing) | Core `catalog_product_price` indexer |
| `primary="<table>"` | Legacy indexers that rely on a default mview subscription via the `primary` attribute — explicit `mview.xml` subscriptions are now the recommended approach | Some pre-2.3 indexer migrations |

If in doubt, omit these elements — a plain `<indexer>` with `id`, `view_id`, `class`, `title`, and `description` is correct for almost every custom indexer.

---

## Step 2 — Materialized View (`etc/mview.xml`)

The mview system watches source tables for changes and queues entity IDs for incremental reindex. Required when indexer mode is `Update by Schedule`.

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Mview/etc/mview.xsd">

    <view id="vendor_module_custom"
          class="Vendor\Module\Model\Indexer\CustomIndexerAction"
          group="indexer">
        <subscriptions>
            <!-- Watch the primary entity table -->
            <table name="catalog_product_entity" entity_column="entity_id"/>
            <!-- Watch related attribute value tables if EAV data is indexed -->
            <table name="catalog_product_entity_varchar" entity_column="entity_id"/>
            <table name="catalog_product_entity_decimal" entity_column="entity_id"/>
            <!-- Watch a custom join table -->
            <table name="vendor_module_product_link" entity_column="product_id"/>
        </subscriptions>
    </view>

</config>
```

**Rules**:
- `view id` must exactly match the `view_id` attribute in `indexer.xml`
- Every table you JOIN in your index query must be subscribed — otherwise changes to that table will not trigger incremental reindex
- `entity_column` is the column whose value will be passed to `executeList(array $ids)`

---

## Step 3 — Action Class

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Vendor\Module\Model\ResourceModel\Indexer\CustomIndexerResource;

/**
 * Must implement both ActionInterface (full/row reindex)
 * and MviewActionInterface (incremental from mview changelog).
 */
class CustomIndexerAction implements ActionInterface, MviewActionInterface
{
    public function __construct(
        private readonly CustomIndexerResource $indexerResource
    ) {}

    /**
     * Full reindex — rebuild entire index table from scratch.
     * Called by: bin/magento indexer:reindex vendor_module_custom
     */
    public function executeFull(): void
    {
        $this->indexerResource->reindexAll();
    }

    /**
     * Partial reindex — rebuild only the given entity IDs.
     * Called by: mview changelog processing (schedule mode).
     */
    public function executeList(array $ids): void
    {
        $this->indexerResource->reindexEntities($ids);
    }

    /**
     * Single entity reindex — rebuild one entity.
     * Called by: product/entity save in realtime mode.
     */
    public function executeRow($id): void
    {
        $this->indexerResource->reindexEntities([$id]);
    }

    /**
     * MviewActionInterface::execute — called by the mview processor with changelog IDs.
     * Delegates to executeList.
     */
    public function execute($ids): void
    {
        $this->executeList((array) $ids);
    }
}
```

---

## Step 4 — Index Resource Model

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\ResourceModel\Indexer;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CustomIndexerResource extends AbstractDb
{
    // Index table name — use a flat table for fast reads
    private const INDEX_TABLE = 'vendor_module_custom_index';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    protected function _construct(): void
    {
        // No primary model — resource-only class
        $this->_init(self::INDEX_TABLE, 'entity_id');
    }

    /**
     * Full reindex: truncate and repopulate from source tables.
     */
    public function reindexAll(): void
    {
        $connection = $this->getConnection();
        $connection->truncateTable($this->getTable(self::INDEX_TABLE));
        $this->insertBatch($connection, $this->getAllRows($connection));
    }

    /**
     * Partial reindex: delete rows for given IDs, then reinsert.
     */
    public function reindexEntities(array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable(self::INDEX_TABLE),
            ['entity_id IN (?)' => $ids]
        );
        $this->insertBatch($connection, $this->getRowsForIds($connection, $ids));
    }

    private function insertBatch(AdapterInterface $connection, \Generator $rows): void
    {
        $batch = [];
        foreach ($rows as $row) {
            $batch[] = $row;
            if (count($batch) >= 1000) {
                $connection->insertMultiple($this->getTable(self::INDEX_TABLE), $batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $connection->insertMultiple($this->getTable(self::INDEX_TABLE), $batch);
        }
    }

    private function getAllRows(AdapterInterface $connection): \Generator
    {
        $select = $connection->select()
            ->from(['main' => $this->getTable('catalog_product_entity')], ['entity_id'])
            ->joinLeft(
                ['link' => $this->getTable('vendor_module_product_link')],
                'main.entity_id = link.product_id',
                ['custom_value' => 'link.value']
            );

        foreach ($connection->fetchAll($select) as $row) {
            yield $row;
        }
    }

    private function getRowsForIds(AdapterInterface $connection, array $ids): \Generator
    {
        $select = $connection->select()
            ->from(['main' => $this->getTable('catalog_product_entity')], ['entity_id'])
            ->joinLeft(
                ['link' => $this->getTable('vendor_module_product_link')],
                'main.entity_id = link.product_id',
                ['custom_value' => 'link.value']
            )
            ->where('main.entity_id IN (?)', $ids);

        foreach ($connection->fetchAll($select) as $row) {
            yield $row;
        }
    }
}
```

---

## Step 5 — Index Table (`etc/db_schema.xml`)

```xml
<table name="vendor_module_custom_index" resource="default" engine="innodb"
       comment="Vendor Module Custom Index">
    <column xsi:type="int" name="entity_id" unsigned="true" nullable="false" identity="false"
            comment="Product Entity ID"/>
    <column xsi:type="varchar" name="custom_value" nullable="true" length="255"
            comment="Indexed Custom Value"/>
    <constraint xsi:type="primary" referenceId="PRIMARY">
        <column name="entity_id"/>
    </constraint>
    <index referenceId="VENDOR_MODULE_CUSTOM_INDEX_CUSTOM_VALUE" indexType="btree">
        <column name="custom_value"/>
    </index>
</table>
```

---

## CLI Commands

```bash
# Check all indexer statuses
bin/magento indexer:status

# Check all indexer modes
bin/magento indexer:show-mode

# Set all indexers to schedule mode (recommended for 50k+ catalogs)
bin/magento indexer:set-mode schedule

# Set a single indexer to schedule mode
bin/magento indexer:set-mode schedule vendor_module_custom

# Full reindex of your custom indexer
bin/magento indexer:reindex vendor_module_custom

# Full reindex of all indexers
bin/magento indexer:reindex

# Reset an indexer to "invalid" (forces full reindex on next run)
bin/magento indexer:reset vendor_module_custom

# Check mview changelog backlog — query the changelog table directly
# (mview has no dedicated CLI; check the <view_id>_cl table and mview_state)
mysql -e "SELECT COUNT(*) FROM vendor_module_custom_cl;"
mysql -e "SELECT view_id, status, version_id FROM mview_state WHERE view_id = 'vendor_module_custom';"

# Dimension mode — only relevant for dimension-aware indexers like catalog_product_price
bin/magento indexer:show-dimensions-mode catalog_product_price
```

---

## Realtime vs Schedule Mode

| Mode | Trigger | Best For |
|------|---------|----------|
| `Update on Save` (realtime) | Every save event triggers reindex synchronously | Small catalogs (<10k), critical data freshness |
| `Update by Schedule` | mview changelog queued, processed by cron | Large catalogs (50k+), imports, batch updates |

**Set via CLI**:
```bash
bin/magento indexer:set-mode realtime vendor_module_custom
bin/magento indexer:set-mode schedule vendor_module_custom
```

**Set via `crontab.xml`** (for schedule mode processing):
```xml
<job name="indexer_reindex_all_invalid" instance="Magento\Indexer\Cron\ReindexAllInvalid" method="execute">
    <schedule>* * * * *</schedule>
</job>
```

---

## Indexer Connection Isolation

For large reindexing operations, configure a dedicated DB connection to prevent table locks from blocking frontend reads:

```php
// env.php
'db' => [
    'connection' => [
        'indexer' => [
            'host'     => 'db-replica',
            'dbname'   => 'magento',
            'username' => 'magento',
            'password' => 'magento',
            'active'   => '1',
            'model'    => 'mysql4',
        ]
    ]
]
```

```php
// Use in resource model constructor
public function __construct(Context $context, string $connectionName = 'indexer')
{
    parent::__construct($context, $connectionName);
}
```

---

## Built-in Indexers Reference

| Indexer ID | Source Table(s) | Index Table | Cost |
|------------|----------------|-------------|------|
| `catalog_product_price` | `catalog_product_entity_decimal` | `catalog_product_index_price` | High |
| `catalog_product_attribute` | `catalog_product_entity_*` | `catalog_product_flat_*` | High |
| `catalogsearch_fulltext` | Product attribute tables | OpenSearch / Elasticsearch | High |
| `cataloginventory_stock` | `cataloginventory_stock_item` | `cataloginventory_stock_status` | Medium |
| `catalog_category_product` | `catalog_category_product` | `catalog_category_product_index` | Medium |
| `catalog_product_category` | `catalog_category_product` | `catalog_category_product_index` | Medium |
| `catalog_url_rewrite` | `url_rewrite` | `catalog_url_rewrite_product_category` | Low |
| `customer_grid` | `customer_entity` | `customer_grid_flat` | Low |

---

## When NOT to Build a Custom Indexer

| Situation | Use Instead |
|-----------|-------------|
| Filtering/sorting on a native EAV attribute | Add attribute to flat product index via `catalog_product_attribute` |
| Real-time data required on save | Observer on `catalog_product_save_after` |
| Simple derived column from one table | MySQL generated column or view |
| Small dataset (< 1k records) | Direct query with proper indexes |
| Aggregate data updated rarely | Cron job writing to a summary table |

---

## Troubleshooting

### `Intercepted class Vendor\Module\Model\Indexer\X does not exist`

This error surfaces immediately after creating a new indexer action class and means Magento's DI compiler has not generated the interceptors for the new class.

```bash
# Rebuild the generated/ interceptors and factories
bin/magento setup:di:compile

# Then verify the indexer is registered
bin/magento indexer:info | grep vendor_module_custom
```

**Do not** delete the `generated/` directory to "fix" this — `setup:di:compile` is the correct tool and repopulates the same files safely. Deleting `generated/` on production can break running PHP-FPM workers mid-request.

### Indexer does not appear in `bin/magento indexer:status`

After adding `etc/indexer.xml`, run `bin/magento setup:upgrade` to register the indexer, then `bin/magento indexer:status` will list it.

---

## Anti-Patterns

- Never use `ObjectManager::getInstance()` in indexer classes — inject all dependencies via constructor
- Never call `$product->save()` inside `executeFull()` — triggers observer loops and secondary indexer chains
- Never load a collection without `setPageSize()` in full reindex — causes OOM on large catalogs
- Never use `addAttributeToSelect('*')` in index queries — load only needed columns
- Never skip implementing `MviewActionInterface::execute()` — the mview processor calls this signature, not `executeList()`

---

## Large-Catalog Full Reindex Pattern

When asked to "implement the ResourceModel for a full reindex of N products" (where N is large — 50k+), the canonical answer is always the same shape: truncate the index table, stream rows from source via a `\Generator`, and `insertMultiple()` in batches of 500–1000.

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Model\ResourceModel\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomIndexerResource extends AbstractDb
{
    private const INDEX_TABLE = 'vendor_module_custom_index';
    private const BATCH_SIZE  = 1000;

    protected function _construct(): void
    {
        $this->_init(self::INDEX_TABLE, 'entity_id');
    }

    public function reindexAll(): void
    {
        $connection = $this->getConnection();
        $connection->truncateTable($this->getTable(self::INDEX_TABLE));

        $batch = [];
        foreach ($this->streamSourceRows($connection) as $row) {
            $batch[] = $row;
            if (count($batch) >= self::BATCH_SIZE) {
                $connection->insertMultiple($this->getTable(self::INDEX_TABLE), $batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $connection->insertMultiple($this->getTable(self::INDEX_TABLE), $batch);
        }
    }

    private function streamSourceRows(AdapterInterface $connection): \Generator
    {
        $select = $connection->select()
            ->from($this->getTable('catalog_product_entity'), ['entity_id', 'sku']);

        // query()->fetch() streams row by row — never loads the full result into memory.
        $stmt = $connection->query($select);
        while ($row = $stmt->fetch()) {
            yield $row;
        }
    }
}
```

Why this shape:
- `truncateTable()` before a full reindex — faster than `DELETE` and resets auto-increment
- `\Generator` via `fetch()` — constant memory regardless of source row count
- `insertMultiple()` in 1000-row batches — ~50× faster than per-row `insert()` calls
- `BATCH_SIZE` as a class constant — tunable per workload without scattering magic numbers

---

## Instructions for LLM

- **Every PHP code block you emit MUST start with `declare(strict_types=1);`** — this applies to scaffolds, snippets, and examples without exception
- **Every full-reindex implementation MUST use `insertMultiple()` in batches of 500–1000 rows and a `\Generator` to stream source rows** — never row-by-row inserts, never `fetchAll()` into an array on a large catalog
- **Every full reindex MUST `truncateTable()` the index table first** — do not incrementally delete+insert inside a full reindex
- Always implement both `ActionInterface` and `MviewActionInterface` — the mview processor calls `execute()`, not `executeList()` directly
- The `view_id` in `indexer.xml` MUST exactly match the `id` in `mview.xml` — a mismatch silently disables incremental reindex
- After creating a new indexer, run `bin/magento setup:upgrade` to register it, then `bin/magento setup:di:compile` to generate interceptors, then `bin/magento indexer:reindex vendor_module_custom` for the initial full index
- If the user reports "Intercepted class ... does not exist" after adding an indexer, the fix is `bin/magento setup:di:compile` — never `rm -rf generated/`
- Schedule mode requires cron to be running — always confirm `cron_schedule` table is being populated
- Never use `ObjectManager::getInstance()` — inject all dependencies via constructor
