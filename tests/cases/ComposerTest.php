<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests;

use BennoThommo\Packager\Composer;
use PHPUnit\Framework\TestCase;

/**
 * @testdox A Composer instance
 * @coversDefaultClass \BennoThommo\Packager\Composer
 */
class ComposerTest extends TestCase
{
    /** @var Composer */
    protected $composer;

    /** @var string */
    protected $homeDir;

    /**
     * @before
     */
    public function createComposerClass(): void
    {
        $this->composer = new Composer();
    }

    /**
     * @before
     */
    public function setUpTestDirs(): void
    {
        $homeDir = dirname(__DIR__) . '/tmp/homeDir';

        if (is_dir($homeDir)) {
            shell_exec('rm -rf ' . $homeDir);
        }

        mkdir($homeDir, 0755, true);

        $this->homeDir = $homeDir;
    }

    /**
     * @test
     * @testdox can set and get the home directory.
     * @covers ::getHomeDir
     * @covers ::setHomeDir
     */
    public function itCanSetAndGetHomeDir(): void
    {
        $this->assertNull($this->composer->getHomeDir());

        $this->assertSame($this->composer, $this->composer->setHomeDir($this->homeDir));

        $this->assertSame($this->homeDir, $this->composer->getHomeDir());
    }

    /**
     * @test
     * @testdox cannot set a non-existent home directory.
     * @covers ::setHomeDir
     */
    public function itCannotSetANonExistentHomeDir(): void
    {
        $this->expectException(\BennoThommo\Packager\Exceptions\HomeDirException::class);

        $this->tearDownTestDirs();

        $this->composer->setHomeDir($this->homeDir);
    }

    /**
     * @test
     * @testdox can auto-create a home directory when setting the home directory.
     * @covers ::setHomeDir
     */
    public function itCanAutoCreateAHomeDir(): void
    {
        $this->tearDownTestDirs();

        $this->assertSame($this->composer, $this->composer->setHomeDir($this->homeDir, true));

        $this->assertSame($this->homeDir, $this->composer->getHomeDir());
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
