<?php

namespace Winter\Packager\Package;

use Winter\Packager\Composer;

/**
 * Lock file class.
 *
 * This class provides functionality for reading the Composer lock file. This is used to determine (some) details about
 * the packages that are installed in the current project - mainly limited to versioning and type information.
 *
 * We prefer to rely on the Packagist API for most information, as those details are specifically provided by the
 * authors of the library, whereas the lock file is local and easily manipulated.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 */
class LockFile
{
    protected bool $exists = false;

    /**
     * @var array<string, array<string, string>> Collated package information.
     */
    protected array $packages = [];

    public function __construct(
        protected Composer $composer,
    ) {
        if (file_exists(
            rtrim($this->composer->getWorkDir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $this->composer->getLockFilename()
        )) {
            $this->exists = true;
            $this->collatePackageInfo();
        }
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
            file_get_contents(
                rtrim($this->composer->getworkDir(), DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . $this->composer->getLockFilename()
            ),
            true
        );

        foreach ($lockFile['packages'] as $package) {
            $this->packages[$package['name']] = [
                'version' => $package['version'],
                'type' => $package['type'],
            ];
        }
    }
}
