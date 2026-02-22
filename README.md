# magento-ai-toolkit

A library of LLM-ready skills and agents for Magento 2 / Mage-OS development, debugging, and hosting. Each file is a self-contained system prompt — paste it into any LLM to instantly give it deep Magento expertise.

> **Magento version**: 2.4.8+ | **Mage-OS**: 2.x | **PHP**: 8.3+ | **OpenSearch**: 2.x

---

## Quick Start

```bash
git clone https://github.com/furan917/magento-ai-toolkit.git
cd magento-ai-toolkit

# Install into Claude Code
cp -r skills/* ~/.claude/skills/
cp -r agent-skills/* ~/.claude/skills/
cp subagents/*.md ~/.claude/agents/
```

Skills and agent-skills appear in Claude Code's `/` menu. Subagents are auto-delegated by Claude when the task matches — no slash command needed.

---

## How to Use

### With Claude Code — skills (slash commands)

After installing (see Quick Start above), use skills directly in any conversation:

```
/magento-debug       → debug skill as system prompt
/magento-plugin      → plugin skill as system prompt
/magento-agent-code-review → code-review agent skill
```

Subagents run with an isolated context window and are auto-delegated based on task description.

### With Claude Code — CLAUDE.md

Add a skill to `CLAUDE.md` to keep it active for the entire project session:

```markdown
<!-- CLAUDE.md -->
<system_prompt>
  <!-- paste contents of skills/magento-plugin/SKILL.md here -->
</system_prompt>
```

### With any chat LLM (Claude, ChatGPT, Gemini, etc.)

1. Open the relevant `SKILL.md` file
2. Copy the entire contents
3. Paste it as a **system prompt** or at the top of your conversation
4. Ask your question or describe your task

### With OpenAI API / custom tooling

```python
with open("magento-ai-toolkit/skills/magento-deploy/SKILL.md") as f:
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

Chunk and embed the `SKILL.md` files individually — each file is already scoped to a single topic, making them ideal RAG documents without further splitting.

---

## File Structure

Three tiers, each with a distinct purpose:

```
magento-ai-toolkit/
├── README.md
├── package.json
├── skills/                               # Stateless reference skills (Agent Skills format)
│   ├── magento-api/SKILL.md
│   ├── magento-cli-command/SKILL.md
│   ├── magento-db-schema/SKILL.md
│   ├── magento-debug/SKILL.md
│   ├── magento-deploy/SKILL.md
│   ├── magento-hyva/SKILL.md
│   ├── magento-infra/SKILL.md
│   ├── magento-observer/SKILL.md
│   ├── magento-plugin/SKILL.md
│   └── magento-test/SKILL.md
├── agent-skills/                         # Multi-step agent workflows (Agent Skills format)
│   ├── magento-agent-api-builder/SKILL.md
│   ├── magento-agent-bug-triage/SKILL.md
│   ├── magento-agent-code-review/SKILL.md
│   ├── magento-agent-deployment/SKILL.md
│   ├── magento-agent-module-generator/SKILL.md
│   └── magento-agent-performance-auditor/SKILL.md
├── subagents/                            # Claude Code native subagents (isolated context)
│   ├── magento-agent-api-builder.md
│   ├── magento-agent-bug-triage.md
│   ├── magento-agent-code-review.md
│   ├── magento-agent-deployment.md
│   ├── magento-agent-module-generator.md
│   └── magento-agent-performance-auditor.md
├── snippets/                             # Copy-pasteable XML/PHP stubs
│   ├── di.xml
│   ├── routes.xml
│   ├── acl.xml
│   ├── events.xml
│   ├── db_schema.xml
│   ├── webapi.xml
│   ├── crontab.xml
│   ├── module.xml
│   └── registration.php
├── checklists/                           # Human-run workflow gates
│   ├── pre-deploy.md
│   ├── new-module.md
│   └── pr-review.md
└── tests/
    ├── promptfooconfig.yaml              # Root orchestrator (imports all 16 configs)
    ├── providers.yaml                    # Shared: claude-sonnet-4-6 + gpt-4o at temp=0
    ├── prompts/
    │   ├── skill-wrapper.json
    │   └── agent-wrapper.json
    ├── skills/                           # 10 configs × 5 tests = 50 tests
    │   ├── magento-plugin.yaml
    │   ├── magento-db-schema.yaml
    │   ├── magento-debug.yaml
    │   ├── magento-observer.yaml
    │   ├── magento-deploy.yaml
    │   ├── magento-cli-command.yaml
    │   ├── magento-test.yaml
    │   ├── magento-api.yaml
    │   ├── magento-hyva.yaml
    │   └── magento-infra.yaml
    └── agents/                           # 6 configs × 5 tests = 30 tests
        ├── magento-bug-triage.yaml
        ├── magento-code-review.yaml
        ├── magento-deployment.yaml
        ├── magento-performance-auditor.yaml
        ├── magento-api-builder.yaml
        └── magento-module-generator.yaml
```

---

## Skills

Skills are **stateless, single-focus system prompts**. Paste one into any LLM conversation to handle a specific Magento task. No tool use required.

These follow the [Agent Skills open standard](https://agentskills.io) — compatible with Claude Code, Gemini CLI, OpenCode, Cursor, GitHub Copilot, and 26+ other platforms.

| Skill | Purpose |
|-------|---------|
| [`magento-debug`](skills/magento-debug/SKILL.md) | Diagnose errors from symptoms — symptom → log → root cause → fix |
| [`magento-deploy`](skills/magento-deploy/SKILL.md) | Safe deployment with correct command order, maintenance mode rules, checklist |
| [`magento-db-schema`](skills/magento-db-schema/SKILL.md) | Declarative schema (`db_schema.xml`) + Model/ResourceModel/Collection scaffold |
| [`magento-cli-command`](skills/magento-cli-command/SKILL.md) | Custom CLI commands with arguments, progress bars, area-aware execution |
| [`magento-plugin`](skills/magento-plugin/SKILL.md) | Before/after/around plugins (interceptors) with sort order rules |
| [`magento-observer`](skills/magento-observer/SKILL.md) | Event observers, `events.xml`, common events table, custom dispatch |
| [`magento-test`](skills/magento-test/SKILL.md) | Unit tests with mocks, integration tests with fixtures, test annotations |
| [`magento-api`](skills/magento-api/SKILL.md) | REST (`webapi.xml`, SearchCriteria, auth) + GraphQL (schema, resolvers, caching) |
| [`magento-hyva`](skills/magento-hyva/SKILL.md) | Hyvä theme — Alpine.js, Tailwind CSS, View Models, GraphQL data fetching |
| [`magento-infra`](skills/magento-infra/SKILL.md) | Redis, RabbitMQ, OpenSearch — `env.php` config, CLI diagnostics, cloud variants |

---

## Agent Skills

Agent skills are **autonomous, multi-step workflows** for LLMs with tool access (file reads, shell execution). They contain decision trees, diagnostic sequences, and structured output formats.

These also follow the [Agent Skills open standard](https://agentskills.io) — the same `SKILL.md` format, with richer orchestration instructions. Use them with Claude Code, GPT-4o with tools, or any agentic framework.

| Agent Skill | Purpose | Key Tools Needed |
|-------------|---------|-----------------|
| [`magento-agent-bug-triage`](agent-skills/magento-agent-bug-triage/SKILL.md) | Classify symptom → check logs → run diagnostics → confirm root cause → produce fix report | Read files, run bash |
| [`magento-agent-deployment`](agent-skills/magento-agent-deployment/SKILL.md) | Validate environment → stop consumers → deploy artifact → DB upgrade → OPcache → restart → smoke test | Run bash, confirm gates |
| [`magento-agent-code-review`](agent-skills/magento-agent-code-review/SKILL.md) | Grep anti-patterns → review each file type → check XML config → rate findings by severity → produce report | Read files, grep |
| [`magento-agent-performance-auditor`](agent-skills/magento-agent-performance-auditor/SKILL.md) | Audit 8 layers: cache, indexers, Redis, OpenSearch, DB, PHP/OPcache, static assets, queues → prioritised action plan | Run bash, read files |
| [`magento-agent-api-builder`](agent-skills/magento-agent-api-builder/SKILL.md) | From spec: generate service contract → repository → `webapi.xml` → `acl.xml` → GraphQL schema + resolvers | Write files |
| [`magento-agent-module-generator`](agent-skills/magento-agent-module-generator/SKILL.md) | From spec: generate complete module — bootstrap, data layer, REST API, admin UI, CLI, observers, tests | Write files |

---

## Claude Code Subagents

The `subagents/` directory contains **Claude Code native agents** — single `.md` files with `name`, `description`, `tools`, and `model` frontmatter. When installed to `~/.claude/agents/`, Claude automatically delegates tasks to them based on their description. Each runs with an **isolated context window**, preventing context pollution between long-running agent tasks.

```
subagents/
├── magento-agent-api-builder.md       # auto-delegated for API/GraphQL generation tasks
├── magento-agent-bug-triage.md        # auto-delegated for debugging/error analysis
├── magento-agent-code-review.md       # auto-delegated for code review requests
├── magento-agent-deployment.md        # auto-delegated for deployment tasks
├── magento-agent-module-generator.md  # auto-delegated for module scaffolding
└── magento-agent-performance-auditor.md  # auto-delegated for performance audits
```

These are equivalent in content to the agent-skills but formatted for Claude Code's native agent system. Install both if you want cross-CLI compatibility (agent-skills) **and** Claude's native auto-delegation (subagents).

---

## Skills vs Agent Skills vs Subagents — When to Use Which

| Situation | Use |
|-----------|-----|
| "How do I write a plugin?" | Skill: `magento-plugin` |
| "Review this module for issues" | Agent skill / Subagent: `magento-agent-code-review` |
| "My site shows a white page" | Agent skill / Subagent: `magento-agent-bug-triage` |
| "What's the correct deploy order?" | Skill: `magento-deploy` |
| "Deploy to production" | Agent skill / Subagent: `magento-agent-deployment` |
| "Generate a full module for X" | Agent skill / Subagent: `magento-agent-module-generator` |
| "Set up Redis in env.php" | Skill: `magento-infra` |
| "Why is the store slow?" | Agent skill / Subagent: `magento-agent-performance-auditor` |
| "Create a REST API for entity X" | Agent skill / Subagent: `magento-agent-api-builder` |
| "Write a Hyvä Alpine.js component" | Skill: `magento-hyva` |

| Format | Best for | Tool use? | Context |
|--------|----------|-----------|---------|
| `skills/` | Quick reference, any LLM, RAG | No | Shared |
| `agent-skills/` | Agentic workflows, cross-CLI compatibility | Yes | Shared |
| `subagents/` | Claude Code only, auto-delegation | Yes | Isolated |

---

## Companion Skills

Each agent skill lists **companion skills** — skills that cover overlapping reference material. The agent skills are fully self-contained, but loading a companion alongside gives the LLM deeper reference context for complex tasks.

| Agent Skill | Companion Skills |
|-------------|-----------------|
| `magento-agent-bug-triage` | `magento-debug` |
| `magento-agent-deployment` | `magento-deploy` |
| `magento-agent-code-review` | `magento-plugin`, `magento-observer`, `magento-db-schema`, `magento-api`, `magento-test` |
| `magento-agent-performance-auditor` | `magento-infra`, `magento-debug` |
| `magento-agent-api-builder` | `magento-api` |
| `magento-agent-module-generator` | `magento-db-schema`, `magento-api`, `magento-cli-command`, `magento-plugin`, `magento-observer`, `magento-test`, `magento-hyva` |

---

## Examples

### Debugging a white page

```
[Paste contents of skills/magento-debug/SKILL.md]

My Magento store shows a white page after I enabled a new module.
The exception log shows:
  TypeError: Argument 1 passed to Vendor\Module\Model\Service::__construct()
  must be of type Magento\Catalog\Api\ProductRepositoryInterface, null given
```

### Generating a module

```
[Paste contents of agent-skills/magento-agent-module-generator/SKILL.md]

Create a module called Acme_StoreLocator. It manages store locations
with fields: id, name, address, city, postcode, country_code, lat (decimal),
lng (decimal), phone, is_active. I need a REST API, an admin grid/form,
and a CLI command to import locations from a CSV.
```

### Deploying to production

```
[Paste contents of agent-skills/magento-agent-deployment/SKILL.md]

Deploy the artifact at /releases/2025-01-20_build.tar.gz to production.
Releases directory: /var/www/magento/releases/
Current symlink: /var/www/magento/current
Process manager: supervisor
```

---

## Snippets

The `snippets/` directory contains plain XML and PHP stubs — not system prompts. They are copy-pasteable starting points for the most commonly forgotten file structures, with inline comments explaining every attribute.

| File | Contents |
|------|----------|
| [`di.xml`](snippets/di.xml) | Plugin, preference, scalar/object/array type arguments, virtualType |
| [`routes.xml`](snippets/routes.xml) | Frontend (`router id="standard"`) and adminhtml route declarations |
| [`acl.xml`](snippets/acl.xml) | ACL resource tree nested under `Magento_Backend::admin` |
| [`events.xml`](snippets/events.xml) | Observer registration with `shared="false"`, common events table |
| [`db_schema.xml`](snippets/db_schema.xml) | All column types, primary key, FK with naming convention, composite/fulltext indexes |
| [`webapi.xml`](snippets/webapi.xml) | Full CRUD routes, anonymous endpoint, customer self endpoint, auth examples |
| [`crontab.xml`](snippets/crontab.xml) | Default + custom group jobs, `cron_groups.xml`, schedule expression reference |
| [`module.xml`](snippets/module.xml) | Module declaration with sequence dependencies |
| [`registration.php`](snippets/registration.php) | `ComponentRegistrar::register()` with naming rules |

---

## Checklists

Human-run workflow gates in `checklists/`. Use these at key project milestones — they encode the same rules the skills and agent skills enforce, in checklist form for engineers to verify manually.

| File | When to use |
|------|-------------|
| [`pre-deploy.md`](checklists/pre-deploy.md) | Before every production deployment — 6 sections covering code, DB, config, staging, rollback, window |
| [`new-module.md`](checklists/new-module.md) | When scaffolding a new module — 7 phases from skeleton to final checks |
| [`pr-review.md`](checklists/pr-review.md) | When reviewing a PR — blockers, architecture, code quality, tests, migrations |

---

## Testing

The test suite uses [promptfoo](https://promptfoo.dev) to validate all 16 skills and agent skills against Claude and GPT-4o. 80 test cases × 2 providers = ~160 API calls per full run.

### Prerequisites

```bash
node >= 18
ANTHROPIC_API_KEY=...
OPENAI_API_KEY=...    # optional — omit to run single-provider with Claude only
```

### Run commands

```bash
# All 80 cases, both providers (~160 API calls)
npm test

# By category
npm run test:skills           # all 10 skill configs
npm run test:agents           # all 6 agent skill configs

# Per-file (fast iteration during authoring)
npm run test:plugin
npm run test:db-schema
npm run test:debug
npm run test:observer
npm run test:deploy
npm run test:cli-command
npm run test:test
npm run test:api
npm run test:hyva
npm run test:infra
npm run test:bug-triage
npm run test:code-review
npm run test:deployment
npm run test:performance
npm run test:api-builder
npm run test:module-generator

# CI / reporting
npm run test:ci               # outputs results-skills.json + results-agents.json
npm run test:view             # open web UI to browse results

# Single-provider (Anthropic only)
ANTHROPIC_API_KEY=... npm run test:skills -- --filter-providers "anthropic"
ANTHROPIC_API_KEY=... npm run test:agents -- --filter-providers "anthropic"
```

### Test design

Each sub-config (`tests/skills/*.yaml`, `tests/agents/*.yaml`) loads the corresponding `SKILL.md` as the system prompt via `file://`.

Two assertion types per test case:

- **Deterministic** (`contains`, `not-contains`, `regex`) — structural markers explicitly required by the skill. No extra API calls.
- **LLM-as-judge** (`llm-rubric`) — behavioural correctness: did it follow the workflow, avoid the anti-pattern? One extra API call per assertion.

Non-negotiable assertions enforced on every test in a config via `defaultTest`:

| Config | Enforced assertion |
|--------|--------------------|
| `magento-plugin` | `ObjectManager::getInstance` never appears; `declare(strict_types=1)` always present |
| `magento-db-schema` | `InstallSchemaInterface` and `UpgradeSchemaInterface` never appear |
| `magento-debug` | `edit vendor/` never appears |
| `magento-observer` | `shared="true"` never appears; `ObjectManager::getInstance` never appears |
| `magento-deploy` | `rm -rf generated` never appears; `setup:di:compile` never appears (build phase only) |
| `magento-cli-command` | `declare(strict_types=1)` always present; `ObjectManager::getInstance` never appears |
| `magento-test` | `ObjectManager::getInstance` never appears |
| `magento-api` | `ObjectManager::getInstance` never appears |
| `magento-bug-triage` | `## Bug Triage Report` always present |
| `magento-code-review` | `## Code Review Report` always present |
| `magento-deployment` | `## Deployment Report` always present; `rm -rf generated` never appears |
| `magento-performance-auditor` | `## Performance Audit Report` always present |
| `magento-api-builder` | `## API Builder` always present; `ObjectManager::getInstance` never appears |
| `magento-module-generator` | `InstallSchemaInterface`, `UpgradeSchemaInterface`, `ObjectManager::getInstance` never appear |

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

## Contributing

These files are designed to be updated as Magento/Mage-OS evolves. When updating:

- Keep each file focused on one topic — resist adding cross-topic content
- Test the system prompt against real tasks before committing
- Note breaking changes in skill/agent instructions (e.g. deprecated commands)
- Update the version compatibility table when targeting a new Magento release
