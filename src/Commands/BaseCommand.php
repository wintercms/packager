<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Composer;
use BennoThommo\Packager\Exceptions\WorkDirException;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Base Command class.
 *
 * Commands should extend this one to meet the specification for commands.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
abstract class BaseCommand implements Command
{
    /**
     * @var Composer Composer instance.
     */
    protected $composer;

    /**
     * @var Application Composer application.
     */
    protected $composerApp;

    /**
     * @var array An array of environment variables previous to setting up the app.
     */
    protected $preComposerEnv = [];

    /**
     * Constructor.
     *
     * Defines the Composer instance that will run the command.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Returns the instance of Composer that is running the command.
     *
     * @return Composer
     */
    public function getComposer(): Composer
    {
        return $this->composer;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function requiresWorkDir(): bool
    {
        return false;
    }

    /**
     * Sets up the environment and creates the Composer application.
     *
     * @return void
     */
    protected function setUpComposerApp(): void
    {
        ini_set('memory_limit', $this->getComposer()->getMemoryLimit());

        // Save pre-environment
        $this->preComposerEnv = [];
        foreach (array_keys($this->composerEnvVars()) as $envVar) {
            $this->preComposerEnv[$envVar] = getenv($envVar) ?: null;
        }

        // Set environment
        foreach ($this->composerEnvVars() as $envVar => $envValue) {
            if (is_string($envValue)) {
                putenv("$envVar={$this->getComposer()->$envValue()}");
            } else {
                putenv("$envVar=$envValue");
            }
        }

        // Create application
        $this->composerApp = new Application();
        $this->composerApp->setAutoExit(false);
        $this->composerApp->setCatchExceptions(false);
    }

    /**
     * Run a Composer command directly in PHP.
     *
     * This method creates the Composer application, sets the necessary environment variables, then executes the
     * command and returns the execution code and output of the Composer application after restoring the state.
     *
     * @return array the execution code, and any output from the Composer application.
     */
    protected function runComposerCommand(): array
    {
        $this->setUpComposerApp();

        $output = new BufferedOutput();

        // Set arguments
        $arguments = (!empty($this->getCommandName()))
            ? ['command' => $this->getCommandName()]
            : [];

        if ($this->requiresWorkDir()) {
            $workDir = $this->getComposer()->getWorkDir();

            if (empty($workDir)) {
                throw new WorkDirException('No working directory specified.');
            }

            if (!is_dir($workDir)) {
                throw new WorkDirException(
                    sprintf(
                        'Working directory "%s" is missing',
                        $workDir
                    )
                );
            }

            $arguments['--working-dir'] = $workDir;
        }

        $arguments = array_merge($arguments, $this->arguments());
        $input = new ArrayInput($arguments);

        // Run Composer command
        try {
            $code = $this->composerApp->run($input, $output);

            $return = [
                'code' => $code,
                'output' => explode(PHP_EOL, trim($output->fetch())),
            ];
        } catch (\Exception $e) {
            $return = [
                'code' => 1,
                'output' => explode(PHP_EOL, $e->getMessage()),
                'exception' => $e,
            ];

            // Restores the error handler away from Composer's in-built error handler
            restore_error_handler();
        }

        $this->tearDownComposerApp();

        return $return;
    }

    /**
     * Restores the environment and removes the Composer application from memory.
     *
     * @return void
     */
    protected function tearDownComposerApp(): void
    {
        $this->composerApp = null;

        // Reset environment
        foreach ($this->preComposerEnv as $envVar => $envValue) {
            if (!is_null($envVar)) {
                putenv("$envVar=$envValue");
            } else {
                putenv("$envVar");
            }
        }
    }

    /**
     * Defined environment variables.
     *
     * The keys of this array are the environment variables, and the values are - in the case of a string - a callback
     * to the Composer instance, or - in the case of other values - the value to be used for the environment variable.
     *
     * @return array
     */
    protected function composerEnvVars(): array
    {
        return [
            'COMPOSER' => 'getConfigFile',
            'COMPOSER_DISABLE_XDEBUG_WARN' => 1,
            'COMPOSER_HOME' => 'getHomeDir',
            'COMPOSER_NO_INTERACTION' => 1,
            'COMPOSER_MEMORY_LIMIT' => 'getMemoryLimit',
            'COMPOSER_PROCESS_TIMEOUT' => 'getTimeout',
        ];
    }
}
