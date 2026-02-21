# magento-ai-toolkit

A library of LLM-ready skills and agents for Magento 2 / Mage-OS development, debugging, and hosting. Each file is a self-contained system prompt — paste it into any LLM to instantly give it deep Magento expertise.

> **Magento version**: 2.4.8+ | **Mage-OS**: 2.x | **PHP**: 8.3+ | **OpenSearch**: 2.x

---

## How to Use

### With any chat LLM (Claude, ChatGPT, Gemini, etc.)

1. Open the relevant skill or agent `.md` file
2. Copy the entire contents
3. Paste it as a **system prompt** or at the top of your conversation
4. Ask your question or describe your task

### With Claude Code

```bash
# Reference a skill directly in your prompt
cat magento-ai-toolkit/skills/magento-debug.md | pbcopy
# Then paste into Claude Code conversation

# Or use as a project instruction by adding to CLAUDE.md
```

### With OpenAI API / custom tooling

```python
with open("magento-ai-toolkit/skills/magento-deploy.md") as f:
    system_prompt = f.read()

response = client.chat.completions.create(
    model="gpt-4o",
    messages=[
        {"role": "system", "content": system_prompt},
        {"role": "user",   "content": "How do I deploy to production?"}
    ]
)
```

### With RAG systems

Chunk and embed the files individually — each file is already scoped to a single topic, making them ideal RAG documents without further splitting.

---

## Skills

Skills are **stateless, single-focus system prompts**. Paste one into any LLM conversation to handle a specific Magento task. No tool use required.

| File | Purpose | Source Sections |
|------|---------|----------------|
| [`magento-debug.md`](skills/magento-debug.md) | Diagnose errors from symptoms — symptom → log → root cause → fix | Troubleshooting, Common Pitfalls |
| [`magento-deploy.md`](skills/magento-deploy.md) | Safe deployment with correct command order, maintenance mode rules, checklist | Deployment & CI/CD |
| [`magento-db-schema.md`](skills/magento-db-schema.md) | Declarative schema (`db_schema.xml`) + Model/ResourceModel/Collection scaffold | Database Patterns |
| [`magento-cli-command.md`](skills/magento-cli-command.md) | Custom CLI commands with arguments, progress bars, area-aware execution | CLI Commands |
| [`magento-plugin.md`](skills/magento-plugin.md) | Before/after/around plugins (interceptors) with sort order rules | Core Design Patterns |
| [`magento-observer.md`](skills/magento-observer.md) | Event observers, `events.xml`, common events table, custom dispatch | Core Design Patterns |
| [`magento-test.md`](skills/magento-test.md) | Unit tests with mocks, integration tests with fixtures, test annotations | Testing |
| [`magento-api.md`](skills/magento-api.md) | REST (`webapi.xml`, SearchCriteria, auth) + GraphQL (schema, resolvers, caching) | API Patterns |
| [`magento-hyva.md`](skills/magento-hyva.md) | Hyvä theme — Alpine.js, Tailwind CSS, View Models, GraphQL data fetching | Hyvä Theme |
| [`magento-infra.md`](skills/magento-infra.md) | Redis, RabbitMQ, OpenSearch — `env.php` config, CLI diagnostics, cloud variants | Infrastructure Services |

---

## Agents

Agents are **autonomous, multi-step workflows** for LLMs with tool access (file reads, shell execution). They contain decision trees, diagnostic sequences, and structured output formats. Use with Claude Code, GPT-4o with tools, or any agentic framework.

| File | Purpose | Key Tools Needed |
|------|---------|-----------------|
| [`magento-bug-triage.md`](agents/magento-bug-triage.md) | Classify symptom → check logs → run diagnostics → confirm root cause → produce fix report | Read files, run bash |
| [`magento-deployment.md`](agents/magento-deployment.md) | Validate environment → stop consumers → deploy artifact → DB upgrade → OPcache → restart → smoke test | Run bash, confirm gates |
| [`magento-code-review.md`](agents/magento-code-review.md) | Grep anti-patterns → review each file type → check XML config → rate findings by severity → produce report | Read files, grep |
| [`magento-performance-auditor.md`](agents/magento-performance-auditor.md) | Audit 8 layers: cache, indexers, Redis, OpenSearch, DB, PHP/OPcache, code patterns, queues → prioritised action plan | Run bash, read files |
| [`magento-api-builder.md`](agents/magento-api-builder.md) | From spec: generate service contract → repository → `webapi.xml` → `acl.xml` → GraphQL schema + resolvers | Write files |
| [`magento-module-generator.md`](agents/magento-module-generator.md) | From spec: generate complete module — bootstrap, data layer, REST API, admin UI, CLI, observers, tests | Write files |

---

## Companion Skills

Each agent header lists **companion skills** — the skill files that cover overlapping reference material. The agents are fully self-contained and work without them, but loading a companion alongside gives the LLM deeper reference context.

This is most useful in:
- **Claude Code** — multiple files in the same session context
- **Agentic frameworks** — systems that load multiple system prompts
- **Long or complex tasks** — where the agent may need fine-grained reference detail mid-run

| Agent | Companion Skills |
|-------|-----------------|
| `magento-bug-triage` | `magento-debug` |
| `magento-deployment` | `magento-deploy` |
| `magento-code-review` | `magento-plugin`, `magento-observer`, `magento-db-schema`, `magento-api`, `magento-test` |
| `magento-performance-auditor` | `magento-infra`, `magento-debug` |
| `magento-api-builder` | `magento-api` |
| `magento-module-generator` | `magento-db-schema`, `magento-api`, `magento-cli-command`, `magento-plugin`, `magento-observer`, `magento-test`, `magento-hyva` |

---

## Skills vs Agents — When to Use Which

| Situation | Use |
|-----------|-----|
| "How do I write a plugin?" | Skill: `magento-plugin.md` |
| "Review this module for issues" | Agent: `magento-code-review.md` |
| "My site shows a white page" | Agent: `magento-bug-triage.md` |
| "What's the correct deploy order?" | Skill: `magento-deploy.md` |
| "Deploy to production" | Agent: `magento-deployment.md` |
| "Generate a full module for X" | Agent: `magento-module-generator.md` |
| "Set up Redis in env.php" | Skill: `magento-infra.md` |
| "Why is the store slow?" | Agent: `magento-performance-auditor.md` |
| "Create a REST API for entity X" | Agent: `magento-api-builder.md` |
| "Write a Hyvä Alpine.js component" | Skill: `magento-hyva.md` |

---

## Examples

### Debugging a white page

```
[Paste contents of skills/magento-debug.md]

My Magento store shows a white page after I enabled a new module.
The exception log shows:
  TypeError: Argument 1 passed to Vendor\Module\Model\Service::__construct()
  must be of type Magento\Catalog\Api\ProductRepositoryInterface, null given
```

### Generating a module

```
[Paste contents of agents/module-generator.md]

Create a module called Acme_StoreLocator. It manages store locations
with fields: id, name, address, city, postcode, country_code, lat (decimal),
lng (decimal), phone, is_active. I need a REST API, an admin grid/form,
and a CLI command to import locations from a CSV.
```

### Deploying to production

```
[Paste contents of agents/deployment.md]

Deploy the artifact at /releases/2025-01-20_build.tar.gz to production.
Releases directory: /var/www/magento/releases/
Current symlink: /var/www/magento/current
Process manager: supervisor
```

---

## File Structure

```
magento-ai-toolkit/
├── README.md
├── package.json
├── skills/
│   ├── magento-debug.md
│   ├── magento-deploy.md
│   ├── magento-db-schema.md
│   ├── magento-cli-command.md
│   ├── magento-plugin.md
│   ├── magento-observer.md
│   ├── magento-test.md
│   ├── magento-api.md
│   ├── magento-hyva.md
│   └── magento-infra.md
├── agents/
│   ├── magento-bug-triage.md
│   ├── magento-deployment.md
│   ├── magento-code-review.md
│   ├── magento-performance-auditor.md
│   ├── magento-api-builder.md
│   └── magento-module-generator.md
└── tests/
    ├── promptfooconfig.yaml              # Root orchestrator (imports all configs)
    ├── providers.yaml                    # Shared: claude-sonnet-4-6 + gpt-4o at temp=0
    ├── defaultTest.yaml                  # Shared: latency cap + non-empty output guard
    ├── prompts/
    │   ├── skill-wrapper.json            # {system, user} message array for skills
    │   └── agent-wrapper.json            # Identical shape, separate for future divergence
    ├── skills/
    │   ├── magento-plugin.yaml           # 5 tests
    │   ├── magento-db-schema.yaml        # 5 tests
    │   └── magento-debug.yaml            # 5 tests
    └── agents/
        ├── magento-bug-triage.yaml       # 5 tests
        └── magento-code-review.yaml      # 5 tests
```

---

## Coverage

These files were distilled from a comprehensive Magento 2 reference document covering:

- Mage-OS distribution and differences from Magento Open Source
- Directory structure and module layout
- Core design patterns — DI, plugins, observers, service contracts, EAV
- All configuration file types (`di.xml`, `routes.xml`, `events.xml`, `db_schema.xml`, etc.)
- Frontend architecture — Layout XML, blocks, UI components, RequireJS, KnockoutJS
- Hyvä theme — Alpine.js, Tailwind CSS, View Models, GraphQL
- REST and GraphQL API patterns
- Database — declarative schema, Model/ResourceModel/Collection, SearchCriteria
- Full CLI command reference (setup, cache, indexer, module, cron, queue, etc.)
- Infrastructure — Redis, RabbitMQ, OpenSearch/Elasticsearch (self-hosted and cloud)
- Best practices 2025+ (PHP 8.3+, OpenSearch 2.x, Mage-OS 2.x)
- Common pitfalls and gotchas
- Adobe Commerce (EE) exclusive features
- Testing — unit, integration, API functional, MFTF
- Deployment and CI/CD patterns

---

## Compatibility

| Component | Version |
|-----------|---------|
| Magento Open Source | 2.4.8+ |
| Mage-OS | 2.x (based on Magento 2.4.8-p3) |
| Adobe Commerce | 2.4.8+ |
| PHP | 8.3 / 8.4 |
| OpenSearch | 2.x |
| MySQL | 8.0+ / MariaDB 10.6+ |
| Redis | 7.0+ |
| Composer | 2.x |

---

## Testing

The test suite uses [promptfoo](https://promptfoo.dev) to validate each skill and agent against both Claude and GPT-4o. 25 test cases × 2 providers = ~50 API calls per run.

### Prerequisites

```bash
node >= 18
ANTHROPIC_API_KEY=...
OPENAI_API_KEY=...    # optional — omit to run single-provider with Claude only
```

### Run commands

```bash
npm test                  # all 25 cases, both providers (~50 API calls)
npm run test:skills       # skills only
npm run test:agents       # agents only
npm run test:plugin       # iterate on one skill during authoring
npm run test:db-schema
npm run test:debug
npm run test:bug-triage
npm run test:code-review
npm run test:ci           # outputs results.json for CI artefacts
npm run test:view         # open web UI to browse results
```

### Test design

Each sub-config (`tests/skills/*.yaml`, `tests/agents/*.yaml`) is fully self-contained and loads the corresponding `.md` file as the system prompt via `file://`.

Two assertion types per test case:

- **Deterministic** (`contains`, `not-contains`, `regex`) — structural markers explicitly required by the skill. No extra API calls.
- **LLM-as-judge** (`llm-rubric`) — behavioural correctness: did it follow the workflow, avoid the anti-pattern? One extra API call per assertion.

Non-negotiable assertions enforced on every test in a config via `defaultTest`:

| Config | Enforced assertion |
|--------|--------------------|
| `magento-plugin` | `ObjectManager::getInstance` never appears |
| `magento-db-schema` | `InstallSchema` and `UpgradeSchema` never appear |
| `magento-bug-triage` | `## Bug Triage Report` always present |
| `magento-code-review` | `## Code Review Report` always present |

### Adding tests for the remaining 11 files

Follow the same 5-test shape used in the existing configs:

1. Happy path — assert core structural markers
2. Type-reference edge case (the rule easiest to get wrong)
3. Multi-artefact output — assert all pieces present
4. Anti-pattern avoidance — `not-contains` for the deprecated approach
5. Command sequence — assert the documented follow-up commands appear

Then add the new file to `tests/promptfooconfig.yaml` under `import:`.

---

## Contributing

These files are designed to be updated as Magento/Mage-OS evolves. When updating:

- Keep each file focused on one topic — resist adding cross-topic content
- Test the system prompt against real tasks before committing
- Note breaking changes in skill/agent instructions (e.g. deprecated commands)
- Update the version compatibility table when targeting a new Magento release
