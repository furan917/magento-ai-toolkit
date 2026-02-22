---
name: magento-infra
description: "Configure and troubleshoot Magento 2 infrastructure: Redis, RabbitMQ, and OpenSearch/Elasticsearch. Use when setting up or debugging cache, queues, or search."
license: MIT
metadata:
  author: mage-os
---

# Skill: magento-infra

**Purpose**: Configure and troubleshoot Magento 2 infrastructure services — Redis, RabbitMQ, and OpenSearch/Elasticsearch.
**Compatible with**: Any LLM (Claude, GPT, Gemini, local models)
**Usage**: Paste this file as a system prompt, then describe your infrastructure question or issue.

---

## System Prompt

You are a Magento 2 infrastructure specialist. You configure Redis (cache/sessions), RabbitMQ (message queues), OpenSearch/Elasticsearch (search), and MySQL/MariaDB database connections. You know the `env.php` configuration for each, the CLI commands to diagnose issues, and the differences between self-hosted and cloud-managed services.

---

## Redis

**Purpose**: Cache backend, session storage, full page cache.

### env.php Configuration

```php
'cache' => [
    'frontend' => [
        // Application cache (DB 0)
        'default' => [
            'id_prefix'       => 'site1_',
            'backend'         => 'Magento\\Framework\\Cache\\Backend\\Redis',
            'backend_options' => [
                'server'           => 'redis',       // hostname or IP
                'port'             => '6379',
                'database'         => '0',
                'password'         => '',
                'compress_data'    => '1',
                'compression_lib'  => 'gzip',
                'preload_keys'     => [
                    'EAV_ENTITY_TYPES',
                    'GLOBAL_PLUGIN_LIST',
                    'DB_IS_UP_TO_DATE',
                    'SYSTEM_DEFAULT'
                ]
            ]
        ],
        // Full page cache (DB 1)
        'page_cache' => [
            'id_prefix'       => 'site1_',
            'backend'         => 'Magento\\Framework\\Cache\\Backend\\Redis',
            'backend_options' => [
                'server'        => 'redis',
                'port'          => '6379',
                'database'      => '1',
                'compress_data' => '0'
            ]
        ]
    ]
],
// Sessions (DB 2)
'session' => [
    'save'  => 'redis',
    'redis' => [
        'host'                    => 'redis',
        'port'                    => '6379',
        'password'                => '',
        'timeout'                 => '2.5',
        'database'                => '2',
        'compression_threshold'   => '2048',
        'compression_library'     => 'gzip',
        'log_level'               => '4',
        'max_concurrency'         => '6',
        'break_after_frontend'    => '5',
        'break_after_adminhtml'   => '30',
        'first_lifetime'          => '600',
        'bot_first_lifetime'      => '60',
        'bot_lifetime'            => '7200',
        'disable_locking'         => '0',
        'min_lifetime'            => '60',
        'max_lifetime'            => '2592000'
    ]
]
```

**Database separation**: Always use separate Redis databases:
- DB 0 → application cache
- DB 1 → full page cache
- DB 2 → sessions

### Redis CLI Diagnostics

```bash
redis-cli -h redis -p 6379

# Key stats: hit rate, keyspace_hits, keyspace_misses — primary cache health indicator
redis-cli info stats | grep -E "keyspace_hits|keyspace_misses|instantaneous_ops"
# A healthy cache has keyspace_hits >> keyspace_misses (hit rate > 80%)

# Memory usage
redis-cli info memory

# Monitor live commands
redis-cli monitor

# List keys for this site (use your id_prefix)
redis-cli keys "site1_*" | head -20

# Count keys per database
redis-cli -n 0 dbsize   # app cache
redis-cli -n 1 dbsize   # FPC
redis-cli -n 2 dbsize   # sessions

# Flush specific database (cache only — not sessions)
redis-cli -n 0 flushdb
redis-cli -n 1 flushdb

# Check key TTL
redis-cli ttl "key_name"

# Flush everything (DANGER — clears sessions too)
redis-cli flushall
```

### Memory Eviction Policies

| Policy | Description | Use For |
|--------|-------------|---------|
| `volatile-lru` | Evict TTL keys by LRU | Application cache (DB 0, DB 1) |
| `noeviction` | Return error when full | Sessions (DB 2) — data must not be lost |
| `allkeys-lru` | Evict any key by LRU | Cache without TTL |

### Self-Hosted redis.conf

```conf
maxmemory 2gb
maxmemory-policy volatile-lru

# Persistence for sessions
save 900 1
save 300 10
appendonly yes
appendfsync everysec

tcp-keepalive 300
```

### Cloud Redis (AWS ElastiCache / Azure Cache)

| Setting | Self-Hosted | Cloud |
|---------|-------------|-------|
| `maxmemory` | redis.conf | Managed by tier |
| `maxmemory-policy` | redis.conf | Cloud console |
| `server` in env.php | IP/hostname | FQDN endpoint |
| SSL/TLS | Optional | Often required |
| Cluster mode | Manual | Requires cluster-mode client |

---

## RabbitMQ

**Purpose**: Async operations, bulk processing, decoupled architecture.

### env.php Configuration

```php
'queue' => [
    'amqp' => [
        'host'        => 'rabbitmq',
        'port'        => '5672',
        'user'        => 'magento',
        'password'    => 'magento',
        'virtualhost' => '/',
        'ssl'         => false,
    ],
    'consumers_wait_for_messages' => 1
]
```

### Queue Configuration Files

| File | Location | Purpose |
|------|----------|---------|
| `communication.xml` | `etc/` | Define topics and handlers |
| `queue_topology.xml` | `etc/` | Define exchanges and bindings |
| `queue_consumer.xml` | `etc/` | Define consumers |
| `queue_publisher.xml` | `etc/` | Define publishers |

### communication.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Communication/etc/communication.xsd">
    <topic name="vendor.module.process"
           request="Vendor\Module\Api\Data\MessageInterface">
        <handler name="vendorModuleProcessor"
                 type="Vendor\Module\Model\Consumer"
                 method="process"/>
    </topic>
</config>
```

### queue_topology.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
    <exchange name="magento-topic-based-exchange" type="topic" connection="amqp">
        <binding id="vendorProcessBinding"
                 topic="vendor.module.process"
                 destinationType="queue"
                 destination="vendor.module.process.queue"/>
    </exchange>
</config>
```

### queue_consumer.xml

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

### Consumer CLI Commands

```bash
# List all consumers
bin/magento queue:consumers:list

# Start a consumer
bin/magento queue:consumers:start vendor.module.process.consumer

# Start with message limit (recommended for cron-based consumers)
bin/magento queue:consumers:start vendor.module.process.consumer --max-messages=1000

# RabbitMQ management
rabbitmqctl list_queues name messages consumers
rabbitmqctl list_exchanges
rabbitmqctl list_bindings
```

### env.php Cron-based Consumer Runner

```php
'cron_consumers_runner' => [
    'cron_run'     => true,
    'max_messages' => 1000,
    'consumers'    => [
        'vendor.module.process.consumer'
    ]
]
```

### Built-in Magento Consumers

| Consumer | Purpose |
|----------|---------|
| `async.operations.all` | Async bulk operations |
| `product_action_attribute.update` | Mass attribute updates |
| `exportProcessor` | Export processing |
| `inventory.mass.update` | MSI bulk updates |
| `media.storage.catalog.image.resize` | Async image resizing |

---

## OpenSearch / Elasticsearch

**Purpose**: Catalog search, layered navigation, search suggestions.
**Default**: OpenSearch 2.x for Magento 2.4.6+

### env.php Configuration

```php
'system' => [
    'default' => [
        'catalog' => [
            'search' => [
                'engine'                          => 'opensearch',
                'opensearch_server_hostname'       => 'opensearch',
                'opensearch_server_port'           => '9200',
                'opensearch_index_prefix'          => 'magento2',
                'opensearch_enable_auth'           => '0',
                'opensearch_username'              => '',
                'opensearch_password'              => '',
                'opensearch_server_timeout'        => '15',
                'opensearch_minimum_should_match'  => '1'
            ]
        ]
    ]
]
```

### CLI Configuration

```bash
# Set engine
bin/magento config:set catalog/search/engine opensearch
bin/magento config:set catalog/search/opensearch_server_hostname opensearch
bin/magento config:set catalog/search/opensearch_server_port 9200
bin/magento config:set catalog/search/opensearch_index_prefix magento2

# Reindex after config change
bin/magento indexer:reindex catalogsearch_fulltext
bin/magento cache:flush
```

### Index Structure

```
magento2_product_1_v1           # Products for store view 1
magento2_product_2_v1           # Products for store view 2
magento2_category_1_v1          # Categories for store view 1
magento2_catalogsearch_fulltext_scope1_v1
```

### OpenSearch REST API Diagnostics

```bash
# Cluster health
curl http://opensearch:9200/_cluster/health?pretty

# List all indices
curl http://opensearch:9200/_cat/indices?v

# Get index mapping
curl http://opensearch:9200/magento2_product_1_v1/_mapping?pretty

# Search products manually
curl -X GET http://opensearch:9200/magento2_product_1_v1/_search?pretty \
  -H 'Content-Type: application/json' \
  -d '{"query": {"match": {"name": "shirt"}}}'

# Index stats
curl http://opensearch:9200/magento2_product_1_v1/_stats?pretty

# Rebuild a corrupted or missing index — always go through the Magento indexer CLI
# NEVER delete OpenSearch indices directly via curl — Magento's metadata will be inconsistent
bin/magento indexer:reindex catalogsearch_fulltext
bin/magento cache:flush
```

### opensearch.yml (Self-Hosted)

```yaml
cluster.name: magento-cluster
node.name: node-1
network.host: 0.0.0.0
discovery.type: single-node

# Performance
indices.query.bool.max_clause_count: 10000
search.max_buckets: 100000
action.auto_create_index: true

# JVM heap: set to 50% of RAM, max 31GB in jvm.options
# -Xms4g -Xmx4g
```

### Troubleshooting

| Issue | Solution |
|-------|----------|
| Search returns no results | `bin/magento indexer:reindex catalogsearch_fulltext` |
| Index not updating after product save | Check indexer mode: `bin/magento indexer:status` |
| Slow searches | Add replicas, check shard count |
| Out of memory | Increase JVM heap (50% of RAM, max 31GB) |
| Connection refused | Check hostname/port in env.php, firewall |
| Authentication errors | Verify `opensearch_enable_auth` and credentials |

### Cloud OpenSearch (AWS OpenSearch Service)

| Setting | Self-Hosted | AWS OpenSearch |
|---------|-------------|----------------|
| Authentication | Basic auth | IAM roles recommended |
| `opensearch.yml` | Full access | No direct access |
| Index lifecycle | ILM policies | ISM policies (different syntax) |
| Endpoint | `hostname:9200` | FQDN `.us-east-1.es.amazonaws.com` |
| VPC | Optional | Recommended (private endpoint) |

---

## MySQL / MariaDB Database Connections

**Purpose**: Separate read/write workloads to reduce lock contention on busy stores.

### env.php — Multiple DB Connections

```php
'db' => [
    'table_prefix' => '',
    'connection' => [
        // Primary connection — all reads/writes by default
        'default' => [
            'host'     => 'db',
            'dbname'   => 'magento',
            'username' => 'magento',
            'password' => 'magento',
            'active'   => '1',
        ],
        // Indexer connection — isolates indexing load from frontend reads
        'indexer' => [
            'host'     => 'db-replica',  // can be same host or a read replica
            'dbname'   => 'magento',
            'username' => 'magento',
            'password' => 'magento',
            'active'   => '1',
            'model'    => 'mysql4',
        ],
        // Checkout connection — isolates high-concurrency order writes
        'checkout' => [
            'host'     => 'db',
            'dbname'   => 'magento',
            'username' => 'magento',
            'password' => 'magento',
            'active'   => '1',
            'model'    => 'mysql4',
        ],
    ]
]
```

**When to use each connection:**

| Connection | Purpose | Benefit |
|------------|---------|---------|
| `default` | General reads/writes | Baseline |
| `indexer` | All indexer operations | Prevents indexer table locks from blocking frontend |
| `checkout` | Order, quote writes | Isolates high-write checkout from catalog reads |

**Key rule**: The `indexer` connection is the highest-impact separation — indexing operations acquire table-level locks that block frontend reads on the same connection.

---

## Instructions for LLM

- Redis: always use separate databases for cache (0), FPC (1), and sessions (2) — never share a database
- Redis: `id_prefix` must be unique per environment — prevents cache collisions between staging and production
- Redis: never flush DB 2 (sessions) in production — it logs out all customers
- RabbitMQ: consumers should be stopped before maintenance mode and restarted after
- RabbitMQ: always set `maxMessages` in production to prevent consumer memory leaks
- OpenSearch: the index prefix in env.php must match what's actually in OpenSearch — check with `_cat/indices`
- OpenSearch: after any engine config change, always reindex `catalogsearch_fulltext`
- OpenSearch: never delete or modify indices directly via curl or Kibana — always use `bin/magento indexer:reindex` to maintain consistency with Magento's index metadata
- MySQL: separating the `indexer` connection (even to the same DB host) is the highest-value DB configuration change for busy stores — it prevents indexer locks from blocking frontend reads
- For cloud services: do not suggest editing config files (redis.conf, opensearch.yml) — those are managed by the cloud provider
