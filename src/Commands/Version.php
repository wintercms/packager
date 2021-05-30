<?php namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Commands\Traits\RunsComposer;
use BennoThommo\Packager\Exceptions\CommandException;

class Version implements Command
{
    use RunsComposer;

    public function execute(): bool
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            throw new CommandException('Unable to retrieve the Composer version.');
        }

        // Find version line
        foreach ($output as $line) {
            if (preg_match('/^Composer ([0-9\.]+)/i', $line, $matches)) {
                return $matches[1];
            }
        }

        throw new CommandException('Unable to retrieve the Composer version.');
    }

    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return 'version';
    }

    /**
     * @inheritDoc
     */
    protected function requiresWorkDir(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function arguments(): array
    {
        return [];
    }
}
