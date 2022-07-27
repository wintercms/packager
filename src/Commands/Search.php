<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Exceptions\CommandException;

/**
 * Search command.
 *
 * Runs "composer search" within PHP.
 *
 * @author Ben Thomson
 * @since 0.2.0
 */
class Search extends BaseCommand
{
    /**
     * The search query to find packages.
     */
    public string $query;

    /**
     * The type of package to search for.
     */
    public ?string $type = null;

    /**
     * Limit the search parameters. This can be one of the following:
     *
     *  - `name`: Search and return package names only
     *  - `vendor`: Search and return vendors only
     *
     * @var string|null
     */
    public ?string $limitTo = null;

    /**
     * @var array<int, mixed> The results returned from the query.
     */
    public array $results = [];

    /**
     * Command handler.
     */
    public function handle(
        string $query,
        ?string $type = null,
        bool $onlyNames = false,
        bool $onlyVendors = false
    ): void {
        $this->query = $query;
        $this->type = $type;

        if ($onlyNames) {
            $this->limitTo = 'name';
        } elseif ($onlyVendors) {
            $this->limitTo = 'vendor';
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            throw new CommandException(implode(PHP_EOL, $output['output']));
        }

        $this->results = json_decode(implode(PHP_EOL, $output['output']), true);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'search';
    }

    /**
     * @inheritDoc
     */
    public function requiresWorkDir(): bool
    {
        return false;
    }

    /**
     * Returns the list of results found.
     *
     * @return array<int, mixed>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Returns the number of results found.
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * @inheritDoc
     */
    public function arguments(): array
    {
        $arguments = [];

        if (!empty($this->type)) {
            $arguments['--type'] = $this->type;
        }

        if ($this->limitTo === 'name') {
            $arguments['--only-name'] = true;
        } elseif ($this->limitTo === 'vendor') {
            $arguments['--only-vendor'] = true;
        }

        $arguments['--format'] = 'json';

        $arguments['tokens'] = preg_split('/ +/', $this->query, -1, PREG_SPLIT_NO_EMPTY);

        return $arguments;
    }
}
