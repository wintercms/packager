<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Exceptions\ComposerJsonException;

class Update extends BaseCommand
{
    public function execute(): bool
    {
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

        return true;
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

        if ($this->getComposer()->getIncludeDev()) {
            $arguments['--dev'] = true;
        } else {
            $arguments['--no-dev'] = true;
        }

        return $arguments;
    }
}
