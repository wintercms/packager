<?php

namespace Winter\Packager\Package;

use Winter\Packager\Composer;

/**
 * Installed file class.
 *
 * This class provides functionality for reading the Composer installed.json. This is used to determine
 * details about the packages that are installed in the current project.
 *
 * @author Luke Towers
 * @since 0.4.2
 */
class InstalledFile
{
    protected bool $exists = false;

    /**
     * @var array<string, array<string, string>> Collated package information.
     */
    public array $packages = [];

    public function __construct(
        protected Composer $composer,
    ) {
        if (file_exists($this->getFilePath())) {
            $this->exists = true;
            $this->collatePackageInfo();
        }
    }

    protected function getFilePath(): string
    {
        return $this->composer->getComposerVendorDir()
            . DIRECTORY_SEPARATOR
            . 'installed.json';
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function getVersion(string $namespace, string $name): ?string
    {
        if (!array_key_exists($namespace . '/' . $name, $this->packages)) {
            return null;
        }

        return $this->packages[$namespace . '/' . $name]['version'];
    }

    public function getType(string $namespace, string $name): ?string
    {
        if (!array_key_exists($namespace . '/' . $name, $this->packages)) {
            return null;
        }

        return $this->packages[$namespace . '/' . $name]['type'];
    }

    protected function collatePackageInfo(): void
    {
        $lockFile = json_decode(
            file_get_contents($this->getFilePath()),
            flags: JSON_OBJECT_AS_ARRAY
        );

        foreach ($lockFile['packages'] as $package) {
            $this->packages[$package['name']] = $package;
        }
    }
}
