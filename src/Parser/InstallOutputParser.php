<?php

namespace BennoThommo\Packager\Parser;

class InstallOutputParser implements Parser
{
    public function parse(array $output)
    {
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
    }
}
