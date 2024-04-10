<?php

namespace Winter\Packager;

use Throwable;
use Winter\Packager\Commands\Command;
use Winter\Packager\Package\Collection;
use Winter\Packager\Package\Constraint;
use Winter\Packager\Package\DetailedPackage;
use Winter\Packager\Package\DetailedVersionedPackage;
use Winter\Packager\Package\LockFile;
use Winter\Packager\Package\Package;
use Winter\Packager\Package\Packagist;
use Winter\Packager\Package\VersionedPackage;
use Winter\Packager\Storage\Storage;

/**
 * Represents a Composer instance.
 *
 * This is the main class which is used to interact with a Composer project.
 *
 * @author Ben Thomson
 * @since 0.1.0
 * @method \Winter\Packager\Commands\Install i(bool $includeDev = true, bool $lockFileOnly = false, bool $ignorePlatformReqs = false, string $installPreference = 'none', bool $ignoreScripts = false, bool $dryRun = false) Install command
 * @method \Winter\Packager\Commands\Install install(bool $includeDev = true, bool $lockFileOnly = false, bool $ignorePlatformReqs = false, string $installPreference = 'none', bool $ignoreScripts = false, bool $dryRun = false) Install command
 * @method \Winter\Packager\Package\Collection search() Search command
 * @method \Winter\Packager\Package\Collection|\Winter\Packager\Package\Package|null show() Show command
 * @method \Winter\Packager\Commands\Update update(bool $includeDev = true, bool $lockFileOnly = false, bool $ignorePlatformReqs = false, string $installPreference = 'none', bool $ignoreScripts = false, bool $dryRun = false) Update command
 * @method string version(string $detail = 'version') Version command
 */
class Composer
{
    /**
     * The path to the Composer home directory (where settings and cached dependencies are kept).
     */
    protected string $homeDir;

    /**
     * The path to working directory where the project will be built.
     */
    protected string $workDir;

    /**
     * The name of the JSON configuration file.
     */
    protected string $configFile = 'composer.json';

    /**
     * The name of the Composer lock file.
     */
    protected string $lockFile = 'composer.lock';

    /**
     * An instance of the lock file class.
     */
    protected ?LockFile $lockFileInstance = null;

    /**
     * The name of the dependency directory.
     */
    protected string $vendorDir = 'vendor';

    /**
     * The process timeout, in seconds.
     */
    protected int $timeout = 300;

    /**
     * The memory limit, in MBytes.
     */
    protected int $memoryLimit = 1536;

    /**
     * The current behaviour for handling abandoned packages.
     */
    protected string $auditAbandoned = 'ignore';

    /**
     * @var array<string, string|Command> A list of supported commands
     */
    protected array $commands = [
        'i' => \Winter\Packager\Commands\Install::class,
        'install' => \Winter\Packager\Commands\Install::class,
        'search' => \Winter\Packager\Commands\Search::class,
        'show' => \Winter\Packager\Commands\Show::class,
        'update' => \Winter\Packager\Commands\Update::class,
        'version' => \Winter\Packager\Commands\Version::class,
    ];

    /**
     * @var array<string, string> Map of classes to use for packages, constraints and collections. This allows for
     * custom classes to be used for these objects, should a developer wish to extend the functionality.
     */
    protected static array $packageClasses = [
        'package' => \Winter\Packager\Package\Package::class,
        'versionedPackage' => \Winter\Packager\Package\VersionedPackage::class,
        'detailedPackage' => \Winter\Packager\Package\DetailedPackage::class,
        'detailedVersionedPackage' => \Winter\Packager\Package\DetailedVersionedPackage::class,
        'collection' => \Winter\Packager\Package\Collection::class,
        'constraint' => \Winter\Packager\Package\Constraint::class,
    ];

    /**
     * Constructor
     *
     * @param string $workingDir The working directory where the "composer.json" file is located.
     * @param string $homeDir The Composer home directory.
     */
    public function __construct(string $workingDir = '', string $homeDir = '')
    {
        $this->workDir = $workingDir;
        $this->homeDir = $homeDir;
    }

    /**
     * Method overloader.
     *
     * This will execute an allowed Composer command using a method call, ie. `->install()`.
     *
     * @param string $name
     * @param array<int|string, mixed> $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Normalise command
        $name = strtolower($name);

        if (!array_key_exists($name, $this->commands)) {
            throw new \Winter\Packager\Exceptions\CommandException(
                sprintf(
                    'Invalid command "%s"',
                    $name
                )
            );
        }

        // Create a command instance.
        if (is_string($this->commands[$name])) {
            $command = new $this->commands[$name]($this, ...$arguments);
        } elseif ($this->commands[$name] instanceof Command) {
            $command = $this->commands[$name];
        }

        // Allow for command handling
        if (method_exists($command, 'handle')) {
            call_user_func_array([$command, 'handle'], $arguments);
        }

        // Execute the command
        return call_user_func([$command, 'execute']);
    }

    /**
     * Gets the Composer home directory.
     *
     * @return string|null
     */
    public function getHomeDir(): ?string
    {
        return $this->homeDir;
    }

    /**
     * Sets the Composer home directory.
     *
     * @param string $path Path to the Composer home directory.
     * @param bool $autoCreate If true, automatically create the home directory if it is missing.
     */
    public function setHomeDir(string $path, bool $autoCreate = false): static
    {
        if (!is_dir($path)) {
            if ($autoCreate) {
                $this->createHomeDir($path);
            } else {
                throw new \Winter\Packager\Exceptions\HomeDirException(
                    sprintf(
                        'The Composer home directory at path "%s" does not exist',
                        $path
                    )
                );
            }
        }

        if (!is_writable($path)) {
            throw new \Winter\Packager\Exceptions\HomeDirException(
                sprintf(
                    'The Composer home directory at path "%s" is not writable',
                    $path
                )
            );
        }

        $this->homeDir = $path;
        return $this;
    }

    /**
     * Creates the Composer home directory.
     *
     * @param string $path Path to the Composer home directory.
     * @return void
     */
    public function createHomeDir(string $path): void
    {
        if (is_dir($path)) {
            return;
        }

        try {
            mkdir($path, 0755, true);
        } catch (Throwable $e) {
            throw new \Winter\Packager\Exceptions\HomeDirException(
                sprintf(
                    'Unable to create the Composer home directory at path "%s"',
                    $path
                )
            );
        }
    }

    /**
     * Gets the working directory.
     *
     * @return string|null
     */
    public function getWorkDir(): ?string
    {
        return $this->workDir;
    }

    /**
     * Sets the working directory.
     *
     * The working directory is the folder that contains the "composer.json" (or equivalent) config file, and the
     * vendor files to be used for a particular project.
     */
    public function setWorkDir(string $path): static
    {
        $this->workDir = $path;
        return $this;
    }

    /**
     * Gets the name for the config file, where the Composer package configuration is stored.
     *
     * By default, this is "composer.json".
     */
    public function getConfigFile(): string
    {
        return $this->configFile;
    }

    /**
     * Sets the name for the config file, where the Composer package configuration is stored.
     *
     * @param string $configFile Config file name.
     */
    public function setConfigFile(string $configFile): static
    {
        $this->configFile = $configFile;
        return $this;
    }

    /**
     * Gets the name for the lock file, where the Composer package dependencies are stored.
     *
     * By default, this is "composer.lock".
     */
    public function getLockFilename(): string
    {
        return $this->lockFile;
    }

    /**
     * Sets the name for the lock file, where the Composer package dependencies are stored.
     *
     * @param string $lockFile Lock file name.
     */
    public function setLockFile(string $lockFile): static
    {
        $this->lockFile = $lockFile;
        return $this;
    }

    /**
     * Gets an instance of the LockFile class to read the Composer lock file.
     */
    public function getLockFile(): LockFile
    {
        if (!isset($this->lockFileInstance)) {
            $this->lockFileInstance = new LockFile($this);
        }

        return $this->lockFileInstance;
    }

    /**
     * Gets the name for the vendor package directory.
     *
     * By default, this is "vendor".
     */
    public function getVendorDir(): string
    {
        return $this->vendorDir;
    }

    /**
     * Sets the name for the vendor package directory.
     *
     * @param string $vendorDir Vendor directory name.
     */
    public function setVendorDir(string $vendorDir): static
    {
        $this->vendorDir = $vendorDir;
        return $this;
    }

    /**
     * Gets the timeout for a Composer command.
     *
     * The timeout is recorded as seconds. By default, this is 300 (5 minutes).
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Sets the timeout for a Composer command.
     *
     * @param int $timeout Timeout, in seconds.
     */
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Gets the memory limit for a Composer command.
     *
     * The memory limit is recorded (and returned) as MBytes. By default, this is 1536 (1.5 GBytes)
     */
    public function getMemoryLimit(): string
    {
        return $this->memoryLimit . 'M';
    }

    /**
     * Sets the memory limit for a Composer command.
     *
     * @param int $memoryLimit Memory limit, in megabytes.
     */
    public function setMemoryLimit(int $memoryLimit): static
    {
        $this->memoryLimit = $memoryLimit;
        return $this;
    }

    /**
     * Gets registered commands.
     *
     * @return array<string, string|Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Sets a command.
     */
    public function setCommand(string $command, string|Command $commandClass): static
    {
        // Check that command class is a valid Command class
        $reflection = new \ReflectionClass($commandClass);
        if (!$reflection->isSubclassOf(Command::class)) {
            throw new \Exception(
                sprintf(
                    'Invalid command class "%s" - the class must extend "%s"',
                    $commandClass,
                    Command::class
                )
            );
        }

        $this->commands[$command] = $commandClass;
        return $this;
    }

    /**
     * Sets the behaviour for handling abandoned packages.
     */
    public function setAuditAbandoned(string $setting = 'ignore'): static
    {
        if (in_array(strtolower($setting), ['ignore', 'report', 'fail'])) {
            throw new \Winter\Packager\Exceptions\CommandException(
                sprintf(
                    'Invalid setting for "audit-abandoned": "%s"',
                    $setting
                )
            );
        }

        $this->auditAbandoned = strtolower($setting);
        return $this;
    }

    /**
     * Gets the behaviour for handling abandoned packages.
     *
     * @return string
     */
    public function getAuditAbandoned(): string
    {
        return $this->auditAbandoned;
    }

    /**
     * Defines the classes to use for packages, constraints and collections.
     *
     * You may either overwrite a single type by providing both a `$type` and `$class` as a string, or change multiple
     * by providing an array of types and classes.
     *
     * It is up to you to ensure that the classes you provide are compatible with the classes they are replacing - at
     * the very least, you should extend the classes used by default.
     *
     * @param string|array<string, string> $type
     * @param string|null $class
     */
    public static function setPackageClass(string|array $type, ?string $class = null): void
    {
        if (is_array($type)) {
            foreach ($type as $t => $c) {
                static::setPackageClass($t, $c);
            }
            return;
        }

        if (!array_key_exists($type, static::$packageClasses)) {
            throw new \Exception(
                sprintf(
                    'Invalid package class type "%s"',
                    $type
                )
            );
        }

        static::$packageClasses[$type] = $class;
    }

    /**
     * Create a new package instance.
     */
    public static function newPackage(mixed ...$arguments): Package
    {
        $class = static::$packageClasses['package'];
        return new $class(...$arguments);
    }

    /**
     * Create a new versioned package instance.
     */
    public static function newVersionedPackage(mixed ...$arguments): VersionedPackage
    {
        $class = static::$packageClasses['versionedPackage'];
        return new $class(...$arguments);
    }

    /**
     * Create a new detailed package instance.
     */
    public static function newDetailedPackage(mixed ...$arguments): DetailedPackage
    {
        $class = static::$packageClasses['detailedPackage'];
        return new $class(...$arguments);
    }

    /**
     * Create a new detailed versioned package instance.
     */
    public static function newDetailedVersionedPackage(mixed ...$arguments): DetailedVersionedPackage
    {
        $class = static::$packageClasses['detailedVersionedPackage'];
        return new $class(...$arguments);
    }

    /**
     * Create a new package collection instance.
     */
    public static function newCollection(mixed ...$arguments): Collection
    {
        $class = static::$packageClasses['collection'];
        return new $class(...$arguments);
    }

    /**
     * Create a new constraint instance.
     */
    public static function newConstraint(mixed ...$arguments): Constraint
    {
        $class = static::$packageClasses['constraint'];
        return new $class(...$arguments);
    }

    /**
     * Set the user agent for the Packagist API requests.
     *
     * To comply with Packagist's requirements for use of their API, we require that agent names contain a name or
     * reference to the system being used, and a contact email address in the format of:
     *
     * `Name or Reference <email@address.com>`
     */
    public function setAgent(string $agent): static
    {
        Packagist::setAgent($agent);
        return $this;
    }

    /**
     * Sets the metadata storage for Packagist requests.
     */
    public function setStorage(Storage $storage): static
    {
        Packagist::setStorage($storage);
        return $this;
    }
}
