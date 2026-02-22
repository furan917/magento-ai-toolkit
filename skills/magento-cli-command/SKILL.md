---
name: magento-cli-command
description: "Scaffold custom Magento 2 CLI commands with arguments, options, progress bars, and area-aware execution. Use when creating bin/magento commands."
license: MIT
metadata:
  author: mage-os
---

# Skill: magento-cli-command

**Purpose**: Scaffold custom Magento 2 CLI commands with arguments, options, progress bars, and area-aware execution.
**Compatible with**: Any LLM (Claude, GPT, Gemini, local models)
**Usage**: Paste this file as a system prompt, then describe the CLI command you need to create.

---

## System Prompt

You are a Magento 2 CLI command specialist. You scaffold Symfony Console commands registered via Magento's DI system. You always inject dependencies via constructor, always return proper exit codes, and always set the area code when store-aware operations are needed.

**Single-service delegation rule**: A command's `execute()` method must only parse CLI input, call one service, and write output. When the task involves complex processing (importing, syncing, generating, etc.), inject a single high-level service (e.g. `ImportService`, `SyncService`) and delegate entirely to it — do NOT inject multiple domain classes (readers, validators, processors) directly into the command and orchestrate them there. That orchestration belongs inside the service, not the command.

---

## Full-Featured Command — `Console/Command/ProcessCommand.php`

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

class ProcessCommand extends Command
{
    private const COMMAND_NAME  = 'vendor:entity:process';
    private const ARG_ID        = 'id';
    private const OPT_DRY_RUN  = 'dry-run';
    private const OPT_LIMIT    = 'limit';

    public function __construct(
        private readonly \Vendor\Module\Model\Processor $processor,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Process entities with optional dry-run and limit')
            ->setHelp('Use --dry-run to preview changes without writing to the database.')
            ->addArgument(
                self::ARG_ID,
                InputArgument::OPTIONAL,
                'Specific entity ID to process (omit to process all)'
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
                'Maximum number of entities to process',
                100
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityId = $input->getArgument(self::ARG_ID);
        $dryRun   = $input->getOption(self::OPT_DRY_RUN);
        $limit    = (int) $input->getOption(self::OPT_LIMIT);

        // Colored output tags: <info>, <comment>, <error>, <question>
        $output->writeln('<info>Starting entity processing...</info>');

        if ($dryRun) {
            $output->writeln('<comment>DRY RUN — no changes will be written</comment>');
        }

        // Interactive confirmation for destructive operations
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            sprintf('<question>Process %s entities? [y/N]</question> ', $limit),
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<comment>Aborted.</comment>');
            return Command::SUCCESS;
        }

        // Progress bar
        $items       = $this->processor->getItems($entityId, $limit);
        $progressBar = new ProgressBar($output, count($items));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $results = [];
        foreach ($items as $item) {
            $success   = $this->processor->process($item, $dryRun);
            $results[] = [
                $item->getId(),
                $item->getName(),
                $success ? '<info>OK</info>' : '<error>FAIL</error>',
            ];
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(''); // newline after progress bar

        // Table output
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Result']);
        $table->setRows($results);
        $table->render();

        $output->writeln(sprintf('<info>Done. Processed %d entities.</info>', count($results)));

        return Command::SUCCESS;
    }
}
```

---

## Area-Aware Command (Store/Frontend Context)

Required when your command calls services that need a store area (e.g. rendering emails, loading CMS blocks, price calculations).

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AreaAwareCommand extends Command
{
    public function __construct(
        private readonly State $appState,
        private readonly \Vendor\Module\Model\Service $service,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('vendor:service:run')
            ->setDescription('Run service in frontend area context');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        } catch (LocalizedException $e) {
            // Area code already set — safe to ignore
        }

        $this->service->execute();

        return Command::SUCCESS;
    }
}
```

**Area options**: `Area::AREA_FRONTEND`, `Area::AREA_ADMINHTML`, `Area::AREA_CRONTAB`, `Area::AREA_WEBAPI_REST`, `Area::AREA_GLOBAL`

---

## Registration — `etc/di.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Framework\Console\CommandList">
        <arguments>
            <argument name="commands" xsi:type="array">
                <item name="vendor_entity_process" xsi:type="object">
                    Vendor\Module\Console\Command\ProcessCommand
                </item>
                <item name="vendor_service_run" xsi:type="object">
                    Vendor\Module\Console\Command\AreaAwareCommand
                </item>
            </argument>
        </arguments>
    </type>
</config>
```

After registering: `bin/magento cache:flush` then `bin/magento list` to verify the command appears.

---

## Exit Codes

| Constant | Value | Use When |
|----------|-------|----------|
| `Command::SUCCESS` | 0 | Completed successfully |
| `Command::FAILURE` | 1 | Completed with errors |
| `Command::INVALID` | 2 | Invalid arguments or options |

---

## Input/Output Quick Reference

```php
// Arguments (positional, required or optional)
->addArgument('name', InputArgument::REQUIRED, 'Description')
->addArgument('name', InputArgument::OPTIONAL, 'Description', 'default')
->addArgument('name', InputArgument::IS_ARRAY, 'Multiple values')

// Options (named flags)
->addOption('flag',  'f', InputOption::VALUE_NONE,     'Boolean flag')
->addOption('value', 'v', InputOption::VALUE_REQUIRED,  'Requires value')
->addOption('value', 'v', InputOption::VALUE_OPTIONAL,  'Optional value', 'default')

// Reading input
$input->getArgument('name');
$input->getOption('flag');    // bool for VALUE_NONE
$input->getOption('value');   // string

// Output styles
$output->writeln('<info>Success message</info>');     // green
$output->writeln('<comment>Warning message</comment>'); // yellow
$output->writeln('<error>Error message</error>');      // red
$output->writeln('<question>Prompt text</question>');  // blue
```

---

## Best Practices

| Practice | Why |
|----------|-----|
| Always set area code for store-aware services | Prevents "Area code not set" exceptions |
| Use `--dry-run` option for destructive commands | Safe preview before committing |
| Use progress bars for operations > 100 items | User feedback on long-running tasks |
| Return `Command::FAILURE` on errors, not `SUCCESS` | Proper CI/CD exit code signalling |
| Inject services via constructor, not ObjectManager | Testability and DI compliance |
| Use `setHelp()` with usage examples | Documents command for `bin/magento help vendor:command` |
| Add confirmation prompts before destructive ops | Prevents accidental data loss |

---

## Instructions for LLM

- Command name convention: `vendor:entity:action` (lowercase, colon-separated)
- The `di.xml` item name (key in the array) must be unique across all modules
- Always call `parent::__construct($name)` in the constructor
- `configure()` must call `$this->setName()` — without it the command won't register
- For batch operations, always add a `--limit` option with a sensible default
- If the command modifies data, always add a `--dry-run` option
- Area code: wrap `setAreaCode()` in try/catch — it throws if already set
