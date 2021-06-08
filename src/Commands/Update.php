<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Exceptions\ComposerJsonException;
use BennoThommo\Packager\Parser\InstallOutputParser;

/**
 * Update command.
 *
 * Runs "composer update" within PHP.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
class Update extends BaseCommand
{
    /**
     * @var boolean Whether to do a lockfile-only update
     */
    protected $lockFileOnly = false;

    /**
     * @var boolean Include "require-dev" dependencies in the update.
     */
    protected $includeDev = true;

    /**
     * @var boolean Whether this command has already been executed
     */
    protected $executed = false;

    /**
     * @var string Raw output from Composer
     */
    protected $rawOutput;

    /**
     * @var bool Was the update successful
     */
    protected $successful;

    /**
     * @var array Array of packages installed, upgraded or removed
     */
    protected $packages = [
        'installed' => [],
        'upgraded' => [],
        'removed' => [],
    ];

    /**
     * @var array Array of packages locked, upgraded or removed in lock file
     */
    protected $lockFile = [
        'locked' => [],
        'upgraded' => [],
        'removed' => [],
    ];

    /**
     * @var array Array of problems during update.
     */
    protected $problems = [];

    /**
     * Handle options before execution.
     *
     * @param boolean $includeDev Include "require-dev" dependencies in the update.
     * @param boolean $lockFileOnly Do a lockfile update only, do not install dependencies.
     * @return void
     */
    public function handle(bool $includeDev = true, bool $lockFileOnly = false)
    {
        if ($this->executed) {
            return;
        }

        $this->includeDev = $includeDev;
        $this->lockFileOnly = $lockFileOnly;
    }

    /**
     * Executes the command with the given options.
     *
     * @return static
     */
    public function execute()
    {
        if ($this->executed) {
            return;
        }

        $this->executed = true;
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            if (isset($output['exception'])) {
                throw new ComposerJsonException(
                    sprintf(
                        'Your %s file is invalid.',
                        $this->getComposer()->getConfigFile()
                    ), 0, $output['exception']
                );
            }
        }

        $this->rawOutput = $output['output'];

        $parser = new InstallOutputParser;
        $parsed = $parser->parse($this->rawOutput);

        $this->successful = !$parsed['conflicts'];
        $this->lockFile = $parsed['lockFile'];
        $this->packages = $parsed['packages'];
        $this->problems = $parsed['problems'];

        return $this;
    }

    /**
     * Determines if the update was successful.
     *
     * @return boolean
     */
    public function isSuccessful(): bool
    {
        return $this->successful === true;
    }

    /**
     * Returns installed packages.
     *
     * Packages are returned as an array, with the package name as the key, and the installed version as the value.
     *
     * @return array
     */
    public function getInstalled(): array
    {
        return $this->packages['installed'];
    }

    /**
     * Returns the count of installed packages.
     *
     * @return int
     */
    public function getInstalledCount(): int
    {
        return count($this->getInstalled());
    }

    /**
     * Returns upgraded packages.
     *
     * Packages are returned as an array, with the package name as the key. The value is also an array with two values,
     * the previously installed version and the version that the package was updated to.
     *
     * @return array
     */
    public function getUpgraded(): array
    {
        return $this->packages['upgraded'];
    }

    /**
     * Returns the count of upgraded packages.
     *
     * @return int
     */
    public function getUpgradedCount(): int
    {
        return count($this->getUpgraded());
    }

    /**
     * Returns removed packages.
     *
     * Packages are returned as a simple array of package names that have been removed.
     *
     * @return array
     */
    public function getRemoved(): array
    {
        return $this->packages['removed'];
    }

    /**
     * Returns the count of removed packages.
     *
     * @return int
     */
    public function getRemovedCount(): int
    {
        return count($this->getRemoved());
    }

    /**
     * Returns locked packages in the lock file.
     *
     * Packages are returned as an array, with the package name as the key, and the installed version as the value.
     *
     * @return array
     */
    public function getLockInstalled(): array
    {
        return $this->lockFile['locked'];
    }

    /**
     * Returns the count of locked packages in the lock file.
     *
     * @return int
     */
    public function getLockInstalledCount(): int
    {
        return count($this->getLockInstalled());
    }

    /**
     * Returns upgraded packages in the lock file.
     *
     * Packages are returned as an array, with the package name as the key. The value is also an array with two values,
     * the previously installed version and the version that the package was updated to.
     *
     * @return array
     */
    public function getLockUpgraded(): array
    {
        return $this->lockFile['upgraded'];
    }

    /**
     * Returns the count of upgraded packages in the lock file.
     *
     * @return int
     */
    public function getLockUpgradedCount(): int
    {
        return count($this->getLockUpgraded());
    }

    /**
     * Returns removed packages in the lock file.
     *
     * Packages are returned as a simple array of package names that have been removed.
     *
     * @return array
     */
    public function getLockRemoved(): array
    {
        return $this->lockFile['removed'];
    }

    /**
     * Returns the count of removed packages in the lock file.
     *
     * @return int
     */
    public function getLockRemovedCount(): int
    {
        return count($this->getLockRemoved());
    }

    /**
     * Returns the problems encountered with the last update.
     *
     * The problems are returned as a simple array of strings.
     *
     * @return array
     */
    public function getProblems(): array
    {
        return $this->problems;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'update';
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

        if ($this->includeDev) {
            $arguments['--dev'] = true;
        } else {
            $arguments['--no-dev'] = true;
        }

        if ($this->lockFileOnly) {
            $arguments['--no-install'] = true;
        }

        return $arguments;
    }
}
