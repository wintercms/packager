<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Exceptions\CommandException;
use Winter\Packager\Exceptions\WorkDirException;

class RequireCommand extends BaseCommand
{
    /**
     * Command constructor.
     */
    final public function __construct(
        protected Composer $composer,
        protected string $package,
        protected bool $dryRun = false,
        protected bool $dev = false
    ) {
        parent::__construct($composer);
    }

    /**
     * @inheritDoc
     */
    public function arguments(): array
    {
        $arguments = [];

        if ($this->dryRun) {
            $arguments['--dry-run'] = true;
        }

        if ($this->dev) {
            $arguments['--dev'] = true;
        }

        $arguments['packages'] = [$this->package];

        return $arguments;
    }

    /**
     * @throws CommandException
     * @throws WorkDirException
     */
    public function execute(): string
    {
        $output = $this->runComposerCommand();
        $message = implode(PHP_EOL, $output['output']);

        if ($output['code'] !== 0) {
            throw new CommandException($message);
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    protected function requiresWorkDir(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'require';
    }
}
