<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests;

use BennoThommo\Packager\Composer;
use PHPUnit\Framework\TestCase;

class ComposerTestCase extends TestCase
{
    /** @var string */
    protected $homeDir;

    /** @var string */
    protected $workDir;

    /** @var Composer */
    protected $composer;

    /**
     * @before
     */
    public function setUpTestDirs(): void
    {
        $homeDir = __DIR__ . '/tmp/homeDir';
        $workDir = __DIR__ . '/tmp/workDir';

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
     * @before
     */
    public function createComposer(): void
    {
        $this->composer = new Composer();
    }

    /**
     * Mocks a Composer application output.
     *
     * @param string $command
     * @param string $commandClass
     * @param integer $code
     * @param string $output
     * @return void
     */
    protected function mockCommandOutput(string $command, string $commandClass, int $code = 0, string $output = ''): void
    {
        // Mock the command and replace the "runCommand" method
        $mockCommand = $this->getMockBuilder($commandClass)
            ->setConstructorArgs([
                $this->composer
            ])
            ->onlyMethods(['runComposerCommand'])
            ->getMock();

        $mockCommand
            ->method('runComposerCommand')
            ->willReturn([
                'code' => $code,
                'output' => explode(PHP_EOL, $output),
            ]);

        $this->composer->setCommand($command, $mockCommand);
    }

    /**
     * Copies files and directories to the temp work path.
     *
     * @param string $path
     * @return void
     */
    protected function copyToWorkDir(string $path): void
    {
        if (is_file($path)) {
            $info = pathinfo($path);
            @copy($path, $this->workDir . '/' . $info['basename']);
        }
    }

    /**
     * Returns the base path to the tests directory.
     *
     * @return string
     */
    protected function testBasePath(): string
    {
        return __DIR__;
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
