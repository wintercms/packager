<?php

namespace BennoThommo\Packager\Parser;

use Composer\Semver\VersionParser;

class InstallOutputParser implements Parser
{
    public function parse(array $output)
    {
        $parser = new VersionParser;
        $packageOperations = false;
        $conflicts = false;
        $packages = [
            'installed' => [],
            'updated' => [],
            'removed' => [],
        ];
        $problems = [];
        $readingProblem = false;
        $currentProblem = [];

        foreach ($output as $line) {
            if (strpos($line, 'Package operations:') === 0) {
                $packageOperations = true;
            }

            if (strpos($line, 'Your requirements could not be resolved') === 0) {
                $conflicts = true;
            }

            if ($packageOperations && !$conflicts) {
                $line = trim($line);

                if (strpos($line, '- Installing') === 0) {
                    preg_match(
                        '/^\- Installing ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*) \(([^\)]+)\)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];
                    $version = $parser->normalize($matches[5]);

                    $packages['installed'][$package] = $version;
                } elseif (strpos($line, '- Upgrading') === 0) {
                    preg_match(
                        '/^\- Upgrading ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*) \(([^\)]+)\)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];
                    [$oldVersion, $newVersion] = explode(' => ', $matches[5]);
                    $oldVersion = $parser->normalize($oldVersion);
                    $newVersion = $parser->normalize($newVersion);

                    $packages['updated'][$package] = [$oldVersion, $newVersion];
                } elseif (strpos($line, '- Removing') === 0) {
                    preg_match(
                        '/^\- Removing ([a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*)/i',
                        $line,
                        $matches
                    );

                    $package = $matches[1];

                    $packages['removed'][] = $package;
                }
            }

            if ($conflicts) {
                if ($readingProblem) {
                    if (strpos($line, '    - ') === 0) {
                        $currentProblem[] = trim($line);
                    } else {
                        $readingProblem = false;
                        $problems[] = implode(PHP_EOL, $currentProblem);
                    }
                } elseif (strpos($line, '  Problem') === 0) {
                    $readingProblem = true;
                    $currentProblem = [];
                }
            }
        }

        return [
            'conflicts' => $conflicts,
            'problems' => $problems,
            'packages' => $packages,
        ];
    }
}
