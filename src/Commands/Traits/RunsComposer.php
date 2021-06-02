<?php

namespace BennoThommo\Packager\Commands\Traits;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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
