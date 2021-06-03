<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Exceptions\CommandException;
use BennoThommo\Packager\Parser\VersionParser;

class Version extends BaseCommand
{
    /**
     * @var string The detail to return. Valid values: "version", "date", "dateTime", "all"
     */
    protected $detail = 'version';

    public function handle(string $detail = 'version')
    {
        $this->detail = (in_array($detail, ['version', 'date', 'dateTime', 'all']))
            ? $detail
            : 'version';
    }

    public function execute()
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            throw new CommandException('Unable to retrieve the Composer version.');
        }

        $parser = new VersionParser;
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
