<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Enums\ShowMode;
use Winter\Packager\Enums\VersionStatus;
use Winter\Packager\Exceptions\CommandException;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Show command
 * @coversDefaultClass \Winter\Packager\Commands\Show
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
        $results = $this->composer->show();

        $this->assertEquals(2, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('composer', $results['composer/semver']->getNamespace());
        $this->assertEquals('semver', $results['composer/semver']->getName());
        $this->assertEquals('1.7.1', $results['composer/semver']->getVersion());

        $this->assertEquals('composer', $results['composer/ca-bundle']->getNamespace());
        $this->assertEquals('ca-bundle', $results['composer/ca-bundle']->getName());
        $this->assertEquals('1.2.9', $results['composer/ca-bundle']->getVersion());
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

        /** @var \Winter\Packager\Package\DetailedPackage */
        $result = $this->composer->show(ShowMode::INSTALLED, 'composer/ca-bundle');

        $this->assertInstanceOf(\Winter\Packager\Package\Package::class, $result);
        $this->assertEquals('composer', $result->getNamespace());
        $this->assertEquals('ca-bundle', $result->getName());
        $this->assertEquals('library', $result->getType());
        $this->assertContains('cabundle', $result->getKeywords());
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
        $this->composer->show(ShowMode::INSTALLED, 'missing/package');
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
        $results = $this->composer->show(ShowMode::OUTDATED);

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        /** @var \Winter\Packager\Package\VersionedPackage */
        $package = $results['composer/semver'];
        $this->assertEquals('composer', $package->getNamespace());
        $this->assertEquals('semver', $package->getName());
        $this->assertEquals('1.7.1', $package->getVersion());
        $this->assertNotEmpty($package->getLatestVersion());
        $this->assertEquals(VersionStatus::MAJOR_UPDATE, $package->getUpdateStatus());

        /** @var \Winter\Packager\Package\VersionedPackage */
        $package = $results['composer/ca-bundle'];
        $this->assertEquals('composer', $package->getNamespace());
        $this->assertEquals('ca-bundle', $package->getName());
        $this->assertEquals('1.2.9', $package->getVersion());
        $this->assertNotEmpty($package->getLatestVersion());
        $this->assertEquals(VersionStatus::SEMVER_UPDATE, $package->getUpdateStatus());
    }
}
