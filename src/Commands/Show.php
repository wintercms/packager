<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Enums\ShowMode;
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
     * Command constructor.
     *
     * @param Composer $composer Composer instance
     * @param ShowMode $mode Mode to run the command against
     * @param string|null $package Individual package to search
     * @param boolean $noDev Exclude dev dependencies from search
     * @return void
     */
    final public function __construct(
        Composer $composer,
        public ShowMode $mode = ShowMode::INSTALLED,
        public ?string $package = null,
        public bool $noDev = false
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

        if (is_null($this->package) && $this->mode->isCollectible()) {
            // Convert packages in simple lists to a package collection
            $results = $results[$this->mode->getComposerArrayKeyName()];

            foreach ($results as $result) {
                [$namespace, $name] = $this->nameSplit($result['name']);

                if ($this->mode->isLocal() && $this->composer->getLockFile()->exists()) {
                    $result['version'] = $this->composer->getLockFile()->getVersion($namespace, $name);
                    $result['type'] = $this->composer->getLockFile()->getType($namespace, $name);
                }

                if (isset($result['version'])) {
                    $packages[] = Composer::newVersionedPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                        $result['type'] ?? '',
                        $result['version'],
                        $result['latest'] ?? '',
                        VersionStatus::tryFrom($result['latest-status'] ?? '') ?? VersionStatus::UP_TO_DATE
                    );
                } else {
                    $packages[] = Composer::newPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                        $result['type'] ?? '',
                    );
                }
            }

            return Composer::newCollection($packages);
        } elseif ($this->mode === ShowMode::SELF) {
            $result = $results;
            [$namespace, $name] = $this->nameSplit($result['name']);

            // Return the current package
            return Composer::newDetailedVersionedPackage(
                namespace: $namespace,
                name: $name,
                description: $result['description'] ?? '',
                keywords: $result['keywords'] ?? [],
                type: $result['type'] ?? 'library',
                homepage: $result['homepage'] ?? '',
                authors: $result['authors'] ?? [],
                licenses: $result['licenses'] ?? [],
                support: $result['support'] ?? [],
                funding: $result['funding'] ?? [],
                requires: $result['require'] ?? [],
                devRequires: $result['require-dev'] ?? [],
                extras: $result['extra'] ?? [],
                suggests: $result['suggest'] ?? [],
                conflicts: $result['conflict'] ?? [],
                replaces: $result['replace'] ?? [],
                readme: $result['readme'] ?? '',
                version: $result['versions'][0] ?? '',
            );
        } elseif (!is_null($this->package)) {
            $result = $results;
            [$namespace, $name] = $this->nameSplit($result['name']);

            if ($this->mode->isLocal() && $this->composer->getLockFile()->exists()) {
                $result['version'] = $this->composer->getLockFile()->getVersion($namespace, $name);
                $result['type'] = $this->composer->getLockFile()->getType($namespace, $name);
            }

            if (
                isset($result['licenses'])
                || isset($result['authors'])
                || isset($result['requires'])
                || isset($result['require-dev'])
            ) {
                if (isset($result['version'])) {
                    return Composer::newDetailedVersionedPackage(
                        $namespace,
                        $name,
                        description: $result['description'] ?? '',
                        keywords: $result['keywords'] ?? [],
                        type: $result['type'] ?? 'library',
                        homepage: $result['homepage'] ?? '',
                        authors: $result['authors'] ?? [],
                        licenses: $result['licenses'] ?? [],
                        support: $result['support'] ?? [],
                        funding: $result['funding'] ?? [],
                        requires: $result['require'] ?? [],
                        devRequires: $result['require-dev'] ?? [],
                        extras: $result['extra'] ?? [],
                        conflicts: $result['conflict'] ?? [],
                        replaces: $result['replace'] ?? [],
                        readme: $result['readme'] ?? '',
                        version: $result['version'],
                    );
                } else {
                    return Composer::newDetailedPackage(
                        $namespace,
                        $name,
                        description: $result['description'] ?? '',
                        keywords: $result['keywords'] ?? [],
                        type: $result['type'] ?? 'library',
                        homepage: $result['homepage'] ?? '',
                        authors: $result['authors'] ?? [],
                        licenses: $result['licenses'] ?? [],
                        support: $result['support'] ?? [],
                        funding: $result['funding'] ?? [],
                        requires: $result['require'] ?? [],
                        devRequires: $result['require-dev'] ?? [],
                        extras: $result['extra'] ?? [],
                        conflicts: $result['conflict'] ?? [],
                        replaces: $result['replace'] ?? [],
                        readme: $result['readme'] ?? '',
                    );
                }
            } else {
                if (isset($result['version'])) {
                    return Composer::newVersionedPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                        $result['type'] ?? '',
                        $result['version'],
                        $result['latest'] ?? '',
                        VersionStatus::tryFrom($result['latest-status'] ?? '') ?? VersionStatus::UP_TO_DATE
                    );
                } else {
                    return Composer::newPackage(
                        $namespace,
                        $name,
                        $result['description'] ?? '',
                        $result['type'] ?? '',
                    );
                }
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return 'show';
    }

    /**
     * @inheritDoc
     */
    protected function requiresWorkDir(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function arguments(): array
    {
        $arguments = [];

        if (!empty($this->package)) {
            $arguments['package'] = $this->package;
        }

        if ($this->mode !== ShowMode::INSTALLED) {
            $arguments['--' . $this->mode->value] = true;
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
