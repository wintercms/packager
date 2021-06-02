<?php

namespace BennoThommo\Packager\Commands;

use Symfony\Component\Console\Input\ArrayInput;

class Update extends BaseCommand
{
    public function execute(): bool
    {
        $output = $this->runCommand(
            new ArrayInput([
                'command' => 'update',
                '--working-dir' => $this->workingDir,
                '--no-dev' => true,
                '--no-progress' => true,
                '--no-scripts' => true
            ])
        );

        // Retrieve changes from update
        $result = [
            'installed' => [],
            'updated' => [],
            'removed' => [],
        ];
        $parser = new VersionParser();
        $packageOperations = false;
        $conflicts = false;
        $problems = [];
        $readingProblem = false;
        $currentProblem = [];

        foreach ($output as $line) {
            if (starts_with($line, 'Package operations:')) {
                $packageOperations = true;
            }

            if (starts_with($line, 'Your requirements could not be resolved')) {
                $conflicts = true;
            }

            if ($packageOperations && !$conflicts) {
                if (starts_with($line, '  - Installing ')) {
                    preg_match(
                        '/ +\- Installing ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*) \(([^\)]+)\)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];
                    $version = $parser->normalize($matches[5]);

                    $result['installed'][$package] = $version;
                } elseif (starts_with($line, '  - Upgrading ')) {
                    preg_match(
                        '/ +\- Upgrading ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*) \(([^\)]+)\)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];
                    [$oldVersion, $newVersion] = explode(' => ', $matches[5]);
                    $oldVersion = $parser->normalize($oldVersion);
                    $newVersion = $parser->normalize($newVersion);

                    $result['updated'][$package] = [$oldVersion, $newVersion];
                } elseif (starts_with($line, '  - Removing ')) {
                    preg_match(
                        '/ +\- Removing ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];

                    $result['removed'][] = $package;
                }
            }

            if ($conflicts) {
                if ($readingProblem) {
                    if (starts_with($line, '    - ')) {
                        $currentProblem[] = trim($line);
                    } else {
                        $readingProblem = false;
                        $problems[] = implode(PHP_EOL, $currentProblem);
                    }
                } elseif (starts_with($line, '  Problem')) {
                    $readingProblem = true;
                    $currentProblem = [];
                }
            }
        }

        // Throw exception on conflict
        if ($conflicts) {
            $exception = new PackageConflictException();
            $exception->setProblems($problems);

            throw $exception;
        }

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
        return [];
    }
}
