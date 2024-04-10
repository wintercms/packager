<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Enums\ListType;
use Winter\Packager\Package\Collection;
use Winter\Packager\Package\Packagist;

/**
 * List command.
 *
 * This is a divergence from the Composer `list` command, as this lists all packages in Composer, optionally of a
 * given type or vendor. This directly queries the Packagist API for this information.
 *
 * @author Ben Thomson
 * @since 0.3.0
 */
class ListCommand implements Command
{
    /**
     * Command constructor.
     */
    final public function __construct(
        protected Composer $composer,
        protected ListType $type = ListType::ALL,
        protected ?string $query = null
    ) {
    }

    /**
     * Execute the command.
     */
    public function execute()
    {
        $results = $this->queryPackagist();
        $packages = [];

        if (!isset($results['packages'])) {
            return new Collection();
        }

        foreach ($results['packages'] as $package => $data) {
            [$namespace, $name] = $this->nameSplit($package);

            $packages[] = Composer::newPackage(
                namespace: $namespace,
                name: $name,
                description: '',
                type: ($this->type === ListType::TYPE) ? $this->query : ($data['type'] ?? ''),
            );
        }

        return new Collection($packages);
    }

    /**
     * Queries Packagist for a list of packages.
     *
     * @return array<string, array<string, array<string, string>>>
     */
    protected function queryPackagist(): array
    {
        return Packagist::listPackages($this->type, $this->query);
    }

    /**
     * Split package name from namespace.
     *
     * @return string[]
     */
    protected function nameSplit(string $name): array
    {
        return preg_split('/\//', $name, 2);
    }
}
