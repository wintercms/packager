<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Exceptions\ComposerExceptionHandler;
use Winter\Packager\Parser\InstallOutputParser;

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
     * Package preference constants
     */
    const PREFER_NONE = 'none';
    const PREFER_DIST = 'dist';
    const PREFER_SOURCE = 'source';

    /**
     * @var array<int, string> Raw output from Composer
     */
    protected ?array $rawOutput;

    /**
     * Was the update successful?
     */
    protected ?bool $successful;

    /**
     * @var array<string,array<string|int, string|array<int, string>>> Array of packages installed, upgraded or removed
     */
    protected $packages = [
        'installed' => [],
        'upgraded' => [],
        'removed' => [],
    ];

    /**
     * @var array<string,array<string|int, string|array<int, string>>> Array of packages locked, upgraded or removed in
     * lock file
     */
    protected $lockFile = [
        'locked' => [],
        'upgraded' => [],
        'removed' => [],
    ];

    /**
     * @var array<int, string> Array of problems during update.
     */
    protected $problems = [];

    /**
     * Command constructor.
     *
     * @param boolean $includeDev Include "require-dev" dependencies in the update.
     * @param boolean $lockFileOnly Do a lockfile update only, do not install dependencies.
     * @param boolean $ignorePlatformReqs Ignore platform reqs when running the update.
     * @param string $installPreference Set an install preference - must be one of "none", "dist", "source"
     * @param boolean $ignoreScripts Ignores scripts that run after Composer events.
     * @param ?string $package Specify a specific package to update.
     * @return void
     */
    final public function __construct(
        Composer $composer,
        protected bool $includeDev = true,
        protected bool $lockFileOnly = false,
        protected bool $ignorePlatformReqs = false,
        protected string $installPreference = 'none',
        protected bool $ignoreScripts = false,
        protected bool $dryRun = false,
        protected ?string $package = null,
        protected bool $withAllDependencies = false
    ) {
        parent::__construct($composer);

        if (!in_array($this->installPreference, [self::PREFER_NONE, self::PREFER_DIST, self::PREFER_SOURCE])) {
            throw new \InvalidArgumentException(
                'installPreference is not an allowed value `' . $this->installPreference . '`. See: "none", "dist", "source"'
            );
        }
    }

    /**
     * Executes the command with the given options.
     *
     * @return static
     */
    public function execute()
    {
        $output = $this->runComposerCommand();

        if ($output['code'] !== 0) {
            if (isset($output['exception'])) {
                $exception = ComposerExceptionHandler::handle($output['exception'], $this);
                throw new $exception['class'](
                    $exception['message'],
                    $exception['code'] ?? 0,
                    $exception['previous'] ?? null
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
     * Gets raw output from Composer.
     *
     * @return array<int, string>
     */
    public function getRawOutput(): array
    {
        return $this->rawOutput;
    }

    /**
     * Returns installed packages.
     *
     * Packages are returned as an array, with the package name as the key, and the installed version as the value.
     *
     * @return array<string, string>
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
     * @return array<string, array<int, string>>
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
     * @return array<int, string>
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
     * @return array<string, string>
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
     * @return array<string, array<int, string>>
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
     * @return array<int, string>
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
     * @return array<int, string>
     */
    public function getProblems(): array
    {
        return $this->problems;
    }

    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return 'update';
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

        if ($this->dryRun) {
            $arguments['--dry-run'] = true;
        }

        if ($this->includeDev) {
            $arguments['--dev'] = true;
        } else {
            $arguments['--no-dev'] = true;
        }

        if ($this->lockFileOnly) {
            $arguments['--no-install'] = true;
        }

        if ($this->ignorePlatformReqs) {
            $arguments['--ignore-platform-reqs'] = true;
        }

        if ($this->ignoreScripts) {
            $arguments['--no-scripts'] = true;
        }

        if ($this->withAllDependencies) {
            $arguments['--with-all-dependencies'] = true;
        }

        if (in_array($this->installPreference, [self::PREFER_DIST, self::PREFER_SOURCE])) {
            $arguments['--prefer-' . $this->installPreference] = true;
        }

        if ($this->package) {
            $arguments['packages'] = [$this->package];
        }

        return $arguments;
    }
}
