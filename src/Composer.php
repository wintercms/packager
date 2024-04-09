<?php

namespace Winter\Packager;

use Winter\Packager\Commands\Command;
use Throwable;

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
     * @var string The path to the Composer home directory (where settings and cached dependencies are kept).
     */
    protected $homeDir;

    /**
     * @var string The path to working directory where the project will be built.
     */
    protected $workDir;

    /**
     * @var string The name of the JSON configuration file.
     */
    protected $configFile = 'composer.json';

    /**
     * @var string The name of the dependency directory.
     */
    protected $vendorDir = 'vendor';

    /**
     * @var int The process timeout, in seconds.
     */
    protected $timeout = 300;

    /**
     * @var int The memory limit, in MBytes.
     */
    protected $memoryLimit = 1536;

    /**
     * The current behaviour for handling abandoned packages.
     */
    protected string $auditAbandoned = 'ignore';

    /**
     * @var array<string, string|Command> A list of supported commands
     */
    protected $commands = [
        'i' => \Winter\Packager\Commands\Install::class,
        'install' => \Winter\Packager\Commands\Install::class,
        'search' => \Winter\Packager\Commands\Search::class,
        'show' => \Winter\Packager\Commands\Show::class,
        'update' => \Winter\Packager\Commands\Update::class,
        'version' => \Winter\Packager\Commands\Version::class,
    ];

    /**
     * Constructor
     *
     * @param string $workingDir The working directory where the "composer.json" file is located.
     * @param string $homeDir The Composer home directory.
     */
    public function __construct(string $workingDir = null, string $homeDir = null)
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
            $command = new $this->commands[$name]($this);
        } elseif (is_object($this->commands[$name]) && $this->commands[$name] instanceof Command) {
            $command = $this->commands[$name];
        } else {
            throw new \Winter\Packager\Exceptions\CommandException(
                sprintf(
                    'The handler for command "%s" is not an instance of "%s"',
                    $name,
                    Command::class
                )
            );
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
    public function setCommand(string $command, Command $commandClass): static
    {
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
}
