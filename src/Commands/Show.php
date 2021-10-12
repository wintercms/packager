<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Exceptions\CommandException;
use Winter\Packager\Parser\VersionOutputParser;

/**
 * Show command.
 *
 * Runs "composer show" within PHP.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
class Show extends BaseCommand
{
    /**
     * @var string Mode to run the command against
     */
    public $mode = 'installed';

    /**
     * @var string Individual package to search
     */
    public $package;

    /**
     * @var string Exclude dev dependencies from search
     */
    public $noDev;

    public function handle(?string $mode = 'installed', string $package = null, bool $noDev = false)
    {
        $mode = $mode ?? 'installed';

        $validModes = [
            'installed',
            'locked',
            'platform',
            'available',
            'self',
            'path',
            'tree',
            'outdated',
            'direct'
        ];

        if (!in_array(strtolower($mode), $validModes)) {
            throw new CommandException(
                sprintf(
                    'Invalid mode, must be one of the following: %s',
                    implode(', ', $validModes)
                )
            );
        }

        $this->mode = $mode;
        $this->package = $package;
        $this->noDev = $noDev;
    }

    public function execute()
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            if (!empty($this->package)) {
                throw new CommandException(
                    sprintf(
                        'Package %s not found',
                        $this->package
                    )
                );
            } else {
                throw new CommandException(implode(PHP_EOL, $output['output']));
            }
        }

        return json_decode(implode(PHP_EOL, $output['output']), true);
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'show';
    }

    /**
     * @inheritDoc
     */
    public function requiresWorkDir(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function arguments(): array
    {
        $arguments = [];

        if (!empty($this->package)) {
            $arguments['package'] = $this->package;
        }

        if ($this->mode !== 'installed') {
            $arguments['--' . $this->mode] = true;
        }

        if ($this->noDev) {
            $arguments['--no-dev'] = true;
        }

        $arguments['--format'] = 'json';

        return $arguments;
    }
}
