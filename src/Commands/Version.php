<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Exceptions\CommandException;
use Winter\Packager\Parser\VersionOutputParser;

/**
 * Version command.
 *
 * Runs "composer --version" within PHP.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
class Version extends BaseCommand
{
    /**
     * Command constructor.
     *
     * Prepares the details of the Composer version.
     *
     * Detail can be one of the following:
     *  - `all`: Get all details
     *  - `version`: Get only the version number
     *  - `date`: Get the build date
     *  - `dateTime`: Get the build date and time
     */
    final public function __construct(
        Composer $composer,
        protected string $detail = 'version'
    ) {
        parent::__construct($composer);
    }

    /**
     * @inheritDoc
     *
     * @return array<string, string>|string
     */
    public function execute()
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            throw new CommandException('Unable to retrieve the Composer version.');
        }

        $parser = new VersionOutputParser;
        $version = $parser->parse($output['output']);

        if (!$version) {
            throw new CommandException('Unable to retrieve the Composer version.');
        }

        switch ($this->detail) {
            case 'version':
                return $version['version'];
            case 'date':
                return $version['date'];
            case 'dateTime':
                return $version['date'] . ' ' . $version['time'];
            default:
                return $version;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return '';
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
        return [
            '--version'
        ];
    }
}
