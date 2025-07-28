<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Enums\SearchLimitTo;
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
     * Command constructor.
     *
     * @param Composer $composer
     * @param string $query The search query to find packages.
     * @param string|null $type The type of package to search for.
     * @param SearchLimitTo $limitTo Limit the returned results.
     * @param bool $returnArray Return the search results as an array.
     */
    final public function __construct(
        Composer $composer,
        public string $query,
        public ?string $type = null,
        public SearchLimitTo $limitTo = SearchLimitTo::ALL,
        public bool $returnArray = false,
    ) {
        parent::__construct($composer);
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

        $results = json_decode(implode(PHP_EOL, $output['output']), flags: JSON_OBJECT_AS_ARRAY) ?? [];

        if ($this->returnArray) {
            return $results;
        }

        $packages = [];

        foreach ($results as $result) {
            [$namespace, $name] = preg_split('/\//', $result['name'], 2);

            $packages[] = Composer::newPackage(
                $namespace,
                $name,
                $result['description'] ?? ''
            );
        }

        return Composer::newCollection($packages);
    }

    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return 'search';
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
        $arguments = [];

        if (!empty($this->type)) {
            $arguments['--type'] = $this->type;
        }

        if ($this->limitTo === SearchLimitTo::NAME_ONLY) {
            $arguments['--only-name'] = true;
        } elseif ($this->limitTo === SearchLimitTo::NAMESPACE_ONLY) {
            $arguments['--only-vendor'] = true;
        }

        $arguments['--format'] = 'json';

        $arguments['tokens'] = preg_split('/ +/', $this->query, -1, PREG_SPLIT_NO_EMPTY);

        return $arguments;
    }
}
