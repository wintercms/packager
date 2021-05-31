<?php namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Commands\Traits\RunsComposer;
use BennoThommo\Packager\Exceptions\CommandException;

class Version extends BaseCommand
{
    use RunsComposer;

    public function execute(): bool
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            throw new CommandException('Unable to retrieve the Composer version.');
        }

        // Find version line
        foreach ($output['output'] as $line) {
            if (preg_match('/^Composer ([0-9\.]+)/i', $line, $matches)) {
                return $matches[1];
            }
        }

        throw new CommandException('Unable to retrieve the Composer version.');
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function requiresWorkDir(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function arguments(): array
    {
        return [
            '--version'
        ];
    }
}
