<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests;

use PHPUnit\Framework\TestCase;

class ComposerTestCase extends TestCase
{
    /** @var string */
    protected $homeDir;

    /** @var string */
    protected $workDir;

    /**
     * @before
     */
    public function setUpTestDirs(): void
    {
        $homeDir = dirname(__DIR__) . '/tmp/homeDir';
        $workDir = dirname(__DIR__) . '/tmp/workDir';

        if (is_dir($homeDir)) {
            shell_exec('rm -rf ' . $homeDir);
        }
        if (is_dir($workDir)) {
            shell_exec('rm -rf ' . $workDir);
        }

        mkdir($homeDir, 0755, true);
        mkdir($workDir, 0755, true);

        $this->homeDir = $homeDir;
        $this->workDir = $workDir;
    }

    /**
     * @after
     */
    public function tearDownTestDirs(): void
    {
        if (is_dir($this->homeDir)) {
            shell_exec('rm -rf ' . $this->homeDir);
        }
        clearstatcache(true, $this->homeDir);
    }
}
