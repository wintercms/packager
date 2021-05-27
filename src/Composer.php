<?php namespace BennoThommo\Packager;

use Throwable;

/**
 * Represents a Composer instance.
 *
 * This is the main class which is used to interact with a Composer project.
 *
 * @author Ben Thomson
 * @since 0.1.0
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
     * @var bool Include "require-dev" dependencies in any install or update.
     */
    protected $includeDev = true;

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
     * @var array A list of supported commands
     */
    protected $commands = [
        'dumpautoload' => \BennoThommo\Packager\Commands\DumpAutoloader::class,
        'i' => \BennoThommo\Packager\Commands\Install::class,
        'install' => \BennoThommo\Packager\Commands\Install::class,
        'show' => \BennoThommo\Packager\Commands\Show::class,
        'update' => \BennoThommo\Packager\Commands\Update::class,
        'version' => \BennoThommo\Packager\Commands\Version::class,
    ];

    /**
     * Constructor
     *
     * @param string $workingDir The working directory where the "composer.json" file is located.
     * @param string $homeDir The Composer home directory.
     * @param bool $includeDev Whether to include dev dependencies for any install or update.
     */
    public function __construct(string $workingDir = null, string $homeDir = null, bool $includeDev = true)
    {
        $this->workDir = $workingDir;
        $this->homeDir = $homeDir;
        $this->includeDev = $includeDev;
    }

    /**
     * Method overloader.
     *
     * This will execute an allowed Composer command using a method call, ie. `->install()`.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Normalise command
        $name = strtolower($name);

        if (!array_key_exists($name, $this->commands)) {
            throw new \BennoThommo\Packager\Exceptions\CommandException(
                sprintf(
                    'Invalid command "%s"',
                    $name
                )
            );
        }

        // Create a command instance.
        $command = new $this->commands[$name]($this);

        // Allow for command handling
        if (method_exists($command, 'handle') && is_callable([$command, 'handle'])) {
            call_user_func([$command, 'handle'], $arguments);
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
     * @return static
     */
    public function setHomeDir(string $path, bool $autoCreate = false)
    {
        var_dump($path);
        var_dump(is_dir($path));
        var_dump(file_exists($path));
        die();

        if (!is_dir($path)) {
            var_dump($autoCreate);

            if ($autoCreate) {
                print_r($path);
                die();
                $this->createHomeDir($path);
            } else {
                throw new \BennoThommo\Packager\Exceptions\HomeDirException(
                    sprintf(
                        'The Composer home directory at path "%s" does not exist',
                        $path
                    )
                );
            }
        }

        if (!is_writable($path)) {
            throw new \BennoThommo\Packager\Exceptions\HomeDirException(
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
            throw new \BennoThommo\Packager\Exceptions\HomeDirException(
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
     *
     * @param string $path
     * @return static
     */
    public function setWorkDir(string $path)
    {
        $this->workDir = $path;
        return $this;
    }

    /**
     * Gets whether to include dev dependencies on install or update.
     *
     * @return bool
     */
    public function getIncludeDev(): bool
    {
        return $this->includeDev;
    }

    /**
     * Include dev dependencies when installing or updating.
     *
     * @return static
     */
    public function includeDev()
    {
        $this->includeDev = true;
        return $this;
    }

    /**
     * Exclude dev dependencies when installing or updating.
     *
     * @return static
     */
    public function excludeDev()
    {
        $this->includeDev = false;
        return $this;
    }
}
