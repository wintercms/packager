<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Exceptions\ComposerJsonException;
use BennoThommo\Packager\Parser\InstallOutputParser;

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

        // Retrieve changes from update
        // $result = [
        //     'installed' => [],
        //     'updated' => [],
        //     'removed' => [],
        // ];
        // $parser = new VersionParser();

        // // Throw exception on conflict
        // if ($conflicts) {
        //     $exception = new PackageConflictException();
        //     $exception->setProblems($problems);

        //     throw $exception;
        // }

        return $this;
    }

    public function getCommandName(): string
    {
        return 'update';
    }

    public function requiresWorkDir(): bool
    {
        return true;
    }

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
