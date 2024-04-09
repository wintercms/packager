<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Enums\VersionStatus;
use Winter\Packager\Exceptions\CommandException;

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
     * Mode to run the command against
     */
    public string $mode = 'installed';

    /**
     * Individual package to search
     */
    public ?string $package;

    /**
     * Exclude dev dependencies from search
     */
    public bool $noDev = false;

    /**
     * Command handler.
     *
     * The mode can be one of the following:
     *  - `installed`: Show installed packages
     *  - `locked`: Show locked packages
     *  - `platform`: Show platform requirements
     *  - `available`: Show all available packages
     *  - `self`: Show the current package
     *  - `path`: Show the package path
     *  - `tree`: Show packages in a dependency tree
     *  - `outdated`: Show only outdated packages
     *  - `direct`: Show only direct dependencies
     *
     * @param string|null $mode
     * @param string|null $package
     * @param boolean $noDev
     * @return void
     */
    public function handle(?string $mode = 'installed', string $package = null, bool $noDev = false): void
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

    /**
     * @inheritDoc
     */
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

        $results = json_decode(implode(PHP_EOL, $output['output']), true);
        $packages = [];

        if (is_null($this->package) && in_array($this->mode, ['installed', 'locked', 'platform', 'path', 'outdated', 'direct'])) {
            // Convert packages in simple lists to a package collection
            $key = (!in_array($this->mode, ['locked', 'platform'])) ? 'installed' : $this->mode;
            $results = $results[$key];

            foreach ($results as $result) {
                [$namespace, $name] = $this->nameSplit($result['name']);

                if (isset($result['version'])) {
                    $packages[] = Composer::newVersionedPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                        $result['version'],
                        $result['latest'] ?? '',
                        VersionStatus::tryFrom($result['latest-status'] ?? '') ?? VersionStatus::UP_TO_DATE
                    );
                } else {
                    $packages[] = Composer::newPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                    );
                }
            }

            return Composer::newCollection($packages);
        } elseif (is_null($this->package) && $this->mode === 'available') {
            // Convert entire available package list into a package collection
            foreach ($results['available'] as $result) {
                [$namespace, $name] = $this->nameSplit($result['name']);

                $packages[] = Composer::newPackage(
                    $namespace,
                    $name,
                    $result['description'] ?? '',
                );
            }

            return Composer::newCollection($packages);
        } elseif ($this->mode === 'self') {
            $result = $results;
            [$namespace, $name] = $this->nameSplit($result['name']);

            // Return the current package
            return Composer::newDetailedPackage(
                $namespace,
                $name,
                $result['description'] ?? '',
                $result['type'] ?? 'library',
                $result['keywords'] ?? [],
                $result['homepage'] ?? '',
                $result['authors'] ?? [],
                $result['licenses'] ?? [],
                $result['support'] ?? [],
                $result['funding'] ?? [],
                $result['requires'] ?? [],
                $result['devRequires'] ?? [],
                $result['extras'] ?? [],
            );
        } elseif (!is_null($this->package)) {
            $result = $results;
            [$namespace, $name] = $this->nameSplit($result['name']);

            return Composer::newDetailedPackage(
                $namespace,
                $name,
                $result['description'] ?? '',
                $result['type'] ?? 'library',
                $result['keywords'] ?? [],
                $result['homepage'] ?? '',
                $result['authors'] ?? [],
                $result['licenses'] ?? [],
                $result['support'] ?? [],
                $result['funding'] ?? [],
                $result['requires'] ?? [],
                $result['devRequires'] ?? [],
                $result['extras'] ?? [],
            );
        }

        return null;
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
