<?php

namespace Winter\Packager\Commands;

use Winter\Packager\Composer;
use Winter\Packager\Exceptions\WorkDirException;
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
     * Composer instance.
     */
    protected Composer $composer;

    /**
     * Composer application.
     */
    protected ?Application $composerApp;

    /**
     * @var array<string, string> An array of environment variables previous to setting up the app.
     */
    protected array $preComposerEnv = [];

    /**
     * Constructor.
     *
     * Defines the Composer instance that will run the command.
     *
     * @param Composer $composer Composer instance
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Make a new instance of the command.
     *
     * @param \Winter\Packager\Composer $composer
     * @param mixed[] $args
     */
    public static function make(Composer $composer, mixed ...$args): static
    {
        /* @phpstan-ignore-next-line */
        return new static($composer, ...$args);
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
     * Provides the command name for Composer.
     *
     * @return string
     */
    abstract protected function getCommandName(): string;

    /**
     * Provides if the given command requires the working directory to be available.
     *
     * @return bool True if it does, false if it does not.
     */
    abstract protected function requiresWorkDir(): bool;

    /**
     * Provides the arguments for the wrapped Composer command.
     *
     * @return array<string|int,string|int|bool|null> An array of arguments to provide the Composer application.
     */
    abstract protected function arguments(): array;

    /**
     * Sets up the environment and creates the Composer application.
     *
     * @return void
     */
    protected function setUpComposerApp(): void
    {
        // Since the app is running within a normal PHP execution, we should set the max limits in the current process
        set_time_limit($this->getComposer()->getTimeout());
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
     * @return array<string, mixed> the execution code, and any output from the Composer application.
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
                'output' => preg_split('/(\n|\r\n)/', trim($output->fetch())),
                'arguments' => $arguments,
            ];
        } catch (\Exception $e) {
            $return = [
                'code' => 1,
                'output' => preg_split('/(\n|\r\n)/', $e->getMessage()),
                'exception' => $e,
                'arguments' => $arguments,
            ];
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
            putenv("$envVar=$envValue");
        }
    }

    /**
     * Defined environment variables.
     *
     * The keys of this array are the environment variables, and the values are - in the case of a string - a callback
     * to the Composer instance, or - in the case of other values - the value to be used for the environment variable.
     *
     * @return array<string,string|int>
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
            'COMPOSER_FUND' => 0,
            'COMPOSER_AUDIT_ABANDONED' => 'getAuditAbandoned',
        ];
    }
}
