<?php

namespace Winter\Packager\Commands;

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
     * @var string The detail to return. Valid values: "version", "date", "dateTime", "all"
     */
    protected $detail = 'version';

    /**
     * Command handler.
     *
     * Prepares the details of the Composer version.
     *
     * Detail can be one of the following:
     *  - `all`: Get all details
     *  - `version`: Get only the version number
     *  - `date`: Get the build date
     *  - `dateTime`: Get the build date and time
     */
    public function handle(string $detail = 'version'): void
    {
        $this->detail = (in_array($detail, ['version', 'date', 'dateTime', 'all']))
            ? $detail
            : 'version';
    }

    /**
     * @inheritDoc
     *
     * @return array<string, string>|string
     */
    public function execute(): array|string
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
