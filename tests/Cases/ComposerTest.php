<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox A Composer instance
 * @coversDefaultClass \Winter\Packager\Composer
 */
final class ComposerTest extends ComposerTestCase
{
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
        $this->expectException(\Winter\Packager\Exceptions\HomeDirException::class);

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
     * @test
     * @testdox can set and get the working directory.
     * @covers ::getWorkDir
     * @covers ::setWorkDir
     */
    public function itCanSetAndGetWorkDir(): void
    {
        $this->assertNull($this->composer->getWorkDir());

        $this->assertSame($this->composer, $this->composer->setWorkDir($this->workDir));

        $this->assertSame($this->workDir, $this->composer->getWorkDir());
    }

    /**
     * @test
     * @testdox can set and get a name for the configuration file.
     * @covers ::getConfigFile
     * @covers ::setConfigFile
     */
    public function itCanSetAndGetConfigFile(): void
    {
        $this->assertEquals('composer.json', $this->composer->getConfigFile());

        $this->assertSame($this->composer, $this->composer->setConfigFile('packager.json'));

        $this->assertEquals('packager.json', $this->composer->getConfigFile());
    }

    /**
     * @test
     * @testdox can set and get a name for the vendor package directory.
     * @covers ::getVendorDir
     * @covers ::setVendorDir
     */
    public function itCanSetAndGetVendorDir(): void
    {
        $this->assertEquals('vendor', $this->composer->getVendorDir());

        $this->assertSame($this->composer, $this->composer->setVendorDir('packages'));

        $this->assertEquals('packages', $this->composer->getVendorDir());
    }

    /**
     * @test
     * @testdox can set and get a timeout.
     * @covers ::getTimeout
     * @covers ::setTimeout
     */
    public function itCanSetAndGetTimeout(): void
    {
        $this->assertEquals(300, $this->composer->getTimeout());

        $this->assertSame($this->composer, $this->composer->setTimeout(240));

        $this->assertEquals(240, $this->composer->getTimeout());
    }

    /**
     * @test
     * @testdox can set and get a memory limit.
     * @covers ::getMemoryLimit
     * @covers ::setMemoryLimit
     */
    public function itCanSetAndGetMemoryLimit(): void
    {
        $this->assertEquals('1536M', $this->composer->getMemoryLimit());

        $this->assertSame($this->composer, $this->composer->setMemoryLimit(2048));

        $this->assertEquals('2048M', $this->composer->getMemoryLimit());
    }
}
