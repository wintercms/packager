<?php

namespace BennoThommo\Packager\Commands\Traits;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Runs Composer commands.
 *
 * This trait creates and runs Composer commands directly within PHP. After execution, it tears down the application
 * and reverts the state.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
trait RunsComposer
{
    /**
     * @var Application Composer application.
     */
    protected $composerApp;

    /**
     * @var array An array of environment variables previous to setting up the app.
     */
    protected $preComposerEnv = [];

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
            $arguments['--working-dir'] = $this->getComposer()->getWorkDir();
        }

        $arguments = array_merge($arguments, $this->arguments());

        // Run Composer command
        $input = new ArrayInput($arguments);
        $code = $this->composerApp->run($input, $output);

        $this->tearDownComposerApp();

        return [
            'code' => $code,
            'output' => explode(PHP_EOL, trim($output->fetch())),
        ];
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
