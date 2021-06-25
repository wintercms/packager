<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Exceptions\CommandException;
use BennoThommo\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Show command
 * @coversDefaultClass \BennoThommo\Packager\Commands\Show
 */
final class ShowTest extends ComposerTestCase
{
    /**
     * @before
     */
    public function setUpDirectories()
    {
        $this->composer
            ->setHomeDir($this->homeDir)
            ->setWorkDir($this->workDir);
    }

    /**
     * @test
     * @testdox can show installed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanShowInstalledPackages()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update();
        $result = $this->composer->show();

        $this->assertIsArray($result);
        $this->assertCount(2, $result['installed']);
        $this->assertEquals('composer/ca-bundle', $result['installed'][0]['name']);
        $this->assertEquals('1.2.9', $result['installed'][0]['version']);
        $this->assertEquals('composer/semver', $result['installed'][1]['name']);
        $this->assertEquals('1.7.1', $result['installed'][1]['version']);
    }

    /**
     * @test
     * @testdox can show one installed package.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanShowOnePackage()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update();
        $result = $this->composer->show(null, 'composer/ca-bundle');

        $this->assertIsArray($result);
        $this->assertEquals('composer/ca-bundle', $result['name']);
        $this->assertEquals('library', $result['type']);
    }

    /**
     * @test
     * @testdox can safely handle a missing package.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanSafelyHandleAMissingPackage()
    {
        $this->expectException(CommandException::class);

        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update();
        $this->composer->show(null, 'missing/package');
    }

    /**
     * @test
     * @testdox can show installed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanShowOutdatedPackages()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update();
        $result = $this->composer->show('outdated');

        $this->assertIsArray($result);
        $this->assertCount(2, $result['installed']);

        $this->assertArrayHasKey('latest', $result['installed'][0]);
        $this->assertArrayHasKey('latest-status', $result['installed'][0]);
    }
}
