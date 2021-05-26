<?php namespace BennoThommo\Packager;

use Throwable;
use Composer\Console\Application;
use Composer\Semver\VersionParser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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
    protected $workingDir;

    /**
     * @var bool Include "require-dev" dependencies in any install or update.
     */
    protected $devDependencies = true;

    /**
     * @var array A list of supported commands
     */
    protected $commands = [
        'dumpautoload' => \BennoThommo\Packager\Commands\DumpAutoloader::class,
        'i' => \BennoThommo\Packager\Commands\Install::class,
        'install' => \BennoThommo\Packager\Commands\Install::class,
        'show' => \BennoThommo\Packager\Commands\Show::class,
        'update' => \BennoThommo\Packager\Commands\Update::class,
    ];

    /**
     * Constructor
     *
     * @param string $workingDir The working directory where the "composer.json" file is located.
     * @param string $homeDir The Composer home directory.
     */
    public function __construct(string $workingDir = null, string $homeDir = null)
    {
        $this->workingDir = $workingDir;
        $this->homeDir = $homeDir;
    }

    /**
     * Sets the Composer home directory.
     *
     * @param string $path
     * @return void
     */
    public function setHomeDir(string $path)
    {
        if (!is_dir($path)) {
            try {
                mkdir($path, 0777, true);
            } catch (Throwable $e) {
                throw new ApplicationException('Unable to write to specified Composer home path');
            }
        }

        if (!is_writable($path)) {
            throw new ApplicationException('Unable to write to specified Composer home path');
        }

        $this->homeDir = $path;
    }

    /**
     * Sets the working directory.
     *
     * @param string $path
     * @return void
     */
    public function setWorkingDir(string $path)
    {
        if (!is_writable($path)) {
            throw new ApplicationException('Unable to write to specified working directory');
        }
        if (!is_file($path . DIRECTORY_SEPARATOR . 'composer.json')) {
            throw new ApplicationException('No composer.json file found within the working directory');
        }

        $this->workingDir = $path;
    }
}
